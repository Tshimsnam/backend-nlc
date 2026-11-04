<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Professional extends Model
{
    protected $table = 'orientation_professionals';

    protected $guarded = [];

    protected $hidden = ['credential_notes'];

    protected $casts = ['active' => 'boolean', 'languages' => 'array', 'validated_at' => 'datetime'];
}
