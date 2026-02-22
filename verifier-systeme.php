<?php
/**
 * Script de Vérification du Système de Billets
 * 
 * Ce script vérifie que tous les composants sont en place:
 * - Colonnes de la table events
 * - Statistiques des billets
 * - Données de test
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\Schema;

echo "=== VÉRIFICATION DU SYSTÈME DE BILLETS ===\n\n";

// 1. Vérifier les colonnes de la table events
echo "1. Vérification des colonnes de la table 'events':\n";
$requiredColumns = [
    'end_date',
    'end_time',
    'venue_details',
    'contact_phone',
    'contact_email',
    'organizer',
    'registration_deadline',
    'sponsors'
];

$missingColumns = [];
foreach ($requiredColumns as $column) {
    $exists = Schema::hasColumn('events', $column);
    $status = $exists ? '✅' : '❌';
    echo "   {$status} {$column}\n";
    if (!$exists) {
        $missingColumns[] = $column;
    }
}

if (count($missingColumns) > 0) {
    echo "\n⚠️  ATTENTION: Colonnes manquantes détectées!\n";
    echo "   Exécutez: php artisan migrate\n\n";
} else {
    echo "   ✅ Toutes les colonnes sont présentes!\n\n";
}

// 2. Vérifier les événements
echo "2. Vérification des événements:\n";
$eventsCount = Event::count();
echo "   Total événements: {$eventsCount}\n";

if ($eventsCount > 0) {
    $event = Event::first();
    echo "   Premier événement: {$event->title}\n";
    echo "   - Date: {$event->date}\n";
    echo "   - Date fin: " . ($event->end_date ?? 'Non définie') . "\n";
    echo "   - Lieu: {$event->location}\n";
    echo "   - Lieu détaillé: " . ($event->venue_details ?? 'Non défini') . "\n";
    echo "   - Contact: " . ($event->contact_phone ?? 'Non défini') . "\n";
    echo "   - Email: " . ($event->contact_email ?? 'Non défini') . "\n";
    echo "   - Organisateur: " . ($event->organizer ?? 'Non défini') . "\n";
    echo "   - Date limite: " . ($event->registration_deadline ?? 'Non définie') . "\n";
    echo "   - Sponsors: " . (is_array($event->sponsors) ? count($event->sponsors) . ' sponsors' : 'Non définis') . "\n";
} else {
    echo "   ⚠️  Aucun événement trouvé. Exécutez: php artisan db:seed --class=EventSeeder\n";
}
echo "\n";

// 3. Vérifier les statistiques des billets
echo "3. Statistiques des billets:\n";
$totalTickets = Ticket::count();
$physicalTickets = Ticket::whereNotNull('physical_qr_id')->count();
$onlineTickets = Ticket::whereNull('physical_qr_id')->count();
$completedTickets = Ticket::where('payment_status', 'completed')->count();
$pendingTickets = Ticket::where('payment_status', 'pending_cash')->count();

echo "   Total billets: {$totalTickets}\n";
echo "   - Billets physiques: {$physicalTickets}\n";
echo "   - Billets en ligne: {$onlineTickets}\n";
echo "   - Billets validés: {$completedTickets}\n";
echo "   - Billets en attente: {$pendingTickets}\n";

if ($totalTickets > 0) {
    $physicalCompleted = Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->count();
    $onlineCompleted = Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->count();
    
    $physicalRevenue = Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');
    $onlineRevenue = Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');
    
    echo "\n   Billets Physiques:\n";
    echo "   - Validés: {$physicalCompleted}\n";
    echo "   - Revenus: " . number_format($physicalRevenue, 0, ',', ' ') . " $\n";
    echo "   - Taux validation: " . ($physicalTickets > 0 ? round(($physicalCompleted / $physicalTickets) * 100, 1) : 0) . "%\n";
    
    echo "\n   Billets En Ligne:\n";
    echo "   - Validés: {$onlineCompleted}\n";
    echo "   - Revenus: " . number_format($onlineRevenue, 0, ',', ' ') . " $\n";
    echo "   - Taux validation: " . ($onlineTickets > 0 ? round(($onlineCompleted / $onlineTickets) * 100, 1) : 0) . "%\n";
}
echo "\n";

// 4. Résumé
echo "=== RÉSUMÉ ===\n";
if (count($missingColumns) === 0 && $eventsCount > 0) {
    echo "✅ Le système est complet et fonctionnel!\n";
    echo "   - Toutes les colonnes sont présentes\n";
    echo "   - Les événements sont configurés\n";
    echo "   - Les statistiques sont calculables\n";
    echo "\n👉 Vous pouvez accéder au dashboard admin pour voir les statistiques.\n";
} else {
    echo "⚠️  Le système nécessite des actions:\n";
    if (count($missingColumns) > 0) {
        echo "   - Exécuter: php artisan migrate\n";
    }
    if ($eventsCount === 0) {
        echo "   - Exécuter: php artisan db:seed --class=EventSeeder\n";
    }
}
echo "\n";
