<?php

namespace App\Services\Tickets;

use App\Enums\TicketStatus;
use App\Models\Event;
use App\Models\Ticket;

class TicketService
{
    public function generateTicketNumber(Event $event): string
    {
        $prefix = 'TKT-' . strtoupper(substr($event->slug, 0, 4)) . '-';
        $last = Ticket::where('event_id', $event->id)->orderByDesc('id')->first();
        $seq = $last ? ((int) preg_replace('/\D/', '', $last->ticket_number)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }

    public function issueTicket(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => TicketStatus::Issued]);

        return $ticket->fresh();
    }

    public function cancelTicket(Ticket $ticket): Ticket
    {
        $ticket->update(['status' => TicketStatus::Cancelled]);

        return $ticket->fresh();
    }
}
