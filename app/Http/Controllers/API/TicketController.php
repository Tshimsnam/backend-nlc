<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketNotificationMail;

class TicketController extends Controller
{
    /**
     * Modes de paiement disponibles:
     * - En ligne : MaxiCash, M-Pesa, Orange Money
     * - En caisse : génération QR code pour paiement physique
     */
    public function paymentModes(): JsonResponse
    {
        return response()->json([
            [
                'id' => 'cash',
                'label' => 'Paiement en caisse',
                'description' => 'Générez votre QR code et payez directement à la caisse.',
                'requires_phone' => false,
            ],
            [
                'id' => 'maxicash',
                'label' => 'MaxiCash',
                'description' => 'Payez via MaxiCash (Mobile Money, Carte bancaire, PayPal, etc.)',
                'requires_phone' => false,
            ],
            [
                'id' => 'mpesa',
                'label' => 'M-Pesa',
                'description' => 'Payez via M-Pesa (Safaricom - Kenya)',
                'requires_phone' => true,
            ],
            [
                'id' => 'orange_money',
                'label' => 'Orange Money',
                'description' => 'Payez via Orange Money',
                'requires_phone' => true,
            ],
        ]);
    }

    public function store(StoreTicketRequest $request, Event $event): JsonResponse
    {
        $validated = $request->validated();

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

        $gateway = $validated['pay_type'] ?? 'maxicash';

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
            'pay_type' => $gateway,
            'pay_sub_type' => null,
            'payment_status' => in_array($gateway, ['cash', 'mpesa', 'orange_money']) ? 'pending_cash' : 'pending',
        ]);

        // Si paiement en caisse, mpesa ou orange_money : retourner directement les infos du ticket
        if (in_array($gateway, ['cash', 'mpesa', 'orange_money'])) {
            return response()->json([
                'success' => true,
                'payment_mode' => $gateway,
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
                        'payment_mode' => $gateway,
                    ]),
                ],
                'message' => $gateway === 'cash' 
                    ? 'Ticket créé avec succès. Présentez ce QR code à la caisse pour finaliser votre paiement.'
                    : 'Ticket créé avec succès. Suivez les instructions pour effectuer le paiement.',
            ], 201);
        }

        // Pour maxicash uniquement : créer le service de paiement
        try {
            $paymentService = PaymentGatewayFactory::create($gateway);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        // Préparer les URLs de callback
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
            'notify_url' => config('services.maxicash.notify_url') ?? "{$baseUrl}/api/webhooks/{$gateway}",
        ];

        $result = $paymentService->initiatePaymentForTicket($ticket, $urls);

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
            'payment_mode' => $gateway,
            'reference' => $ticket->reference,
            'redirect_url' => $result['redirect_url'],
            'log_id' => $result['log_id'] ?? null,
            'message' => $result['message'] ?? "Redirection vers {$gateway} pour finaliser le paiement.",
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
                ->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket trouvé avec cet identifiant de transaction.',
                    'gateway_log_id' => $request->gateway_log_id,
                ], 404);
            }
        } else {
            // Sinon, chercher par référence
            $ticket = Ticket::with(['event', 'price'])
                ->where('reference', $ticketNumber)
                ->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket trouvé avec cette référence.',
                    'reference' => $ticketNumber,
                ], 404);
            }
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'reference' => $ticket->reference, // Ajouter la référence à la racine pour faciliter l'accès
        ]);
    }

    /**
     * Valider un paiement en caisse (admin uniquement).
     */
    public function validateCashPayment(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::where('reference', $ticketNumber)->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun ticket trouvé avec cette référence.',
                'reference' => $ticketNumber,
            ], 404);
        }

        if ($ticket->payment_status !== 'pending_cash') {
            return response()->json([
                'success' => false,
                'message' => 'Ce ticket n\'est pas en attente de paiement en caisse.',
                'current_status' => $ticket->payment_status,
            ], 400);
        }

        // Récupérer l'utilisateur connecté (agent qui valide)
        $userId = $request->user() ? $request->user()->id : null;

        $ticket->update([
            'payment_status' => 'completed',
            'validated_by' => $userId,
        ]);

        // Incrémenter le compteur registered dans l'événement
        Event::where('id', $ticket->event_id)->increment('registered');

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

    /**
     * Envoyer une notification par email pour un ticket.
     */
    public function sendNotification(string $ticketNumber): JsonResponse
    {
        try {
            $ticket = Ticket::with(['event', 'price'])
                ->where('reference', $ticketNumber)
                ->first();
            
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun ticket trouvé avec cette référence.',
                    'reference' => $ticketNumber,
                ], 404);
            }

            // Vérifier que le ticket a un email
            if (empty($ticket->email)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce ticket n\'a pas d\'adresse email associée.',
                ], 400);
            }

            // Envoyer l'email
            Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée avec succès à ' . $ticket->email,
                'ticket' => [
                    'reference' => $ticket->reference,
                    'full_name' => $ticket->full_name,
                    'email' => $ticket->email,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de la notification : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rechercher des tickets par numéro de téléphone.
     */
    public function searchByPhone(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $request->input('phone');

        // Rechercher tous les tickets avec ce numéro de téléphone
        $tickets = Ticket::with(['event', 'price', 'participant'])
            ->where('phone', $phone)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($tickets->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun ticket trouvé pour ce numéro de téléphone.',
                'phone' => $phone,
                'tickets' => [],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => count($tickets) . ' ticket(s) trouvé(s).',
            'phone' => $phone,
            'count' => $tickets->count(),
            'tickets' => $tickets,
        ]);
    }

}
