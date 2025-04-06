<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB
;use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\Trainees;
use App\Models\Activities;
use App\Models\Classes;
use App\Models\Attendances;
use App\Models\Centres;
use App\Models\Assets;
use App\Models\Users;
use App\Models\Notifications;
use App\Models\Courses;

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
        
        return view('Teacher.dashboard', [
            'classes' => $classes,
            'upcomingClasses' => $upcomingClasses,
            'todaySchedule' => $todaySchedule,
            'traineesCount' => $traineesCount,
            'attendanceStats' => $attendanceStats
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
        
        // This would depend on how your schedule is structured
        // Option 1: If schedule is stored as JSON in the classes table
        $classes = Classes::where('teacher_id', $teacherId)
                ->where('status', 'active')
                ->get()
                ->filter(function($class) use ($dayOfWeek) {
                    // Assuming schedule is stored as JSON with day keys
                    $schedule = json_decode($class->schedule, true);
                    return isset($schedule[$dayOfWeek]) && $schedule[$dayOfWeek];
                });
                
        // Option 2: If you have a separate schedule table
        // $schedules = Schedule::where('teacher_id', $teacherId)
        //             ->where('day_of_week', $dayOfWeek)
        //             ->with('class')
        //             ->orderBy('start_time')
        //             ->get();
        
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
     * Display a listing of users.
     *
     * @return \Illuminate\View\View
     */
    public function users()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher accessed users list', ['teacher_id' => $teacherId, 'centre_id' => $centreId]);
        
        // For teachers, we might want to show other teachers at the same centre
        // and supervisors they report to
        $teachers = Users::where('role', 'teacher')
                    ->where('centre_id', $centreId)
                    ->where('id', '!=', $teacherId)
                    ->where('status', 'active')
                    ->get();
        
        $supervisors = Users::where('role', 'supervisor')
                        ->where('centre_id', $centreId)
                        ->where('status', 'active')
                        ->get();
        
        return view('teacher.users', [
            'teachers' => $teachers,
            'supervisors' => $supervisors
        ]);
    }
    
    /**
     * Display a listing of trainees.
     *
     * @return \Illuminate\View\View
     */
    public function trainees()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher accessed trainees list', ['teacher_id' => $teacherId, 'centre_id' => $centreId]);
        
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
     * Display a listing of centers.
     *
     * @return \Illuminate\View\View
     */
    public function centres()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher accessed centres list', ['teacher_id' => $teacherId, 'centre_id' => $centreId]);
        
        // Teachers should only see their assigned centre
        $centre = Centres::find($centreId);
        
        // Get teacher count for this centre
        $teacherCount = Users::where('role', 'teacher')
                        ->where('centre_id', $centreId)
                        ->count();
        
        // Get class count for this centre
        $classCount = Classes::where('center_id', $centreId)->count();
        
        // Get trainee count for this centre
        $traineeCount = Trainees::where('centre_id', $centreId)->count();
        
        return view('teacher.centres', [
            'centre' => $centre,
            'teacherCount' => $teacherCount,
            'classCount' => $classCount,
            'traineeCount' => $traineeCount
        ]);
    }
    
    /**
     * Display a listing of assets.
     *
     * @return \Illuminate\View\View
     */
    public function assets()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher accessed assets list', ['teacher_id' => $teacherId, 'centre_id' => $centreId]);
        
        // Get assets for this centre, possibly filtered to only show classroom assets
        $assets = Assets::where('centre_id', $centreId)
                ->where(function($query) {
                    $query->where('asset_type', 'classroom')
                        ->orWhere('asset_type', 'equipment');
                })
                ->get();
        
        // Get assets assigned directly to this teacher
        $assignedAssets = Assets::where('assigned_to_id', $teacherId)->get();
        
        return view('teacher.assets', [
            'assets' => $assets,
            'assignedAssets' => $assignedAssets
        ]);
    }
    
    /**
     * Display a listing of reports.
     *
     * @return \Illuminate\View\View
     */
    public function reports()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed reports', ['teacher_id' => $teacherId]);
        
        // Get classes for this teacher
        $classes = Classes::where('teacher_id', $teacherId)->get();
        $classIds = $classes->pluck('id')->toArray();
        
        // Get attendance data for reports
        $attendanceData = $this->getAttendanceReportData($teacherId, $classIds);
        
        // Get trainee progress data
        $progressData = $this->getTraineeProgressData($teacherId, $classIds);
        
        return view('teacher.reports', [
            'classes' => $classes,
            'attendanceData' => $attendanceData,
            'progressData' => $progressData
        ]);
    }
    
    /**
     * Get attendance report data.
     *
     * @param int $teacherId
     * @param array $classIds
     * @return array
     */
    private function getAttendanceReportData($teacherId, $classIds)
    {
        if (empty($classIds)) {
            return [];
        }
        
        // Get attendance by date for the last 30 days
        $startDate = now()->subDays(30)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');
        
        $attendanceByDate = Attendances::whereIn('class_id', $classIds)
                            ->whereBetween('date', [$startDate, $endDate])
                            ->selectRaw('date, status, COUNT(*) as count')
                            ->groupBy('date', 'status')
                            ->orderBy('date')
                            ->get();
        
        // Format data for chart
        $reportData = [];
        $dateRange = [];
        
        // Generate array of all dates in range
        $currentDate = now()->subDays(30);
        while ($currentDate <= now()) {
            $dateStr = $currentDate->format('Y-m-d');
            $dateRange[$dateStr] = [
                'date' => $currentDate->format('M d'),
                'present' => 0,
                'absent' => 0,
                'excused' => 0,
                'late' => 0
            ];
            $currentDate->addDay();
        }
        
        // Fill in actual attendance data
        foreach ($attendanceByDate as $record) {
            $dateStr = $record->date;
            $status = $record->status;
            $count = $record->count;
            
            if (isset($dateRange[$dateStr])) {
                $dateRange[$dateStr][$status] = $count;
            }
        }
        
        // Convert to array format for chart
        foreach ($dateRange as $data) {
            $reportData[] = $data;
        }
        
        return $reportData;
    }
    
    /**
     * Get trainee progress data.
     *
     * @param int $teacherId
     * @param array $classIds
     * @return array
     */
    private function getTraineeProgressData($teacherId, $classIds)
    {
        // This would depend on your progress tracking model
        // For now, return empty data
        return [];
    }
    
    /**
     * Display settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed settings', ['teacher_id' => $teacherId]);
        
        // Get user settings
        $user = Users::find($teacherId);
        
        return view('teacher.settings', [
            'user' => $user
        ]);
    }
    
    /**
     * Update user settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request)
    {
        $teacherId = session('id');
        Log::info('Teacher updating settings', ['teacher_id' => $teacherId]);
        
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'password' => 'nullable|min:5|confirmed',
            'bio' => 'nullable|string',
            'notification_preferences' => 'sometimes|array'
        ]);
        
        // Update user
        $user = Users::find($teacherId);
        
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        if (isset($validated['bio'])) {
            $user->bio = $validated['bio'];
        }
        
        // Handle notification preferences if they exist
        if (isset($validated['notification_preferences'])) {
            $user->notification_preferences = $validated['notification_preferences'];
        }
        
        $user->save();
        
        return redirect()->route('teacher.settings')
            ->with('success', 'Settings updated successfully');
    }
    
    /**
     * Display a listing of activities.
     *
     * @return \Illuminate\View\View
     */
    public function activities()
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher accessed activities list', ['teacher_id' => $teacherId, 'centre_id' => $centreId]);
        
        // Get activities assigned to this teacher
        $activities = Activities::where('teacher_id', $teacherId)
                    ->orderBy('date', 'desc')
                    ->get();
        
        // Get courses assigned to this teacher
        $courses = Courses::where('teacher_id', $teacherId)
                ->get();
        
        return view('teacher.activities', [
            'activities' => $activities,
            'courses' => $courses
        ]);
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
        Log::info('Teacher viewing trainee', ['teacher_id' => $teacherId, 'trainee_id' => $id]);
        
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
     * Update trainee progress.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateTraineeProgress(Request $request, $id)
    {
        $teacherId = session('id');
        Log::info('Teacher updating trainee progress', ['teacher_id' => $teacherId, 'trainee_id' => $id]);
        
        // Check if teacher has access to trainee
        $hasAccess = $this->teacherHasAccessToTrainee($teacherId, $id);
        
        if (!$hasAccess) {
            return redirect()->route('teacher.trainees')
                ->with('error', 'You do not have permission to update this trainee\'s progress');
        }
        
        // Validate input
        $validated = $request->validate([
            'progress_notes' => 'required|string',
            'progress_date' => 'required|date',
            'progress_rating' => 'required|integer|min:1|max:5',
            'class_id' => 'required|exists:classes,id'
        ]);
        
        // Check if teacher has access to this class
        $class = Classes::find($validated['class_id']);
        if ($class->teacher_id != $teacherId) {
            return redirect()->route('teacher.trainees')
                ->with('error', 'You do not have permission to update progress for this class');
        }
        
        // In a full implementation, you would save this to a progress table
        // For now, just log it and return success
        Log::info('Progress update would be saved', [
            'teacher_id' => $teacherId,
            'trainee_id' => $id,
            'class_id' => $validated['class_id'],
            'notes' => $validated['progress_notes'],
            'date' => $validated['progress_date'],
            'rating' => $validated['progress_rating']
        ]);
        
        return redirect()->back()->with('success', 'Progress updated successfully');
    }
    
    /**
     * Display notifications for the teacher.
     *
     * @return \Illuminate\View\View
     */
    public function notifications()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed notifications', ['teacher_id' => $teacherId]);
        
        // Get notifications for this teacher
        $notifications = Notifications::where('user_id', $teacherId)
                        ->where('user_type', 'App\\Models\\Users')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);
        
        // Get unread count
        $unreadCount = Notifications::where('user_id', $teacherId)
                    ->where('user_type', 'App\\Models\\Users')
                    ->where('read', false)
                    ->count();
        
        return view('teacher.notifications', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Mark notifications as read.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markNotificationsRead(Request $request)
    {
        $teacherId = session('id');
        Log::info('Teacher marking notifications as read', ['teacher_id' => $teacherId]);
        
        // Validate request
        $validated = $request->validate([
            'notification_ids' => 'sometimes|array',
            'notification_ids.*' => 'exists:notifications,id',
            'all' => 'sometimes|boolean'
        ]);
        
        // Mark specific notifications as read
        if (isset($validated['notification_ids'])) {
            Notifications::whereIn('id', $validated['notification_ids'])
                ->where('user_id', $teacherId)
                ->where('user_type', 'App\\Models\\Users')
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
        }
        
        // Mark all notifications as read
        if (isset($validated['all']) && $validated['all']) {
            Notifications::where('user_id', $teacherId)
                ->where('user_type', 'App\\Models\\Users')
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
        }
        
        return redirect()->back()->with('success', 'Notifications marked as read');
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
        Log::info('Teacher managing attendance', ['teacher_id' => $teacherId, 'class_id' => $classId]);
        
        // Verify teacher has access to this class
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->first();
        
        if (!$class) {
            return redirect()->route('teacher.classes')
                ->with('error', 'You do not have permission to manage attendance for this class');
        }
        
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
        Log::info('Teacher recording attendance', ['teacher_id' => $teacherId, 'class_id' => $classId]);
        
        // Verify teacher has access to this class
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->first();
        
        if (!$class) {
            return redirect()->route('teacher.classes')
                ->with('error', 'You do not have permission to manage attendance for this class');
        }
        
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
     * Display the classes list.
     *
     * @return \Illuminate\View\View
     */
    public function classes()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed classes list', ['teacher_id' => $teacherId]);
        
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
        Log::info('Teacher viewing class', ['teacher_id' => $teacherId, 'class_id' => $id]);
        
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
    
    /**
     * Edit a class.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editClass($id)
    {
        $teacherId = session('id');
        Log::info('Teacher editing class', ['teacher_id' => $teacherId, 'class_id' => $id]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Get course information
        $course = $class->course;
        
        // Get center information
        $centre = $class->centre;
        
        return view('teacher.classes.edit', [
            'class' => $class,
            'course' => $course,
            'centre' => $centre
        ]);
    }
    
    /**
     * Update a class.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateClass(Request $request, $id)
    {
        $teacherId = session('id');
        Log::info('Teacher updating class', ['teacher_id' => $teacherId, 'class_id' => $id]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|string|max:255',
            'schedule' => 'sometimes|required|array',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date'
        ]);
        
        // Update class
        if (isset($validated['name'])) {
            $class->name = $validated['name'];
        }
        
        if (isset($validated['description'])) {
            $class->description = $validated['description'];
        }
        
        if (isset($validated['location'])) {
            $class->location = $validated['location'];
        }
        
        if (isset($validated['schedule'])) {
            $class->schedule = $validated['schedule'];
        }
        
        if (isset($validated['start_date'])) {
            $class->start_date = $validated['start_date'];
        }
        
        if (isset($validated['end_date'])) {
            $class->end_date = $validated['end_date'];
        }
        
        $class->save();
        
        return redirect()->route('teacher.classes.view', ['id' => $id])
            ->with('success', 'Class updated successfully');
    }
    
    /**
     * Manage trainees in a class.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function manageClassTrainees($id)
    {
        $teacherId = session('id');
        $centreId = session('centre_id');
        Log::info('Teacher managing class trainees', ['teacher_id' => $teacherId, 'class_id' => $id]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Get trainees currently in this class
        $currentTrainees = $class->trainees;
        $currentTraineeIds = $currentTrainees->pluck('id')->toArray();
        
        // Get all trainees in the centre who could be added to this class
        $availableTrainees = Trainees::where('centre_id', $centreId)
                        ->whereNotIn('id', $currentTraineeIds)
                        ->get();
        
        return view('teacher.classes.manage-trainees', [
            'class' => $class,
            'currentTrainees' => $currentTrainees,
            'availableTrainees' => $availableTrainees
        ]);
    }
    
    /**
     * Add trainees to a class.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addClassTrainees(Request $request, $id)
    {
        $teacherId = session('id');
        Log::info('Teacher adding trainees to class', ['teacher_id' => $teacherId, 'class_id' => $id]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Validate request
        $validated = $request->validate([
            'trainee_ids' => 'required|array',
            'trainee_ids.*' => 'exists:trainees,id'
        ]);
        
        // Add trainees to class
        $class->trainees()->attach($validated['trainee_ids']);
        
        return redirect()->route('teacher.classes.manage-trainees', ['id' => $id])
            ->with('success', 'Trainees added to class successfully');
    }
    
    /**
     * Remove trainee from class.
     *
     * @param int $classId
     * @param int $traineeId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeClassTrainee($classId, $traineeId)
    {
        $teacherId = session('id');
        Log::info('Teacher removing trainee from class', [
            'teacher_id' => $teacherId, 
            'class_id' => $classId,
            'trainee_id' => $traineeId
        ]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Remove trainee from class
        $class->trainees()->detach($traineeId);
        
        return redirect()->route('teacher.classes.manage-trainees', ['id' => $classId])
            ->with('success', 'Trainee removed from class successfully');
    }
    
    /**
     * Create a new lesson note.
     *
     * @param int $classId
     * @return \Illuminate\View\View
     */
    public function createLessonNote($classId)
    {
        $teacherId = session('id');
        Log::info('Teacher creating lesson note', ['teacher_id' => $teacherId, 'class_id' => $classId]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        return view('teacher.notes.create', [
            'class' => $class
        ]);
    }
    
    /**
     * Store a new lesson note.
     *
     * @param Request $request
     * @param int $classId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeLessonNote(Request $request, $classId)
    {
        $teacherId = session('id');
        Log::info('Teacher storing lesson note', ['teacher_id' => $teacherId, 'class_id' => $classId]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Validate request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'date' => 'required|date',
            'activity_type' => 'required|string|max:255'
        ]);
        
        // Create activity record for this lesson note
        $activity = new Activities();
        $activity->name = $validated['title'];
        $activity->description = $validated['content'];
        $activity->date = $validated['date'];
        $activity->user_id = $teacherId;
        // Add other fields as necessary
        $activity->save();
        
        return redirect()->route('teacher.classes.view', ['id' => $classId])
            ->with('success', 'Lesson note created successfully');
    }
    
    /**
     * Export attendance report for a class.
     *
     * @param int $classId
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAttendance($classId)
    {
        $teacherId = session('id');
        Log::info('Teacher exporting attendance', ['teacher_id' => $teacherId, 'class_id' => $classId]);
        
        // Get class and verify teacher has access
        $class = Classes::where('id', $classId)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
        
        // Get all attendance records for this class
        $attendanceRecords = Attendances::where('class_id', $classId)
                            ->orderBy('date', 'desc')
                            ->get();
        
        // Get all trainees in this class
        $trainees = $class->trainees;
        
        // Prepare data for export
        $exportData = [];
        $headers = ['Date', 'Trainee ID', 'Trainee Name', 'Status', 'Remarks'];
        $exportData[] = $headers;
        
        foreach ($attendanceRecords as $record) {
            $trainee = $trainees->firstWhere('id', $record->trainee_id);
            if ($trainee) {
                $exportData[] = [
                    $record->date,
                    $trainee->id,
                    $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                    $record->status,
                    $record->remarks ?? ''
                ];
            }
        }
        
        // Generate CSV
        $fileName = 'class_' . $classId . '_attendance_' . date('Y-m-d') . '.csv';
        $tempFile = tempnam(sys_get_temp_dir(), 'attendance_export_');
        $file = fopen($tempFile, 'w');
        
        foreach ($exportData as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        // Return file as download
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
    
    /**
     * Display profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed profile', ['teacher_id' => $teacherId]);
        
        // Get user data
        $user = Users::find($teacherId);
        
        // Get classes taught by this teacher
        $classes = Classes::where('teacher_id', $teacherId)->get();
        
        // Count total trainees taught
        $traineeCount = 0;
        $traineeIds = [];
        
        foreach ($classes as $class) {
            foreach ($class->trainees as $trainee) {
                if (!in_array($trainee->id, $traineeIds)) {
                    $traineeIds[] = $trainee->id;
                    $traineeCount++;
                }
            }
        }
        
        return view('teacher.profile', [
            'user' => $user,
            'classes' => $classes,
            'traineeCount' => $traineeCount
        ]);
    }
    
    /**
     * Update profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $teacherId = session('id');
        Log::info('Teacher updating profile', ['teacher_id' => $teacherId]);
        
        // Validate request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($teacherId)
            ],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:5|confirmed',
        ]);
        
        // Get user
        $user = Users::find($teacherId);
        
        // Update basic information
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        
        if (isset($validated['phone'])) {
            $user->phone = $validated['phone'];
        }
        
        if (isset($validated['bio'])) {
            $user->bio = $validated['bio'];
        }
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '_' . $avatar->getClientOriginalName();
            
            // Delete old avatar if exists
            if ($user->avatar && file_exists(storage_path('app/public/avatars/' . $user->avatar))) {
                unlink(storage_path('app/public/avatars/' . $user->avatar));
            }
            
            // Save new avatar
            $avatar->storeAs('public/avatars', $avatarName);
            $user->avatar = $avatarName;
        }
        
        // Handle password change
        if (isset($validated['current_password']) && isset($validated['new_password'])) {
            // Verify current password
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()
                    ->with('error', 'Current password is incorrect')
                    ->withInput();
            }
            
            // Update password
            $user->password = Hash::make($validated['new_password']);
        }
        
        $user->save();
        
        return redirect()->route('teacher.profile')
            ->with('success', 'Profile updated successfully');
    }
    
    /**
     * Display messages page.
     *
     * @return \Illuminate\View\View
     */
    public function messages()
    {
        $teacherId = session('id');
        Log::info('Teacher accessed messages', ['teacher_id' => $teacherId]);
        
        // Get received messages
        $receivedMessages = DB::table('messages')
                            ->where('recipient_id', $teacherId)
                            ->where('recipient_type', 'App\\Models\\Users')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);
        
        // Get sent messages
        $sentMessages = DB::table('messages')
                        ->where('sender_id', $teacherId)
                        ->where('sender_type', 'App\\Models\\Users')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        
        // Get contacts (supervisors, other teachers at same centre)
        $centreId = session('centre_id');
        $contacts = Users::where('centre_id', $centreId)
                    ->where('id', '!=', $teacherId)
                    ->where('status', 'active')
                    ->get();
        
        return view('teacher.messages', [
            'receivedMessages' => $receivedMessages,
            'sentMessages' => $sentMessages,
            'contacts' => $contacts
        ]);
    }
    
    /**
     * Send a message.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request)
    {
        $teacherId = session('id');
        Log::info('Teacher sending message', ['teacher_id' => $teacherId]);
        
        // Validate request
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'content' => 'required|string'
        ]);
        
        // Create message
        DB::table('messages')->insert([
            'sender_id' => $teacherId,
            'sender_type' => 'App\\Models\\Users',
            'recipient_id' => $validated['recipient_id'],
            'recipient_type' => 'App\\Models\\Users',
            'subject' => $validated['subject'],
            'content' => $validated['content'],
            'read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return redirect()->route('teacher.messages')
            ->with('success', 'Message sent successfully');
    }
    
    /**
     * View a message.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function viewMessage($id)
    {
        $teacherId = session('id');
        Log::info('Teacher viewing message', ['teacher_id' => $teacherId, 'message_id' => $id]);
        
        // Get message
        $message = DB::table('messages')
                ->where('id', $id)
                ->where(function($query) use ($teacherId) {
                    $query->where('recipient_id', $teacherId)
                        ->where('recipient_type', 'App\\Models\\Users')
                        ->orWhere(function($query) use ($teacherId) {
                            $query->where('sender_id', $teacherId)
                                ->where('sender_type', 'App\\Models\\Users');
                        });
                })
                ->first();
        
        if (!$message) {
            return redirect()->route('teacher.messages')
                ->with('error', 'Message not found');
        }
        
        // Mark as read if this teacher is the recipient
        if ($message->recipient_id == $teacherId && $message->recipient_type == 'App\\Models\\Users' && !$message->read) {
            DB::table('messages')
                ->where('id', $id)
                ->update([
                    'read' => true,
                    'read_at' => now()
                ]);
        }
        
        // Get sender info
        $sender = Users::find($message->sender_id);
        
        // Get recipient info
        $recipient = Users::find($message->recipient_id);
        
        return view('teacher.messages.view', [
            'message' => $message,
            'sender' => $sender,
            'recipient' => $recipient
        ]);
    }
    
    /**
     * Delete a message.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteMessage($id)
    {
        $teacherId = session('id');
        Log::info('Teacher deleting message', ['teacher_id' => $teacherId, 'message_id' => $id]);
        
        // Verify ownership/access to message
        $message = DB::table('messages')
                ->where('id', $id)
                ->where(function($query) use ($teacherId) {
                    $query->where('recipient_id', $teacherId)
                        ->where('recipient_type', 'App\\Models\\Users')
                        ->orWhere(function($query) use ($teacherId) {
                            $query->where('sender_id', $teacherId)
                                ->where('sender_type', 'App\\Models\\Users');
                        });
                })
                ->first();
        
        if (!$message) {
            return redirect()->route('teacher.messages')
                ->with('error', 'Message not found');
        }
        
        // Delete message
        DB::table('messages')->where('id', $id)->delete();
        
        return redirect()->route('teacher.messages')
            ->with('success', 'Message deleted successfully');
    }
}