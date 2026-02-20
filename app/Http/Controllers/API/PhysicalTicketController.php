<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Participant;
use App\Models\EventPrice;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;

class PhysicalTicketController extends Controller
{
    /**
     * Mapper les catégories de prix vers les catégories de participants
     */
    private function mapPriceCategoryToParticipantCategory(string $priceCategory): string
    {
        return match($priceCategory) {
            'teacher' => 'enseignant',
            'student_1day', 'student_2days' => 'etudiant',
            'doctor' => 'medecin',
            'parent' => 'parent',
            default => 'etudiant', // Par défaut
        };
    }

    /**
     * Vérifier si un QR code physique est déjà activé
     */
    public function checkPhysicalQR(Request $request): JsonResponse
    {
        $request->validate([
            'physical_qr_id' => 'required|string',
        ]);

        $physicalQrId = $request->physical_qr_id;

        // Vérifier que le physical_qr_id commence par "PHY-"
        if (!str_starts_with($physicalQrId, 'PHY-')) {
            return response()->json([
                'success' => false,
                'error' => 'QR code invalide. Le code doit commencer par PHY-'
            ], 400);
        }

        // Vérifier si le QR code a déjà été utilisé
        $existingTicket = Ticket::where('physical_qr_id', $physicalQrId)
            ->with(['event', 'participant', 'price'])
            ->first();

        if ($existingTicket) {
            return response()->json([
                'success' => true,
                'is_activated' => true,
                'message' => 'Ce QR code a déjà été activé',
                'ticket' => $existingTicket,
                'participant' => $existingTicket->participant,
            ]);
        }

        return response()->json([
            'success' => true,
            'is_activated' => false,
            'message' => 'QR code disponible pour activation',
        ]);
    }

    /**
     * Créer un ticket depuis un QR code physique
     */
    public function createFromPhysicalQR(Request $request): JsonResponse
    {
        // Validation de base (sans vérifier l'unicité du physical_qr_id pour l'instant)
        $validated = $request->validate([
            'physical_qr_id' => 'required|string',
            'event_id' => 'required|exists:events,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'event_price_id' => 'required|exists:event_prices,id',
        ]);

        // Vérifier que le physical_qr_id commence par "PHY-"
        if (!str_starts_with($validated['physical_qr_id'], 'PHY-')) {
            return response()->json([
                'success' => false,
                'error' => 'QR code invalide. Le code doit commencer par PHY-'
            ], 400);
        }

        // Vérifier si le QR code a déjà été utilisé
        $existingTicket = Ticket::where('physical_qr_id', $validated['physical_qr_id'])
            ->with(['event', 'participant', 'price'])
            ->first();

        if ($existingTicket) {
            return response()->json([
                'success' => false,
                'already_used' => true,
                'message' => 'Ce QR code a déjà été utilisé',
                'ticket' => $existingTicket,
                'participant' => $existingTicket->participant,
            ], 409); // 409 Conflict
        }

        // Vérifier que l'event_price_id correspond bien à l'événement
        $eventPrice = EventPrice::where('id', $validated['event_price_id'])
            ->where('event_id', $validated['event_id'])
            ->first();

        if (!$eventPrice) {
            return response()->json([
                'success' => false,
                'error' => 'Prix invalide pour cet événement'
            ], 400);
        }

        // Créer le participant
        $participant = Participant::create([
            'event_id' => $validated['event_id'],
            'user_id' => auth()->id(), // L'agent qui scanne (automatique)
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'category' => $this->mapPriceCategoryToParticipantCategory($eventPrice->category),
            'duration_type' => $eventPrice->duration_type,
        ]);

        // Générer une référence unique
        $reference = 'TKT-' . time() . '-' . strtoupper(Str::random(6));

        // Créer le ticket
        $ticket = Ticket::create([
            'reference' => $reference,
            'physical_qr_id' => $validated['physical_qr_id'],
            'event_id' => $validated['event_id'],
            'participant_id' => $participant->id,
            'event_price_id' => $eventPrice->id,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'category' => $eventPrice->category, // Catégorie du prix (en anglais)
            'amount' => $eventPrice->amount,
            'currency' => $eventPrice->currency,
            'pay_type' => 'cash',
            'payment_status' => 'completed', // Directement validé
            'qr_data' => json_encode([
                'reference' => $reference,
                'event_id' => $validated['event_id'],
                'amount' => $eventPrice->amount,
                'currency' => $eventPrice->currency,
                'payment_mode' => 'cash',
                'category' => $eventPrice->category,
                'duration_type' => $eventPrice->duration_type,
            ])
        ]);

        // Charger les relations pour la réponse
        $ticket->load(['event', 'participant', 'price']);

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'participant' => $participant,
            'message' => 'Billet physique activé avec succès'
        ], 201);
    }

    /**
     * Récupérer les prix d'un événement
     */
    public function getEventPrices($eventId): JsonResponse
    {
        $event = Event::with('event_prices')->findOrFail($eventId);
        
        return response()->json([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->date,
                'location' => $event->location,
            ],
            'prices' => $event->event_prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'category' => $price->category,
                    'duration_type' => $price->duration_type,
                    'amount' => $price->amount,
                    'currency' => $price->currency,
                    'label' => $price->label,
                    'description' => $price->description,
                    // Label complet pour l'affichage
                    'display_label' => $price->label . ($price->description ? ' - ' . $price->description : ''),
                ];
            }),
        ]);
    }
}
