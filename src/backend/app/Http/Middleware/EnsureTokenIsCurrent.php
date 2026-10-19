<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsCurrent
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('api');

        if ((int) Auth::guard('api')->payload()->get('ver', 0) !== $user->token_version) {
            return response()->json(['message' => 'Your session has ended. Please log in again.'], 401);
        }

        return $next($request);
    }
}
