<?php

namespace Tests\Feature\Activity;

use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
use App\Models\Attendance;
use App\Models\Centre;
use App\Models\Trainee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for the SessionEnrollment → ActivityEnrollment migration.
 *
 * SessionEnrollment targets session_enrollments (no migration, table absent).
 * Fix: add sessionEnrollments() on ActivitySession pointing at ActivityEnrollment
 * filtered by enrollment_status=enrolled. All 15+ callers work without any schema change.
 */
class SessionEnrollmentMigrationTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00'));

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name'     => 'Centre Alpha',
                'centre_phone'    => '+60100000001',
                'centre_email'    => 'alpha@test.com',
                'centre_capacity' => 50,
                'centre_status'   => 'active',
                'is_active'       => true,
            ]
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ── Relationship unit tests ───────────────────────────────────────────────

    public function test_session_enrollments_returns_enrolled_activity_enrollments(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        ActivityEnrollment::factory()->create([
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_status' => 'enrolled',
        ]);

        $enrollments = $session->sessionEnrollments;

        $this->assertCount(1, $enrollments);
        $this->assertEquals($trainee->id, $enrollments->first()->trainee_id);
    }

    public function test_session_enrollments_excludes_non_enrolled(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        ActivityEnrollment::factory()->create([
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_status' => 'dropped',
        ]);

        $this->assertCount(0, $session->sessionEnrollments);
    }

    // ── Controller integration tests ─────────────────────────────────────────

    public function test_get_attendance_form_loads_enrolled_trainees(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        ActivityEnrollment::factory()->create([
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_status' => 'enrolled',
        ]);

        $response = $this->actingAsAdmin('01')
            ->get(route('activity-attendance.session.form', $session->id));

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
    }

    public function test_get_attendance_form_returns_422_when_no_trainees_enrolled(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);

        $response = $this->actingAsAdmin('01')
            ->get(route('activity-attendance.session.form', $session->id));

        $response->assertStatus(422);
    }

    public function test_add_enrollment_via_route_creates_activity_enrollment(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        $response = $this->actingAsAdmin('01')
            ->post(route('activities.enrollments.add', [$activity->id, $session->id]), [
                'trainee_id' => $trainee->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_enrollments', [
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_status' => 'enrolled',
        ]);
    }
}
