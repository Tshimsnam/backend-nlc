<?php

namespace App\Services\Payments;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaxiCashService
{
    private string $merchantId;
    private string $merchantPassword;
    private string $apiUrl;
    private string $redirectBase;
    private string $language;
    private bool $sandbox;

    public function __construct()
    {
        $this->merchantId = config('services.maxicash.merchant_id', '');
        $this->merchantPassword = config('services.maxicash.merchant_password', '');
        $this->apiUrl = rtrim(config('services.maxicash.api_url', 'https://webapi-test.maxicashapp.com'), '/');
        $this->redirectBase = rtrim(config('services.maxicash.redirect_base', 'https://api-testbed.maxicashapp.com'), '/');
        $this->language = config('services.maxicash.language', 'fr');
        $this->sandbox = config('services.maxicash.sandbox', true);
    }

    /**
     * Initie un paiement MaxiCash pour un ticket (PayEntryWeb).
     * Tous les paiements passent par MaxiCash :
     * - Mobile Money : Telephone obligatoire (numéro pour le prélèvement)
     * - Carte Visa / Carte / PayPal : l'utilisateur remplit les infos sur la page MaxiCash
     *
     * @param  array  $urls  accepturl, cancelurl, declineurl, notifyurl (optionnel)
     */
    public function initiatePaymentForTicket(Ticket $ticket, array $urls): array
    {
        // === VALIDATION STRICTE: Aucune valeur null ne doit atteindre MaxiCash ===
        
        // 1. Valider les paramètres obligatoires du ticket
        if (empty($ticket->reference)) {
            return ['success' => false, 'message' => 'Référence du ticket manquante'];
        }
        
        if (empty($ticket->amount) || $ticket->amount <= 0) {
            return ['success' => false, 'message' => 'Montant du ticket invalide'];
        }
        
        if (empty($ticket->currency)) {
            return ['success' => false, 'message' => 'Devise du ticket manquante'];
        }

        // 2. Valider les identifiants MaxiCash
        if (empty($this->merchantId) || empty($this->merchantPassword)) {
            if (!$this->sandbox) {
                return [
                    'success' => false,
                    'message' => 'Les identifiants MaxiCash (MerchantID et MerchantPassword) ne sont pas configurés.',
                ];
            }
        }

        // 3. Valider les URLs de callback (OBLIGATOIRES)
        $successUrl = $urls['success_url'] ?? $urls['accepturl'] ?? null;
        $failureUrl = $urls['failure_url'] ?? $urls['declineurl'] ?? null;
        $cancelUrl = $urls['cancel_url'] ?? $urls['cancelurl'] ?? $failureUrl;
        $notifyUrl = $urls['notify_url'] ?? $urls['notifyurl'] ?? null;

        if (empty($successUrl) || !filter_var($successUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'SuccessURL invalide ou manquante'];
        }

        if (empty($failureUrl) || !filter_var($failureUrl, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'FailureURL invalide ou manquante'];
        }

        if (empty($cancelUrl) || !filter_var($cancelUrl, FILTER_VALIDATE_URL)) {
            $cancelUrl = $failureUrl; // Fallback sur FailureURL
        }

        // 4. Préparer les valeurs (conversion + validation)
        $amountCents = (int) round((float) $ticket->amount * 100);
        if ($amountCents <= 0) {
            return ['success' => false, 'message' => 'Montant invalide après conversion'];
        }

        $currency = $this->normalizeCurrency($ticket->currency);
        if (empty($currency)) {
            return ['success' => false, 'message' => 'Devise invalide'];
        }

        $language = strtolower($this->language);
        if (!in_array($language, ['en', 'fr'], true)) {
            $language = 'fr'; // Fallback
        }

        // 5. Construire le payload avec UNIQUEMENT des valeurs non-null
        $payload = [
            'PayType' => 'MaxiCash',
            'MerchantID' => (string) $this->merchantId,
            'MerchantPassword' => (string) $this->merchantPassword,
            'Amount' => (string) $amountCents,
            'Currency' => (string) $currency,
            'Language' => (string) $language,
            'Reference' => (string) $ticket->reference,
            'SuccessURL' => (string) $successUrl,
            'FailureURL' => (string) $failureUrl,
            'CancelURL' => (string) $cancelUrl,
        ];

        // 6. Ajouter les champs optionnels UNIQUEMENT s'ils sont valides
        if (!empty($notifyUrl) && filter_var($notifyUrl, FILTER_VALIDATE_URL)) {
            $payload['NotifyURL'] = (string) $notifyUrl;
        }

        if (!empty($ticket->email) && filter_var($ticket->email, FILTER_VALIDATE_EMAIL)) {
            $payload['Email'] = (string) $ticket->email;
        }

        // Telephone uniquement pour Mobile Money ET si valide
        if ($this->isMobileMoneyPayType($ticket->pay_type, $ticket->pay_sub_type)) {
            $phone = $this->normalizePhone($ticket->phone ?? '');
            if (!empty($phone) && strlen($phone) >= 9) {
                $payload['Telephone'] = (string) $phone;
            }
        }

        // 7. VALIDATION FINALE: Vérifier qu'aucune valeur null n'existe dans le payload
        foreach ($payload as $key => $value) {
            if ($value === null || $value === '') {
                Log::error('MaxiCash payload contains null/empty value', [
                    'field' => $key,
                    'ticket_reference' => $ticket->reference,
                ]);
                return [
                    'success' => false,
                    'message' => "Paramètre invalide: $key ne peut pas être vide",
                ];
            }
        }

        // Mode sandbox : simulation uniquement si les identifiants sont vides
        if ($this->sandbox && empty($this->merchantId)) {
            Log::info('MaxiCash sandbox: payment initiated (no credentials)', [
                'ticket_reference' => $ticket->reference,
                'amount_cents' => $amountCents,
            ]);
            $ticket->update(['payment_status' => 'pending', 'gateway_log_id' => 'sandbox-' . $ticket->reference]);

            // Ajouter la référence à l'URL de succès
            $separator = strpos($successUrl, '?') !== false ? '&' : '?';
            $redirectUrl = $successUrl . $separator . 'reference=' . $ticket->reference;

            return [
                'success' => true,
                'log_id' => 'sandbox-' . $ticket->reference,
                'redirect_url' => $redirectUrl,
            ];
        }

        // Log the request payload (without sensitive data)
        Log::info('MaxiCash PayEntryWeb request', [
            'ticket_reference' => $ticket->reference,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'has_email' => isset($payload['Email']),
            'has_telephone' => isset($payload['Telephone']),
            'has_notify_url' => isset($payload['NotifyURL']),
            'payload_keys' => array_keys($payload),
            'reference_in_payload' => $payload['Reference'] ?? 'MISSING',
            'reference_length' => strlen($payload['Reference'] ?? ''),
        ]);

        $response = Http::withOptions([
            'verify' => false, // Désactiver la vérification SSL en développement
        ])->asJson()->acceptJson()->post("{$this->apiUrl}/Integration/PayEntryWeb", $payload);

        if (! $response->successful()) {
            Log::error('MaxiCash PayEntryWeb failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'ticket_reference' => $ticket->reference,
                'payload_keys' => array_keys($payload),
            ]);

            return [
                'success' => false,
                'message' => $response->json('ResponseError') ?? 'Impossible d\'initier le paiement MaxiCash.',
            ];
        }

