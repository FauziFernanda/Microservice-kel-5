<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsProxyController extends Controller
{
    public function forward(Request $request, $path = '')
    {
        $method = strtolower($request->method());

        // Determine whether this request should hit the API (JSON) or the web UI (HTML)
        $wantsJson = $request->wantsJson() || str_contains($request->header('Accept', ''), 'application/json');

        if ($wantsJson) {
            $base = rtrim(config('services.news_service.url'), '/') . '/api/news';
            $url = $base . ($path ? '/' . ltrim($path, '/') : '');
        } else {
            // Forward to the news-service's web routes so the HTML admin UI is returned
            $base = rtrim(config('services.news_service.url'), '/');
            $url = $base . '/news' . ($path ? '/' . ltrim($path, '/') : '');
        }

        // Add correlation header and accept header to upstream request
        // Only forward X-Correlation-ID when it has a non-empty value; avoid sending an empty header
        $headers = [
            'Accept' => $request->header('Accept', $wantsJson ? 'application/json' : 'text/html'),
        ];

        if ($corr = $request->header('X-Correlation-ID')) {
            $headers['X-Correlation-ID'] = $corr;
        }

        $client = Http::withHeaders($headers);

        if ($token = $request->bearerToken()) {
            $client = $client->withToken($token);
        }

        try {
            if (in_array($method, ['get', 'delete'])) {
                $resp = $client->$method($url, $request->query());
            } else {
                $resp = $client->$method($url, $request->all());
            }
        } catch (\Exception $e) {
            // Upstream service unreachable/failed
            \Illuminate\Support\Facades\Log::error('Upstream call failed', ['exception' => $e->getMessage(), 'url' => $url]);
            $corr = $request->header('X-Correlation-ID') ?: (app()->bound('correlation_id') ? app('correlation_id') : null);
            if (!$corr) {
                $corr = (string) \Illuminate\Support\Str::uuid();
            }

            $err = [
                'error' => 'Upstream service error',
                'service' => 'news-service',
                'correlation_id' => $corr,
            ];

            return response()->json($err, 502)->header('X-Correlation-ID', $corr);
        }

        // If the upstream returned HTML, pass it through as-is.
        $contentType = $resp->header('Content-Type', 'application/json');

        if (str_contains($contentType, 'text/html')) {
            $respBody = response($resp->body(), $resp->status())->header('Content-Type', $contentType);
            // Propagate correlation ID from upstream (prefer header, then body) if present
            $upCorr = $resp->header('X-Correlation-ID') ?: ($resp->json()['correlation_id'] ?? null);
            if ($upCorr) {
                $respBody->header('X-Correlation-ID', $upCorr);
            }

            return $respBody;
        }

        // Default: return JSON body and ensure X-Correlation-ID is echoed so clients can follow it
        $body = $resp->json();
        $upCorr = $resp->header('X-Correlation-ID') ?: ($body['correlation_id'] ?? null) ?: (app()->bound('correlation_id') ? app('correlation_id') : null);
        $out = response()->json($body, $resp->status());
        if ($upCorr) {
            $out->header('X-Correlation-ID', $upCorr);
        }

        return $out;
    }

    public function dashboard(Request $request)
    {
        $token = session('access_token');
        
        if (empty($token)) {
            return redirect('/login');
        }

        try {
            $url = rtrim(config('services.news_service.url'), '/') . '/api/news';
            \Illuminate\Support\Facades\Log::debug('user-service calling news-service', ['tokenSet' => !empty($token), 'correlation' => app()->bound('correlation_id') ? app('correlation_id') : null, 'url' => $url]);

            // Only include correlation header when available to avoid forwarding an empty value
            $client = Http::withToken($token);
            if (app()->bound('correlation_id') && $corr = app('correlation_id')) {
                $client = $client->withHeaders(['X-Correlation-ID' => $corr]);
            }

            $resp = $client->get($url);
            
            if ($resp->failed()) {
                \Illuminate\Support\Facades\Log::error('Failed to fetch news list from news-service', ['status' => $resp->status()]);
                return view('news.dashboard', ['news' => [], 'error' => 'Gagal mengambil data']);
            }

            $data = $resp->json();
            $items = $data['data'] ?? $data ?? [];

            return view('news.dashboard', ['news' => $items]);
        } catch (\Exception $e) {
            return view('news.dashboard', ['news' => [], 'error' => 'Service tidak terjangkau']);
        }
    }
}