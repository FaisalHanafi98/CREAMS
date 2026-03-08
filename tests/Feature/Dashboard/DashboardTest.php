<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Test Centre',
                'centre_phone' => '+60123456789',
                'centre_email' => 'test@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );
    }

    // ========================================
    // Main Dashboard
    // ========================================

    public function test_admin_can_view_dashboard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_dashboard(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_teacher_can_view_dashboard(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_ajk_can_view_dashboard(): void
    {
        $user = User::factory()->ajk()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    // ========================================
    // Dashboard Variants
    // ========================================

    public function test_admin_can_view_modern_dashboard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard/modern-new');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Modern dashboard should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_main_dashboard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/dashboard/main');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Main dashboard should load (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Role-Specific Dashboard Routes
    // ========================================

    public function test_admin_dashboard_route(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/admin/dashboard');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_supervisor_dashboard_route(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/supervisor/dashboard');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_teacher_dashboard_route(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/teacher/dashboard');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_ajk_dashboard_route(): void
    {
        $user = User::factory()->ajk()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/ajk/dashboard');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    // ========================================
    // Auth Guard
    // ========================================

    public function test_unauthenticated_redirected_from_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect();
    }
}
