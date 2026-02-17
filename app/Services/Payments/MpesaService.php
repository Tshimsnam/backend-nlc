<?php

namespace App\Services\Payments;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $apiUrl;
    private bool $sandbox;

    public function __construct()
    {
        $this->consumerKey = config('services.mpesa.consumer_key', '');
        $this->consumerSecret = config('services.mpesa.consumer_secret', '');
        $this->shortcode = config('services.mpesa.shortcode', '');
        $this->passkey = config('services.mpesa.passkey', '');
        $this->apiUrl = rtrim(config('services.mpesa.api_url', 'https://sandbox.safaricom.co.ke'), '/');
        $this->sandbox = config('services.mpesa.sandbox', true);
    }

    /**
     * Initie un paiement M-Pesa pour un ticket.
     */
    public function initiatePaymentForTicket(Ticket $ticket, array $urls): array
    {
        // Validation des paramètres obligatoires
        if (empty($ticket->reference)) {
            return ['success' => false, 'message' => 'Référence du ticket manquante'];
        }

        if (empty($ticket->amount) || $ticket->amount <= 0) {
            return ['success' => false, 'message' => 'Montant du ticket invalide'];
        }

        if (empty($ticket->phone)) {
            return ['success' => false, 'message' => 'Numéro de téléphone requis pour M-Pesa'];
        }

        // Validation des identifiants
        if (empty($this->consumerKey) || empty($this->consumerSecret)) {
            if (!$this->sandbox) {
                return [
                    'success' => false,
                    'message' => 'Les identifiants M-Pesa ne sont pas configurés.',
                ];
            }
        }

        // Validation des URLs
        $callbackUrl = $urls['notify_url'] ?? null;

        if (empty($callbackUrl) || !filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'CallbackURL invalide ou manquante'];
        }

        // Mode sandbox
        if ($this->sandbox && empty($this->consumerKey)) {
            Log::info('M-Pesa sandbox: payment initiated', [
                'ticket_reference' => $ticket->reference,
                'amount' => $ticket->amount,
            ]);

            $ticket->update([
                'payment_status' => 'pending',
                'gateway_log_id' => 'mpesa-sandbox-' . $ticket->reference,
            ]);

            $successUrl = $urls['success_url'] ?? '';
            $separator = strpos($successUrl, '?') !== false ? '&' : '?';
            $redirectUrl = $successUrl . $separator . 'reference=' . $ticket->reference;

            return [
                'success' => true,
                'log_id' => 'mpesa-sandbox-' . $ticket->reference,
                'redirect_url' => $redirectUrl,
            ];
        }

        // Obtenir le token d'accès
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Impossible d\'obtenir le token M-Pesa.',
            ];
        }

        // Préparer le payload STK Push
        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);
        $phone = $this->normalizePhone($ticket->phone);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) round((float) $ticket->amount),
            'PartyA' => $phone,
            'PartyB' => $this->shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => $ticket->reference,
            'TransactionDesc' => 'Paiement ticket ' . $ticket->reference,
        ];

        Log::info('M-Pesa STK Push request', [
            'ticket_reference' => $ticket->reference,
            'amount' => $ticket->amount,
            'phone' => $phone,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/mpesa/stkpush/v1/processrequest", $payload);

        if (!$response->successful()) {
            Log::error('M-Pesa STK Push failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'ticket_reference' => $ticket->reference,
            ]);

            return [
                'success' => false,
                'message' => 'Impossible d\'initier le paiement M-Pesa.',
            ];
        }

        $body = $response->json();
        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;

        if (empty($checkoutRequestId)) {
            Log::error('M-Pesa: invalid response', ['body' => $body]);
            return [
                'success' => false,
                'message' => $body['errorMessage'] ?? 'Réponse M-Pesa invalide.',
            ];
        }

        $ticket->update([
            'payment_status' => 'pending',
            'gateway_log_id' => $checkoutRequestId,
        ]);

        // M-Pesa STK Push ne redirige pas, le paiement se fait sur le téléphone
        $successUrl = $urls['success_url'] ?? '';
        return [
            'success' => true,
            'log_id' => $checkoutRequestId,
            'redirect_url' => $successUrl,
            'message' => 'Demande de paiement envoyée. Veuillez vérifier votre téléphone.',
        ];
    }

    /**
     * Obtient un token d'accès M-Pesa.
     */
    private function getAccessToken(): ?string
    {
        $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->get("{$this->apiUrl}/oauth/v1/generate?grant_type=client_credentials");

        if (!$response->successful()) {
            Log::error('M-Pesa: failed to get access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        }

        return $response->json('access_token');
    }

    /**
     * Vérifie le statut d'une transaction M-Pesa.
     */
    public function verifyTransaction(string $checkoutRequestId): ?array
    {
        if ($this->sandbox && empty($this->consumerKey)) {
            return [
                'ResultCode' => '0',
                'ResultDesc' => 'The service request is processed successfully.',
                'CheckoutRequestID' => $checkoutRequestId,
            ];
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $timestamp = date('YmdHis');
        $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $payload = [
            'BusinessShortCode' => $this->shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->post("{$this->apiUrl}/mpesa/stkpushquery/v1/query", $payload);

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Normalise un numéro de téléphone pour M-Pesa (format: 254XXXXXXXXX).
     */
    private function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $phone);
        
        // Ajouter le préfixe 254 si nécessaire (Kenya)
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '254' . substr($cleaned, 1);
        } elseif (substr($cleaned, 0, 3) !== '254') {
            $cleaned = '254' . $cleaned;
        }
        
        return $cleaned;
    }
}
