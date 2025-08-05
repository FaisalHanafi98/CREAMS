<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSEssentialDataSeeder extends Seeder
{
    /**
     * Run the database seeds for essential communication data
     */
    public function run(): void
    {
        $this->command->info('📱 Creating essential communication data...');

        try {
            DB::beginTransaction();

            // Create message categories
            $this->createMessageCategories();
            
            // Create message templates
            $this->createMessageTemplates();

            DB::commit();

            $this->command->info('✅ Essential communication data seeded successfully!');
            $this->showStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed essential data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create essential message categories
     */
    private function createMessageCategories(): void
    {
        $this->command->info('📂 Creating message categories...');

        $categories = [
            [
                'category_name' => 'General',
                'category_description' => 'General communication and announcements',
                'category_color' => '#6c757d',
                'category_icon' => 'fas fa-comment',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'category_name' => 'Urgent',
                'category_description' => 'Urgent messages requiring immediate attention',
                'category_color' => '#dc3545',
                'category_icon' => 'fas fa-exclamation-triangle',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'category_name' => 'Progress Update',
                'category_description' => 'Trainee progress and development updates',
                'category_color' => '#28a745',
                'category_icon' => 'fas fa-chart-line',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'category_name' => 'Schedule Change',
                'category_description' => 'Activity and session schedule modifications',
                'category_color' => '#ffc107',
                'category_icon' => 'fas fa-calendar-alt',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'category_name' => 'Administrative',
                'category_description' => 'Administrative and operational messages',
                'category_color' => '#17a2b8',
                'category_icon' => 'fas fa-cog',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'category_name' => 'Parent Communication',
                'category_description' => 'Messages to and from parents/guardians',
                'category_color' => '#6f42c1',
                'category_icon' => 'fas fa-users',
                'is_system' => false,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            DB::table('message_categories')->insert(array_merge($category, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }

    /**
     * Create essential message templates
     */
    private function createMessageTemplates(): void
    {
        $this->command->info('📝 Creating message templates...');

        // Get first centre and admin for templates
        $centre = DB::table('centres')->first();
        $admin = DB::table('users')->where('role', 'admin')->first();

        if (!$centre || !$admin) {
            $this->command->warn('Centre or admin not found - skipping message templates');
            return;
        }

        $templates = [
            [
                'template_name' => 'Session Reminder',
                'template_subject' => 'Reminder: Upcoming Session for {trainee_name}',
                'template_body' => 'Dear {guardian_name}, this is a reminder that {trainee_name} has a {activity_name} session scheduled for {session_date} at {session_time}. Please ensure timely attendance.',
                'template_type' => 'alert',
                'template_variables' => json_encode(['trainee_name', 'guardian_name', 'activity_name', 'session_date', 'session_time']),
                'is_active' => true,
            ],
            [
                'template_name' => 'Progress Update',
                'template_subject' => 'Progress Update for {trainee_name}',
                'template_body' => 'Dear {guardian_name}, we are pleased to share that {trainee_name} has shown {progress_description} in recent sessions. {additional_notes}',
                'template_type' => 'update',
                'template_variables' => json_encode(['trainee_name', 'guardian_name', 'progress_description', 'additional_notes']),
                'is_active' => true,
            ],
            [
                'template_name' => 'Schedule Change',
                'template_subject' => 'Schedule Change Notice',
                'template_body' => 'Important: The {activity_name} session scheduled for {original_date} has been rescheduled to {new_date} at {new_time}. Please update your calendar accordingly.',
                'template_type' => 'notice',
                'template_variables' => json_encode(['activity_name', 'original_date', 'new_date', 'new_time']),
                'is_active' => true,
            ],
            [
                'template_name' => 'Welcome Message',
                'template_subject' => 'Welcome to {centre_name}',
                'template_body' => 'Dear {guardian_name}, welcome to {centre_name}! We are excited to begin working with {trainee_name}. Your assigned therapist is {therapist_name}.',
                'template_type' => 'info',
                'template_variables' => json_encode(['guardian_name', 'centre_name', 'trainee_name', 'therapist_name']),
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DB::table('message_templates')->insert(array_merge($template, [
                'created_by' => $admin->id,
                'centre_id' => $centre->centre_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }

    /**
     * Show statistics
     */
    private function showStatistics(): void
    {
        $categoriesCount = DB::table('message_categories')->count();
        $templatesCount = DB::table('message_templates')->count();
        
        $this->command->info("\n📊 ESSENTIAL DATA STATISTICS:");
        $this->command->line("   📂 Message Categories: {$categoriesCount}");
        $this->command->line("   📝 Message Templates: {$templatesCount}");
    }
}