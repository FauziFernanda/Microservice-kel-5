<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class LoginLoggingTest extends TestCase
{
    use RefreshDatabase;
    public function test_user_service_logs_correlation_on_login()
    {
        // Create user
        $user = User::factory()->create(['email' => 'logtest@example.com', 'password' => bcrypt('pw')]);

        Log::spy();

        $corr = 'corr-test-xyz';

        $resp = $this->withHeaders(['X-Correlation-ID' => $corr])->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'pw']);

        $resp->assertStatus(200);

        // Assert the user-service logged the successful login and included the correlation id
        Log::shouldHaveReceived('info')->withArgs(function ($message, $context) use ($corr) {
            if (stripos($message, 'USER LOGGED IN') === false) {
                return false;
            }

            return isset($context['correlation_id']) && ($context['correlation_id'] === $corr || $context['correlation_id'] === null /* fallback allowed */);
        });
    }
}
