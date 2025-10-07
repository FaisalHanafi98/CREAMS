<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ActivitySession;
use App\Models\Activity;
use Carbon\Carbon;

class CreateSampleSessions extends Command
{
    protected $signature = 'creams:create-sample-sessions';
    protected $description = 'Create sample activity sessions for demo';

    public function handle()
    {
        // Get a sample activity
        $activity = Activity::first();
        if (!$activity) {
            $this->error('No activities found in database');
            return 1;
        }

        // Check if sessions already exist for today
        $existingTodaySessions = ActivitySession::whereDate('session_date', Carbon::today())->count();
        if ($existingTodaySessions > 0) {
            $this->info("Today already has {$existingTodaySessions} sessions");
        } else {
            // Create today's session
            $todaySession = ActivitySession::create([
                'activity_id' => $activity->id,
                'session_id' => 'SES-' . date('Ymd') . '-001',
                'session_code' => 'SES-' . date('Ymd') . '-001',
                'session_name' => 'Today Speech Therapy Session',
                'session_description' => 'Daily speech therapy session for rehabilitation',
                'session_date' => Carbon::today(),
                'start_time' => '09:00:00',
                'session_start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'session_end_time' => '10:00:00',
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
            $this->info("Created today's session: " . $todaySession->session_code);
        }

        // Create tomorrow's session
        $existingTomorrowSessions = ActivitySession::whereDate('session_date', Carbon::tomorrow())->count();
        if ($existingTomorrowSessions > 0) {
            $this->info("Tomorrow already has {$existingTomorrowSessions} sessions");
        } else {
            $tomorrowSession = ActivitySession::create([
                'activity_id' => $activity->id,
                'session_id' => 'SES-' . Carbon::tomorrow()->format('Ymd') . '-001',
                'session_code' => 'SES-' . Carbon::tomorrow()->format('Ymd') . '-001',
                'session_name' => 'Tomorrow Speech Therapy Session',
                'session_description' => 'Next day speech therapy session',
                'session_date' => Carbon::tomorrow(),
                'start_time' => '14:00:00',
                'session_start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'session_end_time' => '15:00:00',
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
            $this->info("Created tomorrow's session: " . $tomorrowSession->session_code);
        }

        $totalSessions = ActivitySession::count();
        $todaySessions = ActivitySession::whereDate('session_date', Carbon::today())->count();
        
        $this->info("Total sessions in database: {$totalSessions}");
        $this->info("Today's sessions: {$todaySessions}");
        
        return 0;
    }
}