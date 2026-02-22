<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QRScanController extends Controller
{
    /**
     * Scanner un QR code de billet
     * 
     * Cette méthode permet de scanner un billet via:
     * - Le QR code (contient toutes les infos en JSON)
     * - Le numéro de référence
     * - Le numéro de téléphone
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function scan(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'qr_data' => 'nullable|string',
                'reference' => 'nullable|string',
                'phone' => 'nullable|string',
                'scan_location' => 'nullable|string',
            ]);

            $ticket = null;

            // Méthode 1: Scan via QR code (données JSON)
            if ($request->filled('qr_data')) {
                $qrData = json_decode($request->qr_data, true);
                
                if (!$qrData || !isset($qrData['reference'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'QR code invalide',
                    ], 400);
                }

                $ticket = Ticket::where('reference', $qrData['reference'])->first();
            }
            // Méthode 2: Recherche par référence
            elseif ($request->filled('reference')) {
                $ticket = Ticket::where('reference', $request->reference)->first();
            }
            // Méthode 3: Recherche par téléphone
            elseif ($request->filled('phone')) {
                $phone = $request->phone;
                
                // Normaliser le numéro de téléphone pour la recherche
                $cleanPhone = preg_replace('/[\s\-\(\)]+/', '', $phone);
                
                // Créer des variantes du numéro pour la recherche
                $phoneVariants = [$phone, $cleanPhone];
                
                // Si commence par +243, ajouter la variante avec 0
                if (str_starts_with($cleanPhone, '+243')) {
                    $localPhone = '0' . substr($cleanPhone, 4);
                    $phoneVariants[] = $localPhone;
                }
                // Si commence par 243 (sans +), ajouter la variante avec 0
                elseif (str_starts_with($cleanPhone, '243')) {
                    $localPhone = '0' . substr($cleanPhone, 3);
                    $phoneVariants[] = $localPhone;
                }
                // Si commence par 0, ajouter la variante avec +243
                elseif (str_starts_with($cleanPhone, '0')) {
                    $internationalPhone = '+243' . substr($cleanPhone, 1);
                    $phoneVariants[] = $internationalPhone;
                }
                
                // Rechercher avec l'une des variantes
                $ticket = Ticket::where(function($query) use ($phoneVariants) {
                    foreach ($phoneVariants as $variant) {
                        $query->orWhere('phone', 'LIKE', '%' . $variant . '%');
                    }
                })->first();
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez fournir un QR code, une référence ou un numéro de téléphone',
                ], 400);
            }

            // Vérifier si le ticket existe
            if (!$ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Billet introuvable',
                ], 404);
            }

            // Charger les relations
            $ticket->load(['event', 'price']);

            // Enregistrer le scan dans la base de données
            DB::transaction(function () use ($ticket, $request) {
                // Créer un enregistrement de scan
                TicketScan::create([
                    'ticket_id' => $ticket->id,
                    'event_id' => $ticket->event_id,
                    'scanned_by' => auth()->id(), // ID de l'utilisateur connecté (agent)
                    'scan_location' => $request->scan_location ?? 'Entrée',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'scanned_at' => now(),
                ]);

                // Mettre à jour le compteur de scans du ticket
                $ticket->increment('scan_count');

                // Mettre à jour les timestamps de scan
                if ($ticket->scan_count === 1) {
                    $ticket->first_scanned_at = now();
                }
                $ticket->last_scanned_at = now();
                $ticket->save();
            });

            // Recharger le ticket pour avoir les données à jour
            $ticket->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Billet scanné avec succès',
                'ticket' => [
                    'id' => $ticket->id,
                    'reference' => $ticket->reference,
                    'full_name' => $ticket->full_name,
                    'email' => $ticket->email,
                    'phone' => $ticket->phone,
                    'category' => $ticket->category,
                    'amount' => $ticket->amount,
                    'currency' => $ticket->currency,
                    'payment_status' => $ticket->payment_status,
                    'scan_count' => $ticket->scan_count,
                    'first_scanned_at' => $ticket->first_scanned_at,
                    'last_scanned_at' => $ticket->last_scanned_at,
                    'event' => [
                        'id' => $ticket->event->id,
                        'title' => $ticket->event->title,
                        'date' => $ticket->event->date,
                        'time' => $ticket->event->time,
                        'location' => $ticket->event->location,
                    ],
                    'price' => $ticket->price ? [
                        'label' => $ticket->price->label,
                        'category' => $ticket->price->category,
                        'duration_type' => $ticket->price->duration_type,
                    ] : null,
                ],
                'scan_info' => [
                    'scan_count' => $ticket->scan_count,
                    'is_first_scan' => $ticket->scan_count === 1,
                    'last_scanned_at' => $ticket->last_scanned_at,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du scan QR: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan du billet',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtenir l'historique des scans d'un billet
     * 
     * @param string $reference
     * @return JsonResponse
     */
    public function getScanHistory(string $reference): JsonResponse
    {
        $ticket = Ticket::where('reference', $reference)->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Billet introuvable',
            ], 404);
        }

        $scans = TicketScan::where('ticket_id', $ticket->id)
            ->with('scannedBy:id,name,email')
            ->orderBy('scanned_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'ticket_reference' => $ticket->reference,
            'total_scans' => $ticket->scan_count,
            'scans' => $scans,
        ]);
    }

    /**
     * Obtenir les statistiques de scan pour un événement
     * 
     * @param int $eventId
     * @return JsonResponse
     */
    public function getEventScanStats(int $eventId): JsonResponse
    {
        $event = \App\Models\Event::find($eventId);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Événement introuvable.',
                'event_id' => $eventId,
            ], 404);
        }

        $stats = [
            'event_id' => $event->id,
            'event_title' => $event->title,
            'total_tickets' => Ticket::where('event_id', $eventId)->count(),
            'total_scans' => TicketScan::where('event_id', $eventId)->count(),
            'unique_tickets_scanned' => Ticket::where('event_id', $eventId)
                ->where('scan_count', '>', 0)
                ->count(),
            'tickets_not_scanned' => Ticket::where('event_id', $eventId)
                ->where('scan_count', 0)
                ->count(),
        ];

        // Scans par jour
        $scansByDay = TicketScan::where('event_id', $eventId)
            ->select(
                DB::raw('DATE(scanned_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Scans par lieu
        $scansByLocation = TicketScan::where('event_id', $eventId)
            ->select('scan_location', DB::raw('COUNT(*) as count'))
            ->groupBy('scan_location')
            ->orderByDesc('count')
            ->get();

        // Scans récents
        $recentScans = TicketScan::where('event_id', $eventId)
            ->with(['ticket:id,reference,full_name', 'scannedBy:id,name'])
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'scans_by_day' => $scansByDay,
            'scans_by_location' => $scansByLocation,
            'recent_scans' => $recentScans,
        ]);
    }
}
