<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with comprehensive Malaysian rehabilitation centre data.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🚀 Starting comprehensive Malaysian CREAMS database seeding...');
        
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
                CREAMSCentresSeeder::class,       // Malaysian rehabilitation centres
            ]);

            // Phase 2: Staff and User
            $this->command->info("\n👥 PHASE 2: Creating Malaysian staff members...");
            $this->call([
                CREAMSMalaysianStaffSeeder::class, // Realistic Malaysian staff with proper qualifications
            ]);

            // Phase 3: Course and Programs
            $this->command->info("\n📚 PHASE 3: Setting up courses and programs...");
            $this->call([
                CREAMSCourseSeeder::class,             // Create courses first
            ]);

            // Phase 4: Trainee
            $this->command->info("\n🧒 PHASE 4: Creating diverse Malaysian trainees...");
            $this->call([
                CREAMSEnhancedTraineeSeeder::class,    // Enhanced trainee seeder with unique, realistic Malaysian profiles
            ]);

            // Phase 5: Activity and Programs
            $this->command->info("\n🎯 PHASE 5: Setting up rehabilitation activities and academic programs...");
            $this->call([
                CREAMSCategorySeeder::class,                  // Activity categories with metadata
                CREAMSRehabilitationActivitiesSeeder::class, // Comprehensive bilingual activities
                // Enhanced activities moved to archive - using comprehensive activity seeder
            ]);

            // Phase 6: Enhanced Sessions and Enrollments
            $this->command->info("\n📅 PHASE 6: Creating enhanced activity sessions and trainee enrollments...");
            $this->call([
                CREAMSEnhancedSessionSeeder::class,           // Enhanced session seeder with 3+ sessions per week
                CREAMSActivitySeeder::class,                  // Comprehensive activity sessions and enrollments
            ]);

            // Phase 7: Enhanced System Features
            $this->command->info("\n🎯 PHASE 7: Adding enhanced system features...");
            // Disabled seeders that require additional table columns not present in current schema
            /*
            $this->call([
                // CREAMSStaffExpertiseSeeder::class,      // DISABLED - requires additional user table columns
                // CREAMSRealisticAttendanceSeeder::class,  // DISABLED - requires additional session_enrollments columns
            ]);
            */

            // Phase 8: Asset Management and Enhanced Systems
            $this->command->info("\n🏗️ PHASE 8: Setting up asset management and enhanced systems...");
            // Disabled seeders with enum/data mismatch issues
            /*
            $this->call([
                // CREAMSAssetManagementSeeder::class,  // DISABLED - status enum mismatch
                // CREAMSIEPProgressSeeder::class,      // May have similar issues
                // CREAMSCommunicationSeeder::class, // Communication system (schema needs fixing)
            ]);
            */

            // Phase 9: Additional Data (Optional)
            $this->command->info("\n📝 PHASE 9: Adding supplementary data...");
            // Disabled due to column mismatches with actual table structure
            /*
            $this->call([
                // CREAMSMessageSeeder::class,       // DISABLED - message table column mismatch
                // CREAMSNotificationSeeder::class,  // May have similar issues
            ]);
            */

            // Post-seeding operations
            $this->command->info("\n🔧 PHASE 10: Post-seeding optimizations...");
            
            // Run centre diversification if command exists
            try {
                Artisan::call('centres:diversify');
                $this->command->info('✅ Centre distribution optimized');
            } catch (\Exception $e) {
                $this->command->warn('Centre diversification command not available - skipping');
            }

            // Update statistics
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

            // Update trainee attendance percentages
            DB::statement("
                UPDATE trainees 
                SET trainee_attendance = (
                    SELECT ROUND(
                        (COUNT(CASE WHEN se.attendance_status = 'present' THEN 1 END) * 100.0) / 
                        NULLIF(COUNT(*), 0), 0
                    )
                    FROM session_enrollments se
                    WHERE se.trainee_id = trainees.id
                    AND se.attendance_status IN ('present', 'absent', 'late', 'excused')
                )
                WHERE id IN (SELECT DISTINCT trainee_id FROM session_enrollments)
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
        $this->command->info("🎉 COMPREHENSIVE MALAYSIAN CREAMS DATABASE SEEDING COMPLETED! 🎉");
        $this->command->info(str_repeat('=', 80));

        try {
            // Get comprehensive statistics
            $stats = $this->getSystemStatistics();
            
            $this->command->info("\n📊 FINAL SYSTEM STATISTICS:");
            $this->command->info("🏢 Rehabilitation Centre: " . $stats['centres']);
            $this->command->info("👥 Total Staff Members: " . $stats['staff']);
            $this->command->info("   📋 Administrators: " . $stats['admins']);
            $this->command->info("   📋 Supervisor: " . $stats['supervisors']); 
            $this->command->info("   📋 Teacher/Therapists: " . $stats['teachers']);
            $this->command->info("   📋 Committee Members (AJK): " . $stats['ajk']);
            
            $this->command->info("\n🧒 TRAINEES & PROGRAMS:");
            $this->command->info("👶 Total Trainee: " . $stats['trainees']);
            $this->command->info("🎯 Rehabilitation Activity: " . $stats['activities']);
            $this->command->info("📅 Activity Sessions: " . $stats['sessions']);
            $this->command->info("✅ Completed Sessions: " . $stats['completed_sessions']);
            $this->command->info("📋 Total Enrollments: " . $stats['enrollments']);
            
            $this->command->info("\n🇲🇾 MALAYSIAN CONTEXT FEATURES:");
            $this->command->line("   ✅ Authentic Malaysian names across Malay, Chinese, and Indian ethnicities");
            $this->command->line("   ✅ Realistic rehabilitation conditions reflecting Malaysian demographics");
            $this->command->line("   ✅ Bilingual activity names (Bahasa Malaysia / English)");
            $this->command->line("   ✅ Malaysian address formats, phone numbers, and postal codes");
            $this->command->line("   ✅ Professional qualifications relevant to Malaysian healthcare");
            $this->command->line("   ✅ Cultural appropriate guardian relationships and family structures");
            $this->command->line("   ✅ IIUM-style staff ID formats and email addresses");
            
            $this->command->info("\n🏥 REHABILITATION PROGRAMS:");
            $this->command->line("   🗣️  Speech and Language Therapy");
            $this->command->line("   🖐️  Occupational Therapy");
            $this->command->line("   🏃  Physical Therapy / Physiotherapy");
            $this->command->line("   🧠  Behavioral Intervention Therapy");
            $this->command->line("   👥  Social Skills Training");
            $this->command->line("   🎨  Art and Music Therapy");
            $this->command->line("   🔢  Academic Support (Mathematics, Literacy)");
            $this->command->line("   💻  Computer and Technology Skills");
            $this->command->line("   🏠  Daily Living Skills Training");
            $this->command->line("   🔧  Vocational Training Programs");
            
            $this->command->info("\n🎯 READY FOR USE:");
            $this->command->line("   ✅ Login with any staff member using 'password123'");
            $this->command->line("   ✅ Role-based dashboards for admin, supervisor, teacher, ajk");
            $this->command->line("   ✅ Activity scheduling and session management");
            $this->command->line("   ✅ Trainee enrollment and progress tracking");
            $this->command->line("   ✅ Realistic attendance patterns and progress notes");
            
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
            'enrollments' => DB::table('session_enrollments')->count(),
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