<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\User;
use App\Models\Centre;

class CREAMSRehabilitationActivitiesSeeder extends Seeder
{
    /**
     * Malaysian rehabilitation activities with bilingual names
     */
    private array $rehabilitationActivities = [
        [
            'activity_code' => 'ST001',
            'name' => 'Terapi Pertuturan dan Bahasa / Speech and Language Therapy',
            'category' => 'Speech Therapy',
            'description' => 'Terapi komprehensif untuk mengatasi masalah komunikasi, pertuturan, dan bahasa. Comprehensive therapy to address communication, speech, and language difficulties.',
            'required_materials' => ['Picture cards', 'communication boards', 'speech therapy tools', 'mirrors', 'audio recording devices'],
            'learning_objectives' => ['Meningkatkan kemahiran komunikasi verbal dan bukan verbal', 'Improve verbal and non-verbal communication skills'],
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 3,
            'duration_minutes' => 45,
        ],
        [
            'activity_code' => 'OT001',
            'name' => 'Terapi Okupasi / Occupational Therapy',
            'category' => 'Occupational Therapy',
            'description' => 'Terapi untuk meningkatkan kemahiran motor halus, motor kasar, dan aktiviti kehidupan seharian. Therapy to improve fine motor, gross motor, and daily living skills.',
            'required_materials' => ['Sensory tools', 'fine motor activities', 'adaptive equipment', 'therapeutic toys'],
            'learning_objectives' => ['Meningkatkan kemandirian dalam aktiviti harian', 'Enhance independence in daily activities'],
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 4,
            'duration_minutes' => 60,
        ],
        [
            'activity_code' => 'PT001',
            'activity_name' => 'Fisioterapi Pediatrik / Pediatric Physiotherapy',
            'category' => 'Physical Therapy',
            'activity_type' => 'Individual',
            'description' => 'Terapi fizikal khusus untuk kanak-kanak bagi meningkatkan fungsi motor dan mobiliti. Specialized physical therapy for children to improve motor function and mobility.',
            'objectives' => 'Meningkatkan kekuatan, keseimbangan, dan mobiliti / Improve strength, balance, and mobility',
            'materials_needed' => 'Exercise equipment, balance boards, therapy balls, walking aids',
            'skills_developed' => ['Gross Motor Skills', 'Balance', 'Coordination', 'Strength', 'Mobility'],
            'age_group' => '2-18 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 2,
            'duration_minutes' => 45,
            'location_type' => 'Physiotherapy Room',
            'requires_equipment' => true,
            'equipment_list' => ['Exercise equipment', 'Balance training tools', 'Mobility aids']
        ],
        [
            'activity_code' => 'BT001',
            'activity_name' => 'Intervensi Tingkah Laku / Behavioral Intervention',
            'category' => 'Behavioral Therapy',
            'activity_type' => 'Both',
            'description' => 'Program intervensi khusus untuk mengurangkan tingkah laku mencabar dan meningkatkan tingkah laku positif. Specialized intervention program to reduce challenging behaviors and promote positive behaviors.',
            'objectives' => 'Mengurangkan tingkah laku mencabar dan meningkatkan kemahiran sosial / Reduce challenging behaviors and improve social skills',
            'materials_needed' => 'Behavior tracking sheets, reward systems, visual schedules, social stories',
            'skills_developed' => ['Self-regulation', 'Social Skills', 'Emotional Control', 'Following Instructions', 'Positive Behaviors'],
            'age_group' => '3-16 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 6,
            'duration_minutes' => 60,
            'location_type' => 'Behavioral Therapy Room',
            'requires_equipment' => false,
            'equipment_list' => ['Visual aids', 'Tracking materials', 'Reward systems']
        ],
        [
            'activity_code' => 'SI001',
            'activity_name' => 'Integrasi Sensori / Sensory Integration',
            'category' => 'Sensory Integration',
            'activity_type' => 'Individual',
            'description' => 'Terapi untuk membantu kanak-kanak memproses dan bertindak balas kepada maklumat sensori dengan lebih baik. Therapy to help children better process and respond to sensory information.',
            'objectives' => 'Meningkatkan pemprosesan sensori dan regulasi diri / Improve sensory processing and self-regulation',
            'materials_needed' => 'Sensory equipment, textured materials, weighted items, fidget tools',
            'skills_developed' => ['Sensory Processing', 'Self-regulation', 'Attention', 'Motor Planning', 'Emotional Regulation'],
            'age_group' => '3-12 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 3,
            'duration_minutes' => 45,
            'location_type' => 'Sensory Integration Room',
            'requires_equipment' => true,
            'equipment_list' => ['Sensory swing', 'Therapy balls', 'Weighted blankets', 'Textured materials']
        ],
        [
            'activity_code' => 'SS001',
            'activity_name' => 'Latihan Kemahiran Sosial / Social Skills Training',
            'category' => 'Social Skills',
            'activity_type' => 'Group',
            'description' => 'Program kumpulan untuk mengajar kemahiran interaksi sosial, persahabatan, dan komunikasi. Group program to teach social interaction, friendship, and communication skills.',
            'objectives' => 'Meningkatkan kemahiran berinteraksi dan berkomunikasi dengan orang lain / Improve skills in interacting and communicating with others',
            'materials_needed' => 'Role-play scenarios, social stories, group games, communication cards',
            'skills_developed' => ['Social Interaction', 'Friendship Skills', 'Communication', 'Empathy', 'Problem Solving'],
            'age_group' => '5-16 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 3,
            'max_participants' => 8,
            'duration_minutes' => 60,
            'location_type' => 'Group Activity Room',
            'requires_equipment' => false,
            'equipment_list' => ['Games', 'Activity materials', 'Visual aids']
        ],
        [
            'activity_code' => 'LS001',
            'activity_name' => 'Kemahiran Hidup Harian / Daily Living Skills',
            'category' => 'Life Skills',
            'activity_type' => 'Both',
            'description' => 'Latihan kemahiran asas seperti makan, mandi, berpakaian, dan kebersihan diri. Training in basic skills such as eating, bathing, dressing, and personal hygiene.',
            'objectives' => 'Meningkatkan kemandirian dalam aktiviti kehidupan seharian / Increase independence in daily living activities',
            'materials_needed' => 'Daily living training materials, adaptive tools, practice items',
            'skills_developed' => ['Self-care Skills', 'Independence', 'Personal Hygiene', 'Feeding Skills', 'Dressing Skills'],
            'age_group' => '3-18 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 4,
            'duration_minutes' => 45,
            'location_type' => 'Life Skills Training Room',
            'requires_equipment' => true,
            'equipment_list' => ['Training materials', 'Adaptive equipment', 'Practice tools']
        ],
        [
            'activity_code' => 'AT001',
            'activity_name' => 'Terapi Seni / Art Therapy',
            'category' => 'Art & Creativity',
            'activity_type' => 'Both',
            'description' => 'Penggunaan seni sebagai medium terapi untuk ekspresi diri dan pemulihan emosi. Use of art as therapeutic medium for self-expression and emotional healing.',
            'objectives' => 'Meningkatkan ekspresi diri dan kesihatan mental / Improve self-expression and mental health',
            'materials_needed' => 'Art supplies, paper, paints, brushes, clay, craft materials',
            'skills_developed' => ['Creative Expression', 'Fine Motor Skills', 'Emotional Expression', 'Self-esteem', 'Focus'],
            'age_group' => '4-18 years',
            'difficulty_level' => 'Beginner',
            'min_participants' => 1,
            'max_participants' => 6,
            'duration_minutes' => 60,
            'location_type' => 'Art Therapy Room',
            'requires_equipment' => true,
            'equipment_list' => ['Art supplies', 'Craft materials', 'Display boards']
        ],
        [
            'activity_code' => 'MT001',
            'activity_name' => 'Terapi Muzik / Music Therapy',
            'category' => 'Music Therapy',
            'activity_type' => 'Both',
            'description' => 'Penggunaan muzik dan aktiviti muzik untuk mencapai objektif terapi dan pemulihan. Use of music and musical activities to achieve therapeutic and rehabilitation goals.',
            'objectives' => 'Meningkatkan komunikasi, motor, dan kemahiran sosial melalui muzik / Improve communication, motor, and social skills through music',
            'materials_needed' => 'Musical instruments, audio equipment, songbooks, rhythm tools',
            'skills_developed' => ['Auditory Processing', 'Rhythm', 'Communication', 'Social Skills', 'Emotional Expression'],
            'age_group' => '3-18 years',
            'difficulty_level' => 'Beginner',
            'min_participants' => 1,
            'max_participants' => 8,
            'duration_minutes' => 45,
            'location_type' => 'Music Therapy Room',
            'requires_equipment' => true,
            'equipment_list' => ['Musical instruments', 'Audio system', 'Recording equipment']
        ],
        [
            'activity_code' => 'AS001',
            'activity_name' => 'Sokongan Akademik / Academic Support',
            'category' => 'Mathematics',
            'activity_type' => 'Both',
            'description' => 'Sokongan akademik khusus dalam matematik dengan kaedah pembelajaran yang disesuaikan. Specialized academic support in mathematics with adapted learning methods.',
            'objectives' => 'Meningkatkan kemahiran matematik asas / Improve basic mathematical skills',
            'materials_needed' => 'Math manipulatives, worksheets, educational games, calculators',
            'skills_developed' => ['Number Recognition', 'Basic Math', 'Problem Solving', 'Logical Thinking', 'Academic Skills'],
            'age_group' => '6-18 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 4,
            'duration_minutes' => 45,
            'location_type' => 'Learning Support Room',
            'requires_equipment' => false,
            'equipment_list' => ['Educational materials', 'Learning aids', 'Assessment tools']
        ],
        [
            'activity_code' => 'LIT001',
            'activity_name' => 'Kemahiran Literasi / Literacy Skills',
            'category' => 'Literacy',
            'activity_type' => 'Both',
            'description' => 'Program literasi untuk meningkatkan kemahiran membaca, menulis, dan pemahaman bahasa. Literacy program to improve reading, writing, and language comprehension skills.',
            'objectives' => 'Meningkatkan kemahiran membaca dan menulis / Improve reading and writing skills',
            'materials_needed' => 'Books, reading materials, writing tools, phonics materials',
            'skills_developed' => ['Reading', 'Writing', 'Phonics', 'Comprehension', 'Vocabulary'],
            'age_group' => '5-16 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 4,
            'duration_minutes' => 45,
            'location_type' => 'Reading Room',
            'requires_equipment' => false,
            'equipment_list' => ['Books', 'Writing materials', 'Phonics tools']
        ],
        [
            'activity_code' => 'CS001',
            'activity_name' => 'Kemahiran Komputer / Computer Skills',
            'category' => 'Computer Skills',
            'activity_type' => 'Both',
            'description' => 'Latihan kemahiran komputer asas dan penggunaan teknologi assistif. Training in basic computer skills and assistive technology use.',
            'objectives' => 'Meningkatkan literasi digital dan kemahiran teknologi / Improve digital literacy and technology skills',
            'materials_needed' => 'Computers, tablets, assistive software, educational programs',
            'skills_developed' => ['Digital Literacy', 'Technology Skills', 'Problem Solving', 'Independence', 'Communication'],
            'age_group' => '8-18 years',
            'difficulty_level' => 'Intermediate',
            'min_participants' => 1,
            'max_participants' => 6,
            'duration_minutes' => 60,
            'location_type' => 'Computer Lab',
            'requires_equipment' => true,
            'equipment_list' => ['Computers', 'Assistive technology', 'Educational software']
        ],
        [
            'activity_code' => 'VT001',
            'activity_name' => 'Latihan Vokasional / Vocational Training',
            'category' => 'Vocational Training',
            'activity_type' => 'Group',
            'description' => 'Program latihan vokasional untuk mempersiapkan remaja untuk kehidupan bekerja. Vocational training program to prepare teenagers for working life.',
            'objectives' => 'Mempersiapkan kemahiran kerja dan vokasional / Prepare work and vocational skills',
            'materials_needed' => 'Work simulation materials, tools, training equipment',
            'skills_developed' => ['Work Skills', 'Following Instructions', 'Task Completion', 'Social Skills', 'Independence'],
            'age_group' => '14-18 years',
            'difficulty_level' => 'Advanced',
            'min_participants' => 3,
            'max_participants' => 8,
            'duration_minutes' => 90,
            'location_type' => 'Vocational Training Workshop',
            'requires_equipment' => true,
            'equipment_list' => ['Training tools', 'Work simulation equipment', 'Safety equipment']
        ]
    ];

