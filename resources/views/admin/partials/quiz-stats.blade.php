{{-- Onglet Quiz — Résultats + Gestion des questions --}}

@if(session('success'))
<div id="toast-ok" class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-lg">
    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span class="text-sm font-semibold">{{ session('success') }}</span>
</div>
<script>setTimeout(()=>document.getElementById('toast-ok')?.remove(),3500)</script>
@endif

{{-- Filtre événement --}}
<div class="bg-white rounded-xl border border-purple-100 shadow-sm p-4 mb-6 no-print">
    <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex flex-wrap items-end gap-3">
        <input type="hidden" name="tab" value="quiz">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Événement</label>
            <select name="quiz_event_id" class="w-full border border-purple-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-purple-50">
                <option value="">— Tous les événements —</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" {{ ($quizEventId ?? '') == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition">Filtrer</button>
        @if($quizEventId ?? false)
        <a href="{{ route('admin.dashboard.view', ['tab'=>'quiz']) }}" class="text-xs text-gray-400 hover:text-gray-600 py-2">Réinitialiser</a>
        @endif
    </form>
</div>

{{-- KPIs --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-purple-100 p-5 text-center shadow-sm">
        <p class="text-3xl font-black text-purple-700">{{ $quizStats['total'] }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Participants</p>
    </div>
    <div class="bg-white rounded-xl border border-purple-100 p-5 text-center shadow-sm">
        <p class="text-3xl font-black text-pink-600">{{ $quizStats['questions']->count() }}</p>
        <p class="text-xs text-gray-400 uppercase tracking-wide mt-1">Questions actives</p>
    </div>
    <div class="bg-white rounded-xl border border-purple-100 p-5 text-center shadow-sm">
        @if($quizEventId ?? false)
            @php $ev = $events->firstWhere('id', $quizEventId); @endphp
            <p class="text-sm font-bold text-purple-700 truncate">{{ $ev->title ?? 'Événement' }}</p>
        @else
            <p class="text-sm font-bold text-gray-700">Tous événements</p>
        @endif
        <p class="text-xs text-gray-400 mt-1">Quiz sur l'autisme</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

{{-- Gestion des questions --}}
<div class="bg-white rounded-xl border border-purple-100 shadow-sm p-6">
    <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-5">Gestion des questions</p>

    <form method="POST" action="{{ route('admin.quiz.questions.store') }}" class="mb-5 p-4 bg-purple-50 rounded-xl border border-purple-100">
        @csrf
        <p class="text-xs font-bold text-purple-600 uppercase tracking-wide mb-3">Nouvelle question</p>
        <div class="mb-3">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Événement associé</label>
            <select name="event_id" class="w-full border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 bg-white">
                <option value="">— Général (tous événements) —</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" {{ ($quizEventId ?? '') == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                @endforeach
            </select>
        </div>
        <textarea name="text" rows="2" required placeholder="Texte de la question…"
            class="w-full border border-purple-200 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-purple-400"></textarea>
        <div class="flex items-center gap-3">
            <label class="text-xs font-semibold text-gray-600">Bonne réponse :</label>
            <select name="correct_answer" required class="border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                <option value="vrai">✅ Vrai</option>
                <option value="faux">❌ Faux</option>
                <option value="peut_etre">🤔 Peut-être</option>
            </select>
            <button type="submit" class="ml-auto bg-purple-600 hover:bg-purple-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition">+ Ajouter</button>
        </div>
    </form>

    <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
        @forelse($quizStats['questions'] as $q)
        <div class="border border-gray-100 rounded-xl p-3">
            <div class="view-mode-{{ $q->id }}">
                <div class="flex items-start gap-2">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">{{ $loop->iteration }}</span>
                    <div class="flex-1">
                        <p class="text-sm text-gray-700">{{ $q->text }}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            @php $colors = ['vrai'=>'bg-green-100 text-green-700','faux'=>'bg-red-100 text-red-700','peut_etre'=>'bg-amber-100 text-amber-700']; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $colors[$q->correct_answer] ?? 'bg-gray-100 text-gray-500' }}">
                                ✓ {{ ucfirst(str_replace('_',' ',$q->correct_answer)) }}
                            </span>
                            @if($q->event_id)
                            @php $evName = $events->firstWhere('id', $q->event_id)?->title ?? ''; @endphp
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-600">{{ $evName }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-1 flex-shrink-0">
                        <button onclick="toggleEdit({{ $q->id }})" class="p-1.5 rounded-lg hover:bg-blue-50 text-blue-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <form method="POST" action="{{ route('admin.quiz.questions.delete', $q->id) }}" onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-red-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="edit-mode-{{ $q->id }} hidden">
                <form method="POST" action="{{ route('admin.quiz.questions.update', $q->id) }}">
                    @csrf @method('PUT')
                    <select name="event_id" class="w-full border border-purple-200 rounded-lg px-3 py-1.5 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-purple-400">
                        <option value="">— Général —</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" {{ $q->event_id == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                        @endforeach
                    </select>
                    <textarea name="text" rows="2" required class="w-full border border-purple-200 rounded-lg px-3 py-2 text-sm mb-2 focus:outline-none focus:ring-2 focus:ring-purple-400">{{ $q->text }}</textarea>
                    <div class="flex items-center gap-3">
                        <select name="correct_answer" required class="border border-purple-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400">
                            <option value="vrai"      {{ $q->correct_answer=='vrai'      ?'selected':'' }}>✅ Vrai</option>
                            <option value="faux"      {{ $q->correct_answer=='faux'      ?'selected':'' }}>❌ Faux</option>
                            <option value="peut_etre" {{ $q->correct_answer=='peut_etre' ?'selected':'' }}>🤔 Peut-être</option>
                        </select>
                        <button type="submit" class="ml-auto bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">Sauvegarder</button>
                        <button type="button" onclick="toggleEdit({{ $q->id }})" class="text-gray-400 text-xs">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
        @empty
            <p class="text-gray-400 text-sm italic text-center py-4">Aucune question. Ajoutez-en une ci-dessus.</p>
        @endforelse
    </div>
</div>

{{-- Résultats --}}
<div class="bg-white rounded-xl border border-purple-100 shadow-sm p-6">
    <p class="text-sm font-bold uppercase tracking-wide text-purple-700 border-l-4 border-pink-400 pl-3 mb-5">Résultats — L'autisme est…</p>
    <div class="space-y-4 max-h-[620px] overflow-y-auto pr-1">
        @forelse($quizStats['questions'] as $q)
        @php
            $data = $quizStats['byQuestion'][$q->id] ?? collect();
            $vrai = $data['vrai'] ?? 0; $faux = $data['faux'] ?? 0; $peut = $data['peut_etre'] ?? 0;
            $tot  = $vrai + $faux + $peut;
            $pV   = $tot > 0 ? round($vrai/$tot*100) : 0;
            $pF   = $tot > 0 ? round($faux/$tot*100) : 0;
            $pP   = $tot > 0 ? round($peut/$tot*100) : 0;
        @endphp
        <div class="border border-gray-100 rounded-xl p-3">
            <p class="text-xs font-semibold text-gray-600 mb-2">{{ $loop->iteration }}. {{ $q->text }}</p>
            <div class="space-y-1">
                @foreach([['vrai','✅ Vrai','bg-green-400',$pV,$vrai],['faux','❌ Faux','bg-red-400',$pF,$faux],['peut_etre','🤔 Peut-être','bg-amber-400',$pP,$peut]] as [$key,$label,$color,$pct,$cnt])
                <div class="flex items-center gap-2">
                    <span class="text-xs w-24 {{ $q->correct_answer===$key?'font-bold text-gray-800':'text-gray-500' }}">{{ $label }}{{ $q->correct_answer===$key?' ✓':'' }}</span>
                    <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden"><div class="h-2 {{ $color }} rounded-full" style="width:{{ $pct }}%"></div></div>
                    <span class="text-xs text-gray-400 w-14 text-right">{{ $cnt }} ({{ $pct }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
            <p class="text-gray-400 text-sm italic text-center py-8">Aucune réponse enregistrée.</p>
        @endforelse
    </div>
</div>

</div>

<script>
function toggleEdit(id) {
    document.querySelector('.view-mode-' + id)?.classList.toggle('hidden');
    document.querySelector('.edit-mode-' + id)?.classList.toggle('hidden');
}
</script>
