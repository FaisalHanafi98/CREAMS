<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixCentresModule extends Migration
{
    public function up()
    {
        // First, let's check the current centres table structure
        if (Schema::hasTable('centres')) {
            // The centres table already has most columns with centre_ prefix
            // Just add any missing enhancement columns
            Schema::table('centres', function (Blueprint $table) {
                // Add establishment date if missing
                if (!Schema::hasColumn('centres', 'established_date')) {
                    $table->date('established_date')->nullable()->after('is_active');
                }
                
                // Add operating hours if missing
                if (!Schema::hasColumn('centres', 'operating_hours')) {
                    $table->json('operating_hours')->nullable()->after('established_date');
                }
                
                // Add last updated by for audit trail if missing
                if (!Schema::hasColumn('centres', 'last_updated_by')) {
                    $table->unsignedBigInteger('last_updated_by')->nullable()->after('operating_hours');
                }
                
                // Convert centre_facilities to JSON if it's not already
                // This will be handled in a data migration if needed
            });
            
            // Add performance indexes
            $this->addIndexesSafely();
            
            // Fix foreign key constraints
            $this->fixForeignKeyConstraints();
        } else {
            // Create centres table if it doesn't exist
            $this->createCentresTable();
        }
        
        // Create centre audit log table
        if (!Schema::hasTable('centre_audit_logs')) {
            Schema::create('centre_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('centre_id');
                $table->unsignedBigInteger('user_id');
                $table->string('action'); // created, updated, deleted, activated, deactivated
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->text('notes')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
                
                $table->index(['centre_id', 'created_at']);
                $table->index('action');
                $table->index('user_id');
            });
        }
        
        // Create centre statistics cache table
        if (!Schema::hasTable('centre_statistics')) {
            Schema::create('centre_statistics', function (Blueprint $table) {
                $table->id();
                $table->string('centre_id');
                $table->integer('total_users')->default(0);
                $table->integer('total_trainees')->default(0);
                $table->integer('total_activities')->default(0);
                $table->integer('total_assets')->default(0);
                $table->decimal('utilization_rate', 5, 2)->default(0);
                $table->decimal('attendance_rate', 5, 2)->default(0);
                $table->json('monthly_stats')->nullable();
                $table->timestamp('last_calculated')->nullable();
                $table->timestamps();
                
                $table->unique('centre_id');
                $table->index('last_calculated');
            });
        }
    }
    
    protected function createCentresTable()
    {
        Schema::create('centres', function (Blueprint $table) {
            $table->id();
            $table->string('centre_id')->unique();
            $table->string('centre_name');
            $table->enum('centre_type', ['main', 'branch', 'satellite'])->default('branch');
            $table->text('address');
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('manager_name')->nullable();
            $table->integer('capacity')->default(50);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->json('facilities')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->boolean('is_active')->default(true);
            $table->date('established_date')->nullable();
            $table->json('operating_hours')->nullable();
            $table->string('centre_photo')->nullable();
            $table->unsignedBigInteger('last_updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('is_active');
            $table->index('centre_type');
            $table->index(['latitude', 'longitude'], 'idx_location');
        });
    }
    
    protected function addIndexesSafely()
    {
        try {
            Schema::table('centres', function (Blueprint $table) {
                $existingIndexes = $this->getExistingIndexes('centres');
                
                if (!in_array('centres_centre_status_index', $existingIndexes)) {
                    $table->index('centre_status');
                }
                
                if (!in_array('centres_is_active_index', $existingIndexes)) {
                    $table->index('is_active');
                }
                
                if (!in_array('centres_centre_latitude_centre_longitude_index', $existingIndexes)) {
                    $table->index(['centre_latitude', 'centre_longitude'], 'idx_centre_location');
                }
                
                if (!in_array('centres_centre_name_index', $existingIndexes)) {
                    $table->index('centre_name');
                }
            });
        } catch (\Exception $e) {
            \Log::warning('Could not add some indexes to centres table: ' . $e->getMessage());
        }
    }
    
    protected function fixForeignKeyConstraints()
    {
        // Fix foreign keys in related tables
        $this->fixUsersCentreForeignKey();
        $this->fixTraineesCentreForeignKey();
        $this->fixActivitiesCentreForeignKey();
        
        // Add foreign key for last_updated_by
        try {
            if (Schema::hasColumn('centres', 'last_updated_by')) {
                Schema::table('centres', function (Blueprint $table) {
                    $foreignKeys = $this->getExistingForeignKeys('centres');
                    
                    if (!in_array('centres_last_updated_by_foreign', $foreignKeys)) {
                        $table->foreign('last_updated_by')
                              ->references('id')
                              ->on('users')
                              ->onDelete('set null');
                    }
                });
            }
        } catch (\Exception $e) {
            \Log::warning('Could not add foreign key for centres.last_updated_by: ' . $e->getMessage());
        }
    }
    
    protected function fixUsersCentreForeignKey()
    {
        try {
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'centre_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $foreignKeys = $this->getExistingForeignKeys('users');
                    
                    // Drop existing foreign key if it exists
                    if (in_array('users_centre_id_foreign', $foreignKeys)) {
                        $table->dropForeign(['centre_id']);
                    }
                    
                    // Add correct foreign key
                    $table->foreign('centre_id')
                          ->references('id')
                          ->on('centres')
                          ->onDelete('restrict');
                });
            }
        } catch (\Exception $e) {
            \Log::warning('Could not fix users.centre_id foreign key: ' . $e->getMessage());
        }
    }
    
    protected function fixTraineesCentreForeignKey()
    {
        try {
            if (Schema::hasTable('trainees') && Schema::hasColumn('trainees', 'centre_id')) {
                Schema::table('trainees', function (Blueprint $table) {
                    $foreignKeys = $this->getExistingForeignKeys('trainees');
                    
                    // Drop existing foreign key if it exists
                    if (in_array('trainees_centre_id_foreign', $foreignKeys)) {
                        $table->dropForeign(['centre_id']);
                    }
                    
                    // Add correct foreign key
                    $table->foreign('centre_id')
                          ->references('id')
                          ->on('centres')
                          ->onDelete('restrict');
                });
            }
        } catch (\Exception $e) {
            \Log::warning('Could not fix trainees.centre_id foreign key: ' . $e->getMessage());
        }
    }
    
    protected function fixActivitiesCentreForeignKey()
    {
        try {
            if (Schema::hasTable('activities') && Schema::hasColumn('activities', 'centre_id')) {
                Schema::table('activities', function (Blueprint $table) {
                    $foreignKeys = $this->getExistingForeignKeys('activities');
                    
                    // Drop existing foreign key if it exists
                    if (in_array('activities_centre_id_foreign', $foreignKeys)) {
                        $table->dropForeign(['centre_id']);
                    }
                    
                    // Add correct foreign key
                    $table->foreign('centre_id')
                          ->references('id')
                          ->on('centres')
                          ->onDelete('restrict');
                });
            }
        } catch (\Exception $e) {
            \Log::warning('Could not fix activities.centre_id foreign key: ' . $e->getMessage());
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
        // Drop new tables
        Schema::dropIfExists('centre_statistics');
        Schema::dropIfExists('centre_audit_logs');
        
        // Remove added columns from centres table
        if (Schema::hasTable('centres')) {
            Schema::table('centres', function (Blueprint $table) {
                $columnsToRemove = [
                    'latitude', 'longitude', 'facilities', 'is_active',
                    'established_date', 'operating_hours', 'centre_photo', 'last_updated_by'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('centres', $column)) {
                        try {
                            if ($column === 'last_updated_by') {
                                $table->dropForeign(['last_updated_by']);
                            }
                            $table->dropColumn($column);
                        } catch (\Exception $e) {
                            // Column might not exist or have dependencies
                        }
                    }
                }
                
                // Drop indexes
                try {
                    $table->dropIndex(['status']);
                    $table->dropIndex(['is_active']);
                    $table->dropIndex(['centre_type']);
                    $table->dropIndex('idx_centre_location');
                } catch (\Exception $e) {
                    // Indexes might not exist
                }
            });
        }
    }
}