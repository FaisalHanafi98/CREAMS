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
            'activity_type' => fake()->randomElement(['therapy', 'education', 'recreation', 'training']),
            'difficulty_level' => fake()->randomElement(['Beginner', 'Intermediate', 'Advanced']),
            'age_group' => fake()->randomElement(['6-10', '11-14', '15-17', '6-17']),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'max_participants' => fake()->numberBetween(5, 20),
            'materials_required' => fake()->optional()->sentence(),
            'instructor_id' => $teacher->id,
            'centre_id' => $centre->centre_id,
            'status' => 'active',
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 week'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }

    /**
     * Indicate that the activity is for autism support
     */
    public function autismSupport(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Autism Support',
            'activity_type' => 'therapy',
        ]);
    }

    /**
     * Indicate that the activity is recreational
     */
    public function recreational(): static
    {
        return $this->state(fn (array $attributes) => [
            'activity_type' => 'recreation',
            'difficulty_level' => 'Beginner',
        ]);
    }

    /**
     * Indicate that the activity is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    /**
     * Indicate that the activity is completed
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'end_date' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
