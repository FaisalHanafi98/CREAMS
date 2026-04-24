<?php

namespace Tests\Feature\Security;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Centre;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Verifies that AssetMaintenance records are isolated by centre.
 *
 * AssetMaintenance has no centre_id column. Isolation is enforced via
 * a global scope ('centre_isolation') in AssetMaintenance::booted()
 * that filters through the asset relationship: asset_maintenance.asset_id
 * → assets.centre_id = session('centre_id').
 *
 * @see app/Models/AssetMaintenance.php — centre_isolation global scope
 * @see docs/MULTI_CENTRE_ISOLATION.md — documented exception rationale
 */
class AssetMaintenanceCentreIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private Centre $centreA;
    private Centre $centreB;
    private User $userA;
    private Asset $assetA;
    private Asset $assetB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centreA = Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Centre Alpha',
                'centre_phone' => '+60100000001',
                'centre_email' => 'alpha@test.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $this->centreB = Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Centre Beta',
                'centre_phone' => '+60100000002',
                'centre_email' => 'beta@test.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $this->userA = User::factory()->teacher()->create(['centre_id' => '01']);

        // Override condition/status to match test DB ENUM values:
        // condition: enum('excellent','good','fair','poor','damaged','disposed')
        // status:    enum('available','in_use','maintenance','retired','missing')
        $this->assetA = Asset::factory()->create([
            'centre_id' => '01',
            'condition' => 'good',
            'status' => 'available',
        ]);
        $this->assetB = Asset::factory()->create([
            'centre_id' => '02',
            'condition' => 'good',
            'status' => 'available',
        ]);

        session([
            'id' => $this->userA->id,
            'role' => 'teacher',
            'centre_id' => '01',
            'logged_in' => true,
        ]);
    }

    // ---- Scope blocks cross-centre reads ----

    public function test_centre_a_user_cannot_see_centre_b_maintenance_records(): void
    {
        $maintenanceBId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetB->id,
            'maintenance_type' => 'corrective',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertFalse(
            AssetMaintenance::where('id', $maintenanceBId)->exists(),
            'Centre A teacher must not see Centre B maintenance records via scoped query'
        );
    }

    public function test_centre_b_record_exists_in_db_but_is_hidden_by_scope(): void
    {
        $maintenanceBId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetB->id,
            'maintenance_type' => 'preventive',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'low',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Scoped query: invisible
        $this->assertFalse(
            AssetMaintenance::where('id', $maintenanceBId)->exists(),
            'Scoped query must not return Centre B record'
        );

        // Without scope: record is in the database
        $this->assertTrue(
            AssetMaintenance::withoutGlobalScope('centre_isolation')
                ->where('id', $maintenanceBId)->exists(),
            'Record must physically exist in the database'
        );
    }

    // ---- Own centre records remain accessible ----

    public function test_centre_a_user_can_see_centre_a_maintenance_records(): void
    {
        $maintenanceAId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetA->id,
            'maintenance_type' => 'inspection',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'high',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(
            AssetMaintenance::where('id', $maintenanceAId)->exists(),
            'Centre A teacher must be able to see their own centre maintenance records'
        );
    }

    // ---- Collection-level isolation ----

    public function test_maintenance_collection_excludes_other_centre_records(): void
    {
        $maintenanceAId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetA->id,
            'maintenance_type' => 'cleaning',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $maintenanceBId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetB->id,
            'maintenance_type' => 'emergency',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'critical',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $visible = AssetMaintenance::whereIn('id', [$maintenanceAId, $maintenanceBId])->pluck('id');

        $this->assertContains($maintenanceAId, $visible->toArray(), 'Centre A record must appear');
        $this->assertNotContains($maintenanceBId, $visible->toArray(), 'Centre B record must not appear');
    }

    // ---- Admin bypass ----

    public function test_admin_can_see_maintenance_records_from_all_centres(): void
    {
        $admin = User::factory()->admin()->create(['centre_id' => '01']);

        session([
            'id' => $admin->id,
            'role' => 'admin',
            'centre_id' => '01',
            'logged_in' => true,
        ]);

        $maintenanceAId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetA->id,
            'maintenance_type' => 'safety_check',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $maintenanceBId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetB->id,
            'maintenance_type' => 'calibration',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'normal',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(
            AssetMaintenance::where('id', $maintenanceAId)->exists(),
            'Admin must see Centre A records'
        );
        $this->assertTrue(
            AssetMaintenance::where('id', $maintenanceBId)->exists(),
            'Admin must see Centre B records'
        );
    }

    // ---- No session: scope is a no-op ----

    public function test_scope_is_inactive_with_no_session(): void
    {
        session()->flush();

        $maintenanceBId = DB::table('asset_maintenance')->insertGetId([
            'asset_id' => $this->assetB->id,
            'maintenance_type' => 'upgrade',
            'scheduled_date' => now()->toDateString(),
            'status' => 'scheduled',
            'priority' => 'low',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(
            AssetMaintenance::where('id', $maintenanceBId)->exists(),
            'With no session the scope must not filter (console/queue safety)'
        );
    }
}
