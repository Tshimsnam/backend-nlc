<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMaxiCashWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaxiCashWebhookController extends Controller
{
    /**
     * Reçoit les notifications de paiement MaxiCash (POST body ou GET query string).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload) && $request->isMethod('post')) {
            $payload = $request->json()->all();
        }

        // Fusion query + body au cas où MaxiCash envoie des paramètres en query string
        if (! empty($request->query())) {
            $payload = array_merge($request->query(), $payload);
        }

        ProcessMaxiCashWebhook::dispatch($payload);

        return response()->json(['message' => 'Webhook reçu'], 200);
    }
}
