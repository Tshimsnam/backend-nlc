<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrangeMoneyWebhookController extends Controller
{
    /**
     * Traite les notifications Orange Money.
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('Orange Money webhook received', ['payload' => $request->all()]);

        $status = $request->input('status');
        $transactionId = $request->input('notif_token') ?? $request->input('pay_token');
        $orderId = $request->input('order_id');

        if (!$transactionId && !$orderId) {
            Log::error('Orange Money webhook: missing transaction/order ID');
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        // Chercher le ticket par gateway_log_id ou référence
        $ticket = Ticket::where('gateway_log_id', $transactionId)
            ->orWhere('reference', $orderId)
            ->first();

        if (!$ticket) {
            Log::warning('Orange Money webhook: ticket not found', [
                'transaction_id' => $transactionId,
                'order_id' => $orderId,
            ]);
            return response()->json(['status' => 'error', 'message' => 'Ticket not found'], 404);
        }

        // Statuts Orange Money: SUCCESS, FAILED, PENDING, EXPIRED
        if (strtoupper($status) === 'SUCCESS') {
            $ticket->update(['payment_status' => 'completed']);
            
            // Incrémenter le compteur registered dans l'événement
            if ($ticket->event) {
                $ticket->event->increment('registered');
            }
            
            Log::info('Orange Money payment completed', ['reference' => $ticket->reference]);
        } elseif (in_array(strtoupper($status), ['FAILED', 'EXPIRED'], true)) {
            $ticket->update(['payment_status' => 'failed']);
            Log::warning('Orange Money payment failed', [
                'reference' => $ticket->reference,
                'status' => $status,
            ]);
        }

        return response()->json(['status' => 'success', 'message' => 'Notification processed']);
    }
}
