<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'orientation_contacts';

    protected $guarded = [];

    protected $attributes = ['active' => true];

    protected $casts = ['active' => 'boolean'];
}