    public function run(): void
    {
        $this->command->info('🎯 Creating Malaysian rehabilitation activities...');

        $centres = Centre::all();
        $teachers = User::where('role', 'teacher')->get();

        if ($centres->isEmpty()) {
            $this->command->error('No centres found! Please run CREAMSCentresSeeder first.');
            return;
        }

        if ($teachers->isEmpty()) {
            $this->command->error('No teachers found! Please run CREAMSMalaysianStaffSeeder first.');
            return;
        }

        $totalActivities = 0;

        foreach ($centres as $centre) {
            $this->command->info("\n🏢 Creating activities for {$centre->centre_name}...");
            
            // Create subset of activities for each centre based on specialization
            $activitiesToCreate = $this->getActivitiesForCentre($centre->centre_id);
            $centreTeachers = $teachers->where('centre_id', $centre->centre_id);
            
            foreach ($activitiesToCreate as $activityData) {
                $teacher = $centreTeachers->random();
                $activity = $this->createActivity($activityData, $centre, $teacher);
                $totalActivities++;
                
                $this->command->line("   ✅ {$activity->name} (Teacher: {$teacher->name})");
            }
        }

        $this->showActivitySummary($totalActivities);
    }

    private function getActivitiesForCentre(string $centreId): array
    {
        // Different centres may specialize in different types of activities
        switch ($centreId) {
            case '01': // Gombak (Main centre) - All activities
                return $this->rehabilitationActivities;
                
            case '02': // Kuantan (Specialized) - Focus on autism and developmental
                return array_filter($this->rehabilitationActivities, function($activity) {
                    $category = $activity['category'] ?? '';
                    return in_array($category, [
                        'Speech Therapy', 'Behavioral Therapy', 'Sensory Integration',
                        'Social Skills', 'Occupational Therapy', 'Art & Creativity'
                    ]);
                });
                
            case '03': // Pagoh (Community-based) - Focus on life skills and vocational
                return array_filter($this->rehabilitationActivities, function($activity) {
                    $category = $activity['category'] ?? '';
                    return in_array($category, [
                        'Life Skills', 'Vocational Training', 'Computer Skills',
                        'Physical Therapy', 'Social Skills', 'Mathematics', 'Literacy'
                    ]);
                });
                
            default:
                return array_slice($this->rehabilitationActivities, 0, 8); // Basic set
        }
    }

