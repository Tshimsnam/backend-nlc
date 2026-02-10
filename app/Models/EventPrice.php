<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'category',
        'duration_type',
        'amount',
        'currency',
        'label',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'event_price_id');
    }
}
