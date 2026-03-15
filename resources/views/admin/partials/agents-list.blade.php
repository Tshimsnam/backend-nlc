<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-xl font-bold text-gray-800">Liste des agents</h3>
    </div>

    <!-- Filtres -->
    <div class="p-6 border-b bg-gray-50">
        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="flex gap-4">
            <input type="hidden" name="tab" value="agents">
            
            <input 
                type="text" 
                name="agents_search" 
                value="{{ request('agents_search') }}"
                placeholder="Rechercher un agent..." 
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date création</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($agents as $agent)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $agent->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $agent->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            @foreach($agent->roles as $role)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $agent->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.agents.details', $agent->id) }}" class="text-blue-600 hover:text-blue-800">
                                Voir détails
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Aucun agent trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($agents->hasPages())
        <div class="px-6 py-4 border-t">
            {{ $agents->appends(request()->query())->links() }}
        </div>
    @endif
</div>
