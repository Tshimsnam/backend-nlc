<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'orientation_messages';

    protected $guarded = [];

    protected $casts = ['body' => 'encrypted'];
}
