<?php

namespace App\Services\Orientation;

use App\Models\ConsultationRequest;
use App\Models\Orientation\Contact;
use App\Models\Orientation\Draft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Intake
{
    public static function rules(bool $final = true): array
    {
        $r = $final ? 'required' : 'nullable';

        return [
            'parent_nom' => "$r|string|max:150", 'parent_prenom' => "$r|string|max:150", 'parent_lien' => [$r, Rule::in(['mere', 'pere', 'tuteur', 'tiers', 'professionnel'])],
            'parent_lien_autre' => 'nullable|string|max:255', 'telephone' => "$r|string|regex:/^\+[0-9 ()\-.]{7,28}$/", 'whatsapp' => 'nullable|string|max:30',
            'preferred_contact' => [$r, Rule::in(['email', 'telephone', 'whatsapp'])], 'pays' => "$r|string|max:100", 'ville' => "$r|string|max:100", 'fuseau_horaire' => "$r|timezone:all",
            'langue_seance' => "$r|string|max:80", 'guardian_name' => 'nullable|string|max:255', 'guardian_email' => 'nullable|email|max:255',
            'authority_consent' => $final ? 'accepted' : 'nullable|boolean', 'consentement_donnees' => $final ? 'accepted' : 'nullable|boolean', 'acceptation_conditions' => $final ? 'accepted' : 'nullable|boolean',
            'enfant_nom' => "$r|string|max:150", 'enfant_postnom' => 'nullable|string|max:150', 'enfant_prenom' => "$r|string|max:150",
            'enfant_sexe' => 'nullable|in:F,M', 'enfant_date_naissance' => [$r, 'date', 'before_or_equal:today', 'after:'.now()->subYears(18)->toDateString()],
            'langues_maison' => 'nullable|string|max:255', 'scolarise' => 'nullable|boolean', 'type_ecole' => 'nullable|in:ordinaire,specialisee', 'classe' => 'nullable|string|max:80',
            'niveau_langage' => 'nullable|in:non_verbal,quelques_mots,phrases_simples,courant', 'motif' => "$r|string|min:10|max:5000", 'attentes' => 'nullable|string|max:3000',
            'age_premieres_difficultes' => 'nullable|string|max:100', 'suivis' => 'nullable|array|max:12', 'suivis.*' => 'string|max:100',
            'diagnostics_anterieurs' => 'nullable|array:types,autre,par_qui,quand', 'diagnostics_anterieurs.types' => 'nullable|array|max:10', 'diagnostics_anterieurs.types.*' => 'string|max:100',
            'diagnostics_anterieurs.autre' => 'nullable|string|max:500', 'diagnostics_anterieurs.par_qui' => 'nullable|string|max:255', 'diagnostics_anterieurs.quand' => 'nullable|string|max:100',
            'antecedents_familiaux' => 'nullable|in:oui,non,ne_sait_pas', 'naissance_particuliere' => 'nullable|string|max:2000', 'traitement_actuel' => 'nullable|string|max:2000',
            'observations' => 'nullable|array:regard,prenom,jeu_autres,gestes_repetitifs,changements,sensorialite,alimentation', 'observations.*' => 'nullable|in:souvent,parfois,jamais,ne_sait_pas,non_applicable',
            'consultation_mode' => [$r, Rule::in(['remote', 'centre', 'either'])], 'creneaux' => "$r|array|min:1|max:3", 'creneaux.*' => 'array:start', 'creneaux.*.start' => "$r|date|after:now",
            'plateforme' => 'nullable|in:zoom,meet,indifferent', 'participants' => 'nullable|in:parents,parents_enfant', 'source_connaissance' => 'nullable|string|max:100',
        ];
    }

    public function validate(array $data, Contact $contact): array
    {
        if (isset($data['guardian_email']) && is_string($data['guardian_email'])) {
            $data['guardian_email'] = strtolower(trim($data['guardian_email']));
        }
        $data = Validator::make($data, self::rules(), ['accepted' => 'Votre accord est nécessaire.', 'required' => 'Ce champ est obligatoire.', 'enfant_date_naissance.after' => 'Ce parcours est réservé aux personnes de moins de 18 ans.', 'telephone.regex' => 'Utilisez un numéro avec indicatif international, par exemple +243… .'])->validate();
        if (in_array($data['parent_lien'], ['tiers', 'professionnel'], true)) {
            Validator::make($data, ['guardian_name' => 'required', 'guardian_email' => ['required', Rule::notIn([$contact->email])], 'parent_lien_autre' => 'required'])->validate();
        }
        if ($data['preferred_contact'] === 'whatsapp' && empty($data['whatsapp'])) {
            throw ValidationException::withMessages(['whatsapp' => 'Indiquez un numéro WhatsApp ou choisissez un autre moyen de contact.']);
        }

        return $data;
    }

    public function submit(Request $request, Contact $actor): ConsultationRequest
    {
        $payload = json_decode($request->input('payload', ''), true);
        if (! is_array($payload)) {
            throw ValidationException::withMessages(['payload' => 'Le formulaire est invalide.']);
        }
        $data = $this->validate($payload, $actor);
        $request->validate(['submission_key' => 'required|uuid', 'documents' => 'nullable|array|max:5', 'documents.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png']);
        $paths = [];
        try {
            return DB::transaction(function () use ($request, $actor, $data, &$paths) {
                Contact::whereKey($actor->id)->lockForUpdate()->firstOrFail();
                $existing = ConsultationRequest::where('submission_key', $request->submission_key)->first();
                if ($existing) {
                    abort_unless((int) $existing->orientation_contact_id === $actor->id, 409);

                    return $existing;
                }
                $thirdParty = in_array($data['parent_lien'], ['tiers', 'professionnel'], true);
                $guardian = $thirdParty ? Contact::firstOrCreate(['email' => strtolower($data['guardian_email'])], ['name' => $data['guardian_name']]) : $actor;
                $c = new ConsultationRequest;
                $c->fill(collect($data)->except(['authority_consent', 'guardian_name', 'guardian_email', 'preferred_contact', 'consultation_mode'])->all());
                $c->forceFill(['email' => $actor->email, 'whatsapp' => $data['whatsapp'] ?? '', 'enfant_sexe' => $data['enfant_sexe'] ?? '', 'plateforme' => $data['plateforme'] ?? 'indifferent',
                    'orientation_contact_id' => $actor->id, 'guardian_contact_id' => $guardian->id, 'guardian_name' => $thirdParty ? $data['guardian_name'] : trim($data['parent_prenom'].' '.$data['parent_nom']),
                    'guardian_email' => $guardian->email, 'authority_status' => $thirdParty ? 'pending' : 'declared', 'guardian_approved_at' => $thirdParty ? null : now(),
                    'preferred_contact' => $data['preferred_contact'], 'consultation_mode' => $data['consultation_mode'], 'processing_due_at' => now()->addHours(config('orientation.processing_hours')),
                    'status' => $thirdParty ? 'autorisation_attendue' : 'nouvelle', 'consented_at' => now(), 'engagement_avis_psychiatre' => false, 'submission_key' => $request->submission_key, 'documents' => []]);
                $c->save();
                $this->appendFiles($c, $request->file('documents', []), $paths);
                Draft::where('contact_id', $actor->id)->delete();
                app(Workflow::class)->event($c, 'family', $actor->id, 'Demande reçue');
                app(Notices::class)->send('family', $actor->id, $actor->email, 'Demande enregistrée', 'Votre demande '.$c->reference.' est enregistrée. Le secrétariat prévoit une première réponse sous 72 heures. Un entretien d’orientation n’est pas un diagnostic.', Settings::url('/espace-famille'));
                if ($thirdParty) {
                    app(Access::class)->issue('family', $guardian, 'guardian', $c);
                }
                app(Notices::class)->admin('Nouvelle demande d’orientation', 'Une nouvelle demande '.$c->reference.' attend un traitement sous 72 heures. Consultez le dossier dans l’administration.', $c);

                return $c;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }
    }

    public function appendFiles(ConsultationRequest $c, array $files, array &$paths): void
    {
        $documents = $c->documents ?? [];
        $size = array_sum(array_column($documents, 'size')) + array_sum(array_map(fn ($f) => $f->getSize(), $files));
        if (count($documents) + count($files) > 10 || $size > 100 * 1024 * 1024) {
            throw ValidationException::withMessages(['documents' => 'Limite du dossier : 10 documents et 100 Mo au total.']);
        }
        foreach ($files as $file) {
            $path = $file->storeAs(config('orientation.storage_prefix').'/'.$c->id, Str::uuid().'.'.$file->extension(), 'local');
            if (! $path) {
                throw new \RuntimeException('Échec du stockage privé.');
            }
            $paths[] = $path;
            $documents[] = ['name' => Str::limit(basename(str_replace('\\', '/', $file->getClientOriginalName())), 200, ''), 'path' => $path, 'mime' => $file->getMimeType(), 'size' => $file->getSize(), 'uploaded_at' => now()->toIso8601String()];
        }
        $c->forceFill(['documents' => $documents])->save();
    }
}
