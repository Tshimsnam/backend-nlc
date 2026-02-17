<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\InitiatePaymentRequest;
use App\Models\Participant;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Initie un paiement pour un participant selon le gateway choisi.
     */
    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        $participant = Participant::with('payment', 'event')->findOrFail($request->participant_id);

        if (! $participant->payment) {
            return response()->json(['message' => 'Aucun paiement associé à ce participant.'], 422);
        }

        $gateway = $request->gateway ?? $participant->payment->gateway ?? 'maxicash';

        // Paiement en caisse : pas de traitement en ligne
        if (!PaymentGatewayFactory::requiresOnlinePayment($gateway)) {
            return response()->json([
                'message' => 'Paiement en caisse enregistré',
                'gateway' => $gateway,
                'payment_id' => $participant->payment->id,
            ]);
        }

        // Créer le service de paiement approprié
        try {
            $paymentService = PaymentGatewayFactory::create($gateway);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Initier le paiement
        $result = $paymentService->initiatePayment(
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
            'message' => $result['message'] ?? 'Paiement initié',
            'redirect_url' => $result['redirect_url'],
            'payment_id' => $result['payment_id'] ?? $participant->payment->id,
            'gateway' => $gateway,
        ]);
    }

    /**
     * Liste les gateways de paiement disponibles.
     */
    public function gateways(): JsonResponse
    {
        return response()->json([
            'gateways' => PaymentGatewayFactory::availableGateways(),
        ]);
    }
}
