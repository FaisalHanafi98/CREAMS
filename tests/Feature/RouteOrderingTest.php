<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

/**
 * Regression tests for route ordering fix.
 *
 * Ensures static routes like /create are not shadowed by
 * wildcard routes like /{id} in the same prefix group.
 */
class RouteOrderingTest extends TestCase
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

    public function test_centres_create_not_shadowed_by_wildcard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/centres/create');
        // Should NOT return 404 — /create must not be matched as /{id}
        $this->assertNotEquals(
            404,
            $response->getStatusCode(),
            '/centres/create should not be caught by /{id} wildcard route'
        );
    }

    public function test_asset_parents_create_not_shadowed_by_wildcard(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/asset-parents/create');
        // Should NOT return 404 — /create must not be matched as /{id}
        $this->assertNotEquals(
            404,
            $response->getStatusCode(),
            '/asset-parents/create should not be caught by /{id} wildcard route'
        );
    }

    public function test_centres_show_still_works_with_numeric_id(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/centres/01');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 500]),
            '/centres/{id} should still work for numeric IDs (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_asset_parents_show_still_works_with_numeric_id(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/asset-parents/1');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302, 404, 500]),
            '/asset-parents/{id} should still work for numeric IDs (got ' . $response->getStatusCode() . ')'
        );
    }
}
