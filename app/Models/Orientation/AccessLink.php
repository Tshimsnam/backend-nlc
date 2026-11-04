<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class AccessLink extends Model
{
    protected $table = 'orientation_access_links';

    protected $guarded = [];

    protected $hidden = ['token_hash', 'email_hash'];

    protected $casts = ['expires_at' => 'datetime', 'used_at' => 'datetime'];
}
