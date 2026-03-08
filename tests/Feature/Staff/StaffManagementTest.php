<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class StaffManagementTest extends TestCase
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
    }

    // ========================================
    // Staff List Access
    // ========================================

    public function test_admin_can_view_staff_list(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/home');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_staff_index(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/index');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_staff_list(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/home');
        $response->assertStatus(200);
    }

    public function test_teacher_can_view_staff_list(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/home');
        $response->assertStatus(200);
    }

    // ========================================
    // Staff Registration
    // ========================================

    public function test_admin_can_view_staff_register_page(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/register');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_staff_create_page(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/staffs/create');
        $response->assertStatus(200);
    }

    // ========================================
    // Staff Profile
    // ========================================

    public function test_admin_can_view_staff_profile(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/profile/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff profile should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_staff_edit_page(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/edit/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff edit page should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Staff Sub-pages
    // ========================================

    public function test_admin_can_view_staff_schedule(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/schedule/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff schedule should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_staff_activities(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/activities/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff activities should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_staff_trainees(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/trainees/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff trainees should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_staff_attendance(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '01']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/attendance/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Staff attendance should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    // ========================================
    // Cross-Centre Access
    // ========================================

    public function test_admin_can_view_staff_from_other_centre(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $staff = User::factory()->teacher()->create(['centre_id' => '02']);

        $encryptedId = encrypt($staff->id);
        $response = $this->actingAs($admin)->get("/staffs/profile/{$encryptedId}");
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Admin should access staff from other centre (got ' . $response->getStatusCode() . ')'
        );
    }
}
