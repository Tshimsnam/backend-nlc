<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventScan;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class EventScanController extends Controller
{
    /**
     * Enregistrer un scan de QR code
     */
    public function recordScan(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        // Déterminer le type d'appareil
        $deviceType = 'desktop';
        if ($agent->isMobile()) {
            $deviceType = 'mobile';
        } elseif ($agent->isTablet()) {
            $deviceType = 'tablet';
        }

        // Enregistrer le scan
        EventScan::create([
            'event_id' => $event->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_type' => $deviceType,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scan enregistré avec succès',
            'event' => [
                'title' => $event->title,
                'slug' => $event->slug,
            ],
        ]);
    }

    /**
     * Obtenir les statistiques de scans pour un événement
     */
    public function getEventScans($slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $totalScans = EventScan::where('event_id', $event->id)->count();
        
        $scansByDevice = EventScan::where('event_id', $event->id)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        $recentScans = EventScan::where('event_id', $event->id)
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'event' => $event,
            'total_scans' => $totalScans,
            'scans_by_device' => $scansByDevice,
            'recent_scans' => $recentScans,
        ]);
    }
}
