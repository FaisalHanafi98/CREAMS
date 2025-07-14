<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Classes;
use App\Models\Trainees;
use App\Models\Activities;
use App\Models\Attendances;
use App\Models\Notifications;

class TeacherDashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:teacher');
    }

    /**
     * Display the teacher dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        
        Log::info('Teacher accessed dashboard', [
            'teacher_id' => $teacherId,
            'centre_id' => $centreId
        ]);
        
        $data = [
            'todayClasses' => $this->getTodayClasses($teacherId),
            'upcomingClasses' => $this->getUpcomingClasses($teacherId),
            'recentTrainees' => $this->getRecentTrainees($teacherId),
            'traineeSummary' => $this->getTraineeSummary($teacherId),
            'attendanceSummary' => $this->getAttendanceSummary($teacherId),
            'recentActivities' => $this->getRecentActivities($teacherId),
            'notifications' => $this->getNotifications($teacherId)
        ];
        
        return view('teacher.dashboard', $data);
    }
    
    /**
     * Get today's classes.
     *
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTodayClasses($teacherId)
    {
        $today = now()->format('Y-m-d');
        $dayOfWeek = strtolower(now()->format('l')); // e.g., 'monday'
        
        return Classes::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->where(function($query) use ($today, $dayOfWeek) {
                $query->whereDate('start_date', '<=', $today)
                      ->whereDate('end_date', '>=', $today);
            })
            ->get()
            ->filter(function($class) use ($dayOfWeek) {
                $schedule = json_decode($class->schedule, true) ?? [];
                return isset($schedule[$dayOfWeek]) && $schedule[$dayOfWeek];
            });
    }
    
    /**
     * Get upcoming classes.
     *
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getUpcomingClasses($teacherId)
    {
        $today = now()->format('Y-m-d');
        $nextWeek = now()->addDays(7)->format('Y-m-d');
        
        return Classes::where('teacher_id', $teacherId)
            ->where('status', 'active')
            ->where(function($query) use ($today, $nextWeek) {
                $query->whereDate('start_date', '>=', $today)
                      ->whereDate('start_date', '<=', $nextWeek);
            })
            ->orderBy('start_date')
            ->limit(5)
            ->get();
    }
    
    /**
     * Get recent trainees.
     *
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentTrainees($teacherId)
    {
        $classIds = Classes::where('teacher_id', $teacherId)
                  ->pluck('id')
                  ->toArray();
        
        if (empty($classIds)) {
            return collect();
        }
        
        $traineeIds = DB::table('class_trainee')
                    ->whereIn('class_id', $classIds)
                    ->pluck('trainee_id')
                    ->toArray();
        
        return Trainees::whereIn('id', $traineeIds)
               ->orderBy('created_at', 'desc')
               ->limit(5)
               ->get();
    }
    
    /**
     * Get trainee summary.
     *
     * @param int $teacherId
     * @return array
     */
    private function getTraineeSummary($teacherId)
    {
        $classIds = Classes::where('teacher_id', $teacherId)
                  ->pluck('id')
                  ->toArray();
        
        if (empty($classIds)) {
            return [
                'total' => 0,
                'byCondition' => [],
                'activeClasses' => 0
            ];
        }
        
        $traineeIds = DB::table('class_trainee')
                    ->whereIn('class_id', $classIds)
                    ->distinct('trainee_id')
                    ->pluck('trainee_id')
                    ->toArray();
        
        $trainees = Trainees::whereIn('id', $traineeIds)->get();
        
        $byCondition = $trainees->groupBy('trainee_condition')
                      ->map(function($group) {
                          return $group->count();
                      });
        
        $activeClasses = Classes::where('teacher_id', $teacherId)
                        ->where('status', 'active')
                        ->count();
        
        return [
            'total' => count($traineeIds),
            'byCondition' => $byCondition,
            'activeClasses' => $activeClasses
        ];
    }
    
    /**
     * Get attendance summary.
     *
     * @param int $teacherId
     * @return array
     */
    private function getAttendanceSummary($teacherId)
    {
        $classIds = Classes::where('teacher_id', $teacherId)
                  ->pluck('id')
                  ->toArray();
        
        if (empty($classIds)) {
            return [
                'present' => 0,
                'absent' => 0,
                'excused' => 0,
                'late' => 0,
                'total' => 0,
                'rate' => '0%'
            ];
        }
        
        $lastMonth = now()->subDays(30)->format('Y-m-d');
        
        $attendanceStats = Attendances::whereIn('class_id', $classIds)
                         ->where('date', '>=', $lastMonth)
                         ->select('status', DB::raw('count(*) as count'))
                         ->groupBy('status')
                         ->get()
                         ->pluck('count', 'status')
                         ->toArray();
        
        $present = $attendanceStats['present'] ?? 0;
        $absent = $attendanceStats['absent'] ?? 0;
        $excused = $attendanceStats['excused'] ?? 0;
        $late = $attendanceStats['late'] ?? 0;
        $total = $present + $absent + $excused + $late;
        
        $rate = $total > 0 
              ? round(($present + ($late * 0.5)) / $total * 100) . '%' 
              : '0%';
        
        return [
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'late' => $late,
            'total' => $total,
            'rate' => $rate
        ];
    }
    
    /**
     * Get recent activities.
     *
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentActivities($teacherId)
    {
        return Activities::where('teacher_id', $teacherId)
               ->orderBy('created_at', 'desc')
               ->limit(5)
               ->get();
    }
    
    /**
     * Get notifications.
     *
     * @param int $teacherId
     * @return array
     */
    private function getNotifications($teacherId)
    {
        // Try to get notifications from the database
        try {
            if (DB::getSchemaBuilder()->hasTable('notifications')) {
                $unread = Notifications::where('user_id', $teacherId)
                         ->where('user_type', 'teacher')
                         ->where('read', false)
                         ->orderBy('created_at', 'desc')
                         ->limit(5)
                         ->get();
                
                $count = Notifications::where('user_id', $teacherId)
                        ->where('user_type', 'teacher')
                        ->where('read', false)
                        ->count();
                
                return [
                    'unread' => $unread,
                    'count' => $count
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        // Return empty notifications if there was an error or the table doesn't exist
        return [
            'unread' => collect(),
            'count' => 0
        ];
    }
}