<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Child extends Model
{
    use HasUuids;

    protected $fillable = [
        'first_name',
        'last_name',
        'date_of_birth',
        'parent_id',
        'medical_info',
        'special_needs',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // Relations
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function dossier()
    {
        return $this->hasOne(Dossier::class);
    }
}
