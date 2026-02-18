<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\User;
use App\Models\EventScan;
use App\Models\TicketScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Vue Blade du dashboard (pour l'admin web)
     */
    public function view()
    {
        $user = session('admin_user');
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Statistiques générales
        $stats = [
            'total_tickets' => Ticket::count(),
            'tickets_pending' => Ticket::where('payment_status', 'pending_cash')->count(),
            'tickets_completed' => Ticket::where('payment_status', 'completed')->count(),
            'tickets_failed' => Ticket::where('payment_status', 'failed')->count(),
            'total_revenue' => Ticket::where('payment_status', 'completed')->sum('amount'),
            'total_events' => Event::count(),
            'active_events' => Event::where('date', '>=', now())->count(),
            'total_users' => User::count(),
            'total_qr_scans' => TicketScan::count(),
            'tickets_scanned' => Ticket::where('scan_count', '>', 0)->count(),
        ];

        // Tickets récents
        $recentTickets = Ticket::with(['event'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('user', 'stats', 'recentTickets'));
    }

    /**
     * Statistiques du dashboard (API JSON)
     */
    public function index(): JsonResponse
    {
        // Statistiques générales
        $stats = [
            'total_tickets' => Ticket::count(),
            'tickets_pending' => Ticket::where('payment_status', 'pending_cash')->count(),
            'tickets_completed' => Ticket::where('payment_status', 'completed')->count(),
            'tickets_failed' => Ticket::where('payment_status', 'failed')->count(),
            'total_revenue' => Ticket::where('payment_status', 'completed')->sum('amount'),
            'total_events' => Event::count(),
            'active_events' => Event::where('date', '>=', now())->count(),
            'total_users' => User::count(),
            'total_qr_scans' => TicketScan::count(),
            'tickets_scanned' => Ticket::where('scan_count', '>', 0)->count(),
        ];

        // Tickets par mode de paiement
        $ticketsByPaymentMode = Ticket::select('pay_type', DB::raw('count(*) as count'))
            ->groupBy('pay_type')
            ->get();

        // Tickets par statut
        $ticketsByStatus = Ticket::select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->get();

        // Revenus par événement
        $revenueByEvent = Ticket::select('events.title', DB::raw('SUM(tickets.amount) as revenue'))
            ->join('events', 'tickets.event_id', '=', 'events.id')
            ->where('tickets.payment_status', 'completed')
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // Tickets récents
        $recentTickets = Ticket::with(['event'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Évolution des tickets (7 derniers jours)
        $ticketsEvolution = Ticket::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'stats' => $stats,
            'tickets_by_payment_mode' => $ticketsByPaymentMode,
            'tickets_by_status' => $ticketsByStatus,
            'revenue_by_event' => $revenueByEvent,
            'recent_tickets' => $recentTickets,
            'tickets_evolution' => $ticketsEvolution,
        ]);
    }

    /**
     * Liste des tickets en attente de validation
     */
    public function pendingTickets(): JsonResponse
    {
        $tickets = Ticket::with(['event'])
            ->where('payment_status', 'pending_cash')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($tickets);
    }

    /**
     * Valider un ticket (depuis le web)
     */
    public function validateTicketWeb(string $reference)
    {
        // Vérifier que l'utilisateur est connecté via session
        $user = session('admin_user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté.');
        }

        // Vérifier que l'utilisateur a le rôle admin
        $hasAdminRole = User::find($user->id)->roles()->where('name', 'admin')->exists();
        
        if (!$hasAdminRole) {
            return redirect()->route('admin.dashboard.view')
                ->with('error', 'Accès refusé. Seuls les administrateurs peuvent valider les tickets.');
        }

        $ticket = Ticket::where('reference', $reference)->firstOrFail();

        if ($ticket->payment_status !== 'pending_cash') {
            return redirect()->route('admin.dashboard.view')
                ->with('error', 'Ce ticket n\'est pas en attente de validation.');
        }

        $ticket->update(['payment_status' => 'completed']);

        return redirect()->route('admin.dashboard.view')
            ->with('success', 'Ticket validé avec succès!');
    }

    /**
     * Valider un ticket (API JSON)
     */
    public function validateTicket(string $reference): JsonResponse
    {
        $ticket = Ticket::where('reference', $reference)->firstOrFail();

        if ($ticket->payment_status !== 'pending_cash') {
            return response()->json([
                'success' => false,
                'message' => 'Ce ticket n\'est pas en attente de validation.',
            ], 400);
        }

        $ticket->update(['payment_status' => 'completed']);

        return response()->json([
            'success' => true,
            'message' => 'Ticket validé avec succès',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Liste des utilisateurs
     */
    public function users(): JsonResponse
    {
        $users = User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Statistiques des événements
     */
    public function eventsStats(): JsonResponse
    {
        $events = Event::withCount(['tickets'])
            ->with(['tickets' => function ($query) {
                $query->select('event_id', DB::raw('SUM(amount) as total_revenue'))
                    ->where('payment_status', 'completed')
                    ->groupBy('event_id');
            }])
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($events);
    }

    /**
     * Statistiques des scans de billets
     */
    public function scanStats(): JsonResponse
    {
        // Statistiques globales
        $globalStats = [
            'total_scans' => TicketScan::count(),
            'unique_tickets_scanned' => Ticket::where('scan_count', '>', 0)->count(),
            'total_tickets' => Ticket::count(),
            'scan_rate' => Ticket::count() > 0 
                ? round((Ticket::where('scan_count', '>', 0)->count() / Ticket::count()) * 100, 2) 
                : 0,
        ];

        // Scans par événement
        $scansByEvent = Event::select('events.id', 'events.title', 'events.date')
            ->withCount(['tickets as total_tickets'])
            ->withCount(['tickets as scanned_tickets' => function ($query) {
                $query->where('scan_count', '>', 0);
            }])
            ->addSelect([
                'total_scans' => TicketScan::selectRaw('COUNT(*)')
                    ->whereColumn('event_id', 'events.id')
            ])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($event) {
                $event->scan_rate = $event->total_tickets > 0 
                    ? round(($event->scanned_tickets / $event->total_tickets) * 100, 2) 
                    : 0;
                return $event;
            });

        // Scans récents
        $recentScans = TicketScan::with([
            'ticket:id,reference,full_name,event_id',
            'event:id,title',
            'scannedBy:id,name'
        ])
            ->orderBy('scanned_at', 'desc')
            ->limit(20)
            ->get();

        // Scans par jour (7 derniers jours)
        $scansByDay = TicketScan::select(
            DB::raw('DATE(scanned_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('scanned_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'global_stats' => $globalStats,
            'scans_by_event' => $scansByEvent,
            'recent_scans' => $recentScans,
            'scans_by_day' => $scansByDay,
        ]);
    }
}
