<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Afficher un ticket par numéro (pour vérification / téléchargement).
     */
    public function show(Request $request, string $ticketNumber): JsonResponse
    {
        $ticket = Ticket::with(['participant', 'event', 'payment'])
            ->where('ticket_number', $ticketNumber)
            ->firstOrFail();

        return response()->json($ticket);
    }
}
