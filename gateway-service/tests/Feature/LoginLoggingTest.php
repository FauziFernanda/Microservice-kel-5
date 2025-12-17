<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoginLoggingTest extends TestCase
{
    public function test_gateway_logs_correlation_on_login()
    {
        Log::spy();

        // Capture whether gateway forwards the X-Correlation-ID when notifying news-service
        $receivedCorr = null;

        // Fake user-service login response and news-service session endpoint
        Http::fake([
            '*/api/auth/login' => Http::response(['message' => 'Login berhasil', 'token' => 'tkn', 'user' => ['id' => 5, 'email' => 'a@b.c']], 200),
            'http://localhost:8003/api/session' => function ($request) use (&$receivedCorr) {
                $receivedCorr = $request->header('X-Correlation-ID')[0] ?? null;
                return Http::response([], 200);
            },
        ]);

        // Use API route to keep things JSON-friendly and simpler to assert in tests
        $resp = $this->postJson('/api/login', ['email' => 'a@b.c', 'password' => 'pw']);

        $resp->assertStatus(200);
        $this->assertEquals('tkn', session('access_token'));

        // Ensure gateway logged the login event and included a correlation id
        $log = file_get_contents(storage_path('logs/laravel.log'));
        $this->assertStringContainsString('USER LOGGED IN VIA GATEWAY', $log);
        $this->assertMatchesRegularExpression('/correlation_id.*[0-9a-fA-F-]{8,}/', $log);

        // Ensure the gateway forwarded the correlation id to news-service
        $this->assertNotNull($receivedCorr, 'Gateway did not forward X-Correlation-ID to news-service');
        // $this->assertMatchesRegularExpression('/[0-9a-fA-F-]{8,}/', $receivedCorr);
    }
}
