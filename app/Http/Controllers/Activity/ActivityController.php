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
use App\Models\Attendance;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Centre;
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
                $query->whereIn('activity_status', ['scheduled', 'ongoing']);
            }

            $activities = $query->orderBy('created_at', 'desc')->paginate(12);

            // Get statistics
            $stats = $this->getActivityStats($role, $userId);
            
            // Get categories for the view
            $categories = $this->getActivityCategories();
            
            // Get data for new academic filters
            $trainees = Trainee::select('id', 'trainee_first_name', 'trainee_last_name')->get();
            $venues = Activity::distinct()->pluck('activity_location')->filter();

            Log::info('Successfully loaded activities', [
                'user_id' => $userId,
                'role' => $role,
                'activities_count' => $activities->count(),
                'stats' => $stats
            ]);

            return view('activities.home', compact('activities', 'stats', 'role', 'categories', 'trainees', 'venues'));

        } catch (Exception $e) {
            Log::error('Error loading activities index: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load activities. Please try again.');
        }
    }

    /**
     * Display modern activities homepage
     */
    public function modernHome()
    {
        try {
            $role = session('role');
            $userId = session('id');
            $centreId = session('centre_id');

            Log::info('Loading modern activities home', ['user_id' => $userId, 'role' => $role]);

            // Get activities with enhanced data for modern view
            $query = Activity::with(['sessions.teacher', 'enrollments.trainee', 'creator', 'centre']);

            // Role-based filtering
            if ($role === 'teacher') {
                $query->whereHas('sessions', function ($q) use ($userId) {
                    $q->where('teacher_id', $userId);
                });
            } elseif (in_array($role, ['supervisor', 'ajk'])) {
                $query->where('centre_id', $centreId);
            }

            $activities = $query->orderBy('created_at', 'desc')->get();

            // Transform activities for modern view
            $activitiesData = $activities->map(function($activity) {
                $latestSession = $activity->sessions->sortByDesc('scheduled_date')->first();
                $teacher = $latestSession ? $latestSession->teacher : $activity->creator;
                
                return [
                    'id' => $activity->id,
                    'name' => $activity->activity_name ?? $activity->name,
                    'description' => $activity->activity_description ?? $activity->description,
                    'status' => $this->getActivityStatus($activity),
                    'status_color' => $this->getStatusColor($this->getActivityStatus($activity)),
                    'status_text_color' => $this->getStatusTextColor($this->getActivityStatus($activity)),
                    'status_bg_color' => $this->getStatusBgColor($this->getActivityStatus($activity)),
                    'teacher_name' => $teacher ? $teacher->name : 'Not assigned',
                    'participant_count' => $activity->enrollments->count(),
                    'schedule_time' => $latestSession ? 
                        Carbon::parse($latestSession->start_time)->format('g:i A') . ' - ' . 
                        Carbon::parse($latestSession->end_time)->format('g:i A') : 'Time TBA',
                    'next_session_info' => $this->getNextSessionInfo($activity)
                ];
            });

            // Get activity statistics
            $activity_stats = [
                [
                    'title' => 'Total Activities',
                    'value' => $activities->count(),
                    'color_class' => 'text-gray-800',
                    'bg_class' => 'bg-blue-100',
                    'icon' => 'fas fa-clipboard-list',
                    'icon_color' => 'text-blue-600'
                ],
                [
                    'title' => 'Ongoing',
                    'value' => $activities->where('activity_status', 'ongoing')->count(),
                    'color_class' => 'text-green-600',
                    'bg_class' => 'bg-green-100',
                    'icon' => 'fas fa-play-circle',
                    'icon_color' => 'text-green-600'
                ],
                [
                    'title' => 'Completed Today',
                    'value' => $this->getTodayCompletedCount($activities),
                    'color_class' => 'text-purple-600',
                    'bg_class' => 'bg-purple-100',
                    'icon' => 'fas fa-check-circle',
                    'icon_color' => 'text-purple-600'
                ],
                [
                    'title' => 'Scheduled',
                    'value' => $activities->where('activity_status', 'scheduled')->count(),
                    'color_class' => 'text-orange-600',
                    'bg_class' => 'bg-orange-100',
                    'icon' => 'fas fa-calendar-alt',
                    'icon_color' => 'text-orange-600'
                ]
            ];

            // Get teachers and trainees for modal
            $teachers = [];
            $trainees = [];
            
            if (in_array($role, ['admin', 'supervisor', 'teacher'])) {
                $teachersQuery = User::where('role', 'teacher')->where('status', 'active');
                $traineesQuery = Trainee::where('status', 'active');
                
                if ($role !== 'admin') {
                    $teachersQuery->where('centre_id', $centreId);
                    $traineesQuery->where('centre_id', $centreId);
                }
                
                $teachers = $teachersQuery->get(['id', 'name']);
                $trainees = $traineesQuery->get(['id', 'trainee_first_name as first_name', 'trainee_last_name as last_name'])
                    ->map(function($trainee) {
                        return [
                            'id' => $trainee->id,
                            'name' => trim($trainee->first_name . ' ' . $trainee->last_name)
                        ];
                    });
            }

            // Get categories
            $categories = $this->getActivityCategories();

            // Calendar events (simplified for now)
            $calendar_events = [];

            Log::info('Successfully loaded modern activities home', [
                'user_id' => $userId,
                'role' => $role,
                'activities_count' => $activities->count()
            ]);

            // Pass the transformed activities data
            $activities = $activitiesData;
            
            // Individual stats for backward compatibility
            $total_activities = $activities->count();
            $ongoing_activities = $activities->where('status', 'ongoing')->count();
            $completed_today = $this->getTodayCompletedCount(collect($activities));
            $scheduled_activities = $activities->where('status', 'scheduled')->count();

            return view('activities.modernhome', compact(
                'activities', 
                'activity_stats', 
                'role', 
                'categories', 
                'teachers', 
                'trainees',
                'calendar_events',
                'total_activities',
                'ongoing_activities', 
                'completed_today',
                'scheduled_activities'
            ));

        } catch (Exception $e) {
            Log::error('Error loading modern activities home: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load activities. Please try again.');
        }
    }

    /**
     * Display activity categories using the proper hierarchical model
     */
    public function categories()
    {
        try {
            // Get all categories with their activity counts using the proper relationship
            $allCategories = Category::active()
                ->withCount(['activities as activities_count' => function($query) {
                    $query->whereIn('activity_status', ['scheduled', 'ongoing', 'completed']);
                }])
                ->ordered()
                ->get();

            // Group categories by their overarching type
            $categoriesGrouped = [
                'rehabilitation' => $allCategories->where('category_type', 'rehabilitation'),
                'academic' => $allCategories->where('category_type', 'academic'),
                'creative_social' => $allCategories->where('category_type', 'creative_social')
            ];

            // Transform categories to include proper data structure
            foreach ($categoriesGrouped as $type => $categories) {
                $categoriesGrouped[$type] = $categories->map(function($category) {
                    return (object)[
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'slug' => \Illuminate\Support\Str::slug($category->category_name),
                        'description' => $category->category_description ?? "Activities in the {$category->category_name} category",
                        'activities_count' => $category->activities_count,
                        'color_code' => $category->category_color,
                        'icon_class' => $category->category_icon ?: $this->getCategoryIcon($category->category_name),
                        'type' => $category->category_type,
                        'type_display' => $category->type_display
                    ];
                });
            }

            Log::info('Categories loaded successfully', [
                'rehabilitation_count' => $categoriesGrouped['rehabilitation']->count(),
                'academic_count' => $categoriesGrouped['academic']->count(),
                'creative_social_count' => $categoriesGrouped['creative_social']->count()
            ]);

            return view('rehabilitation.categories', ['categories' => $categoriesGrouped]);

        } catch (Exception $e) {
            Log::error('Error loading activity categories: ' . $e->getMessage());
            return redirect()->route('activities.home')
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
                $activities = Activity::where('activity_type', $categoryName)
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
                        'description' => "Activity in the {$categoryName} category",
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
                    ->with(['sessions', 'creator'])
                    ->paginate(12);
            }

            return view('rehabilitation.categoryshow', compact('category', 'activities'));

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
        
        // Admin-only restriction as per new requirements
        if ($role !== 'admin') {
            return redirect()->route('activities.home')
                ->with('error', 'Only administrators can create activities.');
        }

        // Get centres for the form
        $centres = Centre::active()->get();
        
        return view('activities.create', compact('centres'));
    }

    /**
     * Store a newly created activity
     */
    public function store(Request $request)
    {
        $role = session('role');
        
        // Admin-only restriction as per new requirements
        if ($role !== 'admin') {
            return redirect()->route('activities.home')
                ->with('error', 'Only administrators can create activities.');
        }

        $validated = $request->validate([
            // Basic Information
            'activity_name' => 'required|string|max:255',
            'activity_id' => 'required|string|max:20|unique:activities',
            'category' => 'required|string|max:100',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'description' => 'required|string',
            
            // Location & Centre
            'centre_id' => 'required|exists:centres,centre_id',
            'location' => 'required|string|max:255',
            
            // Instructor
            'instructor_id' => 'required|exists:users,id',
            
            // Participants
            'max_participants' => 'required|integer|min:1|max:50',
            'min_participants' => 'required|integer|min:1|max:50',
            'participants' => 'nullable|string', // Comma-separated trainee IDs
            
            // Schedule
            'sessions_per_week' => 'required|integer|min:1|max:5',
            'duration_hours' => 'required|numeric|min:0.5|max:3',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'activity_period' => 'required|integer|min:1|max:24', // Duration in months
            'schedule_days' => 'required|array|min:1',
            'schedule_days.*' => 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'
        ]);

        try {
            DB::beginTransaction();

            // Calculate end time based on duration
            $startTime = Carbon::parse($validated['start_time']);
            $endTime = $startTime->copy()->addHours($validated['duration_hours']);

            // Calculate end date based on start date + activity period (months)
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = $startDate->copy()->addMonths($validated['activity_period']);

            // Find category by name to get category_id
            $category = Category::where('category_name', $validated['category'])->first();
            $categoryId = $category ? $category->id : null;

            $activity = Activity::create([
                'activity_id' => strtoupper($validated['activity_id']),
                'activity_name' => $validated['activity_name'],
                'activity_description' => $validated['description'],
                'activity_type' => $validated['category'],
                'activity_date' => $validated['start_date'],
                'activity_start_time' => $validated['start_time'],
                'activity_end_time' => $endTime->format('H:i:s'),
                'activity_location' => $validated['location'],
                'max_participants' => $validated['max_participants'],
                'activity_status' => 'scheduled',
                'centre_id' => $validated['centre_id'],
                'category_id' => $categoryId,
                'created_by' => session('id'),
                'instructor_id' => $validated['instructor_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $endDate->format('Y-m-d'),
                'activity_period' => $validated['activity_period'],
                'sessions_per_week' => $validated['sessions_per_week'],
                'is_active' => true
            ]);

            // Create activity sessions based on schedule
            $this->createActivitySessions($activity, $validated);

            // Enroll selected participants if any
            if (!empty($validated['participants'])) {
                $participantIds = explode(',', $validated['participants']);
                $this->enrollParticipants($activity, $participantIds, $validated['start_date']);
            }

            DB::commit();

            Log::info('Activity created successfully', [
                'activity_id' => $activity->id,
                'activity_name' => $activity->name,
                'created_by' => session('id')
            ]);

            return redirect()->route('activities.show', $activity->id)
                ->with('success', 'Activity created successfully with scheduled sessions!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating activity: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['participants'])
            ]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the activity: ' . $e->getMessage());
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
                    return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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

            return redirect()->route('activities.home')
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
            $teachers = User::where('role', 'teacher')
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

            // Check if attendance already exists for today
            $attendanceExists = Attendance::where('activity_id', $activityId)
                ->whereDate('date', now()->toDateString())
                ->exists();

            return view('activities.attendance', compact('session', 'attendanceExists'));

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
                'notes' => 'array',
                'notes.*' => 'nullable|string|max:500',
                'attendance_date' => 'required|date'
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
                $enrollment = ActivityEnrollment::where('activity_id', $activityId)
                    ->where('trainee_id', $traineeId)
                    ->first();

                if ($enrollment) {
                    // Update enrollment record
                    $enrollment->update([
                        'attendance_marked' => true,
                        'progress_notes' => $validated['notes'][$traineeId] ?? null
                    ]);

                    // Create or update attendance record
                    Attendance::updateOrCreate([
                        'trainee_id' => $traineeId,
                        'activity_id' => $activityId,
                        'date' => $validated['attendance_date'],
                    ], [
                        'status' => $status,
                        'remarks' => $validated['notes'][$traineeId] ?? null,
                        'marked_by' => $userId,
                        'check_in_time' => $status === 'present' ? now() : null,
                        'activity_type' => 'session'
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
                'active_activities' => $query->whereIn('activity_status', ['scheduled', 'ongoing'])->count(),
                'total' => $query->count(), // Backward compatibility
                'active' => $query->whereIn('activity_status', ['scheduled', 'ongoing'])->count(), // Backward compatibility
                'rehabilitation' => $query->get()->filter(function($activity) {
                    return in_array($activity->category, ['Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 'Sensory Integration']);
                })->count(),
                'academic' => $query->get()->filter(function($activity) {
                    return in_array($activity->category, ['Mathematics', 'Literacy', 'Science', 'Computer Skills']);
                })->count()
            ];
        });
    }

    /**
     * Create activity sessions based on schedule
     */
    private function createActivitySessions($activity, $validated)
    {
        $startDate = Carbon::parse($validated['start_date']);
        $scheduleDays = $validated['schedule_days'];
        $sessionsPerWeek = $validated['sessions_per_week'];
        $duration = $validated['duration_hours'];
        $startTime = $validated['start_time'];
        
        // Create sessions for the next 12 weeks (3 months)
        $currentDate = $startDate->copy();
        $endDate = $startDate->copy()->addWeeks(12);
        $sessionCount = 0;
        
        while ($currentDate->lte($endDate) && $sessionCount < ($sessionsPerWeek * 12)) {
            $dayName = $currentDate->format('l'); // Full day name
            
            if (in_array($dayName, $scheduleDays)) {
                // Calculate end time
                $sessionStart = Carbon::parse($currentDate->format('Y-m-d') . ' ' . $startTime);
                $sessionEnd = $sessionStart->copy()->addHours($duration);
                
                ActivitySession::create([
                    'activity_id' => $activity->id,
                    'teacher_id' => $validated['instructor_id'],
                    'scheduled_date' => $currentDate->format('Y-m-d'),
                    'date' => $currentDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $sessionEnd->format('H:i:s'),
                    'duration' => $duration * 60, // Convert to minutes
                    'duration_minutes' => $duration * 60,
                    'location' => $validated['location'],
                    'venue' => $validated['location'],
                    'max_capacity' => $activity->max_participants,
                    'max_participants' => $activity->max_participants,
                    'enrolled_count' => 0,
                    'status' => 'scheduled'
                ]);
                
                $sessionCount++;
            }
            
            $currentDate->addDay();
        }
        
        Log::info('Created activity sessions', [
            'activity_id' => $activity->id,
            'session_count' => $sessionCount,
            'schedule_days' => $scheduleDays
        ]);
    }

    /**
     * Enroll participants in an activity
     */
    private function enrollParticipants($activity, $participantIds, $enrollmentDate)
    {
        $enrolledCount = 0;
        
        foreach ($participantIds as $traineeId) {
            if (empty($traineeId)) continue;
            
            try {
                // Check if trainee exists and is active
                $trainee = Trainee::where('id', $traineeId)
                    ->where('status', 'active')
                    ->first();
                    
                if (!$trainee) {
                    Log::warning('Trainee not found or inactive', ['trainee_id' => $traineeId]);
                    continue;
                }
                
                // Create activity enrollment
                ActivityEnrollment::create([
                    'activity_id' => $activity->id,
                    'trainee_id' => $traineeId,
                    'enrollment_date' => $enrollmentDate,
                    'start_date' => $enrollmentDate,
                    'status' => 'enrolled',
                    'enrolled_by' => session('id')
                ]);
                
                $enrolledCount++;
                
            } catch (Exception $e) {
                Log::error('Error enrolling participant', [
                    'trainee_id' => $traineeId,
                    'activity_id' => $activity->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update activity current participant count if this field exists in the model
        // Note: The current Activity model doesn't have current_participants field
        
        Log::info('Enrolled participants in activity', [
            'activity_id' => $activity->id,
            'enrolled_count' => $enrolledCount,
            'total_requested' => count($participantIds)
        ]);
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
                $query->where('activity_type', $request->category);
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
     * API: Get instructors for a specific centre
     */
    public function getInstructors($centreId)
    {
        try {
            $instructors = User::where('centre_id', $centreId)
                ->whereIn('role', ['supervisor', 'teacher'])
                ->where('status', 'active')
                ->select('id', 'name', 'role')
                ->orderBy('name')
                ->get();

            return response()->json($instructors);

        } catch (Exception $e) {
            Log::error('Error fetching instructors: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * API: Get trainees for a specific centre
     */
    public function getTrainees($centreId)
    {
        try {
            $trainees = Trainee::where('centre_id', $centreId)
                ->where('status', 'active')
                ->select('id', 'trainee_first_name as first_name', 'trainee_last_name as last_name', 'trainee_condition as condition')
                ->orderBy('trainee_first_name')
                ->get();

            // Format the response to include full name
            $trainees = $trainees->map(function ($trainee) {
                return [
                    'id' => $trainee->id,
                    'name' => trim($trainee->first_name . ' ' . $trainee->last_name),
                    'condition' => $trainee->condition
                ];
            });

            return response()->json($trainees);

        } catch (Exception $e) {
            Log::error('Error fetching trainees: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Get disability-appropriate activity recommendations
     * Maps trainee conditions to suitable activity categories
     */
    private function getConditionActivityMapping()
    {
        return [
            // Physical conditions work well with adaptive programs
            'Physical Disability' => [
                'Physical Therapy', 'Occupational Therapy', 'Art & Creativity', 
                'Computer Skills', 'Mathematics', 'Literacy', 'Music Therapy', 'Vocational Training'
            ],
            'Cerebral Palsy' => [
                'Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 
                'Computer Skills', 'Art & Creativity', 'Music Therapy', 'Mathematics', 'Literacy'
            ],
            
            // Cognitive/Learning conditions benefit from structured learning
            'Autism Spectrum Disorder' => [
                'Mathematics', 'Computer Skills', 'Art & Creativity', 'Music Therapy',
                'Sensory Integration', 'Behavioral Therapy', 'Life Skills', 'Science'
            ],
            'ADHD' => [
                'Physical Therapy', 'Behavioral Therapy', 'Art & Creativity', 
                'Music Therapy', 'Social Skills', 'Life Skills', 'Vocational Training'
            ],
            'Learning Disabilities' => [
                'Mathematics', 'Literacy', 'Computer Skills', 'Art & Creativity',
                'Occupational Therapy', 'Life Skills', 'Vocational Training'
            ],
            'Intellectual Disability' => [
                'Life Skills', 'Social Skills', 'Art & Creativity', 'Music Therapy',
                'Physical Therapy', 'Occupational Therapy', 'Vocational Training'
            ],
            'Down Syndrome' => [
                'Social Skills', 'Life Skills', 'Music Therapy', 'Art & Creativity',
                'Physical Therapy', 'Mathematics', 'Literacy'
            ],
            
            // Communication conditions need specialized support
            'Speech and Language Disorders' => [
                'Speech Therapy', 'Art & Creativity', 'Music Therapy', 'Computer Skills',
                'Social Skills', 'Mathematics', 'Literacy'
            ],
            'Hearing Impairment' => [
                'Art & Creativity', 'Computer Skills', 'Mathematics', 'Science',
                'Vocational Training', 'Life Skills', 'Physical Therapy'
            ],
            'Visual Impairment' => [
                'Music Therapy', 'Computer Skills', 'Mathematics', 'Literacy',
                'Life Skills', 'Vocational Training', 'Physical Therapy'
            ],
            
            // Multiple conditions need comprehensive support
            'Multiple Disabilities' => [
                'Music Therapy', 'Sensory Integration', 'Life Skills', 'Art & Creativity',
                'Physical Therapy', 'Occupational Therapy', 'Social Skills'
            ],
            
            // Sensory conditions benefit from specialized interventions
            'Sensory Processing Disorder' => [
                'Sensory Integration', 'Occupational Therapy', 'Art & Creativity',
                'Music Therapy', 'Physical Therapy', 'Behavioral Therapy'
            ]
        ];
    }

    /**
     * API: Get filtered trainees based on activity category appropriateness
     */
    public function getFilteredTrainees($centreId, $categoryId = null)
    {
        try {
            $query = Trainee::where('centre_id', $centreId)
                ->where('status', 'active')
                ->select('id', 'trainee_first_name as first_name', 'trainee_last_name as last_name', 'trainee_condition as condition')
                ->orderBy('trainee_first_name');

            $trainees = $query->get();
            
            // If category is provided, filter trainees by condition appropriateness
            if ($categoryId) {
                $category = Category::find($categoryId);
                if ($category) {
                    $conditionMapping = $this->getConditionActivityMapping();
                    $categoryName = $category->category_name;
                    
                    $trainees = $trainees->filter(function ($trainee) use ($conditionMapping, $categoryName) {
                        $condition = $trainee->condition;
                        return isset($conditionMapping[$condition]) && 
                               in_array($categoryName, $conditionMapping[$condition]);
                    });
                }
            }

            // Format the response to include full name and appropriateness indicator
            $trainees = $trainees->map(function ($trainee) {
                return [
                    'id' => $trainee->id,
                    'name' => trim($trainee->first_name . ' ' . $trainee->last_name),
                    'condition' => $trainee->condition
                ];
            });

            return response()->json($trainees->values()); // values() resets array keys

        } catch (Exception $e) {
            Log::error('Error fetching filtered trainees: ' . $e->getMessage());
            return response()->json([], 500);
        }
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
                return redirect()->route('activities.home')
                    ->with('error', 'You do not have permission to manage this activity schedule.');
            }

            return view('activities.schedule', compact('activity'));

        } catch (Exception $e) {
            Log::error('Error loading activity schedule: ' . $e->getMessage());
            return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
                ->with('error', 'Unable to load weekly schedule.');
        }
    }

    /**
     * Display teacher's personal schedule
     */
    public function teacherSchedule($teacherId)
    {
        try {
            $teacher = User::findOrFail($teacherId);
            
            // Check permissions - users can only view their own schedule unless admin/supervisor
            $role = session('role');
            $currentUserId = session('id');
            
            if (!in_array($role, ['admin', 'supervisor']) && $currentUserId != $teacherId) {
                return redirect()->route('activities.home')
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
            return redirect()->route('activities.home')
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
            $query = ActivitySession::with(['activity.centre', 'teacher', 'enrollments.trainee']);

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
                    $q->where('activity_type', $categoryFilter);
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
                $centres = Centre::active()->get();
            } else {
                $centres = Centre::where('centre_id', $userCentreId)->active()->get();
            }

            // Get teachers for filter (admin/supervisor only)
            $teachers = [];
            if (in_array($role, ['admin', 'supervisor'])) {
                $teachersQuery = User::where('role', 'teacher')->where('status', 'active');
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

    private function getCategoryColor($category)
    {
        $colors = [
            'Physical Therapy' => '#e74c3c',
            'Occupational Therapy' => '#3498db',
            'Speech Therapy' => '#2ecc71',
            'Sensory Integration' => '#f39c12',
            'Mathematics' => '#9b59b6',
            'Literacy' => '#1abc9c',
            'Science' => '#34495e',
            'Computer Skills' => '#16a085'
        ];
        return $colors[$category] ?? '#7f8c8d';
    }

    private function getCategoryIcon($category)
    {
        $icons = [
            'Physical Therapy' => 'fas fa-dumbbell',
            'Occupational Therapy' => 'fas fa-hands',
            'Speech Therapy' => 'fas fa-comments',
            'Sensory Integration' => 'fas fa-brain',
            'Mathematics' => 'fas fa-calculator',
            'Literacy' => 'fas fa-book',
            'Science' => 'fas fa-flask',
            'Computer Skills' => 'fas fa-laptop'
        ];
        return $icons[$category] ?? 'fas fa-tasks';
    }

    // Helper methods for modern home view
    private function getActivityStatus($activity)
    {
        // Check if activity has ongoing sessions today
        $ongoingSessions = $activity->sessions()
            ->where('status', 'ongoing')
            ->whereDate('scheduled_date', today())
            ->count();
            
        if ($ongoingSessions > 0) {
            return 'ongoing';
        }
        
        // Check if activity has upcoming sessions
        $upcomingSessions = $activity->sessions()
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', today())
            ->count();
            
        if ($upcomingSessions > 0) {
            return 'scheduled';
        }
        
        // Check activity status field if it exists
        if (isset($activity->activity_status)) {
            return $activity->activity_status;
        }
        
        return 'completed';
    }
    
    private function getStatusColor($status)
    {
        $colors = [
            'ongoing' => 'gradient-bg',
            'scheduled' => 'bg-orange-500',
            'completed' => 'bg-blue-500',
            'cancelled' => 'bg-red-500'
        ];
        return $colors[$status] ?? 'bg-gray-500';
    }
    
    private function getStatusTextColor($status)
    {
        $colors = [
            'ongoing' => 'text-green-600',
            'scheduled' => 'text-orange-600',  
            'completed' => 'text-blue-600',
            'cancelled' => 'text-red-600'
        ];
        return $colors[$status] ?? 'text-gray-600';
    }
    
    private function getStatusBgColor($status)
    {
        $colors = [
            'ongoing' => 'bg-green-100',
            'scheduled' => 'bg-orange-100',
            'completed' => 'bg-blue-100', 
            'cancelled' => 'bg-red-100'
        ];
        return $colors[$status] ?? 'bg-gray-100';
    }
    
    private function getNextSessionInfo($activity)
    {
        $nextSession = $activity->sessions()
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', today())
            ->orderBy('scheduled_date')
            ->orderBy('start_time')
            ->first();
            
        if (!$nextSession) {
            return null;
        }
        
        $sessionDate = Carbon::parse($nextSession->scheduled_date);
        $now = Carbon::now();
        
        if ($sessionDate->isToday()) {
            $sessionTime = Carbon::parse($nextSession->start_time);
            $minutesUntil = $now->diffInMinutes($sessionTime);
            
            if ($minutesUntil < 60) {
                return "Starts in {$minutesUntil} minutes";
            } else {
                return "Starts at " . $sessionTime->format('g:i A');
            }
        } elseif ($sessionDate->isTomorrow()) {
            return "Next session tomorrow at " . Carbon::parse($nextSession->start_time)->format('g:i A');
        } else {
            return "Next session " . $sessionDate->format('M j') . " at " . Carbon::parse($nextSession->start_time)->format('g:i A');
        }
    }
    
    private function getTodayCompletedCount($activities)
    {
        return $activities->filter(function($activity) {
            return $activity->sessions()
                ->where('status', 'completed')
                ->whereDate('scheduled_date', today())
                ->count() > 0;
        })->count();
    }
    
    /**
     * Check for scheduling conflicts during activity creation
     */
    public function checkScheduleConflicts(Request $request)
    {
        try {
            $validated = $request->validate([
                'instructor_id' => 'required|exists:users,id',
                'schedule_days' => 'required|array',
                'start_time' => 'required|date_format:H:i',
                'duration_hours' => 'required|numeric|min:0.5|max:8',
                'start_date' => 'required|date|after_or_equal:today',
                'location' => 'nullable|string',
                'participants' => 'nullable|array',
                'participants.*' => 'exists:trainees,trainee_id'
            ]);

            $conflicts = [];
            $hasConflicts = false;

            // Calculate end time
            $startTime = Carbon::parse($validated['start_time']);
            $endTime = $startTime->copy()->addHours($validated['duration_hours']);

            // Get start date for the activity
            $startDate = Carbon::parse($validated['start_date']);

            // Check conflicts for each scheduled day
            foreach ($validated['schedule_days'] as $dayOfWeek) {
                // Find the first occurrence of this day from start date
                $nextOccurrence = $startDate->copy();
                while ($nextOccurrence->format('l') !== $dayOfWeek) {
                    $nextOccurrence->addDay();
                }

                // Check instructor conflicts using ActivitySessions
                $instructorConflicts = ActivitySession::whereHas('activity', function ($query) use ($validated) {
                        $query->where('instructor_id', $validated['instructor_id'])
                              ->where('activity_status', '!=', 'cancelled');
                    })
                    ->where('day_of_week', $dayOfWeek)
                    ->whereIn('status', ['scheduled', 'ongoing'])
                    ->with('activity')
                    ->get();

                foreach ($instructorConflicts as $session) {
                    $existingStart = Carbon::parse($session->start_time);
                    $existingEnd = Carbon::parse($session->end_time);
                    
                    if ($this->timesOverlap($startTime, $endTime, $existingStart, $existingEnd)) {
                        $hasConflicts = true;
                        $conflicts[] = [
                            'type' => 'instructor',
                            'day' => $dayOfWeek,
                            'message' => "Instructor conflict on {$dayOfWeek}: Already scheduled for '{$session->activity->activity_name}' from {$existingStart->format('g:i A')} to {$existingEnd->format('g:i A')}"
                        ];
                    }
                }

                // Check location conflicts if location is specified
                if (!empty($validated['location'])) {
                    $locationConflicts = ActivitySession::whereHas('activity', function ($query) use ($validated) {
                            $query->where('activity_location', $validated['location'])
                                  ->where('activity_status', '!=', 'cancelled');
                        })
                        ->where('day_of_week', $dayOfWeek)
                        ->whereIn('status', ['scheduled', 'ongoing'])
                        ->with('activity')
                        ->get();

                    foreach ($locationConflicts as $session) {
                        $existingStart = Carbon::parse($session->start_time);
                        $existingEnd = Carbon::parse($session->end_time);
                        
                        if ($this->timesOverlap($startTime, $endTime, $existingStart, $existingEnd)) {
                            $hasConflicts = true;
                            $conflicts[] = [
                                'type' => 'location',
                                'day' => $dayOfWeek,
                                'message' => "Location conflict on {$dayOfWeek}: {$validated['location']} is already booked for '{$session->activity->activity_name}' from {$existingStart->format('g:i A')} to {$existingEnd->format('g:i A')}"
                            ];
                        }
                    }
                }

                // Check participant conflicts if participants are specified
                if (!empty($validated['participants'])) {
                    foreach ($validated['participants'] as $traineeId) {
                        $participantConflicts = SessionEnrollment::whereHas('session', function ($query) use ($dayOfWeek) {
                                $query->where('day_of_week', $dayOfWeek)
                                      ->whereIn('status', ['scheduled', 'ongoing']);
                            })
                            ->whereHas('session.activity', function ($query) {
                                $query->where('activity_status', '!=', 'cancelled');
                            })
                            ->where('trainee_id', $traineeId)
                            ->with(['session.activity'])
                            ->get();

                        foreach ($participantConflicts as $enrollment) {
                            $session = $enrollment->session;
                            $existingStart = Carbon::parse($session->start_time);
                            $existingEnd = Carbon::parse($session->end_time);
                            
                            if ($this->timesOverlap($startTime, $endTime, $existingStart, $existingEnd)) {
                                $hasConflicts = true;
                                $trainee = Trainee::find($traineeId);
                                $traineeName = $trainee ? $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name : "Trainee #{$traineeId}";
                                
                                $conflicts[] = [
                                    'type' => 'participant',
                                    'day' => $dayOfWeek,
                                    'trainee_id' => $traineeId,
                                    'message' => "Participant conflict on {$dayOfWeek}: {$traineeName} is already enrolled in '{$session->activity->activity_name}' from {$existingStart->format('g:i A')} to {$existingEnd->format('g:i A')}"
                                ];
                            }
                        }
                    }
                }
            }

            return response()->json([
                'hasConflicts' => $hasConflicts,
                'conflicts' => $conflicts,
                'summary' => $hasConflicts ? count($conflicts) . ' conflict(s) detected' : 'No conflicts detected'
            ]);

        } catch (Exception $e) {
            Log::error('Error checking schedule conflicts: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to check conflicts. Please try again.',
                'hasConflicts' => false,
                'conflicts' => []
            ], 500);
        }
    }

}