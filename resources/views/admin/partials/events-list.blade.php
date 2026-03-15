<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-800">Liste des événements</h3>
    </div>

    <!-- Filtres -->
    <div class="p-6 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex gap-4">
            <input type="hidden" name="tab" value="events">
            
            <input 
                type="text" 
                name="events_search" 
                value="{{ request('events_search') }}"
                placeholder="Rechercher un événement..." 
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Rechercher
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billets</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarifs</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($eventsList as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $event->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->location }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->tickets_count }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $event->event_prices_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucun événement trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($eventsList->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $eventsList->appends(request()->query())->links() }}
        </div>
    @endif
</div>
