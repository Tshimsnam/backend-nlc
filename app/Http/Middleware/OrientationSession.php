<?php

namespace App\Http\Middleware;

use App\Services\Orientation\Access;
use Closure;
use Illuminate\Http\Request;

class OrientationSession
{
    public function handle(Request $request, Closure $next)
    {
        Access::actor($request);
        $response = $next($request);

        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
