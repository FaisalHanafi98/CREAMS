<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Users;
use App\Models\Trainees;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities
     */
    public function index()
    {
        try {
            $role = session('role');
            $userId = session('id');

            $query = Activity::with(['sessions', 'creator', 'centre']);

            // Role-based filtering
            if ($role === 'teacher') {
                $query->whereHas('sessions', function ($q) use ($userId) {
                    $q->where('teacher_id', $userId);
                });
            } elseif ($role === 'ajk') {
                // AJK can only view activities
                $query->where('is_active', true);
            }

            $activities = $query->orderBy('created_at', 'desc')->paginate(12);

            // Get statistics
            $stats = $this->getActivityStats($role, $userId);

            return view('activities.index', compact('activities', 'stats', 'role'));

        } catch (Exception $e) {
            Log::error('Error loading activities index: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load activities. Please try again.');
        }
    }

    /**
     * Display activity categories for rehabilitation module
     */
    public function categories()
    {
        try {
            $categories = [
                'Physical Therapy' => [
                    'icon' => 'fas fa-running',
                    'description' => 'Improve physical strength and mobility',
                    'count' => Activity::where('category', 'Physical Therapy')->count()
                ],
                'Occupational Therapy' => [
                    'icon' => 'fas fa-hands-helping',
                    'description' => 'Develop daily living skills',
                    'count' => Activity::where('category', 'Occupational Therapy')->count()
                ],
                'Speech Therapy' => [
                    'icon' => 'fas fa-comments',
                    'description' => 'Enhance communication abilities',
                    'count' => Activity::where('category', 'Speech Therapy')->count()
                ],
                'Behavioral Therapy' => [
                    'icon' => 'fas fa-brain',
                    'description' => 'Manage behaviors and emotions',
                    'count' => Activity::where('category', 'Behavioral Therapy')->count()
                ],
                'Sensory Integration' => [
                    'icon' => 'fas fa-hand-paper',
                    'description' => 'Process sensory information effectively',
                    'count' => Activity::where('category', 'Sensory Integration')->count()
                ],
                'Life Skills' => [
                    'icon' => 'fas fa-graduation-cap',
                    'description' => 'Learn essential life skills',
                    'count' => Activity::where('category', 'Life Skills')->count()
                ]
            ];

            return view('rehabilitation.categories', compact('categories'));

        } catch (Exception $e) {
            Log::error('Error loading rehabilitation categories: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Unable to load categories.');
        }
    }

    /**
     * Show activities for a specific category
     */
    public function categoryShow($category)
    {
        try {
            $activities = Activity::where('category', $category)
                ->where('is_active', true)
                ->with(['sessions', 'creator'])
                ->paginate(12);

            return view('rehabilitation.category-show', compact('category', 'activities'));

        } catch (Exception $e) {
            Log::error('Error loading category activities: ' . $e->getMessage());
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'Unable to load activities for this category.');
        }
    }

    /**
     * Show the form for creating a new activity
     */
    public function create()
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create activities.');
        }

        $categories = $this->getActivityCategories();
        
        return view('activities.create', compact('categories'));
    }

    /**
     * Store a newly created activity
     */
    public function store(Request $request)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to create activities.');
        }

        $validated = $request->validate([
            'activity_name' => 'required|string|max:255',
            'activity_code' => 'required|string|max:20|unique:activities',
            'description' => 'required|string',
            'category' => 'required|string',
            'activity_type' => 'required|in:Individual,Group,Both',
            'objectives' => 'nullable|string',
            'materials_needed' => 'nullable|string',
            'age_group' => 'required|string',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|gte:min_participants',
            'duration_minutes' => 'required|integer|min:15',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $activity = Activity::create([
                'activity_name' => $validated['activity_name'],
                'activity_code' => strtoupper($validated['activity_code']),
                'description' => $validated['description'],
                'category' => $validated['category'],
                'activity_type' => $validated['activity_type'],
                'objectives' => $validated['objectives'],
                'materials_needed' => $validated['materials_needed'],
                'age_group' => $validated['age_group'],
                'difficulty_level' => $validated['difficulty_level'],
                'min_participants' => $validated['min_participants'],
                'max_participants' => $validated['max_participants'],
                'duration_minutes' => $validated['duration_minutes'],
                'is_active' => $request->has('is_active'),
                'created_by' => session('id'),
                'centre_id' => session('centre_id')
            ]);

            DB::commit();

            return redirect()->route('activities.show', $activity->id)
                ->with('success', 'Activity created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating activity: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the activity.');
        }
    }

    /**
     * Display the specified activity
     */
    public function show($id)
    {
        try {
            $activity = Activity::with(['sessions.teacher', 'sessions.enrollments.trainee', 'creator', 'centre'])
                ->findOrFail($id);

            $role = session('role');
            $userId = session('id');

            // Check access for teachers
            if ($role === 'teacher') {
                $hasAccess = $activity->sessions->contains('teacher_id', $userId);
                if (!$hasAccess) {
                    return redirect()->route('activities.index')
                        ->with('error', 'You do not have access to this activity.');
                }
            }

            // Get activity statistics
            $stats = [
                'totalSessions' => $activity->sessions->count(),
                'upcomingSessions' => $activity->upcomingSessions->count(),
                'completedSessions' => $activity->completedSessions->count(),
                'totalEnrollments' => SessionEnrollment::whereIn('session_id', $activity->sessions->pluck('id'))->count(),
                'averageAttendance' => $this->calculateAverageAttendance($activity)
            ];

            return view('activities.show', compact('activity', 'stats', 'role'));

        } catch (Exception $e) {
            Log::error('Error showing activity: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found.');
        }
    }

    /**
     * Show the form for editing the activity
     */
    public function edit($id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to edit activities.');
        }

        try {
            $activity = Activity::findOrFail($id);
            $categories = $this->getActivityCategories();
            
            return view('activities.edit', compact('activity', 'categories'));

        } catch (Exception $e) {
            Log::error('Error loading activity for edit: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found.');
        }
    }

    /**
     * Update the specified activity
     */
    public function update(Request $request, $id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to update activities.');
        }

        try {
            $activity = Activity::findOrFail($id);

            $validated = $request->validate([
                'activity_name' => 'required|string|max:255',
                'activity_code' => 'required|string|max:20|unique:activities,activity_code,' . $id,
                'description' => 'required|string',
                'category' => 'required|string',
                'activity_type' => 'required|in:Individual,Group,Both',
                'objectives' => 'nullable|string',
                'materials_needed' => 'nullable|string',
                'age_group' => 'required|string',
                'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
                'min_participants' => 'required|integer|min:1',
                'max_participants' => 'required|integer|gte:min_participants',
                'duration_minutes' => 'required|integer|min:15',
                'is_active' => 'boolean'
            ]);

            $activity->update([
                'activity_name' => $validated['activity_name'],
                'activity_code' => strtoupper($validated['activity_code']),
                'description' => $validated['description'],
                'category' => $validated['category'],
                'activity_type' => $validated['activity_type'],
                'objectives' => $validated['objectives'],
                'materials_needed' => $validated['materials_needed'],
                'age_group' => $validated['age_group'],
                'difficulty_level' => $validated['difficulty_level'],
                'min_participants' => $validated['min_participants'],
                'max_participants' => $validated['max_participants'],
                'duration_minutes' => $validated['duration_minutes'],
                'is_active' => $request->has('is_active')
            ]);

            return redirect()->route('activities.show', $activity->id)
                ->with('success', 'Activity updated successfully!');

        } catch (Exception $e) {
            Log::error('Error updating activity: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the activity.');
        }
    }

    /**
     * Remove the specified activity
     */
    public function destroy($id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to delete activities.');
        }

        try {
            $activity = Activity::findOrFail($id);
            
            // Check if activity has upcoming sessions
            if ($activity->upcomingSessions->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete activity with upcoming sessions.');
            }

            $activity->delete();

            return redirect()->route('activities.index')
                ->with('success', 'Activity deleted successfully!');

        } catch (Exception $e) {
            Log::error('Error deleting activity: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the activity.');
        }
    }

    /**
     * Display sessions for an activity
     */
    public function sessions($id)
    {
        try {
            $activity = Activity::with(['sessions.teacher', 'sessions.enrollments'])
                ->findOrFail($id);

            $role = session('role');
            $userId = session('id');

            // Filter sessions based on role
            $sessions = $activity->sessions();
            
            if ($role === 'teacher') {
                $sessions = $sessions->where('teacher_id', $userId);
            }

            $sessions = $sessions->orderBy('scheduled_date', 'desc')->paginate(10);

            return view('activities.sessions', compact('activity', 'sessions', 'role'));

        } catch (Exception $e) {
            Log::error('Error loading activity sessions: ' . $e->getMessage());
            return redirect()->route('activities.show', $id)
                ->with('error', 'Unable to load sessions.');
        }
    }

    /**
     * Create a new session for an activity
     */
    public function createSession(Request $request, $id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return redirect()->route('activities.sessions', $id)
                ->with('error', 'You do not have permission to create sessions.');
        }

        try {
            $activity = Activity::findOrFail($id);

            $validated = $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'scheduled_date' => 'required|date|after:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'venue' => 'nullable|string|max:255',
                'room_number' => 'nullable|string|max:50',
                'max_participants' => 'required|integer|min:' . $activity->min_participants . '|max:' . $activity->max_participants,
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Calculate duration
            $start = Carbon::parse($validated['start_time']);
            $end = Carbon::parse($validated['end_time']);
            $duration = $start->diffInMinutes($end);

            $session = ActivitySession::create([
                'activity_id' => $activity->id,
                'teacher_id' => $validated['teacher_id'],
                'scheduled_date' => $validated['scheduled_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'duration_minutes' => $duration,
                'venue' => $validated['venue'],
                'room_number' => $validated['room_number'],
                'max_participants' => $validated['max_participants'],
                'notes' => $validated['notes']
            ]);

            DB::commit();

            return redirect()->route('activities.sessions', $id)
                ->with('success', 'Session created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating session: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the session.');
        }
    }

    /**
     * Show attendance marking form
     */
    public function markAttendance($activityId, $sessionId)
    {
        try {
            $session = ActivitySession::with(['activity', 'enrollments.trainee'])
                ->where('activity_id', $activityId)
                ->findOrFail($sessionId);

            $role = session('role');
            $userId = session('id');

            // Check permissions
            if ($role === 'teacher' && $session->teacher_id != $userId) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'You can only mark attendance for your own sessions.');
            }

            if ($session->status !== 'scheduled' && $session->status !== 'ongoing') {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'Cannot mark attendance for ' . $session->status . ' sessions.');
            }

            return view('activities.attendance', compact('session'));

        } catch (Exception $e) {
            Log::error('Error loading attendance form: ' . $e->getMessage());
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'Session not found.');
        }
    }

    /**
     * Store attendance records
     */
    public function storeAttendance(Request $request, $activityId, $sessionId)
    {
        try {
            $session = ActivitySession::where('activity_id', $activityId)
                ->findOrFail($sessionId);

            $role = session('role');
            $userId = session('id');

            // Check permissions
            if ($role === 'teacher' && $session->teacher_id != $userId) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'You can only mark attendance for your own sessions.');
            }

            $validated = $request->validate([
                'attendance' => 'required|array',
                'attendance.*' => 'required|in:present,absent,late,excused',
                'participation_scores' => 'array',
                'participation_scores.*' => 'nullable|integer|min:0|max:10',
                'progress_notes' => 'array',
                'progress_notes.*' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            // Update session status if needed
            if ($session->status === 'scheduled') {
                $session->update([
                    'status' => 'ongoing',
                    'actual_start' => now()
                ]);
            }

            // Mark attendance for each trainee
            foreach ($validated['attendance'] as $traineeId => $status) {
                $enrollment = SessionEnrollment::where('session_id', $sessionId)
                    ->where('trainee_id', $traineeId)
                    ->first();

                if ($enrollment) {
                    $enrollment->update([
                        'attendance_status' => $status,
                        'checked_in_at' => $status === 'present' ? now() : null,
                        'participation_score' => $validated['participation_scores'][$traineeId] ?? null,
                        'progress_notes' => $validated['progress_notes'][$traineeId] ?? null
                    ]);
                }
            }

            $session->update(['attendance_marked' => true]);

            DB::commit();

            return redirect()->route('activities.sessions', $activityId)
                ->with('success', 'Attendance marked successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error marking attendance: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while marking attendance.');
        }
    }

    /**
     * Manage enrollments for a session
     */
    public function manageEnrollments($activityId, $sessionId)
    {
        try {
            $session = ActivitySession::with(['activity', 'enrollments.trainee'])
                ->where('activity_id', $activityId)
                ->findOrFail($sessionId);

            $role = session('role');

            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'You do not have permission to manage enrollments.');
            }

            // Get eligible trainees (not already enrolled)
            $enrolledTraineeIds = $session->enrollments->pluck('trainee_id');
            $eligibleTrainees = Trainees::whereNotIn('id', $enrolledTraineeIds)
                ->get();

            return view('activities.enrollments', compact('session', 'eligibleTrainees'));

        } catch (Exception $e) {
            Log::error('Error loading enrollments: ' . $e->getMessage());
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'Session not found.');
        }
    }

    /**
     * Enroll trainees in a session
     */
    public function enrollTrainees(Request $request, $activityId, $sessionId)
    {
        try {
            $session = ActivitySession::where('activity_id', $activityId)
                ->findOrFail($sessionId);

            $role = session('role');

            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'You do not have permission to manage enrollments.');
            }

            $validated = $request->validate([
                'trainee_ids' => 'required|array',
                'trainee_ids.*' => 'exists:trainees,id'
            ]);

            DB::beginTransaction();

            $enrolled = 0;
            foreach ($validated['trainee_ids'] as $traineeId) {
                // Check if already enrolled
                $exists = SessionEnrollment::where('session_id', $sessionId)
                    ->where('trainee_id', $traineeId)
                    ->exists();

                if (!$exists && $session->enrolled_count < $session->max_participants) {
                    SessionEnrollment::create([
                        'session_id' => $sessionId,
                        'trainee_id' => $traineeId,
                        'enrolled_at' => now(),
                        'enrolled_by' => session('id'),
                        'enrollment_status' => 'enrolled'
                    ]);

                    $session->increment('enrolled_count');
                    $enrolled++;
                }
            }

            DB::commit();

            return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                ->with('success', $enrolled . ' trainee(s) enrolled successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error enrolling trainees: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while enrolling trainees.');
        }
    }

    /**
     * Get activity categories
     */
    private function getActivityCategories()
    {
        return [
            'Physical Therapy',
            'Occupational Therapy',
            'Speech Therapy',
            'Behavioral Therapy',
            'Sensory Integration',
            'Mathematics',
            'Literacy',
            'Science',
            'Computer Skills',
            'Art & Creativity',
            'Music Therapy',
            'Social Skills',
            'Life Skills',
            'Vocational Training'
        ];
    }

    /**
     * Get activity statistics
     */
    private function getActivityStats($role, $userId)
    {
        return Cache::remember("activity_stats_{$role}_{$userId}", 300, function () use ($role, $userId) {
            $query = Activity::query();

            if ($role === 'teacher') {
                $query->whereHas('sessions', function ($q) use ($userId) {
                    $q->where('teacher_id', $userId);
                });
            }

            return [
                'total' => $query->count(),
                'active' => $query->where('is_active', true)->count(),
                'rehabilitation' => $query->whereIn('category', [
                    'Physical Therapy', 'Occupational Therapy', 
                    'Speech Therapy', 'Sensory Integration'
                ])->count(),
                'academic' => $query->whereIn('category', [
                    'Mathematics', 'Literacy', 'Science', 'Computer Skills'
                ])->count()
            ];
        });
    }

    /**
     * Calculate average attendance for an activity
     */
    private function calculateAverageAttendance($activity)
    {
        $completedSessions = $activity->completedSessions;
        
        if ($completedSessions->count() === 0) {
            return 0;
        }

        $totalAttendance = 0;
        $totalEnrollments = 0;

        foreach ($completedSessions as $session) {
            $presentCount = $session->enrollments->where('attendance_status', 'present')->count();
            $totalCount = $session->enrollments->count();
            
            if ($totalCount > 0) {
                $totalAttendance += $presentCount;
                $totalEnrollments += $totalCount;
            }
        }

        return $totalEnrollments > 0 ? round(($totalAttendance / $totalEnrollments) * 100, 2) : 0;
    }

    // Rehabilitation module methods
    public function createActivity()
    {
        return $this->create();
    }

    public function storeActivity(Request $request)
    {
        return $this->store($request);
    }

    public function showActivity($id)
    {
        return $this->show($id);
    }

    public function editActivity($id)
    {
        return $this->edit($id);
    }

    public function updateActivity(Request $request, $id)
    {
        return $this->update($request, $id);
    }

    public function destroyActivity($id)
    {
        return $this->destroy($id);
    }

    /**
     * API: Get activities
     */
    public function apiIndex(Request $request)
    {
        try {
            $query = Activity::with(['sessions', 'creator']);

            if ($request->has('category')) {
                $query->where('category', $request->category);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('activity_name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('activity_code', 'LIKE', "%{$search}%");
                });
            }

            $activities = $query->where('is_active', true)->get();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (Exception $e) {
            Log::error('API Error fetching activities: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch activities'
            ], 500);
        }
    }

    /**
     * API: Get activity categories
     */
    public function getCategories()
    {
        try {
            $categories = $this->getActivityCategories();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (Exception $e) {
            Log::error('API Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch categories'
            ], 500);
        }
    }

    /**
     * API: Filter activities
     */
    public function filterActivities(Request $request)
    {
        return $this->apiIndex($request);
    }

    // ========================================
    // NEW SCHEDULING & ENROLLMENT METHODS
    // ========================================

    /**
     * Display activity schedule management
     */
    public function schedule($id)
    {
        try {
            $activity = Activity::with(['schedules', 'activeEnrollments.trainee'])->findOrFail($id);
            
            // Check permissions
            $role = session('role');
            $userId = session('id');
            
            if (!$this->canManageActivity($activity, $role, $userId)) {
                return redirect()->route('activities.index')
                    ->with('error', 'You do not have permission to manage this activity schedule.');
            }

            return view('activities.schedule', compact('activity'));

        } catch (Exception $e) {
            Log::error('Error loading activity schedule: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Unable to load activity schedule.');
        }
    }

    /**
     * Display weekly schedule overview
     */
    public function weeklySchedule()
    {
        try {
            $schedules = ActivitySchedule::with(['activity.teacher', 'activity.centre'])
                ->active()
                ->forWeek()
                ->get()
                ->groupBy('day_of_week');

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            return view('activities.weekly-schedule', compact('schedules', 'days'));

        } catch (Exception $e) {
            Log::error('Error loading weekly schedule: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Unable to load weekly schedule.');
        }
    }

    /**
     * Display teacher's personal schedule
     */
    public function teacherSchedule($teacherId)
    {
        try {
            $teacher = Users::findOrFail($teacherId);
            
            // Check permissions - users can only view their own schedule unless admin/supervisor
            $role = session('role');
            $currentUserId = session('id');
            
            if (!in_array($role, ['admin', 'supervisor']) && $currentUserId != $teacherId) {
                return redirect()->route('activities.index')
                    ->with('error', 'You can only view your own schedule.');
            }

            // Get sessions for this teacher - using ActivitySession model to match existing view
            $sessions = \App\Models\ActivitySession::whereHas('activity', function($query) use ($teacherId) {
                    $query->where('created_by', $teacherId);
                })
                ->with(['activity', 'enrollments'])
                ->where('status', 'scheduled')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            // Group sessions by day of week for the existing view
            $groupedSessions = $sessions->groupBy('day_of_week');

            return view('activities.activitiesteacherschedule', compact('teacher', 'groupedSessions'));

        } catch (Exception $e) {
            Log::error('Error loading teacher schedule: ' . $e->getMessage());
            return redirect()->route('activities.index')
                ->with('error', 'Unable to load teacher schedule.');
        }
    }

    /**
     * Show enrollment form for an activity
     */
    public function enrollmentForm($id)
    {
        try {
            $activity = Activity::with(['activeEnrollments.trainee', 'schedules'])->findOrFail($id);
            
            // Get available trainees (not already enrolled in this activity)
            $enrolledTraineeIds = $activity->activeEnrollments->pluck('trainee_id');
            $availableTrainees = Trainees::whereNotIn('id', $enrolledTraineeIds)
                ->orderBy('trainee_first_name')
                ->get();

            return view('activities.enroll', compact('activity', 'availableTrainees'));

        } catch (Exception $e) {
            Log::error('Error loading enrollment form: ' . $e->getMessage());
            return redirect()->route('activities.show', $id)
                ->with('error', 'Unable to load enrollment form.');
        }
    }

    /**
     * Process trainee enrollments
     */
    public function enrollTrainees(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            
            $request->validate([
                'trainee_ids' => 'required|array|min:1',
                'trainee_ids.*' => 'exists:trainees,id',
                'enrollment_date' => 'required|date',
                'goals' => 'nullable|string|max:1000'
            ]);

            $enrolledCount = 0;
            $errors = [];

            foreach ($request->trainee_ids as $traineeId) {
                try {
                    // Check if already enrolled
                    $existingEnrollment = ActivityEnrollment::where('activity_id', $id)
                        ->where('trainee_id', $traineeId)
                        ->first();

                    if ($existingEnrollment) {
                        $trainee = Trainees::find($traineeId);
                        $errors[] = $trainee->full_name . ' is already enrolled in this activity.';
                        continue;
                    }

                    // Check capacity
                    $currentEnrollments = $activity->activeEnrollments()->count();
                    if ($currentEnrollments >= $activity->max_participants) {
                        $errors[] = 'Activity is at full capacity.';
                        break;
                    }

                    // Create enrollment
                    ActivityEnrollment::create([
                        'activity_id' => $id,
                        'trainee_id' => $traineeId,
                        'enrollment_date' => $request->enrollment_date,
                        'start_date' => $request->enrollment_date,
                        'status' => 'enrolled',
                        'goals' => $request->goals,
                        'enrolled_by' => session('id')
                    ]);

                    $enrolledCount++;

                } catch (Exception $e) {
                    Log::error('Error enrolling trainee: ' . $e->getMessage());
                    $errors[] = 'Error enrolling trainee ID: ' . $traineeId;
                }
            }

            $message = "{$enrolledCount} trainee(s) successfully enrolled.";
            if (!empty($errors)) {
                $message .= ' Errors: ' . implode(' ', $errors);
            }

            return redirect()->route('activities.show', $id)
                ->with($enrolledCount > 0 ? 'success' : 'error', $message);

        } catch (Exception $e) {
            Log::error('Error processing enrollments: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to process enrollments.')
                ->withInput();
        }
    }

    /**
     * Store a new activity schedule
     */
    public function storeSchedule(Request $request, $id)
    {
        try {
            $activity = Activity::findOrFail($id);
            
            $request->validate([
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'location' => 'nullable|string|max:255',
                'room' => 'nullable|string|max:255',
                'recurring' => 'required|in:weekly,biweekly,monthly,one_time',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'max_capacity' => 'nullable|integer|min:1'
            ]);

            ActivitySchedule::create([
                'activity_id' => $id,
                'day_of_week' => $request->day_of_week,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'location' => $request->location,
                'room' => $request->room,
                'recurring' => $request->recurring,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'max_capacity' => $request->max_capacity,
                'status' => 'active'
            ]);

            return redirect()->route('activities.schedule', $id)
                ->with('success', 'Schedule added successfully.');

        } catch (Exception $e) {
            Log::error('Error storing schedule: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to add schedule.')
                ->withInput();
        }
    }

    /**
     * Check if user can manage activity
     */
    private function canManageActivity($activity, $role, $userId)
    {
        if (in_array($role, ['admin', 'supervisor'])) {
            return true;
        }
        
        if ($role === 'teacher' && $activity->created_by == $userId) {
            return true;
        }
        
        return false;
    }

    /**
     * Get today's schedule for dashboard widget
     */
    public function getTodaysSchedule()
    {
        try {
            $today = Carbon::now()->format('l'); // Full day name
            
            $schedules = ActivitySchedule::with(['activity.teacher', 'activity.activeEnrollments'])
                ->where('day_of_week', $today)
                ->where('status', 'active')
                ->orderBy('start_time')
                ->get();

            return $schedules;

        } catch (Exception $e) {
            Log::error('Error getting today\'s schedule: ' . $e->getMessage());
            return collect([]);
        }
    }
}