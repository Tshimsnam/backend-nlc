<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - NLC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: true, currentTab: 'dashboard', validateModal: false, selectedTicket: null }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-white border-r border-gray-200 transition-all duration-300 fixed h-full z-10 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <h1 x-show="sidebarOpen" class="text-xl font-bold text-gray-800">Admin Panel</h1>
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="p-4 space-y-2 flex-1">
                <!-- Dashboard -->
                <button @click="currentTab = 'dashboard'" :class="currentTab === 'dashboard' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="font-medium">Dashboard</span>
                </button>

                <!-- Tickets -->
                <button @click="currentTab = 'tickets'" :class="currentTab === 'tickets' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="font-medium">Tickets</span>
                </button>

                <!-- Agents -->
                <button @click="currentTab = 'agents'" :class="currentTab === 'agents' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="font-medium">Agents Mobile</span>
                </button>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-200">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span x-show="sidebarOpen" class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-300">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-8 py-4">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <span x-show="currentTab === 'dashboard'">Dashboard</span>
                        <span x-show="currentTab === 'tickets'">Gestion des Tickets</span>
                        <span x-show="currentTab === 'agents'">Gestion des Agents</span>
                    </h2>
                    <p class="text-sm text-gray-600">Bienvenue, {{ $user->name }}</p>
                </div>
            </header>

            <div class="p-8">
                <!-- Messages de succès/erreur -->
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="text-red-700 hover:text-red-900">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Dashboard Tab -->
                <div x-show="currentTab === 'dashboard'">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Tickets -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Total</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_tickets'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Tickets créés</p>
                        </div>

                        <!-- Tickets Validés -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Validés</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['tickets_completed'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Paiements confirmés</p>
                        </div>

                        <!-- Tickets En Attente -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">En attente</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['tickets_pending'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">À valider</p>
                        </div>

                        <!-- Scans QR Code -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">QR Scans</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_qr_scans'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Personnes scannées</p>
                        </div>
                    </div>

                    <!-- Deuxième ligne de stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Revenus -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Revenus</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">${{ number_format($stats['total_revenue'], 0, ',', ' ') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Total encaissé</p>
                        </div>

                        <!-- Total Événements -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Événements</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_events'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Total créés</p>
                        </div>

                        <!-- Total Utilisateurs -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Utilisateurs</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Agents & Admins</p>
                        </div>
                    </div>

                    <!-- Recent Tickets -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-xl font-bold text-gray-900">Tickets récents</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentTickets as $ticket)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->reference }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $ticket->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $ticket->email }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-900">{{ $ticket->event->title }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $ticket->amount }} {{ $ticket->currency }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($ticket->payment_status === 'completed')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Validé
                                                </span>
                                            @elseif($ticket->payment_status === 'pending_cash')
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                    En attente
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Échoué
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($ticket->payment_status === 'pending_cash')
                                                <button @click="selectedTicket = {{ json_encode($ticket) }}; validateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    Valider
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tickets Tab -->
                <div x-show="currentTab === 'tickets'" style="display: none;">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Liste complète des tickets</h3>
                        <p class="text-gray-600">Fonctionnalité en cours de développement...</p>
                    </div>
                </div>

                <!-- Agents Tab -->
                <div x-show="currentTab === 'agents'" style="display: none;">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Gestion des agents mobile</h3>
                        <p class="text-gray-600">Fonctionnalité en cours de développement...</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de Validation -->
    <div x-show="validateModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Overlay -->
            <div @click="validateModal = false" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

            <!-- Modal Content -->
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Valider le Ticket</h3>
                
                <div x-show="selectedTicket" class="space-y-3 mb-6">
                    <div>
                        <span class="text-sm text-gray-600">Référence:</span>
                        <span class="font-mono font-medium text-gray-900 ml-2" x-text="selectedTicket?.reference"></span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Participant:</span>
                        <span class="font-medium text-gray-900 ml-2" x-text="selectedTicket?.full_name"></span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <span class="text-gray-900 ml-2" x-text="selectedTicket?.email"></span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Montant:</span>
                        <span class="font-medium text-gray-900 ml-2" x-text="selectedTicket?.amount + ' ' + selectedTicket?.currency"></span>
                    </div>
                </div>

                <form :action="'/admin/tickets/' + selectedTicket?.reference + '/validate'" method="POST">
                    @csrf
                    <div class="flex gap-3">
                        <button type="button" @click="validateModal = false" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
