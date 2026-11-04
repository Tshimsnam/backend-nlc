<?php

namespace App\Http\Controllers\Orientation;

use App\Http\Controllers\Controller;
use App\Jobs\VerifyOrientationPayment;
use App\Models\ConsultationRequest;
use App\Models\Orientation\Appointment;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Draft;
use App\Models\Orientation\Message;
use App\Models\Orientation\Notice;
use App\Models\Orientation\Payment;
use App\Models\Orientation\Professional;
use App\Services\Orientation\Access;
use App\Services\Orientation\Intake;
use App\Services\Orientation\Notices;
use App\Services\Orientation\Payments;
use App\Services\Orientation\Presenter;
use App\Services\Orientation\Settings;
use App\Services\Orientation\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PortalController extends Controller
{
    public function config()
    {
        return response()->json(['processing_hours' => config('orientation.processing_hours'), 'secretariat_email' => Settings::get('secretariat_email'), 'centre_address' => Settings::get('centre_address'),
            'payment_methods' => array_values(array_filter([config('orientation.maxicash_enabled') ? ['id' => 'maxicash', 'label' => 'Paiement en ligne MaxiCash'] : null,
                filled(Settings::get('manual_payment_instructions')) ? ['id' => 'manual', 'label' => Settings::get('manual_payment_label')] : null])),
            'clinical_sections' => config('orientation.clinical_sections'), 'csrf_token' => csrf_token()])->header('Cache-Control', 'no-store');
    }

    public function requestAccess(Request $request, Access $access)
    {
        $data = $request->validate(['email' => 'required|email|max:255', 'name' => 'nullable|string|max:150', 'type' => 'required|in:family,professional']);
        $email = strtolower(trim($data['email']));
        DB::transaction(function () use ($data, $email, $access) {
            $actor = $data['type'] === 'professional' ? Professional::where('email', $email)->where('active', true)->whereNotNull('validated_at')->first()
                : Contact::firstOrCreate(['email' => $email], ['name' => ($data['name'] ?? '') ?: 'Famille NLC']);
            if ($actor && $actor->active) {
                $access->issue($data['type'], $actor);
            }
        });

        return response()->json(['message' => 'Si cette adresse peut accéder au service, un lien personnel vient de lui être envoyé. Il expire après 30 minutes.']);
    }

    public function consume(Request $request, Access $access)
    {
        $data = $request->validate(['token' => 'required|string|size:64']);

        return response()->json($access->consume($request, $data['token']));
    }

    public function me(Request $request)
    {
        [$type,$actor] = Access::actor($request);

        return response()->json(['actor' => ['type' => $type, 'id' => $actor->id, 'name' => $actor->name, 'email' => $actor->email, 'timezone' => $actor instanceof Professional ? $actor->timezone : null], 'csrf_token' => csrf_token()]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('orientation_actor');
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion effectuée.', 'csrf_token' => csrf_token()]);
    }

    public function draft(Request $request)
    {
        $actor = Access::family($request);

        return response()->json(['data' => Draft::where('contact_id', $actor->id)->first()?->data ?? []]);
    }

    public function saveDraft(Request $request)
    {
        $actor = Access::family($request);
        $request->validate(['data' => 'required|array']);
        // Un brouillon conserve les saisies incomplètes ; seules les clés du formulaire sont admises.
        $data = collect($request->data)->only(array_keys(Intake::rules()))->all();
        if (strlen(json_encode($data)) > 60000) {
            throw ValidationException::withMessages(['data' => 'Le brouillon est trop volumineux.']);
        }
        Draft::updateOrCreate(['contact_id' => $actor->id], ['data' => $data]);

        return response()->json(['message' => 'Brouillon enregistré dans votre espace sécurisé.']);
    }

    public function store(Request $request, Intake $intake, Presenter $presenter)
    {
        $actor = Access::family($request);
        $c = $intake->submit($request, $actor);

        return response()->json($presenter->dossier($c, 'family', $actor), 201);
    }

    public function index(Request $request, Presenter $presenter)
    {
        [$type,$actor] = Access::actor($request);
        $q = ConsultationRequest::query();
        if ($type === 'family') {
            $q->where(fn ($q) => $q->where('orientation_contact_id', $actor->id)->orWhere('guardian_contact_id', $actor->id));
        } else {
            $q->whereIn('id', Appointment::where('professional_id', $actor->id)->whereIn('status', array_merge(Appointment::ACTIVE, ['completed', 'no_show']))->select('consultation_request_id'));
        }

        return response()->json($q->latest()->limit(100)->get()->map(fn ($c) => $presenter->dossier($c, $type, $actor)));
    }

    private function permission(Request $request, ConsultationRequest $c, bool $documents = false): array
    {
        [$type,$actor] = Access::actor($request);
        if ($type === 'family') {
            Access::requireFamily($request, $c, $documents);
        } else {
            abort_unless(in_array($c->authority_status, ['declared', 'approved'], true), 404);
            $q = Appointment::where('consultation_request_id', $c->id)->where('professional_id', $actor->id)->whereIn('status', array_merge(Appointment::ACTIVE, ['completed', 'no_show']));
            if ($documents) {
                $q->whereNotNull('professional_accepted_at');
            }
            abort_unless($q->exists(), 404);
        }

        return [$type, $actor];
    }

    public function show(Request $request, ConsultationRequest $consultation, Presenter $presenter)
    {
        [$type,$actor] = $this->permission($request, $consultation);

        return response()->json($presenter->dossier($consultation, $type, $actor));
    }

    public function guardian(Request $request, ConsultationRequest $consultation, Workflow $workflow)
    {
        $actor = Access::family($request);
        abort_unless((int) $consultation->guardian_contact_id === $actor->id, 404);
        $data = $request->validate(['approve' => 'required|boolean', 'authority_consent' => 'accepted']);
        DB::transaction(function () use ($consultation, $data, $workflow, $actor) {
            $c = ConsultationRequest::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            if ($c->authority_status !== 'pending' || $c->status === 'annulee') {
                throw ValidationException::withMessages(['authority' => 'Cette autorisation a déjà été traitée.']);
            }
            $c->forceFill(['authority_status' => $data['approve'] ? 'approved' : 'refused', 'guardian_approved_at' => $data['approve'] ? now() : null, 'status' => $data['approve'] ? 'nouvelle' : 'autorisation_refusee'])->save();
            $workflow->event($c, 'family', $actor->id, $data['approve'] ? 'Démarche autorisée par le représentant légal' : 'Démarche refusée par le représentant légal');
            app(Notices::class)->admin('Autorisation traitée', 'Le représentant légal a répondu pour '.$c->reference.'.', $c);
        });

        return response()->json(['message' => 'Votre décision a été enregistrée.']);
    }

    public function resendGuardian(Request $request, ConsultationRequest $consultation, Access $access)
    {
        Access::requireFamily($request, $consultation, false);
        abort_unless($consultation->authority_status === 'pending', 422);
        $access->issue('family', Contact::findOrFail($consultation->guardian_contact_id), 'guardian', $consultation);

        return response()->json(['message' => 'Un nouveau lien a été envoyé au représentant légal.']);
    }

    public function supplement(Request $request, ConsultationRequest $consultation, Intake $intake, Workflow $workflow)
    {
        $actor = Access::requireFamily($request, $consultation);
        $request->validate(['data' => 'required|array', 'response' => 'required|string|min:10|max:3000']);
        DB::transaction(function () use ($request, $consultation, $workflow, $actor) {
            $c = ConsultationRequest::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
            if ($c->status !== 'dossier_incomplet') {
                throw ValidationException::withMessages(['status' => 'Le dossier n’attend pas de compléments.']);
            }
            $allowed = ['motif', 'attentes', 'traitement_actuel', 'naissance_particuliere', 'langues_maison', 'classe', 'suivis', 'niveau_langage'];
            $fields = collect($request->data)->only($allowed)->all();
            $rules = collect(Intake::rules(false))->only($allowed)->all();
            $fields = Validator::make($fields, $rules)->validate();
            $c->fill($fields)->forceFill(['status' => 'nouvelle', 'information_requested' => null])->save();
            Message::create(['consultation_request_id' => $c->id, 'actor_type' => 'family', 'actor_id' => $actor->id, 'author' => $actor->name, 'body' => $request->response]);
            $workflow->event($c, 'family', $actor->id, 'Compléments transmis au secrétariat');
            app(Notices::class)->admin('Compléments reçus', 'Des compléments sont disponibles pour '.$c->reference.'.', $c);
        });

        return response()->json(['message' => 'Vos compléments ont été transmis.']);
    }

    public function upload(Request $request, ConsultationRequest $consultation, Intake $intake, Workflow $workflow)
    {
        $actor = Access::requireFamily($request, $consultation);
        $request->validate(['file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png']);
        $paths = [];
        try {
            DB::transaction(function () use ($request, $consultation, $intake, $workflow, $actor, &$paths) {
                $c = ConsultationRequest::whereKey($consultation->id)->lockForUpdate()->firstOrFail();
                $workflow->authorized($c);
                $intake->appendFiles($c, [$request->file('file')], $paths);
                $workflow->event($c, 'family', $actor->id, 'Document ajouté au dossier');
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }

        return response()->json(['message' => 'Document ajouté.'], 201);
    }

    public function document(Request $request, ConsultationRequest $consultation, int $index)
    {
        [$type,$actor] = $this->permission($request, $consultation, true);
        $d = $consultation->documents[$index] ?? null;
        abort_unless($d && Storage::disk('local')->exists($d['path']), 404);
        app(Workflow::class)->event($consultation, $type, $actor->id, 'Téléchargement d’un document', false);

        return Storage::disk('local')->download($d['path'], $d['name'], ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }

    public function message(Request $request, ConsultationRequest $consultation, Workflow $workflow)
    {
        [$type,$actor] = $this->permission($request, $consultation, true);
        $data = $request->validate(['body' => 'required|string|min:2|max:4000']);
        $workflow->authorized($consultation);
        Message::create(['consultation_request_id' => $consultation->id, 'actor_type' => $type, 'actor_id' => $actor->id, 'author' => $actor->name, 'body' => $data['body']]);
        app(Notices::class)->admin('Nouveau message', 'Un nouveau message est disponible dans le dossier '.$consultation->reference.'.', $consultation);
        if ($type === 'professional') {
            app(Notices::class)->family($consultation, 'Nouveau message', 'Un professionnel vous a écrit dans le dossier '.$consultation->reference.'.');
        }

        return response()->json(['message' => 'Message transmis.'], 201);
    }

    public function appointment(Request $request, Appointment $appointment, Workflow $workflow)
    {
        [$type,$actor] = Access::actor($request);
        $data = $request->validate(['action' => 'required|in:accept,decline,cancel,complete,notes', 'consent' => 'nullable|boolean', 'reason' => 'nullable|string|min:5|max:2000', 'clinical_notes' => 'nullable|array', 'shared_summary' => 'nullable|string|max:10000']);
        if ($type === 'professional') {
            abort_unless($appointment->professional_id === $actor->id, 404);
            if (in_array($data['action'], ['accept', 'decline'], true)) {
                $workflow->professionalDecision($appointment, $actor, $data['action'] === 'accept');
            } elseif ($data['action'] === 'cancel') {
                $workflow->cancel($appointment, $data['reason'] ?? 'Annulation par le professionnel.', $type, $actor->id);
            } else {
                $notes = Validator::make($request->all(), ['clinical_notes' => 'required|array:'.implode(',', array_keys(config('orientation.clinical_sections'))), 'clinical_notes.*' => 'nullable|string|max:6000', 'shared_summary' => 'nullable|string|max:10000'])->validate();
                DB::transaction(function () use ($appointment, $notes, $data, $workflow, $actor) {
                    $a = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
                    $workflow->authorized($a->consultation);
                    if (! in_array($a->status, ['confirmed', 'completed'], true)) {
                        throw ValidationException::withMessages(['appointment' => 'Les notes ne peuvent être enregistrées que pour une séance confirmée.']);
                    }
                    if ($data['action'] === 'complete' && $a->starts_at->isFuture()) {
                        throw ValidationException::withMessages(['appointment' => 'Le rendez-vous n’a pas encore commencé.']);
                    }
                    if ($data['action'] === 'notes') {
                        unset($notes['shared_summary']);
                    }
                    if ($data['action'] === 'complete') {
                        $requestSummary = $notes['shared_summary'] ?? '';
                        if (mb_strlen(trim($requestSummary)) < 10) {
                            throw ValidationException::withMessages(['shared_summary' => 'Une synthèse destinée à la famille est nécessaire pour terminer l’entretien.']);
                        }
                    }
                    $a->update($notes);
                    $workflow->event($a->consultation, 'professional', $actor->id, 'Notes de travail enregistrées', false);
                    if ($data['action'] === 'complete' && ! $a->completed_at) {
                        $a->update(['status' => 'completed', 'completed_at' => now()]);
                        $a->consultation->update(['status' => 'seance_faite']);
                        $workflow->event($a->consultation, 'professional', $actor->id, 'Entretien réalisé ; prochaines étapes disponibles');
                        app(Notices::class)->family($a->consultation, 'Suite de votre entretien', 'Le professionnel a mis à jour la suite de votre dossier '.$a->consultation->reference.'. Consultez votre espace.');
                    }
                });
            }
        } else {
            Access::requireFamily($request, $appointment->consultation);
            if ($data['action'] === 'accept') {
                $request->validate(['consent' => 'accepted']);
                $workflow->accept($appointment, $actor);
            } elseif (in_array($data['action'], ['cancel', 'decline'], true)) {
                $workflow->cancel($appointment, $data['reason'] ?? 'Proposition déclinée par la famille.', $type, $actor->id);
            } else {
                abort(403);
            }
        }

        return response()->json(['message' => 'Votre action a été enregistrée.']);
    }

    public function pay(Request $request, Appointment $appointment, Payments $payments)
    {
        Access::requireFamily($request, $appointment->consultation);
        $data = $request->validate(['gateway' => 'required|in:maxicash,manual']);

        return response()->json($payments->start($appointment, $data['gateway']));
    }

    public function reportPayment(Request $request, Payment $payment)
    {
        $actor = Access::requireFamily($request, $payment->appointment->consultation);
        $data = $request->validate(['transaction_reference' => 'required|string|min:5|max:150']);
        DB::transaction(function () use ($payment, $data) {
            $p = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($p->status === 'paid') {
                return;
            }
            if ($p->gateway !== 'manual') {
                throw ValidationException::withMessages(['payment' => 'Ce paiement doit être vérifié auprès du prestataire en ligne.']);
            }
            if (Payment::where('transaction_reference', $data['transaction_reference'])->where('id', '!=', $p->id)->exists()) {
                throw ValidationException::withMessages(['transaction_reference' => 'Cette référence a déjà été déclarée.']);
            }
            $p->update($data + ['status' => 'verification_required']);
            app(Notices::class)->admin('Paiement à vérifier', 'Une référence de transaction a été déclarée pour '.$p->appointment->consultation->reference.'. Vérifiez sa réception sur le compte marchand avant confirmation.', $p->appointment->consultation);
        });

        return response()->json(['message' => 'Référence transmise. La confirmation interviendra après vérification du paiement.']);
    }

    public function checkPayment(Request $request, Payment $payment, Payments $payments)
    {
        Access::requireFamily($request, $payment->appointment->consultation);
        $payments->verify($payment);

        return response()->json(['message' => 'Vérification effectuée. Actualisez le dossier.']);
    }

    public function callback(Request $request)
    {
        $reference = $request->input('Reference', $request->input('reference'));
        if (is_string($reference) && preg_match('/^ORP-[A-Z0-9]{20}$/', $reference)) {
            $p = Payment::where('reference', $reference)->where('gateway', 'maxicash')->where('status', '!=', 'paid')->first();
            if ($p) {
                VerifyOrientationPayment::dispatch($p->id);
            }
        }

        return response()->json(['received' => true]); // Aucun statut de paiement fourni par l'appelant n'est accepté.
    }

    public function notifications(Request $request)
    {
        [$type,$actor] = Access::actor($request);

        return response()->json(Notice::where(['actor_type' => $type, 'actor_id' => $actor->id])->latest()->limit(60)->get(['id', 'title', 'body', 'email_status', 'read_at', 'created_at']));
    }

    public function read(Request $request, Notice $notice)
    {
        [$type,$actor] = Access::actor($request);
        abort_unless($notice->actor_type === $type && $notice->actor_id === $actor->id, 404);
        $notice->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification lue.']);
    }
}
