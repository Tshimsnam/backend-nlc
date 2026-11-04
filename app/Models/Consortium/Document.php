<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'consortium_documents';

    protected $guarded = ['id'];

    protected $hidden = ['path'];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
