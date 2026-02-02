<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MustChangePassword
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            return redirect()->route('staff.password.change'); // redirect to change password
        }
        return $next($request);
    }
}
