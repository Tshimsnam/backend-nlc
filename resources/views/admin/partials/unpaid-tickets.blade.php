<div class="bg-white rounded-lg shadow-lg">
    <!-- Header avec bouton d'impression -->
    <div class="p-6 border-b flex justify-between items-center no-print">
        <div>
            <h3 class="text-xl font-bold text-gray-800">Billets en attente de paiement</h3>
            <p class="text-sm text-gray-600 mt-1">Liste des personnes ayant généré des billets en ligne sans paiement</p>
        </div>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center gap-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimer la liste
        </button>
    </div>

    <!-- Filtres de recherche -->
    <div class="p-6 border-b bg-gray-50 no-print">
        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex gap-4">
            <input type="hidden" name="tab" value="relancer">
            
            <div class="flex-1">
                <input 
                    type="text" 
                    name="unpaid_search" 
                    value="{{ request('unpaid_search') }}"
                    placeholder="Rechercher par nom, email, téléphone..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Rechercher
            </button>

            @if(request('unpaid_search'))
                <a href="{{ route('admin.dashboard.view') }}?tab=relancer" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Statistiques rapides -->
    <div class="p-6 border-b bg-blue-50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-sm text-gray-600">Total billets non payés</p>
                <p class="text-2xl font-bold text-gray-800">{{ $unpaidTickets->total() }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-sm text-gray-600">Montant total en attente</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($unpaidTickets->sum('amount'), 0, ',', ' ') }} FC</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow">
                <p class="text-sm text-gray-600">Personnes à relancer</p>
                <p class="text-2xl font-bold text-gray-800">{{ $unpaidTickets->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Titre pour l'impression -->
    <div class="hidden print:block p-6 text-center border-b">
        <h1 class="text-2xl font-bold text-gray-800">NLC Events - Liste des billets non payés</h1>
        <p class="text-sm text-gray-600 mt-2">Généré le {{ now()->format('d/m/Y à H:i') }}</p>
        <p class="text-sm text-gray-600">Total: {{ $unpaidTickets->total() }} billets - Montant: {{ number_format($unpaidTickets->sum('amount'), 0, ',', ' ') }} FC</p>
    </div>

    <!-- Table des billets non payés -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N°</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom complet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Téléphone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Événement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date création</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider no-print">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($unpaidTickets as $index => $ticket)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ($unpaidTickets->currentPage() - 1) * $unpaidTickets->perPage() + $index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $ticket->full_name }}</div>
                            <div class="text-xs text-gray-500">Réf: {{ $ticket->reference }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ticket->phone ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $ticket->email ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $ticket->event->title ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-semibold text-gray-900">{{ number_format($ticket->amount, 0, ',', ' ') }} {{ $ticket->currency }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm no-print">
                            <div class="flex gap-2">
                                <a href="tel:{{ $ticket->phone }}" class="text-blue-600 hover:text-blue-800" title="Appeler">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </a>
                                <a href="mailto:{{ $ticket->email }}" class="text-green-600 hover:text-green-800" title="Envoyer un email">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">Aucun billet non payé trouvé</p>
                            <p class="text-sm mt-1">Tous les billets générés ont été payés ou aucun billet ne correspond à votre recherche.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($unpaidTickets->hasPages())
        <div class="px-6 py-4 border-t no-print">
            {{ $unpaidTickets->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<style>
    @media print {
        @page {
            size: landscape;
            margin: 1cm;
        }
        
        body {
            font-size: 10pt;
        }
        
        table {
            font-size: 9pt;
        }
        
        .print\:block {
            display: block !important;
        }
    }
</style>
