<?php

namespace App\Services\Orientation;

use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Payment;
use App\Models\Orientation\Professional;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Workflow
{
    public function event(ConsultationRequest $c, string $actor, ?int $id, string $label, bool $public = true): void
    {
        DB::table('orientation_events')->insert(['consultation_request_id' => $c->id, 'actor_type' => $actor, 'actor_id' => $id, 'label' => $label, 'public' => $public, 'created_at' => now(), 'updated_at' => now()]);
    }

    public function response(ConsultationRequest $c): void
    {
        if (! $c->first_response_at) {
            $c->forceFill(['first_response_at' => now()])->save();
        }
    }

    public function authorized(ConsultationRequest $c): void
    {
        if (! in_array($c->authority_status, ['declared', 'approved'], true)) {
            throw ValidationException::withMessages(['authority' => 'L’autorisation du représentant légal doit être obtenue avant de poursuivre.']);
        }
        if (in_array($c->status, ['annulee', 'autorisation_refusee'], true)) {
            throw ValidationException::withMessages(['status' => 'Ce dossier est clôturé.']);
        }
    }

    public function overlap(Professional $p, Carbon $start, Carbon $end, ?int $except = null): void
    {
        $q = Appointment::where('professional_id', $p->id)->whereIn('status', Appointment::ACTIVE)->where('starts_at', '<', $end)->where('ends_at', '>', $start)
            ->where(fn ($q) => $q->where('status', 'confirmed')->orWhere('expires_at', '>', now()));
        if ($except) {
            $q->where('id', '!=', $except);
        }
        if ($q->exists()) {
            throw ValidationException::withMessages(['starts_at' => 'Ce professionnel a déjà un rendez-vous ou une réservation sur ce créneau.']);
        }
    }

    public function hasAnotherReservation(ConsultationRequest $c, int $except): bool
    {
        return Appointment::where('consultation_request_id', $c->id)->where('id', '!=', $except)->whereIn('status', Appointment::ACTIVE)
            ->where(fn ($q) => $q->where('status', 'confirmed')->orWhere('expires_at', '>', now()))->exists();
    }

    public function propose(ConsultationRequest $c, array $data, int $admin): Appointment
    {
        return DB::transaction(function () use ($c, $data, $admin) {
            $p = Professional::whereKey($data['professional_id'])->lockForUpdate()->firstOrFail();
            $c = ConsultationRequest::whereKey($c->id)->lockForUpdate()->firstOrFail();
            $this->authorized($c);
            if (! $p->active || ! $p->validated_at) {
                throw ValidationException::withMessages(['professional_id' => 'Le professionnel doit être actif et ses compétences validées par NLC.']);
            }
            if (Appointment::where('consultation_request_id', $c->id)->whereIn('status', Appointment::ACTIVE)->where(fn ($q) => $q->where('status', 'confirmed')->orWhere('expires_at', '>', now()))->exists()) {
                throw ValidationException::withMessages(['appointment' => 'Traitez ou annulez le rendez-vous en cours avant d’en proposer un autre.']);
            }
            $start = Carbon::parse($data['starts_at'], $data['timezone'])->utc();
            $end = $start->copy()->addMinutes((int) $data['duration_minutes']);
            $expires = Carbon::parse($data['expires_at'], $data['timezone'])->utc();
            if ($start->isPast() || $expires->isPast() || $expires->gte($start)) {
                throw ValidationException::withMessages(['expires_at' => 'L’échéance doit être future et précéder le rendez-vous.']);
            }
            $fee = (int) round((float) $data['fee_usd'] * 100);
            if ($fee > 0 && ! Settings::paymentAvailable()) {
                throw ValidationException::withMessages(['fee_usd' => 'Activez un paiement électronique dans la configuration avant de proposer une séance payante.']);
            }
            $this->overlap($p, $start, $end);
            $a = Appointment::create(['consultation_request_id' => $c->id, 'professional_id' => $p->id, 'starts_at' => $start, 'ends_at' => $end, 'duration_minutes' => $data['duration_minutes'],
                'expires_at' => $expires, 'mode' => $data['mode'], 'location' => $data['mode'] === 'centre' ? $data['location'] : null, 'meeting_url' => $data['mode'] === 'remote' ? $data['meeting_url'] : null,
                'fee_cents' => $fee, 'purpose' => $data['purpose'], 'kind' => $data['kind'], 'status' => 'awaiting_professional']);
            $c->update(['status' => 'attribution_en_attente']);
            $this->response($c);
            $this->event($c, 'admin', $admin, 'Un professionnel a été sollicité pour votre entretien');
            app(Notices::class)->professional($p, 'Disponibilité à confirmer', 'Une proposition pour le dossier '.$c->reference.' vous attend. Vérifiez votre agenda puis acceptez ou déclinez dans votre espace.', $a);
            app(Notices::class)->family($c, 'Votre demande est prise en charge', 'Le secrétariat prépare une proposition pour votre dossier '.$c->reference.'. Vous serez informé dès qu’un professionnel aura confirmé sa disponibilité.');

            return $a;
        });
    }

    public function professionalDecision(Appointment $a, Professional $p, bool $accept): void
    {
        DB::transaction(function () use ($a, $p, $accept) {
            Professional::whereKey($p->id)->lockForUpdate()->firstOrFail();
            $a = Appointment::whereKey($a->id)->lockForUpdate()->firstOrFail();
            abort_unless($a->professional_id === $p->id, 404);
            if ($a->status !== 'awaiting_professional' || $a->expires_at->isPast()) {
                throw ValidationException::withMessages(['appointment' => 'Cette sollicitation n’est plus disponible.']);
            }
            $c = ConsultationRequest::whereKey($a->consultation_request_id)->lockForUpdate()->firstOrFail();
            $this->authorized($c);
            $this->overlap($p, $a->starts_at, $a->ends_at, $a->id);
            $a->update(['status' => $accept ? 'proposed' : 'declined', 'professional_accepted_at' => $accept ? now() : null]);
            $c->update(['status' => $accept ? 'proposition_envoyee' : 'nouvelle']);
            $this->event($c, 'professional', $p->id, $accept ? 'Proposition de rendez-vous disponible' : 'Recherche d’un autre professionnel');
            if ($accept) {
                app(Notices::class)->family($c, 'Proposition de rendez-vous', 'Une proposition pour votre dossier '.$c->reference.' est disponible. Consultez la date, le professionnel, la durée et le tarif USD avant d’accepter.', $a);
            }
            app(Notices::class)->admin($accept ? 'Disponibilité confirmée' : 'Sollicitation déclinée', 'Le professionnel a répondu pour le dossier '.$c->reference.'.', $c);
        });
    }

    public function accept(Appointment $a, Contact $actor): void
    {
        DB::transaction(function () use ($a, $actor) {
            $p = Professional::whereKey($a->professional_id)->lockForUpdate()->firstOrFail();
            $a = Appointment::whereKey($a->id)->lockForUpdate()->firstOrFail();
            $c = ConsultationRequest::whereKey($a->consultation_request_id)->lockForUpdate()->firstOrFail();
            abort_unless(Access::fullFamily($actor, $c), 404);
            $this->authorized($c);
            if ($a->status !== 'proposed' || $a->expires_at->isPast() || ! $p->active || ! $p->validated_at) {
                throw ValidationException::withMessages(['appointment' => 'La proposition a expiré ou n’est plus disponible. Actualisez votre dossier.']);
            }
            $this->overlap($p, $a->starts_at, $a->ends_at, $a->id);
            $a->update(['accepted_at' => now(), 'accepted_by' => $actor->id, 'status' => $a->fee_cents > 0 ? 'awaiting_payment' : 'confirmed']);
            $this->event($c, 'family', $actor->id, 'Créneau et tarif acceptés : '.number_format($a->fee_cents / 100, 2, '.', '').' USD');
            if ($a->fee_cents === 0) {
                $this->confirm($a);
            } else {
                $c->update(['status' => 'paiement_en_attente']);
                app(Notices::class)->family($c, 'Paiement attendu', 'Votre proposition pour '.$c->reference.' a été acceptée. Le paiement électronique doit être vérifié avant confirmation définitive et avant l’échéance indiquée.', $a);
            }
        });
    }

    public function confirm(Appointment $a): void
    {
        if ($a->confirmed_at) {
            return;
        }
        $a->update(['status' => 'confirmed', 'confirmed_at' => now()]);
        $c = $a->consultation;
        $c->update(['status' => 'rdv_programme', 'professionnel_assigne' => $a->professional->name, 'seance_programmee_at' => $a->starts_at, 'duree_minutes' => $a->duration_minutes, 'lien_visio' => $a->meeting_url, 'prix' => $a->fee_cents / 100, 'devise' => 'USD', 'statut_paiement' => $a->fee_cents > 0 ? 'paye' : 'gratuit']);
        $this->event($c, 'system', null, 'Rendez-vous confirmé');
        $when = $a->starts_at->copy()->setTimezone($c->fuseau_horaire)->locale('fr')->translatedFormat('d/m/Y à H:i').' ('.$c->fuseau_horaire.')';
        $details = 'Dossier '.$c->reference."\n".$when.' · '.$a->duration_minutes.' minutes · '.$a->professional->name."\n".($a->mode === 'centre' ? 'Au centre : '.$a->location : 'À distance. Retrouvez le lien personnel de la séance dans votre espace sécurisé.');
        app(Notices::class)->family($c, 'Rendez-vous confirmé', $details, $a);
        app(Notices::class)->professional($a->professional, 'Rendez-vous confirmé', $details, $a);
        app(Notices::class)->admin('Rendez-vous confirmé', 'Le rendez-vous du dossier '.$c->reference.' est confirmé.', $c);
    }

    public function paid(Payment $payment, ?int $admin = null, ?string $transaction = null): void
    {
        DB::transaction(function () use ($payment, $admin, $transaction) {
            $original = $payment->appointment;
            Professional::whereKey($original->professional_id)->lockForUpdate()->firstOrFail();
            $a = Appointment::whereKey($original->id)->lockForUpdate()->firstOrFail();
            ConsultationRequest::whereKey($a->consultation_request_id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === 'paid') {
                return;
            }
            if ($payment->amount_cents !== $a->fee_cents || $payment->currency !== 'USD' || ! $a->accepted_at) {
                throw ValidationException::withMessages(['payment' => 'Le montant ne correspond pas à une proposition acceptée.']);
            }
            $alreadyPaid = Payment::where('appointment_id', $a->id)->where('status', 'paid')->exists();
            $late = $alreadyPaid || $a->status !== 'awaiting_payment' || $a->expires_at->isPast() || ! $a->professional->active || ! $a->professional->validated_at || ! in_array($a->consultation->authority_status, ['declared', 'approved'], true) || in_array($a->consultation->status, ['annulee', 'autorisation_refusee'], true);
            $payment->update(['status' => 'paid', 'paid_at' => now(), 'verified_by' => $admin, 'transaction_reference' => $transaction ?? $payment->transaction_reference]);
            $this->event($a->consultation, $admin ? 'admin' : 'system', $admin, 'Paiement vérifié');
            if ($late) {
                $a->update(['refund_status' => 'review']);
                app(Notices::class)->family($a->consultation, 'Paiement reçu, régularisation nécessaire', 'Un paiement a été vérifié pour '.$a->consultation->reference.', mais le créneau ne peut pas être confirmé automatiquement. Le secrétariat vous contactera pour un report ou une régularisation.');
                app(Notices::class)->admin('Paiement à régulariser', 'Un paiement tardif ou supplémentaire a été reçu pour '.$a->consultation->reference.'. Vérifiez le report ou le remboursement.', $a->consultation);

                return;
            }
            $this->authorized($a->consultation);
            $this->overlap($a->professional, $a->starts_at, $a->ends_at, $a->id);
            $this->confirm($a);
        });
    }

    public function cancel(Appointment $a, string $reason, string $actor, ?int $id): void
    {
        DB::transaction(function () use ($a, $reason, $actor, $id) {
            Professional::whereKey($a->professional_id)->lockForUpdate()->firstOrFail();
            $a = Appointment::whereKey($a->id)->lockForUpdate()->firstOrFail();
            if (! in_array($a->status, Appointment::ACTIVE, true)) {
                throw ValidationException::withMessages(['appointment' => 'Ce rendez-vous n’est plus modifiable.']);
            }
            $a->update(['status' => 'cancelled', 'cancellation_reason' => $reason, 'refund_status' => $a->payments()->where('status', 'paid')->exists() ? 'review' : 'none']);
            $c = ConsultationRequest::whereKey($a->consultation_request_id)->lockForUpdate()->firstOrFail();
            if (! $this->hasAnotherReservation($c, $a->id)) {
                $c->update(['status' => 'nouvelle']);
            }
            $this->event($c, $actor, $id, 'Rendez-vous annulé ; une nouvelle proposition peut être organisée');
            app(Notices::class)->family($c, 'Rendez-vous annulé', 'Le rendez-vous de votre dossier '.$c->reference.' a été annulé. Consultez votre espace pour le motif et la suite. Un paiement déjà reçu sera examiné par le secrétariat.');
            app(Notices::class)->professional($a->professional, 'Rendez-vous annulé', 'La réservation concernant '.$c->reference.' est annulée.');
            app(Notices::class)->admin('Rendez-vous annulé', 'Une réservation est annulée pour '.$c->reference.'.', $c);
        });
    }
}
