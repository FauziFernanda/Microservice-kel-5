<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthProxyController extends Controller
{
    public function register(Request $request)
    {
        $payload = $request->except('_token');
        Log::info('PROXY REGISTER - outgoing', ['payload' => $payload]);

        // Forward as JSON and request JSON responses so validation returns JSON (422)
        $resp = Http::withHeaders(['Accept' => 'application/json'])->post(rtrim(config('services.user_service.url'), '/') . '/api/auth/register', $payload);

        Log::info('PROXY REGISTER - response', ['status' => $resp->status(), 'body' => $resp->body()]);

        // If the client expects JSON, forward the raw response
        if ($request->expectsJson()) {
            return response()->json($resp->json(), $resp->status());
        }

        // For browser flows, respond with redirects/flash messages so forms work
        $body = null;
        try {
            $body = $resp->json();
        } catch (\Exception $e) {
            $body = null;
        }

        // Success (created)
        if ($resp->status() === 201) {
            return redirect('/login')->with('status', $body['message'] ?? 'Register berhasil');
        }

        // Validation errors
        if ($resp->status() === 422 && is_array($body) && isset($body['errors'])) {
            return redirect()->back()->withErrors($body['errors'])->withInput();
        }

        // Generic error: redirect back with a generic message
        return redirect()->back()->with('error', $body['message'] ?? 'Terjadi kesalahan saat registrasi')->withInput();
    }

    public function login(Request $request)
    {
        $payload = $request->only('email', 'password');
        Log::info('PROXY LOGIN - outgoing', ['payload' => $payload]);

        // Request JSON responses so errors are returned as JSON
        $resp = Http::withHeaders(['Accept' => 'application/json'])->post(rtrim(config('services.user_service.url'), '/') . '/api/auth/login', $payload);

        Log::info('PROXY LOGIN - response', ['status' => $resp->status(), 'body' => $resp->body()]);

        if ($resp->failed()) {
            // For AJAX/JSON clients forward raw response
            if ($request->expectsJson()) {
                return response()->json($resp->json(), $resp->status());
            }

            // For browser flows show validation/auth errors as flash message
            $body = null;
            try {
                $body = $resp->json();
            } catch (\Exception $e) {
                $body = null;
            }

            return redirect()->back()->with('error', $body['message'] ?? 'Gagal login')->withInput();
        }

        $data = $resp->json();

        // Optional: notify news-service about session/login
        $token = $data['token'] ?? $data['access_token'] ?? null;

        if (!empty($token)) {
            // store token and user in gateway session so browser requests can be forwarded
            session([
                'access_token' => $token,
                'user' => $data['user'] ?? null,
            ]);

            // Log successful login via gateway and include correlation id so it's visible in gateway logs
            $corr = $request->header('X-Correlation-ID') ?: (app()->bound('correlation_id') ? app('correlation_id') : \Illuminate\Support\Str::uuid()->toString());
            Log::info('USER LOGGED IN VIA GATEWAY', ['user' => session('user') ?? null, 'access_token' => session('access_token'), 'correlation_id' => $corr]);

            // notify news-service about session/login (best-effort)
            try {
                // Forward our correlation id so downstream services can log/propagate it
                $headers = ['X-Correlation-ID' => $corr];
                Http::withToken($token)->withHeaders($headers)->post(rtrim(config('services.news_service.url'), '/') . '/api/session', []);
            } catch (\Exception $e) {
                // swallow network errors; login should still succeed
            }
        }

        if ($request->expectsJson()) {
            return response()->json($data, $resp->status());
        }

        // For browser flows redirect to a proxied news page
        return redirect('/news/dashboard');
    }
}