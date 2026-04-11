<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_slug', 'order', 'text', 'correct_answer', 'is_active', 'event_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
