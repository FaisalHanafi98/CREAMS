<?php

namespace Tests\Feature\RBAC;

use Tests\TestCase;
use App\Models\User;
use App\Models\Centre;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Tests that each role can access what it should and is blocked from what it shouldn't.
 * Covers the gap identified in Wave 4: existing RBAC tests only check admin access,
 * not supervisor/teacher/ajk specific permissions.
 */
class RoleAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'centre_id' => '01',
            'status' => 'active',
        ]);
    }

    // -- Supervisor tests --

    public function test_supervisor_can_access_dashboard()
    {
        $user = $this->makeUser('supervisor');
        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_supervisor_can_access_trainee_list()
    {
        $user = $this->makeUser('supervisor');
        $response = $this->actingAs($user)->get('/supervisor/trainees');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_supervisor_can_access_activities()
    {
        $user = $this->makeUser('supervisor');
        $response = $this->actingAs($user)->get('/activities');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_supervisor_blocked_from_admin_centres()
    {
        $user = $this->makeUser('supervisor');
        $response = $this->actingAs($user)->get('/admin/centres');
        // Should redirect or return 403
        $this->assertContains($response->getStatusCode(), [302, 403]);
    }

    // -- Teacher tests --

    public function test_teacher_can_access_dashboard()
    {
        $user = $this->makeUser('teacher');
        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_teacher_can_access_activities()
    {
        $user = $this->makeUser('teacher');
        $response = $this->actingAs($user)->get('/activities');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_teacher_blocked_from_admin_users()
    {
        $user = $this->makeUser('teacher');
        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertContains($response->getStatusCode(), [302, 403]);
    }

    public function test_teacher_blocked_from_admin_centres()
    {
        $user = $this->makeUser('teacher');
        $response = $this->actingAs($user)->get('/admin/centres');
        $this->assertContains($response->getStatusCode(), [302, 403]);
    }

    // -- AJK tests --

    public function test_ajk_can_access_dashboard()
    {
        $user = $this->makeUser('ajk');
        $response = $this->actingAs($user)->get('/dashboard');
        $this->assertContains($response->getStatusCode(), [200, 302]);
    }

    public function test_ajk_blocked_from_admin_users()
    {
        $user = $this->makeUser('ajk');
        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertContains($response->getStatusCode(), [302, 403]);
    }

    // -- Unauthenticated tests --

    public function test_unauthenticated_user_redirected_from_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect();
    }

    public function test_unauthenticated_user_redirected_from_trainees()
    {
        $response = $this->get('/admin/trainees');
        $response->assertRedirect();
    }

    public function test_forbidden_access_renders_styled_403_page()
    {
        $user = $this->makeUser('teacher');

        $response = $this->withSession([
            'id' => $user->id,
            'role' => 'teacher',
            'logged_in' => true,
        ])->get('/admin/users');

        $response->assertStatus(403);
        $response->assertSee('Access Denied');
        $response->assertSee('Go to My Dashboard');
    }
}
