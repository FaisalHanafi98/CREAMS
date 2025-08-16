<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RestructureActivityModule extends Migration
{
    public function up()
    {
        // Drop redundant tables if they exist
        Schema::dropIfExists('trainee_activities');
        Schema::dropIfExists('rehabilitation_activities');
        
        // Create optimized activities table
        Schema::create('activities_new', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', [
                'Physical Therapy',
                'Occupational Therapy',
                'Speech Therapy',
                'Behavioral Therapy',
                'Sensory Integration',
                'Mathematics',
                'Literacy',
                'Science',
                'Computer Skills',
                'Art & Creativity',
                'Music Therapy',
                'Social Skills',
                'Life Skills',
                'Vocational Training'
            ]);
            $table->enum('difficulty_level', ['Beginner', 'Intermediate', 'Advanced']);
            $table->integer('max_participants')->default(20);
            $table->integer('min_participants')->default(1);
            $table->integer('duration_minutes')->default(60);
            $table->json('required_materials')->nullable();
            $table->json('learning_objectives')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('centre_id');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('centre_id');
            $table->index('created_by');
        });
        
        // Create optimized sessions table
        Schema::create('activity_sessions_new', function (Blueprint $table) {
            $table->id();
            $table->string('session_code')->unique();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('teacher_id');
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue');
            $table->integer('capacity');
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])
                  ->default('scheduled');
            $table->text('notes')->nullable();
            $table->json('session_data')->nullable();
            $table->timestamps();
            
            $table->foreign('activity_id')->references('id')->on('activities_new')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users');
            
            $table->index(['session_date', 'status']);
            $table->index(['teacher_id', 'session_date']);
            $table->unique(['teacher_id', 'session_date', 'start_time'], 'no_double_booking');
        });
        
        // Create optimized enrollments table
        Schema::create('activity_enrollments_new', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trainee_id');
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('session_id');
            $table->enum('enrollment_status', ['enrolled', 'completed', 'dropped', 'absent'])
                  ->default('enrolled');
            $table->date('enrollment_date');
            $table->boolean('attendance_marked')->default(false);
            $table->integer('participation_score')->nullable();
            $table->text('progress_notes')->nullable();
            $table->json('assessment_data')->nullable();
            $table->timestamps();
            
            $table->foreign('trainee_id')->references('id')->on('trainees');
            $table->foreign('activity_id')->references('id')->on('activities_new');
            $table->foreign('session_id')->references('id')->on('activity_sessions_new');
            
            $table->unique(['trainee_id', 'session_id'], 'unique_enrollment');
            $table->index(['session_id', 'enrollment_status']);
            $table->index(['trainee_id', 'enrollment_status']);
        });
        
        // Migrate existing data
        $this->migrateExistingData();
    }
    
    public function down()
    {
        Schema::dropIfExists('activity_enrollments_new');
        Schema::dropIfExists('activity_sessions_new');
        Schema::dropIfExists('activities_new');
    }
    
    private function migrateExistingData()
    {
        try {
            // Migrate activities
            if (Schema::hasTable('activities')) {
                // Get existing activities
                $existingActivities = DB::table('activities')->get();
                
                foreach ($existingActivities as $activity) {
                    // Generate activity code if not exists
                    $activityCode = $activity->activity_code ?? $this->generateActivityCode($activity->category ?? 'General');
                    
                    DB::table('activities_new')->insert([
                        'id' => $activity->id,
                        'activity_code' => $activityCode,
                        'name' => $activity->name ?? 'Unknown Activity',
                        'description' => $activity->description,
                        'category' => $this->mapCategory($activity->category ?? 'Life Skills'),
                        'difficulty_level' => $activity->difficulty_level ?? 'Beginner',
                        'max_participants' => $activity->max_participants ?? 20,
                        'min_participants' => $activity->min_participants ?? 1,
                        'duration_minutes' => $activity->duration_minutes ?? 60,
                        'required_materials' => $activity->required_materials ? json_encode(json_decode($activity->required_materials, true) ?: []) : json_encode([]),
                        'learning_objectives' => $activity->learning_objectives ? json_encode(json_decode($activity->learning_objectives, true) ?: []) : json_encode([]),
                        'is_active' => $activity->is_active ?? true,
                        'centre_id' => $activity->centre_id ?? 1,
                        'created_by' => $activity->created_by ?? 1,
                        'created_at' => $activity->created_at ?? now(),
                        'updated_at' => $activity->updated_at ?? now()
                    ]);
                }
            }
            
            // Migrate sessions
            if (Schema::hasTable('activity_sessions')) {
                $existingSessions = DB::table('activity_sessions')->get();
                
                foreach ($existingSessions as $session) {
                    // Generate session code if not exists
                    $sessionCode = $session->session_code ?? 'SES-' . $session->id . '-' . date('ymd');
                    
                    DB::table('activity_sessions_new')->insert([
                        'id' => $session->id,
                        'session_code' => $sessionCode,
                        'activity_id' => $session->activity_id,
                        'teacher_id' => $session->teacher_id ?? $session->user_id ?? 1,
                        'session_date' => $session->session_date ?? $session->date ?? now()->toDateString(),
                        'start_time' => $session->start_time ?? '09:00:00',
                        'end_time' => $session->end_time ?? '10:00:00',
                        'venue' => $session->venue ?? 'Room 1',
                        'capacity' => $session->capacity ?? 20,
                        'status' => $session->status ?? 'scheduled',
                        'notes' => $session->notes,
                        'session_data' => $session->session_data ? json_encode(json_decode($session->session_data, true) ?: []) : json_encode([]),
                        'created_at' => $session->created_at ?? now(),
                        'updated_at' => $session->updated_at ?? now()
                    ]);
                }
            }
            
            // Migrate enrollments if exists
            if (Schema::hasTable('activity_enrollments')) {
                $existingEnrollments = DB::table('activity_enrollments')->get();
                
                foreach ($existingEnrollments as $enrollment) {
                    DB::table('activity_enrollments_new')->insert([
                        'id' => $enrollment->id,
                        'trainee_id' => $enrollment->trainee_id,
                        'activity_id' => $enrollment->activity_id,
                        'session_id' => $enrollment->session_id ?? 1,
                        'enrollment_status' => $enrollment->enrollment_status ?? 'enrolled',
                        'enrollment_date' => $enrollment->enrollment_date ?? now()->toDateString(),
                        'attendance_marked' => $enrollment->attendance_marked ?? false,
                        'participation_score' => $enrollment->participation_score,
                        'progress_notes' => $enrollment->progress_notes,
                        'assessment_data' => $enrollment->assessment_data ? json_encode(json_decode($enrollment->assessment_data, true) ?: []) : json_encode([]),
                        'created_at' => $enrollment->created_at ?? now(),
                        'updated_at' => $enrollment->updated_at ?? now()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            // Log migration issues but don't fail the migration
            \Log::warning('Activity module migration had issues: ' . $e->getMessage());
        }
    }
    
    private function generateActivityCode($category)
    {
        $prefix = substr(strtoupper(str_replace(' ', '', $category)), 0, 3);
        $year = date('y');
        $month = date('m');
        $sequence = DB::table('activities_new')->where('activity_code', 'LIKE', "{$prefix}-{$year}{$month}-%")->count() + 1;
        
        return sprintf("{$prefix}-{$year}{$month}-%04d", $sequence);
    }
    
    private function mapCategory($oldCategory)
    {
        $categoryMap = [
            'Physical Therapy' => 'Physical Therapy',
            'Occupational Therapy' => 'Occupational Therapy',
            'Speech Therapy' => 'Speech Therapy',
            'Behavioral Therapy' => 'Behavioral Therapy',
            'Sensory Integration' => 'Sensory Integration',
            'Mathematics' => 'Mathematics',
            'Literacy' => 'Literacy',
            'Science' => 'Science',
            'Computer Skills' => 'Computer Skills',
            'Art & Creativity' => 'Art & Creativity',
            'Music Therapy' => 'Music Therapy',
            'Social Skills' => 'Social Skills',
            'Life Skills' => 'Life Skills',
            'Vocational Training' => 'Vocational Training',
            // Map old category names to new ones
            'Rehabilitation' => 'Physical Therapy',
            'Education' => 'Life Skills',
            'Academic' => 'Mathematics',
            'Therapy' => 'Physical Therapy',
            'Training' => 'Vocational Training'
        ];
        
        return $categoryMap[$oldCategory] ?? 'Life Skills';
    }
}