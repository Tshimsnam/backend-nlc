<?php
/**
 * Script de Test des Statistiques de Billets
 * 
 * Ce script affiche les statistiques exactement comme elles apparaissent dans le dashboard
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Ticket;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║     STATISTIQUES DES BILLETS - DASHBOARD ADMIN            ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Statistiques globales
$totalTickets = Ticket::count();
$ticketsPending = Ticket::where('payment_status', 'pending_cash')->count();
$ticketsCompleted = Ticket::where('payment_status', 'completed')->count();
$ticketsFailed = Ticket::where('payment_status', 'failed')->count();
$totalRevenue = Ticket::where('payment_status', 'completed')->sum('amount');

echo "┌─────────────────────────────────────────────────────────────┐\n";
echo "│  STATISTIQUES GLOBALES                                      │\n";
echo "├─────────────────────────────────────────────────────────────┤\n";
echo "│  Total Tickets:        " . str_pad($totalTickets, 10, ' ', STR_PAD_LEFT) . "                          │\n";
echo "│  Tickets Validés:      " . str_pad($ticketsCompleted, 10, ' ', STR_PAD_LEFT) . "                          │\n";
echo "│  Tickets En Attente:   " . str_pad($ticketsPending, 10, ' ', STR_PAD_LEFT) . "                          │\n";
echo "│  Tickets Échoués:      " . str_pad($ticketsFailed, 10, ' ', STR_PAD_LEFT) . "                          │\n";
echo "│  Revenus Total:        " . str_pad(number_format($totalRevenue, 0, ',', ' ') . ' $', 20, ' ', STR_PAD_LEFT) . "          │\n";
echo "└─────────────────────────────────────────────────────────────┘\n\n";

// Statistiques par type de billet
$physicalTickets = Ticket::whereNotNull('physical_qr_id')->count();
$physicalTicketsCompleted = Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->count();
$physicalTicketsRevenue = Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');

$onlineTickets = Ticket::whereNull('physical_qr_id')->count();
$onlineTicketsCompleted = Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->count();
$onlineTicketsRevenue = Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');

// Calcul des taux de validation
$physicalRate = $physicalTickets > 0 ? round(($physicalTicketsCompleted / $physicalTickets) * 100, 1) : 0;
$onlineRate = $onlineTickets > 0 ? round(($onlineTicketsCompleted / $onlineTickets) * 100, 1) : 0;

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  🔲 BILLETS PHYSIQUES (QR Code)                          ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║                                                           ║\n";
echo "║  Total créés:          " . str_pad($physicalTickets, 10, ' ', STR_PAD_LEFT) . "                          ║\n";
echo "║                                                           ║\n";
echo "║  ┌──────────────────┐  ┌──────────────────┐             ║\n";
echo "║  │ Validés          │  │ Revenus          │             ║\n";
echo "║  │ " . str_pad($physicalTicketsCompleted, 16, ' ', STR_PAD_LEFT) . " │  │ " . str_pad(number_format($physicalTicketsRevenue, 0, ',', ' ') . ' $', 16, ' ', STR_PAD_LEFT) . " │             ║\n";
echo "║  └──────────────────┘  └──────────────────┘             ║\n";
echo "║                                                           ║\n";
echo "║  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  ║\n";
echo "║  " . str_pad($physicalRate . '% de taux de validation', 57, ' ', STR_PAD_RIGHT) . "║\n";
echo "║                                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║  💻 BILLETS EN LIGNE (Site Web)                          ║\n";
echo "╠═══════════════════════════════════════════════════════════╣\n";
echo "║                                                           ║\n";
echo "║  Total créés:          " . str_pad($onlineTickets, 10, ' ', STR_PAD_LEFT) . "                          ║\n";
echo "║                                                           ║\n";
echo "║  ┌──────────────────┐  ┌──────────────────┐             ║\n";
echo "║  │ Validés          │  │ Revenus          │             ║\n";
echo "║  │ " . str_pad($onlineTicketsCompleted, 16, ' ', STR_PAD_LEFT) . " │  │ " . str_pad(number_format($onlineTicketsRevenue, 0, ',', ' ') . ' $', 16, ' ', STR_PAD_LEFT) . " │             ║\n";
echo "║  └──────────────────┘  └──────────────────┘             ║\n";
echo "║                                                           ║\n";
echo "║  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  ║\n";
echo "║  " . str_pad($onlineRate . '% de taux de validation', 57, ' ', STR_PAD_RIGHT) . "║\n";
echo "║                                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n\n";

// Détails des billets
if ($totalTickets > 0) {
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    echo "│  DÉTAILS DES DERNIERS BILLETS                               │\n";
    echo "├─────────────────────────────────────────────────────────────┤\n";
    
    $recentTickets = Ticket::orderBy('created_at', 'desc')->limit(5)->get();
    
    foreach ($recentTickets as $ticket) {
        $type = $ticket->physical_qr_id ? '🔲 Physique' : '💻 En ligne';
        $status = $ticket->payment_status === 'completed' ? '✅ Validé' : 
                 ($ticket->payment_status === 'pending_cash' ? '⏰ En attente' : '❌ Échoué');
        
        echo "│  " . str_pad($ticket->reference, 15, ' ', STR_PAD_RIGHT) . " │ ";
        echo str_pad($type, 15, ' ', STR_PAD_RIGHT) . " │ ";
        echo str_pad($status, 15, ' ', STR_PAD_RIGHT) . " │\n";
        
        if ($ticket->physical_qr_id) {
            echo "│  QR: " . str_pad(substr($ticket->physical_qr_id, 0, 20), 54, ' ', STR_PAD_RIGHT) . "│\n";
        }
        echo "│  " . str_pad($ticket->full_name, 58, ' ', STR_PAD_RIGHT) . "│\n";
        echo "│  " . str_pad($ticket->amount . ' ' . $ticket->currency, 58, ' ', STR_PAD_RIGHT) . "│\n";
        echo "├─────────────────────────────────────────────────────────────┤\n";
    }
    
    echo "└─────────────────────────────────────────────────────────────┘\n\n";
}

// Résumé
echo "═══════════════════════════════════════════════════════════\n";
echo "  RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════════\n\n";

if ($totalTickets === 0) {
    echo "⚠️  Aucun billet trouvé dans la base de données.\n";
    echo "   Créez des billets pour voir les statistiques.\n\n";
} else {
    $physicalPercent = $totalTickets > 0 ? round(($physicalTickets / $totalTickets) * 100, 1) : 0;
    $onlinePercent = $totalTickets > 0 ? round(($onlineTickets / $totalTickets) * 100, 1) : 0;
    
    echo "✅ Répartition des billets:\n";
    echo "   - Physiques: {$physicalTickets} ({$physicalPercent}%)\n";
    echo "   - En ligne: {$onlineTickets} ({$onlinePercent}%)\n\n";
    
    echo "💰 Répartition des revenus:\n";
    echo "   - Physiques: " . number_format($physicalTicketsRevenue, 0, ',', ' ') . " $ (" . 
         ($totalRevenue > 0 ? round(($physicalTicketsRevenue / $totalRevenue) * 100, 1) : 0) . "%)\n";
    echo "   - En ligne: " . number_format($onlineTicketsRevenue, 0, ',', ' ') . " $ (" . 
         ($totalRevenue > 0 ? round(($onlineTicketsRevenue / $totalRevenue) * 100, 1) : 0) . "%)\n\n";
    
    echo "📊 Taux de validation:\n";
    echo "   - Physiques: {$physicalRate}%\n";
    echo "   - En ligne: {$onlineRate}%\n";
    echo "   - Global: " . ($totalTickets > 0 ? round(($ticketsCompleted / $totalTickets) * 100, 1) : 0) . "%\n\n";
}

echo "═══════════════════════════════════════════════════════════\n\n";
echo "👉 Accédez au dashboard admin pour voir ces statistiques\n";
echo "   avec un design moderne et coloré!\n\n";
