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
    echo "Créez d'abord un ticket pour prévisualiser les templates.\n";
    exit(1);
}

echo "🎫 Ticket trouvé: {$ticket->reference}\n";
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
