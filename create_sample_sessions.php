<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\ActivitySession;
use App\Models\Activity;
use Carbon\Carbon;

// Get a sample activity
$activity = Activity::first();
if (!$activity) {
    echo "No activities found\n";
    exit;
}

// Create today's session
$todaySession = ActivitySession::create([
    'activity_id' => $activity->id,
    'session_code' => 'SES-' . date('Ymd') . '-001',
    'session_name' => 'Today Speech Therapy Session',
    'session_description' => 'Daily speech therapy session for rehabilitation',
    'session_date' => Carbon::today(),
    'scheduled_date' => Carbon::today(),
    'start_time' => '09:00:00',
    'end_time' => '10:00:00',
    'venue' => 'Speech Therapy Room',
    'max_participants' => 10,
    'current_participants' => 5,
    'attendance_marked' => false,
    'teacher_id' => 15,
    'supervisor_id' => 1,
    'status' => 'active',
    'session_objectives' => 'Improve articulation and speech clarity',
    'notes' => 'Focus on pronunciation exercises'
]);

// Create tomorrow's session
$tomorrowSession = ActivitySession::create([
    'activity_id' => $activity->id,
    'session_code' => 'SES-' . Carbon::tomorrow()->format('Ymd') . '-001',
    'session_name' => 'Tomorrow Speech Therapy Session',
    'session_description' => 'Next day speech therapy session',
    'session_date' => Carbon::tomorrow(),
    'scheduled_date' => Carbon::tomorrow(),
    'start_time' => '14:00:00',
    'end_time' => '15:00:00',
    'venue' => 'Speech Therapy Room',
    'max_participants' => 10,
    'current_participants' => 3,
    'attendance_marked' => false,
    'teacher_id' => 15,
    'supervisor_id' => 1,
    'status' => 'scheduled',
    'session_objectives' => 'Continue speech development',
    'notes' => 'Group therapy session'
]);

echo "Created sessions: " . ActivitySession::count() . "\n";
echo "Today sessions: " . ActivitySession::whereDate('session_date', Carbon::today())->count() . "\n";
echo "Sample session ID: " . $todaySession->id . "\n";