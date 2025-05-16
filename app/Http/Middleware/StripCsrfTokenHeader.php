<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripCsrfTokenHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if ($request->headers->has('X-CSRF-TOKEN')) {
            $request->headers->remove('X-CSRF-TOKEN');
        }

        return $next($request);
    }
}
