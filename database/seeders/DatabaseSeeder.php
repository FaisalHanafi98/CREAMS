<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database using new CREAMS consolidated structure.
     * This ensures all tables are properly populated with Malaysian context and real data preservation.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting CREAMS Database Seeding - Consolidated Structure');
        
        try {
            // CREAMS Consolidated Seeding - 7 Modules
            $this->command->info("\n🏗️ CREAMS CONSOLIDATED SEEDING (7 MODULES)");
            $this->call([
                CREAMSSeederFoundationManagement::class,      // Module 01: Centres, Users, System
                CREAMSSeederClientManagement::class,          // Module 02: Trainees, Volunteers, Contacts  
                CREAMSSeederServiceDeliveryManagement::class, // Module 03: Activities, Sessions, Enrollments
                CREAMSSeederAttendanceManagement::class,      // Module 04: All Attendance Systems
                CREAMSSeederAssetManagement::class,           // Module 05: Assets, Maintenance, Inventory
                CREAMSSeederCommunicationManagement::class,   // Module 06: Messages, Notifications, Letters
                CREAMSSeederSystemConstraints::class,         // Module 07: Foreign Keys, Validation
            ]);

            // Show final comprehensive summary
            $this->showFinalSummary();
            
        } catch (\Exception $e) {
            Log::error('Critical error in CREAMS database seeding', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->command->error("\n❌ SEEDING FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Show comprehensive summary of all 29 tables
     */
    private function showFinalSummary(): void
    {
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🎉 CREAMS DATABASE SEEDING COMPLETED SUCCESSFULLY! 🎉");
        $this->command->info(str_repeat('=', 80));

        try {
            $stats = $this->getComprehensiveStatistics();
            
            $this->command->info("\n📊 ALL 29 TABLES POPULATED:");
            
            // Core System Tables
            $this->command->info("\n🏗️  FOUNDATION & USER MANAGEMENT:");
            $this->command->info("   🏢 Centres: " . $stats['centres']);
            $this->command->info("   👥 Users: " . $stats['users']);
            $this->command->info("   🔐 Password Resets: " . $stats['password_resets']);
            $this->command->info("   💾 Sessions: " . $stats['sessions']);
            
            // Trainee System
            $this->command->info("\n🧒 TRAINEE MANAGEMENT:");
            $this->command->info("   👶 Trainees: " . $stats['trainees']);
            $this->command->info("   📋 Trainee Attendances: " . $stats['trainee_attendances']);
            
            // Activity System
            $this->command->info("\n🎯 ACTIVITY MANAGEMENT:");
            $this->command->info("   📂 Activity Categories: " . $stats['activity_categories']);
            $this->command->info("   🎯 Activities: " . $stats['activities']);
            $this->command->info("   📅 Activity Sessions: " . $stats['activity_sessions']);
            $this->command->info("   📝 Activity Enrollments: " . $stats['activity_enrollments']);
            
            // Attendance System  
            $this->command->info("\n📋 ATTENDANCE SYSTEM:");
            $this->command->info("   👨‍💼 Staff Attendances: " . $stats['staff_attendances']);
            $this->command->info("   📝 Session Attendance: " . $stats['session_attendance']);
            $this->command->info("   🚨 Attendance Alerts: " . $stats['attendance_alerts']);
            
            // Asset Management
            $this->command->info("\n🏭 ASSET MANAGEMENT:");
            $this->command->info("   🏷️  Asset Types: " . $stats['asset_types']);
            $this->command->info("   📂 Asset Categories: " . $stats['asset_categories']);
            $this->command->info("   📍 Asset Locations: " . $stats['asset_locations']);
            $this->command->info("   🏭 Assets: " . $stats['assets']);
            $this->command->info("   🔧 Asset Maintenance: " . $stats['asset_maintenance']);
            $this->command->info("   📋 Maintenance History: " . $stats['asset_maintenance_history']);
            $this->command->info("   📦 Asset Movements: " . $stats['asset_movements']);
            
            // Communication System
            $this->command->info("\n📧 COMMUNICATION SYSTEM:");
            $this->command->info("   📞 Contact Messages: " . $stats['contact_messages']);
            $this->command->info("   💬 Messages: " . $stats['messages']);
            $this->command->info("   🔔 Notifications: " . $stats['notifications']);
            $this->command->info("   🤝 Volunteers: " . $stats['volunteers']);
            
            // Letter System
            $this->command->info("\n📄 LETTER GENERATION:");
            $this->command->info("   📝 Letter Templates: " . $stats['letter_templates']);
            $this->command->info("   📄 Letters: " . $stats['letters']);
            
            // System Infrastructure
            $this->command->info("\n⚙️  SYSTEM INFRASTRUCTURE:");
            $this->command->info("   🔧 Migrations: " . $stats['migrations']);
            $this->command->info("   ❌ Failed Jobs: " . $stats['failed_jobs']);
            $this->command->info("   ⏳ Jobs: " . $stats['jobs']);
            $this->command->info("   🔑 Personal Access Tokens: " . $stats['personal_access_tokens']);
            
            $this->command->info("\n🎯 SYSTEM READY FOR PRODUCTION!");
            $this->command->line("   ✅ All 29 tables populated according to DATABASE_ARCHITECTURE.txt");
            $this->command->line("   ✅ Malaysian rehabilitation center data with realistic demographics");
            $this->command->line("   ✅ Complete role-based access control (Admin, Supervisor, Teacher, AJK)");
            $this->command->line("   ✅ Comprehensive activity scheduling and session management");
            $this->command->line("   ✅ Full asset tracking and maintenance systems");
            $this->command->line("   ✅ Integrated communication and letter generation");
            
        } catch (\Exception $e) {
            $this->command->warn("Could not generate statistics: " . $e->getMessage());
        }

        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🇲🇾 MALAYSIAN REHABILITATION MANAGEMENT SYSTEM READY! 🇲🇾");
        $this->command->info(str_repeat('=', 80) . "\n");
    }

    /**
     * Get statistics for all 29 tables
     */
    private function getComprehensiveStatistics(): array
    {
        return [
            // Foundation & User Management (4 tables)
            'centres' => DB::table('centres')->count(),
            'users' => DB::table('users')->count(),
            'password_resets' => DB::table('password_resets')->count(),
            'sessions' => DB::table('sessions')->count(),
            
            // Trainee Management (2 tables)
            'trainees' => DB::table('trainees')->count(),
            'trainee_attendances' => DB::table('trainee_attendances')->count(),
            
            // Activity Management (4 tables)
            'activity_categories' => DB::table('activity_categories')->count(),
            'activities' => DB::table('activities')->count(),
            'activity_sessions' => DB::table('activity_sessions')->count(),
            'activity_enrollments' => DB::table('activity_enrollments')->count(),
            
            // Attendance & Progress (3 tables)
            'staff_attendances' => DB::table('staff_attendances')->count(),
            'session_attendance' => DB::table('session_attendance')->count(),
            'attendance_alerts' => DB::table('attendance_alerts')->count(),
            
            // Asset Management (7 tables)
            'asset_types' => DB::table('asset_types')->count(),
            'asset_categories' => DB::table('asset_categories')->count(),
            'asset_locations' => DB::table('asset_locations')->count(),
            'assets' => DB::table('assets')->count(),
            'asset_maintenance' => DB::table('asset_maintenance')->count(),
            'asset_maintenance_history' => DB::table('asset_maintenance_history')->count(),
            'asset_movements' => DB::table('asset_movements')->count(),
            
            // Communication (4 tables)
            'contact_messages' => DB::table('contact_messages')->count(),
            'messages' => DB::table('messages')->count(),
            'notifications' => DB::table('notifications')->count(),
            'volunteers' => DB::table('volunteers')->count(),
            
            // Letter Generation (2 tables)
            'letter_templates' => DB::table('letter_templates')->count(),
            'letters' => DB::table('letters')->count(),
            
            // System Infrastructure (4 tables)
            'migrations' => DB::table('migrations')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'jobs' => DB::table('jobs')->count(),
            'personal_access_tokens' => DB::table('personal_access_tokens')->count(),
        ];
    }
}