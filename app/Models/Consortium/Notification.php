<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'consortium_notifications';

    protected $guarded = ['id'];

    // Les liens de vérification/récupération sont chiffrés au repos et absents de l'API.
    protected $hidden = ['recipient', 'action_url'];

    protected $casts = ['action_url' => 'encrypted', 'sent_at' => 'datetime', 'read_at' => 'datetime', 'expires_at' => 'datetime'];
}
