<?php

namespace App\Models;

use App\Enums\DurationType;
use App\Enums\ParticipantCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPrice extends Model
{
    protected $fillable = [
        'event_id',
        'category',
        'duration_type',
        'amount',
        'currency',
        'label',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'category' => ParticipantCategory::class,
            'duration_type' => DurationType::class,
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
