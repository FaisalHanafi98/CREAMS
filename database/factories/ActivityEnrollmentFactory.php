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

        return [
            'activity_id' => $activity->id,
            'trainee_id' => $trainee->id,
            'enrollment_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'enrollment_status' => 'enrolled',
            'enrollment_notes' => fake()->optional()->sentence(),
            'progress_percentage' => fake()->randomFloat(2, 0, 100),
            'attendance_count' => fake()->numberBetween(0, 10),
            'completion_date' => null,
            'completion_notes' => null,
            'enrolled_by' => $teacher->id,
        ];
    }

    /**
     * Indicate that the enrollment is enrolled
     */
    public function enrolled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'enrolled',
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
            'completion_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'completion_notes' => fake()->sentence(),
            'progress_percentage' => 100,
            'attendance_count' => fake()->numberBetween(10, 20),
        ]);
    }

    /**
     * Indicate that the enrollment is dropped
     */
    public function dropped(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'dropped',
            'completion_date' => fake()->dateTimeBetween('-1 week', 'now'),
            'completion_notes' => 'Trainee dropped from program',
        ]);
    }

    /**
     * Indicate that the enrollment is pending
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'enrollment_status' => 'pending',
            'attendance_count' => 0,
        ]);
    }
}
