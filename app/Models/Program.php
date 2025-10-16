<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Program extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'child_id',
        'created_by',
        'status',
        'start_date',
        'end_date',
        'objectives',
    ];

    protected $casts = [
        'objectives' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relations
    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
