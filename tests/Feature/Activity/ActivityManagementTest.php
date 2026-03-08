<?php

namespace Tests\Feature\Activity;

use App\Models\User;
use App\Models\Activity;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    public function test_can_view_activities_list(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/activities/home');
        $response->assertStatus(200);
    }

    public function test_can_view_modern_activities_home(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/activities/modern-home');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_activity_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/activities/create');
        $response->assertStatus(200);
    }

    public function test_can_view_activity_categories(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/activities/categories');
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Activity categories page should be accessible or redirect'
        );
    }

    public function test_can_view_schedule(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/activities/schedule');
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Schedule page should be accessible or redirect'
        );
    }

    public function test_can_view_activity_detail(): void
    {
        $user = User::factory()->admin()->create();
        $activity = Activity::factory()->create(['centre_id' => $user->centre_id]);

        $response = $this->actingAs($user)->get("/activities/{$activity->id}");
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->isRedirect(),
            'Activity detail page should be accessible or redirect'
        );
    }

    public function test_can_view_activity_sessions(): void
    {
        $user = User::factory()->admin()->create();
        $activity = Activity::factory()->create(['centre_id' => $user->centre_id]);

        $response = $this->actingAs($user)->get("/activities/{$activity->id}/sessions");
        $response->assertStatus(200);
    }
}
