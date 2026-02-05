<?php

namespace App\Models;

use App\Enums\DurationType;
use App\Enums\ParticipantCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Participant extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'category',
        'duration_type',
    ];

    protected function casts(): array
    {
        return [
            'category' => ParticipantCategory::class,
            'duration_type' => DurationType::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}
