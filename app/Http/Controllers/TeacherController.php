<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Trainees;
use App\Models\Classes;
use App\Models\Activities;
use App\Models\Attendances;
use App\Models\Notifications;

class TeacherController extends Controller
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
        
        // Get classes for this teacher
        $classes = Classes::where('teacher_id', $teacherId)
                ->orderBy('name')
                ->get();
        
        // Get upcoming classes for this teacher
        $upcomingClasses = Classes::where('teacher_id', $teacherId)
                        ->where('status', 'active')
                        ->orderBy('start_date')
                        ->take(5)
                        ->get();
        
        // Get today's schedule
        $todaySchedule = $this->getTodaySchedule($teacherId);
        
        // Get trainee statistics
        $traineesCount = $this->getTraineesCount($teacherId);
        $attendanceStats = $this->getAttendanceStats($teacherId);
        
        // Get recent activities
        $recentActivities = Activities::where('teacher_id', $teacherId)
                           ->orderBy('created_at', 'desc')
                           ->take(5)
                           ->get();
        
        // Get unread notifications
        $unreadNotifications = Notifications::where('user_id', $teacherId)
                              ->where('read', false)
                              ->orderBy('created_at', 'desc')
                              ->take(5)
                              ->get();
        
        return view('teacher.dashboard', [
            'classes' => $classes,
            'upcomingClasses' => $upcomingClasses,
            'todaySchedule' => $todaySchedule,
            'traineesCount' => $traineesCount,
            'attendanceStats' => $attendanceStats,
            'recentActivities' => $recentActivities,
            'unreadNotifications' => $unreadNotifications
        ]);
    }
    
    /**
     * Get today's schedule for the teacher.
     *
     * @param int $teacherId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getTodaySchedule($teacherId)
    {
        $dayOfWeek = strtolower(date('l')); // e.g., 'monday', 'tuesday', etc.
        
        // If schedule is stored as JSON in the classes table
        $classes = Classes::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->get()
                ->filter(function($class) use ($dayOfWeek) {
                    // Assuming schedule is stored as JSON with day keys
                    $schedule = json_decode($class->schedule, true);
                    return isset($schedule[$dayOfWeek]) && $schedule[$dayOfWeek];
                });
        
        return $classes;
    }
    
    /**
     * Get trainees count for the teacher.
     *
     * @param int $teacherId
     * @return array
     */
    private function getTraineesCount($teacherId)
    {
        // Get class IDs for this teacher
        $classIds = Classes::where('teacher_id', $teacherId)
                    ->pluck('id')
                    ->toArray();
        
        // Count trainees in these classes
        $totalTrainees = 0;
        
        if (!empty($classIds)) {
            $totalTrainees = DB::table('class_trainee')
                            ->whereIn('class_id', $classIds)
                            ->distinct('trainee_id')
                            ->count('trainee_id');
        }
        
        // Get active classes count
        $activeClasses = Classes::where('teacher_id', $teacherId)
                        ->where('status', 'active')
                        ->count();
        
        return [
            'total' => $totalTrainees,
            'activeClasses' => $activeClasses
        ];
    }
    
    /**
     * Get attendance statistics for the teacher.
     *
     * @param int $teacherId
     * @return array
     */
    private function getAttendanceStats($teacherId)
    {
        // Get class IDs for this teacher
        $classIds = Classes::where('teacher_id', $teacherId)
                    ->pluck('id')
                    ->toArray();
        
        if (empty($classIds)) {
            return [
                'present' => 0,
                'absent' => 0,
                'excused' => 0,
                'late' => 0,
                'presentPercentage' => '0%'
            ];
        }
        
        // Get attendance records for the last 30 days
        $startDate = now()->subDays(30);
        
        $attendanceStats = Attendances::whereIn('class_id', $classIds)
                        ->where('date', '>=', $startDate)
                        ->select('status', DB::raw('count(*) as count'))
                        ->groupBy('status')
                        ->get()
                        ->pluck('count', 'status')
                        ->toArray();
        
        // Calculate total
        $total = array_sum($attendanceStats);
        
        // Ensure all statuses have a value
        $present = $attendanceStats['present'] ?? 0;
        $absent = $attendanceStats['absent'] ?? 0;
        $excused = $attendanceStats['excused'] ?? 0;
        $late = $attendanceStats['late'] ?? 0;
        
        // Calculate percentage
        $presentPercentage = $total > 0 
            ? round(($present + ($late * 0.5)) / $total * 100) . '%' 
            : '0%';
        
        return [
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'late' => $late,
            'presentPercentage' => $presentPercentage
        ];
    }

    /**
     * Display a listing of trainees.
     *
     * @return \Illuminate\View\View
     */
    public function trainees()
    {
        $teacherId = session('id');
        
        // Get classes for this teacher
        $classes = Classes::where('teacher_id', $teacherId)->get();
        $classIds = $classes->pluck('id')->toArray();
        
        // Get trainees in these classes
        $trainees = [];
        if (!empty($classIds)) {
            $traineeIds = DB::table('class_trainee')
                        ->whereIn('class_id', $classIds)
                        ->pluck('trainee_id')
                        ->toArray();
            
            if (!empty($traineeIds)) {
                $trainees = Trainees::whereIn('id', $traineeIds)->get();
            }
        }
        
        // Group trainees by class
        $traineesByClass = [];
        foreach ($classes as $class) {
            $classTrainees = $class->trainees ?? collect();
            $traineesByClass[$class->id] = [
                'class' => $class,
                'trainees' => $classTrainees
            ];
        }
        
        return view('teacher.trainees', [
            'trainees' => $trainees,
            'traineesByClass' => $traineesByClass,
            'classes' => $classes
        ]);
    }

    /**
     * Display a listing of classes.
     *
     * @return \Illuminate\View\View
     */
    public function classes()
    {
        $teacherId = session('id');
        
        // Get all classes for this teacher
        $classes = Classes::where('teacher_id', $teacherId)
                ->orderBy('name')
                ->get();
        
        // Group classes by status
        $activeClasses = $classes->where('status', 'active');
        $completedClasses = $classes->where('status', 'completed');
        $cancelledClasses = $classes->where('status', 'cancelled');
        
        return view('teacher.classes.index', [
            'classes' => $classes,
            'activeClasses' => $activeClasses,
            'completedClasses' => $completedClasses,
            'cancelledClasses' => $cancelledClasses
        ]);
    }

    /**
     * Display a specific class.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewClass($id)
    {
        $teacherId = session('id');
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Get trainees in this class
        $trainees = $class->trainees;
        
        // Get recent attendance records
        $recentAttendance = Attendances::where('class_id', $id)
                ->orderBy('date', 'desc')
                ->limit(10)
                ->get()
                ->groupBy('date');
        
        // Get attendance statistics for this class
        $attendanceStats = $this->getClassAttendanceStats($id);
        
        return view('teacher.classes.view', [
            'class' => $class,
            'trainees' => $trainees,
            'recentAttendance' => $recentAttendance,
            'attendanceStats' => $attendanceStats
        ]);
    }

    /**
     * Manage attendance for a class.
     *
     * @param int $classId
     * @return \Illuminate\View\View
     */
    public function manageAttendance($classId)
    {
        $teacherId = session('id');
        
        // Verify teacher has access to this class
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Get trainees in this class
        $trainees = $class->trainees;
        
        // Get today's date for default attendance form
        $today = now()->format('Y-m-d');
        
        // Check if attendance has already been recorded for today
        $existingAttendance = Attendances::where('class_id', $classId)
                            ->where('date', $today)
                            ->exists();
        
        // Get recent attendance records for this class
        $recentAttendance = Attendances::where('class_id', $classId)
                            ->orderBy('date', 'desc')
                            ->limit(10)
                            ->get()
                            ->groupBy('date');
        
        return view('teacher.attendance.manage', [
            'class' => $class,
            'trainees' => $trainees,
            'today' => $today,
            'existingAttendance' => $existingAttendance,
            'recentAttendance' => $recentAttendance
        ]);
    }

    /**
     * Record attendance for a class.
     *
     * @param Request $request
     * @param int $classId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recordAttendance(Request $request, $classId)
    {
        $teacherId = session('id');
        
        // Verify teacher has access to this class
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Validate request
        $validated = $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent,excused,late'
        ]);
        
        $date = $validated['date'];
        $attendanceData = $validated['attendance'];
        
        // Begin transaction
        DB::beginTransaction();
        
        try {
            // Delete any existing attendance records for this date
            Attendances::where('class_id', $classId)
                    ->where('date', $date)
                    ->delete();
            
            // Create new attendance records
            foreach ($attendanceData as $traineeId => $status) {
                $attendance = new Attendances();
                $attendance->trainee_id = $traineeId;
                $attendance->class_id = $classId;
                $attendance->date = $date;
                $attendance->status = $status;
                $attendance->marked_by = $teacherId;
                $attendance->save();
            }
            
            DB::commit();
            
            return redirect()->route('teacher.attendance.manage', ['classId' => $classId])
                ->with('success', 'Attendance recorded successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording attendance', [
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while recording attendance')
                ->withInput();
        }
    }

    /**
     * View specific trainee details.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewTrainee($id)
    {
        $teacherId = session('id');
        
        // Get trainee
        $trainee = Trainees::findOrFail($id);
        
        // Check if this teacher has access to this trainee
        $hasAccess = $this->teacherHasAccessToTrainee($teacherId, $id);
        
        if (!$hasAccess) {
            return redirect()->route('teacher.trainees')
                ->with('error', 'You do not have permission to view this trainee');
        }
        
        // Get trainee's classes
        $classes = Classes::where('teacher_id', $teacherId)
                    ->whereHas('trainees', function($query) use ($id) {
                        $query->where('trainees.id', $id);
                    })
                    ->get();
        
        // Get attendance records
        $attendance = Attendances::where('trainee_id', $id)
                    ->whereIn('class_id', $classes->pluck('id')->toArray())
                    ->orderBy('date', 'desc')
                    ->take(30)
                    ->get();
        
        // Calculate attendance statistics
        $attendanceStats = [
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
        ];
        
        $totalAttendance = $attendance->count();
        $attendanceStats['presentPercentage'] = $totalAttendance > 0 
            ? round(($attendanceStats['present'] + ($attendanceStats['late'] * 0.5)) / $totalAttendance * 100) . '%' 
            : '0%';
        
        return view('teacher.trainee.view', [
            'trainee' => $trainee,
            'classes' => $classes,
            'attendance' => $attendance,
            'attendanceStats' => $attendanceStats
        ]);
    }

    /**
     * Check if teacher has access to trainee.
     *
     * @param int $teacherId
     * @param int $traineeId
     * @return bool
     */
    private function teacherHasAccessToTrainee($teacherId, $traineeId)
    {
        // Get class IDs for this teacher
        $classIds = Classes::where('teacher_id', $teacherId)
                    ->pluck('id')
                    ->toArray();
        
        if (empty($classIds)) {
            return false;
        }
        
        // Check if trainee is in any of these classes
        $count = DB::table('class_trainee')
                ->whereIn('class_id', $classIds)
                ->where('trainee_id', $traineeId)
                ->count();
        
        return $count > 0;
    }

    /**
     * Get attendance statistics for a specific class.
     *
     * @param int $classId
     * @return array
     */
    private function getClassAttendanceStats($classId)
    {
        // Get attendance records for the last 30 days
        $startDate = now()->subDays(30);
        
        $attendanceStats = Attendances::where('class_id', $classId)
                        ->where('date', '>=', $startDate)
                        ->select('status', DB::raw('count(*) as count'))
                        ->groupBy('status')
                        ->get()
                        ->pluck('count', 'status')
                        ->toArray();
        
        // Calculate total
        $total = array_sum($attendanceStats);
        
        // Ensure all statuses have a value
        $present = $attendanceStats['present'] ?? 0;
        $absent = $attendanceStats['absent'] ?? 0;
        $excused = $attendanceStats['excused'] ?? 0;
        $late = $attendanceStats['late'] ?? 0;
        
        // Calculate percentage
        $presentPercentage = $total > 0 
            ? round(($present + ($late * 0.5)) / $total * 100) . '%' 
            : '0%';
        
        return [
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'late' => $late,
            'total' => $total,
            'presentPercentage' => $presentPercentage
        ];
    }
}