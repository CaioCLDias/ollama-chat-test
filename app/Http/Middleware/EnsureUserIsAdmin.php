<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->is_admin) {
            return response()->json([
                'status' => false,
                'statusCode' => 403,
                'message' => 'Unauthorized',
                'data' => null,
            ], 403);
        }

        return $next($request);
    }
}
