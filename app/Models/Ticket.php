<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_price_id',
        'full_name',
        'email',
        'phone',
        'category',
        'days',
        'amount',
        'currency',
        'reference',
        'pay_type',
        'pay_sub_type',
        'payment_status',
        'gateway_log_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'days' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(EventPrice::class, 'event_price_id');
    }
}
