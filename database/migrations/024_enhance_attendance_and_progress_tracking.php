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
        // Enhance trainee conditions tracking
        if (Schema::hasTable('trainees')) {
            Schema::table('trainees', function (Blueprint $table) {
                // Add specific condition fields if they don't exist
                if (!Schema::hasColumn('trainees', 'hearing_impaired')) {
                    $table->boolean('hearing_impaired')->default(false)->after('trainee_condition');
                }
                if (!Schema::hasColumn('trainees', 'visually_impaired')) {
                    $table->boolean('visually_impaired')->default(false)->after('hearing_impaired');
                }
                if (!Schema::hasColumn('trainees', 'mobility_impaired')) {
                    $table->boolean('mobility_impaired')->default(false)->after('visually_impaired');
                }
                if (!Schema::hasColumn('trainees', 'cognitive_impairment')) {
                    $table->enum('cognitive_impairment', ['none', 'mild', 'moderate', 'severe'])->default('none')->after('mobility_impaired');
                }
                if (!Schema::hasColumn('trainees', 'behavioral_support_needed')) {
                    $table->boolean('behavioral_support_needed')->default(false)->after('cognitive_impairment');
                }
                if (!Schema::hasColumn('trainees', 'communication_method')) {
                    $table->enum('communication_method', ['verbal', 'sign_language', 'visual_aids', 'assistive_device', 'mixed'])->default('verbal')->after('behavioral_support_needed');
                }
                if (!Schema::hasColumn('trainees', 'special_accommodations')) {
                    $table->text('special_accommodations')->nullable()->after('communication_method');
                }
            });
        }

        // Enhance activity sessions for better tracking
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                // Add session-specific tracking fields
                if (!Schema::hasColumn('activity_sessions', 'session_objectives')) {
                    $table->json('session_objectives')->nullable()->after('session_notes');
                }
                if (!Schema::hasColumn('activity_sessions', 'completed_objectives')) {
                    $table->json('completed_objectives')->nullable()->after('session_objectives');
                }
                if (!Schema::hasColumn('activity_sessions', 'session_rating')) {
                    $table->decimal('session_rating', 3, 2)->nullable()->after('completed_objectives');
                }
                if (!Schema::hasColumn('activity_sessions', 'attendance_marked')) {
                    $table->boolean('attendance_marked')->default(false)->after('session_rating');
                }
                if (!Schema::hasColumn('activity_sessions', 'marked_by')) {
                    $table->unsignedBigInteger('marked_by')->nullable()->after('attendance_marked');
                }
                if (!Schema::hasColumn('activity_sessions', 'session_feedback')) {
                    $table->text('session_feedback')->nullable()->after('marked_by');
                }
            });
        }

        // Enhanced attendance tracking with progress notes
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table) {
                // Add detailed attendance tracking
                if (!Schema::hasColumn('attendance', 'arrival_time')) {
                    $table->time('arrival_time')->nullable()->after('attendance_status');
                }
                if (!Schema::hasColumn('attendance', 'departure_time')) {
                    $table->time('departure_time')->nullable()->after('arrival_time');
                }
                if (!Schema::hasColumn('attendance', 'participation_level_enum')) {
                    $table->enum('participation_level_enum', ['excellent', 'good', 'fair', 'poor', 'unable_to_participate'])->nullable()->after('departure_time');
                }
                if (!Schema::hasColumn('attendance', 'mood_rating')) {
                    $table->enum('mood_rating', ['very_happy', 'happy', 'neutral', 'sad', 'very_upset'])->nullable()->after('participation_level_enum');
                }
                if (!Schema::hasColumn('attendance', 'progress_notes')) {
                    $table->text('progress_notes')->nullable()->after('mood_rating');
                }
                if (!Schema::hasColumn('attendance', 'parent_feedback_required')) {
                    $table->boolean('parent_feedback_required')->default(false)->after('progress_notes');
                }
                if (!Schema::hasColumn('attendance', 'follow_up_needed')) {
                    $table->boolean('follow_up_needed')->default(false)->after('parent_feedback_required');
                }
                if (!Schema::hasColumn('attendance', 'recorded_by')) {
                    $table->unsignedBigInteger('recorded_by')->nullable()->after('follow_up_needed');
                }
            });
        }

        // Create session progress tracking table
        if (!Schema::hasTable('session_progress')) {
            Schema::create('session_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('activity_session_id');
                $table->unsignedBigInteger('trainee_id');
                $table->unsignedBigInteger('activity_id');
                
                // Progress tracking
                $table->json('skills_practiced')->nullable(); // Skills worked on during session
                $table->json('achievements')->nullable(); // What they accomplished
                $table->json('challenges')->nullable(); // Difficulties encountered
                $table->json('adaptations_made')->nullable(); // How activity was modified for trainee
                
                // Detailed assessments
                $table->enum('engagement_level', ['very_high', 'high', 'moderate', 'low', 'very_low'])->nullable();
                $table->enum('comprehension_level', ['excellent', 'good', 'fair', 'poor', 'unable_to_assess'])->nullable();
                $table->enum('independence_level', ['independent', 'minimal_help', 'moderate_help', 'maximum_help', 'dependent'])->nullable();
                
                // Specific progress metrics (scale 1-5)
                $table->tinyInteger('motor_skills_progress')->nullable(); // 1-5 scale
                $table->tinyInteger('communication_progress')->nullable();
                $table->tinyInteger('social_interaction_progress')->nullable();
                $table->tinyInteger('cognitive_progress')->nullable();
                $table->tinyInteger('behavioral_progress')->nullable();
                
                // Goals and outcomes
                $table->text('session_goals_met')->nullable();
                $table->text('next_session_focus')->nullable();
                $table->text('recommendations')->nullable();
                $table->text('parent_communication')->nullable();
                
                // Metadata
                $table->unsignedBigInteger('assessed_by'); // Staff member who made assessment
                $table->timestamp('assessment_date');
                $table->text('additional_notes')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['activity_session_id']);
                $table->index(['trainee_id']);
                $table->index(['activity_id']);
                $table->index(['assessment_date']);
                $table->index(['assessed_by']);
                
                // Composite indexes for common queries
                $table->index(['trainee_id', 'activity_id']);
                $table->index(['activity_session_id', 'trainee_id']);
            });
        }

        // Create attendance alerts table for tracking missed sessions
        if (!Schema::hasTable('attendance_alerts')) {
            Schema::create('attendance_alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trainee_id');
                $table->unsignedBigInteger('activity_id');
                $table->enum('alert_type', ['consecutive_absences', 'frequent_tardiness', 'pattern_concern', 'medical_concern']);
                $table->integer('absence_count')->default(0);
                $table->date('last_attendance_date')->nullable();
                $table->text('alert_description');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->enum('status', ['active', 'resolved', 'dismissed'])->default('active');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('assigned_to')->nullable(); // Staff member to follow up
                $table->text('action_taken')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                
                $table->index(['trainee_id']);
                $table->index(['activity_id']);
                $table->index(['alert_type']);
                $table->index(['status']);
                $table->index(['priority']);
                $table->index(['assigned_to']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('attendance_alerts');
        Schema::dropIfExists('session_progress');
        
        // Remove added columns from existing tables
        if (Schema::hasTable('trainees')) {
            Schema::table('trainees', function (Blueprint $table) {
                $columns = ['hearing_impaired', 'visually_impaired', 'mobility_impaired', 
                           'cognitive_impairment', 'behavioral_support_needed', 'communication_method', 
                           'special_accommodations'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('trainees', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $columns = ['session_objectives', 'completed_objectives', 'session_rating', 
                           'attendance_marked', 'marked_by', 'session_feedback'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('activity_sessions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
        
        if (Schema::hasTable('attendance')) {
            Schema::table('attendance', function (Blueprint $table) {
                $columns = ['arrival_time', 'departure_time', 'participation_level_enum', 'mood_rating', 
                           'progress_notes', 'parent_feedback_required', 'follow_up_needed', 'recorded_by'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('attendance', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};