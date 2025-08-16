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
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Make these fields nullable or add default values
            $table->string('session_location')->nullable()->default('TBA')->change();
            $table->time('session_start_time')->nullable()->default('09:00:00')->change();
            $table->string('session_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Revert the changes
            $table->string('session_location')->nullable(false)->change();
            $table->time('session_start_time')->nullable(false)->change();
            $table->string('session_id')->nullable(false)->change();
        });
    }
};