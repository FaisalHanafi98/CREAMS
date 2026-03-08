<?php

namespace Tests\Feature\IEP;

use App\Models\User;
use Tests\TestCase;

class IepManagementTest extends TestCase
{
    public function test_admin_can_view_iep_list(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/iep');
        $response->assertStatus(200);
    }

    public function test_supervisor_can_view_iep_list(): void
    {
        $user = User::factory()->supervisor()->create();

        $response = $this->actingAs($user)->get('/iep');
        $response->assertStatus(200);
    }

    public function test_teacher_can_view_iep_list(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->get('/iep');
        $response->assertStatus(200);
    }

    public function test_admin_can_view_iep_create_page(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/iep/create');
        $response->assertStatus(200);
    }
}
