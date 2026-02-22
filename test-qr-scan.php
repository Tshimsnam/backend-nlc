<?php

/**
 * Script de test pour le système de scan QR
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;
use App\Models\EventScan;

echo "🧪 Test du Système de Scan QR\n";
echo "==============================\n\n";

// 1. Vérifier si l'événement existe
echo "1️⃣ Vérification de l'événement...\n";
$slug = 'le-grand-salon-de-lautisme';
$event = Event::where('slug', $slug)->first();

if (!$event) {
    echo "❌ Événement non trouvé avec le slug: $slug\n";
    echo "📋 Événements disponibles:\n";
    $events = Event::all();
    foreach ($events as $e) {
        echo "   - {$e->title} (slug: {$e->slug})\n";
    }
    exit(1);
}

echo "✅ Événement trouvé: {$event->title}\n";
echo "   ID: {$event->id}\n";
echo "   Slug: {$event->slug}\n\n";

// 2. Créer un scan de test
echo "2️⃣ Création d'un scan de test...\n";
try {
    $scan = EventScan::create([
        'event_id' => $event->id,
        'ip_address' => '192.168.171.100',
        'user_agent' => 'Test Script',
        'device_type' => 'desktop',
        'scanned_at' => now(),
    ]);
    
    echo "✅ Scan créé avec succès!\n";
    echo "   ID: {$scan->id}\n";
    echo "   Event ID: {$scan->event_id}\n";
    echo "   IP: {$scan->ip_address}\n\n";
} catch (\Exception $e) {
    echo "❌ Erreur lors de la création du scan: {$e->getMessage()}\n";
    exit(1);
}

// 3. Compter les scans
echo "3️⃣ Comptage des scans...\n";
$totalScans = EventScan::count();
$eventScans = EventScan::where('event_id', $event->id)->count();

echo "✅ Total des scans dans la base: $totalScans\n";
echo "✅ Scans pour cet événement: $eventScans\n\n";

// 4. Afficher les derniers scans
echo "4️⃣ Derniers scans (5 derniers):\n";
$recentScans = EventScan::with('event')
    ->orderBy('scanned_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentScans as $s) {
    echo "   - {$s->event->title} | {$s->device_type} | {$s->scanned_at}\n";
}

echo "\n";
echo "==============================\n";
echo "✅ Test terminé avec succès!\n";
echo "==============================\n";
