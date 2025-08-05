<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSCommunicationSeeder extends Seeder
{
    /**
     * Run the database seeds for communication system
     */
    public function run(): void
    {
        $this->command->info('📱 Creating comprehensive CREAMS communication system...');

        try {
            DB::beginTransaction();

            // Create message categories
            $this->createMessageCategories();
            
            // Create message templates  
            $this->createMessageTemplates();
            
            // Create events
            $this->createEvents();
            
            // Create volunteers
            $this->createVolunteers();

            DB::commit();

            $this->command->info('✅ Communication system seeded successfully!');
            $this->showCommunicationStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed communication system: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create message categories for different types of communication
     */
    private function createMessageCategories(): void
    {
        $this->command->info('📂 Creating message categories...');

        $categories = [
            [
                'category_name' => 'Appointment Reminders',
                'category_description' => 'Automated reminders for therapy sessions and appointments',
                'category_icon' => 'fas fa-calendar-check',
                'category_color' => '#3498db',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'category_name' => 'Progress Updates',
                'category_description' => 'Updates on trainee progress and achievements',
                'category_icon' => 'fas fa-chart-line',
                'category_color' => '#2ecc71',
                'is_system' => false,
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'category_name' => 'Event Notifications',
                'category_description' => 'Notifications about centre events and activities',
                'category_icon' => 'fas fa-bullhorn',
                'category_color' => '#e74c3c',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'category_name' => 'Emergency Alerts',
                'category_description' => 'Critical emergency notifications',
                'category_icon' => 'fas fa-exclamation-triangle',
                'category_color' => '#e74c3c',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'category_name' => 'General Information',
                'category_description' => 'General announcements and information',
                'category_icon' => 'fas fa-info-circle',
                'category_color' => '#95a5a6',
                'is_system' => false,
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'category_name' => 'Fee Reminders',
                'category_description' => 'Payment due reminders and fee notifications',
                'category_icon' => 'fas fa-money-bill',
                'category_color' => '#f39c12',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'category_name' => 'Schedule Changes',
                'category_description' => 'Notifications about session or schedule modifications',
                'category_icon' => 'fas fa-clock',
                'category_color' => '#9b59b6',
                'is_system' => true,
                'is_active' => true,
                'sort_order' => 7
            ],
            [
                'category_name' => 'Achievement Celebrations',
                'category_description' => 'Celebrating trainee milestones and achievements',
                'category_icon' => 'fas fa-trophy',
                'category_color' => '#f1c40f',
                'is_system' => false,
                'is_active' => true,
                'sort_order' => 8
            ]
        ];

        foreach ($categories as $category) {
            DB::table('message_categories')->insert(array_merge($category, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Create message templates for various scenarios
     */
    private function createMessageTemplates(): void
    {
        $this->command->info('📝 Creating message templates...');

        $templates = [
            [
                'template_name' => 'Session Reminder - 1 Day Before',
                'template_subject' => 'Reminder: {{trainee_name}} Session Tomorrow',
                'template_body' => 'Dear {{guardian_name}}, This is a friendly reminder that {{trainee_name}} has a {{activity_name}} session scheduled for tomorrow ({{session_date}}) at {{session_time}}. Location: {{location}}. Please ensure {{trainee_name}} arrives 15 minutes early. Best regards, CREAMS Team',
                'template_type' => 'email',
                'template_variables' => json_encode(['trainee_name', 'guardian_name', 'activity_name', 'session_date', 'session_time', 'location']),
                'is_active' => true,
                'created_by' => 1,
                'centre_id' => null
            ],
            [
                'template_name' => 'Session Reminder - Same Day',
                'template_subject' => 'Today: {{trainee_name}} Session at {{session_time}}',
                'template_body' => 'Hi {{guardian_name}}, just a quick reminder that {{trainee_name}} has a session today at {{session_time}}. See you soon!',
                'template_type' => 'sms',
                'template_variables' => json_encode(['trainee_name', 'guardian_name', 'session_time']),
                'is_active' => true,
                'created_by' => 1,
                'centre_id' => null
            ],
            [
                'template_name' => 'Monthly Progress Report',
                'template_subject' => '{{trainee_name}} - Monthly Progress Update',
                'template_body' => 'Dear {{guardian_name}}, We are pleased to share {{trainee_name}} progress update for {{month_year}}. {{progress_summary}} Key Achievements: {{achievements}} Best regards, {{therapist_name}}',
                'template_type' => 'email',
                'template_variables' => json_encode(['trainee_name', 'guardian_name', 'month_year', 'progress_summary', 'achievements', 'therapist_name']),
                'is_active' => true,
                'created_by' => 1,
                'centre_id' => null
            ],
            [
                'template_name' => 'Event Invitation',
                'template_subject' => 'You are Invited: {{event_name}}',
                'template_body' => 'Dear {{guardian_name}}, You and {{trainee_name}} are cordially invited to our upcoming event: {{event_name}} on {{event_date}} at {{event_time}}. Please RSVP by {{rsvp_date}}. Best regards, CREAMS Team',
                'template_type' => 'email',
                'template_variables' => json_encode(['guardian_name', 'trainee_name', 'event_name', 'event_date', 'event_time', 'rsvp_date']),
                'is_active' => true,
                'created_by' => 1,
                'centre_id' => null
            ],
            [
                'template_name' => 'Emergency Closure Notice',
                'template_subject' => 'URGENT: Centre Closure Notice',
                'template_body' => 'URGENT NOTICE: {{centre_name}} will be CLOSED {{closure_date}} due to {{reason}}. All sessions scheduled for this day are cancelled. For urgent matters, please call {{emergency_contact}}.',
                'template_type' => 'sms',
                'template_variables' => json_encode(['centre_name', 'closure_date', 'reason', 'emergency_contact']),
                'is_active' => true,
                'created_by' => 1,
                'centre_id' => null
            ]
        ];

        foreach ($templates as $template) {
            DB::table('message_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Create realistic events for centres
     */
    private function createEvents(): void
    {
        $this->command->info('🎉 Creating centre events...');

        $centres = DB::table('centres')->get();
        $users = DB::table('users')->get();

        $eventTypes = [
            [
                'title' => 'Family Fun Day',
                'description' => 'A day of fun activities for families and children with special needs. Games, food, and entertainment for all ages.',
                'type' => 'social',
                'category' => 'family'
            ],
            [
                'title' => 'Therapy Workshop for Parents',
                'description' => 'Educational workshop teaching parents therapeutic techniques they can use at home.',
                'type' => 'educational',
                'category' => 'training'
            ],
            [
                'title' => 'Special Olympics Training',
                'description' => 'Preparation and training sessions for upcoming Special Olympics competition.',
                'type' => 'sports',
                'category' => 'competition'
            ],
            [
                'title' => 'Art Exhibition - "Abilities Beyond Limits"',
                'description' => 'Showcasing artwork created by our talented trainees, celebrating creativity and artistic expression.',
                'type' => 'exhibition',
                'category' => 'arts'
            ],
            [
                'title' => 'Volunteer Appreciation Dinner',
                'description' => 'Annual dinner to thank our dedicated volunteers for their invaluable contribution to our centre.',
                'type' => 'appreciation',
                'category' => 'volunteer'
            ],
            [
                'title' => 'Sensory Integration Workshop',
                'description' => 'Professional development workshop on sensory integration techniques for staff and therapists.',
                'type' => 'professional',
                'category' => 'training'
            ]
        ];

        foreach ($centres as $centre) {
            // Create 4-6 events per centre (past and future)
            $eventCount = rand(4, 6);
            $selectedEvents = collect($eventTypes)->random($eventCount);

            foreach ($selectedEvents as $index => $eventTemplate) {
                // Mix of past and future events
                $isUpcoming = $index < ($eventCount / 2);
                $eventDate = $isUpcoming 
                    ? Carbon::now()->addDays(rand(15, 120))
                    : Carbon::now()->subDays(rand(30, 365));

                $startTime = sprintf('%02d:%02d:00', rand(9, 14), [0, 30][rand(0, 1)]);
                $endTime = sprintf('%02d:%02d:00', rand(15, 18), [0, 30][rand(0, 1)]);

                DB::table('events')->insert([
                    'event_name' => $eventTemplate['title'],
                    'event_description' => $eventTemplate['description'],
                    'event_date' => $eventDate->format('Y-m-d'),
                    'event_start_time' => $startTime,
                    'event_end_time' => $endTime,
                    'event_location' => $this->getEventLocation($centre->centre_name),
                    'event_type' => $eventTemplate['type'],
                    'max_participants' => rand(30, 100),
                    'current_participants' => $isUpcoming ? rand(0, 25) : rand(20, 80),
                    'event_fee' => $eventTemplate['type'] == 'appreciation' ? 0 : rand(0, 50),
                    'event_status' => $isUpcoming ? 'upcoming' : 'completed',
                    'centre_id' => $centre->centre_id,
                    'organizer_id' => $users->where('centre_id', $centre->centre_id)->first()->id ?? 1,
                    'created_at' => $eventDate->copy()->subDays(rand(14, 60)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Create volunteer profiles
     */
    private function createVolunteers(): void
    {
        $this->command->info('🙋‍♀️ Creating volunteer profiles...');

        $centres = DB::table('centres')->get();
        
        $volunteerProfiles = [
            ['name' => 'Sarah Lim Wei Ling', 'email' => 'sarah.lim@email.com', 'phone' => '012-345-6789'],
            ['name' => 'Ahmad Firdaus bin Rahman', 'email' => 'ahmad.firdaus@email.com', 'phone' => '013-456-7890'],
            ['name' => 'Priya Devi Krishnan', 'email' => 'priya.krishnan@email.com', 'phone' => '014-567-8901'],
            ['name' => 'Wong Jia Hao', 'email' => 'wong.jiahao@email.com', 'phone' => '015-678-9012'],
            ['name' => 'Siti Aminah binti Ismail', 'email' => 'siti.aminah@email.com', 'phone' => '016-789-0123'],
            ['name' => 'Raj Kumar Mohan', 'email' => 'raj.kumar@email.com', 'phone' => '017-890-1234'],
            ['name' => 'Tan Mei Ling', 'email' => 'tan.meiling@email.com', 'phone' => '018-901-2345'],
            ['name' => 'Muhammad Hakim bin Abdullah', 'email' => 'hakim.abdullah@email.com', 'phone' => '019-012-3456'],
            ['name' => 'Kavitha Ramasamy', 'email' => 'kavitha.ramasamy@email.com', 'phone' => '011-123-4567'],
            ['name' => 'David Lim Choon Hock', 'email' => 'david.lim@email.com', 'phone' => '012-234-5678']
        ];

        $skills = ['Teaching', 'Arts & Crafts', 'Music', 'Sports', 'Cooking', 'Language Support', 'Technology', 'Transportation', 'Event Planning', 'Photography'];
        $availability = ['weekdays', 'weekends', 'evenings', 'flexible'];
        
        foreach ($centres as $centre) {
            // Create 6-10 volunteers per centre
            $volunteerCount = rand(6, 10);
            $selectedVolunteers = collect($volunteerProfiles)->random($volunteerCount);
            
            foreach ($selectedVolunteers as $volunteer) {
                $joinDate = Carbon::now()->subDays(rand(30, 730));
                $volunteerSkills = collect($skills)->random(rand(2, 4))->toArray();
                
                DB::table('volunteers')->insert([
                    'volunteer_name' => $volunteer['name'],
                    'volunteer_email' => $volunteer['email'],
                    'volunteer_phone' => $volunteer['phone'],
                    'volunteer_address' => $this->generateMalaysianAddress(),
                    'volunteer_birth_date' => Carbon::now()->subYears(rand(18, 65))->format('Y-m-d'),
                    'volunteer_gender' => ['male', 'female'][rand(0, 1)],
                    'volunteer_skills' => json_encode($volunteerSkills),
                    'volunteer_experience' => $this->generateVolunteerNotes(),
                    'volunteer_availability' => $availability[rand(0, count($availability) - 1)],
                    'volunteer_status' => ['active', 'active', 'active', 'pending'][rand(0, 3)], // 75% active
                    'volunteer_start_date' => $joinDate->format('Y-m-d'),
                    'emergency_contact_name' => $this->generateMalaysianName(),
                    'emergency_contact_phone' => $this->generateMalaysianPhone(),
                    'created_at' => $joinDate,
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Helper methods
     */
    private function getEventLocation($centreName): string
    {
        $locations = [
            'Main Activity Hall',
            'Therapy Garden',
            'Conference Room',
            'Sports Complex',
            'Art Studio',
            'Multi-purpose Hall'
        ];
        
        return $centreName . ' - ' . $locations[rand(0, count($locations) - 1)];
    }

    private function generateEventEmail($centreId): string
    {
        return "events.centre{$centreId}@iium.edu.my";
    }

    private function generateMalaysianPhone(): string
    {
        $prefixes = ['012', '013', '014', '016', '017', '018', '019'];
        $prefix = $prefixes[rand(0, count($prefixes) - 1)];
        $number = str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        return $prefix . '-' . substr($number, 0, 3) . '-' . substr($number, 3);
    }

    private function generateMalaysianAddress(): string
    {
        $streets = ['Jalan Tun Razak', 'Jalan Ampang', 'Jalan Sultan Ismail', 'Jalan Bukit Bintang'];
        $areas = ['Kuala Lumpur', 'Petaling Jaya', 'Shah Alam', 'Subang Jaya', 'Gombak', 'Kuantan'];
        
        $number = rand(1, 999);
        $street = $streets[rand(0, count($streets) - 1)];
        $area = $areas[rand(0, count($areas) - 1)];
        $postcode = str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT);
        
        return "{$number}, {$street}, {$postcode} {$area}, Malaysia";
    }

    private function generateMalaysianIC(): string
    {
        $year = str_pad(rand(70, 99), 2, '0', STR_PAD_LEFT);
        $month = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $birth = rand(1, 9);
        $gender = rand(0, 9);
        $check = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
        
        return $year . $month . $day . $birth . $gender . $check;
    }

    private function generateMalaysianName(): string
    {
        $names = [
            'Ahmad Rahman', 'Siti Fatimah', 'Lim Wei Ming', 'Priya Sharma',
            'Wong Jia Jun', 'Nurul Aina', 'Raj Kumar', 'Tan Siew Lan'
        ];
        
        return $names[rand(0, count($names) - 1)];
    }

    private function getRandomOccupation(): string
    {
        $occupations = [
            'Teacher', 'Engineer', 'Doctor', 'Nurse', 'Student', 'Retiree',
            'Business Owner', 'Government Officer', 'IT Professional', 'Social Worker'
        ];
        
        return $occupations[rand(0, count($occupations) - 1)];
    }

    private function generateVolunteerNotes(): string
    {
        $notes = [
            'Very dedicated volunteer with excellent communication skills',
            'Great with children, patient and understanding',
            'Reliable and punctual, always available when needed',
            'Has experience working with special needs individuals',
            'Brings positive energy to all activities'
        ];
        
        return $notes[rand(0, count($notes) - 1)];
    }

    /**
     * Show communication statistics
     */
    private function showCommunicationStatistics(): void
    {
        $this->command->info("\n📊 COMMUNICATION SYSTEM STATISTICS:");
        
        $categories = DB::table('message_categories')->count();
        $templates = DB::table('message_templates')->count();
        $events = DB::table('events')->count();
        $volunteers = DB::table('volunteers')->count();
        
        $upcomingEvents = DB::table('events')->where('status', 'upcoming')->count();
        $activeVolunteers = DB::table('volunteers')->where('status', 'active')->count();
        
        $this->command->line("   📂 Message Categories: {$categories}");
        $this->command->line("   📝 Message Templates: {$templates}");
        $this->command->line("   🎉 Total Events: {$events}");
        $this->command->line("   📅 Upcoming Events: {$upcomingEvents}");
        $this->command->line("   🙋‍♀️ Total Volunteers: {$volunteers}");
        $this->command->line("   ✅ Active Volunteers: {$activeVolunteers}");
        
        $this->command->info("   ✅ Complete communication and event management system ready!");
    }
}