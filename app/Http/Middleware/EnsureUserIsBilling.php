<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsBilling
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
{
    if (!$request->user() || $request->user()->role !== 'billing') {
        return response()->json(['message' => 'Unauthorized. Billing team only.'], 403);
    }

    return $next($request);
}

}
