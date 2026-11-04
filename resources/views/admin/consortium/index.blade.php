@extends('admin.consultations.layout')
@section('title', 'Consortium')
@section('header')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div><p class="text-sm text-purple-700 font-semibold">Réseau NLC</p><h1 class="text-3xl font-bold text-gray-900">Adhésions au consortium</h1><p class="text-gray-500 mt-2">Instruisez les demandes et accompagnez les structures partenaires. Horaires affichés à Kinshasa.</p></div>
        <a href="{{ rtrim(config('consortium.frontend_url'), '/') }}/consortium" target="_blank" rel="noopener" class="text-purple-700 font-medium">Voir la page publique ↗</a>
    </div>
@endsection
@section('content')
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach(['submitted' => 'À prendre en charge', 'under_review' => 'En étude', 'needs_information' => 'Compléments attendus', 'accepted' => 'Structures acceptées'] as $status => $label)
            <a href="{{ route('admin.consortium.index', ['status' => $status]) }}" class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">{{ $label }}</p><p class="text-3xl font-bold mt-2">{{ $counts[$status] ?? 0 }}</p></a>
        @endforeach
    </div>
    @unless(config('consortium.agreement_url'))
        <p class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-lg mb-6">L’accord officiel n’est pas encore configuré. Transmettez-le aux structures avant de demander leur exemplaire signé. L’acceptation exige une vérification de cet exemplaire.</p>
    @endunless
    <form method="GET" class="bg-white border rounded-xl p-4 grid md:grid-cols-3 gap-3 mb-6">
        <input name="search" value="{{ request('search') }}" placeholder="Nom de structure ou référence" aria-label="Rechercher" class="border rounded-lg px-3 py-2">
        <select name="status" aria-label="Statut" class="border rounded-lg px-3 py-2"><option value="">Tous les statuts</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select>
        <select name="province" aria-label="Province" class="border rounded-lg px-3 py-2"><option value="">Toutes les provinces</option>@foreach(config('consortium.provinces') as $province)<option @selected(request('province') === $province)>{{ $province }}</option>@endforeach</select>
        <select name="structure_type" aria-label="Type de structure" class="border rounded-lg px-3 py-2"><option value="">Tous les types</option>@foreach(['association'=>'Association', 'centre'=>'Centre', 'school'=>'École', 'clinic'=>'Clinique', 'other'=>'Autre'] as $value=>$label)<option value="{{ $value }}" @selected(request('structure_type') === $value)>{{ $label }}</option>@endforeach</select>
        <select name="expertise" aria-label="Expertise" class="border rounded-lg px-3 py-2"><option value="">Toutes les expertises</option>@foreach(config('consortium.expertise') as $value=>$label)<option value="{{ $value }}" @selected(request('expertise') === $value)>{{ $label }}</option>@endforeach</select>
        <button class="bg-purple-700 text-white rounded-lg px-4 py-2">Filtrer</button>
    </form>
    <div class="bg-white border rounded-xl overflow-x-auto">
        <table class="w-full text-sm text-left"><thead class="bg-gray-50 text-gray-600"><tr><th class="p-4">Structure</th><th class="p-4">Référence</th><th class="p-4">Statut</th><th class="p-4">Suivi NLC</th><th class="p-4">Mise à jour</th><th class="p-4">Dossier</th></tr></thead>
            <tbody>@forelse($applications as $application)<tr class="border-t"><td class="p-4 font-semibold">{{ $application->structure->name }}<div class="text-gray-500 font-normal">{{ $application->data['city'] ?? '' }}</div></td><td class="p-4">{{ $application->reference }}</td><td class="p-4">{{ $statuses[$application->status] }}</td><td class="p-4">{{ $application->assigned_to ?: 'Non attribué' }}</td><td class="p-4">{{ $application->updated_at->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</td><td class="p-4"><a href="{{ route('admin.consortium.show', $application) }}" class="text-purple-700 font-semibold">Ouvrir →</a></td></tr>@empty<tr><td colspan="6" class="p-10 text-center text-gray-500">Aucune demande ne correspond à ces filtres.</td></tr>@endforelse</tbody>
        </table>
    </div>
    <div class="mt-5">{{ $applications->links() }}</div>
@endsection
