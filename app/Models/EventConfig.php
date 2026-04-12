<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventConfig extends Model
{
    protected $fillable = [
        'event_id',
        'quiz_enabled',
        'evaluation_enabled',
        'certificate_enabled',
    ];

    protected $casts = [
        'quiz_enabled'        => 'boolean',
        'evaluation_enabled'  => 'boolean',
        'certificate_enabled' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
