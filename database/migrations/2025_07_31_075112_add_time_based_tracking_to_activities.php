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
        Schema::table('activities', function (Blueprint $table) {
            // Add time-based tracking fields for academic progress calculation
            $table->date('start_date')->nullable()->after('activity_date');
            $table->date('end_date')->nullable()->after('start_date');
            $table->integer('sessions_per_week')->default(2)->after('end_date');
            $table->decimal('pass_threshold', 5, 2)->default(60.00)->after('sessions_per_week'); // 60% to pass
            $table->boolean('is_active')->default(true)->after('pass_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'sessions_per_week', 'pass_threshold', 'is_active']);
        });
    }
};
