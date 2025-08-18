<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CREAMSEnhancedSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🎯 Creating Enhanced Session Schedule System...\n";
        
        // Clear existing sessions safely (handle foreign key constraints)
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            ActivitySession::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            echo "✅ Cleared existing sessions\n";
        } catch (\Exception $e) {
            // If truncate fails, delete records instead
            ActivitySession::query()->delete();
            echo "✅ Cleared existing sessions (using delete)\n";
        }
        
        $activities = Activity::with('centre', 'instructor')->get();
        $teachers = User::where('role', 'teacher')->get();
        
        $sessionCount = 0;
        $activityCount = 0;
        
        // Define time slots for different session types
        $timeSlots = [
            'morning' => [
                ['start' => '08:00', 'end' => '09:30'],
                ['start' => '09:45', 'end' => '11:15'],
                ['start' => '11:30', 'end' => '13:00']
            ],
            'afternoon' => [
                ['start' => '14:00', 'end' => '15:30'],
                ['start' => '15:45', 'end' => '17:15']
            ],
            'evening' => [
                ['start' => '18:00', 'end' => '19:30'],
                ['start' => '19:45', 'end' => '21:15']
            ]
        ];
        
        // Define venues per centre
        $venues = [
            'Gombak' => [
                'Therapy Room 1', 'Therapy Room 2', 'Therapy Room 3',
                'Activity Hall A', 'Activity Hall B', 'Computer Lab',
                'Art & Craft Room', 'Music Room', 'Sensory Room'
            ],
            'Kuantan' => [
                'Main Therapy Room', 'Group Activity Room', 'Individual Session Room',
                'Outdoor Activity Area', 'Computer Corner', 'Quiet Room'
            ],
            'Pagoh' => [
                'Rehabilitation Room', 'Learning Center', 'Skills Training Room',
                'Community Hall', 'Garden Area', 'Multi-purpose Room'
            ]
        ];
        
        foreach ($activities as $activity) {
            $activityCount++;
            $centreName = $activity->centre->centre_name ?? 'Gombak';
            
            // Get teachers from the same centre
            $centreTeachers = $teachers->where('centre_id', $activity->centre_id);
            if ($centreTeachers->isEmpty()) {
                $centreTeachers = $teachers; // Fallback to all teachers
            }
            
            // Assign primary teacher
            $primaryTeacher = $activity->instructor ?: $centreTeachers->random();
            
            // Create sessions for 6 weeks (1.5 months) minimum
            $startDate = Carbon::now()->addDays(rand(1, 7)); // Start within next week
            $endDate = $startDate->copy()->addWeeks(6);
            
            $activitySessions = [];
            $current = $startDate->copy();
            
            echo "📋 Creating sessions for: {$activity->activity_name} ({$centreName})\n";
            
            // Create 3-4 sessions per week
            $sessionsPerWeek = rand(3, 4);
            $daysOfWeek = $this->getRandomDaysOfWeek($sessionsPerWeek);
            
            while ($current <= $endDate) {
                $dayOfWeek = $current->dayOfWeek; // 0 = Sunday, 1 = Monday, etc.
                
                if (in_array($dayOfWeek, $daysOfWeek)) {
                    // Choose time slot based on activity type
                    $preferredTime = $this->getPreferredTimeSlot($activity->activity_name);
                    $timeSlot = $timeSlots[$preferredTime][array_rand($timeSlots[$preferredTime])];
                    
                    // Choose venue
                    $centreVenues = $venues[$centreName] ?? $venues['Gombak'];
                    $venue = $centreVenues[array_rand($centreVenues)];
                    
                    // Create session with correct column names
                    $session = [
                        'activity_id' => $activity->id,
                        'session_date' => $current->format('Y-m-d'),
                        'scheduled_date' => $current->format('Y-m-d'),
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'venue' => $venue,
                        'room_number' => rand(101, 350),
                        'instructor_id' => $primaryTeacher->id,
                        'teacher_id' => $primaryTeacher->id,
                        'max_capacity' => $this->getMaxParticipants($activity->activity_name),
                        'max_participants' => $this->getMaxParticipants($activity->activity_name),
                        'current_enrollment' => 0,
                        'current_participants' => 0,
                        'session_status' => $current->isFuture() ? 'scheduled' : 'completed',
                        'status' => $current->isFuture() ? 'scheduled' : 'completed',
                        'attendance_marked' => $current->isPast(),
                        'session_objectives' => json_encode($this->getSessionObjectives($activity->activity_name)),
                        'session_notes' => $this->getSessionNotes($activity->activity_name),
                        'materials_used' => json_encode($this->getSessionMaterials($activity->activity_name)),
                        'centre_id' => $activity->centre_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    $activitySessions[] = $session;
                    $sessionCount++;
                }
                
                $current->addDay();
            }
            
            // Batch insert sessions for this activity
            if (!empty($activitySessions)) {
                ActivitySession::insert($activitySessions);
                echo "   ✅ Created " . count($activitySessions) . " sessions\n";
            }
            
            // Update activity with calculated data (using existing columns)
            $activity->update([
                'activity_date' => $startDate->format('Y-m-d'),
                'activity_period' => $startDate->diffInWeeks($endDate) . ' weeks'
            ]);
        }
        
        echo "\n🎯 Enhanced Session Creation Summary:\n";
        echo "   📊 Total Activities: {$activityCount}\n";
        echo "   📅 Total Sessions Created: {$sessionCount}\n";
        echo "   ⏱️  Average Sessions per Activity: " . round($sessionCount / $activityCount, 1) . "\n";
        echo "   📋 Minimum Duration: 6 weeks per activity\n";
        echo "   📅 Minimum Sessions per Week: 3\n";
        echo "   ✅ All activities now have proper scheduling!\n\n";
    }
    
    /**
     * Get random days of week for sessions
     */
    private function getRandomDaysOfWeek(int $count): array
    {
        $weekdays = [1, 2, 3, 4, 5]; // Monday to Friday
        shuffle($weekdays);
        return array_slice($weekdays, 0, $count);
    }
    
    /**
     * Get preferred time slot based on activity type
     */
    private function getPreferredTimeSlot(string $activityName): string
    {
        $morningActivities = ['Terapi', 'Therapy', 'Assessment', 'Academic'];
        $afternoonActivities = ['Training', 'Skills', 'Social', 'Art', 'Music'];
        $eveningActivities = ['Support', 'Group', 'Community'];
        
        foreach ($morningActivities as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return 'morning';
            }
        }
        
        foreach ($afternoonActivities as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return 'afternoon';
            }
        }
        
        foreach ($eveningActivities as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return 'evening';
            }
        }
        
        // Default to morning or afternoon
        return rand(0, 1) ? 'morning' : 'afternoon';
    }
    
    /**
     * Generate session name
     */
    private function generateSessionName(string $activityName, Carbon $date): string
    {
        $week = $date->weekOfMonth;
        $shortName = substr($activityName, 0, 30);
        return "{$shortName} - Week {$week}";
    }
    
    /**
     * Generate session description
     */
    private function generateSessionDescription(string $activityName): string
    {
        $descriptions = [
            'Terapi' => 'Individual therapy session focusing on specific goals and outcomes',
            'Training' => 'Skill-building session with practical exercises and activities',
            'Support' => 'Supportive group session with peer interaction and guidance',
            'Academic' => 'Educational session covering academic skills and knowledge',
            'Social' => 'Social interaction session promoting communication and cooperation',
            'Art' => 'Creative expression session using various art mediums and techniques',
            'Music' => 'Musical therapy session incorporating rhythm, melody, and movement'
        ];
        
        foreach ($descriptions as $keyword => $desc) {
            if (stripos($activityName, $keyword) !== false) {
                return $desc;
            }
        }
        
        return 'Structured rehabilitation session with individualized goals and activities';
    }
    
    /**
     * Get maximum participants based on activity type
     */
    private function getMaxParticipants(string $activityName): int
    {
        $individualTherapy = ['Terapi Pertuturan', 'Occupational Therapy', 'Physiotherapy'];
        $smallGroup = ['Social Skills', 'Academic Support', 'Behavioral'];
        $largeGroup = ['Art', 'Music', 'Community', 'Recreation'];
        
        foreach ($individualTherapy as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return rand(1, 3); // Individual or very small group
            }
        }
        
        foreach ($smallGroup as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return rand(4, 8); // Small group
            }
        }
        
        foreach ($largeGroup as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return rand(8, 15); // Larger group
            }
        }
        
        return rand(3, 8); // Default medium group
    }
    
    /**
     * Get supervisor for the centre
     */
    private function getSupervisor(int $centreId): ?int
    {
        $supervisor = User::where('role', 'supervisor')
            ->where('centre_id', $centreId)
            ->inRandomOrder()
            ->first();
            
        return $supervisor ? $supervisor->id : null;
    }
    
    /**
     * Get priority based on activity type
     */
    private function getPriority(string $activityName): string
    {
        $highPriority = ['Terapi', 'Therapy', 'Medical', 'Emergency'];
        $mediumPriority = ['Training', 'Skills', 'Academic'];
        
        foreach ($highPriority as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return 'high';
            }
        }
        
        foreach ($mediumPriority as $keyword) {
            if (stripos($activityName, $keyword) !== false) {
                return 'medium';
            }
        }
        
        return 'normal';
    }
    
    /**
     * Get session objectives
     */
    private function getSessionObjectives(string $activityName): array
    {
        $objectives = [
            'Improve functional skills and independence',
            'Develop communication and social interaction',
            'Enhance motor skills and coordination',
            'Build confidence and self-esteem',
            'Practice daily living skills'
        ];
        
        $activitySpecific = [
            'Terapi Pertuturan' => ['Improve speech clarity', 'Enhance language comprehension', 'Develop communication strategies'],
            'Occupational' => ['Improve fine motor skills', 'Develop daily living skills', 'Enhance sensory processing'],
            'Physiotherapy' => ['Improve gross motor skills', 'Enhance balance and coordination', 'Build muscle strength'],
            'Social Skills' => ['Practice social interaction', 'Develop friendship skills', 'Learn social rules'],
            'Academic' => ['Improve reading skills', 'Develop math concepts', 'Enhance writing abilities']
        ];
        
        foreach ($activitySpecific as $keyword => $specificObjectives) {
            if (stripos($activityName, $keyword) !== false) {
                return array_merge($objectives, $specificObjectives);
            }
        }
        
        return $objectives;
    }
    
    /**
     * Get session notes
     */
    private function getSessionNotes(string $activityName): string
    {
        $notes = [
            'Session prepared with appropriate materials and equipment',
            'Individual goals reviewed and updated as needed',
            'Progress monitoring and assessment ongoing',
            'Family involvement and communication maintained',
            'Safety protocols and accommodations in place'
        ];
        
        return $notes[array_rand($notes)];
    }
    
    /**
     * Get session materials
     */
    private function getSessionMaterials(string $activityName): array
    {
        $baseMaterials = ['Assessment forms', 'Progress tracking sheets', 'Safety equipment'];
        
        $specificMaterials = [
            'Terapi Pertuturan' => ['Speech therapy tools', 'Communication boards', 'Audio recording equipment'],
            'Occupational' => ['Fine motor tools', 'Sensory equipment', 'Daily living practice items'],
            'Physiotherapy' => ['Exercise equipment', 'Balance tools', 'Mobility aids'],
            'Art' => ['Art supplies', 'Creative materials', 'Display boards'],
            'Music' => ['Musical instruments', 'Audio system', 'Rhythm tools'],
            'Academic' => ['Learning materials', 'Educational games', 'Assessment tools']
        ];
        
        foreach ($specificMaterials as $keyword => $materials) {
            if (stripos($activityName, $keyword) !== false) {
                return array_merge($baseMaterials, $materials);
            }
        }
        
        return $baseMaterials;
    }
}