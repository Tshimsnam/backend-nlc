{{-- Onglet Évaluation — Résultats + Gestion questions TSA --}}
@php
    $labelAdequation = ['tres_adequat'=>'Très en adéquation','adequat'=>'En adéquation','neutre'=>'Neutre','pas_vraiment'=>'Pas vraiment','pas_du_tout'=>'Pas du tout'];
    $letters = ['A','B','C','D','E','F'];
    $barColors = ['A'=>'bg-purple-400','B'=>'bg-blue-400','C'=>'bg-green-400','D'=>'bg-amber-400','E'=>'bg-pink-400','F'=>'bg-gray-400'];
@endphp

{{-- Toast --}}
@if(session('success'))
<div id="toast-ok" class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-lg max-w-sm">
    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span class="text-sm font-semibold">{{ session('success') }}</span>
</div>
<script>setTimeout(()=>document.getElementById('toast-ok')?.remove(),3500)</script>
@endif

{{-- Filtre événement --}}
<div class="bg-white rounded-xl border border-purple-100 shadow-sm p-4 mb-6 no-print">
    <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" value="evaluation">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Événement</label>
            <select name="eval_event_id" class="w-full border border-purple-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-purple-50">
                <option value="">— Tous les événements —</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" {{ ($evalEventId ?? '') == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition">Filtrer</button>
        @if($evalEventId ?? false)
        <a href="{{ route('admin.dashboard.view', ['tab'=>'evaluation']) }}" class="text-xs text-gray-400 hover:text-gray-600 py-2">Réinitialiser</a>
        @endif
        <a href="{{ route('admin.evaluations.print-list', array_filter(['eval_event_id' => $evalEventId ?? ''])) }}" target="_blank"
           class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-semibold transition ml-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer la liste
        </a>
    </form>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-green-100 p-5 text-center shadow-sm">
        <p class="text-3xl font-black text-green-700">{{ $evaluationStats['total'] }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Évaluations</p>
    </div>
    <div class="bg-white rounded-xl border border-blue-100 p-5 text-center shadow-sm">
        <p class="text-3xl font-black text-blue-700">{{ $evaluationStats['noteAvg'] ?? '—' }}<span class="text-lg font-normal">/10</span></p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Note moyenne</p>
    </div>
    <div class="bg-white rounded-xl border border-purple-100 p-5 text-center shadow-sm">
        <p class="text-3xl font-black text-purple-700">{{ $evaluationStats['evalQuestions']->count() }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Questions TSA</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 p-5 text-center shadow-sm">
        <p class="text-sm font-bold text-gray-700">GSA 2026</p>
        <p class="text-xs text-gray-400 mt-1">Colloque autisme</p>
    </div>
</div>

{{-- Stats profil + adéquation --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Profils des participants</p>
        @php $totalProfil = $evaluationStats['byProfil']->sum('count'); @endphp
        <div class="space-y-2">
            @forelse($evaluationStats['byProfil'] as $row)
            @php $pct = $totalProfil > 0 ? round($row->count/$totalProfil*100) : 0; @endphp
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600 w-28 truncate">{{ $row->profil ?? 'Non renseigné' }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:linear-gradient(90deg,#7c3aed,#ec4899)"></div>
                </div>
                <span class="text-xs text-gray-500 w-14 text-right">{{ $row->count }} ({{ $pct }}%)</span>
            </div>
            @empty
            <p class="text-gray-400 text-xs italic">Aucune donnée.</p>
            @endforelse
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">Adéquation du thème</p>
        @php $totalAdeq = $evaluationStats['byAdequation']->sum('count'); @endphp
        <div class="space-y-2">
            @forelse($evaluationStats['byAdequation'] as $row)
            @php $pct = $totalAdeq > 0 ? round($row->count/$totalAdeq*100) : 0; @endphp
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600 w-36 truncate">{{ $labelAdequation[$row->adequation_theme] ?? $row->adequation_theme }}</span>
                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 bg-blue-400 rounded-full" style="width:{{ $pct }}%"></div>
                </div>
                <span class="text-xs text-gray-500 w-14 text-right">{{ $row->count }} ({{ $pct }}%)</span>
            </div>
            @empty
            <p class="text-gray-400 text-xs italic">Aucune donnée.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════ --}}
{{-- Gestion des questions TSA                                         --}}
{{-- ══════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl border border-purple-100 shadow-sm p-6 mb-6">
    <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-6">
        Questions TSA — Gestion & Résultats
    </p>

    {{-- ── Formulaire d'ajout ── --}}
    <form method="POST" action="{{ route('admin.eval.questions.store') }}"
          class="mb-6 p-5 bg-purple-50 rounded-2xl border border-purple-100" id="eval-add-form">
        @csrf
        <p class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-4">➕ Nouvelle question QCM</p>

        <div class="mb-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Événement associé</label>
            <select name="event_id" class="w-full border border-purple-200 rounded-xl px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                <option value="">— Général (tous événements) —</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" {{ ($evalEventId ?? '') == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Texte de la question</label>
            <textarea name="text" rows="2" required placeholder="Ex : Les personnes avec un TSA présentent…"
                class="w-full border border-purple-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white"></textarea>
        </div>

        <label class="block text-xs font-semibold text-gray-600 mb-2">
            Options de réponse
            <span class="text-gray-400 font-normal ml-1">(cochez la bonne réponse)</span>
        </label>

        <div id="add-options-list" class="space-y-2 mb-3">
            @foreach(['A','B','C','D'] as $i => $letter)
            <div class="flex items-center gap-2 option-row">
                <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0" title="Bonne réponse">
                    <input type="radio" name="correct_answer" value="{{ $letter }}"
                           class="w-4 h-4 accent-purple-600">
                    <span class="text-xs font-bold text-purple-700 w-5">{{ $letter }})</span>
                </label>
                <input type="text" name="options[]" required placeholder="Option {{ $letter }}…"
                    class="flex-1 border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                @if($i >= 2)
                <button type="button" onclick="removeOption(this)"
                    class="text-red-400 hover:text-red-600 p-1 flex-shrink-0" title="Supprimer cette option">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                @else
                <span class="w-6 flex-shrink-0"></span>
                @endif
            </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            <button type="button" onclick="addOption('add-options-list')"
                class="text-xs text-purple-600 hover:text-purple-800 font-semibold flex items-center gap-1 border border-purple-200 rounded-lg px-3 py-1.5 hover:bg-purple-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une option
            </button>
            <button type="submit"
                class="ml-auto bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition shadow-sm">
                Enregistrer la question
            </button>
        </div>
    </form>

    {{-- ── Liste des questions existantes ── --}}
    <div class="space-y-5">
        @forelse($evaluationStats['evalQuestions'] as $q)
        @php
            $tsaKey = 'tsa_q' . $loop->iteration;
            $data   = $evaluationStats['tsaStats'][$tsaKey] ?? collect();
            $total  = (int) $data->sum();
        @endphp
        <div class="border-2 border-gray-100 rounded-2xl overflow-hidden" id="eval-card-{{ $q->id }}">

            {{-- ── Vue normale ── --}}
            <div class="view-eval-{{ $q->id }} p-4">
                <div class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full bg-purple-100 text-purple-700 text-xs font-black flex items-center justify-center flex-shrink-0 mt-0.5">
                        {{ $loop->iteration }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 mb-3">{{ $q->text }}</p>

                        {{-- Options avec bonne réponse mise en évidence --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mb-3">
                            @foreach($q->options as $i => $opt)
                            @php
                                $letter    = $letters[$i] ?? chr(65+$i);
                                $isCorrect = $q->correct_answer === $letter;
                                $cnt       = (int) ($data[$letter] ?? 0);
                                $pct       = $total > 0 ? round($cnt/$total*100) : 0;
                            @endphp
                            <div class="flex items-center gap-2 rounded-lg px-3 py-2 {{ $isCorrect ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-100' }}">
                                <span class="text-xs font-black {{ $isCorrect ? 'text-green-600' : 'text-gray-400' }} w-5 flex-shrink-0">
                                    {{ $letter }})
                                </span>
                                <span class="text-xs {{ $isCorrect ? 'text-green-700 font-semibold' : 'text-gray-600' }} flex-1 truncate">
                                    {{ $opt }}
                                </span>
                                @if($isCorrect)
                                <svg class="w-3.5 h-3.5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                @endif
                                @if($total > 0)
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-1">{{ $pct }}%</span>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        {{-- Barres de résultats --}}
                        @if($total > 0)
                        <div class="flex gap-2 items-end h-10">
                            @foreach($q->options as $i => $opt)
                            @php
                                $letter = $letters[$i] ?? chr(65+$i);
                                $cnt    = $data[$letter] ?? 0;
                                $pct    = $total > 0 ? round($cnt/$total*100) : 0;
                                $color  = $barColors[$letter] ?? 'bg-gray-300';
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-0.5">
                                <span class="text-xs text-gray-400">{{ $pct }}%</span>
                                <div class="w-full bg-gray-100 rounded-t-md overflow-hidden" style="height:32px">
                                    <div class="{{ $color }} w-full rounded-t-md transition-all" style="height:{{ max($pct,4) }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ ($q->correct_answer===$letter)?'text-green-600':'text-gray-400' }}">{{ $letter }}</span>
                            </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $total }} réponse(s)</p>
                        @else
                        <p class="text-xs text-gray-400 italic">Aucune réponse enregistrée.</p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-1 flex-shrink-0">
                        <button onclick="toggleEval({{ $q->id }})"
                            class="p-2 rounded-lg hover:bg-blue-50 text-blue-500 transition" title="Modifier">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <form method="POST" action="{{ route('admin.eval.questions.delete', $q->id) }}"
                              onsubmit="return confirm('Supprimer cette question ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-red-400 transition" title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ── Formulaire d'édition (caché) ── --}}
            <div class="edit-eval-{{ $q->id }} hidden bg-purple-50 border-t-2 border-purple-100 p-4">
                <form method="POST" action="{{ route('admin.eval.questions.update', $q->id) }}">
                    @csrf @method('PUT')
                    <p class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-3">Modifier la question</p>

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Texte</label>
                        <textarea name="text" rows="2" required
                            class="w-full border border-purple-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">{{ $q->text }}</textarea>
                    </div>

                    <label class="block text-xs font-semibold text-gray-600 mb-2">
                        Options
                        <span class="text-gray-400 font-normal ml-1">(cochez la bonne réponse)</span>
                    </label>

                    <div id="edit-options-{{ $q->id }}" class="space-y-2 mb-3">
                        @foreach($q->options as $i => $opt)
                        @php $letter = $letters[$i] ?? chr(65+$i); @endphp
                        <div class="flex items-center gap-2 option-row">
                            <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0">
                                <input type="radio" name="correct_answer" value="{{ $letter }}"
                                       class="w-4 h-4 accent-purple-600"
                                       {{ $q->correct_answer === $letter ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-purple-700 w-5">{{ $letter }})</span>
                            </label>
                            <input type="text" name="options[]" value="{{ $opt }}" required
                                class="flex-1 border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                            @if($i >= 2)
                            <button type="button" onclick="removeOption(this)"
                                class="text-red-400 hover:text-red-600 p-1 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            @else
                            <span class="w-6 flex-shrink-0"></span>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <button type="button" onclick="addOption('edit-options-{{ $q->id }}')"
                            class="text-xs text-purple-600 hover:text-purple-800 font-semibold flex items-center gap-1 border border-purple-200 rounded-lg px-3 py-1.5 hover:bg-white transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter une option
                        </button>
                        <div class="ml-auto flex gap-2">
                            <button type="button" onclick="toggleEval({{ $q->id }})"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-gray-500 text-xs font-semibold hover:bg-white transition">
                                Annuler
                            </button>
                            <button type="submit"
                                class="px-4 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold transition shadow-sm">
                                Sauvegarder
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @empty
        <div class="text-center py-10 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm italic">Aucune question TSA. Utilisez le formulaire ci-dessus pour en ajouter.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Dernières évaluations --}}
@if($evaluationStats['recent']->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
    <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-4">
        Dernières évaluations
    </p>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="pb-2 text-left text-xs text-gray-400 font-semibold uppercase">Nom</th>
                    <th class="pb-2 text-left text-xs text-gray-400 font-semibold uppercase">Profil</th>
                    <th class="pb-2 text-center text-xs text-gray-400 font-semibold uppercase">Note</th>
                    <th class="pb-2 text-right text-xs text-gray-400 font-semibold uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($evaluationStats['recent'] as $ev)
                <tr class="hover:bg-purple-50 cursor-pointer" onclick="window.location='{{ route('admin.evaluation.show', $ev->id) }}'">
                    <td class="py-2 font-medium text-gray-800">{{ $ev->full_name ?? '—' }}</td>
                    <td class="py-2 text-gray-500 text-xs">{{ $ev->profil ?? '—' }}</td>
                    <td class="py-2 text-center">
                        @if($ev->note_globale)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold
                                {{ $ev->note_globale >= 7 ? 'bg-green-100 text-green-700' : ($ev->note_globale >= 5 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $ev->note_globale }}/10
                            </span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="py-2 text-right text-gray-400 text-xs">
                        {{ \Carbon\Carbon::parse($ev->created_at)->format('d/m/Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<script>
// Lettres disponibles pour les options
const LETTERS = ['A','B','C','D','E','F'];

function getNextLetter(container) {
    const rows = container.querySelectorAll('.option-row');
    return LETTERS[rows.length] ?? String.fromCharCode(65 + rows.length);
}

function addOption(containerId) {
    const container = document.getElementById(containerId);
    const letter    = getNextLetter(container);
    if (!letter) return; // max 6 options

    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 option-row';
    row.innerHTML = `
        <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0">
            <input type="radio" name="correct_answer" value="${letter}" class="w-4 h-4 accent-purple-600">
            <span class="text-xs font-bold text-purple-700 w-5">${letter})</span>
        </label>
        <input type="text" name="options[]" placeholder="Option ${letter}…" required
            class="flex-1 border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
        <button type="button" onclick="removeOption(this)"
            class="text-red-400 hover:text-red-600 p-1 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;
    container.appendChild(row);
}

function removeOption(btn) {
    const row = btn.closest('.option-row');
    const container = row.parentElement;
    if (container.querySelectorAll('.option-row').length <= 2) {
        alert('Il faut au minimum 2 options.');
        return;
    }
    row.remove();
    // Renuméroter les lettres
    container.querySelectorAll('.option-row').forEach((r, i) => {
        const letter = LETTERS[i];
        const radio  = r.querySelector('input[type=radio]');
        const label  = r.querySelector('span.text-purple-700');
        const text   = r.querySelector('input[type=text]');
        if (radio)  radio.value = letter;
        if (label)  label.textContent = letter + ')';
        if (text && !text.value) text.placeholder = 'Option ' + letter + '…';
    });
}

function toggleEval(id) {
    document.querySelector('.view-eval-' + id)?.classList.toggle('hidden');
    document.querySelector('.edit-eval-' + id)?.classList.toggle('hidden');
}
</script>
