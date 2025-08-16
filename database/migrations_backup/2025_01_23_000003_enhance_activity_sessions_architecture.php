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
        // First, check if the table exists and add missing columns
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                // Add unique session identifier if not exists
                if (!Schema::hasColumn('activity_sessions', 'session_code')) {
                    $table->string('session_code')->unique()->after('id');
                }
                
                // Add venue field if not exists (mapped from session_location)
                if (!Schema::hasColumn('activity_sessions', 'venue')) {
                    $table->string('venue')->nullable()->after('session_location');
                }
                
                // Add supervisor/instructor tracking
                if (!Schema::hasColumn('activity_sessions', 'supervisor_id')) {
                    $table->unsignedBigInteger('supervisor_id')->nullable()->after('instructor_id');
                }
                
                // Add max participants field if not exists
                if (!Schema::hasColumn('activity_sessions', 'max_participants')) {
                    $table->integer('max_participants')->default(20)->after('current_participants');
                }
                
                // Add start_time and end_time fields that match model expectations
                if (!Schema::hasColumn('activity_sessions', 'start_time')) {
                    $table->time('start_time')->nullable()->after('session_start_time');
                }
                
                if (!Schema::hasColumn('activity_sessions', 'end_time')) {
                    $table->time('end_time')->nullable()->after('session_end_time');
                }
                
                // Add status field that matches model expectations
                if (!Schema::hasColumn('activity_sessions', 'status')) {
                    $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled')->after('session_status');
                }
                
                // Add teacher_id field that matches model expectations
                if (!Schema::hasColumn('activity_sessions', 'teacher_id')) {
                    $table->unsignedBigInteger('teacher_id')->nullable()->after('instructor_id');
                }
                
                // Add attendance tracking
                if (!Schema::hasColumn('activity_sessions', 'attendance_marked')) {
                    $table->boolean('attendance_marked')->default(false)->after('current_participants');
                }
                
                // Add notes field for compatibility
                if (!Schema::hasColumn('activity_sessions', 'notes')) {
                    $table->text('notes')->nullable()->after('session_notes');
                }
                
                // Add venue conflict prevention indexes
                $table->index(['venue', 'session_date', 'start_time'], 'venue_time_conflict_idx');
                $table->index(['supervisor_id', 'session_date', 'start_time'], 'supervisor_time_conflict_idx');
                $table->index(['teacher_id', 'session_date', 'start_time'], 'teacher_time_conflict_idx');
            });
            
            // Sync data from old columns to new columns
            $this->syncColumnData();
            
        } else {
            // Create new enhanced table structure
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_code')->unique();
                $table->unsignedBigInteger('activity_id');
                $table->string('session_name');
                $table->text('session_description')->nullable();
                $table->date('session_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('venue');
                $table->integer('max_participants')->default(20);
                $table->integer('current_participants')->default(0);
                $table->boolean('attendance_marked')->default(false);
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->unsignedBigInteger('supervisor_id')->nullable();
                $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
                $table->text('session_objectives')->nullable();
                $table->text('notes')->nullable();
                $table->json('session_materials')->nullable();
                $table->timestamps();
                
                // Primary indexes
                $table->index(['session_code']);
                $table->index(['activity_id']);
                $table->index(['status']);
                $table->index(['session_date']);
                $table->index(['teacher_id']);
                $table->index(['supervisor_id']);
                
                // Conflict prevention indexes
                $table->index(['venue', 'session_date', 'start_time'], 'venue_time_conflict_idx');
                $table->index(['supervisor_id', 'session_date', 'start_time'], 'supervisor_time_conflict_idx');
                $table->index(['teacher_id', 'session_date', 'start_time'], 'teacher_time_conflict_idx');
                
                // Foreign key constraints
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                // Remove indexes first
                $table->dropIndex('venue_time_conflict_idx');
                $table->dropIndex('supervisor_time_conflict_idx'); 
                $table->dropIndex('teacher_time_conflict_idx');
                
                // Remove added columns
                if (Schema::hasColumn('activity_sessions', 'session_code')) {
                    $table->dropColumn('session_code');
                }
                if (Schema::hasColumn('activity_sessions', 'venue')) {
                    $table->dropColumn('venue');
                }
                if (Schema::hasColumn('activity_sessions', 'supervisor_id')) {
                    $table->dropColumn('supervisor_id');
                }
                if (Schema::hasColumn('activity_sessions', 'start_time')) {
                    $table->dropColumn('start_time');
                }
                if (Schema::hasColumn('activity_sessions', 'end_time')) {
                    $table->dropColumn('end_time');
                }
                if (Schema::hasColumn('activity_sessions', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('activity_sessions', 'teacher_id')) {
                    $table->dropColumn('teacher_id');
                }
                if (Schema::hasColumn('activity_sessions', 'attendance_marked')) {
                    $table->dropColumn('attendance_marked');
                }
                if (Schema::hasColumn('activity_sessions', 'notes')) {
                    $table->dropColumn('notes');
                }
            });
        }
    }
    
    /**
     * Sync data from old columns to new columns
     */
    private function syncColumnData(): void
    {
        try {
            // Generate session codes for existing sessions
            DB::statement("
                UPDATE activity_sessions 
                SET session_code = CONCAT('SES', LPAD(id, 6, '0'))
                WHERE session_code IS NULL OR session_code = ''
            ");
            
            // Sync venue from session_location
            DB::statement("
                UPDATE activity_sessions 
                SET venue = session_location
                WHERE venue IS NULL AND session_location IS NOT NULL
            ");
            
            // Sync start_time from session_start_time
            DB::statement("
                UPDATE activity_sessions 
                SET start_time = session_start_time
                WHERE start_time IS NULL AND session_start_time IS NOT NULL
            ");
            
            // Sync end_time from session_end_time
            DB::statement("
                UPDATE activity_sessions 
                SET end_time = session_end_time
                WHERE end_time IS NULL AND session_end_time IS NOT NULL
            ");
            
            // Sync status from session_status
            DB::statement("
                UPDATE activity_sessions 
                SET status = session_status
                WHERE status IS NULL AND session_status IS NOT NULL
            ");
            
            // Sync teacher_id from instructor_id
            DB::statement("
                UPDATE activity_sessions 
                SET teacher_id = instructor_id
                WHERE teacher_id IS NULL AND instructor_id IS NOT NULL
            ");
            
            // Sync notes from session_notes
            DB::statement("
                UPDATE activity_sessions 
                SET notes = session_notes
                WHERE notes IS NULL AND session_notes IS NOT NULL
            ");
            
        } catch (\Exception $e) {
            \Log::warning('Session data sync had issues: ' . $e->getMessage());
        }
    }
};