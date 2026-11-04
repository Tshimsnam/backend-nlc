<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'consortium_events';

    protected $guarded = ['id'];

    protected $casts = ['metadata' => 'array'];
}
