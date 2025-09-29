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
        // Add current_participants field to activity_sessions table
        Schema::table('activity_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_sessions', 'current_participants')) {
                $table->integer('current_participants')->default(0)->after('max_participants');
            }
        });

        // Fix enrolled_by field type and add foreign key constraint
        Schema::table('activity_enrollments', function (Blueprint $table) {
            // Change enrolled_by from int to bigint unsigned to match users.id
            $table->unsignedBigInteger('enrolled_by')->nullable()->change();

            // Add foreign key constraint to enrolled_by field
            $table->foreign('enrolled_by')->references('id')->on('users')->onDelete('set null');
        });

        // Update current_participants for existing sessions based on actual enrollments
        DB::statement("
            UPDATE activity_sessions
            SET current_participants = (
                SELECT COUNT(*)
                FROM activity_enrollments
                WHERE activity_enrollments.activity_id = activity_sessions.activity_id
                AND activity_enrollments.enrollment_status = 'enrolled'
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->dropColumn('current_participants');
        });

        Schema::table('activity_enrollments', function (Blueprint $table) {
            $table->dropForeign(['enrolled_by']);
        });
    }
};
