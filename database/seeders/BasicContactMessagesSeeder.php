<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContactMessages;

class BasicContactMessagesSeeder extends Seeder
{
    private array $contactData = [
        [
            'sender_name' => 'Ahmad Rahman',
            'sender_email' => 'ahmad.rahman@gmail.com',
            'sender_phone' => '019-2345678',
            'subject' => 'Inquiry about Early Intervention Program',
            'body' => 'Hi, I would like to know more about the early intervention program for my 4-year-old son who has been diagnosed with autism. What are the requirements and how can I apply?',
            'category' => 'general',
            'status' => 'new'
        ],
        [
            'sender_name' => 'Siti Aisyah',
            'sender_email' => 'siti.aisyah@yahoo.com',
            'sender_phone' => '012-3456789',
            'subject' => 'Volunteer Opportunity',
            'body' => 'I am interested in volunteering at your rehabilitation center. I have experience working with children with special needs and would like to contribute to your programs.',
            'category' => 'general',
            'status' => 'read'
        ],
        [
            'sender_name' => 'David Tan',
            'sender_email' => 'david.tan@outlook.com',
            'sender_phone' => '017-4567890',
            'subject' => 'Speech Therapy Services',
            'body' => 'My daughter needs speech therapy services. Can you provide information about the available programs and the costs involved?',
            'category' => 'general',
            'status' => 'replied'
        ],
        [
            'sender_name' => 'Fatimah Zahra',
            'sender_email' => 'fatimah.z@gmail.com',
            'sender_phone' => '013-5678901',
            'subject' => 'Feedback on Services',
            'body' => 'I want to express my gratitude for the excellent services provided to my son. The occupational therapy sessions have helped him tremendously.',
            'category' => 'general',
            'status' => 'resolved'
        ],
        [
            'sender_name' => 'Raj Kumar',
            'sender_email' => 'raj.kumar@gmail.com',
            'sender_phone' => '019-6789012',
            'subject' => 'Location and Operating Hours',
            'body' => 'Could you please provide information about your center locations and operating hours? I am looking for a center close to Gombak area.',
            'category' => 'general',
            'status' => 'new'
        ],
        [
            'sender_name' => 'Nurul Hidayah',
            'sender_email' => 'nurul.hidayah@gmail.com',
            'sender_phone' => '012-7890123',
            'subject' => 'Assessment for My Child',
            'body' => 'I would like to schedule an assessment for my 5-year-old daughter who has developmental delays. What is the process and what documents do I need to bring?',
            'category' => 'general',
            'status' => 'new'
        ]
    ];

    public function run(): void
    {
        $this->command->info('📞 Creating contact messages...');

        $totalMessages = 0;

        foreach ($this->contactData as $contactInfo) {
            $message = ContactMessages::create([
                'sender_name' => $contactInfo['sender_name'],
                'sender_email' => $contactInfo['sender_email'],
                'sender_phone' => $contactInfo['sender_phone'],
                'message_subject' => $contactInfo['subject'],
                'message_body' => $contactInfo['body'],
                'message_category' => $contactInfo['category'],
                'message_status' => $contactInfo['status'],
                'admin_reply' => $contactInfo['status'] == 'replied' ? 'Thank you for your inquiry. We will contact you soon with detailed information.' : null,
                'replied_by' => $contactInfo['status'] == 'replied' ? 1 : null,
                'replied_at' => $contactInfo['status'] == 'replied' ? now()->subDays(rand(1, 5)) : null
            ]);

            $totalMessages++;
            $this->command->line("   ✅ {$message->sender_name}: {$message->message_subject} ({$message->message_status})");
        }

        $this->command->info("📞 Total contact messages created: $totalMessages");
        $this->command->info("   📋 Status distribution: new, read, replied, resolved");
    }
}