    private function createActivity(array $activityData, $centre, $teacher): Activity
    {
        $activityId = $activityData['activity_code'] . '_' . $centre->centre_id;
        
        // Check if activity already exists
        $existingActivity = Activity::where('activity_id', $activityId)->first();
        if ($existingActivity) {
            return $existingActivity;
        }
        
        return Activity::create([
            'activity_id' => $activityId,
            'activity_name' => $activityData['activity_name'] ?? $activityData['name'],
            'activity_description' => $activityData['description'],
            'activity_type' => $activityData['activity_type'] ?? 'Individual',
            'activity_date' => now()->addDays(rand(1, 30))->format('Y-m-d'),
            'activity_start_time' => '09:00:00',
            'activity_end_time' => sprintf('%02d:%02d:00', 
                9 + intval($activityData['duration_minutes'] / 60), 
                $activityData['duration_minutes'] % 60
            ),
            'activity_location' => $activityData['location_type'] ?? 'Therapy Room',
            'max_participants' => $activityData['max_participants'],
            'current_participants' => 0,
            'activity_goals' => isset($activityData['objectives']) ? 
                $activityData['objectives'] : 
                (is_array($activityData['learning_objectives'] ?? []) ? 
                    implode(', ', $activityData['learning_objectives']) : 
                    'General rehabilitation goals'
                ),
            'activity_outcomes' => null,
            'required_resources' => isset($activityData['materials_needed']) ? 
                (is_string($activityData['materials_needed']) ? 
                    json_encode(explode(', ', $activityData['materials_needed'])) : 
                    json_encode($activityData['materials_needed'])
                ) : 
                (is_array($activityData['required_materials'] ?? []) ? 
                    json_encode($activityData['required_materials']) : 
                    json_encode(['Basic therapy materials'])
                ),
            'activity_status' => 'scheduled',
            'centre_id' => $centre->centre_id,
            'category_id' => null, // Will be set later if needed
            'created_by' => $teacher->id,
            'instructor_id' => $teacher->id,
        ]);
    }

