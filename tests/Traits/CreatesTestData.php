<?php

namespace Tests\Traits;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\Trainee;
use App\Models\User;
use App\Models\Centre;

trait CreatesTestData
{
    /**
     * Create an activity with optional overrides
     *
     * @param array $overrides
     * @return Activity
     */
    protected function createActivity(array $overrides = []): Activity
    {
        $defaults = [
            'activity_name' => 'Test Activity',
            'activity_description' => 'Test activity description',
            'category' => 'Autism Support',
            'centre_id' => '01',
            'duration_weeks' => 8,
            'sessions_per_week' => 2,
            'session_duration_minutes' => 60,
            'max_participants' => 15,
            'is_active' => true,
        ];

        // Ensure instructor exists
        if (!isset($overrides['instructor_id'])) {
            $teacher = User::factory()->teacher()->create(['centre_id' => $overrides['centre_id'] ?? '01']);
            $overrides['instructor_id'] = $teacher->id;
        }

        return Activity::create(array_merge($defaults, $overrides));
    }

    /**
     * Create an activity session with optional overrides
     *
     * @param Activity $activity
     * @param array $overrides
     * @return ActivitySession
     */
    protected function createSession(Activity $activity, array $overrides = []): ActivitySession
    {
        $defaults = [
            'activity_id' => $activity->id,
            'session_name' => $activity->activity_name . ' - Session',
            'session_date' => now()->addDays(7),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'location' => 'Therapy Room 1',
            'max_participants' => $activity->max_participants,
            'current_participants' => 0,
            'instructor_id' => $activity->instructor_id,
            'session_status' => 'scheduled',
        ];

        return ActivitySession::create(array_merge($defaults, $overrides));
    }

    /**
     * Create a trainee and enroll them in an activity
     *
     * @param Activity $activity
     * @param array $traineeOverrides
     * @return Trainee
     */
    protected function createTraineeWithEnrollment(Activity $activity, array $traineeOverrides = []): Trainee
    {
        $trainee = Trainee::factory()->create(array_merge([
            'centre_id' => $activity->centre_id,
        ], $traineeOverrides));

        ActivityEnrollment::factory()->create([
            'activity_id' => $activity->id,
            'trainee_id' => $trainee->id,
            'enrolled_by' => $activity->instructor_id,
        ]);

        return $trainee;
    }

    /**
     * Create a centre with optional overrides
     *
     * @param array $overrides
     * @return Centre
     */
    protected function createCentre(array $overrides = []): Centre
    {
        $defaults = [
            'centre_id' => '99',
            'centre_name' => 'Test Centre',
            'centre_phone' => '+60123456789',
            'centre_email' => 'test@centre.com',
            'centre_capacity' => 50,
            'centre_status' => 'active',
            'is_active' => true,
        ];

        return Centre::firstOrCreate(
            ['centre_id' => $overrides['centre_id'] ?? '99'],
            array_merge($defaults, $overrides)
        );
    }

    /**
     * Create multiple trainees for a centre
     *
     * @param string $centreId
     * @param int $count
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function createTrainees(string $centreId, int $count = 5)
    {
        return Trainee::factory()->count($count)->create(['centre_id' => $centreId]);
    }
}
