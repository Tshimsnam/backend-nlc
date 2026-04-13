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
            
            // Statistiques par type de billet
            'physical_tickets' => Ticket::whereNotNull('physical_qr_id')->count(),
            'physical_tickets_completed' => Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->count(),
            'physical_tickets_revenue' => Ticket::whereNotNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount'),
            
            'online_tickets' => Ticket::whereNull('physical_qr_id')->count(),
            'online_tickets_completed' => Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->count(),
            'online_tickets_revenue' => Ticket::whereNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount'),
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

        // Billets non payés (générés en ligne mais sans paiement)
        $unpaidQuery = Ticket::with(['event'])
            ->whereNull('physical_qr_id') // Billets en ligne uniquement
            ->where('payment_status', '!=', 'completed') // Non payés
            ->where('payment_status', '!=', 'cancelled'); // Non annulés

        if ($request->has('unpaid_search') && $request->unpaid_search) {
            $search = $request->unpaid_search;
            $unpaidQuery->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $unpaidTickets = $unpaidQuery->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'unpaid_page')
            ->appends($request->query());

        // Compter les billets non payés pour le badge
        $unpaidCount = Ticket::whereNull('physical_qr_id')
            ->where('payment_status', '!=', 'completed')
            ->where('payment_status', '!=', 'cancelled')
            ->count();

        // Données pour l'onglet Rapport — seulement si le formulaire a été soumis
        $reportDateFrom  = $request->get('report_date_from');
        $reportDateTo    = $request->get('report_date_to');
        $reportEventId   = $request->get('report_event_id');
        $reportGenerated = $request->has('report_date_from') && $reportDateFrom && $reportDateTo;
        $reportData      = $reportGenerated ? $this->buildReportData($reportDateFrom, $reportDateTo, $reportEventId) : null;

        // Compteurs pour les badges sidebar
        $quizCount       = \App\Models\QuizResponse::distinct('session_token')->count('session_token');
        $evaluationCount = \Illuminate\Support\Facades\DB::table('colloque_evaluations')->count();
        $quizEventId     = request('quiz_event_id');
        $evalEventId     = request('eval_event_id');

        // Données Quiz
        $quizStats = null;
        if (request('tab') === 'quiz') {
            $quizStats = $this->buildQuizStats(request('quiz_event_id'));
        }

        // Données Évaluation
        $evaluationStats = null;
        if (request('tab') === 'evaluation') {
            $evaluationStats = $this->buildEvaluationStats(request('eval_event_id'));
        }

        // Données Configuration
        $eventConfigs = null;
        if (request('tab') === 'configuration') {
            $eventConfigs = \App\Models\EventConfig::all()->keyBy('event_id');
        }

        return view('admin.dashboard', compact(
            'user', 'stats', 'recentTickets', 'allTickets', 'agents', 'availableRoles',
            'events', 'eventsList', 'unpaidTickets', 'unpaidCount',
            'reportData', 'reportDateFrom', 'reportDateTo', 'reportEventId', 'reportGenerated',
            'quizCount', 'evaluationCount', 'quizStats', 'evaluationStats',
            'quizEventId', 'evalEventId', 'eventConfigs'
        ));
    }

    /**
     * Stats du quiz GSA 2026
     */
    private function buildQuizStats(?string $eventId = null): array
    {
        $query = \App\Models\QuizResponse::query()
            ->when($eventId, fn($q) => $q->where('event_id', $eventId));
        // Sans filtre événement → on montre tout

        $total = (clone $query)->distinct('session_token')->count('session_token');

        $questionsQuery = \App\Models\QuizQuestion::where('is_active', true)->orderBy('order');
        if ($eventId) {
            $questionsQuery->where('event_id', $eventId);
        }
        $questions = $questionsQuery->get();

        $byQuestion = (clone $query)
            ->select('question_id', 'answer', DB::raw('COUNT(*) as count'))
            ->groupBy('question_id', 'answer')->orderBy('question_id')->get()
            ->groupBy('question_id')
            ->map(fn($rows) => $rows->keyBy('answer')->map(fn($r) => (int) $r->count));

        $recentResponses = (clone $query)
            ->select('session_token', DB::raw('MIN(created_at) as submitted_at'))
            ->groupBy('session_token')->orderByDesc('submitted_at')->limit(10)->get();

        return compact('total', 'questions', 'byQuestion', 'recentResponses');
    }

    /**
     * Stats des évaluations colloque
     */
    private function buildEvaluationStats(?string $eventId = null): array
    {
        $baseQuery = fn() => DB::table('colloque_evaluations')
            ->when($eventId, fn($q) => $q->where('event_id', $eventId));

        $total   = $baseQuery()->count();
        $noteAvg = round($baseQuery()->whereNotNull('note_globale')->avg('note_globale'), 1);

        $evalQuestions = \App\Models\EvaluationQuestion::where('section', 'tsa')
            ->where('is_active', true)
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->orderBy('order')->get();

        $byProfil       = $baseQuery()->select('profil', DB::raw('COUNT(*) as count'))->groupBy('profil')->get();
        $byAdequation   = $baseQuery()->select('adequation_theme', DB::raw('COUNT(*) as count'))->groupBy('adequation_theme')->get();
        $byOrganisation = $baseQuery()->select('organisation_generale', DB::raw('COUNT(*) as count'))->groupBy('organisation_generale')->get();

        $tsaStats = [];
        foreach (['tsa_q1','tsa_q2','tsa_q3','tsa_q4','tsa_q5'] as $q) {
            $tsaStats[$q] = $baseQuery()
                ->select($q . ' as answer', DB::raw('COUNT(*) as count'))
                ->whereNotNull($q)->groupBy($q)->get()
                ->keyBy('answer')->map(fn($r) => (int) $r->count);
        }

        $recent = $baseQuery()
            ->select('id','full_name','profil','note_globale','adequation_theme','created_at')
            ->orderByDesc('created_at')->limit(10)->get();

        return compact('total','noteAvg','evalQuestions','byProfil','byAdequation','byOrganisation','tsaStats','recent');
    }

    // ── CRUD Quiz Questions ────────────────────────────────────────────────────

    public function storeQuizQuestion(Request $request)
    {
        $request->validate(['text' => 'required|string|max:500', 'correct_answer' => 'required|in:vrai,faux,peut_etre', 'event_id' => 'nullable|exists:events,id']);
        $max = \App\Models\QuizQuestion::where('quiz_slug', 'gsa-2026')->max('order') ?? 0;
        \App\Models\QuizQuestion::create(['quiz_slug'=>'gsa-2026','text'=>$request->text,'correct_answer'=>$request->correct_answer,'order'=>$max+1,'is_active'=>true,'event_id'=>$request->event_id]);
        return redirect()->route('admin.dashboard.view', ['tab'=>'quiz'])->with('success', 'Question ajoutée.');
    }

    public function updateQuizQuestion(Request $request, $id)
    {
        $request->validate(['text' => 'required|string|max:500', 'correct_answer' => 'required|in:vrai,faux,peut_etre', 'event_id' => 'nullable|exists:events,id']);
        \App\Models\QuizQuestion::findOrFail($id)->update(['text'=>$request->text,'correct_answer'=>$request->correct_answer,'event_id'=>$request->event_id]);
        return redirect()->route('admin.dashboard.view', ['tab'=>'quiz'])->with('success', 'Question mise à jour.');
    }

    public function deleteQuizQuestion($id)
    {
        \App\Models\QuizQuestion::findOrFail($id)->delete();
        return redirect()->route('admin.dashboard.view', ['tab'=>'quiz'])->with('success', 'Question supprimée.');
    }

    // ── CRUD Evaluation Questions ──────────────────────────────────────────────

    public function storeEvalQuestion(Request $request)
    {
        $request->validate(['text'=>'required|string|max:500','options'=>'required|array|min:2|max:6','options.*'=>'required|string|max:200','correct_answer'=>'nullable|string|max:5','event_id'=>'nullable|exists:events,id']);
        $max = \App\Models\EvaluationQuestion::where('section','tsa')->max('order') ?? 0;
        \App\Models\EvaluationQuestion::create(['section'=>'tsa','text'=>$request->text,'options'=>$request->options,'correct_answer'=>$request->correct_answer,'order'=>$max+1,'is_active'=>true,'event_id'=>$request->event_id]);
        return redirect()->route('admin.dashboard.view', ['tab'=>'evaluation'])->with('success', 'Question ajoutée.');
    }

    public function updateEvalQuestion(Request $request, $id)
    {
        $request->validate(['text'=>'required|string|max:500','options'=>'required|array|min:2|max:6','options.*'=>'required|string|max:200','correct_answer'=>'nullable|string|max:5','event_id'=>'nullable|exists:events,id']);
        \App\Models\EvaluationQuestion::findOrFail($id)->update(['text'=>$request->text,'options'=>$request->options,'correct_answer'=>$request->correct_answer,'event_id'=>$request->event_id]);
        return redirect()->route('admin.dashboard.view', ['tab'=>'evaluation'])->with('success', 'Question mise à jour.');
    }

    public function deleteEvalQuestion($id)
    {
        \App\Models\EvaluationQuestion::findOrFail($id)->delete();
        return redirect()->route('admin.dashboard.view', ['tab'=>'evaluation'])->with('success', 'Question supprimée.');
    }

    /**
     * Sauvegarder la configuration d'un événement
     */
    public function saveConfiguration(Request $request)
    {
        $user = session('admin_user');
        if (!$user) return redirect()->route('login');

        $request->validate([
            'configs'                        => 'required|array',
            'configs.*.event_id'             => 'required|exists:events,id',
            'configs.*.quiz_enabled'         => 'nullable|boolean',
            'configs.*.evaluation_enabled'   => 'nullable|boolean',
            'configs.*.certificate_enabled'  => 'nullable|boolean',
        ]);

        foreach ($request->configs as $cfg) {
            \App\Models\EventConfig::updateOrCreate(
                ['event_id' => $cfg['event_id']],
                [
                    'quiz_enabled'        => isset($cfg['quiz_enabled']),
                    'evaluation_enabled'  => isset($cfg['evaluation_enabled']),
                    'certificate_enabled' => isset($cfg['certificate_enabled']),
                ]
            );
        }

        return redirect()->route('admin.dashboard.view', ['tab' => 'configuration'])
            ->with('success', 'Configuration sauvegardée.');
    }
    public function showEvaluation($id)
    {
        $user = session('admin_user');
        if (!$user) return redirect()->route('login');

        $evaluation = DB::table('colloque_evaluations')->where('id', $id)->first();
        if (!$evaluation) abort(404);

        $tsaQuestions = \App\Models\EvaluationQuestion::where('section', 'tsa')
            ->where('is_active', true)->orderBy('order')->get();

        return view('admin.evaluation-detail', compact('user', 'evaluation', 'tsaQuestions'));
    }

    /**
     * Calcule toutes les statistiques pour la période donnée
     */
    private function buildReportData(string $dateFrom, string $dateTo, ?string $eventId = null): array
    {
        $from = $dateFrom . ' 00:00:00';
        $to   = $dateTo   . ' 23:59:59';

        // Base query helper
        $ticketBase = fn() => Ticket::whereBetween('created_at', [$from, $to])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId));

        // --- Statistiques générales ---
        $totalTickets = $ticketBase()->count();
        $confirmed    = $ticketBase()->where('payment_status', 'completed')->count();
        $pending      = $ticketBase()->whereNotIn('payment_status', ['completed', 'cancelled'])->count();
        $ticketScans  = TicketScan::whereBetween('scanned_at', [$from, $to])
                            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                            ->count();

        // --- Ventes par canal ---
        $physicalTotal     = $ticketBase()->whereNotNull('physical_qr_id')->count();
        $physicalValidated = $ticketBase()->whereNotNull('physical_qr_id')->where('payment_status', 'completed')->count();
        $physicalRevenue   = $ticketBase()->whereNotNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');

        $onlineTotal     = $ticketBase()->whereNull('physical_qr_id')->count();
        $onlineValidated = $ticketBase()->whereNull('physical_qr_id')->where('payment_status', 'completed')->count();
        $onlineRevenue   = $ticketBase()->whereNull('physical_qr_id')->where('payment_status', 'completed')->sum('amount');

        // --- Activité des agents (validations + scans) ---
        $agentActivity = User::select(
                'users.id',
                'users.name as agent_name',
                DB::raw('COUNT(DISTINCT t.id) as total_validations'),
                DB::raw('SUM(CASE WHEN t.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
                DB::raw('SUM(CASE WHEN t.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online'),
                DB::raw('COALESCE(SUM(t.amount), 0) as revenue'),
                DB::raw('COUNT(DISTINCT ts.id) as total_scans')
            )
            ->join('tickets as t', function($join) use ($from, $to, $eventId) {
                $join->on('t.validated_by', '=', 'users.id')
                     ->where('t.payment_status', 'completed')
                     ->whereBetween('t.updated_at', [$from, $to]);
                if ($eventId) {
                    $join->where('t.event_id', $eventId);
                }
            })
            ->leftJoin('ticket_scans as ts', function($join) use ($from, $to, $eventId) {
                $join->on('ts.scanned_by', '=', 'users.id')
                     ->whereBetween('ts.scanned_at', [$from, $to]);
                if ($eventId) {
                    $join->where('ts.event_id', $eventId);
                }
            })
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_validations')
            ->get();

        // --- Statistiques des événements ---
        $eventScans    = EventScan::where(function($q) use ($from, $to) {
                                $q->whereBetween('scanned_at', [$from, $to])
                                  ->orWhere(function($q2) use ($from, $to) {
                                      $q2->whereNull('scanned_at')->whereBetween('created_at', [$from, $to]);
                                  });
                            })
                            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                            ->count();
        $uniqueScanned = $ticketBase()->where('scan_count', '>', 0)->count();
        $totalRevenue  = Ticket::where('payment_status', 'completed')
                            ->whereBetween('updated_at', [$from, $to])
                            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                            ->sum('amount');

        // Détail par événement
        $eventDetails = Event::select('events.id', 'events.title')
            ->when($eventId, fn($q) => $q->where('events.id', $eventId))
            ->withCount(['tickets as tickets_created' => fn($q) => $q->whereBetween('tickets.created_at', [$from, $to])])
            ->withCount(['tickets as tickets_validated' => fn($q) => $q->where('payment_status', 'completed')->whereBetween('tickets.created_at', [$from, $to])])
            ->addSelect([
                'event_revenue' => Ticket::selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('event_id', 'events.id')
                    ->where('payment_status', 'completed')
                    ->whereBetween('created_at', [$from, $to]),
            ])
            ->having('tickets_created', '>', 0)
            ->orderByDesc('tickets_created')
            ->get();

        // Nom de l'événement filtré
        $filteredEvent = $eventId ? Event::find($eventId) : null;

        // --- Billets par type/catégorie de prix ---
        $ticketsByCategory = Ticket::select('category', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN payment_status = "completed" THEN 1 ELSE 0 END) as validated'), DB::raw('SUM(CASE WHEN payment_status = "completed" THEN amount ELSE 0 END) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        // Billets par mode de paiement
        $ticketsByPayType = Ticket::select('pay_type', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN payment_status = "completed" THEN 1 ELSE 0 END) as validated'), DB::raw('SUM(CASE WHEN payment_status = "completed" THEN amount ELSE 0 END) as revenue'))
            ->whereBetween('created_at', [$from, $to])
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->groupBy('pay_type')
            ->orderByDesc('total')
            ->get();

        return compact(
            'totalTickets', 'confirmed', 'pending', 'ticketScans',
            'physicalTotal', 'physicalValidated', 'physicalRevenue',
            'onlineTotal', 'onlineValidated', 'onlineRevenue',
            'agentActivity',
            'eventScans', 'uniqueScanned', 'totalRevenue', 'eventDetails',
            'filteredEvent', 'ticketsByCategory', 'ticketsByPayType'
        );
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

        $ticket->update([
            'payment_status' => 'completed',
            'validated_by' => $user->id
        ]);

        return redirect()->route('admin.dashboard.view', ['tab' => 'relancer'])
            ->with('success', 'Ticket validé avec succès — ' . $ticket->full_name . ' (' . $ticket->reference . ')');
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
            'end_date' => 'nullable|date',
            'time' => 'nullable|string|max:50',
            'end_time' => 'nullable|string|max:50',
            'location' => 'required|string|max:255',
            'venue_details' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'organizer' => 'nullable|string|max:255',
            'registration_deadline' => 'nullable|date',
            'prices' => 'nullable|array',
            'prices.*.id' => 'nullable|exists:event_prices,id',
            'prices.*.category' => 'required|string',
            'prices.*.duration_type' => 'nullable|string|in:per_day,full_event',
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
            'end_date' => $request->end_date,
            'time' => $request->time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'venue_details' => $request->venue_details,
            'capacity' => $request->max_participants,
            'contact_phone' => $request->contact_phone,
            'contact_email' => $request->contact_email,
            'organizer' => $request->organizer,
            'registration_deadline' => $request->registration_deadline,
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
                            'duration_type' => $priceData['duration_type'] ?? 'full_event',
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
                        'duration_type' => $priceData['duration_type'] ?? 'full_event',
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
     * Afficher les détails d'un agent avec ses statistiques
     */
    public function agentDetails($id)
    {
        $user = session('admin_user');
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Récupérer l'agent avec ses rôles
        $agent = User::with('roles')->findOrFail($id);

        // Vérifier que ce n'est pas un Parent ou Administrateur
        $hasRestrictedRole = $agent->roles()->whereIn('name', ['Parent', 'Administrateur'])->exists();
        if ($hasRestrictedRole) {
            return redirect()->route('admin.dashboard.view', ['tab' => 'agents'])
                ->with('error', 'Impossible d\'afficher les détails de cet utilisateur.');
        }

        // Statistiques globales de l'agent
        $stats = [
            // Total de billets validés par cet agent
            'total_validations' => Ticket::where('validated_by', $agent->id)->count(),
            
            // Billets physiques validés
            'physical_validations' => Ticket::where('validated_by', $agent->id)
                ->whereNotNull('physical_qr_id')
                ->count(),
            
            // Billets en ligne validés
            'online_validations' => Ticket::where('validated_by', $agent->id)
                ->whereNull('physical_qr_id')
                ->count(),
            
            // Revenus générés (billets validés)
            'total_revenue' => Ticket::where('validated_by', $agent->id)
                ->where('payment_status', 'completed')
                ->sum('amount'),
            
            // Revenus billets physiques
            'physical_revenue' => Ticket::where('validated_by', $agent->id)
                ->whereNotNull('physical_qr_id')
                ->where('payment_status', 'completed')
                ->sum('amount'),
            
            // Revenus billets en ligne
            'online_revenue' => Ticket::where('validated_by', $agent->id)
                ->whereNull('physical_qr_id')
                ->where('payment_status', 'completed')
                ->sum('amount'),
        ];

        // Évolution des validations par jour (30 derniers jours)
        $validationsEvolution = Ticket::select(
            DB::raw('DATE(updated_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
            DB::raw('SUM(CASE WHEN physical_qr_id IS NULL THEN 1 ELSE 0 END) as online')
        )
            ->where('validated_by', $agent->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Validations par événement
        $validationsByEvent = Ticket::select(
            'events.id',
            'events.title',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
            DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online'),
            DB::raw('SUM(CASE WHEN tickets.payment_status = "completed" THEN tickets.amount ELSE 0 END) as revenue')
        )
            ->join('events', 'tickets.event_id', '=', 'events.id')
            ->where('tickets.validated_by', $agent->id)
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('total')
            ->get();

        // Dernières validations
        $recentValidations = Ticket::with(['event', 'price'])
            ->where('validated_by', $agent->id)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.agent-details', compact(
            'user',
            'agent',
            'stats',
            'validationsEvolution',
            'validationsByEvent',
            'recentValidations'
        ));
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

    /**
     * Page d'impression de la liste des évaluations
     */
    public function printEvaluationList(Request $request)
    {
        $user = session('admin_user');
        if (!$user) return redirect()->route('login');

        $query = DB::table('colloque_evaluations')->orderBy('created_at', 'desc');

        if ($request->eval_event_id) {
            $query->where('event_id', $request->eval_event_id);
        }

        $evaluations = $query->get();
        $event = $request->eval_event_id ? Event::find($request->eval_event_id) : null;

        $stats = [
            'total'   => $evaluations->count(),
            'noteAvg' => round($evaluations->whereNotNull('note_globale')->avg('note_globale'), 1),
        ];

        return view('admin.print.evaluations-list', compact('evaluations', 'stats', 'event'));
    }

    /**
     * Page d'impression de la liste des billets
     */
    public function printTicketList(Request $request)
    {
        $user = session('admin_user');
        if (!$user) return redirect()->route('login');

        $query = Ticket::with(['event', 'price']);

        $status = $request->get('tickets_status', 'completed');
        if ($status !== 'all') {
            $query->where('payment_status', $status);
        }

        if ($request->tickets_search) {
            $search = $request->tickets_search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->tickets_pay_type && $request->tickets_pay_type !== 'all') {
            $query->where('pay_type', $request->tickets_pay_type);
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();

        $stats = [
            'total'    => $tickets->count(),
            'revenue'  => $tickets->where('payment_status', 'completed')->sum('amount'),
            'currency' => $tickets->first()?->currency ?? 'USD',
        ];

        return view('admin.print.tickets-list', compact('tickets', 'stats', 'status'));
    }

    /**
     * Page d'impression d'un billet
     */
    public function printTicket(string $reference)
    {
        $user = session('admin_user');
        if (!$user) return redirect()->route('login');

        $ticket = Ticket::with(['event', 'price'])->where('reference', $reference)->firstOrFail();

        return view('admin.print.ticket', compact('ticket'));
    }

    /**
     * Renvoyer le billet par email (AJAX)
     */
    public function resendTicketMailAjax(string $reference): JsonResponse
    {
        $user = session('admin_user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $ticket = Ticket::with(['event', 'price'])->where('reference', $reference)->first();

        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Billet introuvable'], 404);
        }

        if ($ticket->payment_status !== 'completed') {
            return response()->json(['success' => false, 'message' => 'Seuls les billets validés peuvent être renvoyés'], 400);
        }

        if (!$ticket->email) {
            return response()->json(['success' => false, 'message' => 'Ce billet n\'a pas d\'adresse email'], 400);
        }

        try {
            \Illuminate\Support\Facades\Mail::to($ticket->email)
                ->send(new \App\Mail\TicketBoardingPassMail($ticket));

            return response()->json([
                'success' => true,
                'message' => "Billet envoyé à {$ticket->email}",
                'email' => $ticket->email,
                'name' => $ticket->full_name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Échec de l'envoi à {$ticket->email} : " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Renvoyer le billet par email
     */
    public function resendTicketMail(string $reference)
    {
        $user = session('admin_user');
        if (!$user) {
            return redirect()->route('login');
        }

        $ticket = Ticket::with(['event', 'price'])->where('reference', $reference)->firstOrFail();

        if ($ticket->payment_status !== 'completed') {
            return redirect()->back()->with('error', 'Seuls les billets validés peuvent être renvoyés.');
        }

        if (!$ticket->email) {
            return redirect()->back()->with('error', 'Ce billet n\'a pas d\'adresse email.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($ticket->email)
                ->send(new \App\Mail\TicketBoardingPassMail($ticket));

            return redirect()->back()->with('success', "✅ Billet renvoyé avec succès à {$ticket->email}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', "❌ Échec de l'envoi à {$ticket->email} : " . $e->getMessage());
        }
    }

    /**
     * Page dédiée export PDF du rapport
     */
    public function exportRapport(Request $request)
    {
        $user = session('admin_user');
        if (!$user) {
            return redirect()->route('login');
        }

        $dateFrom  = $request->get('report_date_from');
        $dateTo    = $request->get('report_date_to');
        $eventId   = $request->get('report_event_id');

        if (!$dateFrom || !$dateTo) {
            return redirect()->route('admin.dashboard.view', ['tab' => 'rapport'])
                ->with('error', 'Veuillez sélectionner une période.');
        }

        $reportData  = $this->buildReportData($dateFrom, $dateTo, $eventId);
        $events      = Event::orderBy('date', 'desc')->get();

        return view('admin.rapport-export', compact(
            'user', 'reportData', 'dateFrom', 'dateTo', 'eventId', 'events'
        ));
    }
    public function printUnpaidTickets(Request $request)
    {
        $user = session('admin_user');
        if (!$user) {
            return redirect()->route('login');
        }

        $query = Ticket::with(['event'])
            ->whereNull('physical_qr_id')
            ->where('payment_status', '!=', 'completed')
            ->where('payment_status', '!=', 'cancelled');

        if ($request->has('unpaid_search') && $request->unpaid_search) {
            $search = $request->unpaid_search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Pas de pagination pour l'impression — tout récupérer
        $unpaidTickets = $query->orderBy('created_at', 'desc')->paginate(1000);

        return view('admin.print.unpaid-tickets', compact('unpaidTickets'));
    }

    /**
     * Liste des billets non payés (API JSON)
     */
    public function unpaidTickets(Request $request): JsonResponse
    {
        $query = Ticket::with(['event'])
            ->whereNull('physical_qr_id') // Billets en ligne uniquement
            ->where('payment_status', '!=', 'completed') // Non payés
            ->where('payment_status', '!=', 'cancelled'); // Non annulés

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

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

        // Statistiques
        $stats = [
            'total_unpaid' => Ticket::whereNull('physical_qr_id')
                ->where('payment_status', '!=', 'completed')
                ->where('payment_status', '!=', 'cancelled')
                ->count(),
            'total_amount' => Ticket::whereNull('physical_qr_id')
                ->where('payment_status', '!=', 'completed')
                ->where('payment_status', '!=', 'cancelled')
                ->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }

    /**
     * Statistiques de l'agent connecté (API pour mobile)
     */
    public function myStats(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        // Statistiques globales de l'agent
        $stats = [
            'total_validations' => Ticket::where('validated_by', $user->id)->count(),
            'physical_validations' => Ticket::where('validated_by', $user->id)
                ->whereNotNull('physical_qr_id')
                ->count(),
            'online_validations' => Ticket::where('validated_by', $user->id)
                ->whereNull('physical_qr_id')
                ->count(),
            'total_revenue' => Ticket::where('validated_by', $user->id)
                ->where('payment_status', 'completed')
                ->sum('amount'),
            'physical_revenue' => Ticket::where('validated_by', $user->id)
                ->whereNotNull('physical_qr_id')
                ->where('payment_status', 'completed')
                ->sum('amount'),
            'online_revenue' => Ticket::where('validated_by', $user->id)
                ->whereNull('physical_qr_id')
                ->where('payment_status', 'completed')
                ->sum('amount'),
        ];

        // Évolution des validations par jour (30 derniers jours)
        $validationsEvolution = Ticket::select(
            DB::raw('DATE(updated_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
            DB::raw('SUM(CASE WHEN physical_qr_id IS NULL THEN 1 ELSE 0 END) as online')
        )
            ->where('validated_by', $user->id)
            ->where('updated_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Validations par événement
        $validationsByEvent = Ticket::select(
            'events.id',
            'events.title',
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NOT NULL THEN 1 ELSE 0 END) as physical'),
            DB::raw('SUM(CASE WHEN tickets.physical_qr_id IS NULL THEN 1 ELSE 0 END) as online'),
            DB::raw('SUM(CASE WHEN tickets.payment_status = "completed" THEN tickets.amount ELSE 0 END) as revenue')
        )
            ->join('events', 'tickets.event_id', '=', 'events.id')
            ->where('tickets.validated_by', $user->id)
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('total')
            ->get();

        // Dernières validations
        $recentValidations = Ticket::with(['event', 'price'])
            ->where('validated_by', $user->id)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($ticket) {
                return [
                    'reference' => $ticket->reference,
                    'ticket_type' => $ticket->physical_qr_id ? 'physical' : 'online',
                    'full_name' => $ticket->full_name,
                    'event_title' => $ticket->event->title,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                    'validated_at' => $ticket->updated_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'agent' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'stats' => $stats,
            'validations_evolution' => $validationsEvolution,
            'validations_by_event' => $validationsByEvent,
            'recent_validations' => $recentValidations,
        ]);
    }
}

