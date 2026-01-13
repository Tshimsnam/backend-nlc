<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Child extends Model
{
    use HasUuids;

    protected $table = 'children';

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'medical_info',
        'special_needs',
        'status',
        'gender',
        'diagnosis',
        'allergies',
        'medications',
        'transport_info',
        'additional_notes',
        'doctor_name',
        'doctor_specialty',
        'doctor_phone',
        'doctor_address',
        'emergency_contact_name',
        'emergency_contact_relation',
        'emergency_contact_phone',
        'emergency_contact_phone2',
    ];

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_child', 'child_id', 'parent_id')
            ->withPivot([
                'relationship',
                'is_primary',
                'has_custody',
                'emergency_contact_order',
            ])
            ->withTimestamps();
    }

    // dossier(), programs(), appointments(), reports() etc...
}

