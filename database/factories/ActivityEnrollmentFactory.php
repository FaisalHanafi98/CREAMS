<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Activity;
use App\Models\Trainee;
use App\Models\ActivitySession;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityEnrollment>
 */
class ActivityEnrollmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure activity, trainee, and teacher exist
        $activity = Activity::factory()->create();
        $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
        $teacher = User::factory()->teacher()->create(['centre_id' => $activity->centre_id]);

        // Get or create a session for this activity
        $session = ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'instructor_id' => $teacher->id,
        ]);

        return [
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'session_id' => $session->id,
            'enrollment_status' => 'enrolled',
            'enrollment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'enrollment_notes' => fake()->optional()->sentence(),
            'progress_percentage' => fake()->randomFloat(2, 0, 100),
            'attendance_count' => fake()->numberBetween(0, 10),
            'completion_date' => null,
            'completion_notes' => null,
            'start_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'status' => 'enrolled',
            'enrolled_by' => $teacher->id,
            'attendance_marked' => false,
            'participation_score' => null,
            'progress_notes' => fake()->optional()->sentence(),
            'assessment_data' => null,
        ];
    }

    /**
     * Indicate that the enrollment is enrolled
     */
    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'enrolled',
            'status' => 'enrolled',
            'attendance_marked' => false,
            'completion_date' => null,
        ]);
    }

    /**
     * Indicate that the enrollment is completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'completed',
            'status' => 'completed',
            'attendance_marked' => true,
            'completion_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'completion_notes' => fake()->sentence(),
            'participation_score' => fake()->numberBetween(5, 10),
            'progress_percentage' => 100,
        ]);
    }

    /**
     * Indicate that the enrollment is dropped
     */
    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'dropped',
            'status' => 'dropped',
            'completion_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'completion_notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the enrollment is absent
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'absent',
            'status' => 'absent',
            'attendance_marked' => true,
        ]);
    }
}
