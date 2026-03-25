<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - NLC Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg no-print">
            <div class="p-6 border-b">
                <h1 class="text-2xl font-bold text-gray-800">NLC Events</h1>
                <p class="text-sm text-gray-600 mt-1">Dashboard Admin</p>
            </div>
            
            <nav class="p-4">
                <a href="{{ route('admin.dashboard.view') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ !request('tab') || request('tab') == 'dashboard' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="font-medium">Tableau de bord</span>
                </a>

                <a href="{{ route('admin.dashboard.view') }}?tab=tickets" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'tickets' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <span class="font-medium">Billets</span>
                </a>

                <a href="{{ route('admin.dashboard.view') }}?tab=relancer" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'relancer' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">Relancer</span>
                    @if(isset($unpaidCount) && $unpaidCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $unpaidCount }}</span>
                    @endif
                </a>

                <a href="{{ route('admin.dashboard.view') }}?tab=agents" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'agents' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="font-medium">Agents</span>
                </a>

                <a href="{{ route('admin.dashboard.view') }}?tab=events" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'events' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="font-medium">Événements</span>
                </a>

                <a href="{{ route('admin.dashboard.view') }}?tab=rapport" 
                   class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'rapport' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="font-medium">Rapport</span>
                </a>

                <form action="{{ route('admin.logout') }}" method="POST" class="mt-8">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition w-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <div class="p-8">
                <!-- Header -->
                <div class="mb-8 no-print">
                    <h2 class="text-3xl font-bold text-gray-800">
                        @if(!request('tab') || request('tab') == 'dashboard')
                            Tableau de bord
                        @elseif(request('tab') == 'tickets')
                            Gestion des billets
                        @elseif(request('tab') == 'relancer')
                            Relancer les paiements
                        @elseif(request('tab') == 'agents')
                            Gestion des agents
                        @elseif(request('tab') == 'events')
                            Gestion des événements
                        @elseif(request('tab') == 'rapport')
                            Rapport d'activité
                        @endif
                    </h2>
                    <p class="text-gray-600 mt-1">Bienvenue, {{ $user->name }}</p>
                </div>

                <!-- Messages de succès/erreur -->
                @if(session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 no-print">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6 no-print">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Contenu selon l'onglet -->
                @if(!request('tab') || request('tab') == 'dashboard')
                    @include('admin.partials.dashboard-stats')
                @elseif(request('tab') == 'tickets')
                    @include('admin.partials.tickets-list')
                @elseif(request('tab') == 'relancer')
                    @include('admin.partials.unpaid-tickets')
                @elseif(request('tab') == 'agents')
                    @include('admin.partials.agents-list')
                @elseif(request('tab') == 'events')
                    @include('admin.partials.events-list')
                @elseif(request('tab') == 'rapport')
                    @include('admin.partials.rapport')
                @endif
            </div>
        </main>
    </div>
</body>
</html>
