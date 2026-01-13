<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'category',
        'description',
        'is_public',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
    ];
}
