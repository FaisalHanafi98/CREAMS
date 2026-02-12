<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Centre;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure centre and teacher exist
        $centre = Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Test Centre',
                'centre_phone' => '+60123456789',
                'centre_email' => 'test@centre.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $teacher = User::factory()->teacher()->create(['centre_id' => $centre->centre_id]);

        return [
            'activity_name' => fake()->catchPhrase() . ' Activity',
            'activity_description' => fake()->paragraph(),
            'category' => fake()->randomElement([
                'Autism Support',
                'Physical Disabilities Support',
                'Occupational Therapy',
                'Physiotherapy',
                'Speech Therapy',
                'Behavioral Therapy',
                'Social Skills Training',
                'Life Skills Development',
                'Educational Support',
                'Recreational Activities'
            ]),
            'centre_id' => $centre->centre_id,
            'duration_weeks' => fake()->numberBetween(4, 16),
            'sessions_per_week' => fake()->numberBetween(1, 5),
            'session_duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'max_participants' => fake()->numberBetween(5, 20),
            'learning_outcomes' => fake()->optional()->paragraph(),
            'activity_location' => fake()->optional()->randomElement(['Therapy Room 1', 'Therapy Room 2', 'Activity Hall', 'Classroom A', 'Classroom B', 'Outdoor Area']),
            'instructor_id' => $teacher->id,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the activity is for autism support
     */
    public function autismSupport(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Autism Support',
        ]);
    }

    /**
     * Indicate that the activity is recreational
     */
    public function recreational(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Recreational Activities',
        ]);
    }

    /**
     * Indicate that the activity is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the activity is for therapy
     */
    public function therapy(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => fake()->randomElement(['Occupational Therapy', 'Physiotherapy', 'Speech Therapy', 'Behavioral Therapy']),
        ]);
    }
}
