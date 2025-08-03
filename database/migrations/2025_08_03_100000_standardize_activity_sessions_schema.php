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
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Add missing room_number field
            if (!Schema::hasColumn('activity_sessions', 'room_number')) {
                $table->string('room_number', 50)->nullable()->after('venue');
            }
            
            // Add recurring pattern support for schedule templates
            if (!Schema::hasColumn('activity_sessions', 'recurring_pattern')) {
                $table->json('recurring_pattern')->nullable()->after('status');
            }
            
            // Add color coding for calendar display
            if (!Schema::hasColumn('activity_sessions', 'color_code')) {
                $table->string('color_code', 7)->default('#3498db')->after('recurring_pattern');
            }
            
            // Add session-specific notes separate from general notes
            if (!Schema::hasColumn('activity_sessions', 'session_notes')) {
                $table->text('session_notes')->nullable()->after('notes');
            }
            
            // Add encrypted_id for secure URL access
            if (!Schema::hasColumn('activity_sessions', 'encrypted_id')) {
                $table->string('encrypted_id')->unique()->nullable()->after('id');
            }
            
            // Add session priority for scheduling conflicts
            if (!Schema::hasColumn('activity_sessions', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal')->after('status');
            }
        });

        // Add performance indexes
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_date_teacher ON activity_sessions(session_date, teacher_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_venue_time ON activity_sessions(venue, session_date, start_time)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_status_date ON activity_sessions(status, session_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_sessions_encrypted_id ON activity_sessions(encrypted_id)');
        
        // Generate encrypted IDs for existing sessions
        $this->generateEncryptedIds();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_sessions', function (Blueprint $table) {
            // Remove added columns
            if (Schema::hasColumn('activity_sessions', 'room_number')) {
                $table->dropColumn('room_number');
            }
            if (Schema::hasColumn('activity_sessions', 'recurring_pattern')) {
                $table->dropColumn('recurring_pattern');
            }
            if (Schema::hasColumn('activity_sessions', 'color_code')) {
                $table->dropColumn('color_code');
            }
            if (Schema::hasColumn('activity_sessions', 'session_notes')) {
                $table->dropColumn('session_notes');
            }
            if (Schema::hasColumn('activity_sessions', 'encrypted_id')) {
                $table->dropColumn('encrypted_id');
            }
            if (Schema::hasColumn('activity_sessions', 'priority')) {
                $table->dropColumn('priority');
            }
        });

        // Drop indexes
        DB::statement('DROP INDEX IF EXISTS idx_sessions_date_teacher');
        DB::statement('DROP INDEX IF EXISTS idx_sessions_venue_time');
        DB::statement('DROP INDEX IF EXISTS idx_sessions_status_date');
        DB::statement('DROP INDEX IF EXISTS idx_sessions_encrypted_id');
    }
    
    /**
     * Generate encrypted IDs for existing sessions
     */
    private function generateEncryptedIds(): void
    {
        try {
            $sessions = DB::table('activity_sessions')
                ->whereNull('encrypted_id')
                ->orWhere('encrypted_id', '')
                ->get(['id']);

            foreach ($sessions as $session) {
                $encryptedId = encrypt($session->id);
                DB::table('activity_sessions')
                    ->where('id', $session->id)
                    ->update(['encrypted_id' => $encryptedId]);
            }
        } catch (\Exception $e) {
            \Log::warning('Could not generate encrypted IDs for activity sessions: ' . $e->getMessage());
        }
    }
};