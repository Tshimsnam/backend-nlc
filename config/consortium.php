<?php

return [
    'frontend_url' => env('CONSORTIUM_FRONTEND_URL', env('FRONTEND_WEBSITE_URL', 'http://localhost:8080')),
    'secretariat_email' => env('CONSORTIUM_SECRETARIAT_EMAIL', 'info@nlcrdc.org'),
    // Renseigner l'accord officiel avant l'ouverture au public. Aucun texte contractuel fictif.
    'agreement_url' => env('CONSORTIUM_AGREEMENT_URL'),
    'agreement_version' => env('CONSORTIUM_AGREEMENT_VERSION'),
    'max_file_kb' => 10240,
    'storage_prefix' => env('CONSORTIUM_STORAGE_PREFIX', 'consortium'),
    'max_storage_bytes' => 104857600,
    'document_kinds' => [
        'statutes' => 'Statuts de la structure',
        'internal_rules' => 'Règlement intérieur',
        'authorization' => 'Autorisation de fonctionnement / personnalité juridique',
        'activity_report' => 'Rapport d’activités de la dernière année (facultatif)',
        'signed_agreement' => 'Accord de consortium signé et cacheté',
        'other' => 'Autre justificatif',
    ],
    'required_documents' => ['statutes', 'internal_rules', 'authorization'],
    'provinces' => ['Bas-Uele', 'Équateur', 'Haut-Katanga', 'Haut-Lomami', 'Haut-Uele', 'Ituri', 'Kasaï', 'Kasaï-Central', 'Kasaï-Oriental', 'Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Lomami', 'Lualaba', 'Mai-Ndombe', 'Maniema', 'Mongala', 'Nord-Kivu', 'Nord-Ubangi', 'Sankuru', 'Sud-Kivu', 'Sud-Ubangi', 'Tanganyika', 'Tshopo', 'Tshuapa'],
    'expertise' => [
        'awareness' => 'Sensibilisation et plaidoyer', 'diagnosis' => 'Dépistage et diagnostic précoce',
        'therapy' => 'Prise en charge thérapeutique', 'education' => 'Éducation spéciale et scolarisation',
        'training' => 'Formation des parents et professionnels', 'research' => 'Recherche et collecte de données', 'other' => 'Autre',
    ],
    'needs' => ['funding' => 'Financement d’activités', 'equipment' => 'Équipement', 'training' => 'Formation', 'technical' => 'Accompagnement technique', 'partnerships' => 'Recherche de partenaires', 'other' => 'Autre'],
];
