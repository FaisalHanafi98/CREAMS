<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Users;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;
use App\Models\Notifications;
use Illuminate\Support\Facades\Schema;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            Log::info('Starting notifications table seeding');
            
            // Check if table already has data
            if (Schema::hasTable('notifications') && DB::table('notifications')->count() > 0) {
                if (!$this->command->confirm('Notifications table already has data. Continue with seeding?')) {
                    $this->command->info('Seeding aborted!');
                    return;
                }
            }
            
            // Get seeding mode based on environment
            $mode = config('app.env') === 'production' ? 'minimal' : 'full';
            Log::info("Seeding notifications in {$mode} mode");
            
            DB::beginTransaction();
            
            // Get users from each role for test notifications
            $admins = Admins::where('status', 'active')->get();
            $supervisors = Supervisors::where('status', 'active')->get();
            $teachers = Teachers::where('status', 'active')->get();
            $ajks = AJKs::where('status', 'active')->get();
            
            // Check if we have users to create notifications for
            if ($admins->isEmpty() && $supervisors->isEmpty() && $teachers->isEmpty() && $ajks->isEmpty()) {
                Log::warning('No active users found to seed notifications for.');
                $this->command->warn('No active users found to seed notifications for.');
                DB::rollBack();
                return;
            }
            
            // Create notifications for each user
            $totalNotifications = 0;
            
            foreach ($admins as $admin) {
                $count = $this->createNotificationsForUser($admin->id, 'admin', $mode);
                $totalNotifications += $count;
            }
            
            foreach ($supervisors as $supervisor) {
                $count = $this->createNotificationsForUser($supervisor->id, 'supervisor', $mode);
                $totalNotifications += $count;
            }
            
            foreach ($teachers as $teacher) {
                $count = $this->createNotificationsForUser($teacher->id, 'teacher', $mode);
                $totalNotifications += $count;
            }
            
            foreach ($ajks as $ajk) {
                $count = $this->createNotificationsForUser($ajk->id, 'ajk', $mode);
                $totalNotifications += $count;
            }
            
            DB::commit();
            
            Log::info('Notifications table seeded successfully', [
                'total_notifications' => $totalNotifications
            ]);
            
            $this->command->info("Successfully created {$totalNotifications} notifications.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error seeding notifications table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->command->error('Error seeding notifications: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create notifications for a specific user.
     *
     * @param  int  $userId
     * @param  string  $userType
     * @param  string  $mode
     * @return int Number of notifications created
     */
    private function createNotificationsForUser($userId, $userType, $mode = 'full')
    {
        // Generate 5-15 random notifications per user in full mode, 3-5 in minimal mode
        $count = $mode === 'full' ? rand(5, 15) : rand(3, 5);
        
        Log::debug('Creating notifications for user', [
            'user_id' => $userId,
            'user_type' => $userType,
            'count' => $count,
            'mode' => $mode
        ]);
        
        // Define notification types and their corresponding titles and content templates
        $notificationTypes = [
            'message' => [
                'titles' => [
                    'New Message Received',
                    'Message from {sender}',
                    'New Communication',
                    'Inbox Update',
                    'Message Regarding {topic}'
                ],
                'content' => [
                    'You have received a new message from {sender}.',
                    '{sender} has sent you a message regarding {topic}.',
                    'You have a new message in your inbox from {sender} about {topic}.',
                    'Please check your inbox for a new message from {sender}.',
                    '{sender} is requesting your input on {topic}.'
                ],
                'icon' => 'fas fa-envelope',
                'color' => 'primary'
            ],
            'activity' => [
                'titles' => [
                    'Activity Reminder',
                    'Upcoming Session: {activity}',
                    'Schedule Update',
                    'New Activity Added',
                    'Activity Cancelled'
                ],
                'content' => [
                    'Reminder: {activity} is scheduled for {time}.',
                    'Your {activity} session is starting soon at {time}.',
                    'There has been an update to the schedule for {activity}.',
                    'A new activity ({activity}) has been added to your schedule.',
                    'The {activity} scheduled for {date} has been rescheduled to {time}.'
                ],
                'icon' => 'fas fa-calendar-alt',
                'color' => 'success'
            ],
            'trainee' => [
                'titles' => [
                    'Trainee Update: {trainee_name}',
                    'New Trainee Assignment',
                    'Trainee Progress Note',
                    'Trainee Attendance Alert',
                    'Trainee Assessment Due'
                ],
                'content' => [
                    'There is an update on trainee {trainee_name}\'s progress.',
                    'A new trainee has been assigned to your class.',
                    'Please review the progress notes for trainee {trainee_name}.',
                    'Trainee {trainee_name} has been absent for {days} consecutive days.',
                    'Quarterly assessment for {trainee_name} is due by {date}.'
                ],
                'icon' => 'fas fa-user-graduate',
                'color' => 'info'
            ],
            'asset' => [
                'titles' => [
                    'Asset Assignment',
                    'Inventory Update',
                    'Resource Availability',
                    'Asset Maintenance',
                    'New Equipment Arrival'
                ],
                'content' => [
                    'You have been assigned new equipment: {asset_name}.',
                    'The inventory for {centre_name} has been updated.',
                    'New resources are available for your class: {asset_name}.',
                    'Maintenance scheduled for {asset_name} on {date}.',
                    'New {asset_name} has arrived and is available for use in {centre_name}.'
                ],
                'icon' => 'fas fa-boxes',
                'color' => 'warning'
            ],
            'system' => [
                'titles' => [
                    'System Update',
                    'Maintenance Notification',
                    'Security Alert',
                    'Password Expiry',
                    'System Downtime Scheduled'
                ],
                'content' => [
                    'CREAMS system will be updated on {date}. Please save your work.',
                    'System maintenance is scheduled for {date} from {start_time} to {end_time}.',
                    'Security update has been applied to your account.',
                    'Your password will expire in {days} days. Please update it.',
                    'The system will be unavailable on {date} from {start_time} to {end_time} for scheduled maintenance.'
                ],
                'icon' => 'fas fa-cog',
                'color' => 'danger'
            ]
        ];
        
        $senderNames = [
            'Dr. Sarah Johnson',
            'Ahmad Rizal',
            'Nurul Izzah',
            'Michael Tan',
            'Dr. Suriani',
            'Prof. Abdul Hamid',
            'Siti Zainab',
            'System Administrator',
            'IT Department',
            'HR Department'
        ];
        
        $activities = [
            'Team Meeting',
            'Speech Therapy',
            'Physical Therapy',
            'Art Workshop',
            'Parent Conference',
            'Staff Training',
            'Assessment Session',
            'Music Therapy',
            'Occupational Therapy',
            'Social Skills Group'
        ];
        
        $trainees = [
            'Ahmad',
            'Siti',
            'James',
            'Mei Ling',
            'Rajesh',
            'Fatimah',
            'Xavier',
            'Aisha',
            'Harith',
            'Ling Wei'
        ];
        
        $assets = [
            'iPad Pro',
            'Speech Therapy Kit',
            'Sensory Equipment',
            'Educational Software',
            'Mobility Aids',
            'Art Supplies',
            'Assessment Tools',
            'Interactive Whiteboard',
            'Sound System',
            'Therapeutic Toys'
        ];
        
        $centres = [
            'Gombak',
            'Kuantan',
            'Gambang',
            'Pagoh',
            'Kuala Lumpur',
            'Johor Bahru'
        ];
        
        $topics = [
            'curriculum updates',
            'staff meeting',
            'quarterly review',
            'upcoming event',
            'student assessment',
            'training workshop',
            'budget allocation',
            'facility maintenance',
            'volunteer program',
            'parent feedback'
        ];
        
        $notificationsCreated = 0;
        
        // Generate random notifications
        for ($i = 0; $i < $count; $i++) {
            $type = array_rand($notificationTypes);
            $typeConfig = $notificationTypes[$type];
            
            $titleTemplate = $typeConfig['titles'][array_rand($typeConfig['titles'])];
            $contentTemplate = $typeConfig['content'][array_rand($typeConfig['content'])];
            
            // Prepare placeholders for content
            $replacements = [
                '{sender}' => $senderNames[array_rand($senderNames)],
                '{topic}' => $topics[array_rand($topics)],
                '{activity}' => $activities[array_rand($activities)],
                '{time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, rand(0, 1) ? 'AM' : 'PM'),
                '{trainee_name}' => $trainees[array_rand($trainees)],
                '{days}' => rand(1, 5),
                '{asset_name}' => $assets[array_rand($assets)],
                '{centre_name}' => $centres[array_rand($centres)],
                '{date}' => Carbon::now()->addDays(rand(1, 14))->format('F d, Y'),
                '{start_time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, 'PM'),
                '{end_time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, 'PM')
            ];
            
            $title = str_replace(array_keys($replacements), array_values($replacements), $titleTemplate);
            $content = str_replace(array_keys($replacements), array_values($replacements), $contentTemplate);
            
            // Random data for additional context (nullable)
            $data = null;
            if (rand(0, 2) == 0) { // 1/3 chance of having additional data
                $data = json_encode([
                    'action_url' => '#',
                    'action_text' => 'View Details',
                    'related_id' => rand(1000, 9999),
                    'related_type' => $type,
                    'additional_info' => 'This is additional information for the notification.',
                    'priority' => rand(1, 3)  // 1 = low, 2 = medium, 3 = high
                ]);
            }
            
            // Random timestamps
            $createdAt = Carbon::now()->subMinutes(rand(1, 60 * 24 * 7)); // Within the last week
            $read = rand(0, 2) > 0; // 2/3 chance of being read
            $readAt = $read ? $createdAt->copy()->addMinutes(rand(1, 60)) : null; // If read, set time 1-60 minutes after creation
            
            // Create the notification
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'user_type' => $userType,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'data' => $data,
                'read' => $read,
                'read_at' => $readAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
            
            $notificationsCreated++;
        }
        
        // Ensure some recent unread notifications for testing
        $recentCount = $mode === 'minimal' ? 2 : 3;
        for ($i = 0; $i < $recentCount; $i++) {
            $type = array_rand($notificationTypes);
            $typeConfig = $notificationTypes[$type];
            
            $titleTemplate = $typeConfig['titles'][array_rand($typeConfig['titles'])];
            $contentTemplate = $typeConfig['content'][array_rand($typeConfig['content'])];
            
            // Create more recent notifications (within the last few hours)
            $createdAt = Carbon::now()->subMinutes(rand(5, 180));
            
            // Prepare placeholders
            $replacements = [
                '{sender}' => $senderNames[array_rand($senderNames)],
                '{topic}' => 'urgent ' . $topics[array_rand($topics)],
                '{activity}' => $activities[array_rand($activities)],
                '{time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, rand(0, 1) ? 'AM' : 'PM'),
                '{trainee_name}' => $trainees[array_rand($trainees)],
                '{days}' => rand(1, 5),
                '{asset_name}' => $assets[array_rand($assets)],
                '{centre_name}' => $centres[array_rand($centres)],
                '{date}' => Carbon::now()->addDays(rand(1, 3))->format('F d, Y'),
                '{start_time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, 'PM'),
                '{end_time}' => sprintf('%d:%02d %s', rand(1, 12), rand(0, 5) * 10, 'PM')
            ];
            
            $title = 'URGENT: ' . str_replace(array_keys($replacements), array_values($replacements), $titleTemplate);
            $content = str_replace(array_keys($replacements), array_values($replacements), $contentTemplate);
            
            // Add action data for these important notifications
            $data = json_encode([
                'action_url' => '#',
                'action_text' => 'Take Action',
                'related_id' => rand(1000, 9999),
                'related_type' => $type,
                'priority' => 3, // High priority
                'urgent' => true
            ]);
            
            // Create the notification (always unread for these recent ones)
            DB::table('notifications')->insert([
                'user_id' => $userId,
                'user_type' => $userType,
                'type' => $type,
                'title' => $title,
                'content' => $content,
                'data' => $data,
                'read' => false,
                'read_at' => null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt
            ]);
            
            $notificationsCreated++;
        }
        
        return $notificationsCreated;
    }
}