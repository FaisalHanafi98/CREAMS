<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Category;
use App\Models\ActivitySession;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Users;
use App\Models\Trainee;
use App\Models\Centres;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesErrors;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class ActivityController extends Controller
{
    use HandlesErrors;
    /**
     * Display a listing of activities
     */
    public function index()
    {
        try {
            $role = session('role');
            $userId = session('id');

            Log::info('Loading activities index', ['user_id' => $userId, 'role' => $role]);

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
            
            // Get categories for the view
            $categories = $this->getActivityCategories();

            Log::info('Successfully loaded activities', [
                'user_id' => $userId,
                'role' => $role,
                'activities_count' => $activities->count(),
                'stats' => $stats
            ]);

            return view('activities.home', compact('activities', 'stats', 'role', 'categories'));

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
            $categories = Category::active()
                ->ordered()
                ->withCount('activities')
                ->get()
                ->groupBy('type');

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
    public function categoryShow($categorySlug)
    {
        try {
            $this->logUserAction('view_category_activities', ['category_slug' => $categorySlug]);
            
            // [ClaudeFix: 2025-07-07] Handle slug normalization and fallback to ENUM category search
            $category = Category::where('slug', $categorySlug)->first();
            
            if (!$category) {
                // Try to find by converting slug to title case for ENUM matching
                $categoryName = str_replace('-', ' ', $categorySlug);
                $categoryName = ucwords($categoryName);
                
                // Check if activities exist with this category directly (for ENUM-based categories)
                $activities = Activity::where('category', $categoryName)
                    ->where('is_active', true)
                    ->where('centre_id', session('centre_id'))
                    ->with(['sessions', 'creator'])
                    ->paginate(12);
                
                if ($activities->count() > 0) {
                    // Create a mock category object for display with fallback values
                    $category = (object) [
                        'id' => null,
                        'name' => $categoryName,
                        'slug' => $categorySlug,
                        'description' => "Activities in the {$categoryName} category",
                        'type' => 'rehabilitation',
                        'icon_class' => 'fas fa-tasks',
                        'color_code' => '#8B5CF6'
                    ];
                } else {
                    // Category not found at all
                    return redirect()->route('rehabilitation.categories')
                        ->with('error', 'Category not found.');
                }
            } else {
                $activities = Activity::where('category_id', $category->id)
                    ->where('is_active', true)
                    ->where('centre_id', session('centre_id'))
                    ->with(['sessions', 'creator', 'category'])
                    ->paginate(12);
            }

            return view('rehabilitation.category-show', compact('category', 'activities'));

        } catch (Exception $e) {
            return $this->handleException($e, 'loading category activities', [
                'category_slug' => $categorySlug
            ]);
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

        $categories = Category::active()->ordered()->get();
        
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
            'activity_id' => 'required|string|max:20|unique:activities',
            'activity_description' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'activity_type' => 'required|in:Individual,Group,Both,Education,Therapy,Training',
            'activity_date' => 'required|date|after_or_equal:today',
            'activity_start_time' => 'required|date_format:H:i',
            'activity_end_time' => 'required|date_format:H:i|after:activity_start_time',
            'activity_location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1|max:100',
            'activity_goals' => 'nullable|string',
            'activity_outcomes' => 'nullable|string',
            'required_resources' => 'nullable|string',
            'activity_image' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $activity = Activity::create([
                'activity_name' => $validated['activity_name'],
                'activity_id' => strtoupper($validated['activity_id']),
                'activity_description' => $validated['activity_description'],
                'category_id' => $validated['category_id'],
                'activity_type' => $validated['activity_type'],
                'activity_date' => $validated['activity_date'],
                'activity_start_time' => $validated['activity_start_time'],
                'activity_end_time' => $validated['activity_end_time'],
                'activity_location' => $validated['activity_location'],
                'max_participants' => $validated['max_participants'],
                'current_participants' => 0,
                'activity_goals' => $validated['activity_goals'],
                'activity_outcomes' => $validated['activity_outcomes'],
                'required_resources' => $validated['required_resources'],
                'activity_image' => $validated['activity_image'],
                'activity_status' => 'scheduled',
                'created_by' => session('id'),
                'centre_id' => session('centre_id'),
                'instructor_id' => session('id')
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
            Log::info('Loading activity details', ['activity_id' => $id, 'user_id' => session('id')]);

            $activity = Activity::with(['sessions.teacher', 'sessions.enrollments.trainee', 'creator', 'centre'])
                ->findOrFail($id);

            $role = session('role');
            $userId = session('id');

            // Check access for teachers
            if ($role === 'teacher') {
                $hasAccess = $activity->sessions->contains('teacher_id', $userId);
                if (!$hasAccess) {
                    Log::warning('Teacher tried to access unauthorized activity', [
                        'teacher_id' => $userId,
                        'activity_id' => $id
                    ]);
                    return redirect()->route('activities.index')
                        ->with('error', 'You do not have access to this activity.');
                }
            }

            // Get activity statistics
            $stats = [
                'totalSessions' => $activity->sessions->count(),
                'activeSessions' => $activity->sessions->where('status', 'active')->count(),
                'upcomingSessions' => $activity->upcomingSessions->count(),
                'completedSessions' => $activity->completedSessions->count(),
                'totalEnrollments' => SessionEnrollment::whereIn('session_id', $activity->sessions->pluck('id'))->count(),
                'averageAttendance' => $this->calculateAverageAttendance($activity)
            ];

            Log::info('Successfully loaded activity details', [
                'activity_id' => $id,
                'activity_name' => $activity->activity_name,
                'stats' => $stats
            ]);

            return view('activities.show', compact('activity', 'stats', 'role'));

        } catch (Exception $e) {
            Log::error('Error showing activity: ' . $e->getMessage(), [
                'activity_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found or access denied.');
        }
    }

    /**
     * Show the form for editing the activity
     */
    public function edit($id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            Log::warning('Unauthorized activity edit attempt', [
                'user_id' => session('id'),
                'role' => $role,
                'activity_id' => $id
            ]);
            return redirect()->route('activities.index')
                ->with('error', 'You do not have permission to edit activities.');
        }

        try {
            Log::info('Loading activity for edit', ['activity_id' => $id, 'user_id' => session('id')]);

            $activity = Activity::findOrFail($id);
            $categories = Category::active()->ordered()->get();
            
            Log::info('Successfully loaded activity for edit', [
                'activity_id' => $id,
                'activity_name' => $activity->activity_name
            ]);
            
            return view('activities.edit', compact('activity', 'categories', 'role'));

        } catch (Exception $e) {
            Log::error('Error loading activity for edit: ' . $e->getMessage(), [
                'activity_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('activities.index')
                ->with('error', 'Activity not found or access denied.');
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
                'activity_id' => 'required|string|max:20|unique:activities,activity_id,' . $id,
                'activity_description' => 'required|string',
                'category_id' => 'nullable|exists:categories,id',
                'activity_type' => 'required|in:Individual,Group,Both,Education,Therapy,Training',
                'activity_date' => 'required|date|after_or_equal:today',
                'activity_start_time' => 'required|date_format:H:i',
                'activity_end_time' => 'required|date_format:H:i|after:activity_start_time',
                'activity_location' => 'required|string|max:255',
                'max_participants' => 'required|integer|min:1|max:100',
                'activity_goals' => 'nullable|string',
                'activity_outcomes' => 'nullable|string',
                'required_resources' => 'nullable|string',
                'activity_image' => 'nullable|string'
            ]);

            $activity->update([
                'activity_name' => $validated['activity_name'],
                'activity_id' => strtoupper($validated['activity_id']),
                'activity_description' => $validated['activity_description'],
                'category_id' => $validated['category_id'],
                'activity_type' => $validated['activity_type'],
                'activity_date' => $validated['activity_date'],
                'activity_start_time' => $validated['activity_start_time'],
                'activity_end_time' => $validated['activity_end_time'],
                'activity_location' => $validated['activity_location'],
                'max_participants' => $validated['max_participants'],
                'activity_goals' => $validated['activity_goals'],
                'activity_outcomes' => $validated['activity_outcomes'],
                'required_resources' => $validated['required_resources'],
                'activity_image' => $validated['activity_image']
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

            $sessions = $sessions->orderBy('scheduled_date', 'desc')->orderBy('start_time', 'desc')->paginate(10);

            // Get available teachers for session creation
            $teachers = Users::where('role', 'teacher')
                ->where('status', 'active')
                ->where('centre_id', session('centre_id'))
                ->get(['id', 'name']);

            return view('activities.sessions', compact('activity', 'sessions', 'role', 'teachers'));

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
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:15|max:240',
                'location' => 'required|string|max:255',
                'max_capacity' => 'required|integer|min:1|max:50',
                'status' => 'required|in:scheduled,active,cancelled,completed',
                'room_number' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            // Calculate end time from duration
            $start = Carbon::parse($validated['start_time']);
            $end = $start->copy()->addMinutes($validated['duration']);

            $session = ActivitySession::create([
                'activity_id' => $activity->id,
                'teacher_id' => $validated['teacher_id'],
                'scheduled_date' => $validated['date'],
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $end->format('H:i:s'),
                'duration' => $validated['duration'],
                'duration_minutes' => $validated['duration'],
                'location' => $validated['location'],
                'venue' => $validated['location'],
                'room_number' => $validated['room_number'],
                'max_capacity' => $validated['max_capacity'],
                'max_participants' => $validated['max_capacity'],
                'enrolled_count' => 0,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status']
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

            // Get eligible trainees (not already enrolled) - Fix N+1 query
            $enrolledTraineeIds = $session->enrollments->pluck('trainee_id');
            $eligibleTrainees = Trainee::with(['centre'])
                ->whereNotIn('id', $enrolledTraineeIds)
                ->get();

            return view('activities.enrollments', compact('session', 'eligibleTrainees'));

        } catch (Exception $e) {
            Log::error('Error loading enrollments: ' . $e->getMessage());
            return redirect()->route('activities.sessions', $activityId)
                ->with('error', 'Session not found.');
        }
    }

    /**
     * Add enrollment to a session
     */
    public function addEnrollment(Request $request, $activityId, $sessionId)
    {
        try {
            $validated = $request->validate([
                'trainee_id' => 'required|exists:trainees,id'
            ]);

            $session = ActivitySession::findOrFail($sessionId);
            $role = session('role');

            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'You do not have permission to add enrollments.');
            }

            // Check if trainee is already enrolled
            $existingEnrollment = SessionEnrollment::where('session_id', $sessionId)
                ->where('trainee_id', $validated['trainee_id'])
                ->first();

            if ($existingEnrollment) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Trainee is already enrolled in this session.');
            }

            // Check session capacity
            $currentEnrollments = SessionEnrollment::where('session_id', $sessionId)->count();
            if ($currentEnrollments >= $session->max_participants) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Session is at maximum capacity.');
            }

            // Create enrollment
            SessionEnrollment::create([
                'session_id' => $sessionId,
                'trainee_id' => $validated['trainee_id'],
                'attendance_status' => 'pending'
            ]);

            // Update session enrolled count
            $session->enrolled_count = $currentEnrollments + 1;
            $session->save();

            Log::info('Trainee enrolled in session', [
                'session_id' => $sessionId,
                'trainee_id' => $validated['trainee_id'],
                'enrolled_by' => session('id')
            ]);

            return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                ->with('success', 'Trainee enrolled successfully.');

        } catch (Exception $e) {
            Log::error('Error adding enrollment: ' . $e->getMessage());
            return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                ->with('error', 'An error occurred while enrolling the trainee.');
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
                'total_activities' => $query->count(),
                'active_activities' => $query->where('is_active', true)->count(),
                'total' => $query->count(), // Backward compatibility
                'active' => $query->where('is_active', true)->count(), // Backward compatibility
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
        // Fix N+1 query: Eager load enrollments for all completed sessions
        $completedSessions = $activity->completedSessions()->with(['enrollments'])->get();
        
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
                      ->orWhere('activity_id', 'LIKE', "%{$search}%");
                });
            }

            $activities = $query->whereIn('activity_status', ['scheduled', 'ongoing'])->get();

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

    /**
     * API: Check for session conflicts
     */
    public function apiCheckConflicts(Request $request)
    {
        try {
            $validated = $request->validate([
                'teacher_id' => 'required|exists:users,id',
                'scheduled_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'venue' => 'nullable|string',
                'room_number' => 'nullable|string',
                'exclude_session_id' => 'nullable|integer'
            ]);

            $conflicts = $this->checkSessionConflicts(
                $validated['teacher_id'],
                $validated['scheduled_date'],
                $validated['start_time'],
                $validated['end_time'],
                $validated['venue'],
                $validated['room_number'],
                $validated['exclude_session_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'hasConflict' => $conflicts['hasConflict'],
                'conflicts' => $conflicts['conflicts'],
                'message' => $conflicts['message']
            ]);

        } catch (Exception $e) {
            Log::error('API Error checking conflicts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'hasConflict' => true,
                'message' => 'Unable to check for conflicts'
            ], 500);
        }
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
            $availableTrainees = Trainee::whereNotIn('id', $enrolledTraineeIds)
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
                        $trainee = Trainee::find($traineeId);
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
            
            $validated = $request->validate([
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'location' => 'nullable|string|max:255',
                'room' => 'nullable|string|max:255',
                'recurring' => 'required|in:weekly,biweekly,monthly,one_time',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'max_capacity' => 'nullable|integer|min:1',
                'teacher_id' => 'nullable|exists:users,id'
            ]);

            // Check for recurring schedule conflicts if teacher is specified
            if ($validated['teacher_id']) {
                $conflicts = $this->checkRecurringScheduleConflicts(
                    $validated['teacher_id'],
                    $validated['day_of_week'],
                    $validated['start_time'],
                    $validated['end_time'],
                    $validated['location'],
                    $validated['room']
                );

                if ($conflicts['hasConflict']) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', $conflicts['message']);
                }
            }

            ActivitySchedule::create([
                'activity_id' => $id,
                'day_of_week' => $validated['day_of_week'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'location' => $validated['location'],
                'room' => $validated['room'],
                'recurring' => $validated['recurring'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'max_capacity' => $validated['max_capacity'],
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

    /**
     * Check for session scheduling conflicts
     */
    private function checkSessionConflicts($teacherId, $scheduledDate, $startTime, $endTime, $venue = null, $roomNumber = null, $excludeSessionId = null)
    {
        try {
            $conflicts = [];
            $hasConflict = false;

            // Parse times for comparison
            $newStart = Carbon::parse($scheduledDate . ' ' . $startTime);
            $newEnd = Carbon::parse($scheduledDate . ' ' . $endTime);

            // Check teacher availability conflicts
            $teacherConflicts = ActivitySession::where('teacher_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            foreach ($teacherConflicts as $session) {
                $existingStart = Carbon::parse($session->scheduled_date . ' ' . $session->start_time);
                $existingEnd = Carbon::parse($session->scheduled_date . ' ' . $session->end_time);

                // Check for time overlap
                if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                    $hasConflict = true;
                    $conflicts[] = "Teacher conflict: Already scheduled for '{$session->activity->activity_name}' from {$session->start_time} to {$session->end_time}";
                }
            }

            // Check room/venue conflicts if specified
            if ($venue && $roomNumber) {
                $venueConflicts = ActivitySession::where('venue', $venue)
                    ->where('room_number', $roomNumber)
                    ->where('scheduled_date', $scheduledDate)
                    ->whereIn('status', ['scheduled', 'ongoing'])
                    ->when($excludeSessionId, function ($query, $excludeSessionId) {
                        return $query->where('id', '!=', $excludeSessionId);
                    })
                    ->with('activity')
                    ->get();

                foreach ($venueConflicts as $session) {
                    $existingStart = Carbon::parse($session->scheduled_date . ' ' . $session->start_time);
                    $existingEnd = Carbon::parse($session->scheduled_date . ' ' . $session->end_time);

                    if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                        $hasConflict = true;
                        $conflicts[] = "Room conflict: {$venue} - {$roomNumber} is already booked for '{$session->activity->activity_name}' from {$session->start_time} to {$session->end_time}";
                    }
                }
            }

            // Check for break time violations (minimum 15 minutes between sessions)
            $breakTimeConflicts = ActivitySession::where('teacher_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            foreach ($breakTimeConflicts as $session) {
                $existingStart = Carbon::parse($session->scheduled_date . ' ' . $session->start_time);
                $existingEnd = Carbon::parse($session->scheduled_date . ' ' . $session->end_time);

                // Check if sessions are too close (less than 15 minutes apart)
                $timeBetween = min(
                    abs($newStart->diffInMinutes($existingEnd)),
                    abs($existingStart->diffInMinutes($newEnd))
                );

                if ($timeBetween < 15 && $timeBetween > 0) {
                    $conflicts[] = "Insufficient break time: Only {$timeBetween} minutes between sessions. Minimum 15 minutes required.";
                }
            }

            // Check for daily workload limits (max 8 hours per day)
            $dailySessions = ActivitySession::where('teacher_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            $totalDailyMinutes = $dailySessions->sum('duration_minutes') + $newStart->diffInMinutes($newEnd);
            if ($totalDailyMinutes > 480) { // 8 hours = 480 minutes
                $totalHours = round($totalDailyMinutes / 60, 1);
                $conflicts[] = "Daily workload exceeded: {$totalHours} hours scheduled (maximum 8 hours per day)";
            }

            return [
                'hasConflict' => $hasConflict || !empty($conflicts),
                'conflicts' => $conflicts,
                'message' => $hasConflict || !empty($conflicts) 
                    ? 'Scheduling conflict detected: ' . implode(' | ', $conflicts)
                    : 'No conflicts found'
            ];

        } catch (Exception $e) {
            Log::error('Error checking session conflicts: ' . $e->getMessage());
            return [
                'hasConflict' => true,
                'conflicts' => ['System error checking conflicts'],
                'message' => 'Unable to verify scheduling conflicts. Please try again.'
            ];
        }
    }

    /**
     * Check if two time ranges overlap
     */
    private function timesOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Check for recurring schedule conflicts
     */
    private function checkRecurringScheduleConflicts($teacherId, $dayOfWeek, $startTime, $endTime, $location = null, $room = null, $excludeScheduleId = null)
    {
        try {
            $conflicts = [];
            $hasConflict = false;

            // Parse times for comparison
            $newStart = Carbon::parse($startTime);
            $newEnd = Carbon::parse($endTime);

            // Check teacher availability for this day of week
            $teacherSchedules = ActivitySchedule::whereHas('activity', function ($query) use ($teacherId) {
                    $query->where('created_by', $teacherId);
                })
                ->where('day_of_week', $dayOfWeek)
                ->where('status', 'active')
                ->when($excludeScheduleId, function ($query, $excludeScheduleId) {
                    return $query->where('id', '!=', $excludeScheduleId);
                })
                ->with('activity')
                ->get();

            foreach ($teacherSchedules as $schedule) {
                $existingStart = Carbon::parse($schedule->start_time);
                $existingEnd = Carbon::parse($schedule->end_time);

                // Check for time overlap
                if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                    $hasConflict = true;
                    $conflicts[] = "Teacher conflict: Already scheduled for '{$schedule->activity->activity_name}' on {$dayOfWeek}s from {$schedule->start_time} to {$schedule->end_time}";
                }
            }

            // Check room conflicts if specified
            if ($location && $room) {
                $roomSchedules = ActivitySchedule::where('location', $location)
                    ->where('room', $room)
                    ->where('day_of_week', $dayOfWeek)
                    ->where('status', 'active')
                    ->when($excludeScheduleId, function ($query, $excludeScheduleId) {
                        return $query->where('id', '!=', $excludeScheduleId);
                    })
                    ->with('activity')
                    ->get();

                foreach ($roomSchedules as $schedule) {
                    $existingStart = Carbon::parse($schedule->start_time);
                    $existingEnd = Carbon::parse($schedule->end_time);

                    if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                        $hasConflict = true;
                        $conflicts[] = "Room conflict: {$location} - {$room} is already booked for '{$schedule->activity->activity_name}' on {$dayOfWeek}s from {$schedule->start_time} to {$schedule->end_time}";
                    }
                }
            }

            // Check for insufficient break time between recurring sessions
            foreach ($teacherSchedules as $schedule) {
                $existingStart = Carbon::parse($schedule->start_time);
                $existingEnd = Carbon::parse($schedule->end_time);

                $timeBetween = min(
                    abs($newStart->diffInMinutes($existingEnd)),
                    abs($existingStart->diffInMinutes($newEnd))
                );

                if ($timeBetween < 15 && $timeBetween > 0) {
                    $conflicts[] = "Insufficient break time: Only {$timeBetween} minutes between recurring sessions on {$dayOfWeek}s. Minimum 15 minutes required.";
                }
            }

            return [
                'hasConflict' => $hasConflict || !empty($conflicts),
                'conflicts' => $conflicts,
                'message' => $hasConflict || !empty($conflicts) 
                    ? 'Recurring schedule conflict detected: ' . implode(' | ', $conflicts)
                    : 'No conflicts found'
            ];

        } catch (Exception $e) {
            Log::error('Error checking recurring schedule conflicts: ' . $e->getMessage());
            return [
                'hasConflict' => true,
                'conflicts' => ['System error checking conflicts'],
                'message' => 'Unable to verify recurring schedule conflicts. Please try again.'
            ];
        }
    }

    /**
     * Show the schedule index page with all sessions.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function scheduleIndex(Request $request)
    {
        try {
            $this->logUserAction('view_schedule_index', $request->all());
            
            $role = session('role');
            $userId = session('id');
            $userCentreId = session('centre_id');

            // Get filter parameters with defaults
            $dateFilter = $request->get('date', today()->format('Y-m-d'));
            $centreFilter = $request->get('centre');
            $dayFilter = $request->get('day');
            $categoryFilter = $request->get('category');
            $participantFilter = $request->get('participant');
            $teacherFilter = $request->get('teacher');

            // Base query for sessions with role-based access
            $query = ActivitySession::with(['activity.centre', 'activity.category', 'teacher', 'enrollments.trainee']);

            // Role-based filtering
            if ($role === 'admin') {
                // Admin can see all sessions across centres
                if ($centreFilter) {
                    $query->whereHas('activity', function($q) use ($centreFilter) {
                        $q->where('centre_id', $centreFilter);
                    });
                }
            } elseif ($role === 'supervisor') {
                // Supervisor can only see their centre
                $query->whereHas('activity', function($q) use ($userCentreId) {
                    $q->where('centre_id', $userCentreId);
                });
            } elseif ($role === 'teacher') {
                // Teacher can only see sessions they are assigned to
                $query->where('teacher_id', $userId);
            } else {
                // Other roles (parent/ajk) - limited access
                $query->whereHas('activity', function($q) use ($userCentreId) {
                    $q->where('centre_id', $userCentreId);
                });
            }

            // Default to today's sessions if no specific date filter
            if (!$dayFilter && !$request->has('show_all')) {
                $query->whereDate('scheduled_date', '>=', today());
            }

            // Apply filters
            if ($dayFilter) {
                $query->whereRaw('DAYNAME(scheduled_date) = ?', [ucfirst($dayFilter)]);
            }

            if ($categoryFilter) {
                $query->whereHas('activity', function($q) use ($categoryFilter) {
                    $q->where('category', $categoryFilter);
                });
            }

            if ($participantFilter) {
                $query->whereHas('enrollments.trainee', function($q) use ($participantFilter) {
                    $q->where('id', $participantFilter)
                      ->orWhere('trainee_id', 'LIKE', "%{$participantFilter}%");
                });
            }

            if ($teacherFilter && in_array($role, ['admin', 'supervisor'])) {
                $query->where('teacher_id', $teacherFilter);
            }

            // Get sessions with pagination
            $sessions = $query->orderBy('scheduled_date', 'asc')
                             ->orderBy('start_time', 'asc')
                             ->paginate(25);

            // Get filter options based on role
            if ($role === 'admin') {
                $centres = Centres::where('status', 'active')->get();
            } else {
                $centres = Centres::where('centre_id', $userCentreId)->where('status', 'active')->get();
            }

            // Get teachers for filter (admin/supervisor only)
            $teachers = [];
            if (in_array($role, ['admin', 'supervisor'])) {
                $teachersQuery = Users::where('role', 'teacher')->where('status', 'active');
                if ($role === 'supervisor') {
                    $teachersQuery->where('centre_id', $userCentreId);
                }
                $teachers = $teachersQuery->get(['id', 'name']);
            }

            return view('activities.schedule', compact('sessions', 'centres', 'teachers', 'role'));

        } catch (Exception $e) {
            return $this->handleException($e, 'loading schedule index', [
                'filters' => $request->all()
            ]);
        }
    }
}