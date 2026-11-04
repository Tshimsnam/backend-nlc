<?php

return [
    'frontend_url' => env('ORIENTATION_FRONTEND_URL', env('FRONTEND_WEBSITE_URL', 'http://localhost:8080')),
    'secretariat_email' => env('NLC_SECRETARIAT_EMAIL', 'info@nlcrdc.org'),
    'centre_address' => env('ORIENTATION_CENTRE_ADDRESS'),
    'default_duration_minutes' => 45,
    'processing_hours' => 72,
    'maxicash_enabled' => env('ORIENTATION_MAXICASH_ENABLED', false),
    'manual_payment_label' => 'Mobile Money / virement',
    'manual_payment_instructions' => env('ORIENTATION_PAYMENT_INSTRUCTIONS'),
    'storage_prefix' => env('ORIENTATION_STORAGE_PREFIX', 'orientation'),
    'link_minutes' => 30,
    'session_hours' => 8,
    'clinical_sections' => [
        'concerns' => 'Préoccupations et problématique actuelle',
        'history' => 'Parcours et antécédents rapportés',
        'communication' => 'Communication et interactions : observations contextualisées',
        'behaviour' => 'Comportements, intérêts et particularités sensorielles',
        'impact' => 'Retentissement à la maison et à l’école',
        'support' => 'Accompagnements, bilans et traitements rapportés',
        'next_steps' => 'Orientation et prochaines étapes proposées',
    ],
];
