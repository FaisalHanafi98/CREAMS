<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Centre>
 */
class CentreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'centre_id' => '01',
            'centre_name' => 'Test Centre',
            'centre_address' => fake()->address(),
            'centre_phone' => '+60' . fake()->numerify('##########'),
            'centre_email' => fake()->unique()->safeEmail(),
            'centre_capacity' => fake()->numberBetween(20, 100),
            'centre_status' => 'active',
            'is_active' => true,
        ];
    }
}
