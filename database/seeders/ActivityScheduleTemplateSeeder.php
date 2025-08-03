<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActivityScheduleTemplate;
use App\Models\User;
use App\Models\Centre;

class ActivityScheduleTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::where('role', 'admin')->first();
        $centres = Centre::limit(3)->get();
        
        $templates = [
            [
                'template_name' => 'Monday-Wednesday-Friday Morning',
                'description' => 'Standard 3-day weekly schedule for morning rehabilitation sessions',
                'sessions_per_week' => 3,
                'duration_weeks' => 8,
                'session_length_minutes' => 60,
                'days_of_week' => ['Monday', 'Wednesday', 'Friday'],
                'time_slots' => [['start' => '09:00', 'end' => '10:00']],
                'template_type' => 'weekly',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ],
            [
                'template_name' => 'Tuesday-Thursday Intensive',
                'description' => 'Intensive 2-day schedule with longer sessions for focused therapy',
                'sessions_per_week' => 2,
                'duration_weeks' => 12,
                'session_length_minutes' => 90,
                'days_of_week' => ['Tuesday', 'Thursday'],
                'time_slots' => [['start' => '10:00', 'end' => '11:30']],
                'template_type' => 'intensive',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ],
            [
                'template_name' => 'Daily Weekday Program',
                'description' => 'Comprehensive daily program for intensive rehabilitation',
                'sessions_per_week' => 5,
                'duration_weeks' => 6,
                'session_length_minutes' => 45,
                'days_of_week' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'time_slots' => [['start' => '14:00', 'end' => '14:45']],
                'template_type' => 'intensive',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ],
            [
                'template_name' => 'Weekend Therapy Sessions',
                'description' => 'Weekend-focused sessions for working families',
                'sessions_per_week' => 2,
                'duration_weeks' => 10,
                'session_length_minutes' => 120,
                'days_of_week' => ['Saturday', 'Sunday'],
                'time_slots' => [['start' => '09:00', 'end' => '11:00']],
                'template_type' => 'flexible',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ],
            [
                'template_name' => 'Bi-Weekly Check-in',
                'description' => 'Regular assessment and monitoring sessions',
                'sessions_per_week' => 1,
                'duration_weeks' => 16,
                'session_length_minutes' => 30,
                'days_of_week' => ['Friday'],
                'time_slots' => [['start' => '15:00', 'end' => '15:30']],
                'template_type' => 'flexible',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ],
            [
                'template_name' => 'Morning & Afternoon Dual Sessions',
                'description' => 'Two daily sessions for comprehensive therapy coverage',
                'sessions_per_week' => 6,
                'duration_weeks' => 4,
                'session_length_minutes' => 60,
                'days_of_week' => ['Monday', 'Wednesday', 'Friday'],
                'time_slots' => [
                    ['start' => '09:00', 'end' => '10:00'],
                    ['start' => '14:00', 'end' => '15:00']
                ],
                'template_type' => 'custom',
                'created_by' => $adminUser->id ?? 1,
                'centre_id' => $centres->first()->centre_id ?? null
            ]
        ];

        foreach ($templates as $template) {
            ActivityScheduleTemplate::create($template);
        }

        $this->command->info('Created ' . count($templates) . ' schedule templates.');
    }
}
