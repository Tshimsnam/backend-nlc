<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $with = ['eventPrices'];

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
        'price',
        'capacity',
        'registered',
    ];

    protected $casts = [
        'agenda' => 'array',
        'price' => 'array',
    ];

    public function eventPrices(): HasMany
    {
        return $this->hasMany(EventPrice::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
