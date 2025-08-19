<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with comprehensive CREAMS data.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Starting comprehensive CREAMS database seeding...');
        
        try {
            // Check if database is empty
            if ($this->isDatabaseSeeded()) {
                if (!$this->command->confirm('Database appears to already contain data. Do you want to continue? This may create duplicates.')) {
                    $this->command->info('Seeding cancelled by user.');
                    return;
                }
            }

            // Phase 1: Core Infrastructure
            $this->command->info("\n🏗️  PHASE 1: Setting up core infrastructure...");
            $this->call([
                CREAMSCentreSeeder::class,  // Updated seeder name
            ]);

            // Phase 2: Staff and Users
            $this->command->info("\n👥 PHASE 2: Creating Malaysian staff members...");
            $this->call([
                CREAMSUserSeeder::class,  // Updated seeder name
            ]);

            // Phase 3: Trainees
            $this->command->info("\n🧒 PHASE 3: Creating diverse Malaysian trainees...");
            $this->call([
                CREAMSTraineeSeeder::class,  // Updated seeder name
            ]);

            // Phase 4: Activities and Sessions
            $this->command->info("\n🎯 PHASE 4: Setting up rehabilitation activities and sessions...");
            $this->call([
                CREAMSCategorySeeder::class,        // Activity categories
                CREAMSActivitySeeder::class,        // Updated seeder name
            ]);

            // Phase 5: Additional System Data
            $this->command->info("\n📝 PHASE 5: Adding supplementary data...");
            $this->call([
                CREAMSAssetTypesSeeder::class,      // Asset types and categories
                CREAMSLetterTemplatesSeeder::class, // Letter templates
            ]);

            // Post-seeding operations
            $this->command->info("\n🔧 PHASE 6: Post-seeding optimizations...");
            $this->updateSystemStatistics();
            
            // Show final summary
            $this->showFinalSummary();
            
        } catch (\Exception $e) {
            Log::error('Error in comprehensive database seeding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->command->error("\n❌ Error during seeding: " . $e->getMessage());
            $this->command->error("Check the logs for detailed error information.");
            
            throw $e;
        }
    }

    /**
     * Check if database already contains seeded data
     */
    private function isDatabaseSeeded(): bool
    {
        try {
            $userCount = DB::table('users')->count();
            $centreCount = DB::table('centres')->count();
            $traineeCount = DB::table('trainees')->count();
            
            return ($userCount > 1 || $centreCount > 0 || $traineeCount > 0);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update system statistics after seeding
     */
    private function updateSystemStatistics(): void
    {
        try {
            // Update activity times_conducted based on completed sessions
            DB::statement("
                UPDATE activities 
                SET times_conducted = (
                    SELECT COUNT(*) 
                    FROM activity_sessions 
                    WHERE activity_sessions.activity_id = activities.id 
                    AND activity_sessions.session_status = 'completed'
                )
            ");

            $this->command->info('✅ System statistics updated');
        } catch (\Exception $e) {
            $this->command->warn('Could not update system statistics: ' . $e->getMessage());
        }
    }

    /**
     * Show comprehensive summary of seeded data
     */
    private function showFinalSummary(): void
    {
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🎉 COMPREHENSIVE CREAMS DATABASE SEEDING COMPLETED! 🎉");
        $this->command->info(str_repeat('=', 80));

        try {
            // Get comprehensive statistics
            $stats = $this->getSystemStatistics();
            
            $this->command->info("\n📊 FINAL SYSTEM STATISTICS:");
            $this->command->info("🏢 Rehabilitation Centres: " . $stats['centres']);
            $this->command->info("👥 Total Staff Members: " . $stats['staff']);
            $this->command->info("   📋 Administrators: " . $stats['admins']);
            $this->command->info("   📋 Supervisors: " . $stats['supervisors']); 
            $this->command->info("   📋 Teachers/Therapists: " . $stats['teachers']);
            $this->command->info("   📋 Committee Members (AJK): " . $stats['ajk']);
            
            $this->command->info("\n🧒 TRAINEES & PROGRAMS:");
            $this->command->info("👶 Total Trainees: " . $stats['trainees']);
            $this->command->info("🎯 Rehabilitation Activities: " . $stats['activities']);
            $this->command->info("📅 Activity Sessions: " . $stats['sessions']);
            $this->command->info("✅ Completed Sessions: " . $stats['completed_sessions']);
            
            $this->command->info("\n🇲🇾 MALAYSIAN CONTEXT FEATURES:");
            $this->command->line("   ✅ Authentic Malaysian names across ethnicities");
            $this->command->line("   ✅ Realistic rehabilitation conditions");
            $this->command->line("   ✅ Malaysian address formats and phone numbers");
            $this->command->line("   ✅ Professional qualifications relevant to Malaysian healthcare");
            $this->command->line("   ✅ IIUM-style staff ID formats and email addresses");
            
            $this->command->info("\n🏥 REHABILITATION PROGRAMS:");
            $this->command->line("   🗣️  Speech and Language Therapy");
            $this->command->line("   🖐️  Occupational Therapy");
            $this->command->line("   🏃  Physical Therapy / Physiotherapy");
            $this->command->line("   🧠  Behavioral Intervention Therapy");
            $this->command->line("   👥  Social Skills Training");
            $this->command->line("   🎨  Art and Music Therapy");
            $this->command->line("   💻  Computer and Technology Skills");
            $this->command->line("   🏠  Daily Living Skills Training");
            
            $this->command->info("\n🎯 READY FOR USE:");
            $this->command->line("   ✅ Login with any staff member using 'password123'");
            $this->command->line("   ✅ Role-based dashboards for admin, supervisor, teacher, ajk");
            $this->command->line("   ✅ Activity scheduling and session management");
            $this->command->line("   ✅ Trainee enrollment and progress tracking");
            
            $this->command->info("\n📖 SAMPLE USERS TO TRY:");
            $this->showSampleUsers();
            
        } catch (\Exception $e) {
            $this->command->warn("Could not generate final statistics: " . $e->getMessage());
        }

        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🇲🇾 Your Malaysian rehabilitation centre management system is ready! 🇲🇾");
        $this->command->info(str_repeat('=', 80) . "\n");
    }

    /**
     * Get comprehensive system statistics
     */
    private function getSystemStatistics(): array
    {
        return [
            'centres' => DB::table('centres')->count(),
            'staff' => DB::table('users')->count(),
            'admins' => DB::table('users')->where('role', 'admin')->count(),
            'supervisors' => DB::table('users')->where('role', 'supervisor')->count(),
            'teachers' => DB::table('users')->where('role', 'teacher')->count(),
            'ajk' => DB::table('users')->where('role', 'ajk')->count(),
            'trainees' => DB::table('trainees')->count(),
            'activities' => DB::table('activities')->count(),
            'sessions' => DB::table('activity_sessions')->count(),
            'completed_sessions' => DB::table('activity_sessions')->where('status', 'completed')->count(),
        ];
    }

    /**
     * Show sample users for testing
     */
    private function showSampleUsers(): void
    {
        try {
            $sampleUsers = DB::table('users')
                ->select('name', 'email', 'role', 'iium_id')
                ->whereIn('role', ['admin', 'supervisor', 'teacher'])
                ->limit(6)
                ->get();

            foreach ($sampleUsers as $user) {
                $roleIcon = match($user->role) {
                    'admin' => '👑',
                    'supervisor' => '👨‍💼',
                    'teacher' => '👨‍⚕️',
                    default => '👤'
                };
                
                $this->command->line("   {$roleIcon} {$user->name} ({$user->role}) - {$user->email}");
            }
            
            $this->command->info("   🔑 Password for all users: password123");
            
        } catch (\Exception $e) {
            $this->command->line("   Check users table for login credentials");
        }
    }
}