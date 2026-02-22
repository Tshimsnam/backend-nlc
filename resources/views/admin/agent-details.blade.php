<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Agent - {{ $agent->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard.view') }}?tab=agents" class="text-gray-600 hover:text-gray-900 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Détails de l'Agent</h1>
                        <p class="text-sm text-gray-600">Statistiques et évolution des validations</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>

        <div class="p-8">
            <!-- Informations de l'Agent -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-8">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-white font-bold text-3xl">{{ strtoupper(substr($agent->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $agent->name }}</h2>
                        <p class="text-gray-600">{{ $agent->email }}</p>
                        <div class="flex items-center gap-4 mt-2">
                            @if($agent->roles->isNotEmpty())
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $agent->roles->first()->name }}
                                </span>
                            @endif
                            @if($agent->email_verified_at)
                                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                    ✓ Vérifié
                                </span>
                            @endif
                            <span class="text-sm text-gray-500">
                                Inscrit le {{ $agent->created_at->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques Globales -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Validations -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-500">Total</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_validations'] }}</h3>
                    <p class="text-sm text-gray-600 mt-1">Validations effectuées</p>
                </div>

                <!-- Revenus Générés -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-500">Revenus</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} $</h3>
                    <p class="text-sm text-gray-600 mt-1">Revenus générés</p>
                </div>

                <!-- Taux de Conversion -->
                <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <span class="text-sm text-gray-500">Performance</span>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900">
                        {{ $stats['total_validations'] > 0 ? number_format(($stats['total_revenue'] / $stats['total_validations']), 2) : 0 }} $
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Revenu moyen / validation</p>
                </div>
            </div>

            <!-- Statistiques par Type -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Validations par Type de Billet</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Billets Physiques -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 shadow-sm border-2 border-purple-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-200 text-purple-800">
                                QR Physique
                            </span>
                        </div>
                        <h4 class="text-sm font-medium text-purple-700 mb-2">Billets Physiques</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-purple-600 mb-1">Total validés</p>
                                <p class="text-3xl font-bold text-purple-900">{{ $stats['physical_validations'] }}</p>
                            </div>
                            <div class="pt-3 border-t border-purple-200">
                                <p class="text-xs text-purple-600 mb-1">Revenus générés</p>
                                <p class="text-xl font-bold text-purple-900">{{ number_format($stats['physical_revenue'], 0, ',', ' ') }} $</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-purple-200">
                            <p class="text-xs text-purple-700">
                                <span class="font-semibold">{{ $stats['total_validations'] > 0 ? round(($stats['physical_validations'] / $stats['total_validations']) * 100, 1) : 0 }}%</span> du total des validations
                            </p>
                        </div>
                    </div>

                    <!-- Billets En Ligne -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 shadow-sm border-2 border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-200 text-blue-800">
                                Site Web
                            </span>
                        </div>
                        <h4 class="text-sm font-medium text-blue-700 mb-2">Billets En Ligne</h4>
                        <div class="space-y-3">
                            <div>
                                <p class="text-xs text-blue-600 mb-1">Total validés</p>
                                <p class="text-3xl font-bold text-blue-900">{{ $stats['online_validations'] }}</p>
                            </div>
                            <div class="pt-3 border-t border-blue-200">
                                <p class="text-xs text-blue-600 mb-1">Revenus générés</p>
                                <p class="text-xl font-bold text-blue-900">{{ number_format($stats['online_revenue'], 0, ',', ' ') }} $</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-blue-200">
                            <p class="text-xs text-blue-700">
                                <span class="font-semibold">{{ $stats['total_validations'] > 0 ? round(($stats['online_validations'] / $stats['total_validations']) * 100, 1) : 0 }}%</span> du total des validations
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphique d'Évolution -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Évolution des Validations (30 derniers jours)</h3>
                <canvas id="validationsChart" height="80"></canvas>
            </div>

            <!-- Validations par Événement -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Validations par Événement</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Physiques</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">En Ligne</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($validationsByEvent as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $event->title }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900">{{ $event->total }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                        {{ $event->physical }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $event->online }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-green-600">{{ number_format($event->revenue, 0, ',', ' ') }} $</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    Aucune validation effectuée
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dernières Validations -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Dernières Validations</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentValidations as $ticket)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->reference }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ticket->physical_qr_id)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            🔲 Physique
                                        </span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            💻 En ligne
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900">{{ $ticket->full_name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-900">{{ $ticket->event->title }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">{{ $ticket->amount }} {{ $ticket->currency }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-500">{{ $ticket->updated_at->format('d/m/Y H:i') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Aucune validation récente
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Données pour le graphique
        const evolutionData = @json($validationsEvolution);
        
        // Préparer les données
        const labels = evolutionData.map(item => {
            const date = new Date(item.date);
            return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
        });
        
        const physicalData = evolutionData.map(item => item.physical);
        const onlineData = evolutionData.map(item => item.online);
        const totalData = evolutionData.map(item => item.total);

        // Créer le graphique
        const ctx = document.getElementById('validationsChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Billets Physiques',
                        data: physicalData,
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Billets En Ligne',
                        data: onlineData,
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Total',
                        data: totalData,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderWidth: 2,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
