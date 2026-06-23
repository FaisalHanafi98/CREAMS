<?php

namespace Tests\Feature\Attendance;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\Trainee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for the attendance store() column-mismatch defect.
 *
 * store() wrote attendance_status/recorded_by/attendance_notes/participation_score to the
 * trainee_attendances table, none of which exist there. Mass assignment dropped them, so the
 * row persisted with the enum default 'absent' and null notes — diverging from the real status
 * recorded on session_enrollments and corrupting the trainee attendance history view.
 */
class AttendanceStoreTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    public function test_store_persists_submitted_status_to_trainee_attendances(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $session = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee = Trainee::factory()->create(['centre_id' => '01']);

        $response = $this->actingAsAdmin('01')->postJson(route('attendance.store'), [
            'session_id' => $session->id,
            'attendance' => [
                [
                    'trainee_id' => $trainee->id,
                    'status' => 'present',
                    'participation_score' => 8,
                    'progress_notes' => 'Engaged well in the session',
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        // The row must carry the submitted status and notes, not the 'absent' default.
        $this->assertDatabaseHas('trainee_attendances', [
            'trainee_id' => $trainee->id,
            'session_id' => $session->id,
            'status' => 'present',
            'notes' => 'Engaged well in the session',
        ]);
    }
}
