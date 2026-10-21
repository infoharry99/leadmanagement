<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('leads.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('campaigns.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_sales_rep_can_access_dashboard_and_leads(): void
    {
        $salesRep = User::factory()->create(['role' => 'sales_rep']);

        $response = $this->actingAs($salesRep)->get(route('dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($salesRep)->get(route('leads.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_access_all_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('campaigns.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('sequences.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('inbox.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('blogs.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('reports.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('templates.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('templates.create'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('campaigns.create'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('sequences.create'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('ai.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('social.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('calendar.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('handovers.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('tasks.index'));
        $response->assertStatus(200);
    }
}
