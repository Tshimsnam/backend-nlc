<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ConsultationRequest extends Model
{
    public const STATUSES = ['nouvelle', 'dossier_incomplet', 'rdv_programme', 'seance_faite', 'annulee'];

    public const STATUS_LABELS = [
        'nouvelle'          => 'Nouvelle',
        'dossier_incomplet' => 'Dossier incomplet',
        'rdv_programme'     => 'RDV programmé',
        'seance_faite'      => 'Séance faite',
        'annulee'           => 'Annulée',
    ];

    public const PAYMENT_STATUSES = [
        'gratuit'    => 'Gratuit',
        'en_attente' => 'En attente',
        'paye'       => 'Payé',
    ];

    public const PLATFORM_LABELS = ['zoom' => 'Zoom', 'meet' => 'Google Meet', 'indifferent' => 'Sans préférence'];

    public const LIEN_LABELS = ['mere' => 'Mère', 'pere' => 'Père', 'tuteur' => 'Tuteur / tutrice', 'autre' => 'Autre'];

    public const LANGAGE_LABELS = [
        'non_verbal'      => 'Ne parle pas',
        'quelques_mots'   => 'Quelques mots',
        'phrases_simples' => 'Phrases simples',
        'courant'         => 'Parle couramment',
    ];

    public const OBSERVATION_LABELS = [
        'regard'            => 'Regarde quand on lui parle',
        'prenom'            => 'Répond à son prénom',
        'jeu_autres'        => "Joue avec d'autres enfants",
        'gestes_repetitifs' => 'Gestes répétitifs',
        'changements'       => 'Difficulté avec les changements',
        'sensorialite'      => 'Sensibilité aux bruits / au toucher',
        'alimentation'      => 'Alimentation sélective',
    ];

    protected $fillable = [
        'reference', 'access_token', 'status',
        'parent_nom', 'parent_prenom', 'parent_lien', 'parent_lien_autre',
        'pays', 'ville', 'fuseau_horaire', 'email', 'whatsapp', 'telephone',
        'langue_seance', 'contact2_nom', 'contact2_telephone', 'source_connaissance',
        'enfant_nom', 'enfant_postnom', 'enfant_prenom', 'enfant_sexe', 'enfant_date_naissance',
        'langues_maison', 'scolarise', 'type_ecole', 'classe', 'niveau_langage',
        'motif', 'age_premieres_difficultes', 'diagnostics_anterieurs', 'suivis',
        'antecedents_familiaux', 'naissance_particuliere', 'traitement_actuel',
        'observations', 'attentes',
        'creneaux', 'plateforme', 'participants',
        'documents', 'consentement_donnees', 'engagement_avis_psychiatre',
        'acceptation_conditions', 'consented_at',
        'professionnel_assigne', 'seance_programmee_at', 'lien_visio',
        'duree_minutes', 'prix', 'devise', 'statut_paiement', 'notes_internes',
        'confirmation_envoyee_at',
    ];

    // Ne jamais exposer le jeton d'accès ni les notes internes dans les réponses JSON
    protected $hidden = ['access_token', 'notes_internes'];

    protected $casts = [
        'enfant_date_naissance' => 'date',
        'scolarise' => 'boolean',
        'diagnostics_anterieurs' => 'array',
        'suivis' => 'array',
        'observations' => 'array',
        'creneaux' => 'array',
        'documents' => 'array',
        'consentement_donnees' => 'boolean',
        'engagement_avis_psychiatre' => 'boolean',
        'acceptation_conditions' => 'boolean',
        'consented_at' => 'datetime',
        'seance_programmee_at' => 'datetime',
        'prix' => 'decimal:2',
        'confirmation_envoyee_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConsultationRequest $request) {
            $request->reference ??= static::generateReference();
            $request->access_token ??= Str::random(64);
        });
    }

    /** Ex. TC-2610-7KQ4XZ */
    public static function generateReference(): string
    {
        do {
            $reference = 'TC-' . now()->format('ym') . '-' . strtoupper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function getEnfantNomCompletAttribute(): string
    {
        return trim("{$this->enfant_prenom} {$this->enfant_nom} {$this->enfant_postnom}");
    }

    /**
     * Créneaux proposés, affichés à la fois dans l'heure du parent et l'heure de Kinshasa.
     * @return array<int, array{parent: string, kinshasa: string}>
     */
    public function creneauxFormates(): array
    {
        $format = 'l j F Y à H:i';

        return collect($this->creneaux ?? [])->map(function ($creneau) use ($format) {
            $start = Carbon::parse($creneau['start'])->locale('fr');

            return [
                'parent'   => $start->copy()->setTimezone($this->fuseau_horaire)->translatedFormat($format),
                'kinshasa' => $start->copy()->setTimezone('Africa/Kinshasa')->translatedFormat($format),
            ];
        })->all();
    }

    /** Séance programmée, affichée dans l'heure du parent et l'heure de Kinshasa. */
    public function seanceFormatee(): ?array
    {
        if (!$this->seance_programmee_at) {
            return null;
        }
        $format = 'l j F Y à H:i';
        $start = $this->seance_programmee_at->copy()->locale('fr');

        return [
            'parent'   => $start->copy()->setTimezone($this->fuseau_horaire)->translatedFormat($format),
            'kinshasa' => $start->copy()->setTimezone('Africa/Kinshasa')->translatedFormat($format),
        ];
    }

    public function estGratuite(): bool
    {
        return $this->prix === null || (float) $this->prix <= 0;
    }

    /** Lien wa.me (ouvre WhatsApp avec le message pré-rempli), sans API ni compte WhatsApp Business. */
    public function lienWhatsApp(string $message = ''): ?string
    {
        $numero = preg_replace('/\D+/', '', (string) $this->whatsapp);
        if ($numero === '') {
            return null;
        }

        return 'https://wa.me/' . $numero . ($message !== '' ? '?text=' . rawurlencode($message) : '');
    }

    public function getParentNomCompletAttribute(): string
    {
        return trim("{$this->parent_prenom} {$this->parent_nom}");
    }
}
