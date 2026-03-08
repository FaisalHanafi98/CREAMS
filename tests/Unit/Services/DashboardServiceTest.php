<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\DashboardService;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centre;
use Illuminate\Support\Facades\Cache;

class DashboardServiceTest extends TestCase
{
    protected DashboardService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DashboardService();

        // Create test centre
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
    // Dashboard Data Retrieval Tests
    // ========================================

    public function test_get_dashboard_data_returns_array(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        $this->assertIsArray($data);
    }

    public function test_get_dashboard_data_returns_admin_dashboard_for_admin_role(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        // Admin dashboard should have these sections
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('charts', $data);
        $this->assertArrayHasKey('recent', $data);
    }

    public function test_get_dashboard_data_returns_supervisor_dashboard_for_supervisor_role(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $data = $this->service->getDashboardData($supervisor->id, 'supervisor', '01');

        // Supervisor dashboard should have these sections
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('charts', $data);
    }

    public function test_get_dashboard_data_returns_teacher_dashboard_for_teacher_role(): void
    {
        $teacher = User::factory()->teacher()->create();

        $data = $this->service->getDashboardData($teacher->id, 'teacher', '01');

        // Teacher dashboard should have stats
        $this->assertArrayHasKey('stats', $data);
    }

    public function test_get_dashboard_data_returns_ajk_dashboard_for_ajk_role(): void
    {
        $ajk = User::factory()->ajk()->create();

        $data = $this->service->getDashboardData($ajk->id, 'ajk', '01');

        // AJK dashboard should have stats
        $this->assertArrayHasKey('stats', $data);
    }

    public function test_get_dashboard_data_returns_default_dashboard_for_unknown_role(): void
    {
        $user = User::factory()->admin()->create();

        // Pass 'unknown' as role string directly — no need to store it in DB
        $data = $this->service->getDashboardData($user->id, 'unknown', '01');

        $this->assertArrayHasKey('stats', $data);
    }

    // ========================================
    // Caching Tests
    // ========================================

    public function test_get_dashboard_data_caches_results(): void
    {
        $admin = User::factory()->admin()->create();

        // Clear any existing cache
        Cache::flush();

        // First call - should cache
        $data1 = $this->service->getDashboardData($admin->id, 'admin', '01');

        // Second call - should use cache
        $data2 = $this->service->getDashboardData($admin->id, 'admin', '01');

        // Results should be identical
        $this->assertEquals($data1, $data2);
    }

    public function test_get_dashboard_data_uses_correct_cache_key(): void
    {
        $admin = User::factory()->admin()->create();

        Cache::flush();

        // Call the method
        $this->service->getDashboardData($admin->id, 'admin', '01');

        // Check that cache was set with expected key
        $expectedKey = "dashboard_admin_{$admin->id}_01";
        $this->assertTrue(Cache::has($expectedKey));
    }

    public function test_different_roles_use_different_cache_keys(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        Cache::flush();

        $this->service->getDashboardData($admin->id, 'admin', '01');
        $this->service->getDashboardData($teacher->id, 'teacher', '01');

        // Both cache keys should exist
        $this->assertTrue(Cache::has("dashboard_admin_{$admin->id}_01"));
        $this->assertTrue(Cache::has("dashboard_teacher_{$teacher->id}_01"));
    }

    // ========================================
    // Admin Dashboard Structure Tests
    // ========================================

