<div class="bg-white rounded-lg shadow">

{{-- Toast notifications --}}
@if(session('success'))
<div id="toast-success"
     class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-green-200 text-green-800 px-5 py-4 rounded-xl shadow-lg max-w-sm transition-all duration-300">
    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-semibold">Succès</p>
        <p class="text-xs text-green-600 mt-0.5">{{ session('success') }}</p>
    </div>
    <button onclick="document.getElementById('toast-success').remove()" class="text-green-400 hover:text-green-600 ml-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<script>setTimeout(() => document.getElementById('toast-success')?.remove(), 4000);</script>
@endif

@if(session('error'))
<div id="toast-error"
     class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-lg max-w-sm transition-all duration-300">
    <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-semibold">Erreur</p>
        <p class="text-xs text-red-600 mt-0.5">{{ session('error') }}</p>
    </div>
    <button onclick="document.getElementById('toast-error').remove()" class="text-red-400 hover:text-red-600 ml-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
<script>setTimeout(() => document.getElementById('toast-error')?.remove(), 5000);</script>
@endif

    <div class="p-6 border-b flex items-center justify-between gap-4 flex-wrap">
        <h3 class="text-xl font-bold text-gray-800">Liste des billets</h3>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.tickets.print-list', request()->query()) }}" target="_blank"
               class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimer la liste
            </a>
            @php
                $completedWithEmail = $allTickets->filter(fn($t) => $t->payment_status === 'completed' && $t->email);
            @endphp
            @if($completedWithEmail->count() > 0)
            <button onclick="relancerTous()" id="btn-relancer-tous"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Tout relancer ({{ $completedWithEmail->count() }})
            </button>
            @endif
        </div>
    </div>

    <!-- Filtres -->
    <div class="p-6 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="tab" value="tickets">
            
            <input 
                type="text" 
                name="tickets_search" 
                value="{{ request('tickets_search') }}"
                placeholder="Rechercher..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >

            <select name="tickets_status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                <option value="completed" {{ request('tickets_status', 'completed') == 'completed' ? 'selected' : '' }}>Validés</option>
                <option value="all" {{ request('tickets_status') == 'all' ? 'selected' : '' }}>Tous</option>
                <option value="pending_cash" {{ request('tickets_status') == 'pending_cash' ? 'selected' : '' }}>En attente</option>
                <option value="failed" {{ request('tickets_status') == 'failed' ? 'selected' : '' }}>Échoués</option>
            </select>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Filtrer
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($allTickets as $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $ticket->reference }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $ticket->full_name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $ticket->event->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($ticket->amount, 0, ',', ' ') }} {{ $ticket->currency }}</td>
                        <td class="px-6 py-4">
                            @if($ticket->payment_status == 'completed')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Validé</span>
                            @elseif($ticket->payment_status == 'pending_cash')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">En attente</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $ticket->payment_status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            @if($ticket->payment_status == 'completed' && $ticket->email)
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        onclick="confirmerEnvoi('{{ $ticket->reference }}', '{{ addslashes($ticket->full_name) }}', '{{ $ticket->email }}')"
                                        title="Envoyer par mail à {{ $ticket->email }}"
                                        class="inline-flex items-center gap-1 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Envoyer</span>
                                </button>
                                <a href="{{ route('admin.tickets.print', $ticket->reference) }}" target="_blank"
                                   title="Imprimer le billet"
                                   class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Imprimer</span>
                                </a>
                            </div>
                            @elseif($ticket->payment_status == 'completed')
                            <a href="{{ route('admin.tickets.print', $ticket->reference) }}" target="_blank"
                               title="Imprimer le billet"
                               class="inline-flex items-center gap-1 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                <span class="hidden sm:inline">Imprimer</span>
                            </a>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">Aucun billet trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($allTickets->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $allTickets->appends(request()->query())->links() }}
        </div>
    @endif
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Liste des tickets à relancer (pour le bouton "Tout relancer") --}}
@php
    $ticketsRelancerData = $allTickets
        ->filter(fn($t) => $t->payment_status === 'completed' && $t->email)
        ->values()
        ->map(fn($t) => [
            'reference' => $t->reference,
            'name'      => $t->full_name,
            'email'     => $t->email,
            'url'       => route('admin.tickets.resend-mail-ajax', $t->reference),
        ])
        ->toArray();
@endphp
<script>
const ticketsARelancer = {!! json_encode(array_values($ticketsRelancerData)) !!};

const csrfToken = '{{ csrf_token() }}';

// ── Envoi individuel ──────────────────────────────────────────────────────────
async function confirmerEnvoi(reference, nom, email) {
    const result = await Swal.fire({
        title: 'Renvoyer le billet ?',
        html: `<p class="text-gray-600">Envoyer le billet de <strong>${nom}</strong> à :</p>
               <p class="font-mono text-indigo-600 mt-1">${email}</p>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Oui, envoyer',
        cancelButtonText: 'Annuler',
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Envoi en cours…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const url = `/admin/tickets/${reference}/resend-mail-ajax`;
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({ icon: 'success', title: 'Envoyé !', text: data.message, timer: 2500, showConfirmButton: false });
        } else {
            Swal.fire({ icon: 'error', title: 'Échec', text: data.message });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Erreur réseau', text: e.message });
    }
}

// ── Tout relancer mail par mail ───────────────────────────────────────────────
async function relancerTous() {
    if (ticketsARelancer.length === 0) {
        Swal.fire({ icon: 'info', title: 'Aucun billet', text: 'Aucun billet validé avec email sur cette page.' });
        return;
    }

    const confirm = await Swal.fire({
        title: 'Tout relancer ?',
        html: `<p class="text-gray-600">Envoyer le billet à <strong>${ticketsARelancer.length} participant(s)</strong> sur cette page, mail après mail.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Lancer la relance',
        cancelButtonText: 'Annuler',
    });

    if (!confirm.isConfirmed) return;

    let sent = 0, failed = 0;
    const errors = [];

    for (let i = 0; i < ticketsARelancer.length; i++) {
        const t = ticketsARelancer[i];

        Swal.fire({
            title: `Envoi ${i + 1} / ${ticketsARelancer.length}`,
            html: `<p class="text-gray-600">Envoi à <strong>${t.name}</strong></p>
                   <p class="font-mono text-sm text-indigo-600">${t.email}</p>
                   <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                     <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width:${Math.round(((i+1)/ticketsARelancer.length)*100)}%"></div>
                   </div>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const res = await fetch(t.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (data.success) { sent++; } else { failed++; errors.push(`${t.name} : ${data.message}`); }
        } catch (e) {
            failed++;
            errors.push(`${t.name} : erreur réseau`);
        }

        // Petite pause pour ne pas surcharger le serveur mail
        await new Promise(r => setTimeout(r, 600));
    }

    // Résumé final
    Swal.fire({
        icon: failed === 0 ? 'success' : 'warning',
        title: 'Relance terminée',
        html: `<p>✅ <strong>${sent}</strong> envoyé(s) avec succès</p>
               ${failed > 0 ? `<p class="mt-1">❌ <strong>${failed}</strong> échec(s)</p>
               <ul class="text-left text-xs text-red-600 mt-2 max-h-32 overflow-y-auto">${errors.map(e => `<li>• ${e}</li>`).join('')}</ul>` : ''}`,
        confirmButtonColor: '#4f46e5',
    });
}
</script>
