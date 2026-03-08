<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Trainee;
use App\Models\Centre;
use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\Attendance;
use App\Models\TraineeEducationPlan;

class TraineeTest extends TestCase
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
    // Accessor Tests
    // ========================================

    public function test_get_full_name_attribute_combines_first_and_last_name(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_first_name' => 'Ahmad',
            'trainee_last_name' => 'Ibrahim',
        ]);

        $this->assertEquals('Ahmad Ibrahim', $trainee->full_name);
    }

    public function test_get_age_attribute_calculates_from_date_of_birth(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_date_of_birth' => now()->subYears(10),
        ]);

        $this->assertEquals(10, $trainee->age);
    }

    // Note: test_get_age_attribute_returns_null_when_no_dob removed —
    // trainee_date_of_birth is NOT NULL in schema, null DOB is impossible.

    // Note: test_get_avatar_url_attribute_returns_default_when_no_avatar removed —
    // avatar/trainee_avatar column does not exist in current schema.

    public function test_get_condition_badge_class_attribute_returns_correct_class(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_condition' => 'Physical Disabilities',
        ]);

        $this->assertEquals('primary', $trainee->condition_badge_class);
    }

    public function test_get_condition_badge_class_attribute_returns_default_for_unknown(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_condition' => 'Unknown Condition',
        ]);

        $this->assertEquals('secondary', $trainee->condition_badge_class);
    }

    // ========================================
    // Phone Number Tests
    // ========================================

    public function test_set_trainee_phone_number_normalizes_phone(): void
    {
        $trainee = Trainee::factory()->make();
        $trainee->trainee_phone_number = '012-345 6789';

        $this->assertStringContainsString('601234', $trainee->trainee_phone_number);
    }

    public function test_set_guardian_phone_normalizes_phone(): void
    {
        $trainee = Trainee::factory()->make();
        $trainee->guardian_phone = '012-345 6789';

        $this->assertStringContainsString('601234', $trainee->guardian_phone);
    }

    public function test_get_trainee_phone_formatted_attribute_formats_phone(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_phone_number' => '+60123456789',
        ]);

        $formatted = $trainee->trainee_phone_formatted;

        $this->assertNotEmpty($formatted);
    }

    // ========================================
    // Relationship Tests
    // ========================================

    public function test_centre_relationship_returns_belongs_to(): void
    {
        $trainee = Trainee::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $trainee->centre()
        );
    }

    public function test_centre_relationship_retrieves_correct_centre(): void
    {
        $centre = Centre::where('centre_id', '01')->first();
        $trainee = Trainee::factory()->create(['centre_id' => '01']);

        $this->assertEquals($centre->id, $trainee->centre->id);
    }

    public function test_activities_relationship_returns_belongs_to_many(): void
    {
        $trainee = Trainee::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsToMany::class,
            $trainee->activities()
        );
    }

    public function test_enrollments_relationship_returns_has_many(): void
    {
        $trainee = Trainee::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $trainee->enrollments()
        );
    }

    public function test_attendances_relationship_returns_has_many(): void
    {
        $trainee = Trainee::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $trainee->attendances()
        );
    }

    public function test_education_plans_relationship_returns_has_many(): void
    {
        $trainee = Trainee::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $trainee->educationPlans()
        );
    }

    // ========================================
    // Scope Tests
    // ========================================

    public function test_scope_active_returns_only_active_trainees(): void
    {
        Trainee::factory()->create(['status' => 'active']);
        Trainee::factory()->create(['status' => 'inactive']);

        $activeTrainees = Trainee::active()->get();

        $this->assertCount(1, $activeTrainees);
        $this->assertEquals('active', $activeTrainees->first()->status);
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

        Trainee::factory()->create([
            'centre_id' => '01',
            'centre_name' => 'Test Centre',
        ]);

        Trainee::factory()->create([
            'centre_id' => '02',
            'centre_name' => 'Test Centre 2',
        ]);

        $centreTrainees = Trainee::byCentre('Test Centre')->get();

        $this->assertCount(1, $centreTrainees);
        $this->assertEquals('Test Centre', $centreTrainees->first()->centre_name);
    }

    public function test_scope_by_condition_filters_correctly(): void
    {
        Trainee::factory()->create(['trainee_condition' => 'Autism Spectrum Disorder']);
        Trainee::factory()->create(['trainee_condition' => 'Down Syndrome']);

        $autismTrainees = Trainee::byCondition('Autism Spectrum Disorder')->get();

        $this->assertCount(1, $autismTrainees);
        $this->assertEquals('Autism Spectrum Disorder', $autismTrainees->first()->trainee_condition);
    }

    // ========================================
    // Progress Calculation Tests
    // ========================================

    public function test_calculate_activity_progress_returns_zero_when_no_sessions(): void
    {
        $trainee = Trainee::factory()->create();
        $activity = Activity::factory()->create();

        $progress = $trainee->calculateActivityProgress($activity);

        $this->assertEquals(0, $progress);
    }

    public function test_calculate_activity_progress_calculates_correctly(): void
    {
        $trainee = Trainee::factory()->create();
        $activity = Activity::factory()->create();

        // Create 4 attendance records: 3 present, 1 absent
        Attendance::factory()->count(3)->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'status' => 'absent',
        ]);

        $progress = $trainee->calculateActivityProgress($activity);

        // 3 present out of 4 total = 75%
        $this->assertEquals(75.0, $progress);
    }

    public function test_calculate_activity_progress_includes_late_as_attended(): void
    {
        $trainee = Trainee::factory()->create();
        $activity = Activity::factory()->create();

        // Create attendance: 2 present, 1 late, 1 absent
        Attendance::factory()->count(2)->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'status' => 'present',
        ]);

        Attendance::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'status' => 'late',
        ]);

        Attendance::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'status' => 'absent',
        ]);

        $progress = $trainee->calculateActivityProgress($activity);

        // 3 attended (2 present + 1 late) out of 4 total = 75%
        $this->assertEquals(75.0, $progress);
    }

    public function test_calculate_average_progress_returns_zero_when_no_activities(): void
    {
        $trainee = Trainee::factory()->create();

        $averageProgress = $trainee->calculateAverageProgress();

        $this->assertEquals(0, $averageProgress);
    }

    public function test_calculate_average_progress_calculates_correctly(): void
    {
        $trainee = Trainee::factory()->create();

        // Create 2 activities with enrollments
        $activity1 = Activity::factory()->create();
        ActivityEnrollment::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity1->id,
            'enrollment_status' => 'enrolled',
        ]);

        $activity2 = Activity::factory()->create();
        ActivityEnrollment::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity2->id,
            'enrollment_status' => 'enrolled',
        ]);

        // Add attendance for activity 1: 3/4 = 75%
        Attendance::factory()->count(3)->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity1->id,
            'status' => 'present',
        ]);
        Attendance::factory()->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity1->id,
            'status' => 'absent',
        ]);

        // Add attendance for activity 2: 2/4 = 50%
        Attendance::factory()->count(2)->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity2->id,
            'status' => 'present',
        ]);
        Attendance::factory()->count(2)->create([
            'trainee_id' => $trainee->id,
            'activity_id' => $activity2->id,
            'status' => 'absent',
        ]);

        $averageProgress = $trainee->calculateAverageProgress();

        // Average of 75% and 50% = 62.5%
        $this->assertEquals(62.5, $averageProgress);
    }

    // ========================================
    // Casts Tests
    // ========================================

    public function test_trainee_date_of_birth_casts_to_date(): void
    {
        $trainee = Trainee::factory()->create([
            'trainee_date_of_birth' => '2010-05-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $trainee->trainee_date_of_birth);
        $this->assertEquals('2010-05-15', $trainee->trainee_date_of_birth->format('Y-m-d'));
    }

    public function test_boolean_consent_fields_cast_correctly(): void
    {
        $trainee = Trainee::factory()->create([
            'photo_consent' => 1,
            'services_consent' => 0,
            'data_consent' => 1,
        ]);

        $this->assertIsBool($trainee->photo_consent);
        $this->assertIsBool($trainee->services_consent);
        $this->assertIsBool($trainee->data_consent);
        $this->assertTrue($trainee->photo_consent);
        $this->assertFalse($trainee->services_consent);
        $this->assertTrue($trainee->data_consent);
    }

    // Note: test_array_fields_cast_correctly removed —
    // medical_info and tags columns do not exist in current schema.

    // ========================================
    // Hidden Attributes Tests
    // ========================================

    public function test_ic_number_is_hidden_from_array(): void
    {
        $trainee = Trainee::factory()->create([
            'ic_number' => '123456789012',
        ]);

        $array = $trainee->toArray();

        $this->assertArrayNotHasKey('ic_number', $array);
    }

    // Note: test_medical_info_is_hidden_from_array removed —
    // medical_info column does not exist in current schema.
}
