<?php

namespace Tests\Traits;

use App\Models\User;

trait InteractsWithRoles
{
    /**
     * Create and act as an admin user
     *
     * @param string $centreId
     * @return $this
     */
    protected function actingAsAdmin(string $centreId = '01'): self
    {
        $admin = User::factory()->admin()->create(['centre_id' => $centreId]);

        // Set session data for custom auth middleware
        session([
            'id' => $admin->id,
            'role' => $admin->role,
            'centre_id' => $admin->centre_id,
            'name' => $admin->name,
            'email' => $admin->email,
        ]);

        return $this->actingAs($admin);
    }

    /**
     * Create and act as a supervisor user
     *
     * @param string $centreId
     * @return $this
     */
    protected function actingAsSupervisor(string $centreId = '01'): self
    {
        $supervisor = User::factory()->supervisor()->create(['centre_id' => $centreId]);

        // Set session data for custom auth middleware
        session([
            'id' => $supervisor->id,
            'role' => $supervisor->role,
            'centre_id' => $supervisor->centre_id,
            'name' => $supervisor->name,
            'email' => $supervisor->email,
        ]);

        return $this->actingAs($supervisor);
    }

    /**
     * Create and act as a teacher user
     *
     * @param string $centreId
     * @return $this
     */
    protected function actingAsTeacher(string $centreId = '01'): self
    {
        $teacher = User::factory()->teacher()->create(['centre_id' => $centreId]);

        // Set session data for custom auth middleware
        session([
            'id' => $teacher->id,
            'role' => $teacher->role,
            'centre_id' => $teacher->centre_id,
            'name' => $teacher->name,
            'email' => $teacher->email,
        ]);

        return $this->actingAs($teacher);
    }

    /**
     * Create and act as an AJK user
     *
     * @param string $centreId
     * @return $this
     */
    protected function actingAsAJK(string $centreId = '01'): self
    {
        $ajk = User::factory()->ajk()->create(['centre_id' => $centreId]);

        // Set session data for custom auth middleware
        session([
            'id' => $ajk->id,
            'role' => $ajk->role,
            'centre_id' => $ajk->centre_id,
            'name' => $ajk->name,
            'email' => $ajk->email,
        ]);

        return $this->actingAs($ajk);
    }

    /**
     * Create a user with a specific role
     *
     * @param string $role
     * @param string $centreId
     * @return User
     */
    protected function createUserWithRole(string $role, string $centreId = '01'): User
    {
        return User::factory()->$role()->create(['centre_id' => $centreId]);
    }
}
