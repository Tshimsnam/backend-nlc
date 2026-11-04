<?php

namespace App\Services\Orientation;

use App\Jobs\SendOrientationMail;
use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Notice;
use App\Models\Orientation\Professional;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Notices
{
    public function send(?string $type, ?int $id, string $email, string $title, string $body, ?string $url = null, $expires = null, ?Appointment $appointment = null): Notice
    {
        $notice = Notice::create(['actor_type' => $type, 'actor_id' => $id, 'recipient' => $email, 'title' => $title, 'body' => $body, 'action_url' => $url, 'expires_at' => $expires, 'appointment_id' => $appointment?->id, 'expected_status' => $appointment?->status]);
        DB::afterCommit(function () use ($notice) {
            try {
                SendOrientationMail::dispatch($notice->id);
            } catch (\Throwable $e) {
                Log::error('Orientation: mise en file mail impossible', ['notice_id' => $notice->id]);
            }
        });

        return $notice;
    }

    public function family(ConsultationRequest $c, string $title, string $body, ?Appointment $appointment = null): void
    {
        // Le tiers non autorisé n'est jamais destinataire des informations du dossier.
        $ids = [$c->guardian_contact_id];
        if (in_array($c->authority_status, ['declared', 'approved'], true)) {
            $ids[] = $c->orientation_contact_id;
        }
        foreach (Contact::whereIn('id', array_unique(array_filter($ids)))->where('active', true)->get() as $contact) {
            $this->send('family', $contact->id, $contact->email, $title, $body, Settings::url('/espace-famille?dossier='.$c->id), null, $appointment);
        }
    }

    public function professional(Professional $p, string $title, string $body, ?Appointment $appointment = null): void
    {
        $this->send('professional', $p->id, $p->email, $title, $body, Settings::url('/espace-professionnel'), null, $appointment);
    }

    public function admin(string $title, string $body, ?ConsultationRequest $c = null): void
    {
        $this->send(null, null, Settings::get('secretariat_email'), $title, $body, $c ? route('admin.consultations.show', $c) : route('admin.consultations.index'));
    }
}
