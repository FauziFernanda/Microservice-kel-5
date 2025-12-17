<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AuthErrorHandlingTest extends TestCase
{
    public function test_login_exception_returns_500_and_correlation_id()
    {
        // Arrange: make Auth::attempt throw
        Auth::shouldReceive('attempt')->andThrow(new \Exception('boom'));

        $corr = 'test-corr-123';

        // Act
        $response = $this->withHeaders(['X-Correlation-ID' => $corr])
            ->postJson('/api/auth/login', ['email' => 'a@b.c', 'password' => 'pw']);

        // Assert
        $response->assertStatus(500);
        $response->assertJsonStructure(['error', 'correlation_id']);
        $this->assertEquals($corr, $response->headers->get('X-Correlation-ID'));
    }
}
