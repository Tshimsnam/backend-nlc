<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConsortiumVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user('consortium')?->email_verified_at) {
            return response()->json(['message' => 'Confirmez votre adresse e-mail pour accéder à votre dossier.', 'verification_required' => true], 403);
        }

        return $next($request);
    }
}
