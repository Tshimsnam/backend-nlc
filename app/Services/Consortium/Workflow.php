<?php

namespace App\Services\Consortium;

use App\Models\Consortium\Application;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Workflow
{
    public function summary(Application $application): string
    {
        $lines = ['NLC — CONSORTIUM SUR L’AUTISME EN RDC', $application->reference, 'Statut : '.Application::STATUSES[$application->status], 'Révision : '.$application->revision, ''];
        foreach (Fields::LABELS as $key => $label) {
            $value = $application->data[$key] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            $lines[] = $label.' : '.Fields::display($key, $value);
        }
        $lines[] = "\nPIÈCES TRANSMISES";
        foreach ($application->latestDocuments() as $document) {
            $lines[] = config('consortium.document_kinds.'.$document->kind).' — '.$document->name.' (v'.$document->version.')';
        }
        $lines[] = "\nL’adhésion et l’attribution d’un financement font l’objet de décisions distinctes.";

        return implode("\r\n", $lines);
    }

    public function record(Application $application, string $type, string $actor, array $metadata = []): void
    {
        $application->events()->create(['type' => $type, 'actor_name' => $actor, 'metadata' => $metadata]);
    }

    public function submit(Application $application, string $actor, ?string $response): void
    {
        if (! $application->editable()) {
            throw ValidationException::withMessages(['status' => 'Ce dossier a déjà été soumis ou clôturé. Rechargez votre espace.']);
        }
        $data = Validator::make($application->data, Fields::rules(true), [], Fields::LABELS)->validate();
        if (in_array('other', $data['expertise'] ?? [], true) && empty($data['expertise_other'])) {
            throw ValidationException::withMessages(['expertise_other' => 'Précisez votre autre domaine d’intervention.']);
        }
        $latest = $application->latestDocuments()->keyBy('kind');
        foreach (config('consortium.required_documents') as $kind) {
            if (! isset($latest[$kind]) || $latest[$kind]->status === 'rejected') {
                throw ValidationException::withMessages(['documents' => 'Ajoutez une pièce valide : '.config("consortium.document_kinds.$kind")]);
            }
        }
        $requests = $application->informationRequests()->whereNull('responded_at')->get();
        if ($requests->isNotEmpty()) {
            if (mb_strlen(trim($response ?? '')) < 10) {
                throw ValidationException::withMessages(['response' => 'Ajoutez une réponse d’au moins 10 caractères à la demande de NLC.']);
            }
            foreach ($requests as $request) {
                foreach ($request->document_kinds as $kind) {
                    if (! isset($latest[$kind]) || in_array($latest[$kind]->id, $application->submitted_document_ids ?? [], true) || $latest[$kind]->status === 'rejected') {
                        throw ValidationException::withMessages(['documents' => 'Une nouvelle version est attendue : '.config("consortium.document_kinds.$kind")]);
                    }
                }
                foreach ($request->fields as $field) {
                    if (($data[$field] ?? null) === null || ($data[$field] ?? '') === '' || ($data[$field] ?? null) === []) {
                        throw ValidationException::withMessages([$field => 'Complétez cette information demandée par NLC.']);
                    }
                }
                $request->update(['responded_at' => now()]);
            }
            $application->messages()->create(['author_type' => 'partner', 'author_id' => auth('consortium')->id(), 'author_name' => $actor, 'body' => $response]);
        }
        $resubmission = $application->status === 'needs_information';
        $application->forceFill([
            'status' => $resubmission ? 'under_review' : 'submitted', 'submitted_data' => $data,
            'submitted_document_ids' => $latest->pluck('id')->values()->all(), 'submitted_at' => now(), 'revision' => $application->revision + 1,
        ])->save();
        $this->record($application, $resubmission ? 'resubmitted' : 'submitted', $actor, [
            'revision' => $application->revision, 'data' => $data, 'document_ids' => $application->submitted_document_ids,
            'agreement_version' => config('consortium.agreement_version'),
        ]);
        app(Notifications::class)->partners($application, $resubmission ? 'Compléments reçus' : 'Votre demande a été reçue', "Votre dossier {$application->reference} est enregistré. Vous pouvez suivre son traitement dans votre espace. L’adhésion ne constitue pas une attribution de financement.");
        app(Notifications::class)->admin($application, $resubmission ? 'Compléments à examiner' : 'Nouvelle demande d’adhésion', "Le dossier {$application->reference} de {$application->structure->name} attend votre traitement.");
    }
}
