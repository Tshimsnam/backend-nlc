<?php

namespace App\Models\Orientation;

use App\Models\ConsultationRequest;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    public const ACTIVE = ['awaiting_professional', 'proposed', 'awaiting_payment', 'confirmed'];

    public const LABELS = ['awaiting_professional' => 'Disponibilité à confirmer', 'proposed' => 'Proposition à accepter', 'awaiting_payment' => 'Paiement attendu', 'confirmed' => 'Confirmé', 'completed' => 'Réalisé', 'declined' => 'Décliné', 'expired' => 'Proposition expirée', 'cancelled' => 'Annulé', 'no_show' => 'Absence'];

    protected $table = 'orientation_appointments';

    protected $guarded = [];

    protected $hidden = ['clinical_notes', 'meeting_url', 'refund_note'];

    protected $casts = ['fee_cents' => 'integer', 'duration_minutes' => 'integer', 'professional_id' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'expires_at' => 'datetime', 'professional_accepted_at' => 'datetime', 'accepted_at' => 'datetime', 'confirmed_at' => 'datetime', 'reminded_at' => 'datetime', 'completed_at' => 'datetime', 'clinical_notes' => 'encrypted:array', 'shared_summary' => 'encrypted'];

    public function consultation()
    {
        return $this->belongsTo(ConsultationRequest::class, 'consultation_request_id');
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
