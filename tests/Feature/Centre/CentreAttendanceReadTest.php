<?php

namespace Tests\Feature\Centre;

use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
use App\Models\Attendance;
use App\Models\Centre;
use App\Models\Trainee;
use App\Models\User;
use Tests\TestCase;

class CentreAttendanceReadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 4)->setTime(17, 0));
        foreach (['01', '02'] as $centreId) {
            Centre::factory()->create(['centre_id' => $centreId, 'centre_name' => 'Synthetic Attendance Centre '.$centreId, 'centre_email' => 'attendance-'.$centreId.'@example.test']);
        }
    }

    public function test_weekly_rate_uses_due_trainee_opportunities_and_discloses_unmarked_rows(): void
    {
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic weekly attendance', 'category' => 'Learning Support']);
        $session = ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic due session', 'session_date' => today(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'completed']);
        foreach (['present', 'late', 'excused', null] as $index => $status) {
            $trainee = Trainee::factory()->create(['centre_id' => '01', 'trainee_first_name' => 'Synthetic', 'trainee_last_name' => 'Weekly '.$index, 'ic_number' => 'SYNTHETIC'.sprintf('%03d', $index)]);
            ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $trainee->id, 'enrollment_date' => today(), 'enrollment_status' => 'enrolled']);
            if ($status !== null) {
                Attendance::create(['activity_id' => $activity->id, 'session_id' => $session->id, 'trainee_id' => $trainee->id, 'attendance_date' => today(), 'status' => $status, 'marked_by_user_id' => $teacher->id]);
            }
        }
        $future = Trainee::factory()->create(['centre_id' => '01']);
        ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $future->id, 'enrollment_date' => today()->addDay(), 'enrollment_status' => 'enrolled']);
        ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic future session', 'session_date' => today()->addDay(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'scheduled']);
        ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic cancelled session', 'session_date' => today(), 'start_time' => '11:00:00', 'end_time' => '12:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'cancelled']);

        $this->actingAs($teacher)->get('/centre/attendance?date=2026-11-04')
            ->assertOk()
            ->assertViewHas('analytics', function ($analytics) {
                $this->assertSame(4, $analytics['expected']);
                $this->assertSame(3, $analytics['recorded']);
                $this->assertSame(1, $analytics['unmarked']);
                $this->assertSame(50.0, $analytics['weekly_attendance_rate']);
                $this->assertSame(75.0, $analytics['recording_coverage']);

                return true;
            })
            ->assertViewHas('attendanceOverview', fn ($overview) => $overview['present_today'] === 2)
            ->assertSee('1 unmarked.')
            ->assertSee('Cancelled');
        $this->assertDatabaseCount('trainee_attendances', 3);
    }

    public function test_empty_and_denied_attendance_states_are_explicit(): void
    {
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $this->actingAs($teacher)->get('/centre/attendance?date=2026-11-04')
            ->assertOk()->assertSee('No Sessions Scheduled')->assertSee('No eligible sessions');
        $this->getJson('/centre/attendance?date=invalid')->assertUnprocessable();
        $this->get('/centre/attendance?centre_id=02')->assertForbidden();
    }

    public function test_admin_can_select_a_centre_but_ajk_and_guests_cannot_enter_attendance(): void
    {
        $this->get('/centre/attendance')->assertRedirect(route('auth.loginpage'));
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $this->actingAs($admin)->get('/centre/attendance?centre_id=02')
            ->assertOk()->assertViewHas('centre', fn ($centre) => $centre->centre_id === '02');

        $ajk = User::factory()->ajk()->create(['centre_id' => '01']);
        $this->actingAs($ajk)->getJson('/centre/attendance')->assertForbidden();
    }

    public function test_admin_summary_excludes_foreign_deleted_and_ended_roster_records(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic roster controls', 'category' => 'Learning Support']);
        ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic roster session', 'session_date' => today(), 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'session_status' => 'completed']);
        foreach ([
            ['01', null, 'enrolled', null],
            ['02', null, 'enrolled', null],
            ['01', now(), 'enrolled', null],
            ['01', null, 'completed', today()->subDay()],
        ] as $index => [$centreId, $deletedAt, $status, $completedAt]) {
            $trainee = Trainee::factory()->create(['centre_id' => $centreId, 'deleted_at' => $deletedAt, 'trainee_id' => 'SYN-ROSTER-'.$index]);
            ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $trainee->id, 'enrollment_date' => today()->subWeek(), 'enrollment_status' => $status, 'completion_date' => $completedAt]);
        }

        $this->actingAs($admin)->get('/centre/attendance?date=2026-11-04')
            ->assertOk()
            ->assertViewHas('activitySessions', fn ($sessions) => $sessions->first()->enrollments->count() === 1)
            ->assertViewHas('analytics', fn ($analytics) => $analytics['expected'] === 1 && $analytics['unmarked'] === 1);
    }

    public function test_dashboard_reads_real_session_attendance_without_legacy_tables(): void
    {
        $teacher = User::factory()->teacher()->create(['centre_id' => '01']);
        $activity = Activity::create(['centre_id' => '01', 'activity_name' => 'Synthetic attendance programme', 'category' => 'Learning Support', 'instructor_id' => $teacher->id]);
        $session = ActivitySession::create([
            'activity_id' => $activity->id, 'session_date' => today(), 'session_name' => 'Synthetic attendance session',
            'start_time' => '10:00:00', 'end_time' => '11:00:00', 'instructor_id' => $teacher->id, 'session_status' => 'completed',
        ]);
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'trainee_first_name' => 'Synthetic', 'trainee_last_name' => 'Registered Trainee', 'ic_number' => 'SYNTHETIC001']);
        ActivityEnrollment::create(['activity_id' => $activity->id, 'trainee_id' => $trainee->id, 'enrollment_date' => today(), 'enrollment_status' => 'enrolled']);
        Attendance::create(['activity_id' => $activity->id, 'session_id' => $session->id, 'trainee_id' => $trainee->id, 'attendance_date' => today(), 'status' => 'present', 'marked_by_user_id' => $teacher->id, 'marked_at' => now()]);
        Activity::create(['centre_id' => '02', 'activity_name' => 'Synthetic forbidden attendance programme', 'category' => 'Learning Support']);

        $this->actingAs($teacher)->get('/centre/attendance?date=2026-11-04')
            ->assertOk()
            ->assertViewHas('activitySessions', fn ($sessions) => $sessions->pluck('id')->all() === [$session->id])
            ->assertViewHas('attendanceOverview', fn ($overview) => $overview['present_today'] === 1)
            ->assertViewHas('analytics', fn ($analytics) => $analytics['weekly_attendance_rate'] === 100.0)
            ->assertSee('Synthetic attendance programme')
            ->assertSee('Learning Support')
            ->assertDontSee('Synthetic forbidden attendance programme');
    }
}
