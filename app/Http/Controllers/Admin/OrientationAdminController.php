<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Message;
use App\Models\Orientation\Notice;
use App\Models\Orientation\Payment;
use App\Models\Orientation\Professional;
use App\Services\Orientation\Access;
use App\Services\Orientation\Notices;
use App\Services\Orientation\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrientationAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = ConsultationRequest::query()->latest();
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');
        if (in_array($status, ConsultationRequest::STATUSES, true)) {
            $q->where('status', $status);
        }
        if ($request->query('late')) {
            $q->whereNull('first_response_at')->where('processing_due_at', '<=', now())->whereNotIn('status', ['annulee', 'autorisation_refusee']);
        }
        if ($search !== '') {
            $q->where(function ($q) use ($search) {
                foreach (['reference', 'parent_nom', 'parent_prenom', 'enfant_nom', 'enfant_prenom', 'email'] as $column) {
                    $q->orWhere($column, 'like', '%'.$search.'%');
                }
            });
        }

        return view('admin.consultations.index', ['user' => session('admin_user'), 'consultations' => $q->paginate(20)->withQueryString(), 'status' => $status, 'search' => $search,
            'counts' => ConsultationRequest::selectRaw('status,count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'lateCount' => ConsultationRequest::whereNull('first_response_at')->where('processing_due_at', '<=', now())->whereNotIn('status', ['annulee', 'autorisation_refusee'])->count()]);
    }

    public function show(ConsultationRequest $consultation)
    {
        return view('admin.consultations.show', ['user' => session('admin_user'), 'c' => $consultation,
            'appointments' => Appointment::where('consultation_request_id', $consultation->id)->with(['professional', 'payments'])->latest('starts_at')->get(),
            'professionals' => Professional::where('active', true)->whereNotNull('validated_at')->orderBy('name')->get(),
            'messages' => Message::where('consultation_request_id', $consultation->id)->oldest()->get(),
            'events' => DB::table('orientation_events')->where('consultation_request_id', $consultation->id)->latest('id')->get(),
            'mailIssues' => Notice::whereIn('email_status', ['pending', 'failed'])->where('body', 'like', '%'.$consultation->reference.'%')->latest()->limit(20)->get()]);
    }

    public function update(Request $request, ConsultationRequest $consultation, Workflow $workflow)
    {
        $admin = $request->attributes->get('orientation_admin');
        $request->validate(['action' => 'required|in:propose,information,message,notes,cancel,payment_verify,payment_reject,refund,close,adopt,retry_mail,no_show,guardian_contact']);
        $action = $request->action;
        if ($action === 'propose') {
            $data = $request->validate(['professional_id' => 'required|exists:orientation_professionals,id', 'starts_at' => 'required|date', 'expires_at' => 'required|date', 'timezone' => 'required|timezone:all',
                'duration_minutes' => 'required|integer|min:10|max:240', 'fee_usd' => 'required|numeric|min:0|max:100000|decimal:0,2', 'mode' => 'required|in:remote,centre',
                'location' => 'nullable|required_if:mode,centre|string|max:1000', 'meeting_url' => 'nullable|required_if:mode,remote|url:https|max:1500', 'purpose' => 'required|string|min:10|max:2000', 'kind' => 'required|in:orientation,evaluation,follow_up']);
            $workflow->propose($consultation, $data, $admin->id);

            return back()->with('success', 'Proposition préparée. Le professionnel doit maintenant confirmer sa disponibilité.');
        }
        if (in_array($action, ['cancel', 'no_show', 'refund'], true)) {
            $data = $request->validate(['appointment_id' => 'required|integer', 'reason' => 'required|string|min:5|max:2000']);
            $a = Appointment::where('consultation_request_id', $consultation->id)->findOrFail($data['appointment_id']);
            if ($action === 'cancel') {
                $workflow->cancel($a, $data['reason'], 'admin', $admin->id);
            } elseif ($action === 'no_show') {
                DB::transaction(function () use ($a, $data, $workflow, $admin) {
                    $a = Appointment::whereKey($a->id)->lockForUpdate()->firstOrFail();
                    if ($a->status !== 'confirmed' || $a->starts_at->isFuture()) {
                        throw ValidationException::withMessages(['appointment' => 'Seule une séance confirmée déjà commencée peut être marquée en absence.']);
                    }
                    $a->update(['status' => 'no_show', 'cancellation_reason' => $data['reason']]);
                    $a->consultation->update(['status' => 'nouvelle']);
                    $workflow->event($a->consultation, 'admin', $admin->id, 'Absence au rendez-vous ; suivi à organiser');
                    app(Notices::class)->family($a->consultation, 'Suivi de votre rendez-vous', 'Votre présence n’a pas été constatée pour le rendez-vous du dossier '.$a->consultation->reference.'. Contactez le secrétariat pour la suite.');
                });
            } else {
                $request->validate(['refund_status' => 'required|in:review,refunded,credited,not_required', 'verified' => 'accepted']);
                if (! $a->payments()->where('status', 'paid')->exists()) {
                    throw ValidationException::withMessages(['refund' => 'Aucun paiement reçu pour ce rendez-vous.']);
                }
                $a->update(['refund_status' => $request->refund_status, 'refund_note' => $data['reason']]);
                $workflow->event($consultation, 'admin', $admin->id, 'Régularisation financière enregistrée', false);
                app(Notices::class)->family($consultation, 'Régularisation mise à jour', 'Le secrétariat a mis à jour la régularisation du dossier '.$consultation->reference.'. Contactez-le pour le justificatif et les modalités.');
            }

            return back()->with('success', 'Rendez-vous mis à jour.');
        }
        if (in_array($action, ['payment_verify', 'payment_reject'], true)) {
            $data = $request->validate(['payment_id' => 'required|integer', 'transaction_reference' => 'required|string|min:5|max:150', 'review_note' => 'required|string|min:10|max:2000', 'verified' => 'accepted']);
            $p = Payment::whereHas('appointment', fn ($q) => $q->where('consultation_request_id', $consultation->id))->findOrFail($data['payment_id']);
            DB::transaction(function () use ($p, $data, $action, $workflow, $admin) {
                Professional::whereKey($p->appointment->professional_id)->lockForUpdate()->firstOrFail();
                Appointment::whereKey($p->appointment_id)->lockForUpdate()->firstOrFail();
                ConsultationRequest::whereKey($p->appointment->consultation_request_id)->lockForUpdate()->firstOrFail();
                $p = Payment::whereKey($p->id)->lockForUpdate()->firstOrFail();
                if ($p->status === 'paid') {
                    throw ValidationException::withMessages(['payment' => 'Ce paiement a déjà été vérifié.']);
                }
                if (Payment::where('transaction_reference', $data['transaction_reference'])->where('id', '!=', $p->id)->exists()) {
                    throw ValidationException::withMessages(['transaction_reference' => 'Cette référence est déjà utilisée.']);
                }
                $p->update(['review_note' => $data['review_note']]);
                if ($action === 'payment_verify') {
                    $workflow->paid($p, $admin->id, $data['transaction_reference']);
                } else {
                    $p->update(['status' => 'failed', 'verified_by' => $admin->id]);
                    $workflow->event($p->appointment->consultation, 'admin', $admin->id, 'Paiement non confirmé ; nouvelle tentative possible');
                    app(Notices::class)->family($p->appointment->consultation, 'Paiement à reprendre', 'Le paiement du dossier '.$p->appointment->consultation->reference.' n’a pas pu être confirmé. Consultez votre espace ou contactez le secrétariat.');
                }
            });

            return back()->with('success', 'Vérification financière enregistrée.');
        }
        if ($action === 'retry_mail') {
            $request->validate(['notice_id' => 'required|integer']);
            $notice = Notice::where('body', 'like', '%'.$consultation->reference.'%')->findOrFail($request->notice_id);
            if ($notice->expires_at?->isPast()) {
                throw ValidationException::withMessages(['mail' => 'Ce lien est expiré ; envoyez une nouvelle invitation.']);
            }
            \App\Jobs\SendOrientationMail::dispatch($notice->id);

            return back()->with('success', 'Notification remise en file.');
        }
        DB::transaction(function () use ($request, $consultation, $workflow, $admin, $action) {
            $c = ConsultationRequest::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            if ($action === 'guardian_contact') {
                if ($c->authority_status !== 'pending') {
                    throw ValidationException::withMessages(['guardian' => 'Les coordonnées ne peuvent être corrigées ici qu’avant l’autorisation.']);
                }
                $request->merge(['guardian_email' => strtolower(trim((string) $request->guardian_email))]);
                $data = $request->validate(['guardian_name' => 'required|string|max:255', 'guardian_email' => ['required', 'email', 'max:255', Rule::notIn([$c->email])], 'verified' => 'accepted']);
                $guardian = Contact::firstOrCreate(['email' => $data['guardian_email']], ['name' => $data['guardian_name']]);
                $c->forceFill(['guardian_name' => $data['guardian_name'], 'guardian_email' => $guardian->email, 'guardian_contact_id' => $guardian->id])->save();
                app(Access::class)->issue('family', $guardian, 'guardian', $c);
                $workflow->event($c, 'admin', $admin->id, 'Coordonnées du représentant légal vérifiées ; autorisation sollicitée');

                return;
            }
            if ($action === 'adopt') {
                $request->validate(['verified' => 'accepted']);
                if ($c->orientation_contact_id) {
                    throw ValidationException::withMessages(['dossier' => 'Ce dossier dispose déjà d’un accès.']);
                }
                if ($c->enfant_date_naissance->lte(now()->subYears(18)->startOfDay())) {
                    throw ValidationException::withMessages(['dossier' => 'Ce parcours est réservé aux mineurs. Une orientation distincte est nécessaire pour ce dossier.']);
                }
                $contact = Contact::firstOrCreate(['email' => strtolower($c->email)], ['name' => $c->parent_nom_complet]);
                $c->forceFill(['orientation_contact_id' => $contact->id, 'guardian_contact_id' => $contact->id, 'guardian_name' => $c->parent_nom_complet, 'guardian_email' => $c->email, 'authority_status' => 'declared', 'guardian_approved_at' => now(), 'processing_due_at' => now()->addHours(72)])->save();
                $workflow->event($c, 'admin', $admin->id, 'Suivi sécurisé activé après vérification du responsable légal');
                app(Access::class)->issue('family', $contact);

                return;
            }
            if ($action === 'notes') {
                $data = $request->validate(['notes_internes' => 'nullable|string|max:10000']);
                $c->update($data);
                $workflow->event($c, 'admin', $admin->id, 'Note administrative mise à jour', false);

                return;
            }
            if ($action === 'close') {
                $request->validate(['body' => 'required|string|min:10|max:3000']);
                if (Appointment::where('consultation_request_id', $c->id)->whereIn('status', Appointment::ACTIVE)->exists()) {
                    throw ValidationException::withMessages(['appointment' => 'Annulez d’abord les rendez-vous actifs.']);
                }
                $c->update(['status' => 'annulee']);
                $workflow->response($c);
                $workflow->event($c, 'admin', $admin->id, 'Dossier clôturé');
            } else {
                $workflow->authorized($c);
                $request->validate(['body' => 'required|string|min:10|max:4000']);
            }
            if ($action === 'information') {
                if (Appointment::where('consultation_request_id', $c->id)->whereIn('status', Appointment::ACTIVE)->exists()) {
                    throw ValidationException::withMessages(['appointment' => 'Un rendez-vous est en cours. Utilisez la messagerie ou annulez la proposition avant de rouvrir le dossier.']);
                }
                $c->forceFill(['status' => 'dossier_incomplet', 'information_requested' => $request->body, 'information_requested_at' => now()])->save();
                $workflow->event($c, 'admin', $admin->id, 'Compléments demandés par le secrétariat');
            }
            $workflow->response($c);
            Message::create(['consultation_request_id' => $c->id, 'actor_type' => 'admin', 'actor_id' => $admin->id, 'author' => 'Secrétariat NLC', 'body' => $request->body]);
            app(Notices::class)->family($c, $action === 'information' ? 'Compléments nécessaires' : 'Message du secrétariat', 'Une mise à jour vous attend dans le dossier '.$c->reference.'. Consultez votre espace sécurisé.');
        });

        return back()->with('success', 'Dossier mis à jour.');
    }

    public function document(Request $request, ConsultationRequest $consultation, int $index)
    {
        $d = $consultation->documents[$index] ?? null;
        abort_unless($d && Storage::disk('local')->exists($d['path']), 404);
        app(Workflow::class)->event($consultation, 'admin', $request->attributes->get('orientation_admin')->id, 'Téléchargement administratif d’un document', false);

        return Storage::disk('local')->download($d['path'], $d['name'], ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function professionals()
    {
        return view('admin.consultations.professionals', ['user' => session('admin_user'), 'professionals' => Professional::orderBy('name')->get()]);
    }

    public function saveProfessional(Request $request, ?Professional $professional = null)
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);
        $data = $request->validate(['name' => 'required|string|max:150', 'email' => ['required', 'email', 'max:255', Rule::unique('orientation_professionals', 'email')->ignore($professional?->id)], 'profession' => 'required|string|max:150', 'specialty' => 'nullable|string|max:255', 'engagement' => 'required|in:internal,consultant', 'languages' => 'nullable|string|max:255', 'timezone' => 'required|timezone:all', 'default_fee_usd' => 'required|numeric|min:0|max:100000|decimal:0,2', 'credential_notes' => 'nullable|string|max:3000', 'validated' => 'nullable|boolean', 'active' => 'nullable|boolean']);
        $data['languages'] = array_values(array_filter(array_map('trim', explode(',', $data['languages'] ?? ''))));
        $data['default_fee_cents'] = (int) round((float) $data['default_fee_usd'] * 100);
        unset($data['default_fee_usd']);
        $validated = $request->boolean('validated');
        unset($data['validated']);
        $data['active'] = $request->boolean('active');
        if ($data['active'] && ! $validated) {
            throw ValidationException::withMessages(['validated' => 'Validez les compétences et le cadre d’intervention avant d’activer le compte.']);
        }
        $data['validated_at'] = $validated ? ($professional?->validated_at ?? now()) : null;
        $data['validated_by'] = $validated ? $request->attributes->get('orientation_admin')->id : null;
        DB::transaction(function () use ($professional, $data, $request) {
            if ($professional && ! $data['active'] && Appointment::where('professional_id', $professional->id)->whereIn('status', Appointment::ACTIVE)->exists()) {
                throw ValidationException::withMessages(['active' => 'Réaffectez ou annulez les rendez-vous actifs avant de désactiver ce compte.']);
            }
            $p = $professional ?? new Professional;
            $p->fill($data)->save();
            if ($request->boolean('invite') && $p->active) {
                app(Access::class)->issue('professional', $p);
            }
        });

        return back()->with('success', 'Professionnel enregistré.');
    }

    public function settings()
    {
        return view('admin.consultations.settings', ['user' => session('admin_user')]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate(['secretariat_email' => 'required|email|max:255', 'centre_address' => 'nullable|string|max:1000', 'default_duration_minutes' => 'required|integer|min:10|max:240', 'manual_payment_label' => 'required|string|max:150', 'manual_payment_instructions' => 'nullable|string|max:3000']);
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                DB::table('orientation_settings')->updateOrInsert(['key' => $key], ['value' => $value]);
            }
        });

        return back()->with('success', 'Configuration enregistrée.');
    }
}
