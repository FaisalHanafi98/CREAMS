<?php

namespace Tests\Feature\Volunteer;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class VolunteerTest extends TestCase
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

    public function test_admin_can_view_volunteer_page(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/volunteer');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Volunteer page should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_volunteer_applications(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/volunteer/applications');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Volunteer applications should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_admin_can_view_admin_volunteers(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/admin/volunteers');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Admin volunteers should load (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_volunteer_centres_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/volunteer/centres');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Volunteer centres should respond (got ' . $response->getStatusCode() . ')'
        );
    }
}
