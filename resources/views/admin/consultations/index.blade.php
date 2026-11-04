@extends('admin.consultations.layout')
@section('title','Demandes d’orientation')
@section('header')
<div class="flex flex-wrap justify-between gap-4"><div><p class="text-purple-600 text-sm font-semibold">PARCOURS FAMILLES</p><h1 class="text-3xl font-bold mt-2">Demandes d’orientation</h1><p class="text-gray-500 mt-2">Une première réponse sous 72 heures. Horaires affichés en heure de Kinshasa.</p></div>
<div class="flex flex-wrap gap-2 items-start"><a class="border bg-white rounded-lg px-4 py-2" href="{{ route('admin.consultations.professionals') }}">Professionnels</a><a class="border bg-white rounded-lg px-4 py-2" href="{{ route('admin.consultations.settings') }}">Configuration</a></div></div>
@endsection
@section('content')
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
@foreach(['nouvelle'=>'Nouvelles','autorisation_attendue'=>'Autorisations attendues','paiement_en_attente'=>'Paiements attendus','rdv_programme'=>'Rendez-vous confirmés'] as $key=>$label)
<a href="{{ route('admin.consultations.index',['status'=>$key]) }}" class="bg-white border rounded-xl p-5"><p class="text-gray-500 text-sm">{{ $label }}</p><strong class="block text-3xl mt-2">{{ $counts[$key] ?? 0 }}</strong></a>
@endforeach
</div>
@if($lateCount)<a href="{{ route('admin.consultations.index',['late'=>1]) }}" class="block bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl mb-6">{{ $lateCount }} demande(s) sans première réponse après 72 heures — ouvrir les dossiers à traiter.</a>@endif
<form method="GET" class="flex flex-wrap gap-3 mb-5"><input aria-label="Rechercher un dossier" name="search" value="{{ $search }}" placeholder="Référence, parent, enfant ou e-mail" class="border rounded-lg px-4 py-2 flex-1 min-w-0">
<select aria-label="Statut" name="status" class="border rounded-lg px-3 py-2 max-w-full"><option value="">Tous les statuts</option>@foreach(\App\Models\ConsultationRequest::STATUS_LABELS as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>@endforeach</select>
<button class="bg-purple-700 text-white rounded-lg px-5 py-2">Filtrer</button><a href="{{ route('admin.consultations.index') }}" class="px-3 py-2">Réinitialiser</a></form>
<div class="bg-white border rounded-xl overflow-x-auto"><table class="w-full text-sm text-left"><thead class="bg-gray-50 text-gray-500"><tr><th class="p-4">Dossier</th><th class="p-4">Famille</th><th class="p-4">Statut</th><th class="p-4">Première réponse</th><th class="p-4"></th></tr></thead><tbody class="divide-y">
@forelse($consultations as $c)<tr><td class="p-4"><strong>{{ $c->reference }}</strong><p class="text-gray-500 mt-1">{{ $c->created_at->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</p></td><td class="p-4">{{ $c->enfant_nom_complet }}<p class="text-gray-500 mt-1">{{ $c->parent_nom_complet }}</p></td><td class="p-4"><span class="bg-purple-50 text-purple-800 rounded-full px-3 py-1 inline-block">{{ \App\Models\ConsultationRequest::STATUS_LABELS[$c->status] ?? $c->status }}</span></td>
<td class="p-4">@if($c->first_response_at)<span class="text-green-700">Réponse apportée</span>@elseif($c->processing_due_at)<span class="{{ $c->processing_due_at->isPast() ? 'text-red-700 font-semibold' : 'text-gray-600' }}">{{ $c->processing_due_at->timezone('Africa/Kinshasa')->format('d/m/Y H:i') }}</span>@else<span class="text-gray-400">Dossier antérieur</span>@endif</td><td class="p-4"><a class="text-purple-700 font-semibold" href="{{ route('admin.consultations.show',$c) }}">Ouvrir →</a></td></tr>
@empty<tr><td colspan="5" class="p-12 text-center text-gray-500">Aucune demande ne correspond à ces critères.</td></tr>@endforelse
</tbody></table></div><div class="mt-5">{{ $consultations->links() }}</div>
@endsection
