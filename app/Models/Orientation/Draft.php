<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    protected $table = 'orientation_drafts';

    protected $guarded = [];

    protected $casts = ['data' => 'encrypted:array'];
}
