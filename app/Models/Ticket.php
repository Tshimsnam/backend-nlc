<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'participant_id',
        'payment_id',
        'event_id',
        'ticket_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
        ];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
