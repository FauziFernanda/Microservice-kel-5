<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AttachAuthToken
{
    /**
     * Attach token from session to Authorization header when missing.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->header('Authorization') && session()->has('access_token')) {
            $request->headers->set('Authorization', 'Bearer ' . session('access_token'));
        }

        return $next($request);
    }
}
