<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // Activities table indexes
        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                // Check if indexes exist before adding them
                $this->addIndexIfNotExists('activities', ['status', 'created_at'], 'idx_activities_status_created');
                $this->addIndexIfNotExists('activities', ['category', 'status'], 'idx_activities_category_status');
                $this->addIndexIfNotExists('activities', ['created_by', 'status'], 'idx_activities_creator_status');
                $this->addIndexIfNotExists('activities', ['difficulty_level'], 'idx_activities_difficulty');
                
                // Add fulltext index only if MySQL/MariaDB version supports it and the columns exist
                if ($this->supportsFTIndex() && 
                    Schema::hasColumn('activities', 'activity_name') && 
                    Schema::hasColumn('activities', 'description')) {
                    try {
                        $this->addFullTextIndexIfNotExists('activities', ['activity_name', 'description'], 'ft_activities_search');
                    } catch (\Exception $e) {
                        Log::warning('Could not add fulltext index to activities: ' . $e->getMessage());
                    }
                }
            });
        } else {
            Log::warning('Activities table does not exist, skipping index creation');
        }

        // Rehabilitation activities table indexes
        if (Schema::hasTable('rehabilitation_activities')) {
            Schema::table('rehabilitation_activities', function (Blueprint $table) {
                $this->addIndexIfNotExists('rehabilitation_activities', ['category', 'status'], 'idx_rehab_category_status');
                $this->addIndexIfNotExists('rehabilitation_activities', ['status', 'created_at'], 'idx_rehab_status_created');
                $this->addIndexIfNotExists('rehabilitation_activities', ['difficulty_level', 'status'], 'idx_rehab_difficulty_status');
                $this->addIndexIfNotExists('rehabilitation_activities', ['activity_type', 'status'], 'idx_rehab_type_status');
                $this->addIndexIfNotExists('rehabilitation_activities', ['created_by'], 'idx_rehab_created_by');
                
                // Add fulltext index if supported
                if ($this->supportsFTIndex() && 
                    Schema::hasColumn('rehabilitation_activities', 'name') && 
                    Schema::hasColumn('rehabilitation_activities', 'short_description') &&
                    Schema::hasColumn('rehabilitation_activities', 'full_description')) {
                    try {
                        $this->addFullTextIndexIfNotExists('rehabilitation_activities', 
                            ['name', 'short_description', 'full_description'], 
                            'ft_rehab_search');
                    } catch (\Exception $e) {
                        Log::warning('Could not add fulltext index to rehabilitation_activities: ' . $e->getMessage());
                    }
                }
            });
        } else {
            Log::info('Rehabilitation_activities table does not exist, skipping index creation');
        }

        // Activity sessions table indexes
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $this->addIndexIfNotExists('activity_sessions', ['is_active', 'day_of_week'], 'idx_sessions_active_day');
                $this->addIndexIfNotExists('activity_sessions', ['semester', 'is_active'], 'idx_sessions_semester');
                $this->addIndexIfNotExists('activity_sessions', ['activity_id', 'is_active'], 'idx_sessions_activity');
            });
        } else {
            Log::info('Activity_sessions table does not exist, skipping index creation');
        }

        // Session enrollments table indexes
        if (Schema::hasTable('session_enrollments')) {
            Schema::table('session_enrollments', function (Blueprint $table) {
                $this->addIndexIfNotExists('session_enrollments', ['status', 'enrollment_date'], 'idx_enrollment_status_date');
                $this->addIndexIfNotExists('session_enrollments', ['enrolled_by'], 'idx_enrollment_staff');
            });
        } else {
            Log::info('Session_enrollments table does not exist, skipping index creation');
        }
    }

    private function addIndexIfNotExists($table, $columns, $indexName)
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        
        if (!array_key_exists($indexName, $indexes)) {
            Log::info("Adding index {$indexName} to {$table} table");
            
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        } else {
            Log::info("Index {$indexName} already exists on {$table} table, skipping");
        }
    }
    
    private function addFullTextIndexIfNotExists($table, $columns, $indexName)
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        
        if (!array_key_exists($indexName, $indexes)) {
            Log::info("Adding fulltext index {$indexName} to {$table} table");
            
            DB::statement("ALTER TABLE {$table} ADD FULLTEXT {$indexName} (" . implode(',', $columns) . ")");
        } else {
            Log::info("Fulltext index {$indexName} already exists on {$table} table, skipping");
        }
    }
    
    private function supportsFTIndex()
    {
        try {
            $version = DB::select('SELECT VERSION() as version')[0]->version;
            return (strpos(strtolower($version), 'mariadb') !== false) || 
                   (strpos($version, 'MySQL') !== false && version_compare($version, '5.6', '>='));
        } catch (\Exception $e) {
            Log::warning('Could not determine database version, assuming fulltext indexes are not supported');
            return false;
        }
    }

    public function down()
    {
        // Remove indexes from activities table
        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                $this->dropIndexIfExists('activities', 'idx_activities_status_created');
                $this->dropIndexIfExists('activities', 'idx_activities_category_status');
                $this->dropIndexIfExists('activities', 'idx_activities_creator_status');
                $this->dropIndexIfExists('activities', 'idx_activities_difficulty');
                $this->dropIndexIfExists('activities', 'ft_activities_search');
            });
        }

        // Remove indexes from rehabilitation_activities table
        if (Schema::hasTable('rehabilitation_activities')) {
            Schema::table('rehabilitation_activities', function (Blueprint $table) {
                $this->dropIndexIfExists('rehabilitation_activities', 'idx_rehab_category_status');
                $this->dropIndexIfExists('rehabilitation_activities', 'idx_rehab_status_created');
                $this->dropIndexIfExists('rehabilitation_activities', 'idx_rehab_difficulty_status');
                $this->dropIndexIfExists('rehabilitation_activities', 'idx_rehab_type_status');
                $this->dropIndexIfExists('rehabilitation_activities', 'idx_rehab_created_by');
                $this->dropIndexIfExists('rehabilitation_activities', 'ft_rehab_search');
            });
        }

        // Remove indexes from activity_sessions table
        if (Schema::hasTable('activity_sessions')) {
            Schema::table('activity_sessions', function (Blueprint $table) {
                $this->dropIndexIfExists('activity_sessions', 'idx_sessions_active_day');
                $this->dropIndexIfExists('activity_sessions', 'idx_sessions_semester');
                $this->dropIndexIfExists('activity_sessions', 'idx_sessions_activity');
            });
        }

        // Remove indexes from session_enrollments table
        if (Schema::hasTable('session_enrollments')) {
            Schema::table('session_enrollments', function (Blueprint $table) {
                $this->dropIndexIfExists('session_enrollments', 'idx_enrollment_status_date');
                $this->dropIndexIfExists('session_enrollments', 'idx_enrollment_staff');
            });
        }
    }
    
    private function dropIndexIfExists($table, $indexName)
    {
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexes = $sm->listTableIndexes($table);
        
        if (array_key_exists($indexName, $indexes)) {
            Log::info("Dropping index {$indexName} from {$table} table");
            
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } else {
            Log::info("Index {$indexName} does not exist on {$table} table, skipping");
        }
    }
};