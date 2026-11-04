<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'consortium_messages';

    protected $guarded = ['id'];
}
