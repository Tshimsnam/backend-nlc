<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class InformationRequest extends Model
{
    protected $table = 'consortium_information_requests';

    protected $guarded = ['id'];

    protected $casts = ['fields' => 'array', 'document_kinds' => 'array', 'due_on' => 'date', 'responded_at' => 'datetime', 'reminded_at' => 'datetime'];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
