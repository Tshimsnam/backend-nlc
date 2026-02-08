<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyMaxiCashSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.maxicash.webhook_secret');

        // En sandbox sans secret, accepter la requête (MaxiCash peut notifier sans signature)
        if (empty($secret)) {
            return $next($request);
        }

        $signature = $request->header('X-Maxicash-Signature')
            ?? $request->header('X-Webhook-Signature')
            ?? $request->input('signature');

        if (empty($signature)) {
            return response()->json(['message' => 'Missing signature'], 401);
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            Log::warning('MaxiCash webhook signature mismatch');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        return $next($request);
    }
}
