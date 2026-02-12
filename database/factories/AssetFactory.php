<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Centre;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
class AssetFactory extends Factory
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

        // Create assigned user if asset is in use
        $assignedUser = null;
        $status = fake()->randomElement(['available', 'in_use', 'maintenance', 'disposed']);
        if ($status === 'in_use') {
            $assignedUser = User::factory()->teacher()->create(['centre_id' => $centre->centre_id]);
        }

        // Create or get asset category
        $categoryName = fake()->randomElement(['Therapy Equipment', 'Educational Materials', 'Medical Equipment', 'Furniture', 'Technology', 'Vehicles']);
        $category = \DB::table('asset_categories')->where('category_name', $categoryName)->first();
        if (!$category) {
            $categoryId = \DB::table('asset_categories')->insertGetId([
                'category_name' => $categoryName,
                'category_description' => 'Test category',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $categoryId = $category->id;
        }

        // Create or get asset parent (type)
        $parentType = \DB::table('asset_parents')->where('name', 'General Equipment')->first();
        if (!$parentType) {
            $parentId = \DB::table('asset_parents')->insertGetId([
                'name' => 'General Equipment',
                'type_description' => 'Test type',
                'centre_id' => $centre->centre_id,
                'category_id' => $categoryId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $parentId = $parentType->id;
        }

        // Create or get asset location
        $locationName = fake()->randomElement(['Therapy Room', 'Classroom', 'Storage', 'Office']);
        $location = \DB::table('asset_locations')->where('location_name', $locationName)->first();
        if (!$location) {
            $locationId = \DB::table('asset_locations')->insertGetId([
                'location_name' => $locationName,
                'location_description' => 'Test location',
                'centre_id' => $centre->centre_id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $locationId = $location->id;
        }

        $assetNames = ['Therapy Balls', 'Educational Books', 'Desktop Computer', 'Tables', 'Wheelchair'];
        $assetName = fake()->randomElement($assetNames);

        return [
            'asset_tag' => 'AST-' . fake()->unique()->numerify('####'),
            'asset_name' => $assetName,
            'asset_description' => fake()->sentence(),
            'category_id' => $categoryId,
            'type_id' => $parentId,
            'centre_id' => $centre->centre_id,
            'location_id' => $locationId,
            'serial_number' => fake()->optional()->numerify('SN-##########'),
            'model_number' => fake()->optional()->bothify('MOD-????-####'),
            'manufacturer' => fake()->optional()->company(),
            'purchase_date' => fake()->dateTimeBetween('-5 years', '-1 month'),
            'purchase_price' => fake()->randomFloat(2, 100, 10000),
            'warranty_expiry' => fake()->optional()->dateTimeBetween('now', '+3 years'),
            'condition' => fake()->randomElement(['new', 'good', 'fair', 'poor']),
            'status' => $status,
            'assigned_to_user' => $assignedUser?->id,
            'notes' => fake()->optional()->sentence(),
            'images' => null,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the asset is available
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
            'assigned_to_user' => null,
        ]);
    }

    /**
     * Indicate that the asset is in use
     */
    public function inUse(): static
    {
        $user = User::factory()->teacher()->create();

        return $this->state(fn (array $attributes) => [
            'status' => 'in_use',
            'assigned_to_user' => $user->id,
        ]);
    }

    /**
     * Indicate that the asset is under maintenance
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
            'condition' => fake()->randomElement(['fair', 'poor']),
            'notes' => 'Currently under maintenance. ' . fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the asset is disposed
     */
    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disposed',
            'is_active' => false,
            'notes' => 'Asset disposed. ' . fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the asset is brand new
     */
    public function brandNew(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'new',
            'purchase_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'warranty_expiry' => fake()->dateTimeBetween('+1 year', '+3 years'),
        ]);
    }

    /**
     * Indicate that the asset is a vehicle
     */
    public function vehicle(): static
    {
        return $this->state(fn (array $attributes) => [
            'asset_name' => fake()->randomElement(['Van', 'Bus', 'Wheelchair Transport Vehicle']),
            'purchase_price' => fake()->randomFloat(2, 50000, 200000),
            'serial_number' => fake()->bothify('??####??'),
            'model_number' => fake()->bothify('####-???'),
        ]);
    }
}
