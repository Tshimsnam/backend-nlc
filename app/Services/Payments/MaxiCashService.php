<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MaxiCashService
{
    private string $merchantId;
    private string $merchantPassword;
    private string $apiUrl;
    private bool $sandbox;

    public function __construct()
    {
        $this->merchantId = config('services.maxicash.merchant_id', '');
        $this->merchantPassword = config('services.maxicash.merchant_password', '');
        $this->apiUrl = rtrim(config('services.maxicash.api_url', 'https://webapi.maxicashapp.com'), '/');
        $this->sandbox = config('services.maxicash.sandbox', true);
    }

    /**
     * Initie un paiement et retourne l'URL de redirection ou les données pour le formulaire.
     */
    public function initiatePayment(Payment $payment, ?string $returnUrl = null, ?string $cancelUrl = null): array
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

        $response = Http::asForm()->post("{$this->apiUrl}/Integration/PayMerchantTransaction", $payload);

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

    /**
     * Vérifie le statut d'une transaction (pour webhook ou polling).
     */
    public function verifyTransaction(string $gatewayReference): ?array
    {
        $response = Http::asForm()->post("{$this->apiUrl}/Integration/GetTransactionStatus", [
            'MerchantID' => $this->merchantId,
            'MerchantPassword' => $this->merchantPassword,
            'TransactionID' => $gatewayReference,
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }
}
