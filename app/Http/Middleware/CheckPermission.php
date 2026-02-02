<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        // Super‐admins and admins skip all checks
        if (
            $user &&
            (
                $user->hasRole('super_admin') ||
                $user->hasRole('admin')
            )
        ) {
            return $next($request);
        }

        // Everybody else needs the explicit permission
        if (! $user || ! $user->hasPermission($permission)) {
            return response()->json([
                'error' => "Forbidden — missing permission: {$permission}"
            ], 403);
        }

        return $next($request);
    }
}
