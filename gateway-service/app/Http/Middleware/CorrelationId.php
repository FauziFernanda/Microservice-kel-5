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
        // Use header X-Correlation-ID if present, otherwise generate one
        $header = 'X-Correlation-ID';
        $corr = $request->header($header) ?: (string) Str::uuid();

        // Ensure request has the header so downstream code can read it
        $request->headers->set($header, $corr);

        // Add to log context for this request
        Log::withContext([
            'correlation_id' => $corr,
            'service' => config('app.name', 'gateway-service'),
        ]);

        // Make available globally via container if needed
        app()->instance('correlation_id', $corr);

        // Continue
        $response = $next($request);

        // Echo header back to the client so they can follow the value
        if (method_exists($response, 'header')) {
            $response->header($header, $corr);
        }

        return $response;
    }
}
