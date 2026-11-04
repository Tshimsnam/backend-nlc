<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consortium\Application;
use App\Models\Consortium\Document;
use App\Services\Consortium\Fields;
use App\Services\Consortium\Notifications;
use App\Services\Consortium\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ConsortiumController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with('structure')->where('status', '!=', 'draft')->latest('updated_at');
        if ($request->filled('status') && array_key_exists($request->status, Application::STATUSES)) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = mb_substr($request->search, 0, 100);
            $query->where(fn ($q) => $q->where('reference', 'like', "%$search%")->orWhereHas('structure', fn ($s) => $s->where('name', 'like', "%$search%")));
        }
        if ($request->filled('province')) {
            $query->whereJsonContains('data->provinces', $request->province);
        }
        if ($request->filled('structure_type')) {
            $query->where('data->structure_type', $request->structure_type);
        }
        if ($request->filled('expertise')) {
            $query->whereJsonContains('data->expertise', $request->expertise);
        }

        return view('admin.consortium.index', [
            'applications' => $query->paginate(20)->withQueryString(), 'statuses' => array_diff_key(Application::STATUSES, ['draft' => true]),
            'counts' => Application::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(Application $application)
    {
        $application->load(['structure.accounts', 'documents' => fn ($q) => $q->latest('id'), 'informationRequests' => fn ($q) => $q->latest('id'), 'messages', 'events' => fn ($q) => $q->latest('id')]);

        return view('admin.consortium.show', ['application' => $application, 'statuses' => Application::STATUSES, 'fields' => Fields::LABELS]);
    }

    public function update(Request $request, Application $application, Workflow $workflow, Notifications $notifications)
    {
        $input = $request->validate([
            'action' => 'required|in:save,review,request_information,accept,reject,message',
            'assigned_to' => 'nullable|string|max:255', 'internal_notes' => 'nullable|string|max:10000',
            'message' => 'nullable|string|max:5000', 'due_on' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'fields' => 'nullable|array', 'fields.*' => [Rule::in(array_keys(Fields::LABELS))],
            'document_kinds' => 'nullable|array', 'document_kinds.*' => [Rule::in(array_keys(config('consortium.document_kinds')))],
        ]);
        DB::transaction(function () use ($request, $application, $input, $workflow, $notifications) {
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $admin = $request->attributes->get('consortium_admin');
            $action = $input['action'];
            if ($action === 'save') {
                $application->forceFill(['assigned_to' => $input['assigned_to'] ?? null, 'internal_notes' => $input['internal_notes'] ?? null])->save();
                $workflow->record($application, 'internal_update', $admin->name);

                return;
            }
            if ($action === 'message') {
                if (mb_strlen(trim($input['message'] ?? '')) < 2) {
                    throw ValidationException::withMessages(['message' => 'Saisissez votre message.']);
                }
                $application->messages()->create(['author_type' => 'admin', 'author_id' => $admin->id, 'author_name' => $admin->name, 'body' => $input['message']]);
                $notifications->partners($application, 'Nouveau message de NLC', "Un message vous attend dans le dossier {$application->reference}.");

                return;
            }
            if ($action === 'review') {
                abort_unless($application->status === 'submitted', 409, 'Ce dossier ne peut pas être pris en étude dans cet état.');
                $application->forceFill(['status' => 'under_review', 'assigned_to' => $application->assigned_to ?: $admin->name])->save();
                $workflow->record($application, 'under_review', $admin->name);

                return;
            }
            abort_unless(in_array($application->status, ['submitted', 'under_review'], true), 409, 'Le statut du dossier a changé. Rechargez la page.');
            if ($action === 'request_information') {
                if (mb_strlen(trim($input['message'] ?? '')) < 10) {
                    throw ValidationException::withMessages(['message' => 'Précisez les compléments attendus (10 caractères minimum).']);
                }
                $application->informationRequests()->create(['message' => $input['message'], 'fields' => $input['fields'] ?? [], 'document_kinds' => $input['document_kinds'] ?? [], 'due_on' => $input['due_on'] ?? null]);
                $application->forceFill(['status' => 'needs_information'])->save();
                $workflow->record($application, 'needs_information', $admin->name, ['message' => $input['message']]);
                $notifications->partners($application, 'Compléments attendus pour votre adhésion', $input['message'].(! empty($input['due_on']) ? "\nÉchéance : {$input['due_on']}" : ''));

                return;
            }
            if (mb_strlen(trim($input['message'] ?? '')) < 10) {
                throw ValidationException::withMessages(['message' => 'Indiquez une décision motivée destinée à la structure (10 caractères minimum).']);
            }
            if ($action === 'accept') {
                $latest = $application->latestDocuments()->keyBy('kind');
                foreach (array_merge(config('consortium.required_documents'), ['signed_agreement']) as $kind) {
                    if (! isset($latest[$kind]) || $latest[$kind]->status !== 'accepted') {
                        throw ValidationException::withMessages(['documents' => 'Vérifiez et validez la pièce avant acceptation : '.config("consortium.document_kinds.$kind")]);
                    }
                }
            }
            $status = $action === 'accept' ? 'accepted' : 'rejected';
            $application->forceFill(['status' => $status, 'decision_reason' => $input['message'], 'decided_at' => now()])->save();
            $workflow->record($application, $status, $admin->name, ['message' => $input['message']]);
            $notifications->partners($application, $status === 'accepted' ? 'Votre adhésion au consortium est acceptée' : 'Décision concernant votre adhésion', $input['message']);
        });

        return back()->with('success', 'Action enregistrée. Les notifications sont mises en envoi.');
    }

    public function reviewDocument(Request $request, Application $application, Document $document, Workflow $workflow)
    {
        abort_unless($document->application_id === $application->id, 404);
        $data = $request->validate(['status' => 'required|in:accepted,rejected', 'review_comment' => 'nullable|string|max:2000']);
        if ($data['status'] === 'rejected' && empty($data['review_comment'])) {
            throw ValidationException::withMessages(['review_comment' => 'Expliquez pourquoi la pièce doit être remplacée.']);
        }
        DB::transaction(function () use ($application, $document, $data, $workflow, $request) {
            $locked = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status, ['submitted', 'under_review'], true), 409, 'Les pièces sont vérifiables pendant l’instruction du dossier.');
            $document->update($data);
            $workflow->record($locked, 'document_reviewed', $request->attributes->get('consortium_admin')->name, ['kind' => $document->kind, 'version' => $document->version, 'status' => $data['status']]);
        });

        return back()->with('success', 'Vérification de la pièce enregistrée.');
    }

    public function download(Application $application, Document $document)
    {
        abort_unless($document->application_id === $application->id && Storage::disk('local')->exists($document->getRawOriginal('path')), 404);

        return Storage::disk('local')->download($document->getRawOriginal('path'), $document->name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
