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
     * Deux modes de paiement:
     * - En ligne : via MaxiCash (Mobile Money, Carte, PayPal, etc.)
     * - En caisse : génération QR code pour paiement physique
     */
    public function paymentModes(): JsonResponse
    {
        return response()->json([
            [
                'id' => 'online',
                'label' => 'Paiement en ligne',
                'description' => 'Payez en ligne via MaxiCash (Mobile Money, Carte bancaire, PayPal, etc.)',
                'requires_phone' => false,
            ],
            [
                'id' => 'cash',
                'label' => 'Paiement en caisse',
                'description' => 'Générez votre QR code et payez directement à la caisse.',
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
            'pay_sub_type' => null, // Plus besoin de sous-types
            'payment_status' => $validated['pay_type'] === 'cash' ? 'pending_cash' : 'pending',
        ]);

        // Si paiement en caisse, retourner directement les infos du ticket avec QR code
        if ($validated['pay_type'] === 'cash') {
            return response()->json([
                'success' => true,
                'payment_mode' => 'cash',
                'ticket' => [
                    'reference' => $ticket->reference,
                    'full_name' => $ticket->full_name,
                    'email' => $ticket->email,
                    'phone' => $ticket->phone,
                    'event' => $event->title,
                    'category' => $ticket->category,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                    'status' => 'pending_cash',
                    'qr_data' => json_encode([
                        'reference' => $ticket->reference,
                        'event_id' => $event->id,
                        'amount' => $ticket->amount,
                        'currency' => $ticket->currency,
                    ]),
                ],
                'message' => 'Ticket créé avec succès. Présentez ce QR code à la caisse pour finaliser votre paiement.',
            ], 201);
        }

        // Sinon, continuer avec le flux MaxiCash normal
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
            'payment_mode' => 'online',
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

    /**
     * Valider un paiement en caisse (admin uniquement).
     */
    public function validateCashPayment(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::where('reference', $ticketNumber)->firstOrFail();

        if ($ticket->payment_status !== 'pending_cash') {
            return response()->json([
                'success' => false,
                'message' => 'Ce ticket n\'est pas en attente de paiement en caisse.',
                'current_status' => $ticket->payment_status,
            ], 400);
        }

        $ticket->update([
            'payment_status' => 'completed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Paiement en caisse validé avec succès.',
            'ticket' => [
                'reference' => $ticket->reference,
                'full_name' => $ticket->full_name,
                'amount' => $ticket->amount,
                'currency' => $ticket->currency,
                'status' => $ticket->payment_status,
            ],
        ]);
    }

    /**
     * Lister tous les tickets en attente de paiement en caisse (admin uniquement).
     */
    public function pendingCashPayments(): JsonResponse
    {
        $tickets = Ticket::with(['event', 'price'])
            ->where('payment_status', 'pending_cash')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $tickets->count(),
            'tickets' => $tickets,
        ]);
    }
}
