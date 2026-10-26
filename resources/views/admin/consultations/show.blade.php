@extends('admin.consultations.layout')

@section('title', 'Dossier ' . $c->reference)

@use('App\Models\ConsultationRequest')

@php
    $kin = 'Africa/Kinshasa';
    $liens = ConsultationRequest::LIEN_LABELS;
    $liens['autre'] = $c->parent_lien_autre ?: 'Autre';
    $diag = $c->diagnostics_anterieurs ?? [];
    $diagTexte = implode(', ', array_filter(array_merge($diag['types'] ?? [], [$diag['autre'] ?? null])));
    $seance = $c->seanceFormatee();

    // Message WhatsApp pré-rempli : confirmation si le RDV est fixé, sinon premier contact
    $messageWhatsApp = $seance && $c->lien_visio
        ? "Bonjour {$c->parent_prenom}, ici le secrétariat de Never Limit Children. La séance pour {$c->enfant_prenom} est confirmée le {$seance['parent']} (votre heure), soit {$seance['kinshasa']} à Kinshasa. Lien : {$c->lien_visio} - Dossier {$c->reference}"
        : "Bonjour {$c->parent_prenom}, ici le secrétariat de Never Limit Children au sujet de votre demande pour {$c->enfant_prenom} (dossier {$c->reference}).";

    $input = 'w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400';
    $label = 'block text-sm font-medium text-gray-700 mb-1';
    $dt = 'text-gray-500 py-1.5 pr-4 align-top w-48';
    $dd = 'text-gray-900 py-1.5';
@endphp

