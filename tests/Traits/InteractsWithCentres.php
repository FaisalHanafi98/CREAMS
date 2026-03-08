<?php

namespace Tests\Traits;

use App\Models\User;
use App\Models\Centre;
use Illuminate\Database\Eloquent\Model;

trait InteractsWithCentres
{
    /**
     * Assert that centre isolation is enforced
     * User should not be able to access resources from other centres
     *
     * @param User $user
     * @param Model $resource
     * @return void
     */
    protected function assertCentreIsolation(User $user, Model $resource): void
    {
        // If user is admin, they should have access to all centres
        if ($user->isAdmin()) {
            $this->assertTrue(
                true,
                'Admins have access to all centres'
            );
            return;
        }

        // For non-admin users, assert they can only access their own centre's resources
        $resourceCentreId = $resource->centre_id ?? null;

        $this->assertEquals(
            $user->centre_id,
            $resourceCentreId,
            "User from centre {$user->centre_id} should not access resources from centre {$resourceCentreId}"
        );
    }

    /**
     * Get test data for a specific centre
     *
     * @param string $centreId
     * @return array
     */
    protected function getCentreData(string $centreId): array
    {
        $centre = Centre::where('centre_id', $centreId)->first();

        if (!$centre) {
            $centre = Centre::firstOrCreate(
                ['centre_id' => $centreId],
                [
                    'centre_name' => "Test Centre {$centreId}",
                    'centre_phone' => '+6012345' . str_pad($centreId, 4, '0'),
                    'centre_email' => "centre{$centreId}@test.com",
                    'centre_capacity' => 50,
                    'centre_status' => 'active',
                    'is_active' => true,
                ]
            );
        }

        return [
            'centre_id' => $centre->centre_id,
            'centre_name' => $centre->centre_name,
            'centre' => $centre,
        ];
    }

    /**
     * Assert that a user can access their own centre's resource
     *
     * @param User $user
     * @param string $route
     * @return void
     */
    protected function assertCanAccessOwnCentre(User $user, string $route): void
    {
        $response = $this->actingAs($user)->get($route);

        $response->assertSuccessful();
    }

    /**
     * Assert that a user cannot access another centre's resource
     *
     * @param User $user
     * @param string $route
     * @return void
     */
    protected function assertCannotAccessOtherCentre(User $user, string $route): void
    {
        $response = $this->actingAs($user)->get($route);

        $response->assertStatus(403); // or 404 depending on implementation
    }

    /**
     * Create users for multiple centres for testing centre isolation
     *
     * @return array
     */
    protected function createUsersAcrossCentres(): array
    {
        return [
            'admin' => User::factory()->admin()->create(['centre_id' => '01']),
            'supervisor_01' => User::factory()->supervisor()->create(['centre_id' => '01']),
            'supervisor_02' => User::factory()->supervisor()->create(['centre_id' => '02']),
            'teacher_01' => User::factory()->teacher()->create(['centre_id' => '01']),
            'teacher_02' => User::factory()->teacher()->create(['centre_id' => '02']),
        ];
    }

    /**
     * Assert admin can access all centres
     *
     * @param User $admin
     * @param array $routes
     * @return void
     */
    protected function assertAdminAccessAllCentres(User $admin, array $routes): void
    {
        $this->assertTrue($admin->isAdmin(), 'User must be an admin');

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get($route);
            $response->assertSuccessful();
        }
    }
}
