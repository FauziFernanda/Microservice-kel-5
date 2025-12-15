<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CorrelationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 1. Ambil correlation ID dari header atau generate baru
        $correlationId = $request->header('X-Correlation-ID')
            ?? Str::uuid()->toString();

        // 2. Simpan di request attributes (aman & standar)
        $request->attributes->set('correlation_id', $correlationId);

        // 3. Set ke header request internal
        $request->headers->set('X-Correlation-ID', $correlationId);

        // 4. Set context log
        Log::withContext([
            'correlation_id' => $correlationId,
        ]);

        // 5. Lanjutkan request
        $response = $next($request);

        // 6. Pastikan response punya correlation ID
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