    private function showActivitySummary(int $totalActivities): void
    {
        $this->command->info("\n📊 Malaysian Rehabilitation Activity Summary:");
        
        // Summary by centre
        $centreStats = Activity::join('centres', 'activities.centre_id', '=', 'centres.centre_id')
            ->selectRaw('centres.centre_name, COUNT(*) as count')
            ->groupBy('centres.centre_name')
            ->get();
            
        foreach ($centreStats as $stat) {
            $this->command->info("🏢 {$stat->centre_name}: {$stat->count} activities");
        }
        
        // Summary by activity type
        $this->command->info("\n🎯 Activity Types:");
        $typeStats = Activity::selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($typeStats as $stat) {
            $this->command->line("   📋 {$stat->activity_type}: {$stat->count} activities");
        }
        
        // Summary by status
        $this->command->info("\n🔄 Activity Status:");
        $statusStats = Activity::selectRaw('activity_status, COUNT(*) as count')
            ->groupBy('activity_status')
            ->get();
            
        foreach ($statusStats as $stat) {
            $this->command->line("   ⚙️ {$stat->activity_status}: {$stat->count} activities");
        }

        $this->command->info("\n🎯 Total: {$totalActivities} rehabilitation activities created!");
        $this->command->info("✅ All activities include Malaysian context with bilingual names!");
        $this->command->info("🇲🇾 Activity reflect real Malaysian rehabilitation programs!");
        $this->command->info("👨‍⚕️ Each activity assigned to qualified therapists/teachers!");
    }
}