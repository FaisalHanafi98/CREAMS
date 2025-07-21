<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixAttendanceTables extends Migration
{
    public function up()
    {
        // Create session_enrollments table if missing
        if (!Schema::hasTable('session_enrollments')) {
            Schema::create('session_enrollments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('session_id');
                $table->unsignedBigInteger('trainee_id');
                $table->enum('attendance_status', ['present', 'absent', 'late', 'excused'])->nullable();
                $table->integer('participation_score')->nullable();
                $table->text('progress_notes')->nullable();
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamps();
                
                $table->foreign('session_id')->references('id')->on('activity_sessions')->onDelete('cascade');
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->unique(['session_id', 'trainee_id']);
                
                $table->index(['session_id', 'attendance_status']);
                $table->index(['trainee_id', 'attendance_status']);
                $table->index('checked_in_at');
            });
        }

        // Fix attendance table
        $this->fixAttendanceTable();
        
        // Create activity_sessions table if missing
        if (!Schema::hasTable('activity_sessions')) {
            Schema::create('activity_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_id');
                $table->string('session_name');
                $table->date('session_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('venue')->nullable();
                $table->unsignedBigInteger('teacher_id')->nullable();
                $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
                $table->boolean('attendance_marked')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('users')->onDelete('set null');
                
                $table->index(['activity_id', 'session_date']);
                $table->index(['teacher_id', 'session_date']);
                $table->index(['session_date', 'status']);
            });
        }
    }
    
    protected function fixAttendanceTable()
    {
        // The attendance table already exists with the correct structure
        // Just add any missing indexes for performance
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table) {
                // Add indexes for performance (with error handling)
                try {
                    $existingIndexes = $this->getExistingIndexes('attendance');
                    
                    if (!in_array('attendance_trainee_id_attendance_date_index', $existingIndexes)) {
                        $table->index(['trainee_id', 'attendance_date'], 'idx_trainee_date_perf');
                    }
                    
                    if (!in_array('attendance_activity_id_attendance_date_index', $existingIndexes)) {
                        $table->index(['activity_id', 'attendance_date'], 'idx_activity_date_perf');
                    }
                    
                    if (!in_array('attendance_attendance_status_index', $existingIndexes)) {
                        $table->index('attendance_status', 'idx_status_perf');
                    }
                    
                    if (!in_array('attendance_session_id_index', $existingIndexes)) {
                        $table->index('session_id', 'idx_session_perf');
                    }
                } catch (\Exception $e) {
                    // Indexes might already exist or have different names
                    \Log::info('Could not add some indexes to attendance table: ' . $e->getMessage());
                }
            });
            
            // Add foreign keys safely
            $this->addForeignKeysSafely();
        }
    }
    
    protected function addForeignKeysSafely()
    {
        try {
            Schema::table('attendance', function (Blueprint $table) {
                // Check if foreign keys exist before adding
                $foreignKeys = $this->getExistingForeignKeys('attendance');
                
                // The attendance table uses 'recorded_by' instead of 'marked_by'
                if (!in_array('attendance_recorded_by_foreign', $foreignKeys)) {
                    $table->foreign('recorded_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        } catch (\Exception $e) {
            // Foreign keys might already exist or tables might not exist yet
            \Log::warning('Could not add foreign keys to attendance table: ' . $e->getMessage());
        }
    }
    
    protected function getExistingIndexes($table)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            return array_column($indexes, 'Key_name');
        } catch (\Exception $e) {
            return [];
        }
    }
    
    protected function getExistingForeignKeys($table)
    {
        try {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [env('DB_DATABASE'), $table]);
            
            return array_column($foreignKeys, 'CONSTRAINT_NAME');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function down()
    {
        Schema::dropIfExists('session_enrollments');
        
        if (Schema::hasTable('activity_sessions')) {
            Schema::dropIfExists('activity_sessions');
        }
        
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table) {
                try {
                    $table->dropForeign(['session_id']);
                    $table->dropForeign(['marked_by']);
                    $table->dropIndex('idx_trainee_date');
                    $table->dropIndex('idx_activity_date');
                    $table->dropIndex('idx_session');
                    $table->dropColumn(['session_id', 'marked_by']);
                } catch (\Exception $e) {
                    // Columns/indexes might not exist
                }
            });
        }
    }
}