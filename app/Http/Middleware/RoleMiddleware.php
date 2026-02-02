<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('staff.login.form');
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            abort(403, 'Unauthorized: Your role is not permitted to access this route.');
        }

        return $next($request);
    }
}
