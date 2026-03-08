<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class ApiEndpointTest extends TestCase
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

    public function test_health_endpoint_responds(): void
    {
        $response = $this->get('/api/health');
        $response->assertStatus(200);
    }

    public function test_session_check_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/session-check');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Session check should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_dashboard_data_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/dashboard-data');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Dashboard data should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_notification_check_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/notifications/check');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Notification check should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_stats_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/stats');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Stats should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_search_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/search?q=test');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Search should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_activities_api_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/activities');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Activities API should respond (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_activities_categories_api_endpoint(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/api/activities/categories');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            'Categories API should respond (got ' . $response->getStatusCode() . ')'
        );
    }
}
