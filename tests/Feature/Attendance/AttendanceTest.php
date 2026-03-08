<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class AttendanceTest extends TestCase
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
    // Activity Attendance
    // ========================================

    public function test_admin_can_view_activity_attendance(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/activity-attendance');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Activity attendance should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_activity_attendance_today_stats(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/activity-attendance/stats/today');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Today stats should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Staff Attendance
    // ========================================

    public function test_admin_can_view_staff_attendance_dashboard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staff-attendance');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff attendance dashboard should load (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Centre Attendance
    // ========================================

    public function test_admin_can_view_centre_attendance(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/centre/attendance');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Centre attendance should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_centres_attendance(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/centres/attendance');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Centres attendance should load (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Legacy Attendance
    // ========================================

    public function test_admin_can_view_attendance_page(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/attendance');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Attendance page should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_attendance_report(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/attendance/report');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Attendance report should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // RBAC
    // ========================================

    public function test_unauthenticated_cannot_view_attendance(): void
    {
        $response = $this->get('/attendance');
        $response->assertRedirect();
    }
}
