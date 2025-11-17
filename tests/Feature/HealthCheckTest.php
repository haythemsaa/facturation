<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Test basic health check endpoint.
     */
    public function test_basic_health_check_returns_success(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
            ])
            ->assertJson([
                'status' => 'healthy',
            ]);
    }

    /**
     * Test detailed health check endpoint.
     */
    public function test_detailed_health_check_returns_all_components(): void
    {
        $response = $this->getJson('/api/health/detailed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['status', 'response_time'],
                    'cache' => ['status', 'response_time'],
                    'storage' => ['status', 'response_time'],
                    'queue' => ['status', 'response_time'],
                ],
            ]);
    }

    /**
     * Test metrics endpoint returns system statistics.
     */
    public function test_metrics_endpoint_returns_statistics(): void
    {
        $response = $this->getJson('/api/health/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'tenants_count',
                'users_count',
                'documents_count',
                'database_size',
                'storage_size',
                'timestamp',
            ]);
    }
}
