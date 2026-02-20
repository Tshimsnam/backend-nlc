<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $with = ['event_prices'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'full_description',
        'date',
        'end_date',
        'time',
        'end_time',
        'location',
        'type',
        'status',
        'image',
        'agenda',
        'capacity',
        'contact_phone',
        'contact_email',
        'venue_details',
        'sponsors',
        'organizer',
        'registration_deadline',
    ];

    protected $casts = [
        'agenda' => 'array',
        'sponsors' => 'array',
        'registration_deadline' => 'date',
    ];

    public function event_prices(): HasMany
    {
        return $this->hasMany(EventPrice::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