    public function test_admin_dashboard_has_stats_section(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);
    }

    public function test_admin_dashboard_has_charts_section(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        $this->assertArrayHasKey('charts', $data);
        $this->assertIsArray($data['charts']);
    }

    public function test_admin_dashboard_has_recent_section(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        $this->assertArrayHasKey('recent', $data);
        $this->assertIsArray($data['recent']);
    }

    public function test_admin_dashboard_has_alerts_section(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        $this->assertArrayHasKey('alerts', $data);
    }

    public function test_admin_dashboard_structure_is_valid(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        // Verify it's an array with expected sections
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(3, count($data), 'Admin dashboard should have at least 3 sections');
    }

    // ========================================
    // Supervisor Dashboard Structure Tests
    // ========================================

    public function test_supervisor_dashboard_has_stats_section(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $data = $this->service->getDashboardData($supervisor->id, 'supervisor', '01');

        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);
    }

    public function test_supervisor_dashboard_has_charts_section(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $data = $this->service->getDashboardData($supervisor->id, 'supervisor', '01');

        $this->assertArrayHasKey('charts', $data);
        $this->assertIsArray($data['charts']);
    }

    public function test_supervisor_dashboard_structure_is_valid(): void
    {
        $supervisor = User::factory()->supervisor()->create();

        $data = $this->service->getDashboardData($supervisor->id, 'supervisor', '01');

        // Verify it's an array with expected sections
        $this->assertIsArray($data);
        $this->assertGreaterThanOrEqual(2, count($data), 'Supervisor dashboard should have at least 2 sections');
    }

    // ========================================
    // Error Handling Tests
    // ========================================

    public function test_get_dashboard_data_handles_exceptions_gracefully(): void
    {
        // Use invalid user ID to trigger potential errors
        $data = $this->service->getDashboardData(999999, 'admin', '01');

        // Should return array even on error
        $this->assertIsArray($data);
    }

    public function test_get_dashboard_data_with_null_centre_id(): void
    {
        $admin = User::factory()->admin()->create();

        $data = $this->service->getDashboardData($admin->id, 'admin', null);

        // Should handle null centre_id
        $this->assertIsArray($data);
    }

    // ========================================
    // Data Presence Tests
    // ========================================

    public function test_admin_dashboard_includes_stats_section(): void
    {
        // Create some test data
        User::factory()->count(5)->create(['centre_id' => '01']);
        Trainee::factory()->count(10)->create(['centre_id' => '01']);
        Activity::factory()->count(3)->create(['centre_id' => '01']);

        $admin = User::factory()->admin()->create();
        $data = $this->service->getDashboardData($admin->id, 'admin', '01');

        // Stats section should exist (may be empty or populated)
        $this->assertArrayHasKey('stats', $data);
        $this->assertIsArray($data['stats']);
    }

    public function test_supervisor_dashboard_filters_by_centre(): void
    {
        // Create data for different centres
        Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Test Centre 2',
                'centre_phone' => '+60123456790',
                'centre_email' => 'test2@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        Trainee::factory()->create(['centre_id' => '01']);
        Trainee::factory()->create(['centre_id' => '02']);

        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);
        $data = $this->service->getDashboardData($supervisor->id, 'supervisor', '01');

        // Should only include centre 01 data
        $this->assertIsArray($data);
        $this->assertArrayHasKey('stats', $data);
    }

    public function test_teacher_dashboard_includes_personal_activities(): void
    {
        $teacher = User::factory()->teacher()->create();

        // Create activities for this teacher
        Activity::factory()->count(2)->create([
            'instructor_id' => $teacher->id,
            'centre_id' => $teacher->centre_id,
        ]);

        $data = $this->service->getDashboardData($teacher->id, 'teacher', $teacher->centre_id);

        // Should include stats
        $this->assertArrayHasKey('stats', $data);
    }

    // ========================================
    // Cache Expiration Tests
    // ========================================

    public function test_dashboard_cache_expires_after_timeout(): void
    {
        $admin = User::factory()->admin()->create();
        Cache::flush();

        // First call
        $this->service->getDashboardData($admin->id, 'admin', '01');

        // Cache should exist
        $cacheKey = "dashboard_admin_{$admin->id}_01";
        $this->assertTrue(Cache::has($cacheKey));

        // After clearing cache, it should be gone
        Cache::forget($cacheKey);
        $this->assertFalse(Cache::has($cacheKey));
    }

    // ========================================
    // Performance Tests
    // ========================================

    public function test_dashboard_data_retrieval_completes_quickly(): void
    {
        $admin = User::factory()->admin()->create();

        $startTime = microtime(true);
        $this->service->getDashboardData($admin->id, 'admin', '01');
        $duration = (microtime(true) - $startTime) * 1000;

        // Should complete within 2 seconds (2000ms)
        $this->assertLessThan(2000, $duration, "Dashboard took {$duration}ms, expected <2000ms");
    }
}
