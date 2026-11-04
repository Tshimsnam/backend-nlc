<?php

namespace App\Services\Orientation;

use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Message;
use Illuminate\Support\Facades\DB;

class Presenter
{
    public function dossier(ConsultationRequest $c, string $type, $actor): array
    {
        $full = $type === 'professional' ? Appointment::where('consultation_request_id', $c->id)->where('professional_id', $actor->id)->whereNotNull('professional_accepted_at')->whereIn('status', ['proposed', 'awaiting_payment', 'confirmed', 'completed', 'no_show'])->exists() : Access::fullFamily($actor, $c);
        $base = ['id' => $c->id, 'reference' => $c->reference, 'status' => $c->status, 'status_label' => ConsultationRequest::STATUS_LABELS[$c->status] ?? $c->status, 'authority_status' => $c->authority_status,
            'can_authorize' => $type === 'family' && (int) $c->guardian_contact_id === $actor->id && $c->authority_status === 'pending',
            'authorization_summary' => $type === 'family' && (int) $c->guardian_contact_id === $actor->id && $c->authority_status === 'pending' ? ['child_first_name' => $c->enfant_prenom, 'birth_year' => $c->enfant_date_naissance?->format('Y'), 'requester' => $c->parent_nom_complet, 'relationship' => $c->parent_lien_autre] : null,
            'full_access' => $full, 'created_at' => $c->created_at->toIso8601String(), 'processing_due_at' => $c->processing_due_at?->toIso8601String(), 'first_response_at' => $c->first_response_at?->toIso8601String()];
        if (! $full) {
            return $base + ['appointments' => $type === 'professional' ? Appointment::where('consultation_request_id', $c->id)->where('professional_id', $actor->id)->where('status', 'awaiting_professional')->with(['professional', 'payments'])->get()->map(fn ($a) => $this->appointment($a, $type)) : [], 'documents' => [], 'messages' => [], 'events' => []];
        }
        $data = collect($c->getAttributes())->only(array_keys(Intake::rules()))->map(fn ($v, $k) => $c->getAttribute($k))->all();
        $data['enfant_date_naissance'] = $c->enfant_date_naissance?->toDateString();
        $data['email'] = $c->email;
        $appointments = Appointment::where('consultation_request_id', $c->id)->when($type === 'professional', fn ($q) => $q->where('professional_id', $actor->id))->with(['professional', 'payments'])->latest('starts_at')->get();

        return $base + ['data' => $data, 'information_requested' => $c->information_requested,
            'documents' => collect($c->documents ?? [])->map(fn ($d, $i) => ['index' => $i, 'name' => $d['name'], 'size' => $d['size'], 'uploaded_at' => $d['uploaded_at'] ?? null])->values(),
            'appointments' => $appointments->map(fn ($a) => $this->appointment($a, $type)),
            'events' => DB::table('orientation_events')->where('consultation_request_id', $c->id)->where('public', true)->orderByDesc('id')->limit(60)->get(['id', 'label', 'created_at']),
            'messages' => Message::where('consultation_request_id', $c->id)->oldest()->limit(200)->get(['id', 'author', 'actor_type', 'body', 'created_at'])];
    }

    public function appointment(Appointment $a, string $type = 'family'): array
    {
        return ['id' => $a->id, 'status' => $a->status, 'status_label' => Appointment::LABELS[$a->status] ?? $a->status, 'kind' => $a->kind,
            'professional' => ['id' => $a->professional->id, 'name' => $a->professional->name, 'profession' => $a->professional->profession, 'specialty' => $a->professional->specialty],
            'starts_at' => $a->starts_at->toIso8601String(), 'ends_at' => $a->ends_at->toIso8601String(), 'duration_minutes' => $a->duration_minutes, 'expires_at' => $a->expires_at?->toIso8601String(),
            'mode' => $a->mode, 'location' => $a->location, 'meeting_url' => in_array($a->status, ['confirmed', 'completed'], true) ? $a->meeting_url : null,
            'fee_cents' => $a->fee_cents, 'currency' => 'USD', 'purpose' => $a->purpose, 'accepted_at' => $a->accepted_at?->toIso8601String(),
            'shared_summary' => $a->shared_summary, 'clinical_notes' => $type === 'professional' ? $a->clinical_notes : null, 'cancellation_reason' => $a->cancellation_reason, 'refund_status' => $a->refund_status,
            'payments' => $a->payments->map(fn ($p) => ['id' => $p->id, 'reference' => $p->reference, 'gateway' => $p->gateway, 'status' => $p->status, 'amount_cents' => $p->amount_cents, 'currency' => $p->currency, 'paid_at' => $p->paid_at?->toIso8601String()])];
    }
}
