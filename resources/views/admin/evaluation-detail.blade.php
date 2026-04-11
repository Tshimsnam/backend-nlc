<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation — {{ $evaluation->full_name ?? 'Anonyme' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="bg-gray-50 py-8 px-4">
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6 no-print">
        <a href="{{ route('admin.dashboard.view', ['tab' => 'evaluation']) }}"
           class="flex items-center gap-2 text-sm text-gray-500 hover:text-purple-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour aux évaluations
        </a>
        <button onclick="window.print()"
            class="flex items-center gap-2 text-sm bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>

    {{-- Cover --}}
    <div class="bg-gradient-to-r from-purple-600 to-pink-500 rounded-2xl p-6 text-white mb-6">
        <p class="text-xs font-bold uppercase tracking-widest text-purple-200 mb-1">GSA 2026 — Évaluation du colloque</p>
        <h1 class="text-2xl font-black mb-1">{{ $evaluation->full_name ?? 'Participant anonyme' }}</h1>
        <div class="flex flex-wrap gap-3 text-sm mt-2">
            @if($evaluation->profil)
                <span class="bg-white bg-opacity-20 rounded-lg px-3 py-1">{{ $evaluation->profil }}</span>
            @endif
            @if($evaluation->etablissement)
                <span class="bg-white bg-opacity-20 rounded-lg px-3 py-1">{{ $evaluation->etablissement }}</span>
            @endif
            @if($evaluation->note_globale)
                <span class="bg-white font-black text-purple-700 rounded-lg px-3 py-1">
                    ⭐ {{ $evaluation->note_globale }}/10
                </span>
            @endif
            <span class="bg-white bg-opacity-10 rounded-lg px-3 py-1 text-purple-200 text-xs">
                {{ \Carbon\Carbon::parse($evaluation->created_at)->format('d/m/Y à H:i') }}
            </span>
        </div>
    </div>

    @php
        $labelAdequation = ['tres_adequat'=>'Très en adéquation','adequat'=>'En adéquation','neutre'=>'Neutre','pas_vraiment'=>'Pas vraiment en adéquation','pas_du_tout'=>'Pas du tout en adéquation'];
        $labelClarte     = ['excellente'=>'Excellente','tres_bonne'=>'Très bonne','bonne'=>'Bonne','acceptable'=>'Acceptable','insatisfaisante'=>'Insatisfaisante'];
        $labelAttention  = ['toujours'=>'Toujours','souvent'=>'Souvent','parfois'=>'Parfois','jamais'=>'Jamais'];
        $labelOrga       = ['excellente'=>'Excellente','tres_bonne'=>'Très bonne','bonne'=>'Bonne','acceptable'=>'Acceptable','a_ameliorer'=>'À améliorer'];
        $labelHoraires   = ['toujours'=>'Toujours','la_plupart'=>'La plupart du temps','parfois'=>'Parfois','rarement'=>'Rarement','jamais'=>'Jamais'];
        $labelInteraction= ['beaucoup'=>'Beaucoup d\'opportunités','quelques'=>'Quelques opportunités','peu'=>'Peu d\'opportunités','aucune'=>'Aucune opportunité'];
        $tsaKeys         = ['tsa_q1','tsa_q2','tsa_q3','tsa_q4','tsa_q5'];
    @endphp

    {{-- Section 1 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 1 — Informations personnelles</p>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-0.5">Contact</span><span class="font-medium text-gray-800">{{ $evaluation->contact ?? '—' }}</span></div>
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-0.5">Date du colloque</span><span class="font-medium text-gray-800">{{ $evaluation->date_colloque ? \Carbon\Carbon::parse($evaluation->date_colloque)->format('d/m/Y') : '—' }}</span></div>
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-0.5">Durée de la session</span><span class="font-medium text-gray-800">{{ $evaluation->duree_session ?? '—' }}</span></div>
            @if($evaluation->profil_autre)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-0.5">Précision profil</span><span class="font-medium text-gray-800">{{ $evaluation->profil_autre }}</span></div>
            @endif
        </div>
    </div>

    {{-- Section 2 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 2 — Contenu du colloque</p>
        <div class="space-y-4 text-sm">
            <div>
                <span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Adéquation du thème</span>
                <span class="inline-block px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold text-xs">
                    {{ $labelAdequation[$evaluation->adequation_theme] ?? $evaluation->adequation_theme ?? '—' }}
                </span>
            </div>
            @if($evaluation->aspects_pertinents)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Aspects les plus pertinents</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->aspects_pertinents }}</p></div>
            @endif
            @if($evaluation->sujets_manquants)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Sujets souhaités non abordés</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->sujets_manquants }}</p></div>
            @endif
        </div>
    </div>

    {{-- Section 3 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 3 — Qualité des présentations</p>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Clarté des présentations</span>
                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">{{ $labelClarte[$evaluation->clarte_presentations] ?? $evaluation->clarte_presentations ?? '—' }}</span></div>
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Maintien de l'attention</span>
                <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs">{{ $labelAttention[$evaluation->maintien_attention] ?? $evaluation->maintien_attention ?? '—' }}</span></div>
        </div>
    </div>

    {{-- Section 4 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 4 — Organisation & logistique</p>
        <div class="space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4">
                <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Organisation générale</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-xs">{{ $labelOrga[$evaluation->organisation_generale] ?? $evaluation->organisation_generale ?? '—' }}</span></div>
                <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Respect des horaires</span>
                    <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-700 font-semibold text-xs">{{ $labelHoraires[$evaluation->respect_horaires] ?? $evaluation->respect_horaires ?? '—' }}</span></div>
            </div>
            @if($evaluation->logistique_commentaire)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Commentaire logistique</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->logistique_commentaire }}</p></div>
            @endif
        </div>
    </div>

    {{-- Section 5 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 5 — Interaction & Networking</p>
        <div class="space-y-4 text-sm">
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Opportunités d'interaction</span>
                <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold text-xs">{{ $labelInteraction[$evaluation->opportunites_interaction] ?? $evaluation->opportunites_interaction ?? '—' }}</span></div>
            @if($evaluation->contacts_professionnels)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Contacts professionnels établis</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->contacts_professionnels }}</p></div>
            @endif
        </div>
    </div>

    {{-- Section 6 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 6 — Retour sur l'apprentissage</p>
        <div class="space-y-4 text-sm">
            @if($evaluation->enseignements_tires)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Principaux enseignements</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->enseignements_tires }}</p></div>
            @endif
            @if($evaluation->application_enseignements)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Application prévue</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->application_enseignements }}</p></div>
            @endif
        </div>
    </div>

    {{-- Section 7 --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 7 — Évaluation globale</p>
        <div class="space-y-4 text-sm">
            @if($evaluation->note_globale)
            <div>
                <span class="text-gray-400 text-xs uppercase tracking-wide block mb-2">Note globale</span>
                <div class="flex gap-1">
                    @for($i = 1; $i <= 10; $i++)
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold
                        {{ $i <= $evaluation->note_globale ? 'bg-gradient-to-br from-purple-500 to-pink-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                        {{ $i }}
                    </div>
                    @endfor
                </div>
            </div>
            @endif
            @if($evaluation->points_forts)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Points forts</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->points_forts }}</p></div>
            @endif
            @if($evaluation->suggestions_amelioration)
            <div><span class="text-gray-400 text-xs uppercase tracking-wide block mb-1">Suggestions d'amélioration</span>
                <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3">{{ $evaluation->suggestions_amelioration }}</p></div>
            @endif
        </div>
    </div>

    {{-- Section 8 --}}
    @if($evaluation->commentaires_additionnels)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-4">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 8 — Commentaires additionnels</p>
        <p class="text-gray-700 leading-relaxed bg-gray-50 rounded-xl p-3 text-sm">{{ $evaluation->commentaires_additionnels }}</p>
    </div>
    @endif

    {{-- Section 9 — Quiz TSA --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 mb-6">
        <p class="text-xs font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Section 9 — Quiz TSA</p>
        @if($tsaQuestions->isEmpty())
            <p class="text-gray-400 text-sm italic">Aucune question TSA configurée.</p>
        @else
        <div class="space-y-4">
            @foreach($tsaQuestions as $i => $q)
            @php
                $tsaKey = 'tsa_q' . ($i + 1);
                $answer = $evaluation->$tsaKey ?? null;
                $letters = ['A','B','C','D','E','F'];
                $isCorrect = $answer && $answer === $q->correct_answer;
            @endphp
            <div class="border border-gray-100 rounded-xl p-4">
                <p class="text-sm font-semibold text-gray-800 mb-3">{{ $i + 1 }}. {{ $q->text }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                    @foreach($q->options as $j => $opt)
                    @php
                        $letter    = $letters[$j] ?? chr(65+$j);
                        $chosen    = $answer === $letter;
                        $correct   = $q->correct_answer === $letter;
                    @endphp
                    <div class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm
                        {{ $chosen && $correct  ? 'bg-green-50 border-2 border-green-400' :
                           ($chosen && !$correct ? 'bg-red-50 border-2 border-red-300' :
                           ($correct             ? 'bg-green-50 border border-green-200' :
                                                   'bg-gray-50 border border-gray-100')) }}">
                        <span class="font-black text-xs w-5 flex-shrink-0
                            {{ $correct ? 'text-green-600' : 'text-gray-400' }}">{{ $letter }})</span>
                        <span class="flex-1 {{ $chosen ? 'font-semibold' : '' }}
                            {{ $chosen && $correct ? 'text-green-700' : ($chosen && !$correct ? 'text-red-600' : 'text-gray-600') }}">
                            {{ $opt }}
                        </span>
                        @if($chosen && $correct)
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        @elseif($chosen && !$correct)
                            <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        @endif
                    </div>
                    @endforeach
                </div>
                @if(!$answer)
                    <p class="text-xs text-gray-400 italic mt-2">Pas de réponse fournie.</p>
                @elseif($isCorrect)
                    <p class="text-xs text-green-600 font-semibold mt-2">✓ Bonne réponse</p>
                @else
                    <p class="text-xs text-red-500 mt-2">✗ Mauvaise réponse — bonne réponse : <strong>{{ $q->correct_answer }}</strong></p>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="text-center text-xs text-gray-400 mb-8">
        GSA 2026 — Never Limit Children &nbsp;·&nbsp; Évaluation #{{ $evaluation->id }}
    </div>
</div>
</body>
</html>
