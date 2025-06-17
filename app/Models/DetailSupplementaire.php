<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSupplementaire extends Model
{
     protected $fillable = [
        'contact_urgence',
        'telephone_urgence',
        'medecin_traitant',
        'telephone_medecin',
        'allergies_conditions',
        'notes_additionnelles',
        'enfant_id',
    ];
    public function enfant()
    {
        return $this->belongsTo(Enfant::class);
    }
}
