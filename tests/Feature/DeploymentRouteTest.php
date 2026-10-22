<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_deploy_route_requires_valid_token(): void
    {
        $response = $this->get('/deploy');
        $response->assertStatus(403);

        $response = $this->get('/deploy?token=invalid_token');
        $response->assertStatus(403);
    }

    public function test_deploy_route_renders_with_valid_token(): void
    {
        $token = env('DEPLOY_TOKEN', 'nexteck2026deploy');
        $response = $this->get('/deploy?token=' . $token);
        
        $response->assertStatus(200);
        $response->assertSee('Database Deployment Runner');
        $response->assertSee('Run Migrate', false);
    }
}
