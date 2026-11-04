<?php

namespace App\Http\Controllers\Consortium;

use App\Http\Controllers\Controller;
use App\Models\Consortium\Application;
use App\Models\Consortium\Document;
use App\Models\Consortium\Notification;
use App\Services\Consortium\Fields;
use App\Services\Consortium\Notifications;
use App\Services\Consortium\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PartnerController extends Controller
{
    public function config()
    {
        return response()->json([
            'statuses' => Application::STATUSES, 'fields' => Fields::LABELS,
            'document_kinds' => config('consortium.document_kinds'), 'required_documents' => config('consortium.required_documents'),
            'provinces' => config('consortium.provinces'), 'expertise' => config('consortium.expertise'), 'needs' => config('consortium.needs'),
            'agreement_url' => config('consortium.agreement_url'), 'agreement_version' => config('consortium.agreement_version'),
            'max_file_kb' => config('consortium.max_file_kb'),
        ]);
    }

    private function application(Request $request, bool $lock = false): Application
    {
        $query = Application::where('structure_id', $request->user('consortium')->structure_id);

        return ($lock ? $query->lockForUpdate() : $query)->firstOrFail();
    }

    public function show(Request $request)
    {
        return response()->json($this->application($request)->load([
            'documents' => fn ($q) => $q->latest('id'), 'informationRequests' => fn ($q) => $q->latest('id'),
            'messages' => fn ($q) => $q->oldest('id'), 'events' => fn ($q) => $q->latest('id'),
        ]));
    }

    public function save(Request $request)
    {
        $request->validate(['data' => 'required|array']);
        $data = Validator::make($request->input('data'), Fields::rules(), [], Fields::LABELS)->validate();
        DB::transaction(function () use ($request, $data) {
            $application = $this->application($request, true);
            abort_unless($application->editable(), 409, 'Le dossier est en cours d’étude ou clôturé.');
            $application->update(['data' => array_replace($application->data, $data)]);
            if (! empty($data['official_name'])) {
                $application->structure->update(['name' => $data['official_name']]);
            }
        });

        return $this->show($request);
    }

    public function summary(Request $request, Workflow $workflow)
    {
        $application = $this->application($request);

        return response()->streamDownload(fn () => print ($workflow->summary($application)), $application->reference.'.txt', ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(array_keys(config('consortium.document_kinds')))],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.config('consortium.max_file_kb')],
        ]);
        $path = null;
        try {
            $document = DB::transaction(function () use ($request, $data, &$path) {
                $application = $this->application($request, true);
                abort_unless($application->editable(), 409, 'Ce dossier ne peut plus recevoir de document.');
                $file = $request->file('file');
                if ($application->documents()->sum('size') + $file->getSize() > config('consortium.max_storage_bytes') || $application->documents()->count() >= 50) {
                    throw ValidationException::withMessages(['file' => 'Limite de stockage atteinte. Contactez NLC pour poursuivre votre dossier.']);
                }
                $path = $file->store(config('consortium.storage_prefix').'/'.$application->id, 'local');
                if (! $path) {
                    throw new \RuntimeException('Document non enregistré.');
                }
                $document = $application->documents()->create([
                    'kind' => $data['kind'], 'version' => ($application->documents()->where('kind', $data['kind'])->max('version') ?? 0) + 1,
                    'name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 200),
                    'path' => $path, 'mime' => $file->getMimeType(), 'size' => $file->getSize(),
                ]);
                app(Workflow::class)->record($application, 'document_uploaded', $request->user('consortium')->name, ['kind' => $document->kind, 'version' => $document->version]);

                return $document;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        return response()->json($document, 201);
    }

    public function download(Request $request, Document $document)
    {
        abort_unless($document->application_id === $this->application($request)->id, 404);
        abort_unless(Storage::disk('local')->exists($document->getRawOriginal('path')), 404);

        return Storage::disk('local')->download($document->getRawOriginal('path'), $document->name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function submit(Request $request, Workflow $workflow)
    {
        $request->validate(['response' => 'nullable|string|max:5000']);
        DB::transaction(fn () => $workflow->submit($this->application($request, true), $request->user('consortium')->name, $request->input('response')));

        return $this->show($request);
    }

    public function message(Request $request, Notifications $notifications)
    {
        $data = $request->validate(['body' => 'required|string|min:2|max:5000']);
        DB::transaction(function () use ($request, $data, $notifications) {
            $application = $this->application($request, true);
            $account = $request->user('consortium');
            $application->messages()->create(['author_type' => 'partner', 'author_id' => $account->id, 'author_name' => $account->name, 'body' => $data['body']]);
            $notifications->admin($application, 'Nouveau message partenaire', "Un nouveau message est disponible dans le dossier {$application->reference}.");
        });

        return $this->show($request);
    }

    public function withdraw(Request $request, Workflow $workflow)
    {
        DB::transaction(function () use ($request, $workflow) {
            $application = $this->application($request, true);
            abort_if(in_array($application->status, ['accepted', 'rejected', 'withdrawn'], true), 409);
            $application->forceFill(['status' => 'withdrawn'])->save();
            $workflow->record($application, 'withdrawn', $request->user('consortium')->name);
            app(Notifications::class)->admin($application, 'Demande retirée', "La structure a retiré le dossier {$application->reference}.");
        });

        return $this->show($request);
    }

    public function notifications(Request $request)
    {
        return response()->json(Notification::where('account_id', $request->user('consortium')->id)->latest('id')->limit(100)->get());
    }

    public function markRead(Request $request, Notification $notification)
    {
        abort_unless($notification->account_id === $request->user('consortium')->id, 404);
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
