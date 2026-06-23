<?php

namespace Tests\Feature\Centre;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\Trainee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for Centre/AttendanceController::storeActivityAttendance() multi-failure.
 *
 * Four bugs existed simultaneously:
 *   1. SessionEnrollment::updateOrCreate() → session_enrollments table absent → exception
 *   2. DB::table('attendances')->updateOrInsert() → attendances table absent → exception
 *   3. TraineeCompetencyProgress::updateOrCreate() → table absent → exception
 *   4. redirect()->route('centre.enhanced-attendance.index') → route does not exist
 *
 * Fix: remove the SessionEnrollment write, replace the attendances write with
 * Attendance::updateOrCreate() on trainee_attendances, guard the competency block,
 * and correct the redirect route name to centre.attendance.index.
 */
class CentreAttendanceStoreTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-23 10:00:00')); // Tuesday — bypasses weekend guard

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

    public function test_store_activity_attendance_persists_to_trainee_attendances(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session  = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        $response = $this->actingAsAdmin('01')
            ->post(route('centre.attendance.store-session', $session->id), [
                'attendance' => [
                    [
                        'trainee_id' => $trainee->id,
                        'status'     => 'present',
                        'notes'      => 'Participated actively',
                    ],
                ],
            ]);

        $response->assertRedirect(route('centre.attendance.index'));

        $this->assertDatabaseHas('trainee_attendances', [
            'trainee_id' => $trainee->id,
            'session_id' => $session->id,
            'status'     => 'present',
            'notes'      => 'Participated actively',
        ]);
    }
}
