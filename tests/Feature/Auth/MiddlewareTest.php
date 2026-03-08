<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
    }

    public function test_session_without_id_is_rejected(): void
    {
        $response = $this->withSession(['role' => 'admin'])->get('/dashboard');
        $response->assertRedirect();
    }

    public function test_session_without_role_is_rejected(): void
    {
        $response = $this->withSession(['id' => 1])->get('/dashboard');
        $response->assertRedirect();
    }

    public function test_public_routes_accessible_without_auth(): void
    {
        $publicRoutes = [
            '/login',
            '/auth/login',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                $response->getStatusCode() === 200 || $response->isRedirect(),
                "Public route {$route} should be accessible"
            );
        }
    }
}
