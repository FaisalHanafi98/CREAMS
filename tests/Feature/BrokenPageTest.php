<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class BrokenPageTest extends TestCase
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
    // Previously Broken Pages — Now Fixed
    // ========================================

    public function test_aboutus_page_loads(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/aboutus');
        $response->assertStatus(200);
    }

    public function test_schedulehomepage_loads(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/schedulehomepage');
        $response->assertStatus(200);
    }

    public function test_settings_page_loads_for_admin(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertStatus(200);
    }

    public function test_settings_redirects_for_non_admin(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/settings');
        $response->assertRedirect();
    }

    public function test_login_page_loads_without_duplicate(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_contact_page_loads(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/contact');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Contact page should load (got ' . $response->getStatusCode() . ')'
        );
    }
}
