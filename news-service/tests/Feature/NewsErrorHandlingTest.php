<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NewsErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_api_returns_404_and_sets_correlation_id_header()
    {
        $corr = 'news-corr-abc';

        $response = $this->withHeaders(['X-Correlation-ID' => $corr])
            ->getJson('/api/news/999999');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'News not found', 'correlation_id' => $corr]);
        $this->assertEquals($corr, $response->headers->get('X-Correlation-ID'));
    }
}
