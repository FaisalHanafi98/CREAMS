<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing foreign key constraint on activities table
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
        });

        // Add new foreign key constraint pointing to activities_new table
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities_new')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint on activities_new table
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
        });

        // Restore the original foreign key constraint pointing to activities table
        Schema::table('activity_schedules', function (Blueprint $table) {
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
        });
    }
};
