<?php

namespace Tests\Feature\RBAC;

use App\Models\ActivityLog;
use App\Models\ActivityScheduleTemplate;
use App\Models\Centre;
use App\Models\Letter;
use App\Models\LetterTemplate;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\Volunteer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Verifies that CentreScope correctly isolates data per centre.
 * For each model with centre_id, data from Centre B must be invisible
 * when queried in the context of a Centre A session.
 *
 * Tables tested: staffs, staff_attendances, letters, letter_templates,
 * activity_logs, activity_schedule_templates, volunteers.
 *
 * Tables skipped (no migration / table doesn't exist in test DB):
 * message_templates, centre_statistics, centre_audit_logs.
 */
class CentreScopeIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private Centre $centreA;
    private Centre $centreB;
    private User $userA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centreA = Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );

        $this->centreB = Centre::firstOrCreate(
            ['centre_id' => '02'],
            ['centre_name' => 'Centre Beta', 'centre_phone' => '+60100000002', 'centre_email' => 'beta@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );

        $this->userA = User::factory()->teacher()->create([
            'centre_id' => '01',
        ]);

        // Simulate Centre A teacher session
        session([
            'id' => $this->userA->id,
            'role' => 'teacher',
            'centre_id' => '01',
            'logged_in' => true,
        ]);
    }

    // ---- User / Staff model ----

    public function test_user_scoped_to_centre(): void
    {
        $userB = User::factory()->teacher()->create(['centre_id' => '02']);

        $visible = User::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)
            ->where('id', $userB->id)->exists();
        $this->assertTrue($visible, 'User B exists in DB');

        $scoped = User::where('id', $userB->id)->exists();
        $this->assertFalse($scoped, 'Centre A session must not see Centre B user');
    }

    // ---- StaffAttendance ----

    public function test_staff_attendance_scoped_to_centre(): void
    {
        $recordA = StaffAttendance::create([
            'user_id' => $this->userA->id,
            'centre_id' => '01',
            'attendance_date' => now()->toDateString(),
            'status' => 'present',
        ]);

        $recordB = StaffAttendance::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'user_id' => $this->userA->id,
            'centre_id' => '02',
            'attendance_date' => now()->subDay()->toDateString(),
            'status' => 'present',
        ]);

        $results = StaffAttendance::all();
        $this->assertTrue($results->contains('id', $recordA->id));
        $this->assertFalse($results->contains('id', $recordB->id));
    }

    // ---- Letter ----

    public function test_letter_scoped_to_centre(): void
    {
        $letterA = Letter::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'letter_name' => 'Test Letter A',
            'letter_type' => 'general',
            'centre_id' => '01',
            'status' => 'draft',
            'created_by' => $this->userA->id,
            'recipient_id' => 0,
        ]);

        $letterB = Letter::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'letter_name' => 'Test Letter B',
            'letter_type' => 'general',
            'centre_id' => '02',
            'status' => 'draft',
            'created_by' => $this->userA->id,
            'recipient_id' => 0,
        ]);

        $results = Letter::all();
        $this->assertTrue($results->contains('id', $letterA->id));
        $this->assertFalse($results->contains('id', $letterB->id));
    }

    // ---- LetterTemplate ----

    public function test_letter_template_scoped_to_centre(): void
    {
        $tplA = LetterTemplate::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'template_name' => 'Template A',
            'template_content' => 'Content A',
            'template_type' => 'general',
            'centre_id' => '01',
            'is_active' => true,
            'created_by' => $this->userA->id,
        ]);

        $tplB = LetterTemplate::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'template_name' => 'Template B',
            'template_content' => 'Content B',
            'template_type' => 'general',
            'centre_id' => '02',
            'is_active' => true,
            'created_by' => $this->userA->id,
        ]);

        $results = LetterTemplate::all();
        $this->assertTrue($results->contains('id', $tplA->id));
        $this->assertFalse($results->contains('id', $tplB->id));
    }

    // ---- ActivityLog ----

    public function test_activity_log_scoped_to_centre(): void
    {
        $logA = ActivityLog::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'centre_id' => '01',
            'user_id' => $this->userA->id,
            'user_name' => 'Test User',
            'user_role' => 'teacher',
            'action_type' => 'create',
            'model_type' => 'App\\Models\\User',
            'title' => 'Test A',
        ]);

        $logB = ActivityLog::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'centre_id' => '02',
            'user_id' => $this->userA->id,
            'user_name' => 'Test User',
            'user_role' => 'teacher',
            'action_type' => 'create',
            'model_type' => 'App\\Models\\User',
            'title' => 'Test B',
        ]);

        $results = ActivityLog::all();
        $this->assertTrue($results->contains('id', $logA->id));
        $this->assertFalse($results->contains('id', $logB->id));
    }

    // ---- ActivityScheduleTemplate ----

    public function test_activity_schedule_template_scoped_to_centre(): void
    {
        $astA = ActivityScheduleTemplate::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'template_name' => 'Schedule A',
            'centre_id' => '01',
            'sessions_per_week' => 3,
            'duration_weeks' => 4,
            'session_length_minutes' => 60,
            'days_of_week' => json_encode(['Monday', 'Wednesday', 'Friday']),
            'time_slots' => json_encode(['09:00', '14:00']),
            'template_type' => 'weekly',
            'is_active' => true,
        ]);

        $astB = ActivityScheduleTemplate::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'template_name' => 'Schedule B',
            'centre_id' => '02',
            'sessions_per_week' => 3,
            'duration_weeks' => 4,
            'session_length_minutes' => 60,
            'days_of_week' => json_encode(['Tuesday', 'Thursday']),
            'time_slots' => json_encode(['10:00']),
            'template_type' => 'weekly',
            'is_active' => true,
        ]);

        $results = ActivityScheduleTemplate::all();
        $this->assertTrue($results->contains('id', $astA->id));
        $this->assertFalse($results->contains('id', $astB->id));
    }

    // ---- Volunteer ----

    public function test_volunteer_scoped_to_centre(): void
    {
        $volA = Volunteer::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'volunteer_id' => 'VOL-' . Str::random(8),
            'name' => 'Volunteer A',
            'email' => 'vola' . Str::random(4) . '@test.com',
            'phone' => '+60111111111',
            'centre_id' => '01',
            'status' => 'applied',
        ]);

        $volB = Volunteer::withoutGlobalScope(\App\Models\Scopes\CentreScope::class)->create([
            'volunteer_id' => 'VOL-' . Str::random(8),
            'name' => 'Volunteer B',
            'email' => 'volb' . Str::random(4) . '@test.com',
            'phone' => '+60222222222',
            'centre_id' => '02',
            'status' => 'applied',
        ]);

        $results = Volunteer::all();
        $this->assertTrue($results->contains('id', $volA->id));
        $this->assertFalse($results->contains('id', $volB->id));
    }

    // ---- Admin sees all centres ----

    public function test_admin_sees_all_centres(): void
    {
        // Switch session to admin
        session(['role' => 'admin', 'centre_id' => '01']);

        User::factory()->teacher()->create(['centre_id' => '01']);
        User::factory()->teacher()->create(['centre_id' => '02']);

        $results = User::all();
        $centreIds = $results->pluck('centre_id')->unique()->toArray();
        $this->assertContains('01', $centreIds);
        $this->assertContains('02', $centreIds);
    }

    // ---- No session = no filtering ----

    public function test_no_session_returns_all_data(): void
    {
        session()->flush();

        User::factory()->teacher()->create(['centre_id' => '01']);
        User::factory()->teacher()->create(['centre_id' => '02']);

        $results = User::all();
        $centreIds = $results->pluck('centre_id')->unique()->toArray();
        $this->assertContains('01', $centreIds);
        $this->assertContains('02', $centreIds);
    }
}
