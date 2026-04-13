<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColloqueEvaluationController extends Controller
{
    /**
     * Soumettre une évaluation de colloque
     * POST /api/colloque/evaluate
     * POST /api/evenements/{slug}/evaluation/submit
     */
    public function store(Request $request, ?string $slug = null)
    {
        if ($slug) $request->merge(['event_slug' => $slug]);
        $data = $request->validate([
            // Section 1
            'full_name'                => 'nullable|string|max:255',
            'etablissement'            => 'nullable|string|max:255',
            'profil'                   => 'nullable|string|max:100',
            'profil_autre'             => 'nullable|string|max:255',
            'contact'                  => 'nullable|string|max:100',
            'date_colloque'            => 'nullable|date',
            'duree_session'            => 'nullable|string|max:100',
            // Section 2
            'adequation_theme'         => 'nullable|in:tres_adequat,adequat,neutre,pas_vraiment,pas_du_tout',
            'aspects_pertinents'       => 'nullable|string',
            'sujets_manquants'         => 'nullable|string',
            // Section 3
            'clarte_presentations'     => 'nullable|in:excellente,tres_bonne,bonne,acceptable,insatisfaisante',
            'maintien_attention'       => 'nullable|in:toujours,souvent,parfois,jamais',
            // Section 4
            'organisation_generale'    => 'nullable|in:excellente,tres_bonne,bonne,acceptable,a_ameliorer',
            'respect_horaires'         => 'nullable|in:toujours,la_plupart,parfois,rarement,jamais',
            'logistique_commentaire'   => 'nullable|string',
            // Section 5
            'opportunites_interaction' => 'nullable|in:beaucoup,quelques,peu,aucune',
            'contacts_professionnels'  => 'nullable|string',
            // Section 6
            'enseignements_tires'      => 'nullable|string',
            'application_enseignements'=> 'nullable|string',
            // Section 7
            'note_globale'             => 'nullable|integer|min:1|max:10',
            'points_forts'             => 'nullable|string',
            'suggestions_amelioration' => 'nullable|string',
            // Section 8
            'commentaires_additionnels'=> 'nullable|string',
            // Section 9 — dynamique : tsa_q1 … tsa_qN
            'tsa_answers'              => 'nullable|array',
            'tsa_answers.*'            => 'nullable|in:A,B,C,D,E,F',
            'event_id'                 => 'nullable|exists:events,id',
        ]);

        // Mapper tsa_answers[0..N] → tsa_q1..tsa_q5 (colonnes fixes en DB)
        $tsaAnswers = $request->input('tsa_answers', []);
        foreach ($tsaAnswers as $i => $answer) {
            $col = 'tsa_q' . ($i + 1);
            if (in_array($col, ['tsa_q1','tsa_q2','tsa_q3','tsa_q4','tsa_q5'])) {
                $data[$col] = $answer;
            }
        }
        unset($data['tsa_answers']);

        // Résoudre event_id depuis le slug si fourni
        if (empty($data['event_id']) && $request->event_slug) {
            $data['event_id'] = \App\Models\Event::where('slug', $request->event_slug)->value('id');
        }

        $data['ip_hash'] = hash('sha256', $request->ip());

        DB::table('colloque_evaluations')->insert(array_merge($data, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Merci pour votre évaluation ! Vos réponses ont été enregistrées.',
        ]);
    }

    /**
     * Retourner les questions TSA dynamiques pour le frontend
     * GET /api/colloque/questions?event_id=1
     * GET /api/evenements/{slug}/evaluation/questions
     */
    public function questions(Request $request, ?string $slug = null)
    {
        if ($slug) $request->merge(['event_slug' => $slug]);
        $query = \App\Models\EvaluationQuestion::where('section', 'tsa')
            ->where('is_active', true)
            ->orderBy('order');

        if ($request->event_id) {
            $query->where('event_id', $request->event_id);
        } elseif ($request->event_slug) {
            $eventId = \App\Models\Event::where('slug', $request->event_slug)->value('id');
            if ($eventId) $query->where('event_id', $eventId);
        }

        return response()->json(['questions' => $query->get(['id', 'text', 'options'])]);
    }

    /**
     * Statistiques pour l'admin
     * GET /api/colloque/stats
     */
    public function stats()
    {
        $total = DB::table('colloque_evaluations')->count();

        $noteAvg = DB::table('colloque_evaluations')
            ->whereNotNull('note_globale')
            ->avg('note_globale');

        $byProfil = DB::table('colloque_evaluations')
            ->select('profil', DB::raw('COUNT(*) as count'))
            ->groupBy('profil')->get();

        $byAdequation = DB::table('colloque_evaluations')
            ->select('adequation_theme', DB::raw('COUNT(*) as count'))
            ->groupBy('adequation_theme')->get();

        $tsaStats = [];
        foreach (['tsa_q1','tsa_q2','tsa_q3','tsa_q4','tsa_q5'] as $q) {
            $tsaStats[$q] = DB::table('colloque_evaluations')
                ->select($q . ' as answer', DB::raw('COUNT(*) as count'))
                ->whereNotNull($q)
                ->groupBy($q)->get()->keyBy('answer')->map(fn($r) => $r->count);
        }

        return response()->json([
            'total'          => $total,
            'note_moyenne'   => round($noteAvg, 1),
            'by_profil'      => $byProfil,
            'by_adequation'  => $byAdequation,
            'tsa_stats'      => $tsaStats,
        ]);
    }
}
