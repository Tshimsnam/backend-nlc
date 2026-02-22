<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;
use App\Services\Payments\MaxiCashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /**
     * Étape 1: Créer une réservation (référence) avec juste event_price_id
     * Les informations du participant seront remplies plus tard
     */
    public function createReservation(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'event_price_id' => ['required', 'integer', 'exists:event_prices,id'],
        ]);

        $price = EventPrice::where('id', $validated['event_price_id'])
            ->where('event_id', $event->id)
            ->first();
        
        if (!$price) {
            return response()->json([
                'success' => false,
                'message' => 'Prix invalide pour cet événement.',
                'event_price_id' => $validated['event_price_id'],
            ], 404);
        }

        // Créer un ticket "réservé" sans informations complètes
        $ticket = Ticket::create([
            'event_id' => $event->id,
            'event_price_id' => $price->id,
            'full_name' => null, // À remplir plus tard
            'email' => null,     // À remplir plus tard
            'phone' => null,     // À remplir plus tard
            'category' => $price->category,
            'days' => 1,
            'amount' => $price->amount,
            'currency' => $price->currency,
            'reference' => strtoupper(Str::random(10)),
            'pay_type' => null,  // À remplir plus tard
            'payment_status' => 'reserved', // Statut spécial pour réservation
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Référence générée avec succès. Complétez vos informations pour finaliser.',
            'reservation' => [
                'reference' => $ticket->reference,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                ],
                'price' => [
                    'id' => $price->id,
                    'category' => $price->category,
                    'amount' => $price->amount,
                    'currency' => $price->currency,
                ],
                'status' => 'reserved',
                'expires_at' => now()->addMinutes(30)->toIso8601String(), // Expire dans 30 minutes
            ],
        ], 201);
    }

    /**
     * Étape 2: Compléter la réservation avec les informations du participant
     */
    public function completeReservation(Request $request, string $reference, MaxiCashService $maxiCash): JsonResponse
    {
        $ticket = Ticket::where('reference', $reference)
            ->where('payment_status', 'reserved')
            ->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable ou déjà complétée.',
                'reference' => $reference,
            ], 404);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50', 'min:9'],
            'pay_type' => ['required', 'string', 'in:online,cash'],
            'days' => ['nullable', 'integer', 'min:1'],
            'success_url' => ['nullable', 'string', 'url', 'max:500'],
            'cancel_url' => ['nullable', 'string', 'url', 'max:500'],
            'failure_url' => ['nullable', 'string', 'url', 'max:500'],
        ]);

        // Mettre à jour le ticket avec les informations complètes
        $ticket->update([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'pay_type' => $validated['pay_type'],
            'days' => $validated['days'] ?? 1,
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
                    'event' => $ticket->event->title,
                    'category' => $ticket->category,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                    'status' => 'pending_cash',
                    'qr_data' => json_encode([
                        'reference' => $ticket->reference,
                        'event_id' => $ticket->event_id,
                        'amount' => $ticket->amount,
                        'currency' => $ticket->currency,
                    ]),
                ],
                'message' => 'Ticket créé avec succès. Présentez ce QR code à la caisse pour finaliser votre paiement.',
            ], 200);
        }

        // Sinon, continuer avec le flux MaxiCash normal
        $baseUrl = rtrim(config('app.url'), '/');
        $frontendUrl = rtrim(env('FRONTEND_NLC', $baseUrl), '/');
        
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
            'message' => 'Redirection vers MaxiCash pour finaliser le paiement.',
        ], 200);
    }

    /**
     * Vérifier le statut d'une réservation
     */
    public function checkReservation(string $reference): JsonResponse
    {
        $ticket = Ticket::where('reference', $reference)->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Réservation introuvable.',
                'reference' => $reference,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'reservation' => [
                'reference' => $ticket->reference,
                'status' => $ticket->payment_status,
                'is_completed' => !is_null($ticket->full_name),
                'event' => [
                    'id' => $ticket->event_id,
                    'title' => $ticket->event->title,
                ],
                'price' => [
                    'category' => $ticket->category,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                ],
                'participant' => [
                    'full_name' => $ticket->full_name,
                    'email' => $ticket->email,
                    'phone' => $ticket->phone,
                ],
                'created_at' => $ticket->created_at->toIso8601String(),
            ],
        ]);
    }
}
