<?php

/**
 * Script de test pour l'envoi de notification email
 * 
 * Usage: php test-send-notification.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ticket;
use App\Mail\TicketNotificationMail;
use Illuminate\Support\Facades\Mail;

echo "=== Test d'Envoi de Notification Email ===\n\n";

// Récupérer un ticket avec email
echo "1. Recherche d'un ticket avec email...\n";
$ticket = Ticket::with(['event', 'price'])
    ->whereNotNull('email')
    ->where('email', '!=', '')
    ->first();

if (!$ticket) {
    echo "❌ Aucun ticket trouvé avec un email.\n";
    echo "Créez d'abord un ticket avec un email valide.\n";
    exit(1);
}

echo "✅ Ticket trouvé:\n";
echo "   - Référence: {$ticket->reference}\n";
echo "   - Participant: {$ticket->full_name}\n";
echo "   - Email: {$ticket->email}\n";
echo "   - Événement: {$ticket->event->title}\n";
echo "   - Montant: {$ticket->amount} {$ticket->currency}\n";
echo "   - Statut: {$ticket->payment_status}\n";
echo "\n";

// Vérifier la configuration email
echo "2. Vérification de la configuration email...\n";
$mailConfig = [
    'MAIL_MAILER' => env('MAIL_MAILER'),
    'MAIL_HOST' => env('MAIL_HOST'),
    'MAIL_PORT' => env('MAIL_PORT'),
    'MAIL_USERNAME' => env('MAIL_USERNAME'),
    'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
    'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
];

foreach ($mailConfig as $key => $value) {
    $display = $key === 'MAIL_PASSWORD' ? '***' : ($value ?: 'NON CONFIGURÉ');
    echo "   - {$key}: {$display}\n";
}
echo "\n";

// Demander confirmation
echo "3. Voulez-vous envoyer l'email de test à {$ticket->email}? (y/n): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$confirmation = trim($line);
fclose($handle);

if (strtolower($confirmation) !== 'y') {
    echo "❌ Envoi annulé.\n";
    exit(0);
}

// Envoyer l'email
echo "\n4. Envoi de l'email...\n";
try {
    Mail::to($ticket->email)->send(new TicketNotificationMail($ticket));
    
    echo "✅ Email envoyé avec succès!\n";
    echo "\n";
    echo "Vérifiez la boîte de réception de: {$ticket->email}\n";
    echo "(N'oubliez pas de vérifier le dossier spam)\n";
    echo "\n";
    echo "Détails de l'email envoyé:\n";
    echo "   - De: " . env('MAIL_FROM_ADDRESS') . " (" . env('MAIL_FROM_NAME') . ")\n";
    echo "   - À: {$ticket->email}\n";
    echo "   - Sujet: Votre Billet pour {$ticket->event->title}\n";
    echo "   - Contenu: HTML avec QR code\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur lors de l'envoi:\n";
    echo "   {$e->getMessage()}\n";
    echo "\n";
    echo "Vérifiez:\n";
    echo "   1. La configuration SMTP dans .env\n";
    echo "   2. Les logs: storage/logs/laravel.log\n";
    echo "   3. Que le serveur SMTP est accessible\n";
    exit(1);
}

echo "\n=== Test terminé ===\n";
