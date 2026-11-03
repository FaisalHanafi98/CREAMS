<?php

namespace Tests\Feature\Centre;

use App\Models\Activity;
use App\Models\Centre;
use App\Models\User;
use Tests\TestCase;

class CentreActivityReadTest extends TestCase
{
    public function test_centre_detail_lists_only_its_live_active_activities(): void
    {
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Centre One', 'centre_email' => 'centre-one@example.test']);
        Centre::factory()->create(['centre_id' => '02', 'centre_name' => 'Synthetic Centre Two', 'centre_email' => 'centre-two@example.test']);
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $visible = Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic visible programme', 'is_active' => true]);
        Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic inactive programme', 'is_active' => false]);
        Activity::create(['centre_id' => '02', 'activity_name' => 'Synthetic foreign programme', 'is_active' => true]);
        $deleted = Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic deleted programme', 'is_active' => true]);
        $deleted->delete();

        $this->actingAs($admin)->get('/centres/01')
            ->assertOk()
            ->assertViewHas('recentActivities', fn ($activities) => $activities->pluck('id')->all() === [$visible->id])
            ->assertSee('Synthetic visible programme')
            ->assertDontSee('Synthetic inactive programme')
            ->assertDontSee('Synthetic foreign programme')
            ->assertDontSee('Synthetic deleted programme');
    }

    public function test_teacher_sees_own_centre_activities_and_cannot_open_another_centre(): void
    {
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Assigned Centre', 'centre_email' => 'assigned@example.test']);
        Centre::factory()->create(['centre_id' => '02', 'centre_name' => 'Synthetic Other Centre', 'centre_email' => 'other@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic permitted programme', 'is_active' => true]);
        Activity::create(['centre_id' => '02', 'activity_name' => 'Synthetic forbidden programme', 'is_active' => true]);

        $this->actingAs($teacher)->get('/centres/01')
            ->assertOk()
            ->assertSee('Synthetic permitted programme')
            ->assertDontSee('Synthetic forbidden programme');

        $this->get('/centres/02')
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_guest_cannot_open_a_centre_detail(): void
    {
        $this->get('/centres/01')->assertRedirect(route('auth.loginpage'));
    }
}
