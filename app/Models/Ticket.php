<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_price_id',
        'participant_id',
        'physical_qr_id',
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
        'validated_by',
        'gateway_log_id',
        'qr_data',
        'scan_count',
        'first_scanned_at',
        'last_scanned_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'days' => 'integer',
        'scan_count' => 'integer',
        'first_scanned_at' => 'datetime',
        'last_scanned_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(EventPrice::class, 'event_price_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Relation avec les scans du billet
     */
    public function scans(): HasMany
    {
        return $this->hasMany(TicketScan::class);
    }

    /**
     * Relation avec l'utilisateur qui a validé le billet
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
