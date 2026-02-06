<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Centre;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trainee>
 */
class TraineeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure centre exists
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

        return [
            'trainee_name' => fake()->name(),
            'trainee_ic' => fake()->numerify('############'), // Malaysian IC format
            'trainee_email' => fake()->unique()->safeEmail(),
            'trainee_phone' => '+60' . fake()->numerify('##########'),
            'trainee_dob' => fake()->dateTimeBetween('-17 years', '-6 years'), // 6-17 years old
            'trainee_gender' => fake()->randomElement(['male', 'female']),
            'trainee_address' => fake()->address(),
            'trainee_postcode' => fake()->numerify('#####'),
            'trainee_city' => fake()->city(),
            'trainee_state' => fake()->randomElement([
                'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
                'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah',
                'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur'
            ]),
            'disability_type' => fake()->randomElement([
                'Autism Spectrum Disorder', 'Down Syndrome',
                'Cerebral Palsy', 'Intellectual Disability',
                'Physical Disability', 'Learning Disability'
            ]),
            'disability_severity' => fake()->randomElement(['Mild', 'Moderate', 'Severe']),
            'medical_history' => fake()->optional()->sentence(),
            'guardian_name' => fake()->name(),
            'guardian_relationship' => fake()->randomElement(['Father', 'Mother', 'Grandfather', 'Grandmother', 'Uncle', 'Aunt', 'Guardian']),
            'guardian_phone' => '+60' . fake()->numerify('##########'),
            'guardian_email' => fake()->optional()->safeEmail(),
            'centre_id' => $centre->centre_id,
            'trainee_status' => 'active',
            'enrollment_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Indicate that the trainee has autism
     */
    public function autism(): static
    {
        return $this->state(fn (array $attributes) => [
            'disability_type' => 'Autism Spectrum Disorder',
        ]);
    }

    /**
     * Indicate that the trainee has Down syndrome
     */
    public function downSyndrome(): static
    {
        return $this->state(fn (array $attributes) => [
            'disability_type' => 'Down Syndrome',
        ]);
    }

    /**
     * Indicate that the trainee is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'trainee_status' => 'inactive',
        ]);
    }
}