@section('header')
    <a href="{{ route('admin.consultations.index') }}" class="text-sm text-gray-500 hover:text-blue-600">← Toutes les consultations</a>
    <div class="flex flex-wrap items-center gap-3 mt-2">
        <h2 class="text-3xl font-bold text-gray-800">{{ $c->enfant_nom_complet }}</h2>
        @include('admin.consultations._status-badge', ['status' => $c->status])
    </div>
    <p class="text-gray-600 mt-1">
        Dossier <span class="font-mono">{{ $c->reference }}</span> - reçu le {{ $c->created_at->timezone($kin)->format('d/m/Y à H:i') }}
        @if($c->confirmation_envoyee_at)
            - confirmation envoyée le {{ $c->confirmation_envoyee_at->timezone($kin)->format('d/m/Y à H:i') }}
        @endif
    </p>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

    {{-- ── Dossier ─────────────────────────────────────────────── --}}
    <div class="xl:col-span-3 space-y-6">

        <section class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Créneaux proposés par le parent</h3>
            <ul class="space-y-2">
                @foreach($c->creneaux ?? [] as $creneau)
                    @php $start = \Carbon\Carbon::parse($creneau['start']); @endphp
                    <li class="flex flex-wrap items-center justify-between gap-2 border rounded-lg px-4 py-3">
                        <div>
                            <div class="font-medium text-gray-900">{{ ucfirst($start->copy()->timezone($kin)->locale('fr')->translatedFormat('l j F Y à H:i')) }} <span class="text-gray-500 font-normal">(Kinshasa)</span></div>
                            <div class="text-xs text-gray-500">{{ $start->copy()->timezone($c->fuseau_horaire)->locale('fr')->translatedFormat('l j F à H:i') }} chez le parent ({{ $c->fuseau_horaire }})</div>
                        </div>
                        <button type="button" class="text-sm text-blue-600 hover:underline no-print"
                                onclick="document.getElementById('seance_programmee_at').value='{{ $start->copy()->timezone($kin)->format('Y-m-d\TH:i') }}'">
                            Retenir ce créneau
                        </button>
                    </li>
                @endforeach
            </ul>
            <p class="text-sm text-gray-600 mt-3">
                Plateforme souhaitée : <strong>{{ ConsultationRequest::PLATFORM_LABELS[$c->plateforme] ?? $c->plateforme }}</strong>
                - Langue : <strong>{{ $c->langue_seance }}</strong>
                @if($c->participants) - {{ $c->participants === 'parents_enfant' ? "Parents avec l'enfant" : 'Parents seuls' }} @endif
            </p>
        </section>

        <section class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Parent / responsable</h3>
            <table class="text-sm w-full">
                <tr><td class="{{ $dt }}">Nom</td><td class="{{ $dd }}">{{ $c->parent_nom_complet }} ({{ $liens[$c->parent_lien] ?? $c->parent_lien }})</td></tr>
                <tr><td class="{{ $dt }}">Résidence</td><td class="{{ $dd }}">{{ $c->ville }}, {{ $c->pays }}</td></tr>
                <tr><td class="{{ $dt }}">E-mail</td><td class="{{ $dd }}"><a href="mailto:{{ $c->email }}" class="text-blue-600 hover:underline">{{ $c->email }}</a></td></tr>
                <tr><td class="{{ $dt }}">WhatsApp</td><td class="{{ $dd }}">{{ $c->whatsapp }}</td></tr>
                @if($c->telephone)<tr><td class="{{ $dt }}">Autre téléphone</td><td class="{{ $dd }}">{{ $c->telephone }}</td></tr>@endif
                @if($c->contact2_nom)<tr><td class="{{ $dt }}">Second contact</td><td class="{{ $dd }}">{{ $c->contact2_nom }} {{ $c->contact2_telephone }}</td></tr>@endif
                @if($c->source_connaissance)<tr><td class="{{ $dt }}">A connu NLC par</td><td class="{{ $dd }}">{{ $c->source_connaissance }}</td></tr>@endif
            </table>
        </section>

        <section class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Enfant</h3>
            <table class="text-sm w-full">
                <tr><td class="{{ $dt }}">Nom complet</td><td class="{{ $dd }}">{{ $c->enfant_nom_complet }} ({{ $c->enfant_sexe === 'F' ? 'fille' : 'garçon' }})</td></tr>
                <tr><td class="{{ $dt }}">Date de naissance</td><td class="{{ $dd }}">{{ $c->enfant_date_naissance->format('d/m/Y') }} ({{ $c->enfant_date_naissance->age }} ans)</td></tr>
                @if($c->langues_maison)<tr><td class="{{ $dt }}">Langues à la maison</td><td class="{{ $dd }}">{{ $c->langues_maison }}</td></tr>@endif
                @if(!is_null($c->scolarise))
                    <tr><td class="{{ $dt }}">Scolarisé</td><td class="{{ $dd }}">{{ $c->scolarise ? 'Oui' : 'Non' }}@if($c->scolarise) - école {{ $c->type_ecole === 'specialisee' ? 'spécialisée' : 'ordinaire' }} {{ $c->classe }}@endif</td></tr>
                @endif
                @if($c->niveau_langage)<tr><td class="{{ $dt }}">Langage</td><td class="{{ $dd }}">{{ ConsultationRequest::LANGAGE_LABELS[$c->niveau_langage] ?? $c->niveau_langage }}</td></tr>@endif
            </table>
        </section>

        <section class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Situation</h3>
            <p class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 whitespace-pre-line mb-4">{{ $c->motif }}</p>
            <table class="text-sm w-full">
                @if($c->age_premieres_difficultes)<tr><td class="{{ $dt }}">Premières difficultés</td><td class="{{ $dd }}">{{ $c->age_premieres_difficultes }}</td></tr>@endif
                @if($diagTexte || !empty($diag['par_qui']))
                    <tr><td class="{{ $dt }}">Diagnostic(s) déjà posé(s)</td><td class="{{ $dd }}">
                        {{ $diagTexte ?: 'Non précisé' }}
                        @if(!empty($diag['par_qui'])) - par {{ $diag['par_qui'] }} @endif
                        @if(!empty($diag['quand'])) ({{ $diag['quand'] }}) @endif
                    </td></tr>
                @endif
                @if(!empty($c->suivis))<tr><td class="{{ $dt }}">Suivis</td><td class="{{ $dd }}">{{ implode(', ', $c->suivis) }}</td></tr>@endif
                @if($c->antecedents_familiaux)<tr><td class="{{ $dt }}">Antécédents familiaux</td><td class="{{ $dd }}">{{ ['oui' => 'Oui', 'non' => 'Non', 'ne_sait_pas' => 'Ne sait pas'][$c->antecedents_familiaux] }}</td></tr>@endif
                @if($c->naissance_particuliere)<tr><td class="{{ $dt }}">Grossesse / naissance</td><td class="{{ $dd }}">{{ $c->naissance_particuliere }}</td></tr>@endif
                @if($c->traitement_actuel)<tr><td class="{{ $dt }}">Traitement actuel</td><td class="{{ $dd }}">{{ $c->traitement_actuel }}</td></tr>@endif
                @foreach(ConsultationRequest::OBSERVATION_LABELS as $key => $obsLabel)
                    @if(!empty($c->observations[$key]))
                        <tr><td class="{{ $dt }}">{{ $obsLabel }}</td><td class="{{ $dd }}">{{ ucfirst($c->observations[$key]) }}</td></tr>
                    @endif
                @endforeach
                @if($c->attentes)<tr><td class="{{ $dt }}">Attentes</td><td class="{{ $dd }}">{{ $c->attentes }}</td></tr>@endif
            </table>
        </section>

        <section class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Documents</h3>
            @if(empty($c->documents))
                <p class="text-sm text-gray-500">Aucun document. L'avis du psychiatre doit être transmis après l'inscription.</p>
            @else
                <ul class="divide-y text-sm">
                    @foreach($c->documents as $i => $doc)
                        <li class="flex items-center justify-between py-2">
                            <span class="text-gray-800">{{ $doc['name'] }} <span class="text-gray-400">({{ number_format($doc['size'] / 1024 / 1024, 1) }} Mo)</span></span>
                            <a href="{{ route('admin.consultations.document', [$c, $i]) }}" class="text-blue-600 hover:underline">Télécharger</a>
                        </li>
                    @endforeach
                </ul>
            @endif
            <p class="text-xs text-gray-400 mt-3">Consentements donnés le {{ $c->consented_at?->timezone($kin)->format('d/m/Y à H:i') }}.</p>
        </section>
    </div>

    {{-- ── Traitement par le secrétariat ───────────────────────── --}}
    <div class="xl:col-span-2">
        <div class="xl:sticky xl:top-0 space-y-6">
            <form method="POST" action="{{ route('admin.consultations.update', $c) }}" class="bg-white rounded-xl shadow p-6 space-y-4 no-print">
                @csrf
                @method('PUT')
                <h3 class="text-lg font-bold text-gray-800">Traitement</h3>

                <div>
                    <label for="status" class="{{ $label }}">Statut</label>
                    <select id="status" name="status" class="{{ $input }}">
                        @foreach(ConsultationRequest::STATUS_LABELS as $key => $statusLabel)
                            <option value="{{ $key }}" @selected(old('status', $c->status) === $key)>{{ $statusLabel }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="professionnel_assigne" class="{{ $label }}">Professionnel assigné</label>
                    <input id="professionnel_assigne" name="professionnel_assigne" value="{{ old('professionnel_assigne', $c->professionnel_assigne) }}"
                           placeholder="Ex. Dr Kabongo, psychologue" class="{{ $input }}">
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label for="seance_programmee_at" class="{{ $label }}">Date et heure (Kinshasa)</label>
                        <input type="datetime-local" id="seance_programmee_at" name="seance_programmee_at"
                               value="{{ old('seance_programmee_at', $c->seance_programmee_at?->timezone($kin)->format('Y-m-d\TH:i')) }}" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="duree_minutes" class="{{ $label }}">Durée (min)</label>
                        <input type="number" id="duree_minutes" name="duree_minutes" min="10" max="240" step="5"
                               value="{{ old('duree_minutes', $c->duree_minutes ?? 45) }}" class="{{ $input }}">
                    </div>
                </div>
                @if($seance)
                    <p class="text-xs text-gray-500 -mt-2">Chez le parent : {{ $seance['parent'] }} ({{ $c->fuseau_horaire }})</p>
                @endif

                <div>
                    <label for="lien_visio" class="{{ $label }}">Lien Zoom ou Google Meet</label>
                    <input type="url" id="lien_visio" name="lien_visio" value="{{ old('lien_visio', $c->lien_visio) }}"
                           placeholder="https://meet.google.com/…" class="{{ $input }}">
                    <p class="text-xs text-gray-500 mt-1">Créez la réunion dans Zoom ou Meet, puis collez son lien ici.</p>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label for="prix" class="{{ $label }}">Tarif</label>
                        <input type="number" id="prix" name="prix" min="0" step="0.01" value="{{ old('prix', $c->prix) }}" placeholder="0 = gratuit" class="{{ $input }}">
                    </div>
                    <div>
                        <label for="devise" class="{{ $label }}">Devise</label>
                        <select id="devise" name="devise" class="{{ $input }}">
                            @foreach(['USD', 'CDF', 'EUR'] as $devise)
                                <option @selected(old('devise', $c->devise ?? 'USD') === $devise)>{{ $devise }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="statut_paiement" class="{{ $label }}">Paiement</label>
                        <select id="statut_paiement" name="statut_paiement" class="{{ $input }}">
                            <option value="">Auto</option>
                            @foreach(ConsultationRequest::PAYMENT_STATUSES as $key => $paymentLabel)
                                <option value="{{ $key }}" @selected(old('statut_paiement', $c->statut_paiement) === $key)>{{ $paymentLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="notes_internes" class="{{ $label }}">Notes internes <span class="text-gray-400 font-normal">(jamais visibles par le parent)</span></label>
                    <textarea id="notes_internes" name="notes_internes" rows="4" class="{{ $input }}">{{ old('notes_internes', $c->notes_internes) }}</textarea>
                </div>

                <div class="flex flex-col gap-2 pt-2">
                    <button type="submit" name="action" value="notify"
                            onclick="return confirm('Envoyer la confirmation du rendez-vous à {{ $c->email }} ?')"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-lg transition">
                        {{ $c->confirmation_envoyee_at ? 'Enregistrer et renvoyer la confirmation' : 'Enregistrer et confirmer le RDV au parent' }}
                    </button>
                    <button type="submit" name="action" value="save"
                            class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2.5 rounded-lg transition">
                        Enregistrer seulement
                    </button>
                </div>
            </form>

            <div class="bg-white rounded-xl shadow p-6 no-print">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Contacter le parent</h3>
                <div class="flex flex-col gap-2">
                    @if($c->lienWhatsApp())
                        <a href="{{ $c->lienWhatsApp($messageWhatsApp) }}" target="_blank" rel="noopener"
                           class="w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2.5 rounded-lg transition">
                            {{ $seance && $c->lien_visio ? 'Envoyer la confirmation sur WhatsApp' : 'Écrire sur WhatsApp' }}
                        </a>
                    @endif
                    <a href="mailto:{{ $c->email }}?subject={{ rawurlencode('Votre demande ' . $c->reference) }}"
                       class="w-full text-center bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-4 py-2.5 rounded-lg transition">
                        Écrire un e-mail
                    </a>
                </div>
                <p class="text-xs text-gray-500 mt-3">WhatsApp s'ouvre avec un message pré-rempli : vérifiez-le avant de l'envoyer.</p>
            </div>
        </div>
    </div>
</div>
@endsection
