<?php

namespace App\Models\Consortium;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Application extends Model
{
    protected $table = 'consortium_applications';

    protected $fillable = ['structure_id', 'data'];

    protected $hidden = ['internal_notes'];

    protected $casts = ['data' => 'array', 'submitted_data' => 'array', 'submitted_document_ids' => 'array', 'submitted_at' => 'datetime', 'decided_at' => 'datetime'];

    public const STATUSES = ['draft' => 'Brouillon', 'submitted' => 'Déposée', 'under_review' => 'En étude', 'needs_information' => 'Compléments demandés', 'accepted' => 'Acceptée', 'rejected' => 'Refusée', 'withdrawn' => 'Retirée'];

    protected static function booted(): void
    {
        static::creating(function (self $application) {
            $application->reference = 'NLC-ADH-'.now()->format('Y').'-'.Str::upper(Str::random(8));
        });
    }

    public function structure()
    {
        return $this->belongsTo(Structure::class, 'structure_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'application_id');
    }

    public function informationRequests()
    {
        return $this->hasMany(InformationRequest::class, 'application_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'application_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'application_id');
    }

    public function editable(): bool
    {
        return in_array($this->status, ['draft', 'needs_information'], true);
    }

    public function latestDocuments()
    {
        return $this->documents()->orderByDesc('id')->get()->unique('kind')->values();
    }
}
