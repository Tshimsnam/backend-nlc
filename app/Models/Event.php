<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
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
}
