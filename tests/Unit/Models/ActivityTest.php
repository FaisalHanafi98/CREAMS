<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Activity;
use App\Models\Centre;
use App\Models\User;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\Trainee;

class ActivityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a default centre for testing
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

    // ========================================
    // Relationship Tests
    // ========================================

    public function test_centre_relationship_returns_belongs_to(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $activity->centre()
        );
    }

    public function test_centre_relationship_retrieves_correct_centre(): void
    {
        $centre = Centre::where('centre_id', '01')->first();
        $activity = Activity::factory()->create(['centre_id' => '01']);

        $this->assertEquals($centre->id, $activity->centre->id);
    }

    public function test_instructor_relationship_returns_belongs_to(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $activity->instructor()
        );
    }

    public function test_sessions_relationship_returns_has_many(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $activity->sessions()
        );
    }

    public function test_enrollments_relationship_returns_has_many(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $activity->enrollments()
        );
    }

    public function test_participants_relationship_returns_belongs_to_many(): void
    {
        $activity = Activity::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $activity->participants()
        );
    }

    // ========================================
    // Scope Tests
    // ========================================

    public function test_scope_active_returns_only_active_activities(): void
    {
        Activity::factory()->create(['is_active' => true]);
        Activity::factory()->create(['is_active' => false]);

        $activeActivities = Activity::active()->get();

        $this->assertCount(1, $activeActivities);
        $this->assertTrue($activeActivities->first()->is_active);
    }

    public function test_scope_by_category_filters_correctly(): void
    {
        Activity::factory()->create(['category' => 'Autism Spectrum Support']);
        Activity::factory()->create(['category' => 'Speech Therapy']);

        $autismActivities = Activity::byCategory('Autism Spectrum Support')->get();

        $this->assertCount(1, $autismActivities);
        $this->assertEquals('Autism Spectrum Support', $autismActivities->first()->category);
    }

    public function test_scope_by_centre_filters_correctly(): void
    {
        Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Test Centre 2',
                'centre_phone' => '+60123456790',
                'centre_email' => 'test2@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        Activity::factory()->create(['centre_id' => '01']);
        Activity::factory()->create(['centre_id' => '02']);

        $centre01Activities = Activity::byCentre('01')->get();

        $this->assertCount(1, $centre01Activities);
        $this->assertEquals('01', $centre01Activities->first()->centre_id);
    }

    // ========================================
    // Capacity Tests
    // ========================================

    public function test_has_capacity_returns_true_when_below_max(): void
    {
        $activity = Activity::factory()->create(['max_participants' => 10]);

        // Create 5 enrollments
        for ($i = 0; $i < 5; $i++) {
            $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
            ActivityEnrollment::factory()->create([
                'activity_id' => $activity->id,
                'trainee_id' => $trainee->id,
                'enrollment_status' => 'enrolled',
            ]);
        }

        $this->assertTrue($activity->fresh()->hasCapacity());
    }

    public function test_has_capacity_returns_false_when_at_max(): void
    {
        $activity = Activity::factory()->create(['max_participants' => 5]);

        // Create 5 enrollments
        for ($i = 0; $i < 5; $i++) {
            $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
            ActivityEnrollment::factory()->create([
                'activity_id' => $activity->id,
                'trainee_id' => $trainee->id,
                'enrollment_status' => 'enrolled',
            ]);
        }

        $this->assertFalse($activity->fresh()->hasCapacity());
    }

    // ========================================
    // Completion Tests
    // ========================================

    public function test_should_be_completed_returns_false_when_no_sessions(): void
    {
        $activity = Activity::factory()->create();

        $this->assertFalse($activity->shouldBeCompleted());
    }

    public function test_should_be_completed_returns_false_when_sessions_pending(): void
    {
        $activity = Activity::factory()->create();

        // Get next Monday (ensure weekday)
        $nextWeekday = now()->addDays(7);
        while ($nextWeekday->dayOfWeek === 0 || $nextWeekday->dayOfWeek === 6) {
            $nextWeekday->addDay();
        }

        ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'session_status' => 'scheduled',
            'session_date' => $nextWeekday,
        ]);

        $this->assertFalse($activity->shouldBeCompleted());
    }

    public function test_should_be_completed_returns_true_when_all_sessions_completed(): void
    {
        $activity = Activity::factory()->create();

        // Get past weekdays
        $pastWeekday1 = now()->subDays(7);
        while ($pastWeekday1->dayOfWeek === 0 || $pastWeekday1->dayOfWeek === 6) {
            $pastWeekday1->subDay();
        }

        $pastWeekday2 = now()->subDays(3);
        while ($pastWeekday2->dayOfWeek === 0 || $pastWeekday2->dayOfWeek === 6) {
            $pastWeekday2->subDay();
        }

        // Create 2 completed sessions
        ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'session_status' => 'completed',
            'session_date' => $pastWeekday1,
        ]);

        ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'session_status' => 'completed',
            'session_date' => $pastWeekday2,
        ]);

        $this->assertTrue($activity->shouldBeCompleted());
    }

    public function test_update_completion_status_marks_activity_inactive(): void
    {
        $activity = Activity::factory()->create(['is_active' => true]);

        // Get past weekday
        $pastWeekday = now()->subDays(7);
        while ($pastWeekday->dayOfWeek === 0 || $pastWeekday->dayOfWeek === 6) {
            $pastWeekday->subDay();
        }

        // Create all completed sessions
        ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'session_status' => 'completed',
            'session_date' => $pastWeekday,
        ]);

        $result = $activity->updateCompletionStatus();

        $this->assertTrue($result);
        $this->assertFalse($activity->fresh()->is_active);
    }

    // ========================================
    // Type Check Tests
    // ========================================

    public function test_is_rehabilitation_returns_true_for_autism_support(): void
    {
        $activity = Activity::factory()->create(['category' => 'Autism Spectrum Support']);

        $this->assertTrue($activity->isRehabilitation());
    }

    public function test_is_academic_returns_true_for_learning_support(): void
    {
        $activity = Activity::factory()->create(['category' => 'Learning Support']);

        $this->assertTrue($activity->isAcademic());
    }

    public function test_overarching_type_attribute_returns_correct_type(): void
    {
        $activity = Activity::factory()->create(['category' => 'Physical Disabilities']);

        $this->assertEquals('rehabilitation', $activity->overarching_type);
    }

    // ========================================
    // Accessor Tests
    // ========================================

    public function test_category_icon_attribute_returns_correct_icon(): void
    {
        $activity = Activity::factory()->create(['category' => 'Speech Therapy']);

        $this->assertEquals('fas fa-comments', $activity->category_icon);
    }

    public function test_category_color_attribute_returns_correct_color(): void
    {
        $activity = Activity::factory()->create(['category' => 'Speech Therapy']);

        $this->assertEquals('#F44336', $activity->category_color);
    }

    public function test_formatted_duration_attribute_returns_hours_and_minutes(): void
    {
        $activity = Activity::factory()->create(['session_duration_minutes' => 90]);

        $this->assertEquals('1h 30m', $activity->formatted_duration);
    }

    public function test_formatted_duration_attribute_returns_only_minutes(): void
    {
        $activity = Activity::factory()->create(['session_duration_minutes' => 45]);

        $this->assertEquals('45m', $activity->formatted_duration);
    }

    // ========================================
    // Count Tests
    // ========================================

    public function test_active_enrollments_count_returns_correct_count(): void
    {
        $activity = Activity::factory()->create();

        // Create 3 enrolled trainees
        for ($i = 0; $i < 3; $i++) {
            $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
            ActivityEnrollment::factory()->create([
                'activity_id' => $activity->id,
                'trainee_id' => $trainee->id,
                'enrollment_status' => 'enrolled',
            ]);
        }

        // Create 1 dropped trainee
        $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
        ActivityEnrollment::factory()->create([
            'activity_id' => $activity->id,
            'trainee_id' => $trainee->id,
            'enrollment_status' => 'dropped',
        ]);

        $this->assertEquals(3, $activity->active_enrollments_count);
    }

    public function test_total_sessions_count_returns_correct_count(): void
    {
        $activity = Activity::factory()->create();

        ActivitySession::factory()->count(5)->create(['activity_id' => $activity->id]);

        $this->assertEquals(5, $activity->total_sessions_count);
    }

    public function test_completion_rate_calculates_correctly(): void
    {
        $activity = Activity::factory()->create();

        // Create 4 total sessions, 3 completed
        ActivitySession::factory()->count(3)->create([
            'activity_id' => $activity->id,
            'session_status' => 'completed',
        ]);

        ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'session_status' => 'scheduled',
        ]);

        $this->assertEquals(75.0, $activity->completion_rate);
    }

    public function test_completion_rate_returns_zero_when_no_sessions(): void
    {
        $activity = Activity::factory()->create();

        $this->assertEquals(0, $activity->completion_rate);
    }

    // ========================================
    // Casts Tests
    // ========================================

    public function test_is_active_casts_to_boolean(): void
    {
        $activity = Activity::factory()->create(['is_active' => 1]);

        $this->assertIsBool($activity->is_active);
        $this->assertTrue($activity->is_active);
    }

    public function test_numeric_fields_cast_to_integer(): void
    {
        $activity = Activity::factory()->create([
            'duration_weeks' => '8',
            'sessions_per_week' => '2',
            'session_duration_minutes' => '60',
            'max_participants' => '15',
        ]);

        $this->assertIsInt($activity->duration_weeks);
        $this->assertIsInt($activity->sessions_per_week);
        $this->assertIsInt($activity->session_duration_minutes);
        $this->assertIsInt($activity->max_participants);
    }
}
