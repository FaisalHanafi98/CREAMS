<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\Trainee;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NewActivityController extends Controller
{
    /**
     * Main activity dashboard with role-based views
     */
    public function index(Request $request)
    {
        $role = session('role');
        $viewData = [];
        
        switch ($role) {
            case 'admin':
            case 'supervisor':
                $viewData = $this->getAdminDashboardData($request);
                break;
                
            case 'teacher':
                $viewData = $this->getTeacherDashboardData($request);
                break;
                
            case 'ajk':
                $viewData = $this->getAjkDashboardData($request);
                break;
                
            default:
                return redirect()->route('dashboard')
                    ->with('error', 'Unauthorized access');
        }
        
        return view('activities.unified-dashboard', $viewData);
    }
    
    /**
     * Get admin/supervisor dashboard data
     */
    private function getAdminDashboardData($request)
    {
        // Get statistics with caching
        $stats = Cache::remember('activity_stats_' . session('centre_id'), 300, function() {
            return [
                'total_activities' => Activity::where('centre_id', session('centre_id'))->count(),
                'active_activities' => Activity::where('centre_id', session('centre_id'))
                    ->where('is_active', true)->count(),
                'total_sessions' => ActivitySession::whereHas('activity', function($q) {
                    $q->where('centre_id', session('centre_id'));
                })->count(),
                'today_sessions' => ActivitySession::whereHas('activity', function($q) {
                    $q->where('centre_id', session('centre_id'));
                })->whereDate('session_date', today())->count(),
                'total_enrollments' => ActivityEnrollment::whereHas('activity', function($q) {
                    $q->where('centre_id', session('centre_id'));
                })->count(),
                'active_trainees' => ActivityEnrollment::whereHas('activity', function($q) {
                    $q->where('centre_id', session('centre_id'));
                })->where('enrollment_status', 'enrolled')
                  ->distinct('trainee_id')->count('trainee_id')
            ];
        });
        
        // Get activities with filters
        $query = Activity::with(['sessions' => function($q) {
            $q->orderBy('session_date', 'desc')->limit(5);
        }])->where('centre_id', session('centre_id'));
        
        // Apply filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('activity_code', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        $activities = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get categories for filter
        $categories = $this->getActivityCategories();
        
        // Get upcoming sessions
        $upcomingSessions = ActivitySession::with(['activity', 'teacher'])
            ->whereHas('activity', function($q) {
                $q->where('centre_id', session('centre_id'));
            })
            ->where('session_date', '>=', today())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
        
        return compact('stats', 'activities', 'categories', 'upcomingSessions');
    }
    
    /**
     * Get teacher dashboard data
     */
    private function getTeacherDashboardData($request)
    {
        $teacherId = session('id');
        
        // Get teacher-specific statistics
        $stats = [
            'my_activities' => Activity::where('centre_id', session('centre_id'))
                ->whereHas('sessions', function($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                })->count(),
            'my_sessions' => ActivitySession::where('teacher_id', $teacherId)->count(),
            'today_sessions' => ActivitySession::where('teacher_id', $teacherId)
                ->whereDate('session_date', today())->count(),
            'my_trainees' => ActivityEnrollment::whereHas('session', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })->where('enrollment_status', 'enrolled')
              ->distinct('trainee_id')->count('trainee_id')
        ];
        
        // Get teacher's activities
        $activities = Activity::with(['sessions' => function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)->orderBy('session_date', 'desc')->limit(5);
        }])->whereHas('sessions', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId);
        })->paginate(20);
        
        // Get upcoming sessions for this teacher
        $upcomingSessions = ActivitySession::with(['activity', 'enrollments.trainee'])
            ->where('teacher_id', $teacherId)
            ->where('session_date', '>=', today())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->limit(10)
            ->get();
        
        $categories = $this->getActivityCategories();
        
        return compact('stats', 'activities', 'categories', 'upcomingSessions');
    }
    
    /**
     * Get AJK dashboard data
     */
    private function getAjkDashboardData($request)
    {
        // AJK has read-only access
        $stats = [
            'total_activities' => Activity::where('centre_id', session('centre_id'))->count(),
            'active_activities' => Activity::where('centre_id', session('centre_id'))
                ->where('is_active', true)->count(),
            'today_sessions' => ActivitySession::whereHas('activity', function($q) {
                $q->where('centre_id', session('centre_id'));
            })->whereDate('session_date', today())->count(),
            'active_trainees' => ActivityEnrollment::whereHas('activity', function($q) {
                $q->where('centre_id', session('centre_id'));
            })->where('enrollment_status', 'enrolled')
              ->distinct('trainee_id')->count('trainee_id')
        ];
        
        $activities = Activity::with(['sessions' => function($q) {
            $q->orderBy('session_date', 'desc')->limit(3);
        }])->where('centre_id', session('centre_id'))->paginate(20);
        
        $categories = $this->getActivityCategories();
        $upcomingSessions = collect(); // Empty for AJK
        
        return compact('stats', 'activities', 'categories', 'upcomingSessions');
    }
    
    /**
     * Create new activity
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'max_participants' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:15',
            'required_materials' => 'nullable|array',
            'learning_objectives' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        try {
            // Generate activity code
            $code = $this->generateActivityCode($validated['category']);
            
            $activity = Activity::create([
                'activity_code' => $code,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'difficulty_level' => $validated['difficulty_level'],
                'max_participants' => $validated['max_participants'],
                'min_participants' => $request->min_participants ?? 1,
                'duration_minutes' => $validated['duration_minutes'],
                'required_materials' => $validated['required_materials'] ?? [],
                'learning_objectives' => $validated['learning_objectives'] ?? [],
                'centre_id' => session('centre_id'),
                'created_by' => session('id'),
                'is_active' => true
            ]);
            
            DB::commit();
            
            // Clear cache
            Cache::forget('activity_stats_' . session('centre_id'));
            
            return response()->json([
                'success' => true,
                'message' => 'Activity created successfully',
                'activity' => $activity
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Activity creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create activity'
            ], 500);
        }
    }
    
    /**
     * Schedule new session
     */
    public function scheduleSession(Request $request, $activityId)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'session_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'venue' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1'
        ]);
        
        // Check for double booking
        $conflict = ActivitySession::where('teacher_id', $validated['teacher_id'])
            ->where('session_date', $validated['session_date'])
            ->where(function($q) use ($validated) {
                $q->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhere(function($q2) use ($validated) {
                      $q2->where('start_time', '<=', $validated['start_time'])
                         ->where('end_time', '>=', $validated['end_time']);
                  });
            })
            ->exists();
        
        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher already has a session scheduled at this time'
            ], 422);
        }
        
        DB::beginTransaction();
        try {
            $activity = Activity::findOrFail($activityId);
            
            // Generate session code
            $sessionCode = $this->generateSessionCode($activity);
            
            $session = ActivitySession::create([
                'session_code' => $sessionCode,
                'activity_id' => $activityId,
                'teacher_id' => $validated['teacher_id'],
                'session_date' => $validated['session_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'venue' => $validated['venue'],
                'capacity' => $validated['capacity'],
                'status' => 'scheduled'
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Session scheduled successfully',
                'session' => $session->load('teacher')
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Session scheduling failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule session'
            ], 500);
        }
    }
    
    /**
     * Enroll trainee in activity
     */
    public function enrollTrainee(Request $request, $activityId)
    {
        $validated = $request->validate([
            'trainee_id' => 'required|exists:trainees,id',
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:activity_sessions_new,id'
        ]);
        
        DB::beginTransaction();
        try {
            $activity = Activity::findOrFail($activityId);
            $trainee = Trainee::findOrFail($validated['trainee_id']);
            
            $enrollments = [];
            
            foreach ($validated['session_ids'] as $sessionId) {
                $session = ActivitySession::findOrFail($sessionId);
                
                // Check capacity
                $currentEnrollments = ActivityEnrollment::where('session_id', $sessionId)
                    ->where('enrollment_status', 'enrolled')
                    ->count();
                
                if ($currentEnrollments >= $session->capacity) {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Session ' . $session->session_code . ' is full'
                    ], 422);
                }
                
                // Check for existing enrollment
                $existing = ActivityEnrollment::where('trainee_id', $trainee->id)
                    ->where('session_id', $sessionId)
                    ->first();
                
                if (!$existing) {
                    $enrollment = ActivityEnrollment::create([
                        'trainee_id' => $trainee->id,
                        'activity_id' => $activityId,
                        'session_id' => $sessionId,
                        'enrollment_date' => now(),
                        'enrollment_status' => 'enrolled'
                    ]);
                    
                    $enrollments[] = $enrollment;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Trainee enrolled successfully',
                'enrollments' => $enrollments
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Enrollment failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll trainee'
            ], 500);
        }
    }
    
    /**
     * Mark attendance for session
     */
    public function markAttendance(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'attendance' => 'required|array',
            'attendance.*.trainee_id' => 'required|exists:trainees,id',
            'attendance.*.present' => 'required|boolean',
            'attendance.*.participation_score' => 'nullable|integer|min:0|max:10',
            'attendance.*.notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        try {
            $session = ActivitySession::findOrFail($sessionId);
            
            // Update session status
            if ($session->status === 'scheduled') {
                $session->update(['status' => 'ongoing']);
            }
            
            foreach ($validated['attendance'] as $record) {
                $enrollment = ActivityEnrollment::where('session_id', $sessionId)
                    ->where('trainee_id', $record['trainee_id'])
                    ->first();
                
                if ($enrollment) {
                    $enrollment->update([
                        'attendance_marked' => true,
                        'enrollment_status' => $record['present'] ? 'completed' : 'absent',
                        'participation_score' => $record['participation_score'] ?? null,
                        'progress_notes' => $record['notes'] ?? null
                    ]);
                }
            }
            
            // Check if all attendance marked
            $totalEnrollments = ActivityEnrollment::where('session_id', $sessionId)->count();
            $markedEnrollments = ActivityEnrollment::where('session_id', $sessionId)
                ->where('attendance_marked', true)
                ->count();
            
            if ($totalEnrollments === $markedEnrollments) {
                $session->update(['status' => 'completed']);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Attendance marking failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance'
            ], 500);
        }
    }
    
    /**
     * View specific activity details
     */
    public function show($id)
    {
        $activity = Activity::with(['sessions.teacher', 'sessions.enrollments.trainee'])
            ->where('centre_id', session('centre_id'))
            ->findOrFail($id);
        
        return view('activities.show', compact('activity'));
    }
    
    /**
     * Get activity categories
     */
    private function getActivityCategories()
    {
        return [
            'Rehabilitation' => [
                'Physical Therapy' => ['icon' => 'fas fa-running', 'color' => '#4CAF50'],
                'Occupational Therapy' => ['icon' => 'fas fa-hands-helping', 'color' => '#2196F3'],
                'Speech Therapy' => ['icon' => 'fas fa-comments', 'color' => '#FF9800'],
                'Behavioral Therapy' => ['icon' => 'fas fa-brain', 'color' => '#9C27B0'],
                'Sensory Integration' => ['icon' => 'fas fa-hand-paper', 'color' => '#00BCD4']
            ],
            'Academic' => [
                'Mathematics' => ['icon' => 'fas fa-calculator', 'color' => '#F44336'],
                'Literacy' => ['icon' => 'fas fa-book', 'color' => '#3F51B5'],
                'Science' => ['icon' => 'fas fa-flask', 'color' => '#009688'],
                'Computer Skills' => ['icon' => 'fas fa-laptop', 'color' => '#607D8B'],
                'Art & Creativity' => ['icon' => 'fas fa-palette', 'color' => '#E91E63'],
                'Music Therapy' => ['icon' => 'fas fa-music', 'color' => '#673AB7'],
                'Social Skills' => ['icon' => 'fas fa-users', 'color' => '#795548'],
                'Life Skills' => ['icon' => 'fas fa-home', 'color' => '#FF5722'],
                'Vocational Training' => ['icon' => 'fas fa-briefcase', 'color' => '#FFC107']
            ]
        ];
    }
    
    /**
     * Generate unique activity code
     */
    private function generateActivityCode($category)
    {
        $prefix = substr(strtoupper(str_replace(' ', '', $category)), 0, 3);
        $year = date('y');
        $month = date('m');
        
        $lastActivity = Activity::where('activity_code', 'LIKE', "{$prefix}-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastActivity) {
            $lastNumber = intval(substr($lastActivity->activity_code, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("{$prefix}-{$year}{$month}-%04d", $newNumber);
    }
    
    /**
     * Generate unique session code
     */
    private function generateSessionCode($activity)
    {
        $activityPrefix = substr($activity->activity_code, 0, 3);
        $date = Carbon::now()->format('ymd');
        
        $lastSession = ActivitySession::where('session_code', 'LIKE', "SES-{$activityPrefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastSession) {
            $lastNumber = intval(substr($lastSession->session_code, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf("SES-{$activityPrefix}-{$date}-%03d", $newNumber);
    }
}