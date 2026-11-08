<?php

namespace Tests\Feature\Centre;

use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\Trainee;
use App\Models\User;
use Tests\TestCase;

class CentreAttendanceMarkTest extends TestCase
{
    public function test_cancelled_session_does_not_accept_a_valid_roster_mark(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 4)->setTime(10, 0));
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Cancelled Session Centre', 'centre_email' => 'cancelled-centre@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'category' => 'Learning Support', 'activity_name' => 'Synthetic cancelled programme']);
        $session = ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic cancelled marking', 'session_date' => today(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'cancelled']);
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'trainee_id' => 'SYN-CANCEL-001']);
        ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $trainee->id, 'enrollment_date' => today(), 'enrollment_status' => 'enrolled']);

        $this->actingAs($teacher)->postJson('/centre/attendance/session/'.$session->id.'/store', [
            'attendance' => [['trainee_id' => $trainee->id, 'status' => 'present']],
        ])->assertUnprocessable()->assertJsonPath('message', 'This session was cancelled.');
        $this->assertDatabaseMissing('trainee_attendances', ['session_id' => $session->id]);
        $this->assertSame('cancelled', $session->fresh()->session_status);
    }

    public function test_unenrolled_trainee_cannot_be_marked_even_in_the_same_centre(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 4)->setTime(10, 0));
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Roster Validation Centre', 'centre_email' => 'roster-validation@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'category' => 'Learning Support', 'activity_name' => 'Synthetic roster validation programme']);
        $session = ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic roster validation session', 'session_date' => today(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'ongoing']);
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'trainee_id' => 'SYN-NOROSTER-001']);

        $this->actingAs($teacher)->postJson('/centre/attendance/session/'.$session->id.'/store', [
            'attendance' => [['trainee_id' => $trainee->id, 'status' => 'present']],
        ])->assertUnprocessable()->assertJsonValidationErrors('attendance');
        $this->assertDatabaseMissing('trainee_attendances', ['session_id' => $session->id, 'trainee_id' => $trainee->id]);
        $this->assertSame('ongoing', $session->fresh()->session_status);
    }

    public function test_assigned_teacher_opens_the_current_roster_without_legacy_relationships(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 4)->setTime(10, 0));
        Centre::factory()->create(['centre_id' => '01', 'centre_name' => 'Synthetic Marking Centre', 'centre_email' => 'marking@example.test']);
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'category' => 'Learning Support', 'activity_name' => 'Synthetic marking programme']);
        $session = ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic marking session', 'session_date' => today(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'ongoing']);
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'trainee_first_name' => 'Synthetic', 'trainee_last_name' => 'Marking Learner', 'trainee_id' => 'SYN-MARK-001', 'ic_number' => 'SYNTHETIC001']);
        ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $trainee->id, 'enrollment_date' => today(), 'enrollment_status' => 'enrolled']);

        $this->actingAs($teacher)->get('/centre/attendance/session/'.$session->id.'/mark')
            ->assertOk()
            ->assertSee('Synthetic Marking Learner')
            ->assertSee('name="attendance[0][status]"', false)
            ->assertSee('/centre/attendance/session/'.$session->id.'/store', false);
    }
}
