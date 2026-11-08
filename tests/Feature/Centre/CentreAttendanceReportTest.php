<?php

namespace Tests\Feature\Centre;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Attendance;
use App\Models\Centre;
use App\Models\Trainee;
use App\Models\User;
use Tests\TestCase;

class CentreAttendanceReportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(now()->setDate(2026, 11, 4)->setTime(13, 0));
        foreach (['01', '02'] as $centreId) {
            Centre::factory()->create(['centre_id' => $centreId, 'centre_name' => 'Synthetic Report Centre '.$centreId, 'centre_email' => 'report-'.$centreId.'@example.test']);
        }
    }

    public function test_supervisor_report_shows_only_real_marks_in_its_centre_and_period(): void
    {
        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);
        $this->record('01', 'In Range Learner', 'late', '2026-11-04', '01');
        $this->record('02', 'Foreign Learner', 'present', '2026-11-04', '02');
        $this->record('01', 'Earlier Learner', 'absent', '2026-11-03', '03');

        $this->actingAs($supervisor)->get('/centre/attendance/analytics?start_date=2026-11-04&end_date=2026-11-04')
            ->assertOk()
            ->assertSee('Recorded attendance')
            ->assertSee('Synthetic In Range Learner')
            ->assertSee('Late')
            ->assertDontSee('Synthetic Foreign Learner')
            ->assertDontSee('Synthetic Earlier Learner');
    }

    public function test_csv_and_legacy_excel_alias_export_only_safe_scoped_text(): void
    {
        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);
        $this->record('01', 'Formula Learner', 'late', '2026-11-04', '01', '=1+1');
        $this->record('02', 'Foreign Learner', 'present', '2026-11-04', '02');
        $this->actingAs($supervisor);
        $csv = $this->get('/centre/attendance/export?centre_id=01&start_date=2026-11-04&end_date=2026-11-04&format=csv')
            ->assertOk()->assertDownload('attendance-01-2026-11-04-2026-11-04.csv')->streamedContent();
        $legacy = $this->post('/activity-attendance/export', ['centre_id' => '01', 'date_from' => '2026-11-04', 'date_to' => '2026-11-04', 'format' => 'excel'])
            ->assertOk()->assertDownload('attendance-01-2026-11-04-2026-11-04.csv')->streamedContent();

        $this->assertSame($csv, $legacy);
        $rows = array_values(array_filter(explode("\n", trim($csv))));
        $this->assertCount(2, $rows);
        $fields = str_getcsv($rows[1], ',', '"', '');
        $this->assertSame('SYN-RPT-01', $fields[1]);
        $this->assertSame("'=1+1 Formula Learner", $fields[2]);
        $this->assertSame('Late', $fields[5]);
        $this->assertStringNotContainsString('Foreign Learner', $csv);
    }

    public function test_admin_can_download_a_real_pdf_for_a_selected_centre(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);
        $this->record('02', 'Selected Centre Learner', 'present', '2026-11-04', '02');
        $response = $this->actingAs($admin)->get('/centre/attendance/export?centre_id=02&start_date=2026-11-04&end_date=2026-11-04&format=pdf')
            ->assertOk()->assertDownload('attendance-02-2026-11-04-2026-11-04.pdf');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertGreaterThan(512, strlen($response->getContent()));
    }

    public function test_report_authorisation_covers_both_export_routes(): void
    {
        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);
        $this->actingAs($supervisor)->getJson('/centre/attendance/analytics?centre_id=02')->assertForbidden();
        $this->getJson('/centre/attendance/export?centre_id=02')->assertForbidden();
        $this->postJson('/activity-attendance/export', ['centre_id' => '02', 'format' => 'csv'])->assertForbidden();

        foreach (['teacher', 'ajk'] as $role) {
            $user = User::factory()->create(['role' => $role, 'centre_id' => '01']);
            $this->actingAs($user)->getJson('/centre/attendance/analytics')->assertForbidden();
            $this->getJson('/centre/attendance/export')->assertForbidden();
            $this->postJson('/activity-attendance/export', ['format' => 'csv'])->assertForbidden();
        }
    }

    public function test_empty_reports_and_invalid_periods_are_distinct(): void
    {
        $supervisor = User::factory()->supervisor()->create(['centre_id' => '01']);
        $this->actingAs($supervisor)->get('/centre/attendance/analytics?start_date=2026-11-04&end_date=2026-11-04')
            ->assertOk()->assertSee('No recorded attendance in this period.');
        $this->getJson('/centre/attendance/analytics?start_date=2026-11-04&end_date=2026-11-03')
            ->assertUnprocessable()->assertJsonValidationErrors('end_date');
        $this->getJson('/centre/attendance/export?format=xlsx')->assertUnprocessable()->assertJsonValidationErrors('format');
    }

    private function record(string $centreId, string $name, string $status, string $day, string $suffix, string $firstName = 'Synthetic'): void
    {
        $activity = Activity::create(['centre_id' => $centreId, 'activity_name' => 'Synthetic Report Programme '.$suffix, 'category' => 'Learning Support']);
        $session = ActivitySession::create(['activity_id' => $activity->id, 'session_name' => 'Synthetic Report Session '.$suffix, 'session_date' => $day, 'start_time' => '10:00:00', 'end_time' => '11:00:00', 'session_status' => 'completed']);
        $trainee = Trainee::factory()->create(['centre_id' => $centreId, 'trainee_first_name' => $firstName, 'trainee_last_name' => $name, 'trainee_id' => 'SYN-RPT-'.$suffix]);
        Attendance::create(['activity_id' => $activity->id, 'session_id' => $session->id, 'trainee_id' => $trainee->id, 'attendance_date' => $day, 'status' => $status]);
    }
}
