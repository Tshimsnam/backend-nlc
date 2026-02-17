<?php

namespace App\Services\Payments;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrangeMoneyService
{
    private string $merchantId;
    private string $merchantKey;
    private string $apiUrl;
    private bool $sandbox;

    public function __construct()
    {
        $this->merchantId = config('services.orange_money.merchant_id', '');
        $this->merchantKey = config('services.orange_money.merchant_key', '');
        $this->apiUrl = rtrim(config('services.orange_money.api_url', 'https://api.orange.com/orange-money-webpay/dev/v1'), '/');
        $this->sandbox = config('services.orange_money.sandbox', true);
    }

    /**
     * Initie un paiement Orange Money pour un ticket.
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
            return ['success' => false, 'message' => 'Numéro de téléphone requis pour Orange Money'];
        }

        // Validation des identifiants
        if (empty($this->merchantId) || empty($this->merchantKey)) {
            if (!$this->sandbox) {
                return [
                    'success' => false,
                    'message' => 'Les identifiants Orange Money ne sont pas configurés.',
                ];
            }
        }

        // Validation des URLs
        $successUrl = $urls['success_url'] ?? null;
        $failureUrl = $urls['failure_url'] ?? null;
        $notifyUrl = $urls['notify_url'] ?? null;

        if (empty($successUrl) || !filter_var($successUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'SuccessURL invalide ou manquante'];
        }

        if (empty($failureUrl) || !filter_var($failureUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'FailureURL invalide ou manquante'];
        }

        // Préparer le payload
        $payload = [
            'merchant_key' => $this->merchantId,
            'currency' => strtoupper($ticket->currency),
            'order_id' => $ticket->reference,
            'amount' => (int) round((float) $ticket->amount),
            'return_url' => $successUrl,
            'cancel_url' => $failureUrl,
            'notif_url' => $notifyUrl,
            'lang' => 'fr',
            'reference' => $ticket->reference,
        ];

        // Mode sandbox
        if ($this->sandbox && empty($this->merchantId)) {
            Log::info('Orange Money sandbox: payment initiated', [
                'ticket_reference' => $ticket->reference,
                'amount' => $ticket->amount,
            ]);

            $ticket->update([
                'payment_status' => 'pending',
                'gateway_log_id' => 'om-sandbox-' . $ticket->reference,
            ]);

            $separator = strpos($successUrl, '?') !== false ? '&' : '?';
            $redirectUrl = $successUrl . $separator . 'reference=' . $ticket->reference;

            return [
                'success' => true,
                'log_id' => 'om-sandbox-' . $ticket->reference,
                'redirect_url' => $redirectUrl,
            ];
        }

        // Appel API réel
        Log::info('Orange Money payment request', [
            'ticket_reference' => $ticket->reference,
            'amount' => $ticket->amount,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->merchantKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/webpayment", $payload);

        if (!$response->successful()) {
            Log::error('Orange Money payment failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'ticket_reference' => $ticket->reference,
            ]);

            return [
                'success' => false,
                'message' => 'Impossible d\'initier le paiement Orange Money.',
            ];
        }

        $body = $response->json();
        $paymentUrl = $body['payment_url'] ?? null;
        $transactionId = $body['pay_token'] ?? $body['notif_token'] ?? null;

        if (empty($paymentUrl) || empty($transactionId)) {
            Log::error('Orange Money: invalid response', ['body' => $body]);
            return [
                'success' => false,
                'message' => 'Réponse Orange Money invalide.',
            ];
        }

        $ticket->update([
            'payment_status' => 'pending',
            'gateway_log_id' => $transactionId,
        ]);

        return [
            'success' => true,
            'log_id' => $transactionId,
            'redirect_url' => $paymentUrl,
        ];
    }

    /**
     * Vérifie le statut d'une transaction Orange Money.
     */
    public function verifyTransaction(string $transactionId): ?array
    {
        if ($this->sandbox) {
            return [
                'status' => 'SUCCESS',
                'transaction_id' => $transactionId,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->merchantKey,
        ])->get("{$this->apiUrl}/transactionstatus/{$transactionId}");

        if (!$response->successful()) {
            return null;
        }

        return $response->json();
    }
}
