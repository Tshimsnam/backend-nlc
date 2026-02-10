<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;
use App\Services\Payments\MaxiCashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    /**
     * Tous les paiements passent par MaxiCash.
     * - Mobile Money : le participant doit indiquer son numéro de téléphone (pour le prélèvement).
     * - Visa / Carte bancaire / PayPal : le participant remplit les informations sur la page sécurisée MaxiCash après redirection.
     */
    public function paymentModes(): JsonResponse
    {
        return response()->json([
            [
                'id' => 'mobile_money',
                'label' => 'Mobile Money',
                'description' => 'Indiquez votre numéro de téléphone pour le prélèvement.',
                'requires_phone' => true,
                'sub_modes' => [
                    ['id' => 'mpesa', 'label' => 'Vodacom M-Pesa'],
                    ['id' => 'orange', 'label' => 'Orange Money'],
                    ['id' => 'airtel', 'label' => 'Airtel Money'],
                    ['id' => 'p_mobile', 'label' => 'P Mobile'],
                ],
            ],
            [
                'id' => 'credit_card',
                'label' => 'Carte bancaire (Visa / MasterCard)',
                'description' => 'Vous serez redirigé vers MaxiCash pour saisir les informations de votre carte.',
                'requires_phone' => false,
            ],
            [
                'id' => 'maxicash',
                'label' => 'MaxiCash Wallet',
                'description' => 'Paiement avec votre portefeuille MaxiCash.',
                'requires_phone' => false,
            ],
            [
                'id' => 'paypal',
                'label' => 'PayPal',
                'description' => 'Vous serez redirigé vers MaxiCash puis PayPal pour finaliser le paiement.',
                'requires_phone' => false,
            ],
        ]);
    }

    public function store(StoreTicketRequest $request, Event $event, MaxiCashService $maxiCash): JsonResponse
    {
        $validated = $request->validated();

        $price = EventPrice::where('id', $validated['event_price_id'])
            ->where('event_id', $event->id)
            ->firstOrFail();

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'event_price_id' => $price->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'category' => $price->category,
            'days' => $validated['days'] ?? 1,
            'amount' => $price->amount,
            'currency' => $price->currency,
            'reference' => strtoupper(Str::random(10)),
            'pay_type' => $validated['pay_type'],
            'pay_sub_type' => $validated['pay_sub_type'] ?? null,
        ]);

        $baseUrl = rtrim(config('app.url'), '/');
        $frontendUrl = rtrim(env('FRONTEND_NLC', $baseUrl), '/');
        
        // Ajouter la référence du ticket dans les URLs de callback
        $successUrl = $validated['success_url'] ?? config('services.maxicash.success_url') ?? "{$frontendUrl}/paiement/success";
        $failureUrl = $validated['failure_url'] ?? config('services.maxicash.failure_url') ?? "{$frontendUrl}/paiement/failure";
        $cancelUrl = $validated['cancel_url'] ?? config('services.maxicash.cancel_url') ?? $failureUrl;
        
        // Ajouter la référence du ticket aux URLs
        $separator = strpos($successUrl, '?') !== false ? '&' : '?';
        $successUrl .= $separator . 'reference=' . $ticket->reference;
        
        $separator = strpos($failureUrl, '?') !== false ? '&' : '?';
        $failureUrl .= $separator . 'reference=' . $ticket->reference;
        
        $separator = strpos($cancelUrl, '?') !== false ? '&' : '?';
        $cancelUrl .= $separator . 'reference=' . $ticket->reference;
        
        $urls = [
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'failure_url' => $failureUrl,
            'notify_url' => config('services.maxicash.notify_url') ?? "{$baseUrl}/api/webhooks/maxicash",
        ];

        $result = $maxiCash->initiatePaymentForTicket($ticket, $urls);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Impossible d\'initier le paiement.',
                'ticket' => [
                    'reference' => $ticket->reference,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                ],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'reference' => $ticket->reference,
            'redirect_url' => $result['redirect_url'],
            'log_id' => $result['log_id'] ?? null,
            'message' => 'Redirection vers MaxiCash pour finaliser le paiement (Mobile Money, Visa, Carte ou PayPal).',
        ], 201);
    }

    /**
     * Afficher un ticket par référence (vérification / téléchargement).
     */
    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        // Si un gateway_log_id est fourni en query, chercher par celui-ci
        if ($request->has('gateway_log_id')) {
            $ticket = Ticket::with(['event', 'price'])
                ->where('gateway_log_id', $request->gateway_log_id)
                ->firstOrFail();
        } else {
            // Sinon, chercher par référence
            $ticket = Ticket::with(['event', 'price'])
                ->where('reference', $ticketNumber)
                ->firstOrFail();
        }

        return response()->json($ticket);
    }
}
