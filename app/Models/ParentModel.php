<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    protected $table = 'parent_models';

     protected $fillable = [
        'nom',
        'email',
        'relation',
        'telephone',
    ];

    public function enfants()
    {
       return $this->belongsToMany(Enfant::class, 'enfant_parent', 'parent_id', 'enfant_id');
    }
}
