{{-- Onglet Configuration des événements --}}

@if(session('success'))
<div id="toast-ok" class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-lg">
    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span class="text-sm font-semibold">{{ session('success') }}</span>
</div>
<script>setTimeout(()=>document.getElementById('toast-ok')?.remove(),3500)</script>
@endif

<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-4">
    <p class="text-sm text-gray-500 leading-relaxed">
        Activez ou désactivez le <strong>Quiz</strong>, l'<strong>Évaluation</strong> et le <strong>Certificat</strong>
        pour chaque événement. Ces paramètres contrôlent ce qui s'affiche sur le frontend.
    </p>
</div>

<form method="POST" action="{{ route('admin.configuration.save') }}">
    @csrf

    <div class="space-y-4">
        @forelse($eventsList as $event)
        @php $cfg = $eventConfigs[$event->id] ?? null; @endphp

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Header événement --}}
            <div class="flex items-center gap-4 p-5 border-b border-gray-50">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800 truncate">{{ $event->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        📅 {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                        @if($event->location) &nbsp;·&nbsp; 📍 {{ $event->location }} @endif
                    </p>
                </div>
                {{-- Badge statut global --}}
                @php
                    $anyEnabled = $cfg && ($cfg->quiz_enabled || $cfg->evaluation_enabled);
                @endphp
                <span class="text-xs px-3 py-1 rounded-full font-semibold flex-shrink-0
                    {{ $anyEnabled ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $anyEnabled ? '● Actif' : '○ Inactif' }}
                </span>
            </div>

            {{-- Toggles --}}
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <input type="hidden" name="configs[{{ $loop->index }}][event_id]" value="{{ $event->id }}">

                {{-- Quiz --}}
                <label class="flex items-center justify-between gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                    {{ $cfg?->quiz_enabled ? 'border-purple-400 bg-purple-50' : 'border-gray-100 hover:border-purple-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $cfg?->quiz_enabled ? 'bg-purple-600' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 {{ $cfg?->quiz_enabled ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ $cfg?->quiz_enabled ? 'text-purple-700' : 'text-gray-700' }}">Quiz</p>
                            <p class="text-xs text-gray-400">Afficher le quiz</p>
                        </div>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="configs[{{ $loop->index }}][quiz_enabled]" value="1"
                               {{ $cfg?->quiz_enabled ? 'checked' : '' }}
                               class="sr-only peer"
                               onchange="updateToggle(this)">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-purple-600 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>

                {{-- Évaluation --}}
                <label class="flex items-center justify-between gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                    {{ $cfg?->evaluation_enabled ? 'border-green-400 bg-green-50' : 'border-gray-100 hover:border-green-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $cfg?->evaluation_enabled ? 'bg-green-600' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 {{ $cfg?->evaluation_enabled ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ $cfg?->evaluation_enabled ? 'text-green-700' : 'text-gray-700' }}">Évaluation</p>
                            <p class="text-xs text-gray-400">Formulaire colloque</p>
                        </div>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="configs[{{ $loop->index }}][evaluation_enabled]" value="1"
                               {{ $cfg?->evaluation_enabled ? 'checked' : '' }}
                               class="sr-only peer"
                               onchange="updateToggle(this)">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-green-600 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>

                {{-- Certificat --}}
                <label class="flex items-center justify-between gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all
                    {{ ($cfg?->certificate_enabled ?? true) ? 'border-amber-400 bg-amber-50' : 'border-gray-100 hover:border-amber-200' }}">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ ($cfg?->certificate_enabled ?? true) ? 'bg-amber-500' : 'bg-gray-100' }} flex items-center justify-center flex-shrink-0 transition-colors">
                            <svg class="w-5 h-5 {{ ($cfg?->certificate_enabled ?? true) ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold {{ ($cfg?->certificate_enabled ?? true) ? 'text-amber-700' : 'text-gray-700' }}">Certificat</p>
                            <p class="text-xs text-gray-400">Téléchargement PDF</p>
                        </div>
                    </div>
                    <div class="relative flex-shrink-0">
                        <input type="checkbox" name="configs[{{ $loop->index }}][certificate_enabled]" value="1"
                               {{ ($cfg?->certificate_enabled ?? true) ? 'checked' : '' }}
                               class="sr-only peer"
                               onchange="updateToggle(this)">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-amber-500 rounded-full transition-colors"></div>
                        <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform peer-checked:translate-x-5"></div>
                    </div>
                </label>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-400">
            <p class="text-sm italic">Aucun événement trouvé.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6 flex justify-end">
        <button type="submit"
            class="bg-gradient-to-r from-purple-600 to-blue-600 hover:opacity-90 text-white px-8 py-3 rounded-xl font-bold text-sm shadow-md transition">
            💾 Sauvegarder la configuration
        </button>
    </div>
</form>

{{-- Pagination --}}
@if($eventsList->hasPages())
<div class="mt-4">{{ $eventsList->appends(request()->query())->links() }}</div>
@endif

<script>
// Mettre à jour le style du label parent quand le toggle change
function updateToggle(checkbox) {
    const label = checkbox.closest('label');
    if (!label) return;
    // Forcer le re-render en toggleant une classe temporaire
    label.classList.toggle('opacity-90');
    setTimeout(() => label.classList.toggle('opacity-90'), 50);
}
</script>
