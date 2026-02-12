<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Activity;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivitySession>
 */
class ActivitySessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure activity and teacher exist
        $activity = Activity::factory()->create();
        $teacher = User::factory()->teacher()->create(['centre_id' => $activity->centre_id]);

        // Generate valid weekday session date (Monday-Friday, between 9:30 AM - 4:00 PM)
        $sessionDate = fake()->dateTimeBetween('+1 day', '+2 months');
        while (date('w', $sessionDate->getTimestamp()) == 0 || date('w', $sessionDate->getTimestamp()) == 6) {
            $sessionDate = fake()->dateTimeBetween('+1 day', '+2 months');
        }

        // Generate valid time slots (9:30 AM to 4:00 PM, sessions end by 4:00 PM)
        $startHour = fake()->numberBetween(9, 14);
        $startMinute = ($startHour == 9) ? 30 : fake()->randomElement([0, 30]);
        $durationMinutes = fake()->randomElement([30, 45, 60, 90]);

        $startTime = sprintf('%02d:%02d:00', $startHour, $startMinute);
        $endTimeStamp = strtotime($startTime) + ($durationMinutes * 60);
        $endTime = date('H:i:s', min($endTimeStamp, strtotime('16:00:00')));

        return [
            'activity_id' => $activity->id,
            'session_name' => $activity->activity_name . ' - Session',
            'session_description' => fake()->optional()->sentence(),
            'session_date' => $sessionDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'location' => fake()->randomElement(['Therapy Room 1', 'Therapy Room 2', 'Activity Hall', 'Classroom A', 'Classroom B', 'Outdoor Area']),
            'max_participants' => fake()->numberBetween(5, 20),
            'current_participants' => 0,
            'instructor_id' => $teacher->id,
            'session_status' => 'scheduled',
            'session_notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the session is scheduled
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'scheduled',
            'session_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    /**
     * Indicate that the session is ongoing
     */
    public function ongoing(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'ongoing',
            'session_date' => now()->toDateString(),
        ]);
    }

    /**
     * Indicate that the session is completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'completed',
            'session_date' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Indicate that the session is cancelled
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_status' => 'cancelled',
        ]);
    }
}
