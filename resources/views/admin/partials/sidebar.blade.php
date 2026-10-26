<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg no-print">
    <div class="p-6 border-b">
        <h1 class="text-2xl font-bold text-gray-800">NLC Events</h1>
        <p class="text-sm text-gray-600 mt-1">Dashboard Admin</p>
    </div>
    
    <nav class="p-4">
        <a href="{{ route('admin.dashboard.view') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard.view') && (!request('tab') || request('tab') == 'dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span class="font-medium">Tableau de bord</span>
        </a>

        <a href="{{ route('admin.consultations.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.consultations.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            <span class="font-medium">Consultations</span>
            @php $nouvellesConsultations = \App\Models\ConsultationRequest::where('status', 'nouvelle')->count(); @endphp
            @if($nouvellesConsultations > 0)
                <span class="ml-auto bg-purple-600 text-white text-xs px-2 py-1 rounded-full">{{ $nouvellesConsultations }}</span>
            @endif
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

        <a href="{{ route('admin.dashboard.view') }}?tab=quiz"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'quiz' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">Quiz</span>
            @if(isset($quizCount) && $quizCount > 0)
                <span class="ml-auto bg-purple-500 text-white text-xs px-2 py-1 rounded-full">{{ $quizCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.dashboard.view') }}?tab=evaluation"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'evaluation' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span class="font-medium">Évaluation</span>
            @if(isset($evaluationCount) && $evaluationCount > 0)
                <span class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">{{ $evaluationCount }}</span>
            @endif
        </a>

        <a href="{{ route('admin.dashboard.view') }}?tab=configuration"
           class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request('tab') == 'configuration' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50' }} transition mb-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="font-medium">Configuration</span>
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
