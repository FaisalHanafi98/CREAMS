<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Improve activity_sessions table:
     * 1. Set proper default values for max_participants
     * 2. Add default placeholder for session_notes
     * 3. Fix overbooked sessions
     * 4. Add validation constraints
     */
    public function up(): void
    {
        // 1. UPDATE MAX_PARTICIPANTS FOR OVERBOOKED SESSIONS
        $this->fixOverbookedSessions();

        // 2. IMPROVE SESSION_NOTES HANDLING
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Change session_notes to have a default empty string instead of NULL
            $table->text('session_notes')->default('')->change();

            // Ensure max_participants has a reasonable default
            $table->integer('max_participants')->default(10)->change();

            // Add check constraint to ensure max_participants is positive
            // Note: MySQL doesn't support check constraints in older versions, so we'll handle this in application logic
        });

        // 3. UPDATE EXISTING NULL VALUES
        DB::table('activity_sessions')
            ->whereNull('session_notes')
            ->update(['session_notes' => '']);

        // 4. SET DEFAULT MAX_PARTICIPANTS FOR NULL VALUES
        DB::table('activity_sessions')
            ->whereNull('max_participants')
            ->update(['max_participants' => 10]);

        // 5. ADD BUSINESS LOGIC VALIDATION TRIGGERS
        $this->addValidationTriggers();
    }

    /**
     * Fix overbooked sessions by adjusting max_participants
     */
    private function fixOverbookedSessions(): void
    {
        // Get overbooked sessions and fix them
        $overbookedSessions = DB::select("
            SELECT
                a_s.id,
                a_s.activity_id,
                a_s.max_participants,
                a_s.current_participants,
                (a_s.current_participants - a_s.max_participants) as excess
            FROM activity_sessions a_s
            WHERE a_s.current_participants > a_s.max_participants
        ");

        foreach ($overbookedSessions as $session) {
            // Set max_participants to current_participants + 2 buffer spots
            $newMax = $session->current_participants + 2;

            DB::table('activity_sessions')
                ->where('id', $session->id)
                ->update(['max_participants' => $newMax]);

            // Log the change
            DB::table('audit_logs')->insert([
                'action' => 'update',
                'table' => 'activity_sessions',
                'record_id' => $session->id,
                'description' => "Fixed overbooked session: increased max_participants from {$session->max_participants} to {$newMax}",
                'old_values' => json_encode(['max_participants' => $session->max_participants]),
                'new_values' => json_encode(['max_participants' => $newMax]),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Add validation triggers (MySQL compatible)
     */
    private function addValidationTriggers(): void
    {
        // Add trigger to validate max_participants is positive
        DB::unprepared("
            CREATE TRIGGER validate_activity_sessions_before_insert
            BEFORE INSERT ON activity_sessions
            FOR EACH ROW
            BEGIN
                IF NEW.max_participants <= 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'max_participants must be greater than 0';
                END IF;

                IF NEW.start_time >= NEW.end_time THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'start_time must be before end_time';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER validate_activity_sessions_before_update
            BEFORE UPDATE ON activity_sessions
            FOR EACH ROW
            BEGIN
                IF NEW.max_participants <= 0 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'max_participants must be greater than 0';
                END IF;

                IF NEW.start_time >= NEW.end_time THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'start_time must be before end_time';
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove validation triggers
        DB::unprepared("DROP TRIGGER IF EXISTS validate_activity_sessions_before_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS validate_activity_sessions_before_update");

        // Revert field changes
        Schema::table('activity_sessions', function (Blueprint $table) {
            $table->text('session_notes')->nullable()->change();
            $table->integer('max_participants')->nullable()->change();
        });

        // Note: We don't revert the overbooking fixes as they were data quality improvements
    }
};