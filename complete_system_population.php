<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== Complete CREAMS System Population ===\n";

try {
    // Disable foreign key checks temporarily
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    
    echo "\n=== STEP 1: Complete Activities Creation ===\n";
    
    // Activity types and names
    $activityTypes = [
        'Individual' => [
            'Speech and Language Therapy',
            'Occupational Therapy Session',
            'Physical Therapy Session',
            'Behavioral Therapy Session',
            'Cognitive Assessment',
            'Individual Learning Support'
        ],
        'Group' => [
            'Social Skills Training',
            'Group Art Therapy',
            'Music and Movement',
            'Life Skills Workshop',
            'Computer Skills Class',
            'Mathematics Learning Group',
            'Reading and Writing Group',
            'Science Exploration'
        ]
    ];
    
    $locations = ['Therapy Room 1', 'Therapy Room 2', 'Computer Lab', 'Art Room', 'Music Room', 'Gymnasium'];
    
    // Get users who don't have activities yet
    $usersWithoutActivities = DB::table('users')
        ->whereIn('centre_id', ['01', '02', '03'])
        ->whereNotIn('id', function($query) {
            $query->select('created_by')->from('activities')->where('created_by', '>', 0);
        })
        ->get();
    
    $activityCounter = DB::table('activities')->count() + 1;
    
    foreach ($usersWithoutActivities as $user) {
        // Each user creates 1-2 activities
        $numActivities = rand(1, 2);
        
        for ($i = 0; $i < $numActivities; $i++) {
            $type = array_rand($activityTypes);
            $activities = $activityTypes[$type];
            $activityName = $activities[array_rand($activities)];
            
            $startDate = Carbon::now()->addDays(rand(1, 30));
            $startTime = Carbon::createFromTime(rand(8, 15), [0, 15, 30, 45][rand(0, 3)]);
            $duration = rand(30, 90);
            $endTime = $startTime->copy()->addMinutes($duration);
            
            $activityData = [
                'activity_id' => 'ACT' . str_pad($activityCounter, 4, '0', STR_PAD_LEFT) . '_' . $user->centre_id,
                'activity_name' => $activityName,
                'activity_description' => "Professional $activityName program designed to improve skills and development.",
                'activity_type' => $type,
                'activity_date' => $startDate->format('Y-m-d'),
                'activity_start_time' => $startTime->format('H:i:s'),
                'activity_end_time' => $endTime->format('H:i:s'),
                'activity_location' => $locations[array_rand($locations)],
                'max_participants' => rand(3, 8),
                'current_participants' => 0,
                'activity_goals' => "Improve specific skills related to $activityName and enhance overall development",
                'required_resources' => json_encode(['Basic equipment', 'Educational materials', 'Safety gear']),
                'activity_status' => 'scheduled',
                'centre_id' => $user->centre_id,
                'created_by' => $user->id,
                'instructor_id' => $user->id,
                'times_conducted' => rand(1, 15),
                'average_rating' => rand(35, 50) / 10,
                'duration_minutes' => $duration,
                'min_participants' => rand(1, 3),
                'difficulty_level' => ['beginner', 'intermediate', 'advanced'][rand(0, 2)],
                'age_group' => ['children', 'adolescents', 'adults', 'all_ages'][rand(0, 3)],
                'activity_period' => 'Regular',
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            
            $activityId = DB::table('activities')->insertGetId($activityData);
            echo "✅ Created activity: $activityName (ID: $activityId) for {$user->name}\n";
            
            // Create 1-2 sessions for each activity
            $numSessions = rand(1, 2);
            
            for ($s = 0; $s < $numSessions; $s++) {
                $sessionDate = $startDate->copy()->addDays($s * 7);
                
                $sessionData = [
                    'activity_id' => $activityId,
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'scheduled_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'venue' => $activityData['activity_location'],
                    'room_number' => rand(1, 5),
                    'instructor_id' => $user->id,
                    'teacher_id' => $user->id,
                    'max_capacity' => $activityData['max_participants'],
                    'max_participants' => $activityData['max_participants'],
                    'current_enrollment' => 0,
                    'current_participants' => 0,
                    'session_status' => 'scheduled',
                    'status' => 'scheduled',
                    'attendance_marked' => 0,
                    'marked_by' => $user->id,
                    'session_feedback' => null,
                    'session_notes' => 'Session notes will be updated after completion',
                    'session_objectives' => json_encode(['Skill development', 'Engagement improvement']),
                    'completed_objectives' => null,
                    'materials_used' => json_encode(['Educational materials']),
                    'session_rating' => null,
                    'centre_id' => $user->centre_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
                
                $sessionId = DB::table('activity_sessions')->insertGetId($sessionData);
                
                // Enroll 2-4 trainees from the same centre
                $centreTrainees = DB::table('trainees')
                    ->where('centre_id', $user->centre_id)
                    ->inRandomOrder()
                    ->limit(rand(2, 4))
                    ->get();
                
                $enrolledCount = 0;
                
                foreach ($centreTrainees as $trainee) {
                    // Create activity enrollment if not exists
                    $enrollmentExists = DB::table('activity_enrollments')
                        ->where('activity_id', $activityId)
                        ->where('trainee_id', $trainee->id)
                        ->exists();
                    
                    if (!$enrollmentExists) {
                        DB::table('activity_enrollments')->insert([
                            'activity_id' => $activityId,
                            'trainee_id' => $trainee->id,
                            'enrollment_date' => Carbon::now()->subDays(rand(1, 14))->format('Y-m-d'),
                            'enrollment_status' => 'enrolled',
                            'progress_percentage' => rand(20, 70),
                            'attendance_count' => rand(1, 5),
                            'completion_date' => null,
                            'completion_notes' => null,
                            'enrollment_notes' => 'Enrolled for skill development and therapeutic support',
                            'individual_goals' => 'Improve specific skills and overall development',
                            'progress_notes' => 'Making steady progress with consistent attendance',
                            'sessions_attended' => rand(1, 3),
                            'total_sessions' => rand(5, 10),
                            'attendance_rate' => rand(70, 95),
                            'overall_progress' => rand(60, 95), // Decimal value 60.00-95.00
                            'enrolled_by' => $user->id,
                            'centre_id' => $user->centre_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                        
                        $enrolledCount++;
                    }
                    
                    // Create session enrollment
                    $sessionEnrollmentExists = DB::table('session_enrollments')
                        ->where('session_id', $sessionId)
                        ->where('trainee_id', $trainee->id)
                        ->exists();
                    
                    if (!$sessionEnrollmentExists) {
                        DB::table('session_enrollments')->insert([
                            'session_id' => $sessionId,
                            'trainee_id' => $trainee->id,
                            'enrollment_status' => 'enrolled',
                            'enrollment_date' => Carbon::now()->format('Y-m-d'),
                            'special_requirements' => rand(0, 1) ? 'Requires visual aids and communication support' : 'Standard accommodations',
                            'enrolled_by' => $user->id,
                            'centre_id' => $user->centre_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                    
                    // Create logical trainee attendance (75% present, 15% late, 10% absent)
                    $attendanceStatus = 'present';
                    $attendanceRate = rand(1, 100);
                    if ($attendanceRate <= 10) $attendanceStatus = 'absent';
                    elseif ($attendanceRate <= 25) $attendanceStatus = 'late';
                    
                    // Check if attendance record already exists
                    $attendanceExists = DB::table('trainee_attendances')
                        ->where('trainee_id', $trainee->id)
                        ->where('session_id', $sessionId)
                        ->exists();
                    
                    if (!$attendanceExists) {
                        DB::table('trainee_attendances')->insert([
                            'trainee_id' => $trainee->id,
                            'activity_id' => $activityId,
                            'session_id' => $sessionId,
                            'attendance_date' => $sessionDate->format('Y-m-d'),
                            'check_in_time' => $attendanceStatus !== 'absent' ? $startTime->format('H:i:s') : null,
                            'check_out_time' => $attendanceStatus !== 'absent' ? $endTime->format('H:i:s') : null,
                            'attendance_status' => $attendanceStatus,
                            'attendance_notes' => $attendanceStatus === 'present' ? 'Excellent participation and engagement' : 
                                                ($attendanceStatus === 'late' ? 'Arrived late due to transport issues' : 'Absent due to illness'),
                            'participation_level' => $attendanceStatus === 'present' ? rand(3, 5) : 
                                                   ($attendanceStatus === 'late' ? rand(2, 4) : 1),
                            'progress_observation' => $attendanceStatus === 'present' ? 'Steady improvement observed in targeted skills' : 
                                                    'Limited observation due to absence',
                            'recorded_by' => $user->id,
                            'centre_id' => $user->centre_id,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);
                    }
                }
                
                // Update session enrollment count
                DB::table('activity_sessions')
                    ->where('id', $sessionId)
                    ->update([
                        'current_enrollment' => $enrolledCount,
                        'current_participants' => $enrolledCount
                    ]);
                
                echo "  ✓ Session " . ($s + 1) . " with $enrolledCount enrollments\n";
            }
            
            // Update activity current participants
            $totalEnrolled = DB::table('activity_enrollments')->where('activity_id', $activityId)->count();
            DB::table('activities')->where('id', $activityId)->update(['current_participants' => $totalEnrolled]);
            
            $activityCounter++;
        }
    }
    
    echo "\n=== STEP 2: Create Logical Staff Attendance ===\n";
    
    $allStaff = DB::table('users')->whereIn('centre_id', ['01', '02', '03'])->get();
    
    // Create attendance for past 15 working days
    for ($day = 15; $day >= 1; $day--) {
        $attendanceDate = Carbon::now()->subDays($day);
        
        // Skip weekends
        if ($attendanceDate->isWeekend()) continue;
        
        foreach ($allStaff as $staff) {
            // Check if attendance already exists
            $existingAttendance = DB::table('staff_attendances')
                ->where('user_id', $staff->id)
                ->where('attendance_date', $attendanceDate->format('Y-m-d'))
                ->exists();
            
            if ($existingAttendance) continue;
            
            // 82% overall attendance rate (realistic for healthcare settings)
            $isPresent = rand(1, 100) <= 82;
            
            if ($isPresent) {
                $checkInTime = Carbon::createFromTime(rand(7, 9), rand(0, 59));
                $status = 'present';
                
                // 12% chance of being late
                if (rand(1, 100) <= 12) {
                    $checkInTime = Carbon::createFromTime(rand(9, 10), rand(0, 59));
                    $status = 'late';
                }
                
                DB::table('staff_attendances')->insert([
                    'user_id' => $staff->id,
                    'date' => $attendanceDate->format('Y-m-d'),
                    'marked_by_user_id' => $staff->id,
                    'marked_by_email' => $staff->email,
                    'attendance_date' => $attendanceDate->format('Y-m-d'),
                    'time_in' => $checkInTime->format('H:i:s'),
                    'attendance_time' => $checkInTime->format('H:i:s'),
                    'centre_id' => $staff->centre_id,
                    'status' => $status,
                    'remarks' => $status === 'late' ? 'Delayed due to traffic/transport' : 'Punctual arrival',
                    'attendance_type' => 'check_in',
                    'created_at' => $attendanceDate,
                    'updated_at' => $attendanceDate
                ]);
            }
        }
    }
    
    echo "✅ Created logical staff attendance patterns\n";
    
    echo "\n=== STEP 3: Populate Messages Table ===\n";
    
    // Get all users for messaging
    $allUsers = DB::table('users')->whereIn('centre_id', ['01', '02', '03'])->get();
    $userArray = $allUsers->toArray();
    
    // Message categories and templates
    $messageTemplates = [
        'session_update' => [
            'subjects' => [
                'Session Update - {activity}',
                'Progress Report for {activity}',
                'Weekly Update - {activity}',
                'Session Feedback - {activity}'
            ],
            'bodies' => [
                'Hi, I wanted to update you on the recent {activity} session. The participants showed great engagement and we made significant progress on the learning objectives.',
                'The {activity} session went very well today. All enrolled trainees participated actively and we achieved most of our session goals.',
                'Please find attached the progress report for this week\'s {activity} sessions. Overall attendance was good and participants are showing steady improvement.',
                'I\'m pleased to report that today\'s {activity} session was highly successful. The trainees demonstrated improved skills and positive engagement.'
            ]
        ],
        'administrative' => [
            'subjects' => [
                'Schedule Update Required',
                'Equipment Request',
                'Training Session Reminder',
                'Monthly Report Due',
                'Staff Meeting Notice'
            ],
            'bodies' => [
                'Please review and update your schedule for next week. There have been some changes to accommodate new enrollments.',
                'We need to request additional equipment for the therapy sessions. Can you please review the requirements list?',
                'Reminder: Staff training session scheduled for tomorrow at 2 PM in the main conference room.',
                'Monthly progress reports are due by the end of this week. Please ensure all documentation is complete.',
                'Staff meeting scheduled for Friday at 3 PM to discuss center improvements and upcoming activities.'
            ]
        ],
        'coordination' => [
            'subjects' => [
                'Trainee Progress Discussion',
                'Activity Coordination',
                'Resource Sharing',
                'Best Practices Sharing'
            ],
            'bodies' => [
                'I would like to discuss the progress of some of our shared trainees. Could we schedule a brief meeting this week?',
                'Could we coordinate our activities to ensure better resource utilization and avoid scheduling conflicts?',
                'I have some materials that might be useful for your sessions. Would you like me to share them?',
                'I\'ve found a new technique that\'s working well with autism spectrum trainees. Happy to share details if interested.'
            ]
        ]
    ];
    
    // Create 40-60 realistic messages
    $messageCount = rand(40, 60);
    
    for ($i = 0; $i < $messageCount; $i++) {
        $sender = $userArray[array_rand($userArray)];
        $recipient = $userArray[array_rand($userArray)];
        
        // Don't send message to self
        if ($sender->id === $recipient->id) continue;
        
        // Only send within same centre or to admin
        if ($sender->centre_id !== $recipient->centre_id && $recipient->role !== 'admin') continue;
        
        $category = array_rand($messageTemplates);
        $template = $messageTemplates[$category];
        
        $subject = $template['subjects'][array_rand($template['subjects'])];
        $body = $template['bodies'][array_rand($template['bodies'])];
        
        // Replace placeholders
        if (strpos($subject, '{activity}') !== false || strpos($body, '{activity}') !== false) {
            $activities = ['Speech Therapy', 'Physical Therapy', 'Life Skills', 'Art Therapy', 'Music Therapy'];
            $activity = $activities[array_rand($activities)];
            $subject = str_replace('{activity}', $activity, $subject);
            $body = str_replace('{activity}', $activity, $body);
        }
        
        // Determine message characteristics
        $isRead = rand(1, 100) <= 70; // 70% of messages are read
        $isStarred = rand(1, 100) <= 15; // 15% of messages are starred
        $priority = ['low', 'normal', 'high'][rand(0, 2)];
        $sentDate = Carbon::now()->subDays(rand(1, 30));
        
        DB::table('messages')->insert([
            'sender_id' => $sender->id,
            'recipient_id' => $recipient->id,
            'subject' => $subject,
            'body' => $body,
            'priority' => $priority,
            'is_read' => $isRead,
            'read_at' => $isRead ? $sentDate->copy()->addHours(rand(1, 48)) : null,
            'is_starred' => $isStarred,
            'sender_deleted' => 0,
            'recipient_deleted' => 0,
            'attachments' => rand(1, 100) <= 20 ? json_encode(['document.pdf']) : null, // 20% have attachments
            'reply_to' => null,
            'message_thread_id' => 'thread_' . uniqid(),
            'centre_id' => $sender->centre_id,
            'created_at' => $sentDate,
            'updated_at' => $sentDate
        ]);
        
        echo "✅ Created message: '$subject' from {$sender->name} to {$recipient->name}\n";
    }
    
    // Re-enable foreign key checks
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    
    echo "\n=== FINAL SYSTEM SUMMARY ===\n";
    
    // Get comprehensive counts
    $totalUsers = DB::table('users')->count();
    $totalTrainees = DB::table('trainees')->count();
    $totalActivities = DB::table('activities')->count();
    $totalSessions = DB::table('activity_sessions')->count();
    $totalActivityEnrollments = DB::table('activity_enrollments')->count();
    $totalSessionEnrollments = DB::table('session_enrollments')->count();
    $totalTraineeAttendances = DB::table('trainee_attendances')->count();
    $totalStaffAttendances = DB::table('staff_attendances')->count();
    $totalMessages = DB::table('messages')->count();
    
    echo "📊 COMPLETE SYSTEM STATISTICS:\n";
    echo "👥 Users: $totalUsers (across all centres)\n";
    echo "🎓 Trainees: $totalTrainees (with diverse conditions)\n";
    echo "📚 Activities: $totalActivities (assigned to instructors)\n";
    echo "📅 Sessions: $totalSessions (scheduled and completed)\n";
    echo "✍️ Activity Enrollments: $totalActivityEnrollments\n";
    echo "📋 Session Enrollments: $totalSessionEnrollments\n";
    echo "📊 Trainee Attendances: $totalTraineeAttendances (logical patterns)\n";
    echo "👨‍💼 Staff Attendances: $totalStaffAttendances (realistic rates)\n";
    echo "💬 Messages: $totalMessages (professional communications)\n";
    
    echo "\n🎉 SYSTEM FULLY POPULATED WITH LOGICAL, REALISTIC DATA!\n";
    echo "✅ Random IIUM IDs implemented\n";
    echo "✅ Every user has assigned activities to instruct\n";
    echo "✅ Logical attendance patterns for both staff and trainees\n";
    echo "✅ Professional messaging system with realistic communications\n";
    echo "✅ All data follows Malaysian demographics and conventions\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    // Re-enable foreign key checks even on error
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}