<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterParticipantRequest;
use App\Services\Participants\ParticipantService;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private ParticipantService $participantService
    ) {}

    /**
     * Inscription d'un participant (Participant → Payment → Ticket).
     */
    public function store(RegisterParticipantRequest $request): JsonResponse
    {
        try {
            $participant = $this->participantService->register($request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Inscription enregistrée. Procédez au paiement.',
            'data' => [
                'participant' => $participant,
                'payment_id' => $participant->payment->id,
                'amount' => $participant->payment->amount,
                'currency' => $participant->payment->currency,
            ],
        ], 201);
    }
}
