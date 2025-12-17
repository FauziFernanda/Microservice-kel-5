<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayProxyTest extends TestCase
{
    public function test_login_forwards_to_user_service_and_returns_token(): void
    {
        Http::fake([
            'http://localhost:8002/api/auth/login' => Http::response([
                'token' => 'test-token',
                'user' => ['id' => 1, 'email' => 'test@example.com'],
            ], 200),
            // news-service session notification should be faked as well
            'http://localhost:8003/api/session' => Http::response([], 200),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        $response->assertStatus(200)->assertJsonFragment(['token' => 'test-token']);
        $this->assertEquals('test-token', session('access_token'));
    }

    public function test_news_proxy_forwards_authorization_header_from_session(): void
    {
        Http::fake(function ($request) {
            return Http::response(['ok' => true, 'auth' => $request->header('Authorization')], 200);
        });

        $response = $this->withSession(['access_token' => 'session-token'])
            ->get('/api/news/dashboard');

        $response->assertStatus(200);
        $this->assertContains('Bearer session-token', (array) $response->json('auth'));
    }

    public function test_news_dashboard_renders_for_logged_in_user(): void
    {
        // Use a wildcard fake and call the JSON forward route to assert we receive news data
        Http::fake([
            '*' => Http::response(['data' => [[ 'id' => 1, 'title' => 'Hello', 'excerpt' => 'World' ]]], 200),
        ]);

        $response = $this->withSession(['access_token' => 'session-token'])->getJson('/news/some-news');

        $response->assertStatus(200);
        $this->assertEquals('Hello', $response->json('data.0.title'));
    }
}
