<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Users;

class BasicNotificationsSeeder extends Seeder
{
    private array $notificationData = [
        [
            'title' => 'New Activity Session Scheduled',
            'message' => 'A new speech therapy session has been scheduled for next week. Please check your schedule.',
            'type' => 'info',
            'user_type' => 'user'
        ],
        [
            'title' => 'Trainee Progress Update',
            'message' => 'Monthly progress reports are now available for review. Please update trainee assessments.',
            'type' => 'info',
            'user_type' => 'user'
        ],
        [
            'title' => 'System Maintenance Notice',
            'message' => 'The system will undergo maintenance on Sunday from 2:00 AM to 4:00 AM.',
            'type' => 'warning',
            'user_type' => 'user'
        ],
        [
            'title' => 'New Course Registration Open',
            'message' => 'Registration for the new Behavioral Support Program is now open.',
            'type' => 'success',
            'user_type' => 'user'
        ],
        [
            'title' => 'Equipment Maintenance Required',
            'message' => 'Some therapy equipment requires maintenance. Please report any issues.',
            'type' => 'warning',
            'user_type' => 'user'
        ]
    ];

    public function run(): void
    {
        $this->command->info('🔔 Creating basic notifications...');

        $users = Users::all();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found! Please run staff seeders first.');
            return;
        }

        $totalNotifications = 0;

        foreach ($this->notificationData as $notificationInfo) {
            // Create notifications for a few random users
            $selectedUsers = $users->random(min(5, $users->count()));
            
            foreach ($selectedUsers as $user) {
                $notification = Notification::create([
                    'notification_title' => $notificationInfo['title'],
                    'notification_message' => $notificationInfo['message'],
                    'notification_type' => $notificationInfo['type'],
                    'user_id' => $user->id,
                    'user_type' => $notificationInfo['user_type'],
                    'is_read' => rand(0, 1) == 1 ? true : false,
                    'read_at' => rand(0, 1) == 1 ? now()->subDays(rand(1, 7)) : null,
                    'notification_data' => json_encode(['source' => 'system'])
                ]);

                $totalNotifications++;
            }
            
            $this->command->line("   ✅ {$notificationInfo['title']} (sent to " . $selectedUsers->count() . " users)");
        }

        $this->command->info("🔔 Total notifications created: $totalNotifications");
        $this->command->info("   📋 Notification types: info, warning, success");
    }
}