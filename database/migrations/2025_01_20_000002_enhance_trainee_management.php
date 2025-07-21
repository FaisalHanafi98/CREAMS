<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceTraineeManagement extends Migration
{
    public function up()
    {
        // Add missing columns to trainees table
        Schema::table('trainees', function (Blueprint $table) {
            if (!Schema::hasColumn('trainees', 'unique_identifier')) {
                $table->string('unique_identifier')->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('trainees', 'status')) {
                $table->enum('status', ['active', 'inactive', 'graduated', 'transferred'])
                      ->default('active')->after('trainee_condition');
            }
            
            if (!Schema::hasColumn('trainees', 'admission_date')) {
                $table->date('admission_date')->nullable()->after('trainee_date_of_birth');
            }
            
            if (!Schema::hasColumn('trainees', 'graduation_date')) {
                $table->date('graduation_date')->nullable()->after('admission_date');
            }
            
            if (!Schema::hasColumn('trainees', 'medical_info')) {
                $table->json('medical_info')->nullable();
            }
            
            if (!Schema::hasColumn('trainees', 'assessment_data')) {
                $table->json('assessment_data')->nullable();
            }
            
            if (!Schema::hasColumn('trainees', 'tags')) {
                $table->json('tags')->nullable();
            }
            
            if (!Schema::hasColumn('trainees', 'last_updated_by')) {
                $table->unsignedBigInteger('last_updated_by')->nullable();
            }
        });

        // Generate unique identifiers for existing trainees
        try {
            $trainees = DB::table('trainees')->whereNull('unique_identifier')->orWhere('unique_identifier', '')->get();
            foreach ($trainees as $trainee) {
                $year = date('Y', strtotime($trainee->created_at ?? 'now'));
                $centreId = $trainee->centre_id ?? 1;
                
                // Get next sequence number
                $existingCount = DB::table('trainees')
                    ->where('unique_identifier', 'LIKE', "TRN{$year}" . sprintf('%03d', $centreId) . '%')
                    ->count();
                    
                $nextNumber = $existingCount + 1;
                $uniqueId = 'TRN' . $year . sprintf('%03d', $centreId) . sprintf('%04d', $nextNumber);
                
                // Ensure uniqueness
                while (DB::table('trainees')->where('unique_identifier', $uniqueId)->exists()) {
                    $nextNumber++;
                    $uniqueId = 'TRN' . $year . sprintf('%03d', $centreId) . sprintf('%04d', $nextNumber);
                }
                
                DB::table('trainees')->where('id', $trainee->id)->update(['unique_identifier' => $uniqueId]);
            }
        } catch (\Exception $e) {
            // Continue if this fails
        }

        // Add indexes for better search performance
        Schema::table('trainees', function (Blueprint $table) {
            // Check if indexes don't already exist to prevent errors
            try {
                $table->index('unique_identifier');
                $table->index(['trainee_first_name', 'trainee_last_name', 'status']);
                $table->index('admission_date');
                $table->index('status');
                $table->index('centre_id');
                
                // Add fulltext index for search
                DB::statement('ALTER TABLE trainees ADD FULLTEXT search_index (trainee_first_name, trainee_last_name, trainee_email, trainee_phone_number)');
                
                // Add foreign key for last_updated_by
                $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
                
                // Add unique constraint to prevent duplicates
                $table->unique(['trainee_first_name', 'trainee_last_name', 'trainee_date_of_birth', 'guardian_phone'], 'unique_trainee_constraint');
                
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        });
        
        // Create trainee audit log table
        if (!Schema::hasTable('trainee_audit_logs')) {
            Schema::create('trainee_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trainee_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamp('created_at');
                
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                
                $table->index(['trainee_id', 'created_at']);
                $table->index('action');
                $table->index('user_id');
            });
        }
        
        // Create trainee documents table
        if (!Schema::hasTable('trainee_documents')) {
            Schema::create('trainee_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trainee_id');
                $table->string('document_type');
                $table->string('document_name');
                $table->string('file_path');
                $table->string('file_size');
                $table->date('expiry_date')->nullable();
                $table->unsignedBigInteger('uploaded_by');
                $table->boolean('is_verified')->default(false);
                $table->timestamps();
                
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
                
                $table->index('trainee_id');
                $table->index('document_type');
                $table->index('expiry_date');
            });
        }
        
        // Create trainee progress tracking table
        if (!Schema::hasTable('trainee_progress')) {
            Schema::create('trainee_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trainee_id');
                $table->unsignedBigInteger('activity_id')->nullable();
                $table->date('assessment_date');
                $table->string('skill_area');
                $table->integer('baseline_score')->nullable();
                $table->integer('current_score');
                $table->integer('target_score')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('assessed_by');
                $table->timestamps();
                
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
                $table->foreign('assessed_by')->references('id')->on('users')->onDelete('cascade');
                
                $table->index(['trainee_id', 'assessment_date']);
                $table->index('skill_area');
                $table->index('assessment_date');
            });
        }

        // Create trainee emergency contacts table
        if (!Schema::hasTable('trainee_emergency_contacts')) {
            Schema::create('trainee_emergency_contacts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trainee_id');
                $table->string('contact_name');
                $table->string('relationship');
                $table->string('phone');
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
                
                $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                $table->index('trainee_id');
                $table->index('is_primary');
            });
        }

        // Add data validation triggers (MySQL specific)
        try {
            // Prevent duplicate trainees with same name and DOB
            DB::unprepared("
                CREATE TRIGGER prevent_duplicate_trainees
                BEFORE INSERT ON trainees
                FOR EACH ROW
                BEGIN
                    DECLARE duplicate_count INT;
                    SELECT COUNT(*) INTO duplicate_count 
                    FROM trainees 
                    WHERE trainee_first_name = NEW.trainee_first_name 
                    AND trainee_last_name = NEW.trainee_last_name
                    AND trainee_date_of_birth = NEW.trainee_date_of_birth 
                    AND centre_id = NEW.centre_id
                    AND (status != 'transferred' OR status IS NULL);
                    
                    IF duplicate_count > 0 THEN
                        SIGNAL SQLSTATE '45000' 
                        SET MESSAGE_TEXT = 'Duplicate trainee detected: same name and date of birth already exists';
                    END IF;
                END
            ");
        } catch (\Exception $e) {
            // Trigger might already exist or MySQL version doesn't support it
        }
    }
    
    public function down()
    {
        // Drop triggers
        try {
            DB::unprepared("DROP TRIGGER IF EXISTS prevent_duplicate_trainees");
        } catch (\Exception $e) {
            // Trigger might not exist
        }

        // Drop tables in reverse order
        Schema::dropIfExists('trainee_emergency_contacts');
        Schema::dropIfExists('trainee_progress');
        Schema::dropIfExists('trainee_documents');
        Schema::dropIfExists('trainee_audit_logs');
        
        // Remove added columns from trainees table
        Schema::table('trainees', function (Blueprint $table) {
            try {
                // Drop foreign key first
                $table->dropForeign(['last_updated_by']);
                
                // Drop unique constraint
                $table->dropUnique('unique_trainee_constraint');
                
                // Drop indexes
                $table->dropIndex(['unique_identifier']);
                $table->dropIndex(['name', 'status']);
                $table->dropIndex(['admission_date']);
                
                // Drop fulltext index
                DB::statement('ALTER TABLE trainees DROP INDEX search_index');
                
                // Drop columns
                $table->dropColumn([
                    'unique_identifier', 'status', 'admission_date', 
                    'graduation_date', 'medical_info', 'assessment_data', 
                    'tags', 'last_updated_by'
                ]);
            } catch (\Exception $e) {
                // Columns might not exist
            }
        });
    }
}