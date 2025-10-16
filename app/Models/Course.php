<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Course extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'program_id',
        'educator_id',
        'duration_minutes',
        'materials',
        'objectives',
        'status',
        'scheduled_at',
    ];

    protected $casts = [
        'materials' => 'array',
        'objectives' => 'array',
        'scheduled_at' => 'datetime',
    ];

    // Relations
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function educator()
    {
        return $this->belongsTo(User::class, 'educator_id');
    }
}
