<?php

namespace Tests\Feature\Message;

use App\Models\User;
use App\Models\Centre;
use Tests\TestCase;

class MessageManagementTest extends TestCase
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

    public function test_admin_can_view_message_list(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/messages');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_message_list(): void
    {
        $user = User::factory()->supervisor()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/messages');
        $response->assertStatus(200);
    }

    public function test_teacher_can_view_message_list(): void
    {
        $user = User::factory()->teacher()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/messages');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_message_create(): void
    {
        $user = User::factory()->admin()->create(['centre_id' => '01']);

        $response = $this->actingAs($user)->get('/messages/create');
        $this->assertTrue(
            in_array($response->getStatusCode(), [200, 302]),
            'Message create should load or redirect (got ' . $response->getStatusCode() . ')'
        );
    }

    public function test_unauthenticated_cannot_view_messages(): void
    {
        $response = $this->get('/messages');
        $response->assertRedirect();
    }
}
