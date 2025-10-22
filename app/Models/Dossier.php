<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dossier extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'child_id',
        'medical_history',
        'allergies',
        'medications',
        'emergency_contacts',
        'educational_goals',
        'behavioral_notes',
        'documents',
    ];

    protected $casts = [
        'medical_history' => 'array',
        'allergies' => 'array',
        'medications' => 'array',
        'emergency_contacts' => 'array',
        'educational_goals' => 'array',
        'documents' => 'array',
    ];

    // Relations
    public function child()
    {
        return $this->belongsTo(Child::class);
    }
}
