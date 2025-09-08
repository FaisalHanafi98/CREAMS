<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSSeederCommunicationManagement extends Seeder
{
    /**
     * CREAMS Communication Management Seeder
     * Seeds: Messages, notifications, letters, templates
     */
    public function run(): void
    {
        $this->command->info('📨 Seeding CREAMS Communication Management...');
        
        // Create letter templates
        $this->command->info('   📝 Creating letter templates...');
        $this->seedLetterTemplates();
        
        // Create messages between staff and parents
        $this->command->info('   💬 Creating messages...');
        $this->seedMessages();
        
        // Create system notifications
        $this->command->info('   🔔 Creating notifications...');
        $this->seedNotifications();
        
        // Create official letters
        $this->command->info('   📄 Creating letters...');
        $this->seedLetters();
        
        $this->command->info('✅ Communication Management seeding completed');
    }
    
    private function seedLetterTemplates(): void
    {
        $templates = [
            [
                'template_name' => 'Welcome Letter',
                'template_content' => 'Dear {{parent_name}},\n\nWe are pleased to welcome {{trainee_name}} to our CREAMS Rehabilitation Centre. We look forward to working together to support your child\'s development and growth.\n\nBest regards,\n{{centre_name}} Team',
                'template_type' => 'welcome',
                'is_active' => true
            ],
            [
                'template_name' => 'Progress Report Letter',
                'template_content' => 'Dear {{parent_name}},\n\nWe are writing to inform you about {{trainee_name}}\'s progress in our rehabilitation programs. Your child has shown remarkable improvement in the following areas:\n\n{{progress_details}}\n\nWe will continue to work closely with you to ensure the best outcomes for {{trainee_name}}.\n\nSincerely,\n{{instructor_name}}',
                'template_type' => 'progress_report',
                'is_active' => true
            ],
            [
                'template_name' => 'Attendance Alert',
                'template_content' => 'Dear {{parent_name}},\n\nWe have noticed that {{trainee_name}} has been absent from several sessions recently. Regular attendance is crucial for your child\'s progress in our rehabilitation programs.\n\nPlease contact us to discuss any challenges or concerns.\n\nBest regards,\n{{centre_name}} Administration',
                'template_type' => 'attendance_alert',
                'is_active' => true
            ],
            [
                'template_name' => 'Activity Completion Certificate',
                'template_content' => 'Dear {{parent_name}},\n\nCongratulations! {{trainee_name}} has successfully completed the {{activity_name}} program. This achievement demonstrates your child\'s dedication and progress.\n\nWe are proud of {{trainee_name}}\'s accomplishments and look forward to continuing this journey together.\n\nWith pride,\n{{instructor_name}}',
                'template_type' => 'certificate',
                'is_active' => true
            ],
            [
                'template_name' => 'Session Schedule Change',
                'template_content' => 'Dear {{parent_name}},\n\nWe would like to inform you of a schedule change for {{trainee_name}}\'s sessions:\n\n{{schedule_details}}\n\nPlease update your calendar accordingly. If you have any concerns, please contact us immediately.\n\nThank you for your understanding,\n{{centre_name}} Team',
                'template_type' => 'schedule_change',
                'is_active' => true
            ]
        ];
        
        foreach ($templates as $template) {
            DB::table('letter_templates')->insertOrIgnore([
                'template_name' => $template['template_name'],
                'template_content' => $template['template_content'],
                'template_type' => $template['template_type'],
                'is_active' => $template['is_active'],
                'created_by' => 1, // Default admin user
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
        
        $this->command->line('      ✓ Created ' . count($templates) . ' letter templates');
    }
    
    private function seedMessages(): void
    {
        $staff = DB::table('users')->get();
        $trainees = DB::table('trainees')->get();
        $totalMessages = 0;
        
        // Create messages between staff and parents/guardians about trainees
        foreach ($trainees->take(50) as $trainee) {
            $instructor = $staff->where('centre_id', $trainee->centre_id)->random();
            $messagesCount = rand(3, 8); // 3-8 messages per trainee
            
            for ($i = 0; $i < $messagesCount; $i++) {
                $messageDate = Carbon::now()->subDays(rand(1, 90));
                $isFromStaff = rand(1, 100) <= 60; // 60% from staff, 40% from parents
                
                if ($isFromStaff) {
                    $messages = [
                        "Good morning! {$trainee->trainee_first_name} showed excellent progress in today's communication session. Keep up the great work at home!",
                        "Please note that {$trainee->trainee_first_name} seemed a bit tired during today's activities. Please ensure adequate rest.",
                        "Wonderful news! {$trainee->trainee_first_name} achieved a new milestone in motor skills development today.",
                        "Reminder: Please bring {$trainee->trainee_first_name}'s medical reports for next week's assessment.",
                        "{$trainee->trainee_first_name} participated actively in group activities today. Great improvement in social skills!"
                    ];
                    
                    $messageId = DB::table('messages')->insertGetId([
                        'sender_id' => $instructor->id,
                        'subject' => 'Update on ' . $trainee->trainee_first_name,
                        'message_body' => $messages[array_rand($messages)],
                        'status' => rand(1, 100) <= 80 ? 'read' : 'sent',
                        'sent_at' => $messageDate,
                        'created_at' => $messageDate,
                        'updated_at' => $messageDate
                    ]);
                    
                    // Create message recipient record
                    DB::table('message_recipients')->insert([
                        'message_id' => $messageId,
                        'recipient_id' => $trainee->id, // Using trainee as proxy for parent
                        'recipient_type' => 'user',
                        'is_read' => rand(1, 100) <= 80,
                        'read_at' => rand(1, 100) <= 80 ? $messageDate : null,
                        'created_at' => $messageDate,
                        'updated_at' => $messageDate
                    ]);
                } else {
                    $parentMessages = [
                        "Thank you for the update on {$trainee->trainee_first_name}. We've noticed similar improvements at home.",
                        "Could you please provide more details about {$trainee->trainee_first_name}'s speech therapy progress?",
                        "{$trainee->trainee_first_name} mentioned enjoying the art therapy sessions. Thank you for your patience.",
                        "We'll make sure {$trainee->trainee_first_name} gets more rest before sessions. Thank you for letting us know.",
                        "Is there anything specific we should practice with {$trainee->trainee_first_name} at home?"
                    ];
                    
                    $messageId = DB::table('messages')->insertGetId([
                        'sender_id' => $trainee->id, // Using trainee as proxy for parent
                        'subject' => 'Question about ' . $trainee->trainee_first_name,
                        'message_body' => $parentMessages[array_rand($parentMessages)],
                        'status' => 'read',
                        'sent_at' => $messageDate,
                        'created_at' => $messageDate,
                        'updated_at' => $messageDate
                    ]);
                    
                    // Create message recipient record
                    DB::table('message_recipients')->insert([
                        'message_id' => $messageId,
                        'recipient_id' => $instructor->id,
                        'recipient_type' => 'user',
                        'is_read' => true,
                        'read_at' => $messageDate,
                        'created_at' => $messageDate,
                        'updated_at' => $messageDate
                    ]);
                }
                
                $totalMessages++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalMessages} messages between staff and parents");
    }
    
    private function seedNotifications(): void
    {
        $users = DB::table('users')->get();
        $totalNotifications = 0;
        
        $notificationTypes = [
            [
                'type' => 'session_reminder',
                'title' => 'Session Reminder',
                'messages' => [
                    'Reminder: You have a session starting in 30 minutes',
                    'Don\'t forget about your upcoming therapy session today',
                    'Session starting soon - please prepare materials'
                ]
            ],
            [
                'type' => 'attendance_alert',
                'title' => 'Attendance Alert',
                'messages' => [
                    'Multiple trainees absent from today\'s sessions',
                    'Low attendance rate detected for this week',
                    'Please follow up with parents regarding absences'
                ]
            ],
            [
                'type' => 'system_update',
                'title' => 'System Update',
                'messages' => [
                    'System maintenance scheduled for tonight',
                    'New features have been added to the platform',
                    'Please update your browser for better performance'
                ]
            ],
            [
                'type' => 'progress_milestone',
                'title' => 'Progress Milestone',
                'messages' => [
                    'Trainee has achieved a new developmental milestone',
                    'Progress report is ready for review',
                    'Excellent improvement noted in recent assessments'
                ]
            ]
        ];
        
        foreach ($users as $user) {
            $notificationCount = rand(5, 15);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $notificationType = $notificationTypes[array_rand($notificationTypes)];
                $message = $notificationType['messages'][array_rand($notificationType['messages'])];
                $createdDate = Carbon::now()->subDays(rand(1, 30));
                
                DB::table('notifications')->insert([
                    'type' => 'App\\Notifications\\' . $notificationType['type'],
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'title' => $notificationType['title'],
                        'message' => $message,
                        'priority' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])]
                    ]),
                    'read_at' => rand(1, 100) <= 70 ? $createdDate : null, // 70% read
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate
                ]);
                
                $totalNotifications++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalNotifications} notifications");
    }
    
    private function seedLetters(): void
    {
        $templates = DB::table('letter_templates')->get();
        $trainees = DB::table('trainees')->get();
        $staff = DB::table('users')->get();
        $totalLetters = 0;
        
        foreach ($trainees->take(75) as $trainee) {
            $instructor = $staff->where('centre_id', $trainee->centre_id)->random();
            $centre = DB::table('centres')->where('centre_id', $trainee->centre_id)->first();
            $lettersCount = rand(2, 5); // 2-5 letters per trainee
            
            for ($i = 0; $i < $lettersCount; $i++) {
                $template = $templates->random();
                $letterDate = Carbon::now()->subDays(rand(1, 90));
                
                // Replace template placeholders
                // Generate subject based on template type
                $subjects = [
                    'welcome' => 'Welcome to CREAMS Rehabilitation Centre',
                    'progress_report' => 'Progress Report for ' . $trainee->trainee_first_name,
                    'attendance_alert' => 'Attendance Concern for ' . $trainee->trainee_first_name,
                    'certificate' => 'Certificate of Completion - Communication Program',
                    'schedule_change' => 'Schedule Change Notification for ' . $trainee->trainee_first_name
                ];
                $subject = $subjects[$template->template_type] ?? 'Letter for ' . $trainee->trainee_first_name;
                $content = str_replace([
                    '{{parent_name}}', 
                    '{{trainee_name}}', 
                    '{{centre_name}}',
                    '{{instructor_name}}',
                    '{{progress_details}}',
                    '{{activity_name}}',
                    '{{schedule_details}}'
                ], [
                    'Parent/Guardian of ' . $trainee->trainee_first_name,
                    $trainee->trainee_first_name,
                    $centre->centre_name ?? 'CREAMS Centre',
                    $instructor->name ?? 'Instructor',
                    'Significant improvement in communication and motor skills',
                    'Communication Development Program',
                    'Schedule changed from Monday 9AM to Tuesday 10AM'
                ], $template->template_content);
                
                // Generate letter_id using a simple format
                $prefix = 'LTR';
                $year = $letterDate->format('Y');
                $month = $letterDate->format('m');
                $sequence = str_pad($totalLetters + 1, 4, '0', STR_PAD_LEFT);
                $letterId = "{$prefix}/{$year}/{$month}/{$sequence}";
                
                DB::table('letters')->insert([
                    'letter_id' => $letterId,
                    'template_id' => $template->id,
                    'recipient_id' => $trainee->id, // Using trainee as recipient proxy
                    'letter_type' => $template->template_type,
                    'letter_date' => $letterDate,
                    'created_by' => $instructor->id ?? 1,
                    'letter_subject' => $subject,
                    'letter_content' => $content,
                    'letter_status' => ['draft', 'sent', 'delivered'][array_rand(['draft', 'sent', 'delivered'])],
                    'recipient_name' => 'Parent/Guardian of ' . $trainee->trainee_first_name,
                    'recipient_address' => $trainee->trainee_address ?? 'Address not provided',
                    'date_sent' => rand(1, 100) <= 80 ? $letterDate->format('Y-m-d') : null,
                    'created_at' => $letterDate,
                    'updated_at' => $letterDate
                ]);
                
                $totalLetters++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalLetters} letters using templates");
    }
}