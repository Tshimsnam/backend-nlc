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
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-800">Liste des billets</h3>
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
                            <form method="POST" action="{{ route('admin.tickets.resend-mail', $ticket->reference) }}"
                                  onsubmit="return confirm('Renvoyer le billet à {{ $ticket->email }} ?')">
                                @csrf
                                <button type="submit"
                                        title="Envoyer par mail à {{ $ticket->email }}"
                                        class="inline-flex items-center gap-2 bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                                    {{-- Icône toujours visible --}}
                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{-- Texte masqué sur mobile --}}
                                    <span class="hidden sm:inline">Envoyer</span>
                                </button>
                            </form>
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
