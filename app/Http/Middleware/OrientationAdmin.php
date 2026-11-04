<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class OrientationAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $id = data_get(session('admin_user'), 'id');
        if (! $id) {
            return redirect()->guest(route('login'));
        }
        $admin = User::find($id);
        abort_unless($admin && ($admin->is_active === null || (bool) $admin->is_active) && ($admin->role === 'admin' || $admin->roles()->whereIn('name', ['admin', 'Administrateur'])->exists()), 403);
        $request->attributes->set('orientation_admin', $admin);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
