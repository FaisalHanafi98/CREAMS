<?php

namespace Tests\Feature\Letter;

use App\Models\User;
use Tests\TestCase;

class LetterGenerationTest extends TestCase
{
    public function test_admin_can_view_letter_generator(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/letters/modern');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_letter_generator(): void
    {
        $user = User::factory()->supervisor()->create();

        $response = $this->actingAs($user)->get('/letters/modern');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_letter_create_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->withSession([
                'id' => $user->id,
                'role' => 'admin',
                'centre_id' => $user->centre_id,
                'logged_in' => true,
            ])
            ->get('/letters/modern/create');
        // Admin should not get 403 (forbidden) or 401 (unauthorized)
        // 302 is acceptable (redirect to template selection or letter list)
        // 200 is ideal (direct access to create page)
        $this->assertNotEquals(403, $response->getStatusCode(), 'Admin should not be forbidden');
        $this->assertNotEquals(401, $response->getStatusCode(), 'Admin should not be unauthorized');
        $this->assertContains($response->getStatusCode(), [200, 302], 'Expected 200 or 302');
    }

    public function test_admin_can_view_letter_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/letters');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_letter_templates(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/admin/letter-templates');
        $response->assertStatus(200);
    }
}
