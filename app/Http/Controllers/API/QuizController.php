<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\QuizResponse;
use App\Models\QuizQuestion;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    /**
     * Retourner les questions du quiz pour un événement
     * GET /api/quiz/questions?event_id=1  ou  ?event_slug=grand-salon-autisme
     */
    public function questions(Request $request)
    {
        $eventId = $this->resolveEventId($request);

        $query = QuizQuestion::where('is_active', true)->orderBy('order');
        if ($eventId) {
            $query->where('event_id', $eventId);
        } else {
            $query->where('quiz_slug', $request->get('quiz_slug', 'gsa-2026'));
        }

        return response()->json(['questions' => $query->get(['id','text','correct_answer'])]);
    }

    /**
     * Soumettre les réponses du quiz (anonyme)
     * POST /api/quiz/submit
     * Body: { event_id: 1, quiz_slug: "gsa-2026", answers: { "1": "vrai", ... } }
     */
    public function submit(Request $request)
    {
        $request->validate([
            'quiz_slug'   => 'nullable|string|max:50',
            'event_id'    => 'nullable|exists:events,id',
            'answers'     => 'required|array|min:1',
            'answers.*'   => 'required|in:vrai,faux,peut_etre',
        ]);

        $token   = $request->header('X-Quiz-Token') ?? Str::random(40);
        $ipHash  = hash('sha256', $request->ip());
        $slug    = $request->quiz_slug ?? 'gsa-2026';
        $eventId = $request->event_id;

        // Anti-doublon par token + événement
        $alreadySubmitted = QuizResponse::where('session_token', $token)
            ->where('quiz_slug', $slug)
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['success' => false, 'message' => 'Vous avez déjà répondu à ce quiz.'], 409);
        }

        $rows = [];
        foreach ($request->answers as $questionId => $answer) {
            $rows[] = [
                'session_token' => $token,
                'quiz_slug'     => $slug,
                'event_id'      => $eventId,
                'question_id'   => (int) $questionId,
                'answer'        => $answer,
                'ip_hash'       => $ipHash,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        QuizResponse::insert($rows);

        return response()->json([
            'success' => true,
            'message' => 'Réponses enregistrées. Merci !',
            'token'   => $token,
        ]);
    }

    /**
     * Statistiques par question
     * GET /api/quiz/stats?event_id=1
     */
    public function stats(Request $request)
    {
        $eventId = $this->resolveEventId($request);
        $slug    = $request->get('quiz_slug', 'gsa-2026');

        $query = QuizResponse::query()
            ->when($eventId, fn($q) => $q->where('event_id', $eventId))
            ->when(!$eventId, fn($q) => $q->where('quiz_slug', $slug));

        $total = (clone $query)->distinct('session_token')->count('session_token');

        $byQuestion = (clone $query)
            ->select('question_id', 'answer', DB::raw('COUNT(*) as count'))
            ->groupBy('question_id', 'answer')
            ->orderBy('question_id')
            ->get()
            ->groupBy('question_id')
            ->map(fn($rows) => $rows->keyBy('answer')->map(fn($r) => $r->count));

        return response()->json([
            'event_id'        => $eventId,
            'total_responses' => $total,
            'by_question'     => $byQuestion,
        ]);
    }

    private function resolveEventId(Request $request): ?int
    {
        if ($request->event_id) return (int) $request->event_id;
        if ($request->event_slug) {
            return Event::where('slug', $request->event_slug)->value('id');
        }
        return null;
    }
}
