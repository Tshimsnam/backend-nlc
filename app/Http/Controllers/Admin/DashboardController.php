<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Event;
use App\Models\User;
use App\Models\EventScan;
use App\Models\TicketScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Vue Blade du dashboard (pour l'admin web)
     */
    public function view(Request $request)
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
            'total_ticket_scans' => TicketScan::count(), // Scans de billets (validation entrée)
            'total_event_scans' => EventScan::count(), // Scans d'événements (consultation page)
            'tickets_scanned' => Ticket::where('scan_count', '>', 0)->count(),
        ];

        // Tickets récents avec filtres et pagination
        $query = Ticket::with(['event']);

        // Filtre par recherche
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtre par statut
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('payment_status', $request->status);
        }

        $recentTickets = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->query());

        // Tous les tickets pour l'onglet Tickets (avec filtres et pagination)
        // Par défaut, afficher uniquement les tickets validés
        $allTicketsQuery = Ticket::with(['event', 'price']);

        // Filtre par défaut: tickets validés uniquement
        $ticketsStatus = $request->get('tickets_status', 'completed');
        if ($ticketsStatus !== 'all') {
            $allTicketsQuery->where('payment_status', $ticketsStatus);
        }

        // Filtres pour l'onglet Tickets
        if ($request->has('tickets_search') && $request->tickets_search) {
            $search = $request->tickets_search;
            $allTicketsQuery->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->has('tickets_pay_type') && $request->tickets_pay_type !== 'all') {
            $allTicketsQuery->where('pay_type', $request->tickets_pay_type);
        }

        $allTickets = $allTicketsQuery->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'tickets_page')
            ->appends($request->query());

        // Agents mobile (utilisateurs) avec filtres et pagination
        // Exclure les utilisateurs avec les rôles "Parent" et "Administrateur"
        $agentsQuery = User::with('roles')
            ->whereHas('roles', function($q) {
                $q->whereNotIn('name', ['Parent', 'Administrateur']);
            });

        if ($request->has('agents_search') && $request->agents_search) {
            $search = $request->agents_search;
            $agentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $agents = $agentsQuery->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'agents_page')
            ->appends($request->query());

        // Récupérer tous les rôles sauf Parent et Administrateur pour le formulaire de création
        $availableRoles = \App\Models\Role::whereNotIn('name', ['Parent', 'Administrateur'])->get();

        // Récupérer tous les événements pour la génération de QR codes physiques
        $events = Event::orderBy('date', 'desc')->get();

        // Événements avec pagination pour l'onglet Événements
        $eventsQuery = Event::with('event_prices')->withCount(['tickets', 'event_prices']);
        
        if ($request->has('events_search') && $request->events_search) {
            $search = $request->events_search;
            $eventsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $eventsList = $eventsQuery->orderBy('date', 'desc')
            ->paginate(15, ['*'], 'events_page')
            ->appends($request->query());

        return view('admin.dashboard', compact('user', 'stats', 'recentTickets', 'allTickets', 'agents', 'availableRoles', 'events', 'eventsList'));
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
            'total_ticket_scans' => TicketScan::count(), // Scans de billets (validation entrée)
            'total_event_scans' => EventScan::count(), // Scans d'événements (consultation page)
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
     * Liste des tickets en attente de validation avec pagination et filtres
     */
    public function pendingTickets(Request $request): JsonResponse
    {
        $query = Ticket::with(['event', 'price']);

        // Filtre par statut de paiement
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('payment_status', $request->status);
        }

        // Filtre par recherche (référence, nom, email, téléphone)
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtre par événement
        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        // Filtre par mode de paiement
        if ($request->has('pay_type') && $request->pay_type) {
            $query->where('pay_type', $request->pay_type);
        }

        // Filtre par date
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

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

    /**
     * Créer un nouvel agent mobile
     */
    public function createAgent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Vérifier que le rôle n'est pas Parent ou Administrateur
        $role = \App\Models\Role::findOrFail($request->role_id);
        if (in_array($role->name, ['Parent', 'Administrateur'])) {
            return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
                ->with('error', 'Impossible de créer un utilisateur avec ce rôle.');
        }

        // Créer l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'email_verified_at' => now(), // Marquer comme vérifié automatiquement
        ]);

        // Attacher le rôle
        $user->roles()->attach($request->role_id);

        return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
            ->with('success', 'Agent créé avec succès!');
    }

    /**
     * Mettre à jour un événement
     */
    public function updateEvent(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'prices' => 'nullable|array',
            'prices.*.id' => 'nullable|exists:event_prices,id',
            'prices.*.category' => 'required|string',
            'prices.*.amount' => 'required|numeric|min:0',
            'prices.*.currency' => 'required|string|max:10',
            'prices.*.label' => 'nullable|string|max:255',
            'prices.*.description' => 'nullable|string',
        ]);

        $event = Event::findOrFail($id);
        
        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'location' => $request->location,
            'capacity' => $request->max_participants,
        ]);

        // Mettre à jour les prix
        if ($request->has('prices')) {
            foreach ($request->prices as $priceData) {
                if (isset($priceData['id'])) {
                    // Mettre à jour un prix existant
                    $price = \App\Models\EventPrice::find($priceData['id']);
                    if ($price && $price->event_id == $event->id) {
                        $price->update([
                            'category' => $priceData['category'],
                            'amount' => $priceData['amount'],
                            'currency' => $priceData['currency'],
                            'label' => $priceData['label'] ?? null,
                            'description' => $priceData['description'] ?? null,
                        ]);
                    }
                } else {
                    // Créer un nouveau prix
                    $event->event_prices()->create([
                        'category' => $priceData['category'],
                        'amount' => $priceData['amount'],
                        'currency' => $priceData['currency'],
                        'label' => $priceData['label'] ?? null,
                        'description' => $priceData['description'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.dashboard.view', ['tab' => 'events'])
            ->with('success', 'Événement mis à jour avec succès!');
    }

    /**
     * Supprimer un prix d'événement
     */
    public function deleteEventPrice($id)
    {
        $price = \App\Models\EventPrice::findOrFail($id);
        
        // Vérifier qu'il n'y a pas de tickets associés
        if ($price->tickets()->count() > 0) {
            return redirect()->route('admin.dashboard.view', ['tab' => 'events'])
                ->with('error', 'Impossible de supprimer ce tarif car des billets y sont associés.');
        }
        
        $price->delete();
        
        return redirect()->route('admin.dashboard.view', ['tab' => 'events'])
            ->with('success', 'Tarif supprimé avec succès!');
    }
}

