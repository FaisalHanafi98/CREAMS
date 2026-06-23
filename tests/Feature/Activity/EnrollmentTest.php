<?php

namespace Tests\Feature\Activity;

use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\Centre;
use App\Models\Trainee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\InteractsWithRoles;

/**
 * Regression coverage for ActivityController enrollment phantom-column defects.
 *
 * enrollTrainees() and enrollParticipants() wrote `start_date`, `status`, and `goals`
 * to activity_enrollments — none of which exist in the live table. MySQL threw on INSERT,
 * silently caught, so enrollments appeared to succeed (redirect) but nothing was persisted.
 */
class EnrollmentTest extends TestCase
{
    use DatabaseTransactions, InteractsWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_enroll_trainee_persists_enrollment_to_database(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        $response = $this->actingAsAdmin('01')
            ->post(route('activities.enroll.submit', $activity->id), [
                'trainee_ids'     => [$trainee->id],
                'enrollment_date' => now()->toDateString(),
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('activity_enrollments', [
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_status' => 'enrolled',
        ]);
    }

    public function test_enroll_trainee_with_notes_stores_notes_field(): void
    {
        $activity = Activity::factory()->create(['centre_id' => '01']);
        $trainee  = Trainee::factory()->create(['centre_id' => '01']);

        $this->actingAsAdmin('01')
            ->post(route('activities.enroll.submit', $activity->id), [
                'trainee_ids'     => [$trainee->id],
                'enrollment_date' => now()->toDateString(),
                'goals'           => 'Improve motor skills by end of term',
            ]);

        $this->assertDatabaseHas('activity_enrollments', [
            'activity_id'       => $activity->id,
            'trainee_id'        => $trainee->id,
            'enrollment_notes'  => 'Improve motor skills by end of term',
        ]);
    }
}
