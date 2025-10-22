<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ActivityLog;
use Carbon\Carbon;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get real users from database
        $admin = \App\Models\User::where('centre_id', '01')->where('role', 'admin')->first();
        $teacher = \App\Models\User::where('centre_id', '01')->where('role', 'teacher')->first();
        $supervisor = \App\Models\User::where('centre_id', '01')->where('role', 'supervisor')->first();

        $logs = [
            [
                'centre_id' => '01',
                'user_id' => $admin->id ?? 134,
                'user_name' => $admin->name ?? 'Faisal Hanafi',
                'user_role' => 'admin',
                'action_type' => 'created',
                'model_type' => 'Activity',
                'model_id' => '1',
                'title' => 'New Activity Created: Swimming Therapy',
                'description' => 'Created a new aquatic therapy session for children with autism',
                'icon' => 'plus-circle',
                'status' => 'success',
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'centre_id' => '01',
                'user_id' => $teacher->id ?? 164,
                'user_name' => $teacher->name ?? 'Ustaz Ahmad bin Hassan',
                'user_role' => 'teacher',
                'action_type' => 'updated',
                'model_type' => 'Trainee',
                'model_id' => '5',
                'title' => 'Trainee Profile Updated: Ahmad bin Ali',
                'description' => 'Updated medical information and emergency contacts',
                'icon' => 'user-edit',
                'status' => 'info',
                'created_at' => Carbon::now()->subHours(10),
            ],
            [
                'centre_id' => '01',
                'user_id' => $admin->id ?? 134,
                'user_name' => $admin->name ?? 'Faisal Hanafi',
                'user_role' => 'admin',
                'action_type' => 'created',
                'model_type' => 'User',
                'model_id' => '8',
                'title' => 'New Staff Member: Dr. Fatimah Ibrahim',
                'description' => 'Registered new physiotherapist to the centre',
                'icon' => 'user-plus',
                'status' => 'success',
                'created_at' => Carbon::now()->subHours(13),
            ],
            [
                'centre_id' => '01',
                'user_id' => $supervisor->id ?? $teacher->id ?? 164,
                'user_name' => $supervisor->name ?? $teacher->name ?? 'Ustaz Ahmad bin Hassan',
                'user_role' => $supervisor ? 'supervisor' : 'teacher',
                'action_type' => 'updated',
                'model_type' => 'Session',
                'model_id' => '12',
                'title' => 'Session Rescheduled: Art Therapy Class',
                'description' => 'Moved session from Monday to Wednesday due to facility maintenance',
                'icon' => 'calendar-alt',
                'status' => 'warning',
                'created_at' => Carbon::now()->subHours(16),
            ],
            [
                'centre_id' => '01',
                'user_id' => $admin->id ?? 134,
                'user_name' => $admin->name ?? 'Faisal Hanafi',
                'user_role' => 'admin',
                'action_type' => 'deleted',
                'model_type' => 'Activity',
                'model_id' => '22',
                'title' => 'Activity Cancelled: Outdoor Sports',
                'description' => 'Cancelled due to insufficient enrollment',
                'icon' => 'times-circle',
                'status' => 'danger',
                'created_at' => Carbon::now()->subHours(20),
            ],
        ];

        foreach ($logs as $log) {
            ActivityLog::create($log);
        }
    }
}
