<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure activity, session, trainee, and teacher exist
        $activity = Activity::factory()->create();
        $session = ActivitySession::factory()->create(['activity_id' => $activity->id]);
        $trainee = Trainee::factory()->create(['centre_id' => $activity->centre_id]);
        $teacher = User::factory()->teacher()->create(['centre_id' => $activity->centre_id]);

        return [
            'trainee_id' => $trainee->id,
            'activity_id' => $activity->id,
            'session_id' => $session->id,
            'attendance_date' => $session->session_date,
            'status' => fake()->randomElement(['present', 'absent', 'late', 'excused']),
            'notes' => fake()->optional()->sentence(),
            'marked_by_user_id' => $teacher->id,
            'marked_at' => now(),
        ];
    }

    /**
     * Indicate that the trainee was present
     */
    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'present',
        ]);
    }

    /**
     * Indicate that the trainee was absent
     */
    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'absent',
            'notes' => fake()->randomElement(['Sick', 'Family emergency', 'No transportation', 'Other']),
        ]);
    }

    /**
     * Indicate that the trainee was late
     */
    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'notes' => fake()->optional()->sentence(),
        ]);
    }

    /**
     * Indicate that the absence was excused
     */
    public function excused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'excused',
            'notes' => fake()->randomElement(['Medical appointment', 'Official leave', 'Public holiday', 'Approved absence']),
        ]);
    }
}
