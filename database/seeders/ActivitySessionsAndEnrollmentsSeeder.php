<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Trainee;
use App\Models\Users;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActivitySessionsAndEnrollmentsSeeder extends Seeder
{
    /**
     * Days of the week for scheduling
     */
    private array $daysOfWeek = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'
    ];

    /**
     * Time slots for activities (Malaysian working hours)
     */
    private array $timeSlots = [
        ['start' => '08:00', 'end' => '09:00'],
        ['start' => '09:00', 'end' => '10:00'],
        ['start' => '10:30', 'end' => '11:30'], // After break
        ['start' => '11:30', 'end' => '12:30'],
        ['start' => '14:00', 'end' => '15:00'], // After lunch
        ['start' => '15:00', 'end' => '16:00'],
        ['start' => '16:00', 'end' => '17:00'],
    ];

    /**
     * Malaysian venues/rooms for activities
     */
    private array $venues = [
        'Bilik Terapi Pertuturan / Speech Therapy Room',
        'Bilik Terapi Okupasi / Occupational Therapy Room',
        'Bilik Fisioterapi / Physiotherapy Room',
        'Bilik Integrasi Sensori / Sensory Integration Room',
        'Bilik Aktiviti Kumpulan / Group Activity Room',
        'Makmal Komputer / Computer Lab',
        'Bilik Kemahiran Hidup / Life Skills Room',
        'Bilik Seni / Art Room',
        'Bilik Muzik / Music Room',
        'Dewan Serbaguna / Multipurpose Hall',
        'Taman Sensori / Sensory Garden',
        'Bengkel Vokasional / Vocational Workshop'
    ];

    public function run(): void
    {
        $this->command->info('📅 Creating activity sessions and enrollments...');

        $activities = Activity::with(['creator', 'centre'])->get();
        $trainees = Trainee::all();
        
        if ($activities->isEmpty() || $trainees->isEmpty()) {
            $this->command->error('No activities or trainees found! Please run activity and trainee seeders first.');
            return;
        }

        $totalSessions = 0;
        $totalEnrollments = 0;

        foreach ($activities as $activity) {
            $this->command->line("   🎯 Creating sessions for: {$activity->activity_name}");
            
            // Create weekly schedule for each activity
            $schedule = $this->createActivitySchedule($activity);
            
            // Create sessions for the past 3 months and next 2 months
            $sessions = $this->createSessionsForActivity($activity, $schedule);
            $totalSessions += count($sessions);
            
            // Enroll appropriate trainees
            $enrollments = $this->createEnrollmentsForActivity($activity, $sessions, $trainees);
            $totalEnrollments += $enrollments;
        }

        $this->showSessionSummary($totalSessions, $totalEnrollments);
    }

    private function createActivitySchedule(Activity $activity): ActivitySchedule
    {
        // Select appropriate day and time based on activity type and target group
        $dayOfWeek = $this->selectDayForActivity($activity);
        $timeSlot = $this->selectTimeSlotForActivity($activity);
        $venue = $this->selectVenueForActivity($activity);

        return ActivitySchedule::create([
            'activity_id' => $activity->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => $timeSlot['start'],
            'end_time' => $timeSlot['end'],
            'location' => $venue,
            'room' => $this->generateRoomNumber(),
            'recurring' => 'weekly',
            'start_date' => Carbon::now()->subMonths(3)->startOfWeek(),
            'end_date' => Carbon::now()->addMonths(6)->endOfWeek(),
            'max_capacity' => $activity->max_participants,
            'status' => 'active',
            'created_at' => Carbon::now()->subDays(rand(60, 180)),
        ]);
    }

    private function selectDayForActivity(Activity $activity): string
    {
        // Distribute activities across weekdays
        // Therapy sessions typically in the morning, group activities in afternoon
        $dayDistribution = match($activity->category) {
            'Speech Therapy', 'Occupational Therapy', 'Physical Therapy' => 
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'Behavioral Therapy', 'Sensory Integration' => 
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday'],
            'Social Skills', 'Art & Creativity', 'Music Therapy' => 
                ['Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'Mathematics', 'Literacy', 'Computer Skills' => 
                ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'Life Skills', 'Vocational Training' => 
                ['Wednesday', 'Thursday', 'Friday'],
            default => $this->daysOfWeek
        };

        return $dayDistribution[array_rand($dayDistribution)];
    }

    private function selectTimeSlotForActivity(Activity $activity): array
    {
        // Morning slots for therapy, afternoon for group activities
        if (in_array($activity->category, ['Speech Therapy', 'Occupational Therapy', 'Physical Therapy', 'Sensory Integration'])) {
            // Therapy sessions: 8AM-12PM
            $morningSlots = array_slice($this->timeSlots, 0, 4);
            return $morningSlots[array_rand($morningSlots)];
        } elseif (in_array($activity->category, ['Social Skills', 'Art & Creativity', 'Music Therapy', 'Life Skills'])) {
            // Group activities: 2PM-5PM
            $afternoonSlots = array_slice($this->timeSlots, 4);
            return $afternoonSlots[array_rand($afternoonSlots)];
        } else {
            // Academic subjects: spread throughout the day
            return $this->timeSlots[array_rand($this->timeSlots)];
        }
    }

    private function selectVenueForActivity(Activity $activity): string
    {
        $venueMapping = [
            'Speech Therapy' => 'Bilik Terapi Pertuturan / Speech Therapy Room',
            'Occupational Therapy' => 'Bilik Terapi Okupasi / Occupational Therapy Room',
            'Physical Therapy' => 'Bilik Fisioterapi / Physiotherapy Room',
            'Sensory Integration' => 'Bilik Integrasi Sensori / Sensory Integration Room',
            'Behavioral Therapy' => 'Bilik Aktiviti Kumpulan / Group Activity Room',
            'Social Skills' => 'Bilik Aktiviti Kumpulan / Group Activity Room',
            'Art & Creativity' => 'Bilik Seni / Art Room',
            'Music Therapy' => 'Bilik Muzik / Music Room',
            'Computer Skills' => 'Makmal Komputer / Computer Lab',
            'Life Skills' => 'Bilik Kemahiran Hidup / Life Skills Room',
            'Vocational Training' => 'Bengkel Vokasional / Vocational Workshop',
            'Mathematics' => 'Bilik Aktiviti Kumpulan / Group Activity Room',
            'Literacy' => 'Bilik Aktiviti Kumpulan / Group Activity Room',
        ];

        return $venueMapping[$activity->category] ?? $this->venues[array_rand($this->venues)];
    }

    private function generateRoomNumber(): string
    {
        $letters = ['A', 'B', 'C', 'D'];
        $letter = $letters[array_rand($letters)];
        $number = rand(101, 350);
        return "{$letter}{$number}";
    }

    private function createSessionsForActivity(Activity $activity, ActivitySchedule $schedule): array
    {
        $sessions = [];
        $startDate = Carbon::now()->subMonths(3)->startOfWeek();
        $endDate = Carbon::now()->addMonths(2)->endOfWeek();

        // Find all occurrences of the scheduled day between start and end dates
        $current = $startDate->copy();
        while ($current <= $endDate) {
            if ($current->format('l') === $schedule->day_of_week) {
                $sessionDate = $current->copy();
                
                // Determine session status based on date
                $status = $this->getSessionStatus($sessionDate);
                
                // Skip some sessions randomly (holidays, cancellations, etc.)
                if (rand(1, 100) <= 85) { // 85% of sessions actually happen
                    $session = $this->createSession($activity, $schedule, $sessionDate, $status);
                    $sessions[] = $session;
                }
            }
            $current->addDay();
        }

        return $sessions;
    }

    private function getSessionStatus(Carbon $date): string
    {
        $now = Carbon::now();
        
        if ($date < $now->copy()->subWeek()) {
            return 'completed';
        } elseif ($date < $now) {
            return rand(1, 100) <= 90 ? 'completed' : 'cancelled';
        } else {
            return 'scheduled';
        }
    }

    private function createSession(Activity $activity, ActivitySchedule $schedule, Carbon $date, string $status): ActivitySession
    {
        // Calculate duration from schedule
        $startTime = Carbon::parse($schedule->start_time);
        $endTime = Carbon::parse($schedule->end_time);
        $duration = $startTime->diffInMinutes($endTime);

        $session = ActivitySession::create([
            'activity_id' => $activity->id,
            'teacher_id' => $activity->created_by,
            'scheduled_date' => $date->format('Y-m-d'),
            'start_time' => $schedule->start_time,
            'end_time' => $schedule->end_time,
            'duration_minutes' => $duration,
            'venue' => $schedule->location,
            'room_number' => $schedule->room,
            'max_participants' => $activity->max_participants,
            'enrolled_count' => 0, // Will be updated when enrollments are created
            'status' => $status,
            'notes' => $this->generateSessionNotes($activity, $status),
            'materials_prepared' => $status !== 'cancelled',
            'attendance_marked' => $status === 'completed',
            'actual_start' => $status === 'completed' ? $date->copy()->setTimeFromTimeString($schedule->start_time) : null,
            'actual_end' => $status === 'completed' ? $date->copy()->setTimeFromTimeString($schedule->end_time) : null,
            'session_report' => $status === 'completed' ? $this->generateSessionReport($activity) : null,
            'created_at' => $date->copy()->subDays(rand(1, 7)),
        ]);

        return $session;
    }

    private function generateSessionNotes(Activity $activity, string $status): ?string
    {
        if ($status === 'cancelled') {
            $reasons = [
                'Cuti sekolah / School holiday',
                'Juruterapi tidak hadir / Therapist absent',
                'Peralatan rosak / Equipment malfunction',
                'Cuaca buruk / Bad weather',
                'Aktiviti kecemasan / Emergency activity'
            ];
            return $reasons[array_rand($reasons)];
        }

        $notes = [
            'Sesi berjalan lancar. Semua peserta aktif terlibat. / Session went smoothly. All participants actively engaged.',
            'Beberapa peserta menunjukkan kemajuan yang baik. / Several participants showed good progress.',
            'Perlu lebih fokus pada kemahiran tertentu minggu depan. / Need more focus on specific skills next week.',
            'Peserta sangat responsif hari ini. / Participants were very responsive today.',
            'Aktiviti disesuaikan mengikut keperluan kumpulan. / Activity adapted according to group needs.'
        ];

        return $notes[array_rand($notes)];
    }

    private function generateSessionReport(Activity $activity): string
    {
        $templates = [
            'Speech Therapy' => 'Peserta menunjukkan peningkatan dalam artikulasi dan komunikasi. Objektif sesi tercapai dengan baik.',
            'Occupational Therapy' => 'Kemahiran motor halus peserta bertambah baik. Latihan koordinasi mata-tangan memberikan hasil positif.',
            'Physical Therapy' => 'Kekuatan dan keseimbangan peserta menunjukkan penambahbaikan. Aktiviti mobiliti berjaya dilaksanakan.',
            'Behavioral Therapy' => 'Tingkah laku positif peserta semakin konsisten. Strategi pengurusan tingkah laku berkesan.',
            'Social Skills' => 'Interaksi sosial peserta bertambah baik. Aktiviti kumpulan mencapai objektif yang ditetapkan.',
            'Mathematics' => 'Pemahaman konsep matematik asas peserta bertambah baik. Kemajuan yang memberangsangkan.',
            'Literacy' => 'Kemahiran membaca dan menulis peserta menunjukkan peningkatan yang ketara.',
            'Life Skills' => 'Kemandirian peserta dalam aktiviti harian semakin meningkat.',
            'Art & Creativity' => 'Kreativiti dan ekspresi diri peserta berkembang dengan baik melalui aktiviti seni.',
            'Music Therapy' => 'Respons peserta terhadap aktiviti muzik sangat positif. Kemahiran komunikasi bertambah baik.'
        ];

        return $templates[$activity->category] ?? 'Sesi berjalan dengan baik dan objektif tercapai.';
    }

    private function createEnrollmentsForActivity(Activity $activity, array $sessions, $trainees): int
    {
        if (empty($sessions)) {
            return 0;
        }

        // Filter trainees appropriate for this activity
        $appropriateTrainees = $this->filterAppropriateTrainees($activity, $trainees);
        
        if ($appropriateTrainees->isEmpty()) {
            return 0;
        }

        // Determine number of trainees to enroll (based on activity type and capacity)
        $enrollmentCount = $this->getEnrollmentCount($activity, $appropriateTrainees->count());
        
        // Select random trainees to enroll
        $selectedTrainees = $appropriateTrainees->random(min($enrollmentCount, $appropriateTrainees->count()));
        
        $totalEnrollments = 0;

        foreach ($selectedTrainees as $trainee) {
            // Create activity enrollment
            $activityEnrollment = ActivityEnrollment::create([
                'activity_id' => $activity->id,
                'trainee_id' => $trainee->id,
                'enrollment_date' => Carbon::now()->subDays(rand(30, 120)),
                'start_date' => Carbon::now()->subDays(rand(20, 100)),
                'status' => 'enrolled',
                'goals' => $this->generateEnrollmentGoals($activity, $trainee),
                'enrolled_by' => $activity->created_by,
                'progress_notes' => $this->generateEnrollmentNotes($trainee),
            ]);

            // Enroll in individual sessions
            foreach ($sessions as $session) {
                $this->enrollTraineeInSession($session, $trainee);
                $totalEnrollments++;
            }
        }

        // Update enrolled count for sessions
        foreach ($sessions as $session) {
            $session->update([
                'enrolled_count' => SessionEnrollment::where('session_id', $session->id)->count()
            ]);
        }

        return $totalEnrollments;
    }

    private function filterAppropriateTrainees(Activity $activity, $trainees)
    {
        return $trainees->filter(function ($trainee) use ($activity) {
            // Age appropriateness
            $age = Carbon::parse($trainee->trainee_date_of_birth)->age;
            $ageRange = explode('-', explode(' ', $activity->age_group)[0]);
            $minAge = (int)$ageRange[0];
            $maxAge = isset($ageRange[1]) ? (int)$ageRange[1] : 18;
            
            if ($age < $minAge || $age > $maxAge) {
                return false;
            }

            // Condition appropriateness
            $appropriateConditions = $this->getAppropriateConditions($activity);
            return in_array($trainee->trainee_condition, $appropriateConditions);
        });
    }

    private function getAppropriateConditions(Activity $activity): array
    {
        $conditionMapping = [
            'Speech Therapy' => ['Autism Spectrum Disorder', 'Speech and Language Disorder', 'Down Syndrome', 'Cerebral Palsy'],
            'Occupational Therapy' => ['Cerebral Palsy', 'Autism Spectrum Disorder', 'Down Syndrome', 'Physical Disability'],
            'Physical Therapy' => ['Cerebral Palsy', 'Physical Disability', 'Down Syndrome'],
            'Behavioral Therapy' => ['Autism Spectrum Disorder', 'ADHD', 'Intellectual Disability'],
            'Sensory Integration' => ['Autism Spectrum Disorder', 'ADHD', 'Sensory Processing Disorder'],
            'Social Skills' => ['Autism Spectrum Disorder', 'ADHD', 'Learning Disability', 'Intellectual Disability'],
            'Mathematics' => ['Learning Disability', 'Intellectual Disability', 'ADHD', 'Down Syndrome'],
            'Literacy' => ['Learning Disability', 'Intellectual Disability', 'Speech and Language Disorder'],
            'Computer Skills' => ['Learning Disability', 'Intellectual Disability', 'Physical Disability'],
            'Life Skills' => ['Intellectual Disability', 'Down Syndrome', 'Autism Spectrum Disorder', 'Cerebral Palsy'],
            'Vocational Training' => ['Intellectual Disability', 'Learning Disability', 'Physical Disability'],
            'Art & Creativity' => ['All conditions'],
            'Music Therapy' => ['All conditions'],
        ];

        $appropriate = $conditionMapping[$activity->category] ?? ['All conditions'];
        
        if (in_array('All conditions', $appropriate)) {
            return [
                'Autism Spectrum Disorder', 'Cerebral Palsy', 'Down Syndrome', 
                'Intellectual Disability', 'ADHD', 'Learning Disability', 
                'Speech and Language Disorder', 'Physical Disability'
            ];
        }

        return $appropriate;
    }

    private function getEnrollmentCount(Activity $activity, int $availableTrainees): int
    {
        $maxParticipants = $activity->max_participants;
        $minParticipants = $activity->min_participants;
        
        // Target 70-90% capacity for group activities, 80-100% for individual
        if ($activity->activity_type === 'Individual') {
            return min(rand($minParticipants, $maxParticipants), $availableTrainees);
        } else {
            $targetMin = max($minParticipants, (int)($maxParticipants * 0.7));
            $targetMax = (int)($maxParticipants * 0.9);
            return min(rand($targetMin, $targetMax), $availableTrainees);
        }
    }

    private function generateEnrollmentGoals(Activity $activity, Trainee $trainee): string
    {
        $goalTemplates = [
            'Speech Therapy' => 'Meningkatkan kemahiran komunikasi dan pertuturan yang jelas.',
            'Occupational Therapy' => 'Meningkatkan kemahiran motor halus dan kemandirian dalam aktiviti harian.',
            'Physical Therapy' => 'Meningkatkan kekuatan, keseimbangan, dan mobiliti.',
            'Behavioral Therapy' => 'Mengurangkan tingkah laku mencabar dan meningkatkan kemahiran sosial.',
            'Social Skills' => 'Meningkatkan kemahiran berinteraksi dan berkomunikasi dengan rakan sebaya.',
            'Mathematics' => 'Menguasai kemahiran matematik asas yang sesuai dengan tahap perkembangan.',
            'Literacy' => 'Meningkatkan kemahiran membaca dan menulis.',
            'Life Skills' => 'Meningkatkan kemandirian dalam aktiviti kehidupan seharian.',
            'Computer Skills' => 'Menguasai kemahiran teknologi asas untuk pembelajaran dan komunikasi.',
            'Vocational Training' => 'Mempersiapkan kemahiran kerja untuk kehidupan dewasa.',
        ];

        return $goalTemplates[$activity->category] ?? 'Mencapai objektif pembelajaran yang ditetapkan.';
    }

    private function generateEnrollmentNotes(Trainee $trainee): string
    {
        $notes = [
            "Pelajar bermotivasi tinggi dan suka belajar. Perlukan galakan berterusan.",
            "Memerlukan sokongan tambahan dalam persekitaran yang bising. Lebih fokus dalam kumpulan kecil.",
            "Menunjukkan minat yang tinggi dalam aktiviti hands-on. Responsive terhadap pembelajaran visual.",
            "Personality yang mesra dan suka berinteraksi dengan orang lain. Potensi yang baik untuk kemajuan.",
            "Memerlukan masa lebih untuk memproses maklumat. Memberikan respons yang baik dengan pendekatan yang sabar."
        ];

        return $notes[array_rand($notes)];
    }

    private function enrollTraineeInSession(ActivitySession $session, Trainee $trainee): SessionEnrollment
    {
        $attendanceStatus = $this->getAttendanceStatus($session->status);
        
        return SessionEnrollment::create([
            'session_id' => $session->id,
            'trainee_id' => $trainee->id,
            'enrollment_date' => $session->created_at,
            'attendance_status' => $attendanceStatus,
            'checked_in_at' => $attendanceStatus === 'present' ? 
                Carbon::parse($session->scheduled_date . ' ' . $session->start_time)->addMinutes(rand(0, 10)) : null,
            'participation_score' => $attendanceStatus === 'present' ? rand(6, 10) : null,
            'progress_notes' => $attendanceStatus === 'present' ? $this->generateProgressNotes() : null,
        ]);
    }

    private function getAttendanceStatus(string $sessionStatus): string
    {
        if ($sessionStatus !== 'completed') {
            return 'enrolled'; // Future sessions
        }

        // For completed sessions, generate realistic attendance
        $random = rand(1, 100);
        if ($random <= 85) return 'present';
        if ($random <= 92) return 'absent';
        if ($random <= 97) return 'late';
        return 'excused';
    }

    private function generateProgressNotes(): string
    {
        $notes = [
            'Menunjukkan kemajuan yang memberangsangkan hari ini.',
            'Aktif terlibat dalam semua aktiviti yang diberikan.',
            'Memerlukan galakan tambahan untuk beberapa tugasan.',
            'Menunjukkan peningkatan dalam kemahiran yang disasarkan.',
            'Responsive terhadap arahan dan bimbingan juruterapi.',
            'Berinteraksi dengan baik bersama rakan dalam kumpulan.',
            'Menunjukkan motivasi yang tinggi untuk belajar.',
            'Mencapai objektif sesi dengan jayanya.'
        ];

        return $notes[array_rand($notes)];
    }

    private function showSessionSummary(int $totalSessions, int $totalEnrollments): void
    {
        $this->command->info("\n📊 Activity Sessions & Enrollments Summary:");
        
        // Session statistics
        $sessionStats = ActivitySession::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        $this->command->info("\n📅 Session Status:");
        foreach ($sessionStats as $stat) {
            $this->command->line("   ⏰ {$stat->status}: {$stat->count} sessions");
        }
        
        // Enrollment statistics
        $enrollmentStats = SessionEnrollment::selectRaw('attendance_status, COUNT(*) as count')
            ->groupBy('attendance_status')
            ->get();
            
        $this->command->info("\n👥 Attendance Distribution:");
        foreach ($enrollmentStats as $stat) {
            $this->command->line("   📊 {$stat->attendance_status}: {$stat->count} enrollments");
        }
        
        // Activity participation
        $participationStats = DB::table('activities')
            ->join('activity_sessions', 'activities.id', '=', 'activity_sessions.activity_id')
            ->join('session_enrollments', 'activity_sessions.id', '=', 'session_enrollments.session_id')
            ->selectRaw('activities.category, COUNT(DISTINCT session_enrollments.trainee_id) as unique_trainees')
            ->groupBy('activities.category')
            ->orderBy('unique_trainees', 'desc')
            ->get();
            
        $this->command->info("\n🎯 Trainee Participation by Category:");
        foreach ($participationStats as $stat) {
            $this->command->line("   📚 {$stat->category}: {$stat->unique_trainees} trainees");
        }

        $this->command->info("\n🎯 Total: {$totalSessions} sessions with {$totalEnrollments} enrollments created!");
        $this->command->info("✅ Realistic scheduling with Malaysian context and proper attendance patterns!");
        $this->command->info("🇲🇾 Sessions distributed across appropriate venues and time slots!");
        $this->command->info("👨‍⚕️ Enrollments based on trainee-activity compatibility and capacity management!");
    }
}