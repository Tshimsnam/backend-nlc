<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    /**
     * Traite les callbacks M-Pesa (STK Push).
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('M-Pesa webhook received', ['payload' => $request->all()]);

        $body = $request->input('Body.stkCallback', []);
        $resultCode = $body['ResultCode'] ?? null;
        $checkoutRequestId = $body['CheckoutRequestID'] ?? null;

        if (!$checkoutRequestId) {
            Log::error('M-Pesa webhook: missing CheckoutRequestID');
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid payload'], 400);
        }

        $ticket = Ticket::where('gateway_log_id', $checkoutRequestId)->first();

        if (!$ticket) {
            Log::warning('M-Pesa webhook: ticket not found', ['checkout_request_id' => $checkoutRequestId]);
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Ticket not found'], 404);
        }

        // ResultCode 0 = succès
        if ($resultCode === 0 || $resultCode === '0') {
            $ticket->update(['payment_status' => 'completed']);
            
            // Incrémenter le compteur registered dans l'événement
            if ($ticket->event) {
                $ticket->event->increment('registered');
            }
            
            Log::info('M-Pesa payment completed', ['reference' => $ticket->reference]);
        } else {
            $ticket->update(['payment_status' => 'failed']);
            Log::warning('M-Pesa payment failed', [
                'reference' => $ticket->reference,
                'result_code' => $resultCode,
                'result_desc' => $body['ResultDesc'] ?? 'Unknown error',
            ]);
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
