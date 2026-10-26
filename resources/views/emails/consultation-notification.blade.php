@php
    $liens = \App\Models\ConsultationRequest::LIEN_LABELS;
    $liens['autre'] = $c->parent_lien_autre ?: 'Autre';
    $langage = \App\Models\ConsultationRequest::LANGAGE_LABELS;
    $plateformes = \App\Models\ConsultationRequest::PLATFORM_LABELS;
    $observations = \App\Models\ConsultationRequest::OBSERVATION_LABELS;
    $diag = $c->diagnostics_anterieurs ?? [];
    $row = 'padding:6px 10px;border-bottom:1px solid #eee;vertical-align:top;';
    $th = $row . 'color:#666;width:42%;';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Nouvelle demande de consultation</title></head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif;color:#333;font-size:14px;">
<div style="max-width:680px;margin:20px auto;background:#fff;border-radius:10px;overflow:hidden;">
    <div style="background:#764ba2;color:#fff;padding:20px 24px;">
        <h1 style="margin:0;font-size:20px;">Nouvelle demande de consultation à distance</h1>
        <p style="margin:6px 0 0;opacity:.9;">Dossier {{ $c->reference }} - reçu le {{ $c->created_at->timezone('Africa/Kinshasa')->format('d/m/Y à H:i') }} (heure de Kinshasa)</p>
    </div>

    <div style="padding:20px 24px;">
        <h3>Créneaux proposés par le parent</h3>
        <ul>
            @foreach ($creneaux as $creneau)
                <li><strong>{{ $creneau['kinshasa'] }}</strong> (Kinshasa) - {{ $creneau['parent'] }} chez le parent</li>
            @endforeach
        </ul>
        <p>Plateforme : <strong>{{ $plateformes[$c->plateforme] ?? $c->plateforme }}</strong>
            @if ($c->participants) - Participants : {{ $c->participants === 'parents_enfant' ? 'parents avec l\'enfant' : 'parents seuls' }} @endif
            - Langue : {{ $c->langue_seance }}</p>

        <h3>Parent / responsable</h3>
        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="{{ $th }}">Nom</td><td style="{{ $row }}">{{ $c->parent_nom_complet }} ({{ $liens[$c->parent_lien] ?? $c->parent_lien }})</td></tr>
            <tr><td style="{{ $th }}">Résidence</td><td style="{{ $row }}">{{ $c->ville }}, {{ $c->pays }} - fuseau {{ $c->fuseau_horaire }}</td></tr>
            <tr><td style="{{ $th }}">E-mail</td><td style="{{ $row }}">{{ $c->email }}</td></tr>
            <tr><td style="{{ $th }}">WhatsApp</td><td style="{{ $row }}">{{ $c->whatsapp }}</td></tr>
            @if ($c->telephone)<tr><td style="{{ $th }}">Autre téléphone</td><td style="{{ $row }}">{{ $c->telephone }}</td></tr>@endif
            @if ($c->contact2_nom)<tr><td style="{{ $th }}">Second contact</td><td style="{{ $row }}">{{ $c->contact2_nom }} {{ $c->contact2_telephone }}</td></tr>@endif
            @if ($c->source_connaissance)<tr><td style="{{ $th }}">A connu NLC par</td><td style="{{ $row }}">{{ $c->source_connaissance }}</td></tr>@endif
        </table>

        <h3>Enfant</h3>
        <table style="width:100%;border-collapse:collapse;">
            <tr><td style="{{ $th }}">Nom complet</td><td style="{{ $row }}">{{ $c->enfant_nom_complet }} ({{ $c->enfant_sexe === 'F' ? 'fille' : 'garçon' }})</td></tr>
            <tr><td style="{{ $th }}">Date de naissance</td><td style="{{ $row }}">{{ $c->enfant_date_naissance->format('d/m/Y') }} ({{ $c->enfant_date_naissance->age }} ans)</td></tr>
            @if ($c->langues_maison)<tr><td style="{{ $th }}">Langues à la maison</td><td style="{{ $row }}">{{ $c->langues_maison }}</td></tr>@endif
            @if (!is_null($c->scolarise))<tr><td style="{{ $th }}">Scolarisé</td><td style="{{ $row }}">{{ $c->scolarise ? 'Oui' : 'Non' }}@if ($c->scolarise) - école {{ $c->type_ecole === 'specialisee' ? 'spécialisée' : 'ordinaire' }} {{ $c->classe }}@endif</td></tr>@endif
            @if ($c->niveau_langage)<tr><td style="{{ $th }}">Langage</td><td style="{{ $row }}">{{ $langage[$c->niveau_langage] ?? $c->niveau_langage }}</td></tr>@endif
        </table>

        <h3>Situation</h3>
        <p style="background:#f8f8f8;padding:12px;border-radius:6px;white-space:pre-line;">{{ $c->motif }}</p>
        <table style="width:100%;border-collapse:collapse;">
            @if ($c->age_premieres_difficultes)<tr><td style="{{ $th }}">Premières difficultés</td><td style="{{ $row }}">{{ $c->age_premieres_difficultes }}</td></tr>@endif
            @if (!empty($diag['types']) || !empty($diag['autre']))
                <tr><td style="{{ $th }}">Diagnostic(s) déjà posé(s)</td><td style="{{ $row }}">
                    {{ implode(', ', array_filter(array_merge($diag['types'] ?? [], [$diag['autre'] ?? null]))) }}
                    @if (!empty($diag['par_qui'])) - par {{ $diag['par_qui'] }} @endif
                    @if (!empty($diag['quand'])) ({{ $diag['quand'] }}) @endif
                </td></tr>
            @endif
            @if (!empty($c->suivis))<tr><td style="{{ $th }}">Suivis</td><td style="{{ $row }}">{{ implode(', ', $c->suivis) }}</td></tr>@endif
            @if ($c->antecedents_familiaux)<tr><td style="{{ $th }}">Antécédents familiaux</td><td style="{{ $row }}">{{ ['oui' => 'Oui', 'non' => 'Non', 'ne_sait_pas' => 'Ne sait pas'][$c->antecedents_familiaux] }}</td></tr>@endif
            @if ($c->naissance_particuliere)<tr><td style="{{ $th }}">Grossesse / naissance</td><td style="{{ $row }}">{{ $c->naissance_particuliere }}</td></tr>@endif
            @if ($c->traitement_actuel)<tr><td style="{{ $th }}">Traitement actuel</td><td style="{{ $row }}">{{ $c->traitement_actuel }}</td></tr>@endif
            @foreach ($observations as $key => $label)
                @if (!empty($c->observations[$key]))
                    <tr><td style="{{ $th }}">{{ $label }}</td><td style="{{ $row }}">{{ ucfirst($c->observations[$key]) }}</td></tr>
                @endif
            @endforeach
            @if ($c->attentes)<tr><td style="{{ $th }}">Attentes</td><td style="{{ $row }}">{{ $c->attentes }}</td></tr>@endif
        </table>

        <h3>Documents</h3>
        @if (empty($c->documents))
            <p>Aucun document joint. L'avis du psychiatre sera transmis après l'inscription.</p>
        @else
            <ul>
                @foreach ($c->documents as $doc)
                    <li>{{ $doc['name'] }} ({{ number_format($doc['size'] / 1024 / 1024, 1) }} Mo)</li>
                @endforeach
            </ul>
            <p style="color:#666;font-size:12px;">Les documents sont conservés sur le serveur et ne sont pas joints à cet e-mail.</p>
        @endif

        <p style="color:#666;font-size:12px;">Consentements donnés le {{ $c->consented_at->timezone('Africa/Kinshasa')->format('d/m/Y à H:i') }}. Répondre à cet e-mail écrit directement au parent.</p>
    </div>
</div>
</body>
</html>
