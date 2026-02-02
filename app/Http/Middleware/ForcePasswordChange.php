<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            // Redirect to password change page unless already there
            if (!$request->is('password/change') && !$request->is('logout')) {
                return redirect()->route('password.change.form');
            }
        }

        return $next($request);
    }
}

