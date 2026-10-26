@extends('admin.consultations.layout')

@section('title', 'Consultations à distance')

@section('header')
    <h2 class="text-3xl font-bold text-gray-800">Consultations à distance</h2>
    <p class="text-gray-600 mt-1">Bienvenue, {{ $user->name ?? '' }} - heures affichées en heure de Kinshasa</p>
@endsection

@section('content')
    @php
        $total = $counts->sum();
        $tabs = ['' => 'Toutes'] + \App\Models\ConsultationRequest::STATUS_LABELS;
    @endphp

    {{-- Filtres par statut --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach($tabs as $key => $label)
            @php $active = (string) $status === (string) $key; @endphp
            <a href="{{ route('admin.consultations.index', array_filter(['status' => $key, 'search' => $search])) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border hover:bg-gray-50' }}">
                {{ $label }}
                <span class="ml-1 {{ $active ? 'text-blue-100' : 'text-gray-400' }}">{{ $key === '' ? $total : ($counts[$key] ?? 0) }}</span>
            </a>
        @endforeach
    </div>

    {{-- Recherche --}}
    <form method="GET" action="{{ route('admin.consultations.index') }}" class="mb-6 flex gap-2">
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <input type="search" name="search" value="{{ $search }}" placeholder="Rechercher : n° de dossier, nom, e-mail, WhatsApp, pays…"
               class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
        <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium">Rechercher</button>
        @if($search)
            <a href="{{ route('admin.consultations.index', array_filter(['status' => $status])) }}" class="px-4 py-2 rounded-lg border bg-white text-gray-600 hover:bg-gray-50">Effacer</a>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        @if($consultations->isEmpty())
            <div class="p-12 text-center text-gray-500">
                Aucune demande {{ $search ? 'ne correspond à cette recherche' : 'pour le moment' }}.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-left">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Dossier</th>
                            <th class="px-4 py-3 font-semibold">Enfant</th>
                            <th class="px-4 py-3 font-semibold">Parent</th>
                            <th class="px-4 py-3 font-semibold">Séance / 1er créneau proposé</th>
                            <th class="px-4 py-3 font-semibold">Statut</th>
                            <th class="px-4 py-3 font-semibold">Reçue le</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($consultations as $c)
                            @php
                                $premier = $c->creneaux[0]['start'] ?? null;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs">{{ $c->reference }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $c->enfant_prenom }} {{ $c->enfant_nom }}</div>
                                    <div class="text-xs text-gray-500">{{ $c->enfant_date_naissance->age }} ans - {{ $c->enfant_sexe === 'F' ? 'fille' : 'garçon' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-900">{{ $c->parent_nom_complet }}</div>
                                    <div class="text-xs text-gray-500">{{ $c->ville }}, {{ $c->pays }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    @if($c->seance_programmee_at)
                                        <span class="font-medium text-blue-700">{{ $c->seance_programmee_at->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</span>
                                    @elseif($premier)
                                        <span class="text-gray-500">{{ \Carbon\Carbon::parse($premier)->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</span>
                                        <span class="text-xs text-gray-400">(proposé)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">@include('admin.consultations._status-badge', ['status' => $c->status])</td>
                                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $c->created_at->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.consultations.show', $c) }}" class="text-blue-600 hover:underline font-medium">Ouvrir</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t">{{ $consultations->links() }}</div>
        @endif
    </div>
@endsection
