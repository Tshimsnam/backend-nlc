<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - NLC</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
</head>
<body class="bg-gray-50" x-data="{ 
    sidebarOpen: true, 
    currentTab: '{{ request('tab') ?? 'dashboard' }}', 
    validateModal: false, 
    selectedTicket: null 
}" x-init="
    // Gérer le changement d'onglet via URL
    if (window.location.search.includes('tab=tickets')) currentTab = 'tickets';
    if (window.location.search.includes('tab=agents')) currentTab = 'agents';
    if (window.location.search.includes('tab=events')) currentTab = 'events';
">
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

                <!-- QR Billet Physique -->
                <button @click="currentTab = 'qr-physique'" :class="currentTab === 'qr-physique' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="font-medium">QR Billet Physique</span>
                </button>

                <!-- Événements -->
                <button @click="currentTab = 'events'" :class="currentTab === 'events' ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span x-show="sidebarOpen" class="font-medium">Événements</span>
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
                        <span x-show="currentTab === 'qr-physique'">QR Billet Physique</span>
                        <span x-show="currentTab === 'events'">Gestion des Événements</span>
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Billets</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_ticket_scans'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Scans de billets</p>
                        </div>
                    </div>

                    <!-- Statistiques par type de billet -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ventes par canal</h3>
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
                                        <p class="text-xs text-purple-600 mb-1">Total créés</p>
                                        <p class="text-3xl font-bold text-purple-900">{{ $stats['physical_tickets'] }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-purple-200">
                                        <div>
                                            <p class="text-xs text-purple-600 mb-1">Validés</p>
                                            <p class="text-xl font-bold text-purple-900">{{ $stats['physical_tickets_completed'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-purple-600 mb-1">Revenus</p>
                                            <p class="text-xl font-bold text-purple-900">{{ number_format($stats['physical_tickets_revenue'], 0, ',', ' ') }} $</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-purple-200">
                                    <p class="text-xs text-purple-700">
                                        <span class="font-semibold">{{ $stats['physical_tickets'] > 0 ? round(($stats['physical_tickets_completed'] / $stats['physical_tickets']) * 100, 1) : 0 }}%</span> de taux de validation
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
                                        <p class="text-xs text-blue-600 mb-1">Total créés</p>
                                        <p class="text-3xl font-bold text-blue-900">{{ $stats['online_tickets'] }}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 pt-3 border-t border-blue-200">
                                        <div>
                                            <p class="text-xs text-blue-600 mb-1">Validés</p>
                                            <p class="text-xl font-bold text-blue-900">{{ $stats['online_tickets_completed'] }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-blue-600 mb-1">Revenus</p>
                                            <p class="text-xl font-bold text-blue-900">{{ number_format($stats['online_tickets_revenue'], 0, ',', ' ') }} $</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-t border-blue-200">
                                    <p class="text-xs text-blue-700">
                                        <span class="font-semibold">{{ $stats['online_tickets'] > 0 ? round(($stats['online_tickets_completed'] / $stats['online_tickets']) * 100, 1) : 0 }}%</span> de taux de validation
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Deuxième ligne de stats - Scans -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Scans d'événements -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Événements</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_event_scans'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Scans d'événements</p>
                        </div>

                        <!-- Billets uniques scannés -->
                        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Validés</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['tickets_scanned'] ?? 0 }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Billets uniques scannés</p>
                        </div>
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

                        <!-- Total Événements - CACHÉ -->
                        <!-- <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
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
                        </div> -->

                        <!-- Total Utilisateurs - CACHÉ -->
                        <!-- <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-500">Utilisateurs</span>
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</h3>
                            <p class="text-sm text-gray-600 mt-1">Agents & Admins</p>
                        </div> -->
                    </div>

                    <!-- Filtres pour les tickets -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6" x-data="{ searchTerm: '{{ request('search') ?? '' }}', statusFilter: '{{ request('status') ?? 'all' }}' }">
                        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="space-y-4">
                            <!-- Barre de recherche -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input 
                                    type="text" 
                                    name="search" 
                                    x-model="searchTerm"
                                    placeholder="Référence, nom, email, téléphone..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="{{ request('search') }}"
                                />
                            </div>

                            <!-- Filtres par statut -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Statut de paiement</label>
                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" name="status" value="all" :class="statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Tous
                                    </button>
                                    <button type="submit" name="status" value="pending_cash" :class="statusFilter === 'pending_cash' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">
                                        En attente
                                    </button>
                                    <button type="submit" name="status" value="completed" :class="statusFilter === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Validés
                                    </button>
                                    <button type="submit" name="status" value="failed" :class="statusFilter === 'failed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">
                                        Échoués
                                    </button>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                                <a href="{{ route('admin.dashboard.view') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Recent Tickets -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <h3 class="text-xl font-bold text-gray-900">Tickets récents</h3>
                            <span class="text-sm text-gray-600">{{ $recentTickets->total() }} ticket(s) au total</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                                        <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Événement</th> -->
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentTickets as $ticket)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->reference }}</span>
                                                @if($ticket->physical_qr_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                        </svg>
                                                        Physique
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                        En ligne
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($ticket->physical_qr_id)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-sm">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-semibold text-purple-900">Billet Physique</div>
                                                        <div class="text-xs text-purple-600">QR: {{ substr($ticket->physical_qr_id, 0, 8) }}...</div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-semibold text-blue-900">Billet En Ligne</div>
                                                        <div class="text-xs text-blue-600">Généré sur le site</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $ticket->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $ticket->email }}</div>
                                            </div>
                                        </td>
                                        <!-- <td class="px-6 py-4">
                                            <span class="text-sm text-gray-900">{{ $ticket->event->title }}</span>
                                        </td> -->
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

                            <!-- Message si aucun ticket -->
                            @if($recentTickets->isEmpty())
                                <div class="text-center py-12">
                                    <p class="text-gray-500">Aucun ticket trouvé</p>
                                </div>
                            @endif
                        </div>

                        <!-- Pagination -->
                        @if($recentTickets->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Affichage de {{ $recentTickets->firstItem() }} à {{ $recentTickets->lastItem() }} sur {{ $recentTickets->total() }} résultats
                                </div>
                                <div class="flex items-center gap-2">
                                    {{-- Bouton Précédent --}}
                                    @if($recentTickets->onFirstPage())
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                            Précédent
                                        </span>
                                    @else
                                        <a href="{{ $recentTickets->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                            Précédent
                                        </a>
                                    @endif

                                    {{-- Numéros de page --}}
                                    <div class="flex gap-1">
                                        @foreach(range(1, $recentTickets->lastPage()) as $page)
                                            @if($page == $recentTickets->currentPage())
                                                <span class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg">
                                                    {{ $page }}
                                                </span>
                                            @elseif($page == 1 || $page == $recentTickets->lastPage() || abs($page - $recentTickets->currentPage()) <= 2)
                                                <a href="{{ $recentTickets->url($page) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                                    {{ $page }}
                                                </a>
                                            @elseif(abs($page - $recentTickets->currentPage()) == 3)
                                                <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    {{-- Bouton Suivant --}}
                                    @if($recentTickets->hasMorePages())
                                        <a href="{{ $recentTickets->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                            Suivant
                                        </a>
                                    @else
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
                                            Suivant
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Tickets Tab -->
                <div x-show="currentTab === 'tickets'" style="display: none;">
                    <!-- Filtres -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6" x-data="{ searchTerm: '{{ request('tickets_search') ?? '' }}', statusFilter: '{{ request('tickets_status') ?? 'completed' }}', payTypeFilter: '{{ request('tickets_pay_type') ?? 'all' }}' }">
                        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="space-y-4">
                            <input type="hidden" name="tab" value="tickets" />
                            
                            <!-- Barre de recherche -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
                                <input 
                                    type="text" 
                                    name="tickets_search" 
                                    x-model="searchTerm"
                                    placeholder="Référence, nom, email, téléphone..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="{{ request('tickets_search') }}"
                                />
                            </div>

                            <!-- Filtres -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Filtre par statut -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Statut de paiement</label>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" name="tickets_status" value="completed" :class="statusFilter === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            Validés
                                        </button>
                                        <button type="submit" name="tickets_status" value="all" :class="statusFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            Tous
                                        </button>
                                        <button type="submit" name="tickets_status" value="pending_cash" :class="statusFilter === 'pending_cash' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            En attente
                                        </button>
                                        <button type="submit" name="tickets_status" value="failed" :class="statusFilter === 'failed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            Échoués
                                        </button>
                                    </div>
                                </div>

                                <!-- Filtre par mode de paiement -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mode de paiement</label>
                                    <div class="flex flex-wrap gap-2">
                                        <button type="submit" name="tickets_pay_type" value="all" :class="payTypeFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            Tous
                                        </button>
                                        <button type="submit" name="tickets_pay_type" value="cash" :class="payTypeFilter === 'cash' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            Caisse
                                        </button>
                                        <button type="submit" name="tickets_pay_type" value="maxicash" :class="payTypeFilter === 'maxicash' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            MaxiCash
                                        </button>
                                        <button type="submit" name="tickets_pay_type" value="mpesa" :class="payTypeFilter === 'mpesa' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition">
                                            M-Pesa
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                                <a href="{{ route('admin.dashboard.view') }}?tab=tickets" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des tickets -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200" id="tickets-table-container">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Billets Validés</h3>
                                <span class="text-sm text-gray-600">{{ $allTickets->total() }} billet(s) validé(s)</span>
                            </div>
                            <button onclick="printTicketsList()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Imprimer la liste
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Référence</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Participant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Montant</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($allTickets as $ticket)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono text-sm font-medium text-gray-900">{{ $ticket->reference }}</span>
                                                @if($ticket->physical_qr_id)
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 border border-purple-200">
                                                        Physique
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                        En ligne
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $ticket->full_name }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ $ticket->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $ticket->phone }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $ticket->amount }} {{ $ticket->currency }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($ticket->payment_status === 'pending_cash')
                                                <button @click="selectedTicket = {{ json_encode($ticket) }}; validateModal = true" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                    Valider
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            Aucun ticket trouvé
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($allTickets->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Affichage de {{ $allTickets->firstItem() }} à {{ $allTickets->lastItem() }} sur {{ $allTickets->total() }} résultats
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($allTickets->onFirstPage())
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Précédent</span>
                                    @else
                                        <a href="{{ $allTickets->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Précédent</a>
                                    @endif

                                    <div class="flex gap-1">
                                        @foreach(range(1, $allTickets->lastPage()) as $page)
                                            @if($page == $allTickets->currentPage())
                                                <span class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg">{{ $page }}</span>
                                            @elseif($page == 1 || $page == $allTickets->lastPage() || abs($page - $allTickets->currentPage()) <= 2)
                                                <a href="{{ $allTickets->url($page) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $page }}</a>
                                            @elseif(abs($page - $allTickets->currentPage()) == 3)
                                                <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($allTickets->hasMorePages())
                                        <a href="{{ $allTickets->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Suivant</a>
                                    @else
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Suivant</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Agents Tab -->
                <div x-show="currentTab === 'agents'" style="display: none;" x-data="{ showCreateForm: false }">
                    <!-- Bouton Créer un Agent -->
                    <div class="mb-6 flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-gray-900">Gestion des Agents Mobile</h2>
                        <button @click="showCreateForm = !showCreateForm" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span x-text="showCreateForm ? 'Annuler' : 'Créer un Agent'"></span>
                        </button>
                    </div>

                    <!-- Formulaire de Création -->
                    <div x-show="showCreateForm" x-cloak class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Créer un Nouvel Agent</h3>
                        <form method="POST" action="{{ route('admin.agents.create') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="tab" value="agents" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Nom -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nom complet *</label>
                                    <input 
                                        type="text" 
                                        name="name" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="John Doe"
                                    />
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                    <input 
                                        type="email" 
                                        name="email" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="john@example.com"
                                    />
                                </div>

                                <!-- Mot de passe -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Mot de passe *</label>
                                    <input 
                                        type="password" 
                                        name="password" 
                                        required
                                        minlength="6"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Minimum 6 caractères"
                                    />
                                </div>

                                <!-- Rôle -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rôle *</label>
                                    <select 
                                        name="role_id" 
                                        required
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">Sélectionner un rôle</option>
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="flex gap-3 pt-4">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    Créer l'Agent
                                </button>
                                <button type="button" @click="showCreateForm = false" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Filtres -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6" x-data="{ searchTerm: '{{ request('agents_search') ?? '' }}' }">
                        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="space-y-4">
                            <input type="hidden" name="tab" value="agents" />
                            
                            <!-- Barre de recherche -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher un agent</label>
                                <input 
                                    type="text" 
                                    name="agents_search" 
                                    x-model="searchTerm"
                                    placeholder="Nom, email..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="{{ request('agents_search') }}"
                                />
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                                <a href="{{ route('admin.dashboard.view') }}?tab=agents" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des agents -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Agents Mobile</h3>
                                <span class="text-sm text-gray-600">{{ $agents->total() }} agent(s) - Hors Parents et Administrateurs</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nom</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rôle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date d'inscription</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($agents as $agent)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">#{{ $agent->id }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                    <span class="text-blue-600 font-semibold text-sm">{{ strtoupper(substr($agent->name, 0, 2)) }}</span>
                                                </div>
                                                <span class="ml-3 text-sm font-medium text-gray-900">{{ $agent->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $agent->email }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($agent->roles->isNotEmpty())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    {{ $agent->roles->first()->name }}
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Aucun rôle
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-500">{{ $agent->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($agent->email_verified_at)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Vérifié
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                    Non vérifié
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('admin.agents.details', $agent->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                </svg>
                                                Voir Détails
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            Aucun agent trouvé
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($agents->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Affichage de {{ $agents->firstItem() }} à {{ $agents->lastItem() }} sur {{ $agents->total() }} résultats
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($agents->onFirstPage())
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Précédent</span>
                                    @else
                                        <a href="{{ $agents->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Précédent</a>
                                    @endif

                                    <div class="flex gap-1">
                                        @foreach(range(1, $agents->lastPage()) as $page)
                                            @if($page == $agents->currentPage())
                                                <span class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg">{{ $page }}</span>
                                            @elseif($page == 1 || $page == $agents->lastPage() || abs($page - $agents->currentPage()) <= 2)
                                                <a href="{{ $agents->url($page) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $page }}</a>
                                            @elseif(abs($page - $agents->currentPage()) == 3)
                                                <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($agents->hasMorePages())
                                        <a href="{{ $agents->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Suivant</a>
                                    @else
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Suivant</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- QR Billet Physique Tab -->
                <div x-show="currentTab === 'qr-physique'" style="display: none;" x-data="{ 
                    eventId: '', 
                    quantity: 1,
                    events: {{ json_encode($stats['events'] ?? []) }},
                    generatedQRs: [],
                    showQRs: false,
                    generateQRs() {
                        if (!this.eventId) {
                            alert('Veuillez sélectionner un événement');
                            return;
                        }
                        
                        if (this.quantity < 1 || this.quantity > 100) {
                            alert('Le nombre de QR codes doit être entre 1 et 100');
                            return;
                        }
                        
                        this.generatedQRs = [];
                        
                        // Générer les QR codes
                        for (let i = 0; i < this.quantity; i++) {
                            const uniqueId = 'PHY-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9).toUpperCase();
                            this.generatedQRs.push({
                                id: uniqueId,
                                event_id: this.eventId,
                                type: 'physical_ticket',
                                created_at: new Date().toISOString()
                            });
                        }
                        
                        this.showQRs = true;
                        
                        // Générer les QR codes après un court délai
                        this.$nextTick(() => {
                            this.generatedQRs.forEach((qr, index) => {
                                const container = document.getElementById('qr-' + index);
                                if (container) {
                                    container.innerHTML = '';
                                    new QRCode(container, {
                                        text: JSON.stringify(qr),
                                        width: 200,
                                        height: 200,
                                        colorDark: '#000000',
                                        colorLight: '#ffffff',
                                        correctLevel: QRCode.CorrectLevel.H
                                    });
                                }
                            });
                        });
                    },
                    downloadAll() {
                        // Télécharger tous les QR codes en PDF
                        window.print();
                    },
                    reset() {
                        this.eventId = '';
                        this.quantity = 1;
                        this.generatedQRs = [];
                        this.showQRs = false;
                    }
                }">
                    <div class="max-w-6xl mx-auto">
                        <!-- Description -->
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
                            <div class="flex items-start gap-3">
                                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Génération de QR Codes pour Billets Physiques</h3>
                                    <p class="text-blue-800 mb-2">
                                        Générez des QR codes vierges pour vos billets physiques. Ces QR codes pourront être scannés dans l'application mobile pour créer un ticket en remplissant les informations du participant.
                                    </p>
                                    <p class="text-blue-800 font-medium">
                                        Processus : Sélectionner événement → Choisir quantité → Générer → Télécharger → Donner au designer pour impression
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Formulaire de génération -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6" x-show="!showQRs">
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Générer des QR Codes</h3>
                            
                            <form @submit.prevent="generateQRs()" class="space-y-6">
                                <!-- Sélection de l'événement -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Événement *
                                    </label>
                                    <select 
                                        x-model="eventId"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    >
                                        <option value="">Sélectionner un événement</option>
                                        @foreach($events ?? [] as $event)
                                            <option value="{{ $event->id }}">{{ $event->title }} - {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-sm text-gray-500 mt-2">
                                        Choisissez l'événement pour lequel vous voulez générer des billets physiques
                                    </p>
                                </div>

                                <!-- Nombre de QR codes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre de QR Codes *
                                    </label>
                                    <input 
                                        type="number" 
                                        x-model.number="quantity"
                                        min="1"
                                        max="100"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        placeholder="Ex: 50"
                                    />
                                    <p class="text-sm text-gray-500 mt-2">
                                        Entrez le nombre de billets physiques à générer (maximum 100)
                                    </p>
                                </div>

                                <!-- Bouton de génération -->
                                <div class="flex gap-3">
                                    <button 
                                        type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition flex items-center gap-2"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                        Générer les QR Codes
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Affichage des QR Codes générés -->
                        <div x-show="showQRs" x-cloak>
                            <!-- Actions -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 no-print">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">QR Codes Générés</h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span x-text="generatedQRs.length"></span> QR code(s) prêt(s) pour impression
                                        </p>
                                    </div>
                                    <div class="flex gap-3">
                                        <button 
                                            @click="downloadAll()"
                                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition flex items-center gap-2"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                            Télécharger Tout
                                        </button>
                                        <button 
                                            @click="reset()"
                                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition"
                                        >
                                            Nouvelle Génération
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Grille de QR Codes -->
                            <div id="qr-codes-grid" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <template x-for="(qr, index) in generatedQRs" :key="qr.id">
                                        <div class="border-2 border-gray-200 rounded-lg p-4 text-center qr-card">
                                            <div class="mb-3">
                                                <p class="text-xs text-gray-500 mb-1">Billet Physique</p>
                                                <p class="font-mono text-sm font-bold text-gray-900" x-text="qr.id"></p>
                                            </div>
                                            <div class="flex justify-center mb-3">
                                                <div :id="'qr-' + index" class="inline-block"></div>
                                            </div>
                                            <p class="text-xs text-gray-500">
                                                Scanner pour activer
                                            </p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Événements Tab -->
                <div x-show="currentTab === 'events'" style="display: none;" x-data="{ 
                    showEditModal: false, 
                    selectedEvent: null,
                    editEvent(event) {
                        this.selectedEvent = JSON.parse(JSON.stringify(event));
                        // S'assurer que event_prices existe
                        if (!this.selectedEvent.event_prices) {
                            this.selectedEvent.event_prices = [];
                        }
                        this.showEditModal = true;
                    },
                    addPrice() {
                        if (!this.selectedEvent.event_prices) {
                            this.selectedEvent.event_prices = [];
                        }
                        this.selectedEvent.event_prices.push({
                            category: '',
                            amount: '',
                            currency: 'USD',
                            label: '',
                            description: ''
                        });
                    },
                    removePrice(index) {
                        this.selectedEvent.event_prices.splice(index, 1);
                    }
                }">
                    <!-- Filtres -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 mb-6" x-data="{ searchTerm: '{{ request('events_search') ?? '' }}' }">
                        <form method="GET" action="{{ route('admin.dashboard.view') }}" class="space-y-4">
                            <input type="hidden" name="tab" value="events" />
                            
                            <!-- Barre de recherche -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher un événement</label>
                                <input 
                                    type="text" 
                                    name="events_search" 
                                    x-model="searchTerm"
                                    placeholder="Titre, lieu..." 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    value="{{ request('events_search') }}"
                                />
                            </div>

                            <!-- Boutons d'action -->
                            <div class="flex gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Rechercher
                                </button>
                                <a href="{{ route('admin.dashboard.view') }}?tab=events" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium transition">
                                    Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tableau des événements -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Liste des Événements</h3>
                                <span class="text-sm text-gray-600">{{ $eventsList->total() }} événement(s)</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Titre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lieu</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tickets</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tarifs</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($eventsList as $event)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">#{{ $event->id }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                            @if($event->description)
                                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ Str::limit($event->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-sm text-gray-900">{{ $event->location }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ $event->tickets_count }} billets
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                {{ $event->event_prices_count }} tarifs
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button 
                                                @click="editEvent({{ json_encode($event) }})"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm mr-2"
                                            >
                                                Modifier
                                            </button>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                            Aucun événement trouvé
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($eventsList->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Affichage de {{ $eventsList->firstItem() }} à {{ $eventsList->lastItem() }} sur {{ $eventsList->total() }} résultats
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($eventsList->onFirstPage())
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Précédent</span>
                                    @else
                                        <a href="{{ $eventsList->previousPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Précédent</a>
                                    @endif

                                    <div class="flex gap-1">
                                        @foreach(range(1, $eventsList->lastPage()) as $page)
                                            @if($page == $eventsList->currentPage())
                                                <span class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg">{{ $page }}</span>
                                            @elseif($page == 1 || $page == $eventsList->lastPage() || abs($page - $eventsList->currentPage()) <= 2)
                                                <a href="{{ $eventsList->url($page) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">{{ $page }}</a>
                                            @elseif(abs($page - $eventsList->currentPage()) == 3)
                                                <span class="px-4 py-2 text-sm font-medium text-gray-400">...</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($eventsList->hasMorePages())
                                        <a href="{{ $eventsList->nextPageUrl() }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">Suivant</a>
                                    @else
                                        <span class="px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">Suivant</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Modal de Modification -->
                    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                        <div class="flex items-center justify-center min-h-screen px-4 py-8">
                            <!-- Overlay -->
                            <div @click="showEditModal = false" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

                            <!-- Modal Content -->
                            <div class="relative bg-white rounded-xl shadow-xl max-w-4xl w-full p-6 max-h-[90vh] overflow-y-auto">
                                <h3 class="text-xl font-bold text-gray-900 mb-4">Modifier l'Événement</h3>
                                
                                <form :action="'/admin/events/' + selectedEvent?.id + '/update'" method="POST" class="space-y-6">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="tab" value="events" />

                                    <!-- Informations de base -->
                                    <div class="bg-gray-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-semibold text-gray-900">Informations de base</h4>
                                        
                                        <!-- Titre -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Titre *</label>
                                            <input 
                                                type="text" 
                                                name="title" 
                                                x-model="selectedEvent.title"
                                                required
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            />
                                        </div>

                                        <!-- Description -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Description courte</label>
                                            <textarea 
                                                name="description" 
                                                x-model="selectedEvent.description"
                                                rows="2"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Description courte pour les listes"
                                            ></textarea>
                                        </div>

                                        <!-- Description complète -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Description complète</label>
                                            <textarea 
                                                name="full_description" 
                                                x-model="selectedEvent.full_description"
                                                rows="4"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder="Description détaillée pour la page de l'événement"
                                            ></textarea>
                                        </div>

                                        <!-- Dates -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de début *</label>
                                                <input 
                                                    type="date" 
                                                    name="date" 
                                                    x-model="selectedEvent.date"
                                                    required
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                                                <input 
                                                    type="date" 
                                                    name="end_date" 
                                                    x-model="selectedEvent.end_date"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>

                                        <!-- Horaires -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Heure de début</label>
                                                <input 
                                                    type="text" 
                                                    name="time" 
                                                    x-model="selectedEvent.time"
                                                    placeholder="Ex: 08h00"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Heure de fin</label>
                                                <input 
                                                    type="text" 
                                                    name="end_time" 
                                                    x-model="selectedEvent.end_time"
                                                    placeholder="Ex: 16h00"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>

                                        <!-- Lieu -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Ville/Localité *</label>
                                                <input 
                                                    type="text" 
                                                    name="location" 
                                                    x-model="selectedEvent.location"
                                                    required
                                                    placeholder="Ex: Kinshasa"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Lieu détaillé</label>
                                                <input 
                                                    type="text" 
                                                    name="venue_details" 
                                                    x-model="selectedEvent.venue_details"
                                                    placeholder="Ex: Fleuve Congo Hôtel Kinshasa"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>

                                        <!-- Capacité et Date limite -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre maximum de participants</label>
                                                <input 
                                                    type="number" 
                                                    name="max_participants" 
                                                    x-model="selectedEvent.capacity"
                                                    min="1"
                                                    placeholder="Ex: 200"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Date limite d'inscription</label>
                                                <input 
                                                    type="date" 
                                                    name="registration_deadline" 
                                                    x-model="selectedEvent.registration_deadline"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informations de contact -->
                                    <div class="bg-green-50 p-4 rounded-lg space-y-4">
                                        <h4 class="font-semibold text-gray-900">Informations de contact</h4>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Organisateur</label>
                                                <input 
                                                    type="text" 
                                                    name="organizer" 
                                                    x-model="selectedEvent.organizer"
                                                    placeholder="Ex: Never Limit Children"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone de contact</label>
                                                <input 
                                                    type="text" 
                                                    name="contact_phone" 
                                                    x-model="selectedEvent.contact_phone"
                                                    placeholder="Ex: +243 844 338 747"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Email de contact</label>
                                                <input 
                                                    type="email" 
                                                    name="contact_email" 
                                                    x-model="selectedEvent.contact_email"
                                                    placeholder="Ex: info@nlcrdc.org"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Gestion des Prix -->
                                    <div class="bg-blue-50 p-4 rounded-lg space-y-4">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-semibold text-gray-900">Tarifs de l'événement</h4>
                                            <button 
                                                type="button"
                                                @click="addPrice()"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Ajouter un tarif
                                            </button>
                                        </div>

                                        <!-- Liste des prix -->
                                        <div class="space-y-3">
                                            <template x-for="(price, index) in selectedEvent.event_prices" :key="index">
                                                <div class="bg-white p-4 rounded-lg border border-gray-200 space-y-3">
                                                    <input type="hidden" :name="'prices[' + index + '][id]'" x-model="price.id" />
                                                    
                                                    <div class="flex items-center justify-between mb-2">
                                                        <span class="text-sm font-medium text-gray-700">Tarif #<span x-text="index + 1"></span></span>
                                                        <button 
                                                            type="button"
                                                            @click="removePrice(index)"
                                                            class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                        >
                                                            Supprimer
                                                        </button>
                                                    </div>

                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                        <!-- Catégorie -->
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie *</label>
                                                            <select 
                                                                :name="'prices[' + index + '][category]'"
                                                                x-model="price.category"
                                                                required
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                            >
                                                                <option value="">Sélectionner</option>
                                                                <option value="teacher">Enseignants</option>
                                                                <option value="student_1day">Étudiants 1 jour</option>
                                                                <option value="student_2days">Étudiants 2 jours</option>
                                                                <option value="doctor">Médecin</option>
                                                                <option value="parent">Parents</option>
                                                            </select>
                                                        </div>

                                                        <!-- Montant -->
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Montant *</label>
                                                            <div class="flex gap-2">
                                                                <input 
                                                                    type="number" 
                                                                    :name="'prices[' + index + '][amount]'"
                                                                    x-model="price.amount"
                                                                    step="0.01"
                                                                    min="0"
                                                                    required
                                                                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                    placeholder="0.00"
                                                                />
                                                                <select 
                                                                    :name="'prices[' + index + '][currency]'"
                                                                    x-model="price.currency"
                                                                    required
                                                                    class="w-24 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                >
                                                                    <option value="USD">USD</option>
                                                                    <option value="CDF">CDF</option>
                                                                    <option value="EUR">EUR</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <!-- Label -->
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                                                            <input 
                                                                type="text" 
                                                                :name="'prices[' + index + '][label]'"
                                                                x-model="price.label"
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                placeholder="Ex: Tarif réduit"
                                                            />
                                                        </div>

                                                        <!-- Description -->
                                                        <div>
                                                            <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                            <input 
                                                                type="text" 
                                                                :name="'prices[' + index + '][description]'"
                                                                x-model="price.description"
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                                placeholder="Détails du tarif"
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Message si aucun prix -->
                                            <div x-show="!selectedEvent.event_prices || selectedEvent.event_prices.length === 0" class="text-center py-8 text-gray-500">
                                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <p class="text-sm">Aucun tarif configuré</p>
                                                <p class="text-xs mt-1">Cliquez sur "Ajouter un tarif" pour commencer</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Boutons -->
                                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                                        <button type="button" @click="showEditModal = false" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-4 rounded-lg transition">
                                            Annuler
                                        </button>
                                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition">
                                            Enregistrer les modifications
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
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
        
        /* Styles pour l'impression */
        @media print {
            body * {
                visibility: hidden;
            }
            
            /* Pour l'impression du tableau de tickets */
            #tickets-table-container, #tickets-table-container * {
                visibility: visible;
            }
            #tickets-table-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            /* Pour l'impression des QR codes */
            #qr-codes-grid, #qr-codes-grid * {
                visibility: visible !important;
            }
            #qr-codes-grid {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            
            /* Cacher les éléments non imprimables */
            .no-print, button, .pagination, aside, header, nav, .sidebar {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Ajuster les styles pour l'impression */
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f3f4f6;
                font-weight: bold;
            }
            
            /* Styles pour les cartes QR */
            .qr-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>

    <script>
        function printTicketsList() {
            window.print();
        }
    </script>
</body>
</html>
