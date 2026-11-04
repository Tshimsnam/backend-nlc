<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class Structure extends Model
{
    protected $table = 'consortium_structures';

    protected $fillable = ['name'];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'structure_id');
    }

    public function application()
    {
        return $this->hasOne(Application::class, 'structure_id');
    }
}