        $body = $response->json();
        $logId = $body['LogID'] ?? $body['ResponseData'] ?? null;

        if (empty($logId)) {
            Log::error('MaxiCash PayEntryWeb: no LogID in response', ['body' => $body]);

            return [
                'success' => false,
                'message' => $body['ResponseError'] ?? 'Réponse MaxiCash invalide.',
            ];
        }

        $ticket->update([
            'payment_status' => 'pending',
            'gateway_log_id' => (string) $logId,
        ]);

        $redirectUrl = "{$this->redirectBase}/payentryweb?logid=" . urlencode((string) $logId);

        return [
            'success' => true,
            'log_id' => (string) $logId,
            'redirect_url' => $redirectUrl,
        ];
    }

    /**
     * Ancien flux Participant/Payment (conservé pour compatibilité).
     */
    public function initiatePayment(\App\Models\Payment $payment, ?string $returnUrl = null, ?string $cancelUrl = null): array
    {
        $participant = $payment->participant;
        $event = $participant->event;

        $payload = [
            'MerchantID' => $this->merchantId,
            'MerchantPassword' => $this->merchantPassword,
            'Amount' => (float) $payment->amount,
            'Currency' => $payment->currency,
            'OrderID' => (string) $payment->id,
            'CustomerEmail' => $participant->email,
            'CustomerName' => $participant->name,
            'Description' => "Inscription: {$event->title}",
            'ReturnURL' => $returnUrl ?? config('app.url') . '/payment/return',
            'CancelURL' => $cancelUrl ?? config('app.url') . '/payment/cancel',
        ];

        if ($this->sandbox) {
            Log::info('MaxiCash sandbox: payment initiated', ['payment_id' => $payment->id]);

            return [
                'success' => true,
                'redirect_url' => $returnUrl ?? config('app.url') . '/payment/return?order_id=' . $payment->id,
                'payment_id' => $payment->id,
            ];
        }

        $response = Http::withOptions([
            'verify' => false, // Désactiver la vérification SSL en développement
        ])->asForm()->post("{$this->apiUrl}/PayMerchantTransaction", $payload);

        if (! $response->successful()) {
            Log::error('MaxiCash initiate failed', ['response' => $response->body()]);

            return [
                'success' => false,
                'message' => 'Impossible d\'initier le paiement.',
            ];
        }

        $body = $response->json();
        $payment->update([
            'gateway_reference' => $body['TransactionID'] ?? $body['Reference'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], ['maxicash_response' => $body]),
        ]);

        return [
            'success' => true,
            'redirect_url' => $body['PaymentURL'] ?? $body['RedirectURL'] ?? $returnUrl,
            'payment_id' => $payment->id,
            'gateway_reference' => $payment->gateway_reference,
        ];
    }

    public function verifyTransaction(string $gatewayReference): ?array
    {
        $response = Http::withOptions([
            'verify' => false, // Désactiver la vérification SSL en développement
        ])->asForm()->post("{$this->apiUrl}/GetTransactionStatus", [
            'MerchantID' => $this->merchantId,
            'MerchantPassword' => $this->merchantPassword,
            'TransactionID' => $gatewayReference,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    private function isMobileMoneyPayType(?string $payType, ?string $paySubType): bool
    {
        $mobileIds = ['mobile_money', 'mpesa', 'orange', 'airtel', 'maxicash'];
        $type = strtolower((string) $payType);
        $sub = strtolower((string) $paySubType);

        return in_array($type, $mobileIds, true) || in_array($sub, $mobileIds, true);
    }

    private function normalizeCurrency(string $currency): string
    {
        $c = strtoupper($currency);
        if (in_array($c, ['USD'], true)) {
            return 'maxiDollar';
        }
        if (in_array($c, ['ZAR'], true)) {
            return 'maxiRand';
        }

        return $currency;
    }

    private function normalizePhone(string $phone): string
    {
        // Supprimer tous les espaces, tirets, parenthèses
        $cleaned = preg_replace('/[\s\-\(\)]+/', '', $phone);
        
        // Retourner une chaîne vide si invalide (jamais null)
        return $cleaned ?: '';
    }
}
