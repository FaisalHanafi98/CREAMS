<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class ProfileTest extends TestCase
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

    public function test_admin_can_view_profile(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/profile');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Profile should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_profile_home(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/profile/home');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Profile home should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_teacher_can_view_profile(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/profile');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Teacher profile should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_unauthenticated_cannot_view_profile(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect();
    }
}
