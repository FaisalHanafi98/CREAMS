<?php

namespace Tests\Feature\Activity;

use App\Models\Activity;
use App\Models\Centre;
use App\Models\User;
use Tests\TestCase;

class CategoryBrowseTest extends TestCase
{
    public function test_unknown_category_is_not_found_and_guest_access_is_denied(): void
    {
        $this->get('/activities/categories')->assertRedirect(route('auth.loginpage'));

        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Slug Centre', 'centre_email' => 'slug-category@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $this->actingAs($teacher)->get('/activities/categories/not-a-category')->assertNotFound();
    }

    public function test_admin_category_counts_include_permitted_centres(): void
    {
        foreach (['01', '02'] as $centreId) {
            Centre::factory()->create(['centre_id' => $centreId, 'centre_name' => 'Synthetic Admin Category '.$centreId, 'centre_email' => 'admin-category-'.$centreId.'@example.test']);
            Activity::create(['centre_id' => $centreId, 'category' => 'Learning Support', 'is_active' => true, 'activity_name' => 'Synthetic admin category activity '.$centreId]);
        }
        $admin = User::factory()->admin()->create(['centre_id' => '01']);

        $this->actingAs($admin)->get('/activities/categories')
            ->assertOk()
            ->assertViewHas('categories', fn ($categories) => collect($categories)->flatten(1)->firstWhere('name', 'Learning Support')->activities_count === 2);
    }

    public function test_empty_category_does_not_offer_supervisors_an_admin_only_create_action(): void
    {
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Empty Category Centre', 'centre_email' => 'empty-category@example.test']);
        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);

        $this->actingAs($supervisor)->get('/activities/categories/speech-therapy')
            ->assertOk()
            ->assertViewHas('activities', fn ($activities) => $activities->isEmpty())
            ->assertSee('There are currently no activities')
            ->assertDontSee('Create First Activity')
            ->assertDontSee('Create New Activity');
    }

    public function test_category_detail_displays_canonical_fields_without_other_centre_records(): void
    {
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Detail Centre', 'centre_email' => 'category-detail@example.test']);
        Centre::factory()->create(['centre_id' => '02', 'centre_name' => 'Synthetic Foreign Detail Centre', 'centre_email' => 'category-foreign@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $visible = Activity::create([
            'centre_id' => '01', 'category' => 'Learning Support', 'is_active' => true,
            'activity_name' => 'Synthetic visible category detail', 'activity_description' => 'Synthetic description from the canonical field',
            'session_duration_minutes' => 45, 'max_participants' => 8, 'instructor_id' => $teacher->id,
        ]);
        Activity::create(['centre_id' => '02', 'category' => 'Learning Support', 'is_active' => true, 'activity_name' => 'Synthetic forbidden category detail']);
        Activity::create(['centre_id' => '01', 'category' => 'Learning Support', 'is_active' => false, 'activity_name' => 'Synthetic inactive category detail']);

        $this->actingAs($teacher)->get('/activities/categories/learning-support')
            ->assertOk()
            ->assertViewHas('activities', fn ($activities) => $activities->pluck('id')->all() === [$visible->id])
            ->assertSee('Synthetic description from the canonical field')
            ->assertSee('45 min')
            ->assertDontSee('Synthetic forbidden category detail')
            ->assertDontSee('Synthetic inactive category detail');
    }

    public function test_category_page_counts_only_visible_active_activities_using_current_categories(): void
    {
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Category Centre', 'centre_email' => 'category-centre@example.test']);
        Centre::factory()->create(['centre_id' => '02', 'centre_name' => 'Synthetic Other Category Centre', 'centre_email' => 'other-category-centre@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $attributes = ['category' => 'Learning Support', 'is_active' => true];
        Activity::create($attributes + ['centre_id' => '01', 'activity_name' => 'Synthetic counted activity']);
        Activity::create(['category' => 'Learning Support', 'is_active' => false, 'centre_id' => '01', 'activity_name' => 'Synthetic inactive category activity']);
        Activity::create($attributes + ['centre_id' => '02', 'activity_name' => 'Synthetic foreign category activity']);
        $deleted = Activity::create($attributes + ['centre_id' => '01', 'activity_name' => 'Synthetic deleted category activity']);
        $deleted->delete();

        $this->actingAs($teacher)->get('/activities/categories')
            ->assertOk()
            ->assertViewHas('categories', function ($categories) {
                $catalogue = collect($categories)->flatten(1);
                $this->assertCount(6, $catalogue);
                $this->assertSame(1, $catalogue->firstWhere('name', 'Learning Support')->activities_count);
                $this->assertSame(0, $catalogue->firstWhere('name', 'Speech Therapy')->activities_count);

                return true;
            })
            ->assertSee('Learning Support')
            ->assertSee('/activities/categories/learning-support', false);
    }
}
