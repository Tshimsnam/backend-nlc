<div class="bg-white rounded-lg shadow">
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucun billet trouvé</td>
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
