<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add faith-based activities to categories
        $faithCategories = [
            [
                'category_name' => 'Pembelajaran Solat',
                'category_description' => 'Learning prayer rituals and movements for spiritual development',
                'category_color' => '#2ECC71',
                'category_icon' => 'fas fa-praying-hands',
                'category_status' => 'active',
                'category_type' => 'faith',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Tilawah Al-Quran',
                'category_description' => 'Quran recitation and memorization sessions',
                'category_color' => '#3498DB',
                'category_icon' => 'fas fa-book-quran',
                'category_status' => 'active',
                'category_type' => 'faith',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Adab dan Akhlak',
                'category_description' => 'Islamic manners and moral character development',
                'category_color' => '#9B59B6',
                'category_icon' => 'fas fa-hands-helping',
                'category_status' => 'active',
                'category_type' => 'faith',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Sejarah Islam',
                'category_description' => 'Islamic history and stories of prophets',
                'category_color' => '#E67E22',
                'category_icon' => 'fas fa-scroll',
                'category_status' => 'active',
                'category_type' => 'faith',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Doa dan Zikir',
                'category_description' => 'Prayer recitation and remembrance of Allah',
                'category_color' => '#1ABC9C',
                'category_icon' => 'fas fa-hand-holding-heart',
                'category_status' => 'active',
                'category_type' => 'faith',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('categories')->insert($faithCategories);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('categories')->where('category_type', 'faith')->delete();
    }
};
