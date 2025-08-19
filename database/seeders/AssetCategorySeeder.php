<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetCategorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📂 Seeding asset categories...');

        $categories = [
            ['category_name' => 'Therapy Equipment', 'category_description' => 'Equipment used in various therapy sessions'],
            ['category_name' => 'Learning Materials', 'category_description' => 'Educational and learning support materials'],
            ['category_name' => 'Office Equipment', 'category_description' => 'Administrative and office-related equipment'],
            ['category_name' => 'Assistive Technology', 'category_description' => 'Technology to assist individuals with disabilities'],
            ['category_name' => 'Classroom Furniture', 'category_description' => 'Furniture specifically for classroom use']
        ];

        foreach ($categories as $category) {
            DB::table('asset_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        $this->command->info('📂 Successfully seeded ' . count($categories) . ' asset categories');
    }
}