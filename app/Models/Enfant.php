<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enfant extends Model
{
    protected $fillable = [
        'nom',
        'genre',
        'date_naissance',
        'diagnostic_initial',
        'notes_medicales',
    ];
    public function parents()
    {
        return $this->belongsToMany(ParentModel::class, 'enfant_parent', 'enfant_id', 'parent_id');

    }

    public function detailsSupplementaires()
    {
        return $this->hasMany(DetailSupplementaire::class);
    }
}
