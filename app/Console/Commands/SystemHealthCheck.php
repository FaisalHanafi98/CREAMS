<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Centre;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check';
    protected $description = 'Perform comprehensive system health check';

    public function handle()
    {
        $this->info('🔍 Starting CREAMS System Health Check...');

        $issues = [];

        // Check database connection
        $this->info('📊 Checking database connection...');
        try {
            DB::connection()->getPdo();
            $this->info('✅ Database connection: OK');
        } catch (\Exception $e) {
            $issues[] = '❌ Database connection failed: ' . $e->getMessage();
        }

        // Check critical tables exist
        $this->info('🗃️  Checking critical tables...');
        $criticalTables = ['users', 'trainees', 'activities', 'centres', 'assets'];
        foreach ($criticalTables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("✅ Table '{$table}': EXISTS");
            } else {
                $issues[] = "❌ Critical table '{$table}' does not exist";
            }
        }

        // Check model relationships
        $this->info('🔗 Checking model relationships...');
        try {
            // Test User model
            $userCount = User::count();
            $this->info("✅ User model: {$userCount} records");

            // Test Trainee model and avatar field
            $traineeCount = Trainee::count();
            $this->info("✅ Trainee model: {$traineeCount} records");

            // Test if avatar field exists in trainees
            if (Schema::hasColumn('trainees', 'avatar')) {
                $this->info('✅ Trainee avatar field: EXISTS');
            } else {
                $issues[] = '❌ Trainee avatar field missing';
            }

            // Test Activity model with creator relationship
            if (class_exists('App\Models\Activity')) {
                $activityCount = Activity::count();
                $this->info("✅ Activity model: {$activityCount} records");

                // Test creator relationship
                $activityWithCreator = Activity::with('creator')->first();
                if ($activityWithCreator) {
                    $this->info('✅ Activity-Creator relationship: OK');
                } else {
                    $this->info('⚠️  Activity-Creator relationship: No data to test');
                }
            }

            // Test Asset model
            if (class_exists('App\Models\Asset')) {
                $assetCount = Asset::count();
                $this->info("✅ Asset model: {$assetCount} records");
            }

            // Test Centre model
            $centreCount = Centre::count();
            $this->info("✅ Centre model: {$centreCount} records");
        } catch (\Exception $e) {
            $issues[] = '❌ Model relationship error: ' . $e->getMessage();
        }

        // Check asset management system
        $this->info('🏗️  Checking asset management system...');
        try {
            // Check asset_parents table
            if (Schema::hasTable('asset_parents')) {
                $assetParentsCount = DB::table('asset_parents')->count();
                $this->info("✅ Asset types table: {$assetParentsCount} records");
            } else {
                $issues[] = '❌ Asset types table missing';
            }

            // Check enhanced asset tables
            $enhancedTables = ['assets', 'asset_locations', 'asset_movements', 'asset_maintenance'];
            foreach ($enhancedTables as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    $this->info("✅ Table '{$table}': {$count} records");
                } else {
                    $this->warn("⚠️  Enhanced table '{$table}' missing (optional)");
                }
            }
        } catch (\Exception $e) {
            $issues[] = '❌ Asset management system error: ' . $e->getMessage();
        }

        // Check avatar standardization
        $this->info('🖼️  Checking avatar standardization...');
        try {
            $avatarTables = ['users', 'trainees'];
            foreach ($avatarTables as $table) {
                if (Schema::hasColumn($table, 'avatar')) {
                    $this->info("✅ {$table}.avatar: EXISTS");
                } else {
                    $issues[] = "❌ {$table}.avatar field missing";
                }

                // Check if old avatar fields still exist (should be removed)
                $oldFields = ['user_avatar', 'trainee_avatar'];
                foreach ($oldFields as $field) {
                    if (Schema::hasColumn($table, $field)) {
                        $issues[] = "⚠️  Old field {$table}.{$field} still exists";
                    }
                }
            }
        } catch (\Exception $e) {
            $issues[] = '❌ Avatar standardization check error: ' . $e->getMessage();
        }

        // Summary
        $this->info('');
        $this->info('📋 HEALTH CHECK SUMMARY');
        $this->info('========================');

        if (empty($issues)) {
            $this->info('🎉 All checks passed! System is healthy.');
            return 0;
        } else {
            $this->error('⚠️  Issues found:');
            foreach ($issues as $issue) {
                $this->error($issue);
            }
            return 1;
        }
    }
}
