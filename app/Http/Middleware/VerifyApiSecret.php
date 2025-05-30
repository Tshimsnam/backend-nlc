<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiSecret
{
    public function handle(Request $request, Closure $next)
    {
        $secret = $request->header('API-SECRET');
        if ($secret !== env('BACKEND_NLC_API_SECRET')) {
            return response()->json(['message' => 'Clé API invalide.'], 403);
        }

        return $next($request);
    }
}
