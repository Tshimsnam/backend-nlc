<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizResponse extends Model
{
    protected $fillable = [
        'session_token',
        'quiz_slug',
        'question_id',
        'answer',
        'ip_hash',
        'event_id',
    ];
}
