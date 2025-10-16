<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Appointment extends Model
{
    use HasUuids;

    protected $fillable = [
        'child_id',
        'professional_id',
        'appointment_type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'location',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    // Relations
    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
}
