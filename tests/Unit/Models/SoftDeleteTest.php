<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * Verifies SoftDeletes works correctly on PDPA-critical models.
 * CREAMS must never hard-delete trainee or staff records — PDPA requires audit trail.
 */
class SoftDeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    public function test_trainee_soft_delete_sets_deleted_at()
    {
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha']);

        $trainee->delete();

        // Record should NOT exist in normal queries
        $this->assertNull(Trainee::find($trainee->id));

        // Record MUST still exist in DB (soft deleted)
        $this->assertNotNull(Trainee::withTrashed()->find($trainee->id));
        $this->assertNotNull(Trainee::withTrashed()->find($trainee->id)->deleted_at);
    }

    public function test_trainee_can_be_restored_after_soft_delete()
    {
        $trainee = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha']);
        $trainee->delete();

        $trainee->restore();

        $this->assertNotNull(Trainee::find($trainee->id));
        $this->assertNull(Trainee::find($trainee->id)->deleted_at);
    }

    public function test_user_staff_soft_delete_preserves_record()
    {
        $staff = User::factory()->create(['centre_id' => '01', 'status' => 'active']);

        $staff->delete();

        $this->assertNull(User::find($staff->id));
        $this->assertNotNull(User::withTrashed()->find($staff->id));
    }

    public function test_activity_soft_delete_preserves_record()
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);

        $activity->delete();

        $this->assertNull(Activity::find($activity->id));
        $this->assertNotNull(Activity::withTrashed()->find($activity->id));
    }

    public function test_only_trashed_scope_returns_deleted_records()
    {
        $active = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $deleted = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $deleted->delete();

        $trashedOnly = Trainee::onlyTrashed()->get();

        $this->assertTrue($trashedOnly->contains('id', $deleted->id));
        $this->assertFalse($trashedOnly->contains('id', $active->id));
    }
}
