<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège les pages Blade de l'admin : même règle que DashboardController::view
 * (l'administrateur connecté via /login est stocké en session).
 */
class AdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('admin_user')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
