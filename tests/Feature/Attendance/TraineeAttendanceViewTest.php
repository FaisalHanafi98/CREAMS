<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\Centre;
use App\Models\Trainee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for D-08.
 *
 * GET /attendance/trainee/{id} previously returned HTTP 500 because the route closure
 * passed only the id, while the view requires a populated trainee plus the attendance
 * summary, calendar grid, and timeline. These tests pin the working 200 path and the
 * PDPA centre-isolation 404 a teacher must receive for a trainee outside their centre.
 */
class TraineeAttendanceViewTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
        Centre::firstOrCreate(
            ['centre_id' => '02'],
            ['centre_name' => 'Centre Beta', 'centre_phone' => '+60100000002', 'centre_email' => 'beta@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    public function test_trainee_attendance_page_loads_for_admin(): void
    {
        $trainee = Trainee::factory()->create(['centre_id' => '01']);

        $response = $this->actingAsAdmin('01')
            ->get(route('attendance.trainee', ['id' => $trainee->id]));

        $response->assertStatus(200);
        $response->assertSee($trainee->trainee_first_name);
    }

    public function test_trainee_attendance_page_renders_with_records(): void
    {
        $trainee = Trainee::factory()->create(['centre_id' => '01']);
        Attendance::factory()->count(3)->create([
            'trainee_id' => $trainee->id,
            'attendance_date' => now()->toDateString(),
        ]);

        $response = $this->actingAsAdmin('01')
            ->get(route('attendance.trainee', ['id' => $trainee->id]));

        $response->assertStatus(200);
    }

    public function test_teacher_cannot_view_trainee_from_other_centre(): void
    {
        // Trainee belongs to centre 01 (created with no active session, so it persists
        // with a real id). The teacher is then logged into centre 02. CentreScope hides
        // the record from that session, so findOrFail must surface a 404, not a leak.
        $trainee = Trainee::factory()->create(['centre_id' => '01']);

        $this->assertNotNull($trainee->id, 'Trainee must persist with an id for a meaningful test');

        $response = $this->actingAsTeacher('02')
            ->get(route('attendance.trainee', ['id' => $trainee->id]));

        $response->assertStatus(404);
    }
}
