<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Models\Participant;
use App\Services\Payments\MaxiCashService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private MaxiCashService $maxiCashService
    ) {}

    /**
     * Initie un paiement MaxiCash pour un participant.
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $participant = Participant::with('payment', 'event')->findOrFail($request->participant_id);

        if (! $participant->payment) {
            return response()->json(['message' => 'Aucun paiement associé à ce participant.'], 422);
        }

        $result = $this->maxiCashService->initiatePayment(
            $participant->payment,
            $request->return_url,
            $request->cancel_url
        );

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'message' => $result['message'] ?? 'Impossible d\'initier le paiement.',
            ], 422);
        }

        return response()->json([
            'message' => 'Paiement initié',
            'redirect_url' => $result['redirect_url'],
            'payment_id' => $result['payment_id'],
        ]);
    }
}
