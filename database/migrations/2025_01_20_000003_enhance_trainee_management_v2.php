<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class EnhanceTraineeManagementV2 extends Migration
{
    public function up()
    {
        // Step 1: Add missing columns safely
        if (!Schema::hasColumn('trainees', 'unique_identifier')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->string('unique_identifier')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('trainees', 'status')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->enum('status', ['active', 'inactive', 'graduated', 'transferred'])
                      ->default('active');
            });
        }

        if (!Schema::hasColumn('trainees', 'admission_date')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->date('admission_date')->nullable();
            });
        }

        if (!Schema::hasColumn('trainees', 'graduation_date')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->date('graduation_date')->nullable();
            });
        }

        if (!Schema::hasColumn('trainees', 'medical_info')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->json('medical_info')->nullable();
            });
        }

        if (!Schema::hasColumn('trainees', 'assessment_data')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->json('assessment_data')->nullable();
            });
        }

        if (!Schema::hasColumn('trainees', 'tags')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->json('tags')->nullable();
            });
        }

        if (!Schema::hasColumn('trainees', 'last_updated_by')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->unsignedBigInteger('last_updated_by')->nullable();
            });
        }

        // Step 2: Generate unique identifiers for existing trainees
        $this->generateUniqueIdentifiers();

        // Step 3: Create new tables if they don't exist
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
                
                $table->index(['trainee_id', 'created_at']);
                $table->index('action');
                $table->index('user_id');
            });
        }
        
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
                
                $table->index('trainee_id');
                $table->index('document_type');
                $table->index('expiry_date');
            });
        }
        
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
                
                $table->index(['trainee_id', 'assessment_date']);
                $table->index('skill_area');
                $table->index('assessment_date');
            });
        }

        // Step 4: Add indexes and constraints safely
        $this->addIndexesSafely();
        $this->addForeignKeysSafely();
    }
    
    public function down()
    {
        // Drop new tables
        Schema::dropIfExists('trainee_progress');
        Schema::dropIfExists('trainee_documents');
        Schema::dropIfExists('trainee_audit_logs');
        
        // Remove added columns
        if (Schema::hasColumn('trainees', 'unique_identifier')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('unique_identifier');
            });
        }

        if (Schema::hasColumn('trainees', 'status')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasColumn('trainees', 'admission_date')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('admission_date');
            });
        }

        if (Schema::hasColumn('trainees', 'graduation_date')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('graduation_date');
            });
        }

        if (Schema::hasColumn('trainees', 'medical_info')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('medical_info');
            });
        }

        if (Schema::hasColumn('trainees', 'assessment_data')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('assessment_data');
            });
        }

        if (Schema::hasColumn('trainees', 'tags')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('tags');
            });
        }

        if (Schema::hasColumn('trainees', 'last_updated_by')) {
            Schema::table('trainees', function (Blueprint $table) {
                $table->dropColumn('last_updated_by');
            });
        }
    }

    private function generateUniqueIdentifiers()
    {
        try {
            $trainees = DB::table('trainees')
                ->whereNull('unique_identifier')
                ->orWhere('unique_identifier', '')
                ->get();
                
            foreach ($trainees as $trainee) {
                $year = date('Y', strtotime($trainee->created_at ?? 'now'));
                $centreId = $trainee->centre_id ?? 1;
                
                // Find next available sequence number
                $pattern = "TRN{$year}" . sprintf('%03d', $centreId) . '%';
                $existingIds = DB::table('trainees')
                    ->where('unique_identifier', 'LIKE', $pattern)
                    ->pluck('unique_identifier');
                    
                $usedNumbers = [];
                foreach ($existingIds as $id) {
                    if (preg_match('/TRN' . $year . sprintf('%03d', $centreId) . '(\d{4})/', $id, $matches)) {
                        $usedNumbers[] = intval($matches[1]);
                    }
                }
                
                $nextNumber = 1;
                while (in_array($nextNumber, $usedNumbers)) {
                    $nextNumber++;
                }
                
                $uniqueId = 'TRN' . $year . sprintf('%03d', $centreId) . sprintf('%04d', $nextNumber);
                
                DB::table('trainees')
                    ->where('id', $trainee->id)
                    ->update(['unique_identifier' => $uniqueId]);
            }
        } catch (\Exception $e) {
            // Log error but continue
            \Log::warning('Failed to generate unique identifiers: ' . $e->getMessage());
        }
    }

    private function addIndexesSafely()
    {
        $indexes = [
            'trainees_unique_identifier_index',
            'trainees_admission_date_index',
            'trainees_status_index'
        ];

        $existingIndexes = $this->getExistingIndexes('trainees');

        try {
            Schema::table('trainees', function (Blueprint $table) use ($existingIndexes) {
                if (!in_array('trainees_unique_identifier_index', $existingIndexes)) {
                    $table->index('unique_identifier');
                }
                
                if (!in_array('trainees_admission_date_index', $existingIndexes)) {
                    $table->index('admission_date');
                }
                
                if (!in_array('trainees_status_index', $existingIndexes)) {
                    $table->index('status');
                }
            });
        } catch (\Exception $e) {
            // Log error but continue
            \Log::warning('Failed to add some indexes: ' . $e->getMessage());
        }
    }

    private function addForeignKeysSafely()
    {
        try {
            $foreignKeys = $this->getExistingForeignKeys('trainees');
            
            if (!in_array('trainees_last_updated_by_foreign', $foreignKeys)) {
                Schema::table('trainees', function (Blueprint $table) {
                    $table->foreign('last_updated_by')
                          ->references('id')
                          ->on('users')
                          ->onDelete('set null');
                });
            }

            // Add foreign keys for new tables
            if (Schema::hasTable('trainee_audit_logs')) {
                Schema::table('trainee_audit_logs', function (Blueprint $table) {
                    $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }

            if (Schema::hasTable('trainee_documents')) {
                Schema::table('trainee_documents', function (Blueprint $table) {
                    $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                    $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
                });
            }

            if (Schema::hasTable('trainee_progress')) {
                Schema::table('trainee_progress', function (Blueprint $table) {
                    $table->foreign('trainee_id')->references('id')->on('trainees')->onDelete('cascade');
                    $table->foreign('assessed_by')->references('id')->on('users')->onDelete('cascade');
                    if (Schema::hasTable('activities')) {
                        $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
                    }
                });
            }

        } catch (\Exception $e) {
            // Log error but continue
            \Log::warning('Failed to add some foreign keys: ' . $e->getMessage());
        }
    }

    private function getExistingIndexes($table)
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            return array_column($indexes, 'Key_name');
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getExistingForeignKeys($table)
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
}