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
use App\Models\Messages;

class MessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            Log::info('Starting messages table seeding');
            
            // Check if table already has data
            if (Messages::count() > 0) {
                if (!$this->command->confirm('Messages table already has data. Continue with seeding?')) {
                    $this->command->info('Seeding aborted!');
                    return;
                }
            }
            
            // Get seeding mode based on environment
            $mode = config('app.env') === 'production' ? 'minimal' : 'full';
            Log::info("Seeding messages in {$mode} mode");
            
            DB::beginTransaction();
            
            // Get some users from each role for test messages
            $admins = Admins::where('status', 'active')->limit($mode === 'minimal' ? 1 : 2)->get();
            $supervisors = Supervisors::where('status', 'active')->limit($mode === 'minimal' ? 1 : 2)->get();
            $teachers = Teachers::where('status', 'active')->limit($mode === 'minimal' ? 2 : 3)->get();
            $ajks = AJKs::where('status', 'active')->limit($mode === 'minimal' ? 1 : 2)->get();
            
            // Check if we have enough users to create messages
            if ($admins->isEmpty() || $supervisors->isEmpty() || $teachers->isEmpty()) {
                Log::warning('Not enough users found to seed messages. Please create users first.');
                $this->command->warn('Not enough users found to seed messages. Please create users first.');
                return;
            }
            
            // Create sample messages between these users
            $this->createSampleMessages($admins, $supervisors, $teachers, $ajks, $mode);
            
            DB::commit();
            
            Log::info('Messages table seeded successfully');
            $this->command->info('Messages seeded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error seeding messages table', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->command->error('Error seeding messages: ' . $e->getMessage());
            
            throw $e;
        }
    }
    
    /**
     * Create sample messages between users.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $admins
     * @param  \Illuminate\Database\Eloquent\Collection  $supervisors
     * @param  \Illuminate\Database\Eloquent\Collection  $teachers
     * @param  \Illuminate\Database\Eloquent\Collection  $ajks
     * @param  string  $mode
     * @return void
     */
    private function createSampleMessages($admins, $supervisors, $teachers, $ajks, $mode)
    {
        // Sample message subjects
        $subjects = [
            'Welcome to CREAMS!',
            'Important Update on Centre Operations',
            'Regarding Upcoming Training Session',
            'Schedule Change Notification',
            'Feedback Request',
            'Monthly Performance Review',
            'System Update Notification',
            'Holiday Schedule Information',
            'Request for Information',
            'Trainee Progress Report Discussion',
            'Year-End Evaluation Process',
            'New Resources Available',
            'Staff Meeting Agenda',
            'Training Workshop Registration',
            'Community Event Planning'
        ];
        
        // Message content templates
        $contentTemplates = [
            'Hello {recipient},'."\n\n".'I hope this message finds you well. {message_body}. '."\n\n".'Best regards,'."\n".'{sender}',
            'Dear {recipient},'."\n\n".'I wanted to reach out about {message_body}. Please let me know if you have any questions.'."\n\n".'Thanks,'."\n".'{sender}',
            'Hi {recipient},'."\n\n".'{message_body}. I look forward to your response.'."\n\n".'Regards,'."\n".'{sender}',
            'Greetings {recipient},'."\n\n".'I am writing to inform you that {message_body}. Please review at your earliest convenience.'."\n\n".'Sincerely,'."\n".'{sender}',
            'Dear {recipient},'."\n\n".'I hope you are doing well. {message_body}. '."\n\n".'Warm regards,'."\n".'{sender}',
            '{recipient},'."\n\n".'Just a quick note to let you know that {message_body}. '."\n\n".'Cheers,'."\n".'{sender}'
        ];
        
        // Message bodies with more variety
        $messageBodies = [
            'We have scheduled a staff meeting for next Friday at 2 PM to discuss the upcoming changes to our curriculum',
            'The new assessment tools for tracking trainee progress have been uploaded to the shared drive for your review',
            'I wanted to check if you received the updated materials for the speech therapy session',
            'Could we schedule a time to discuss the progress of the new trainees in your class?',
            'The maintenance team will be upgrading the centre facilities next weekend, so please plan accordingly',
            'We have received positive feedback from the parents about the recent activities you organized',
            'The monthly progress reports are due by the end of this week',
            'There has been a change in the schedule for the upcoming training workshop',
            'Please review and approve the attached list of resources needed for the next term',
            'We need to discuss the individualized education plan for trainee ID #45892',
            'I need your input on the new assessment framework we\'re implementing next month',
            'Please review the quarterly performance metrics for your team - some trainees are showing excellent progress',
            'The system maintenance is scheduled for this weekend. Please ensure all reports are submitted beforehand',
            'We\'re organizing a community awareness day and would appreciate your participation',
            'Could you provide feedback on the new training materials before we distribute them?',
            'The parent-teacher meetings have been rescheduled due to the holiday next week',
            'We\'ve updated the security protocols for accessing trainee records - please review the changes',
            'Your request for additional teaching materials has been approved',
            'There\'s a professional development workshop next month that might interest you',
            'Could you share the success stories from your recent therapeutic session?'
        ];
        
        // Create admin to supervisor conversations (threads of messages back and forth)
        foreach ($admins as $admin) {
            foreach ($supervisors as $supervisor) {
                // Create a conversation thread with 3-5 messages
                $messageCount = $mode === 'minimal' ? 2 : rand(3, 5);
                $subject = $subjects[array_rand($subjects)];
                $lastSent = Carbon::now()->subDays(rand(2, 30));
                
                for ($i = 0; $i < $messageCount; $i++) {
                    // Alternate sender and recipient
                    if ($i % 2 == 0) {
                        $senderId = $admin->id;
                        $senderType = 'admin';
                        $senderName = $admin->name;
                        $recipientId = $supervisor->id;
                        $recipientType = 'supervisor';
                        $recipientName = $supervisor->name;
                    } else {
                        $senderId = $supervisor->id;
                        $senderType = 'supervisor';
                        $senderName = $supervisor->name;
                        $recipientId = $admin->id;
                        $recipientType = 'admin';
                        $recipientName = $admin->name;
                    }
                    
                    // Replies should use the same subject with Re: prefix
                    $messageSubject = ($i == 0) ? $subject : 'Re: ' . $subject;
                    
                    // Each message is a few hours or a day after the previous
                    $createdAt = $lastSent->copy()->addHours(rand(2, 24));
                    $lastSent = $createdAt;
                    
                    $this->createMessage(
                        $senderId, $senderType, $senderName,
                        $recipientId, $recipientType, $recipientName,
                        $messageSubject,
                        $this->formatContent($contentTemplates[array_rand($contentTemplates)], $recipientName, $senderName, $messageBodies[array_rand($messageBodies)]),
                        $i < $messageCount - 1 // Only the last message might be unread
                    );
                }
            }
        }
        
        // Create supervisor to teacher messages
        foreach ($supervisors as $supervisor) {
            foreach ($teachers as $teacher) {
                $this->createMessage(
                    $supervisor->id, 'supervisor', $supervisor->name,
                    $teacher->id, 'teacher', $teacher->name,
                    $subjects[array_rand($subjects)],
                    $this->formatContent($contentTemplates[array_rand($contentTemplates)], $teacher->name, $supervisor->name, $messageBodies[array_rand($messageBodies)]),
                    rand(0, 1)
                );
                
                // Create replies with 50% chance
                if (rand(0, 1)) {
                    $this->createMessage(
                        $teacher->id, 'teacher', $teacher->name,
                        $supervisor->id, 'supervisor', $supervisor->name,
                        'Re: ' . $subjects[array_rand($subjects)],
                        $this->formatContent($contentTemplates[array_rand($contentTemplates)], $supervisor->name, $teacher->name, $messageBodies[array_rand($messageBodies)]),
                        rand(0, 1)
                    );
                }
            }
        }
        
        // Create AJK to admin messages
        foreach ($ajks as $ajk) {
            foreach ($admins as $admin) {
                $this->createMessage(
                    $ajk->id, 'ajk', $ajk->name,
                    $admin->id, 'admin', $admin->name,
                    $subjects[array_rand($subjects)],
                    $this->formatContent($contentTemplates[array_rand($contentTemplates)], $admin->name, $ajk->name, $messageBodies[array_rand($messageBodies)]),
                    rand(0, 1)
                );
                
                // Create replies with 30% chance
                if (rand(0, 2) == 0) {
                    $this->createMessage(
                        $admin->id, 'admin', $admin->name,
                        $ajk->id, 'ajk', $ajk->name,
                        'Re: ' . $subjects[array_rand($subjects)],
                        $this->formatContent($contentTemplates[array_rand($contentTemplates)], $ajk->name, $admin->name, $messageBodies[array_rand($messageBodies)]),
                        rand(0, 1)
                    );
                }
            }
        }
        
        // Create teacher to teacher messages
        if (count($teachers) >= 2) {
            for ($i = 0; $i < count($teachers) - 1; $i++) {
                $this->createMessage(
                    $teachers[$i]->id, 'teacher', $teachers[$i]->name,
                    $teachers[$i + 1]->id, 'teacher', $teachers[$i + 1]->name,
                    $subjects[array_rand($subjects)],
                    $this->formatContent($contentTemplates[array_rand($contentTemplates)], $teachers[$i + 1]->name, $teachers[$i]->name, $messageBodies[array_rand($messageBodies)]),
                    rand(0, 1)
                );
            }
        }
        
        // Create a few very recent messages that should be unread
        $recentUsers = [
            ['type' => 'admin', 'users' => $admins],
            ['type' => 'supervisor', 'users' => $supervisors],
            ['type' => 'teacher', 'users' => $teachers],
            ['type' => 'ajk', 'users' => $ajks]
        ];
        
        // Create 3-5 recent messages
        $recentCount = $mode === 'minimal' ? 3 : 5;
        for ($i = 0; $i < $recentCount; $i++) {
            // Select random sender and recipient
            $senderGroup = $recentUsers[array_rand($recentUsers)];
            $recipientGroup = $recentUsers[array_rand($recentUsers)];
            
            if ($senderGroup['users']->isEmpty() || $recipientGroup['users']->isEmpty()) {
                continue;
            }
            
            $sender = $senderGroup['users']->random();
            $recipient = $recipientGroup['users']->random();
            
            // Don't send to self
            if ($senderGroup['type'] === $recipientGroup['type'] && $sender->id === $recipient->id) {
                if ($recipientGroup['users']->count() > 1) {
                    // Try to get a different recipient
                    $recipient = $recipientGroup['users']->filter(function($user) use ($sender) {
                        return $user->id !== $sender->id;
                    })->first();
                    
                    if (!$recipient) {
                        continue;
                    }
                } else {
                    continue;
                }
            }
            
            // Create recent message (from last few hours)
            $this->createMessage(
                $sender->id, $senderGroup['type'], $sender->name,
                $recipient->id, $recipientGroup['type'], $recipient->name,
                'URGENT: ' . $subjects[array_rand($subjects)],
                $this->formatContent($contentTemplates[array_rand($contentTemplates)], $recipient->name, $sender->name, $messageBodies[array_rand($messageBodies)]),
                false, // Unread
                Carbon::now()->subHours(rand(1, 5))->subMinutes(rand(1, 59)) // Recent
            );
        }
    }
    
    /**
     * Format message content with sender, recipient, and message body.
     *
     * @param  string  $template
     * @param  string  $recipient
     * @param  string  $sender
     * @param  string  $messageBody
     * @return string
     */
    private function formatContent($template, $recipient, $sender, $messageBody)
    {
        return str_replace(
            ['{recipient}', '{sender}', '{message_body}'],
            [$recipient, $sender, $messageBody],
            $template
        );
    }
    
    /**
     * Create a message record.
     *
     * @param  int  $senderId
     * @param  string  $senderType
     * @param  string  $senderName
     * @param  int  $recipientId
     * @param  string  $recipientType
     * @param  string  $recipientName
     * @param  string  $subject
     * @param  string  $content
     * @param  bool  $read
     * @param  \Carbon\Carbon|null  $createdAt
     * @return void
     */
    private function createMessage($senderId, $senderType, $senderName, $recipientId, $recipientType, $recipientName, $subject, $content, $read, $createdAt = null)
    {
        $now = Carbon::now();
        $createdAt = $createdAt ?? Carbon::now()->subMinutes(rand(5, 60 * 24 * 10)); // Random time within the past 10 days
        
        $data = [
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'recipient_id' => $recipientId,
            'recipient_type' => $recipientType,
            'subject' => $subject,
            'content' => $content,
            'read' => $read,
            'read_at' => $read ? $createdAt->copy()->addMinutes(rand(1, 60)) : null, // If read, set read time 1-60 minutes after creation
            'created_at' => $createdAt,
            'updated_at' => $createdAt
        ];
        
        DB::table('messages')->insert($data);
        
        // Log message creation
        Log::debug('Created sample message', [
            'sender' => $senderName,
            'recipient' => $recipientName,
            'subject' => $subject,
            'read' => $read,
            'created_at' => $createdAt->toDateTimeString()
        ]);
    }
}