<?php

namespace App\Services\Orientation;

use App\Jobs\SendOrientationMail;
use App\Jobs\VerifyOrientationPayment;
use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Notice;
use App\Models\Orientation\Payment;
use App\Models\Orientation\Professional;
use Illuminate\Support\Facades\DB;

class Maintenance
{
    public function run(): void
    {
        $workflow = app(Workflow::class);
        $notices = app(Notices::class);
        Appointment::whereIn('status', ['awaiting_professional', 'proposed', 'awaiting_payment'])->where('expires_at', '<=', now())->limit(200)->get()->each(function ($original) use ($workflow, $notices) {
            DB::transaction(function () use ($original, $workflow, $notices) {
                Professional::whereKey($original->professional_id)->lockForUpdate()->firstOrFail();
                $a = Appointment::whereKey($original->id)->lockForUpdate()->firstOrFail();
                if (! in_array($a->status, ['awaiting_professional', 'proposed', 'awaiting_payment'], true) || $a->expires_at->isFuture()) {
                    return;
                }
                $a->update(['status' => 'expired']);
                $c = ConsultationRequest::whereKey($a->consultation_request_id)->lockForUpdate()->firstOrFail();
                if (! $workflow->hasAnotherReservation($c, $a->id)) {
                    $c->update(['status' => 'nouvelle']);
                }
                $workflow->event($a->consultation, 'system', null, 'Proposition expirée ; un nouveau créneau peut être organisé');
                $notices->family($a->consultation, 'Proposition expirée', 'La proposition du dossier '.$a->consultation->reference.' a expiré. Contactez le secrétariat pour un nouveau créneau. Si un paiement a déjà été effectué, signalez sa référence pour vérification.');
                $notices->professional($a->professional, 'Réservation libérée', 'La réservation du dossier '.$a->consultation->reference.' a expiré.');
            });
        });
        ConsultationRequest::whereNull('first_response_at')->whereNull('sla_alerted_at')->where('processing_due_at', '<=', now())->whereNotIn('status', ['annulee', 'autorisation_refusee'])->limit(200)->get()->each(function ($original) use ($notices, $workflow) {
            DB::transaction(function () use ($original, $notices, $workflow) {
                $c = ConsultationRequest::whereKey($original->id)->lockForUpdate()->firstOrFail();
                if ($c->first_response_at || $c->sla_alerted_at) {
                    return;
                }
                $notices->admin('Délai de 72 heures dépassé', 'Le dossier '.$c->reference.' attend une première réponse utile depuis plus de 72 heures.', $c);
                $c->forceFill(['sla_alerted_at' => now()])->save();
                $workflow->event($c, 'system', null, 'Alerte de délai adressée au secrétariat', false);
            });
        });
        Appointment::where('status', 'confirmed')->whereNull('reminded_at')->where('starts_at', '>', now())->where('starts_at', '<=', now()->addDay())->limit(200)->get()->each(function ($original) use ($notices) {
            DB::transaction(function () use ($original, $notices) {
                $a = Appointment::whereKey($original->id)->lockForUpdate()->firstOrFail();
                if ($a->status !== 'confirmed' || $a->reminded_at) {
                    return;
                }
                $notices->family($a->consultation, 'Rappel de votre rendez-vous', 'Un rendez-vous du dossier '.$a->consultation->reference.' est prévu dans les prochaines 24 heures. Retrouvez les horaires et les consignes dans votre espace.');
                $notices->professional($a->professional, 'Rappel de rendez-vous', 'Un rendez-vous du dossier '.$a->consultation->reference.' est prévu dans les prochaines 24 heures.');
                $a->update(['reminded_at' => now()]);
            });
        });
        Notice::whereIn('email_status', ['pending', 'failed'])->where('updated_at', '<', now()->subHour())->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->limit(100)->get()->each(fn ($n) => SendOrientationMail::dispatch($n->id));
        Payment::where('gateway', 'maxicash')->whereIn('status', ['pending', 'verification_required'])->where('updated_at', '<', now()->subMinutes(15))->where('created_at', '>', now()->subDays(7))->limit(50)->get()->each(fn ($p) => VerifyOrientationPayment::dispatch($p->id));
    }
}
