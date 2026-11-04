<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ConsultationScheduledMail;
use App\Models\ConsultationRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Espace secrétariat : traitement des demandes de consultation à distance.
 * Les heures sont saisies et affichées en heure de Kinshasa, stockées en UTC.
 */
class ConsultationAdminController extends Controller
{
    private const KINSHASA = 'Africa/Kinshasa';

    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('search'));

        $query = ConsultationRequest::query()->latest();

        if ($status && in_array($status, ConsultationRequest::STATUSES, true)) {
            $query->where('status', $status);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                foreach (['reference', 'parent_nom', 'parent_prenom', 'enfant_nom', 'enfant_prenom', 'email', 'whatsapp', 'pays'] as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $counts = ConsultationRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.consultations.index', [
            'user'          => session('admin_user'),
            'consultations' => $query->paginate(20)->withQueryString(),
            'counts'        => $counts,
            'status'        => $status,
            'search'        => $search,
        ]);
    }

    public function show(ConsultationRequest $consultation)
    {
        return view('admin.consultations.show', [
            'user' => session('admin_user'),
            'c'    => $consultation,
        ]);
    }

    public function update(Request $request, ConsultationRequest $consultation)
    {
        $notify = $request->input('action') === 'notify';

        $data = $request->validate([
            'status'                => 'required|in:' . implode(',', ConsultationRequest::STATUSES),
            'professionnel_assigne' => 'nullable|string|max:255',
            'seance_programmee_at'  => ($notify ? 'required' : 'nullable') . '|date',
            'duree_minutes'         => 'nullable|integer|min:10|max:240',
            'lien_visio'            => ($notify ? 'required' : 'nullable') . '|url:http,https|max:500',
            'prix'                  => 'nullable|numeric|min:0|max:100000',
            'devise'                => 'required|in:USD,CDF,EUR',
            'statut_paiement'       => 'nullable|in:' . implode(',', array_keys(ConsultationRequest::PAYMENT_STATUSES)),
            'notes_internes'        => 'nullable|string|max:10000',
        ], [
            'seance_programmee_at.required' => 'Indiquez la date et l\'heure de la séance avant d\'envoyer la confirmation.',
            'lien_visio.required'           => 'Collez le lien Zoom ou Meet avant d\'envoyer la confirmation.',
            'lien_visio.url'                => 'Le lien de visioconférence doit commencer par https://',
        ]);

        // Le formulaire envoie l'heure de Kinshasa (datetime-local) : on la convertit en UTC
        if (!empty($data['seance_programmee_at'])) {
            $data['seance_programmee_at'] = Carbon::parse($data['seance_programmee_at'], self::KINSHASA)->utc();
        }

        if (empty($data['statut_paiement'])) {
            $data['statut_paiement'] = ($data['prix'] ?? 0) > 0 ? 'en_attente' : 'gratuit';
        }

        if ($notify) {
            $data['status'] = 'rdv_programme';
        }

        $consultation->update($data);

        if (!$notify) {
            return back()->with('success', 'Dossier enregistré.');
        }

        try {
            Mail::to($consultation->email)->send(new ConsultationScheduledMail($consultation));
        } catch (\Throwable $e) {
            Log::error('Consultation: confirmation RDV non envoyée', ['reference' => $consultation->reference, 'error' => $e->getMessage()]);

            return back()->with('error', "Dossier enregistré, mais l'e-mail n'a pas pu être envoyé. Prévenez le parent par WhatsApp.");
        }

        $consultation->update(['confirmation_envoyee_at' => now()]);

        return back()->with('success', 'Rendez-vous confirmé : l\'e-mail a été envoyé au parent. Pensez à le prévenir aussi par WhatsApp.');
    }

    /** Téléchargement d'un document du dossier (disque privé, jamais exposé publiquement). */
    public function document(ConsultationRequest $consultation, int $index)
    {
        $document = $consultation->documents[$index] ?? null;

        abort_unless($document && Storage::disk('local')->exists($document['path']), 404);

        return Storage::disk('local')->download($document['path'], $document['name']);
    }
}
