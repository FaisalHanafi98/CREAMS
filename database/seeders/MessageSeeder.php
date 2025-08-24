<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💬 Seeding comprehensive internal messages and communications...');

        $faker = Faker::create();
        $users = DB::table('users')->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No users found! Run UserSeeder first.');
            return;
        }

        // Generate many more messages (200+) for better demo
        $messageCount = 0;
        
        // Message templates for Malaysian rehabilitation centre context
        $messageTemplates = [
            'administrative' => [
                'subjects' => [
                    'Monthly Centre Report Due',
                    'Staff Meeting Scheduled',
                    'Budget Review Required',
                    'Policy Update Notification',
                    'New Safety Procedures',
                    'Equipment Maintenance Schedule',
                    'Training Workshop Available'
                ],
                'bodies' => [
                    'Please submit your monthly centre report by the end of this week. Include trainee progress updates and resource utilization data.',
                    'All staff are required to attend the monthly meeting this Friday at 2:00 PM in the main conference room.',
                    'The quarterly budget review meeting has been scheduled. Please prepare your department expenditure reports.',
                    'New policies have been implemented regarding trainee safety protocols. Please review the attached documentation.',
                    'Updated safety procedures are now in effect. All staff must complete the safety training module.',
                    'Scheduled maintenance for rehabilitation equipment will take place next week. Please plan accordingly.',
                    'Professional development workshop on inclusive education techniques is available for registration.'
                ]
            ],
            'trainee_related' => [
                'subjects' => [
                    'Trainee Progress Update',
                    'Individual Education Plan Review',
                    'Parent-Guardian Meeting Request',
                    'Medical Documentation Required',
                    'Behavioral Intervention Update',
                    'Activity Enrollment Changes',
                    'Assessment Results Available'
                ],
                'bodies' => [
                    'Please review the latest progress reports for your assigned trainees. Some may require IEP adjustments.',
                    'The IEP review committee will meet next Tuesday to discuss individual education plans for several trainees.',
                    'Several parents have requested meetings to discuss their children\'s progress. Please coordinate schedules.',
                    'Updated medical documentation is required for trainees with recent health changes.',
                    'New behavioral intervention strategies have shown positive results. Please implement in your sessions.',
                    'Activity enrollment changes have been processed. Please check your session rosters.',
                    'Assessment results are now available in the system. Please review and provide feedback.'
                ]
            ],
            'program_updates' => [
                'subjects' => [
                    'New Therapy Program Launch',
                    'Activity Schedule Changes',
                    'Resource Allocation Update',
                    'Success Story to Share',
                    'Community Outreach Opportunity',
                    'Equipment Upgrade Notification',
                    'Volunteer Recruitment Drive'
                ],
                'bodies' => [
                    'We are launching a new sensory integration therapy program next month. Training sessions will be provided.',
                    'Activity schedules have been updated to accommodate increased enrollment. Please review your assignments.',
                    'New resources have been allocated to support expanded therapy programs. Budget details attached.',
                    'One of our trainees achieved remarkable progress this month. Let\'s celebrate this success together.',
                    'Community partnership opportunities are available for outreach programs. Please consider participating.',
                    'Upgraded therapy equipment has arrived and will be installed next week. Training sessions will follow.',
                    'We are recruiting new volunteers for our programs. Please spread the word in your networks.'
                ]
            ],
            'urgent' => [
                'subjects' => [
                    'URGENT: Emergency Protocol Update',
                    'IMPORTANT: System Maintenance Window',
                    'PRIORITY: Staff Coverage Needed',
                    'URGENT: Medical Emergency Procedures',
                    'CRITICAL: Security Access Changes',
                    'IMMEDIATE: Weather Alert Impact',
                    'URGENT: Equipment Malfunction Report'
                ],
                'bodies' => [
                    'Emergency protocols have been updated due to recent incidents. All staff must review immediately.',
                    'System maintenance is scheduled for tonight. Please save all work and log out by 6:00 PM.',
                    'Additional staff coverage is needed for tomorrow\'s sessions due to unexpected absences.',
                    'Updated medical emergency procedures are now in effect. Please familiarize yourself with the changes.',
                    'Security access codes have been changed. New credentials will be distributed this afternoon.',
                    'Weather conditions may impact transportation. Please prepare alternative arrangements for trainees.',
                    'Critical equipment malfunction reported in Room 205. Please use alternative spaces until repaired.'
                ]
            ]
        ];

        // Create messages between staff members
        foreach ($messageTemplates as $category => $templates) {
            $isUrgent = $category === 'urgent';
            $messageCount += $this->createMessagesForCategory($users, $templates, $category, $isUrgent, $faker, 40);
        }

        // Create additional cross-centre communications
        $centres = DB::table('centres')->get();
        foreach ($centres as $centre) {
            $centreUsers = $users->where('centre_id', $centre->centre_id);
            if ($centreUsers->count() >= 2) {
                $messageCount += $this->createCentreCommunications($centreUsers, $centre, $faker, 15);
            }
        }

        $this->command->info("💬 Successfully seeded {$messageCount} comprehensive messages and communications");
        
        // Show message statistics
        $stats = DB::table('messages')
            ->selectRaw('message_type, priority, count(*) as count')
            ->groupBy('message_type', 'priority')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->line("   📊 {$stat->message_type} ({$stat->priority}): {$stat->count} messages");
        }
    }

    private function createMessagesForCategory($users, $templates, $category, $isUrgent, $faker, $count): int
    {
        $messageCount = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $sender = $users->random();
            $availableReceivers = $users->where('id', '!=', $sender->id);
            
            if ($availableReceivers->count() === 0) {
                continue; // Skip if no other users available
            }
            
            $receiver = $availableReceivers->random();
            
            $subject = $templates['subjects'][array_rand($templates['subjects'])];
            $body = $templates['bodies'][array_rand($templates['bodies'])];
            
            // Add Malaysian context and details
            $body .= $this->addMalaysianContext($category, $faker);
            
            DB::table('messages')->insert([
                'sender_id' => $sender->id,
                'sender_type' => 'user',
                'subject' => $subject,
                'content' => $body,
                'message_type' => $this->getMessageType($category),
                'priority' => $isUrgent ? 'urgent' : 'normal',
                'is_draft' => false,
                'scheduled_at' => null,
                'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now()
            ]);
            
            $messageCount++;
        }
        
        return $messageCount;
    }

    private function createCentreCommunications($centreUsers, $centre, $faker, $count): int
    {
        $messageCount = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $sender = $centreUsers->random();
            $availableReceivers = $centreUsers->where('id', '!=', $sender->id);
            
            if ($availableReceivers->count() === 0) {
                continue; // Skip if no other users available
            }
            
            $receiver = $availableReceivers->random();
            
            $subjects = [
                "Re: {$centre->centre_name} Monthly Activities",
                "Trainee Progress - {$centre->centre_name}",
                "Resource Sharing - {$centre->centre_name}",
                "Coordination Meeting - {$centre->centre_name}",
                "Best Practices Discussion - {$centre->centre_name}"
            ];
            
            $bodies = [
                "I wanted to follow up on our discussion about the monthly activities at {$centre->centre_name}. The response from trainees has been very positive.",
                "The trainee progress reports for {$centre->centre_name} show excellent improvement across most programs. Let's schedule a review meeting.",
                "We have some additional resources that could benefit the programs at {$centre->centre_name}. Can we arrange a coordination meeting?",
                "The best practices we implemented at {$centre->centre_name} are showing great results. I'd like to share the methodology with other centres.",
                "Thank you for your collaboration on the recent initiatives at {$centre->centre_name}. The outcomes exceeded our expectations."
            ];
            
            DB::table('messages')->insert([
                'sender_id' => $sender->id,
                'sender_type' => 'user',
                'subject' => $subjects[array_rand($subjects)],
                'content' => $bodies[array_rand($bodies)],
                'message_type' => 'general',
                'priority' => 'normal',
                'is_draft' => false,
                'scheduled_at' => null,
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
                'updated_at' => now()
            ]);
            
            $messageCount++;
        }
        
        return $messageCount;
    }

    private function addMalaysianContext($category, $faker): string
    {
        $contexts = [
            'administrative' => [
                ' Please coordinate with the local authorities as required.',
                ' Ensure compliance with Malaysian Ministry of Health guidelines.',
                ' The report should include outcomes in both Bahasa Malaysia and English.',
                ' Consider the cultural sensitivities of our diverse trainee population.'
            ],
            'trainee_related' => [
                ' Please involve the family guardians in the discussion.',
                ' Cultural and religious considerations should be taken into account.',
                ' The assessment should be conducted in the trainee\'s preferred language.',
                ' Consider the impact of Ramadan and other religious observances on scheduling.'
            ],
            'program_updates' => [
                ' The program has been designed with Malaysian cultural values in mind.',
                ' Community involvement from local NGOs has been very positive.',
                ' We are collaborating with nearby mosques and community centers.',
                ' The initiative aligns with Malaysia\'s inclusive education objectives.'
            ],
            'urgent' => [
                ' Please treat this as high priority and respond immediately.',
                ' All centre coordinators have been notified simultaneously.',
                ' Emergency contact numbers for local authorities are available.',
                ' Follow the standard operating procedures outlined in the manual.'
            ]
        ];
        
        return $contexts[$category][array_rand($contexts[$category])];
    }

    private function getMessageType($category): string
    {
        $types = [
            'administrative' => 'announcement',
            'trainee_related' => 'general',
            'program_updates' => 'announcement',
            'urgent' => 'alert'
        ];
        
        return $types[$category] ?? 'general';
    }
}