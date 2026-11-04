@extends('admin.consultations.layout')
@section('title','Professionnels d’orientation')
@section('header')<a href="{{ route('admin.consultations.index') }}" class="text-purple-700">← Demandes d’orientation</a><h1 class="text-3xl font-bold mt-4">Professionnels</h1><p class="text-gray-500 mt-2">Intervenants internes et consultants. Un compte actif et validé est nécessaire pour recevoir des affectations.</p>@endsection
@section('content')
<div class="space-y-5">
@foreach($professionals as $professional)<details class="bg-white border rounded-xl p-6"><summary class="cursor-pointer flex flex-wrap justify-between gap-3"><span><strong>{{ $professional->name }}</strong> · {{ $professional->profession }}<span class="text-sm text-gray-500 ml-2">{{ $professional->engagement==='consultant' ? 'Consultant externe' : 'Interne' }}</span></span><span class="text-sm {{ $professional->active ? 'text-green-700' : 'text-gray-500' }}">{{ $professional->active ? 'Actif' : 'Inactif' }}</span></summary><form action="{{ route('admin.consultations.professionals.update',$professional) }}" method="POST" class="mt-5">@csrf @method('PUT') @include('admin.consultations.professional-form',['p'=>$professional])</form></details>@endforeach
<section class="bg-purple-50 border border-purple-100 rounded-xl p-6"><h2 class="text-xl font-bold mb-5">Ajouter un professionnel</h2><form method="POST" action="{{ route('admin.consultations.professionals.store') }}">@csrf @include('admin.consultations.professional-form',['p'=>null])</form></section>
</div>@endsection
