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
            'trainee_first_name' => fake()->firstName(),
            'trainee_last_name' => fake()->lastName(),
            'trainee_email' => fake()->unique()->safeEmail(),
            'ic_number' => fake()->numerify('############'), // Malaysian IC format
            'trainee_date_of_birth' => fake()->dateTimeBetween('-17 years', '-6 years'), // 6-17 years old
            'gender' => fake()->randomElement(['male', 'female']),
            'trainee_phone_number' => '+60' . fake()->numerify('##########'),
            'trainee_address' => fake()->address() . ', ' .
                               fake()->numerify('#####') . ' ' .
                               fake()->city() . ', ' .
                               fake()->randomElement([
                                   'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan',
                                   'Pahang', 'Penang', 'Perak', 'Perlis', 'Sabah',
                                   'Sarawak', 'Selangor', 'Terengganu', 'Kuala Lumpur'
                               ]),
            'trainee_condition' => fake()->randomElement([
                'Autism Spectrum Disorder', 'Down Syndrome',
                'Cerebral Palsy', 'Intellectual Disability',
                'Physical Disability', 'Learning Disability'
            ]),
            'centre_id' => $centre->centre_id,
            'centre_name' => $centre->centre_name,
            'status' => 'active',
            'guardian_name' => fake()->name(),
            'guardian_relationship' => fake()->randomElement(['Father', 'Mother', 'Grandfather', 'Grandmother', 'Uncle', 'Aunt', 'Guardian']),
            'guardian_phone' => '+60' . fake()->numerify('##########'),
            'guardian_email' => fake()->optional()->safeEmail(),
            'guardian_address' => fake()->optional()->address(),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '+60' . fake()->numerify('##########'),
            'emergency_contact_relationship' => fake()->randomElement(['Father', 'Mother', 'Sibling', 'Relative', 'Family Friend']),
            'photo_consent' => fake()->boolean(80),
            'services_consent' => true,
            'data_consent' => true,
            'registration_date' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Indicate that the trainee has autism
     */
    public function autism(): static
    {
        return $this->state(fn (array $attributes) => [
            'trainee_condition' => 'Autism Spectrum Disorder',
        ]);
    }

    /**
     * Indicate that the trainee has Down syndrome
     */
    public function downSyndrome(): static
    {
        return $this->state(fn (array $attributes) => [
            'trainee_condition' => 'Down Syndrome',
        ]);
    }

    /**
     * Indicate that the trainee is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
