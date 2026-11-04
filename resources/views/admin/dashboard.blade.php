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
        @include('admin.partials.sidebar')

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
                        @elseif(request('tab') == 'quiz')
                            Quiz — Résultats GSA 2026
                        @elseif(request('tab') == 'evaluation')
                            Évaluations du colloque
                        @elseif(request('tab') == 'configuration')
                            Configuration des événements
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
                @elseif(request('tab') == 'quiz')
                    @include('admin.partials.quiz-stats')
                @elseif(request('tab') == 'evaluation')
                    @include('admin.partials.evaluation-stats')
                @elseif(request('tab') == 'configuration')
                    @include('admin.partials.configuration')
                @endif
            </div>
        </main>
    </div>
</body>
</html>
