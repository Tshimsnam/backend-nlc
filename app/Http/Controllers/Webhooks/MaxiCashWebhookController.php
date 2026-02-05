<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMaxiCashWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaxiCashWebhookController extends Controller
{
    /**
     * Reçoit les notifications de paiement MaxiCash et dispatch le job.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload)) {
            $payload = $request->json()->all();
        }

        ProcessMaxiCashWebhook::dispatch($payload);

        return response()->json(['message' => 'Webhook reçu'], 200);
    }
}
