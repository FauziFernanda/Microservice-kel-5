<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $header = 'X-Correlation-ID';
        $corr = $request->header($header) ?: (string) Str::uuid();
        $request->headers->set($header, $corr);

        Log::withContext(['correlation_id' => $corr, 'service' => config('app.name', 'news-service')]);

        app()->instance('correlation_id', $corr);

        $response = $next($request);
        if (method_exists($response, 'header')) {
            $response->header($header, $corr);
        }

        return $response;
    }
}
