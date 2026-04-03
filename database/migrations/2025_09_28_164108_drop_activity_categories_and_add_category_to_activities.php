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
        // Drop foreign key constraint and column if they exist
        if (Schema::hasColumn('activities', 'category_id')) {
            Schema::table('activities', function (Blueprint $table) {
                // Check if foreign key exists before dropping
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'activities'
                    AND COLUMN_NAME = 'category_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");

                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                }

                $table->dropColumn('category_id');
            });
        }

        // Drop the activity_categories table
        Schema::dropIfExists('activity_categories');

        // Add category field directly to activities table
        if (!Schema::hasColumn('activities', 'category')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->enum('category', [
                    'Autism Spectrum Support',
                    'Hearing Impairment',
                    'Visual Impairment',
                    'Physical Disabilities',
                    'Learning Support',
                    'Speech Therapy'
                ])->after('activity_description');

                $table->index('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate activity_categories table
        Schema::create('activity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->text('category_description')->nullable();
            $table->string('category_type', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category_type', 'is_active']);
        });

        // Remove category enum from activities and add back category_id
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->unsignedBigInteger('category_id')->after('activity_description');
            $table->index('category_id');
        });
    }
};
