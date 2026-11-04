@php
    $colors = [
        'nouvelle'          => 'bg-purple-100 text-purple-700',
        'dossier_incomplet' => 'bg-amber-100 text-amber-700',
        'rdv_programme'     => 'bg-blue-100 text-blue-700',
        'seance_faite'      => 'bg-green-100 text-green-700',
        'annulee'           => 'bg-gray-200 text-gray-600',
    ];
@endphp
<span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors[$status] ?? 'bg-gray-100 text-gray-700' }}">
    {{ \App\Models\ConsultationRequest::STATUS_LABELS[$status] ?? $status }}
</span>
