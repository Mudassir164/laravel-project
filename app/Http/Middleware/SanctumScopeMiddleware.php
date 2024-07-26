<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanctumScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        foreach ($scopes as $ability) {
            $token_scope = $request->user()->currentAccessToken()->abilities;
            if (in_array($ability,$token_scope)) {
                return $next($request);
            }
        }
        return response(['message' => 'Forbidden'],403);
    }
}
