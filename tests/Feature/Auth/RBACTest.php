<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class RBACTest extends TestCase
{
    public function test_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_access_centre_management(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/centres');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_teacher_cannot_access_admin_routes(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirect(),
            'Teacher should be blocked from admin routes'
        );
    }

    public function test_ajk_cannot_access_admin_routes(): void
    {
        $user = User::factory()->ajk()->create();

        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirect(),
            'AJK should be blocked from admin routes'
        );
    }

    public function test_supervisor_can_access_letter_generator(): void
    {
        $user = User::factory()->supervisor()->create();

        $response = $this->actingAs($user)->get('/letters/modern');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_teacher_can_access_activities(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->get('/activities/home');
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_each_role_can_access_dashboard(): void
    {
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/dashboard');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access dashboard");
        }
    }

    public function test_each_role_can_access_profile(): void
    {
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/profile');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access profile");
        }
    }

    // ========================================
    // Expanded RBAC Coverage
    // ========================================

    public function test_supervisor_cannot_access_admin_users(): void
    {
        $user = User::factory()->supervisor()->create();

        $response = $this->actingAs($user)->get('/admin/users');
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirect(),
            'Supervisor should be blocked from admin user management'
        );
    }

    public function test_each_role_can_access_trainee_list(): void
    {
        $roles = ['admin', 'supervisor', 'teacher'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/trainees/home');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access trainee list");
        }
    }

    public function test_each_role_can_access_activities(): void
    {
        $roles = ['admin', 'supervisor', 'teacher'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/activities/home');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access activities");
        }
    }

    public function test_each_role_can_access_notifications(): void
    {
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/notifications');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access notifications");
        }
    }

    public function test_each_role_can_access_messages(): void
    {
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];

        foreach ($roles as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get('/messages');
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access messages");
        }
    }

    public function test_each_role_accesses_own_dashboard_route(): void
    {
        $roleRoutes = [
            'admin' => '/admin/dashboard',
            'supervisor' => '/supervisor/dashboard',
            'teacher' => '/teacher/dashboard',
            'ajk' => '/ajk/dashboard',
        ];

        foreach ($roleRoutes as $role => $route) {
            $user = User::factory()->state(['role' => $role])->create();

            $response = $this->actingAs($user)->get($route);
            $this->assertNotEquals(403, $response->getStatusCode(), "{$role} should access {$route}");
        }
    }

    public function test_teacher_blocked_from_admin_centres(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->get('/admin/centres');
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirect(),
            'Teacher should be blocked from admin centres'
        );
    }

    public function test_ajk_blocked_from_admin_centres(): void
    {
        $user = User::factory()->ajk()->create();

        $response = $this->actingAs($user)->get('/admin/centres');
        $this->assertTrue(
            $response->getStatusCode() === 403 || $response->isRedirect(),
            'AJK should be blocked from admin centres'
        );
    }
}
