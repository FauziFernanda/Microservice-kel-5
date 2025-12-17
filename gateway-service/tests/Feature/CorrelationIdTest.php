<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CorrelationIdTest extends TestCase
{
    public function test_gateway_sends_correlation_id_and_authorization_to_news_service()
    {
        // Fake outgoing HTTP calls. When upstream generates a correlation id we expect the gateway to echo it.
        Http::fake(function ($request) {
            // Return dummy news list and a generated correlation id
            return Http::response(['data' => [['id' => 1, 'title' => 'Test']], 'correlation_id' => 'fake-correlation-id'], 200);
        });

        // Simulate logged in user by setting session access token
        // Call the JSON forward route so the controller issues an upstream HTTP request
        $response = $this->withSession(['access_token' => 'test-token'])
            ->getJson('/news/some-path');

        $response->assertStatus(200);

        // (Recorded upstream request assertions are covered below when we assert headers.)

        // The gateway should echo a usable X-Correlation-ID header back to the client even if the client didn't send one
        $this->assertTrue($response->headers->has('X-Correlation-ID'));
        $this->assertNotEmpty($response->headers->get('X-Correlation-ID'));

        // Now assert that when the client provides a correlation id, the gateway forwards it upstream
        $corr = '00000000-0000-4000-8000-00000000000A';
        Http::fake(); // reset fake to capture new request
        $this->withSession(['access_token' => 'test-token'])
            ->withHeaders(['X-Correlation-ID' => $corr])
            ->getJson('/news/some-path');

        Http::assertSent(function ($request) use ($corr) {
            return $request->hasHeader('X-Correlation-ID') && ($request->header('X-Correlation-ID')[0] ?? '') === $corr;
        });

        // Authorization header forwarding is desirable; assert at least one recorded request had a Bearer token
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization') && str_contains($request->header('Authorization')[0] ?? '', 'Bearer');
        });
    }
}
