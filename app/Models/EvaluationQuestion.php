<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationQuestion extends Model
{
    protected $fillable = ['section', 'order', 'text', 'options', 'correct_answer', 'is_active', 'event_id'];

    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
    ];
}
