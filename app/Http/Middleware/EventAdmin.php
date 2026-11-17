<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EventAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $id = data_get(session('admin_user'), 'id');
        if (!$id) {
            return redirect()->guest(route('login'));
        }
        $admin = User::find($id);
        abort_unless($admin && ($admin->is_active === null || (bool) $admin->is_active)
            && ($admin->role === 'admin' || $admin->hasRole('admin') || $admin->hasRole('Administrateur')), 403);

        return $next($request);
    }
}
