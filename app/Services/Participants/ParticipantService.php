<?php

namespace App\Services\Participants;

use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\Tickets\TicketService;
use Illuminate\Support\Facades\DB;

class ParticipantService
{
    public function __construct(
        private TicketService $ticketService
    ) {}

    /**
     * Inscription d'un participant : crée Participant → Payment (pending) → Ticket (pending).
     */
    public function register(array $data): Participant
    {
        return DB::transaction(function () use ($data) {
            $event = Event::findOrFail($data['event_id']);
            $price = $event->eventPrices()
                ->where('category', $data['category'])
                ->where('duration_type', $data['duration_type'])
                ->first();

            if (! $price) {
                throw new \InvalidArgumentException(
                    'Aucun tarif trouvé pour cette catégorie et type de durée.'
                );
            }

            if ($event->capacity && ($event->registered ?? 0) >= $event->capacity) {
                throw new \InvalidArgumentException('L\'événement est complet.');
            }

            $participant = Participant::create([
                'event_id' => $data['event_id'],
                'user_id' => $data['user_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'category' => $data['category'],
                'duration_type' => $data['duration_type'],
            ]);

            $payment = Payment::create([
                'participant_id' => $participant->id,
                'amount' => $price->amount,
                'currency' => $price->currency,
                'status' => PaymentStatus::Pending,
                'gateway' => 'maxicash',
            ]);

            $ticket = Ticket::create([
                'participant_id' => $participant->id,
                'payment_id' => $payment->id,
                'event_id' => $event->id,
                'ticket_number' => $this->ticketService->generateTicketNumber($event),
                'status' => TicketStatus::Pending,
            ]);

            return $participant->load(['payment', 'ticket', 'event']);
        });
    }
}
