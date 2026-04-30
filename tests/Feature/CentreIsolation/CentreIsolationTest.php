<?php

namespace Tests\Feature\CentreIsolation;

use Tests\TestCase;
use App\Models\User;
use App\Models\Centre;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Scopes\CentreScope;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CentreIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure both test centres exist for FK constraints
        Centre::firstOrCreate(
            ['centre_id' => '01'],
            ['centre_name' => 'Centre Alpha', 'centre_phone' => '+60100000001', 'centre_email' => 'alpha@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
        Centre::firstOrCreate(
            ['centre_id' => '02'],
            ['centre_name' => 'Centre Beta', 'centre_phone' => '+60100000002', 'centre_email' => 'beta@test.com', 'centre_capacity' => 50, 'centre_status' => 'active', 'is_active' => true]
        );
    }

    private function makeUser(string $role, string $centreId): User
    {
        return User::factory()->create([
            'role' => $role,
            'centre_id' => $centreId,
            'status' => 'active',
        ]);
    }

    public function test_teacher_only_sees_trainees_from_own_centre()
    {
        $teacher = $this->makeUser('teacher', '01');

        $ownTrainee = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $otherTrainee = Trainee::factory()->create(['centre_id' => '02', 'centre_name' => 'Centre Beta', 'status' => 'active']);

        $this->actingAs($teacher);

        $trainees = Trainee::all();

        $this->assertTrue($trainees->contains('id', $ownTrainee->id), 'Teacher should see trainees from own centre');
        $this->assertFalse($trainees->contains('id', $otherTrainee->id), 'Teacher must NOT see trainees from other centre');
    }

    public function test_admin_sees_trainees_from_all_centres()
    {
        $admin = $this->makeUser('admin', '01');

        $trainee01 = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $trainee02 = Trainee::factory()->create(['centre_id' => '02', 'centre_name' => 'Centre Beta', 'status' => 'active']);

        $this->actingAs($admin);

        $trainees = Trainee::all();

        $this->assertTrue($trainees->contains('id', $trainee01->id));
        $this->assertTrue($trainees->contains('id', $trainee02->id));
    }

    public function test_supervisor_only_sees_activities_from_own_centre()
    {
        $supervisor = $this->makeUser('supervisor', '01');

        $ownActivity = Activity::factory()->create(['centre_id' => '01']);
        $otherActivity = Activity::factory()->create(['centre_id' => '02']);

        $this->actingAs($supervisor);

        $activities = Activity::all();

        $this->assertTrue($activities->contains('id', $ownActivity->id), 'Supervisor should see own centre activities');
        $this->assertFalse($activities->contains('id', $otherActivity->id), 'Supervisor must NOT see other centre activities');
    }

    public function test_admin_sees_activities_from_all_centres()
    {
        $admin = $this->makeUser('admin', '01');

        $activity01 = Activity::factory()->create(['centre_id' => '01']);
        $activity02 = Activity::factory()->create(['centre_id' => '02']);

        $this->actingAs($admin);

        $activities = Activity::all();

        $this->assertTrue($activities->contains('id', $activity01->id));
        $this->assertTrue($activities->contains('id', $activity02->id));
    }

    public function test_without_global_scope_bypasses_centre_filtering()
    {
        $teacher = $this->makeUser('teacher', '01');

        $ownTrainee = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $otherTrainee = Trainee::factory()->create(['centre_id' => '02', 'centre_name' => 'Centre Beta', 'status' => 'active']);

        $this->actingAs($teacher);

        $allTrainees = Trainee::withoutGlobalScope(CentreScope::class)->get();

        $this->assertTrue($allTrainees->contains('id', $ownTrainee->id));
        $this->assertTrue($allTrainees->contains('id', $otherTrainee->id), 'withoutGlobalScope must bypass centre filtering');
    }

    public function test_unauthenticated_queries_return_all_records()
    {
        $trainee01 = Trainee::factory()->create(['centre_id' => '01', 'centre_name' => 'Centre Alpha', 'status' => 'active']);
        $trainee02 = Trainee::factory()->create(['centre_id' => '02', 'centre_name' => 'Centre Beta', 'status' => 'active']);

        $trainees = Trainee::all();

        $this->assertTrue($trainees->contains('id', $trainee01->id));
        $this->assertTrue($trainees->contains('id', $trainee02->id));
    }
}
