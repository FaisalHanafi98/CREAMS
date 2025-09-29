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
        Schema::table('activity_enrollments', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('activity_enrollments', 'enrollment_notes')) {
                $table->text('enrollment_notes')->nullable()->after('enrollment_status');
            }
            if (!Schema::hasColumn('activity_enrollments', 'completion_notes')) {
                $table->text('completion_notes')->nullable()->after('completion_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropColumn(['enrollment_notes', 'completion_notes']);
        });
    }
};
