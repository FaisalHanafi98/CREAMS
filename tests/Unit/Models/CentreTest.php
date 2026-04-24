<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Centre;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;

class CentreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test centres
        Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Test Centre 1',
                'centre_phone' => '+60123456789',
                'centre_email' => 'test1@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );
    }

    // ========================================
    // Relationship Tests
    // ========================================

    public function test_users_relationship_returns_has_many(): void
    {
        $centre = Centre::find('01');

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $centre->users()
        );
    }

    public function test_trainees_relationship_returns_has_many(): void
    {
        $centre = Centre::find('01');

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $centre->trainees()
        );
    }

    public function test_activities_relationship_returns_has_many(): void
    {
        $centre = Centre::find('01');

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $centre->activities()
        );
    }

    public function test_users_relationship_retrieves_correct_users(): void
    {
        $centre = Centre::find('01');
        $user = User::factory()->create(['centre_id' => '01']);

        $centreUsers = $centre->users;

        $this->assertTrue($centreUsers->contains($user));
    }

    public function test_trainees_relationship_retrieves_correct_trainees(): void
    {
        $centre = Centre::find('01');
        $trainee = Trainee::factory()->create(['centre_id' => '01']);

        $centreTrainees = $centre->trainees;

        $this->assertTrue($centreTrainees->contains($trainee));
    }

    // ========================================
    // Scope Tests
    // ========================================

    public function test_scope_active_returns_only_active_centres(): void
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

        Centre::firstOrCreate(
            ['centre_id' => '03'],
            [
                'centre_name' => 'Test Centre 3',
                'centre_phone' => '+60123456791',
                'centre_email' => 'test3@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'inactive',
                'is_active' => false,
            ]
        );

        $activeCentres = Centre::active()->get();

        $this->assertGreaterThanOrEqual(2, $activeCentres->count());
        $this->assertTrue($activeCentres->every(fn($c) => $c->is_active === true));
    }

    // ========================================
    // Static Method Tests
    // ========================================

    public function test_get_for_dropdown_returns_array(): void
    {
        $dropdown = Centre::getForDropdown();

        $this->assertIsArray($dropdown);
    }

    public function test_get_for_dropdown_returns_key_value_pairs(): void
    {
        $dropdown = Centre::getForDropdown();

        $this->assertNotEmpty($dropdown);
        $this->assertArrayHasKey('01', $dropdown);
        $this->assertIsString($dropdown['01']);
    }

    public function test_get_for_dropdown_filters_active_centres(): void
    {
        Centre::firstOrCreate(
            ['centre_id' => '99'],
            [
                'centre_name' => 'Inactive Centre',
                'centre_phone' => '+60123456799',
                'centre_email' => 'inactive@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'inactive',
                'is_active' => false,
            ]
        );

        $dropdownActive = Centre::getForDropdown(true);
        $dropdownAll = Centre::getForDropdown(false);

        $this->assertArrayNotHasKey('99', $dropdownActive);
        $this->assertArrayHasKey('99', $dropdownAll);
    }

    public function test_get_default_returns_centre_01(): void
    {
        $default = Centre::getDefault();

        $this->assertNotNull($default);
        $this->assertEquals('01', $default->centre_id);
    }

    // ========================================
    // Accessor Tests
    // ========================================

    public function test_get_name_attribute_returns_centre_name(): void
    {
        $centre = Centre::find('01');

        $this->assertEquals($centre->centre_name, $centre->name);
    }

    // ========================================
    // Attendance Policy Tests
    // ========================================

    public function test_get_default_attendance_policies_returns_array(): void
    {
        $centre = Centre::find('01');
        $policies = $centre->getDefaultAttendancePolicies();

        $this->assertIsArray($policies);
        $this->assertArrayHasKey('office_hours', $policies);
        $this->assertArrayHasKey('work_days', $policies);
        $this->assertArrayHasKey('attendance_rules', $policies);
        $this->assertArrayHasKey('leave_policies', $policies);
    }

    public function test_get_attendance_policies_merges_with_defaults(): void
    {
        $centre = Centre::find('01');
        $policies = $centre->getAttendancePolicies();

        $this->assertIsArray($policies);
        $this->assertArrayHasKey('office_hours', $policies);
    }

    public function test_is_late_check_in_detects_late_arrival(): void
    {
        $centre = Centre::find('01');

        // Office starts at 08:00, late threshold is 15 minutes
        // Check-in at 08:20 should be late
        $isLate = $centre->isLateCheckIn('2026-01-15 08:20:00');

        $this->assertTrue($isLate);
    }

    public function test_is_late_check_in_allows_on_time_arrival(): void
    {
        $centre = Centre::find('01');

        // Check-in at 08:10 should NOT be late (within 15-minute threshold)
        $isLate = $centre->isLateCheckIn('2026-01-15 08:10:00');

        $this->assertFalse($isLate);
    }

    public function test_is_early_check_out_detects_early_departure(): void
    {
        $centre = Centre::find('01');

        // Office ends at 17:00, early threshold is 30 minutes
        // Check-out at 16:15 should be early
        $isEarly = $centre->isEarlyCheckOut('2026-01-15 16:15:00');

        $this->assertTrue($isEarly);
    }

    public function test_is_early_check_out_allows_normal_departure(): void
    {
        $centre = Centre::find('01');

        // Check-out at 16:40 should NOT be early (within 30-minute threshold)
        $isEarly = $centre->isEarlyCheckOut('2026-01-15 16:40:00');

        $this->assertFalse($isEarly);
    }

    public function test_is_work_day_returns_true_for_weekdays(): void
    {
        $centre = Centre::find('01');

        // Monday (2026-01-12)
        $isWorkDay = $centre->isWorkDay('2026-01-12');

        $this->assertTrue($isWorkDay);
    }

    public function test_is_work_day_returns_false_for_weekends(): void
    {
        $centre = Centre::find('01');

        // Saturday (2026-01-10)
        $isWorkDay = $centre->isWorkDay('2026-01-10');

        $this->assertFalse($isWorkDay);
    }

    // ========================================
    // Casts Tests
    // ========================================

    public function test_is_active_casts_to_boolean(): void
    {
        $centre = Centre::find('01');

        $this->assertIsBool($centre->is_active);
    }

    public function test_centre_facilities_stores_and_retrieves(): void
    {
        $centre = Centre::find('01');
        $centre->centre_facilities = '["Therapy Room", "Gym"]';
        $centre->save();

        $centre = $centre->fresh();

        $this->assertNotNull($centre->centre_facilities);
        $this->assertStringContainsString('Therapy Room', $centre->centre_facilities);
    }

    // ========================================
    // Primary Key Tests
    // ========================================

    public function test_primary_key_is_centre_id(): void
    {
        $centre = new Centre();

        $this->assertEquals('centre_id', $centre->getKeyName());
    }

    public function test_primary_key_is_string_type(): void
    {
        $centre = new Centre();

        $this->assertEquals('string', $centre->getKeyType());
    }

    public function test_primary_key_is_not_auto_incrementing(): void
    {
        $centre = new Centre();

        $this->assertFalse($centre->incrementing);
    }
}
