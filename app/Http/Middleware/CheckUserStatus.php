<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next)
{
    if (auth()->check()) {
        if (auth()->user()->is_suspended) {
            return response()->json([
                'message' => 'Your account is suspended'
            ], 403);
        }
    }

    return $next($request);
}
}
