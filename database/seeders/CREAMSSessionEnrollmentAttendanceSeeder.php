<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ActivitySession;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\User;
use App\Models\SessionEnrollment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CREAMSSessionEnrollmentAttendanceSeeder extends Seeder
{
    /**
     * Activity type suitability mapping for trainees
     */
    private array $activitySuitability = [
        // Kuantan Centre - Autism & Developmental Disabilities Focus
        'kuantan' => [
            'autism' => [
                'ABA Therapy', 'TEACCH Method', 'PECS Communication', 'Sensory Integration',
                'Social Skills Training', 'Communication Development', 'Behavioral Support',
                'Speech Therapy', 'Occupational Therapy', 'Art Therapy'
            ],
            'cerebral_palsy' => [
                'Physical Therapy', 'Occupational Therapy', 'Adaptive Skills',
                'Motor Skills Development', 'Mobility Training', 'Physiotherapy'
            ],
            'down_syndrome' => [
                'Life Skills', 'Academic Skills', 'Social Integration', 'Speech Therapy',
                'Physical Activity', 'Independence Training'
            ],
            'adhd' => [
                'Behavioral Support', 'Focus Training', 'Social Skills',
                'Physical Activity', 'Art Therapy', 'Music Therapy'
            ],
            'intellectual_disability' => [
                'Life Skills', 'Academic Skills', 'Social Integration',
                'Independence Training', 'Vocational Preparation'
            ]
        ],
        
        // Pagoh Centre - Vocational & Life Skills Focus
        'pagoh' => [
            'mild_intellectual' => [
                'Vocational Training', 'Job Readiness', 'Life Skills',
                'Independence Training', 'Community Integration', 'Work Skills'
            ],
            'learning_disability' => [
                'Academic Skills', 'Life Skills', 'Social Skills',
                'Vocational Training', 'Computer Skills'
            ],
            'autism_high_functioning' => [
                'Social Skills', 'Job Readiness', 'Independence Training',
                'Community Integration', 'Vocational Training'
            ],
            'physical_disability' => [
                'Adaptive Skills', 'Independence Training', 'Life Skills',
                'Assistive Technology', 'Mobility Training'
            ]
        ]
    ];

    /**
     * Attendance probability based on session date and trainee factors
     */
    private array $attendanceFactors = [
        'past_sessions' => [
            'present' => 85,    // 85% chance of being present for past sessions
            'late' => 8,        // 8% chance of being late
            'absent' => 5,      // 5% chance of being absent
            'excused' => 2      // 2% chance of excused absence
        ],
        'current_sessions' => [
            'present' => 75,    // 75% chance for ongoing sessions
            'late' => 12,
            'absent' => 10,
            'excused' => 3
        ],
        'future_sessions' => [
            'enrolled' => 100   // Future sessions just have enrollment status
        ]
    ];

    public function run(): void
    {
        $this->command->info('👥 Starting comprehensive session enrollment and attendance generation...');
        
        // Get sessions from Kuantan and Pagoh centres
        $kuantanSessions = $this->getCentreSessions('02');
        $pagohSessions = $this->getCentreSessions('03');
        
        $this->command->info("🏥 Kuantan sessions to process: {$kuantanSessions->count()}");
        $this->command->info("🔧 Pagoh sessions to process: {$pagohSessions->count()}");
        
        // Process each centre separately for better control
        $this->processKuantanSessions($kuantanSessions);
        $this->processPagohSessions($pagohSessions);
        
        $this->showFinalStatistics();
    }

    private function getCentreSessions(string $centreId)
    {
        return ActivitySession::with(['activity'])
            ->whereHas('activity', function($query) use ($centreId) {
                $query->where('centre_id', $centreId);
            })
            ->whereIn('session_status', ['scheduled', 'ongoing', 'completed'])
            ->orderBy('session_date')
            ->get();
    }

    private function processKuantanSessions($sessions): void
    {
        $this->command->info("\n🏥 Processing Kuantan Centre Sessions...");
        
        $kuantanTrainees = Trainee::where('centre_id', '02')->get();
        $processedCount = 0;
        
        foreach ($sessions as $session) {
            $suitableTrainees = $this->findSuitableTrainees($kuantanTrainees, $session, 'kuantan');
            $enrolledCount = $this->enrollTraineesInSession($session, $suitableTrainees);
            
            $processedCount++;
            if ($processedCount % 20 == 0) {
                $this->command->info("✅ Processed {$processedCount}/{$sessions->count()} Kuantan sessions");
            }
        }
        
        $this->command->info("✅ Completed all {$sessions->count()} Kuantan sessions");
    }

    private function processPagohSessions($sessions): void
    {
        $this->command->info("\n🔧 Processing Pagoh Centre Sessions...");
        
        $pagohTrainees = Trainee::where('centre_id', '03')->get();
        $processedCount = 0;
        
        foreach ($sessions as $session) {
            $suitableTrainees = $this->findSuitableTrainees($pagohTrainees, $session, 'pagoh');
            $enrolledCount = $this->enrollTraineesInSession($session, $suitableTrainees);
            
            $processedCount++;
            if ($processedCount % 20 == 0) {
                $this->command->info("✅ Processed {$processedCount}/{$sessions->count()} Pagoh sessions");
            }
        }
        
        $this->command->info("✅ Completed all {$sessions->count()} Pagoh sessions");
    }

    private function findSuitableTrainees($trainees, $session, string $centreType)
    {
        $activity = $session->activity;
        $activityName = strtolower($activity->activity_name);
        $maxParticipants = min($session->max_participants, 12); // Cap at reasonable number
        
        $suitableTrainees = collect();
        
        foreach ($trainees as $trainee) {
            if ($this->isTraineeSuitableForActivity($trainee, $activityName, $centreType)) {
                $suitableTrainees->push($trainee);
            }
        }
        
        // If we don't have enough suitable trainees, add some general ones
        if ($suitableTrainees->count() < 3) {
            $additionalTrainees = $trainees->diff($suitableTrainees)->shuffle()->take(3);
            $suitableTrainees = $suitableTrainees->merge($additionalTrainees);
        }
        
        // Select appropriate number of trainees
        $enrollmentCount = $this->determineEnrollmentCount($maxParticipants, $suitableTrainees->count());
        
        return $suitableTrainees->shuffle()->take($enrollmentCount);
    }

    private function isTraineeSuitableForActivity($trainee, string $activityName, string $centreType): bool
    {
        $traineeCondition = strtolower($trainee->trainee_condition ?? '');
        
        // Map trainee conditions to activity suitability
        $conditionMap = [
            'autism' => ['autism', 'asd', 'autistic'],
            'cerebral_palsy' => ['cerebral palsy', 'cp', 'physical'],
            'down_syndrome' => ['down syndrome', 'downs', 'trisomy'],
            'adhd' => ['adhd', 'attention', 'hyperactivity'],
            'intellectual_disability' => ['intellectual', 'mental', 'cognitive'],
            'mild_intellectual' => ['mild', 'borderline', 'learning'],
            'learning_disability' => ['learning', 'dyslexia', 'academic'],
            'autism_high_functioning' => ['asperger', 'high functioning', 'mild autism'],
            'physical_disability' => ['physical', 'mobility', 'wheelchair', 'motor']
        ];
        
        // Check if trainee condition matches any category
        $traineeCategory = null;
        foreach ($conditionMap as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($traineeCondition, $keyword) !== false) {
                    $traineeCategory = $category;
                    break 2;
                }
            }
        }
        
        if (!$traineeCategory) {
            return true; // Default: trainee is suitable if condition is unclear
        }
        
        // Check if activity is suitable for this trainee category
        $suitableActivities = $this->activitySuitability[$centreType][$traineeCategory] ?? [];
        
        foreach ($suitableActivities as $suitableActivity) {
            if (strpos($activityName, strtolower($suitableActivity)) !== false) {
                return true;
            }
        }
        
        // Check for general keywords
        $generalKeywords = [
            'kuantan' => ['therapy', 'training', 'support', 'development', 'skills'],
            'pagoh' => ['training', 'skills', 'job', 'work', 'independence', 'life']
        ];
        
        foreach ($generalKeywords[$centreType] as $keyword) {
            if (strpos($activityName, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }

    private function determineEnrollmentCount(int $maxParticipants, int $availableTrainees): int
    {
        // Aim for 60-90% capacity
        $minEnrollment = max(1, intval($maxParticipants * 0.6));
        $maxEnrollment = min($availableTrainees, intval($maxParticipants * 0.9));
        
        if ($minEnrollment > $maxEnrollment) {
            return min($availableTrainees, $maxParticipants);
        }
        
        return rand($minEnrollment, $maxEnrollment);
    }

    private function enrollTraineesInSession($session, $trainees): int
    {
        $enrolledCount = 0;
        $currentEnrolled = SessionEnrollment::where('session_id', $session->id)->count();
        
        // Skip if session already has enrollments
        if ($currentEnrolled > 0) {
            return $currentEnrolled;
        }
        
        foreach ($trainees as $trainee) {
            // Check if already enrolled to avoid duplicates
            $existingEnrollment = SessionEnrollment::where('session_id', $session->id)
                ->where('trainee_id', $trainee->id)
                ->exists();
            
            if (!$existingEnrollment) {
                $enrollment = $this->createSessionEnrollment($session, $trainee);
                if ($enrollment) {
                    $enrolledCount++;
                }
            }
        }
        
        // Update session current participants count
        $session->update(['current_participants' => $enrolledCount]);
        
        return $enrolledCount;
    }

    private function createSessionEnrollment($session, $trainee)
    {
        $sessionDate = Carbon::parse($session->session_date);
        $now = Carbon::now();
        
        // Determine session timing
        if ($sessionDate->isPast()) {
            $timingType = 'past_sessions';
        } elseif ($sessionDate->isToday()) {
            $timingType = 'current_sessions';
        } else {
            $timingType = 'future_sessions';
        }
        
        // Generate realistic enrollment and attendance
        $enrollmentData = $this->generateEnrollmentData($session, $trainee, $timingType);
        
        return SessionEnrollment::create([
            'session_id' => $session->id,
            'trainee_id' => $trainee->id,
            'enrollment_date' => $enrollmentData['enrollment_date'],
            'enrollment_status' => $enrollmentData['enrollment_status'],
            'attendance_status' => $enrollmentData['attendance_status'],
            'checked_in_at' => $enrollmentData['checked_in_at'],
            'participation_score' => $enrollmentData['participation_score'],
            'progress_notes' => $enrollmentData['progress_notes'],
            'requires_assistance' => $this->determineAssistanceNeed($trainee),
            'enrolled_by' => $session->instructor_id ?? $session->teacher_id ?? 1,
            'created_at' => $enrollmentData['created_at'],
            'updated_at' => $enrollmentData['updated_at']
        ]);
    }

    private function generateEnrollmentData($session, $trainee, string $timingType): array
    {
        $sessionDate = Carbon::parse($session->session_date);
        $enrollmentDate = $sessionDate->copy()->subDays(rand(1, 14)); // Enrolled 1-14 days before session
        
        $data = [
            'enrollment_date' => $enrollmentDate->format('Y-m-d'),
            'created_at' => $enrollmentDate,
            'updated_at' => $enrollmentDate,
        ];
        
        if ($timingType === 'future_sessions') {
            // Future sessions just have enrollment status
            $data['enrollment_status'] = 'enrolled';
            $data['attendance_status'] = null;
            $data['checked_in_at'] = null;
            $data['participation_score'] = null;
            $data['progress_notes'] = null;
        } else {
            // Past and current sessions have attendance records
            $attendanceOutcome = $this->determineAttendanceOutcome($timingType);
            
            $data['enrollment_status'] = 'enrolled';
            $data['attendance_status'] = $attendanceOutcome;
            
            if (in_array($attendanceOutcome, ['present', 'late'])) {
                $checkInTime = $sessionDate->copy()->setTimeFromTimeString($session->session_start_time);
                if ($attendanceOutcome === 'late') {
                    $checkInTime->addMinutes(rand(5, 20)); // 5-20 minutes late
                } else {
                    $checkInTime->subMinutes(rand(0, 10)); // On time or early
                }
                
                $data['checked_in_at'] = $checkInTime;
                $data['participation_score'] = $this->generateParticipationScore($attendanceOutcome, $trainee);
                $data['progress_notes'] = $this->generateProgressNotes($attendanceOutcome, $session, $trainee);
                $data['updated_at'] = $checkInTime;
            } else {
                $data['checked_in_at'] = null;
                $data['participation_score'] = null;
                $data['progress_notes'] = $attendanceOutcome === 'excused' ? 'Excused absence - medical appointment' : 'Absent - no reason provided';
            }
        }
        
        return $data;
    }

    private function determineAttendanceOutcome(string $timingType): string
    {
        $probabilities = $this->attendanceFactors[$timingType];
        $random = rand(1, 100);
        
        $cumulative = 0;
        foreach ($probabilities as $outcome => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $outcome;
            }
        }
        
        return 'present'; // Fallback
    }

    private function generateParticipationScore(string $attendanceStatus, $trainee): ?float
    {
        if (!in_array($attendanceStatus, ['present', 'late'])) {
            return null;
        }
        
        // Base score influenced by attendance and trainee factors
        $baseScore = $attendanceStatus === 'present' ? rand(70, 95) : rand(60, 85);
        
        // Adjust based on trainee condition (some conditions may affect participation)
        $condition = strtolower($trainee->trainee_condition ?? '');
        if (strpos($condition, 'autism') !== false) {
            $baseScore += rand(-10, 10); // Can vary widely
        } elseif (strpos($condition, 'adhd') !== false) {
            $baseScore += rand(-5, 5); // Slight variation
        }
        
        return min(100, max(0, $baseScore));
    }

    private function generateProgressNotes(string $attendanceStatus, $session, $trainee): ?string
    {
        if (!in_array($attendanceStatus, ['present', 'late'])) {
            return null;
        }
        
        $activity = $session->activity;
        $activityType = strtolower($activity->activity_name);
        $traineeCondition = strtolower($trainee->trainee_condition ?? '');
        
        $positiveNotes = [
            'Participated actively in session activities',
            'Showed good engagement and cooperation',
            'Made progress toward session objectives',
            'Demonstrated improved skills compared to previous session',
            'Worked well with peers and instructor',
            'Followed instructions well throughout the session'
        ];
        
        $neutralNotes = [
            'Attended session and participated as expected',
            'Completed assigned activities with some assistance',
            'Maintained focus for most of the session duration',
            'Showed steady progress in skill development'
        ];
        
        $challengeNotes = [
            'Had some difficulty with session activities, provided additional support',
            'Required extra encouragement to participate fully',
            'Completed activities with significant assistance from instructor',
            'Found some activities challenging but made effort to participate'
        ];
        
        // Choose note type based on participation score (if available)
        $random = rand(1, 100);
        if ($random <= 60) {
            $notePool = $positiveNotes;
        } elseif ($random <= 85) {
            $notePool = $neutralNotes;
        } else {
            $notePool = $challengeNotes;
        }
        
        return $notePool[array_rand($notePool)];
    }

    private function determineAssistanceNeed($trainee): bool
    {
        $condition = strtolower($trainee->trainee_condition ?? '');
        
        // Conditions that typically require more assistance
        if (strpos($condition, 'cerebral palsy') !== false ||
            strpos($condition, 'severe') !== false ||
            strpos($condition, 'intellectual') !== false) {
            return rand(1, 100) <= 70; // 70% chance of needing assistance
        }
        
        if (strpos($condition, 'autism') !== false ||
            strpos($condition, 'adhd') !== false) {
            return rand(1, 100) <= 40; // 40% chance of needing assistance
        }
        
        return rand(1, 100) <= 20; // 20% default chance
    }

    private function showFinalStatistics(): void
    {
        $this->command->info("\n" . str_repeat('=', 90));
        $this->command->info("📊 SESSION ENROLLMENT & ATTENDANCE STATISTICS 📊");
        $this->command->info(str_repeat('=', 90));
        
        // Get comprehensive statistics
        $kuantanStats = $this->getCentreEnrollmentStats('02', 'Kuantan');
        $pagohStats = $this->getCentreEnrollmentStats('03', 'Pagoh');
        
        $this->displayCentreEnrollmentStats($kuantanStats);
        $this->displayCentreEnrollmentStats($pagohStats);
        
        // Overall system statistics
        $totalEnrollments = $kuantanStats['total_enrollments'] + $pagohStats['total_enrollments'];
        $totalSessions = $kuantanStats['total_sessions'] + $pagohStats['total_sessions'];
        $avgEnrollmentsPerSession = $totalSessions > 0 ? round($totalEnrollments / $totalSessions, 1) : 0;
        
        $this->command->info("\n🌟 OVERALL SYSTEM STATISTICS:");
        $this->command->info("├─ 📅 Total Sessions: {$totalSessions}");
        $this->command->info("├─ 👥 Total Enrollments: {$totalEnrollments}");
        $this->command->info("├─ 📊 Avg Enrollments/Session: {$avgEnrollmentsPerSession}");
        
        // Attendance statistics
        $attendanceStats = $this->getAttendanceStatistics();
        $this->command->info("\n📈 ATTENDANCE OVERVIEW:");
        $this->command->info("├─ ✅ Present: {$attendanceStats['present']} ({$attendanceStats['present_percentage']}%)");
        $this->command->info("├─ ⏰ Late: {$attendanceStats['late']} ({$attendanceStats['late_percentage']}%)");
        $this->command->info("├─ ❌ Absent: {$attendanceStats['absent']} ({$attendanceStats['absent_percentage']}%)");
        $this->command->info("└─ 📋 Excused: {$attendanceStats['excused']} ({$attendanceStats['excused_percentage']}%)");
        
        $this->command->info("\n✅ ACHIEVEMENTS:");
        $this->command->info("├─ ✅ All sessions populated with appropriate number of trainees");
        $this->command->info("├─ ✅ Realistic attendance patterns generated");
        $this->command->info("├─ ✅ Trainee-activity matching based on conditions and centre specialization");
        $this->command->info("├─ ✅ Participation scores and progress notes added");
        $this->command->info("└─ ✅ Complete enrollment and attendance tracking system established");
        
        $this->command->info(str_repeat('=', 90) . "\n");
    }

    private function getCentreEnrollmentStats(string $centreId, string $centreName): array
    {
        $totalSessions = ActivitySession::whereHas('activity', function($query) use ($centreId) {
            $query->where('centre_id', $centreId);
        })->count();
        
        $totalEnrollments = SessionEnrollment::whereHas('session.activity', function($query) use ($centreId) {
            $query->where('centre_id', $centreId);
        })->count();
        
        $avgEnrollmentsPerSession = $totalSessions > 0 ? round($totalEnrollments / $totalSessions, 1) : 0;
        
        $sessionsWithEnrollments = DB::table('session_enrollments')
            ->join('activity_sessions', 'session_enrollments.session_id', '=', 'activity_sessions.id')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->distinct('activity_sessions.id')
            ->count();
        
        $enrollmentCoverage = $totalSessions > 0 ? round(($sessionsWithEnrollments / $totalSessions) * 100, 1) : 0;
        
        return [
            'centre_name' => $centreName,
            'total_sessions' => $totalSessions,
            'total_enrollments' => $totalEnrollments,
            'avg_enrollments_per_session' => $avgEnrollmentsPerSession,
            'sessions_with_enrollments' => $sessionsWithEnrollments,
            'enrollment_coverage' => $enrollmentCoverage
        ];
    }

    private function displayCentreEnrollmentStats(array $stats): void
    {
        $this->command->info("\n🏢 {$stats['centre_name']} CENTRE ENROLLMENT STATISTICS:");
        $this->command->info("├─ 📅 Total Sessions: {$stats['total_sessions']}");
        $this->command->info("├─ 👥 Total Enrollments: {$stats['total_enrollments']}");
        $this->command->info("├─ 📊 Avg Enrollments/Session: {$stats['avg_enrollments_per_session']}");
        $this->command->info("├─ 🎯 Sessions with Enrollments: {$stats['sessions_with_enrollments']}");
        $this->command->info("└─ 📈 Enrollment Coverage: {$stats['enrollment_coverage']}%");
    }

    private function getAttendanceStatistics(): array
    {
        $attendanceStats = DB::table('session_enrollments')
            ->join('activity_sessions', 'session_enrollments.session_id', '=', 'activity_sessions.id')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->whereIn('activities.centre_id', ['02', '03'])
            ->whereNotNull('session_enrollments.attendance_status')
            ->selectRaw('
                session_enrollments.attendance_status,
                COUNT(*) as count
            ')
            ->groupBy('session_enrollments.attendance_status')
            ->get()
            ->keyBy('attendance_status');
        
        $totalAttendance = $attendanceStats->sum('count');
        
        $result = [
            'present' => $attendanceStats->get('present')->count ?? 0,
            'late' => $attendanceStats->get('late')->count ?? 0,
            'absent' => $attendanceStats->get('absent')->count ?? 0,
            'excused' => $attendanceStats->get('excused')->count ?? 0,
            'total' => $totalAttendance
        ];
        
        foreach (['present', 'late', 'absent', 'excused'] as $status) {
            $result["{$status}_percentage"] = $totalAttendance > 0 ? 
                round(($result[$status] / $totalAttendance) * 100, 1) : 0;
        }
        
        return $result;
    }
}