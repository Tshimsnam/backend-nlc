<?php

namespace App\Models\Orientation;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'orientation_notices';

    protected $guarded = [];

    protected $hidden = ['action_url', 'recipient'];

    protected $casts = ['actor_id' => 'integer', 'action_url' => 'encrypted', 'expires_at' => 'datetime', 'sent_at' => 'datetime', 'read_at' => 'datetime'];
}
