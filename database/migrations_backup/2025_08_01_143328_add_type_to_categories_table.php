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
        Schema::table('categories', function (Blueprint $table) {
            $table->enum('category_type', ['rehabilitation', 'academic', 'creative_social'])
                  ->default('rehabilitation')
                  ->after('category_name');
        });

        // Update existing categories with their proper types
        DB::table('categories')->whereIn('category_name', [
            'Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 
            'Behavioral Therapy', 'Sensory Integration'
        ])->update(['category_type' => 'rehabilitation']);

        DB::table('categories')->whereIn('category_name', [
            'Mathematics', 'Literacy', 'Science', 'Computer Skills'
        ])->update(['category_type' => 'academic']);

        DB::table('categories')->whereIn('category_name', [
            'Art & Creativity', 'Music Therapy', 'Social Skills', 
            'Life Skills', 'Vocational Training'
        ])->update(['category_type' => 'creative_social']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('category_type');
        });
    }
};
