<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LetterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📝 Seeding letter templates...');

        $templates = [
            [
                'template_name' => 'Trainee Enrollment Confirmation',
                'template_description' => 'Confirmation letter for trainee enrollment',
                'template_type' => 'trainee',
                'template_content' => 'Dear {{guardian_name}}, We are pleased to confirm the enrollment of {{trainee_name}} in our rehabilitation program.',
                'template_variables' => json_encode(['guardian_name', 'trainee_name', 'program_name', 'start_date']),
                'is_active' => true,
                'is_system_template' => true
            ],
            [
                'template_name' => 'Progress Report Letter',
                'template_description' => 'Monthly progress report for trainees',
                'template_type' => 'trainee',
                'template_content' => 'Dear {{guardian_name}}, This letter contains the progress report for {{trainee_name}} for the month of {{month_year}}.',
                'template_variables' => json_encode(['guardian_name', 'trainee_name', 'month_year', 'progress_details']),
                'is_active' => true,
                'is_system_template' => true
            ],
            [
                'template_name' => 'Staff Appointment Letter',
                'template_description' => 'Official appointment letter for new staff',
                'template_type' => 'staff',
                'template_content' => 'Dear {{staff_name}}, We are pleased to offer you the position of {{position}} at {{centre_name}}.',
                'template_variables' => json_encode(['staff_name', 'position', 'centre_name', 'start_date', 'salary']),
                'is_active' => true,
                'is_system_template' => true
            ],
            [
                'template_name' => 'Certificate of Completion',
                'template_description' => 'Certificate for completed programs',
                'template_type' => 'certificate',
                'template_content' => 'This is to certify that {{trainee_name}} has successfully completed {{program_name}} on {{completion_date}}.',
                'template_variables' => json_encode(['trainee_name', 'program_name', 'completion_date', 'centre_name']),
                'is_active' => true,
                'is_system_template' => true
            ]
        ];

        foreach ($templates as $template) {
            DB::table('letter_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }

        $this->command->info('📝 Successfully seeded ' . count($templates) . ' letter templates');
    }
}