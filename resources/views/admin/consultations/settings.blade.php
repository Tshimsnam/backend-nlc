@extends('admin.consultations.layout')
@section('title','Configuration des consultations')
@section('header')<a href="{{ route('admin.consultations.index') }}" class="text-purple-700">← Demandes d’orientation</a><h1 class="text-3xl font-bold mt-4">Configuration du parcours</h1>@endsection
@section('content')
<form method="POST" action="{{ route('admin.consultations.settings.update') }}" class="max-w-3xl bg-white rounded-xl border p-6 space-y-5">@csrf @method('PUT')
@foreach(['secretariat_email'=>'E-mail du secrétariat','centre_address'=>'Adresse et consignes pour les consultations au centre','default_duration_minutes'=>'Durée proposée par défaut, en minutes','manual_payment_label'=>'Libellé du paiement électronique avec vérification administrative','manual_payment_instructions'=>'Instructions de paiement électronique : bénéficiaire, compte / numéro marchand dédié aux consultations et référence à indiquer'] as $key=>$label)
<label class="block text-sm font-semibold">{{ $label }}
@if(in_array($key,['centre_address','manual_payment_instructions'],true))<textarea name="{{ $key }}" class="block border rounded-lg p-3 mt-2 w-full" rows="4">{{ \App\Services\Orientation\Settings::get($key) }}</textarea>
@else<input type="{{ $key==='secretariat_email' ? 'email' : ($key==='default_duration_minutes' ? 'number' : 'text') }}" name="{{ $key }}" value="{{ \App\Services\Orientation\Settings::get($key) }}" required class="block border rounded-lg p-3 mt-2 w-full">@endif</label>
@endforeach
<p class="text-sm text-gray-500">Laissez les instructions de paiement vides pour désactiver cette option. Un numéro réservé aux dons n’est pas réutilisé automatiquement. Aucun paiement n’est confirmé à partir de la seule déclaration du parent.</p>
<p class="text-sm text-gray-500">MaxiCash : {{ config('orientation.maxicash_enabled') ? 'activé dans la configuration serveur' : 'désactivé' }}. Les identifiants du prestataire restent exclusivement dans la configuration serveur.</p>
<p class="text-sm text-gray-500">Première réponse : 72 heures calendaires après dépôt. Les rappels et alertes nécessitent le planificateur et le worker de mails.</p>
<button class="bg-purple-700 text-white px-5 py-3 rounded-lg">Enregistrer la configuration</button></form>
@endsection
