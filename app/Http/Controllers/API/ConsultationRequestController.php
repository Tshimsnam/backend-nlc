<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ConsultationRequestNotificationMail;
use App\Mail\ConsultationRequestReceivedMail;
use App\Models\ConsultationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConsultationRequestController extends Controller
{
    public const OBSERVATION_KEYS = [
        'regard', 'prenom', 'jeu_autres', 'gestes_repetitifs',
        'changements', 'sensorialite', 'alimentation',
    ];

    /**
     * Inscription d'un enfant + demande de consultation à distance
     * POST /api/consultations
     * Body (multipart) : payload = JSON du formulaire, documents[] = fichiers (facultatifs)
     */
    public function store(Request $request)
    {
        $data = json_decode($request->input('payload', ''), true);
        if (!is_array($data)) {
            return response()->json(['success' => false, 'message' => 'Données du formulaire invalides.'], 422);
        }

        $validator = Validator::make($data, [
            // Parent
            'parent_nom'          => 'required|string|max:255',
            'parent_prenom'       => 'required|string|max:255',
            'parent_lien'         => 'required|in:mere,pere,tuteur,autre',
            'parent_lien_autre'   => 'nullable|required_if:parent_lien,autre|string|max:255',
            'pays'                => 'required|string|max:100',
            'ville'               => 'required|string|max:100',
            'fuseau_horaire'      => 'required|timezone:all',
            'email'               => 'required|email|max:255',
            'whatsapp'            => 'required|string|max:30',
            'telephone'           => 'nullable|string|max:30',
            'langue_seance'       => 'required|string|max:30',
            'contact2_nom'        => 'nullable|string|max:255',
            'contact2_telephone'  => 'nullable|string|max:30',
            'source_connaissance' => 'nullable|string|max:50',

            // Enfant
            'enfant_nom'            => 'required|string|max:255',
            'enfant_postnom'        => 'nullable|string|max:255',
            'enfant_prenom'         => 'required|string|max:255',
            'enfant_sexe'           => 'required|in:F,M',
            'enfant_date_naissance' => 'required|date|before:today|after:1990-01-01',
            'langues_maison'        => 'nullable|string|max:255',
            'scolarise'             => 'nullable|boolean',
            'type_ecole'            => 'nullable|in:ordinaire,specialisee',
            'classe'                => 'nullable|string|max:50',
            'niveau_langage'        => 'nullable|in:non_verbal,quelques_mots,phrases_simples,courant',

            // Situation
            'motif'                          => 'required|string|max:5000',
            'age_premieres_difficultes'      => 'nullable|string|max:100',
            'diagnostics_anterieurs'         => 'nullable|array',
            'diagnostics_anterieurs.types'   => 'nullable|array',
            'diagnostics_anterieurs.types.*' => 'string|max:50',
            'diagnostics_anterieurs.autre'   => 'nullable|string|max:255',
            'diagnostics_anterieurs.par_qui' => 'nullable|string|max:255',
            'diagnostics_anterieurs.quand'   => 'nullable|string|max:100',
            'suivis'                         => 'nullable|array',
            'suivis.*'                       => 'string|max:50',
            'antecedents_familiaux'          => 'nullable|in:oui,non,ne_sait_pas',
            'naissance_particuliere'         => 'nullable|string|max:2000',
            'traitement_actuel'              => 'nullable|string|max:2000',
            'observations'                   => 'nullable|array',
            'observations.*'                 => 'nullable|in:souvent,parfois,jamais',
            'attentes'                       => 'nullable|string|max:5000',

            // Rendez-vous
            'creneaux'         => 'required|array|min:1|max:3',
            'creneaux.*.start' => 'required|date|after:now',
            'plateforme'       => 'required|in:zoom,meet,indifferent',
            'participants'     => 'nullable|in:parents,parents_enfant',

            // Consentements
            'consentement_donnees'       => 'accepted',
            'engagement_avis_psychiatre' => 'accepted',
            'acceptation_conditions'     => 'accepted',
        ], [
            'required'               => 'Ce champ est obligatoire.',
            'required_if'            => 'Ce champ est obligatoire.',
            'email'                  => 'Adresse e-mail invalide.',
            'in'                     => 'Valeur non valide.',
            'max'                    => 'Texte trop long.',
            'timezone'               => 'Fuseau horaire invalide.',
            'enfant_date_naissance.before' => 'La date de naissance doit être dans le passé.',
            'creneaux.*.start.after' => 'Les créneaux proposés doivent être dans le futur.',
            'accepted'               => 'Ce consentement est obligatoire.',
        ]);

        $fileValidator = Validator::make($request->all(), [
            'documents'   => 'nullable|array|max:5',
            'documents.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,heic,doc,docx',
        ], [
            'documents.max'   => '5 documents maximum.',
            'documents.*.max' => 'Chaque document doit faire moins de 10 Mo.',
        ]);

        if ($validator->fails() || $fileValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Certains champs sont invalides.',
                'errors'  => array_merge($validator->errors()->toArray(), $fileValidator->errors()->toArray()),
            ], 422);
        }

        $validated = $validator->validated();
        // Ne garder que les observations connues
        if (isset($validated['observations'])) {
            $validated['observations'] = array_intersect_key($validated['observations'], array_flip(self::OBSERVATION_KEYS));
        }

        $consultation = new ConsultationRequest($validated + ['consented_at' => now()]);
        $consultation->reference = ConsultationRequest::generateReference();

        // Documents : disque privé, jamais exposés publiquement
        $documents = [];
        foreach ($request->file('documents', []) as $file) {
            $path = $file->storeAs(
                "consultations/{$consultation->reference}",
                Str::random(12) . '.' . strtolower($file->getClientOriginalExtension()),
                'local'
            );
            $documents[] = [
                'name'        => $file->getClientOriginalName(),
                'path'        => $path,
                'size'        => $file->getSize(),
                'mime'        => $file->getClientMimeType(),
                'type'        => 'document_initial',
                'uploaded_at' => now()->toIso8601String(),
            ];
        }
        $consultation->documents = $documents;
        $consultation->save();

        $this->sendEmails($consultation);

        return response()->json([
            'success'   => true,
            'message'   => 'Votre demande a bien été enregistrée.',
            'reference' => $consultation->reference,
        ], 201);
    }

    private function sendEmails(ConsultationRequest $consultation): void
    {
        // Un échec d'envoi ne doit pas faire échouer l'inscription
        try {
            Mail::to($consultation->email)->send(new ConsultationRequestReceivedMail($consultation));
        } catch (\Throwable $e) {
            Log::error('Consultation: e-mail parent non envoyé', ['reference' => $consultation->reference, 'error' => $e->getMessage()]);
        }

        try {
            Mail::to(config('services.nlc.secretariat_email'))->send(new ConsultationRequestNotificationMail($consultation));
        } catch (\Throwable $e) {
            Log::error('Consultation: e-mail secrétariat non envoyé', ['reference' => $consultation->reference, 'error' => $e->getMessage()]);
        }
    }
}
