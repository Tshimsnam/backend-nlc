<?php

/**
 * Créer un ticket de test
 * Usage: php create-test-ticket.php
 */

require __DIR__ . '/vendor/autoload.php';

// Charger Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Ticket;

echo "🎫 Création d'un ticket de test\n\n";

// Vérifier s'il y a des événements
$event = Event::with('event_prices')->first();

if (!$event) {
    echo "❌ Aucun événement trouvé dans la base de données.\n";
    echo "💡 Créez d'abord un événement avec : php artisan db:seed --class=EventSeeder\n";
    exit(1);
}

echo "✅ Événement trouvé: {$event->title}\n";

// Vérifier s'il y a des prix
if ($event->event_prices->isEmpty()) {
    echo "❌ Aucun prix défini pour cet événement.\n";
    exit(1);
}

$price = $event->event_prices->first();
echo "✅ Prix trouvé: {$price->label} - {$price->amount} {$price->currency}\n\n";

// Créer un ticket de test
$ticket = Ticket::create([
    'event_id' => $event->id,
    'event_price_id' => $price->id,
    'full_name' => 'Jean Dupont (Test)',
    'email' => 'jean.dupont@test.com',
    'phone' => '+243 812 345 678',
    'category' => $price->category,
    'days' => 1,
    'amount' => $price->amount,
    'currency' => $price->currency,
    'reference' => strtoupper(\Illuminate\Support\Str::random(10)),
    'pay_type' => 'maxicash',
    'payment_status' => 'completed',
    'qr_data' => json_encode([
        'reference' => 'TEMP',
        'event_id' => $event->id,
        'amount' => $price->amount,
        'currency' => $price->currency,
    ]),
]);

// Mettre à jour le qr_data avec la vraie référence
$ticket->qr_data = json_encode([
    'reference' => $ticket->reference,
    'event_id' => $event->id,
    'amount' => $price->amount,
    'currency' => $price->currency,
]);
$ticket->save();

echo "✅ Ticket créé avec succès !\n\n";
echo "📋 Détails du ticket:\n";
echo "   Référence: {$ticket->reference}\n";
echo "   Nom: {$ticket->full_name}\n";
echo "   Email: {$ticket->email}\n";
echo "   Téléphone: {$ticket->phone}\n";
echo "   Montant: {$ticket->amount} {$ticket->currency}\n";
echo "   Statut: {$ticket->payment_status}\n";
echo "   Événement: {$event->title}\n\n";

echo "🧪 Testez maintenant avec:\n";
echo "   php test-ticket-response.php {$ticket->reference}\n";
