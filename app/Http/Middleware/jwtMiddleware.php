<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class jwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request has a token
        if (!$request->hasHeader('Authorization')) {
            return response()->json(['error' => 'Token not provided'], 401);
        }
        // Extract the token from the Authorization header        
        return $next($request);
    }
}
