<?php

/**
 * Script de prévisualisation des templates email
 * Usage: php preview-email-templates.php
 * 
 * Ce script génère des fichiers HTML pour prévisualiser les templates
 */

require __DIR__ . '/vendor/autoload.php';

// Charger Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Récupérer un ticket de test
$ticket = App\Models\Ticket::with(['event', 'price'])->first();

if (!$ticket) {
    echo "❌ Aucun ticket trouvé dans la base de données.\n";
    echo "💡 Création d'un ticket de démonstration...\n\n";
    
    // Créer des données de démonstration
    $event = (object)[
        'id' => 1,
        'title' => 'Conférence sur l\'Autisme 2024',
        'date' => '2024-03-15',
        'end_date' => '2024-03-16',
        'time' => '09:00',
        'end_time' => '17:00',
        'location' => 'Kinshasa, RDC',
        'venue_details' => 'Centre de Conférences NLC',
        'contact_email' => 'info@nlcrdc.org',
        'contact_phone' => '+243 123 456 789',
    ];
    
    $price = (object)[
        'id' => 1,
        'label' => 'Étudiant - 2 jours',
        'category' => 'student_2days',
        'amount' => 50.00,
        'currency' => 'USD',
    ];
    
    $ticket = (object)[
        'id' => 1,
        'reference' => 'TKT-DEMO-' . time(),
        'full_name' => 'Jean Dupont',
        'email' => 'jean.dupont@example.com',
        'phone' => '+243 812 345 678',
        'category' => 'student_2days',
        'amount' => 50.00,
        'currency' => 'USD',
        'payment_status' => 'completed',
        'qr_data' => json_encode(['reference' => 'TKT-DEMO', 'event_id' => 1]),
        'event' => $event,
        'price' => $price,
    ];
    
    echo "✅ Ticket de démonstration créé\n";
} else {
    echo "🎫 Ticket trouvé: {$ticket->reference}\n";
}
echo "📧 Génération des prévisualisations...\n\n";

// Template 1: Classique
$classicHtml = view('emails.ticket-notification', [
    'ticket' => $ticket,
    'event' => $ticket->event,
    'price' => $ticket->price,
])->render();

file_put_contents('preview-email-classic.html', $classicHtml);
echo "✅ Template Classique: preview-email-classic.html\n";

// Template 2: Boarding Pass
$boardingPassHtml = view('emails.ticket-boarding-pass', [
    'ticket' => $ticket,
    'event' => $ticket->event,
    'price' => $ticket->price,
])->render();

file_put_contents('preview-email-boarding-pass.html', $boardingPassHtml);
echo "✅ Template Boarding Pass: preview-email-boarding-pass.html\n";

echo "\n📂 Ouvrez les fichiers HTML dans votre navigateur pour prévisualiser.\n";
