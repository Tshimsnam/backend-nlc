<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'child_id',
        'author_id',
        'report_type',
        'title',
        'content',
        'observations',
        'recommendations',
        'is_confidential',
    ];

    protected $casts = [
        'observations' => 'array',
        'is_confidential' => 'boolean',
    ];

    // Relations
    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
