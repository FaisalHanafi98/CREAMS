<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_admin_can_view_notifications(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_notifications(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/notifications');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_unread_notifications(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/notifications/unread');
        $response->assertStatus(200);
    }

    public function test_unauthenticated_cannot_view_notifications(): void
    {
        $response = $this->get('/notifications');
        $response->assertRedirect();
    }
}
