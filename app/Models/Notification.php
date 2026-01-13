<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'action_url',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
