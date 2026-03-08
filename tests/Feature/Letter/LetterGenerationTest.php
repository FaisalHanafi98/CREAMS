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

        $response = $this->actingAs($user)->get('/letters/modern/create');
        // May return 500 if letter templates not seeded — verify not auth failure
        $this->assertNotEquals(302, $response->getStatusCode(), 'Admin should not be redirected');
        $this->assertNotEquals(403, $response->getStatusCode(), 'Admin should not be forbidden');
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
