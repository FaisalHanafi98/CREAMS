<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\Category;
use App\Models\ActivitySession;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
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
use App\Rules\InstructorQualificationRule;
use App\Rules\TraineeCompatibilityRule;
use App\Rules\ActivityTimeBufferRule;
use App\Rules\MinimumEnrollmentRule;
use App\Helpers\MalaysiaHolidays;

class ActivityController extends Controller
{
    use HandlesErrors;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('enhanced.role:admin,supervisor,teacher')->except(['index', 'show']);
        $this->middleware('enhanced.role:admin,supervisor')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Check for activity overlaps and conflicts
     */
    private function checkActivityConflicts($activityData, $sessionData = null, $excludeActivityId = null)
    {
        $conflicts = [];

        try {
            // Check for instructor conflicts
            if (isset($sessionData) && isset($sessionData['teacher_id'])) {
                $instructorConflicts = $this->checkInstructorAvailability(
                    $sessionData['teacher_id'],
                    $sessionData['session_date'] ?? null,
                    $sessionData['start_time'] ?? null,
                    $sessionData['end_time'] ?? null,
                    $excludeActivityId
                );

                if (!empty($instructorConflicts)) {
                    $conflicts['instructor'] = $instructorConflicts;
                }
            }

            // Check for venue conflicts
            if (isset($activityData['activity_location'])) {
                $venueConflicts = $this->checkVenueAvailability(
                    $activityData['activity_location'],
                    $sessionData['session_date'] ?? null,
                    $sessionData['start_time'] ?? null,
                    $sessionData['end_time'] ?? null,
                    $excludeActivityId
                );

                if (!empty($venueConflicts)) {
                    $conflicts['venue'] = $venueConflicts;
                }
            }

            // Check for participant overlaps
            if (isset($sessionData['participants'])) {
                $participantConflicts = $this->checkParticipantAvailability(
                    $sessionData['participants'],
                    $sessionData['session_date'] ?? null,
                    $sessionData['start_time'] ?? null,
                    $sessionData['end_time'] ?? null,
                    $excludeActivityId
                );

                if (!empty($participantConflicts)) {
                    $conflicts['participants'] = $participantConflicts;
                }
            }
        } catch (Exception $e) {
            Log::error('Error checking activity conflicts: ' . $e->getMessage());
            $conflicts['system'] = ['message' => 'Unable to verify conflicts. Please check manually.'];
        }

        return $conflicts;
    }

    /**
     * Check instructor availability for given time slot
     */
    private function checkInstructorAvailability($teacherId, $date, $startTime, $endTime, $excludeActivityId = null)
    {
        if (!$teacherId || !$date || !$startTime || !$endTime) {
            return [];
        }

        $conflicts = ActivitySession::where('instructor_id', $teacherId)
            ->where('session_date', $date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            });

        if ($excludeActivityId) {
            $conflicts->where('activity_id', '!=', $excludeActivityId);
        }

        return $conflicts->with('activity')->get()->map(function ($session) {
            return [
                'activity' => $session->activity->activity_name,
                'time' => $session->start_time . ' - ' . $session->end_time,
                'date' => $session->session_date->format('Y-m-d')
            ];
        })->toArray();
    }

    /**
     * Check venue availability for given time slot
     */
    private function checkVenueAvailability($venue, $date, $startTime, $endTime, $excludeActivityId = null)
    {
        if (!$venue || !$date || !$startTime || !$endTime) {
            return [];
        }

        $conflicts = Activity::where('activity_location', $venue)
            ->whereHas('sessions', function ($query) use ($date, $startTime, $endTime) {
                $query->where('session_date', $date)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function ($subQ) use ($startTime, $endTime) {
                                $subQ->where('start_time', '<=', $startTime)
                                    ->where('end_time', '>=', $endTime);
                            });
                    });
            });

        if ($excludeActivityId) {
            $conflicts->where('id', '!=', $excludeActivityId);
        }

        return $conflicts->with('sessions')->get()->map(function ($activity) {
            return [
                'activity' => $activity->activity_name,
                'sessions' => $activity->sessions->map(function ($session) {
                    return $session->start_time . ' - ' . $session->end_time;
                })->toArray()
            ];
        })->toArray();
    }

    /**
     * Check participant availability for given time slot
     */
    private function checkParticipantAvailability($participants, $date, $startTime, $endTime, $excludeActivityId = null)
    {
        if (empty($participants) || !$date || !$startTime || !$endTime) {
            return [];
        }

        $conflicts = [];

        $timeOverlapFilter = function($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime])
              ->orWhere(function($sub) use ($startTime, $endTime) {
                  $sub->where('start_time', '<=', $startTime)->where('end_time', '>=', $endTime);
              });
        };

        foreach ($participants as $participantId) {
            $participantConflicts = ActivityEnrollment::where('trainee_id', $participantId)
                ->where('enrollment_status', 'enrolled')
                ->when($excludeActivityId, fn($q) => $q->where('activity_id', '!=', $excludeActivityId))
                ->whereHas('activity.sessions', function($query) use ($date, $timeOverlapFilter) {
                    $query->where('session_date', $date)->where($timeOverlapFilter);
                })
                ->with(['trainee', 'activity' => function($q) use ($date, $timeOverlapFilter) {
                    $q->with(['sessions' => function($sq) use ($date, $timeOverlapFilter) {
                        $sq->where('session_date', $date)->where($timeOverlapFilter);
                    }]);
                }])
                ->get();

            // Check for daily session limit (5 sessions per trainee per day)
            $dailySessionCount = ActivityEnrollment::where('trainee_id', $participantId)
                ->where('enrollment_status', 'enrolled')
                ->when($excludeActivityId, fn($q) => $q->where('activity_id', '!=', $excludeActivityId))
                ->whereHas('activity.sessions', function($query) use ($date) {
                    $query->where('session_date', $date)
                          ->whereIn('session_status', ['scheduled', 'ongoing']);
                })
                ->count();

            if ($dailySessionCount >= 5) {
                $trainee = Trainee::find($participantId);
                $traineeName = $trainee ? $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name : 'Unknown';
                $conflicts[] = [
                    'trainee' => $traineeName,
                    'conflicts' => [["Daily session limit exceeded: {$dailySessionCount} sessions already scheduled (maximum 5 sessions per trainee per day)"]]
                ];
            } elseif ($participantConflicts->isNotEmpty()) {
                $trainee = Trainee::find($participantId);
                $conflicts[] = [
                    'trainee' => $trainee ? $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name : 'Unknown',
                    'conflicts' => $participantConflicts->map(function($enrollment) {
                        $session = $enrollment->activity->sessions->first();
                        return [
                            'activity' => $enrollment->activity->activity_name,
                            'time'     => $session ? $session->start_time . ' - ' . $session->end_time : 'N/A',
                        ];
                    })->toArray()
                ];
            }
        }

        return $conflicts;
    }
    /**
     * Display a listing of activities
     */
    public function index()
    {
        try {
            $role = session('role');
            $userId = session('id');
            $userCentreId = session('centre_id');

            Log::info('Loading activities index', ['user_id' => $userId, 'role' => $role]);

            // Build the base query with necessary relationships
            $query = Activity::with(['sessions' => function ($q) {
                $q->with(['enrollments', 'teacher'])
                    ->orderBy('session_date', 'asc')
                    ->orderBy('start_time', 'asc');
            }, 'centre', 'instructor'])
                ->withCount(['sessions', 'enrollments']);

            // Role-based filtering
            if ($role === 'teacher') {
                $query->whereHas('sessions', function ($q) use ($userId) {
                    $q->where('instructor_id', $userId);
                });
            } elseif ($role === 'ajk') {
                // AJK can only view active activities from their centre
                $query->where('is_active', true)
                    ->where('centre_id', $userCentreId);
            }

            // Get activities with pagination - 9 per page for 3x3 grid
            $activities = $query->orderBy('created_at', 'desc')->paginate(9);

            // Calculate enhanced statistics using direct DB queries
            $stats = [
                'total_activities' => Activity::count(),
                'active_activities' => Activity::where('is_active', true)->count(),
                'total_sessions' => DB::table('activity_occurrences')->count(),
                'total_enrollments' => DB::table('activity_enrollments')->count(),
                'upcoming_sessions' => DB::table('activity_occurrences')->where('session_date', '>=', Carbon::now())->count(),
                'completed_sessions' => DB::table('activity_occurrences')->where('session_date', '<', Carbon::now())->count(),
                'total_trainees' => \App\Models\Trainee::count(),
                'active_trainees' => DB::table('activity_enrollments')->distinct()->count('trainee_id')
            ];

            // Get categories from enum with counts (category is now enum, not relation)
            $categories = Activity::select('category', DB::raw('count(*) as activities_count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->get()
                ->map(function($item) {
                    return (object)[
                        'category_name' => $item->category,
                        'activities_count' => $item->activities_count
                    ];
                });

            // Calculate category-based statistics (category is now a column, not relation)
            $categoryCounts = [
                'total' => $stats['total_activities'],
                'active' => $stats['active_activities'],
                'autism' => Activity::where('category', 'Autism Spectrum Support')->count(),
                'hearing' => Activity::where('category', 'Hearing Impairment')->count(),
                'visual' => Activity::where('category', 'Visual Impairment')->count(),
                'physical' => Activity::where('category', 'Physical Disabilities')->count(),
                'learning' => Activity::where('category', 'Learning Support')->count(),
                'speech' => Activity::where('category', 'Speech Therapy')->count()
            ];

            // Get additional data for filters and modals
            $trainees = \App\Models\Trainee::select('trainee_id', 'trainee_first_name', 'trainee_last_name')
                ->orderBy('trainee_first_name')
                ->get();

            $venues = \App\Models\Centre::select('centre_id', 'centre_name', 'centre_address')
                ->where('is_active', true)
                ->orderBy('centre_name')
                ->get();

            // Prepare activities for JavaScript (avoiding Carbon serialization issues)
            $activitiesForJs = $activities->getCollection()->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'activity_name' => $activity->activity_name,
                    'activity_description' => $activity->activity_description,
                    'activity_type' => $activity->activity_type,
                    'is_active' => $activity->is_active,
                    'sessions_count' => $activity->sessions_count,
                    'enrollments_count' => $activity->enrollments_count,
                    'category_name' => $activity->category ?? 'Uncategorized',
                    'centre_name' => $activity->centre->centre_name ?? 'Unknown Centre'
                ];
            });

            return view('activities.home', compact(
                'activities',
                'activitiesForJs',
                'stats',
                'role',
                'categories',
                'trainees',
                'venues',
                'categoryCounts'
            ));
        } catch (Exception $e) {
            Log::error('Error loading activities index: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

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
                    $q->where('instructor_id', $userId);
                });
            } elseif (in_array($role, ['supervisor', 'ajk'])) {
                $query->where('centre_id', $centreId);
            }

            $activities = $query->orderBy('created_at', 'desc')->get();

            // Transform activities for modern view
            $activitiesData = $activities->map(function ($activity) {
                $latestSession = $activity->sessions->sortByDesc('session_date')->first();
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
                    'value' => $activities->where('is_active', true)->count(),
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
                    ->map(function ($trainee) {
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
            $centreId = session('centre_id');
            $baseQuery = Activity::where('is_active', true);
            if ($centreId) {
                $baseQuery->where('centre_id', $centreId);
            }

            $categoryCounts = $baseQuery->clone()
                ->select('category', DB::raw('COUNT(*) as activities_count'))
                ->whereNotNull('category')
                ->groupBy('category')
                ->pluck('activities_count', 'category');

            $categoryMeta = [
                'Autism Spectrum Support' => ['type' => 'rehabilitation', 'color_code' => '#8B5CF6'],
                'Hearing Impairment' => ['type' => 'rehabilitation', 'color_code' => '#2563EB'],
                'Visual Impairment' => ['type' => 'rehabilitation', 'color_code' => '#059669'],
                'Physical Disabilities' => ['type' => 'rehabilitation', 'color_code' => '#DC2626'],
                'Learning Support' => ['type' => 'academic', 'color_code' => '#D97706'],
                'Speech Therapy' => ['type' => 'academic', 'color_code' => '#0EA5E9'],
            ];

            $categoriesGrouped = [
                'faith' => collect(),
                'rehabilitation' => collect(),
                'academic' => collect(),
                'creative_social' => collect(),
            ];

            foreach ($categoryCounts as $name => $count) {
                $meta = $categoryMeta[$name] ?? ['type' => 'rehabilitation', 'color_code' => '#8B5CF6'];
                $type = $meta['type'];
                $categoriesGrouped[$type]->push((object) [
                    'id' => null,
                    'name' => $name,
                    'slug' => \Illuminate\Support\Str::slug($name),
                    'description' => "Activities in the {$name} category",
                    'activities_count' => (int) $count,
                    'color_code' => $meta['color_code'],
                    'icon_class' => $this->getCategoryIcon($name),
                    'type' => $type,
                    'type_display' => ucwords(str_replace('_', ' ', $type)),
                ]);
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

            $categoryName = str_replace('-', ' ', $categorySlug);
            $categoryName = ucwords($categoryName);

            $activitiesQuery = Activity::where('category', $categoryName)
                ->where('is_active', true)
                ->with(['sessions', 'creator']);

            $centreId = session('centre_id');
            if ($centreId) {
                $activitiesQuery->where('centre_id', $centreId);
            }

            $activities = $activitiesQuery->paginate(9);
            if ($activities->total() === 0) {
                return redirect()->route('activities.categories')
                    ->with('error', 'Category not found.');
            }

            $category = (object) [
                'id' => null,
                'name' => $categoryName,
                'slug' => $categorySlug,
                'description' => "Activities in the {$categoryName} category",
                'type' => 'rehabilitation',
                'icon_class' => $this->getCategoryIcon($categoryName),
                'color_code' => '#8B5CF6',
            ];

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

        // Get centres and categories for the form
        $centres = Centre::active()->get();

        // Use enum-based categories instead of ActivityCategory model
        $categories = collect([
            'Autism Spectrum Support',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disabilities',
            'Learning Support',
            'Speech Therapy'
        ])->map(function($name) {
            return (object)[
                'id' => $name,
                'category_name' => $name,
                'is_active' => true
            ];
        });

        return view('activities.create-enhanced', compact('centres', 'categories'));
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
            'activity_description' => 'required|string|max:2000',
            'category_id' => 'required|in:Autism Spectrum Support,Hearing Impairment,Visual Impairment,Physical Disabilities,Learning Support,Speech Therapy',
            'learning_outcomes' => 'nullable|string|max:2000',

            // Location & Centre
            'centre_id' => 'required|exists:centres,centre_id',
            'activity_location' => 'required|string|max:255',

            // Instructor with qualification validation
            'instructor_id' => [
                'required',
                'exists:staffs,id',
                new InstructorQualificationRule($request->input('category_id'))
            ],

            // Participants
            'max_participants' => 'required|integer|min:3|max:10',
            'min_participants' => 'required|integer|min:3|max:10',
            'participants' => 'nullable|string',

            // Schedule
            'sessions_per_week' => 'nullable|integer|min:1|max:5',
            'session_duration' => 'nullable|numeric|min:0.5|max:180',
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
                new \App\Rules\NoWeekendOrHolidayRule($request->input('centre_id'))
            ],
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'activity_start_time' => [
                'required',
                'date_format:H:i'
            ],
            'activity_end_time' => 'nullable|date_format:H:i',
            'activity_period_type' => 'nullable|string|in:single,recurring,course',
            'recurring_days' => 'nullable|array|min:1',
            'recurring_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',

            // Additional form fields
            'difficulty_level' => 'nullable|string|in:beginner,intermediate,advanced',
            'age_group' => 'nullable|string',
            'prerequisites' => 'nullable|string|max:2000',
        ]);

        // Remap form field names to internal names used by downstream methods
        $validated['start_time'] = $validated['activity_start_time'] ?? null;
        $validated['duration_hours'] = ($validated['session_duration'] ?? 60) / 60;
        $validated['sessions_per_week'] = $validated['sessions_per_week'] ?? 2;

        // Map activity_period_type to integer months
        $periodMap = ['single' => 1, 'recurring' => 3, 'course' => 6];
        $validated['activity_period'] = $periodMap[$validated['activity_period_type'] ?? 'single'] ?? 3;

        // Normalize recurring_days to capitalized schedule_days
        $validated['schedule_days'] = array_map('ucfirst', $validated['recurring_days'] ?? ['Monday']);

        // Additional mandatory requirements validation
        if (empty($validated['instructor_id'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'MANDATORY REQUIREMENT: Every activity must have at least 1 qualified instructor assigned.');
        }

        // Enhanced validation: Check for scheduling conflicts and duplicates
        $validationErrors = $this->validateActivityConflicts($validated);
        if (!empty($validationErrors)) {
            return redirect()->back()
                ->withErrors($validationErrors)
                ->withInput()
                ->with('warning', 'Please resolve the conflicts highlighted below before creating the activity.');
        }

        // Check for instructor availability conflicts
        $instructorConflicts = $this->checkInstructorScheduleConflicts($validated);
        if (!empty($instructorConflicts)) {
            return redirect()->back()
                ->withErrors(['instructor_conflicts' => $instructorConflicts])
                ->withInput()
                ->with('warning', 'Instructor has scheduling conflicts. Please choose different times or instructor.');
        }

        // Check room capacity and availability
        $roomConflicts = $this->checkRoomAvailability($validated);
        if (!empty($roomConflicts)) {
            return redirect()->back()
                ->withErrors(['room_conflicts' => $roomConflicts])
                ->withInput()
                ->with('warning', 'Room conflicts detected. Please choose a different location or time.');
        }

        try {
            DB::beginTransaction();

            // Calculate end time based on duration
            $startTime = Carbon::parse($validated['start_time']);
            $endTime = $startTime->copy()->addHours($validated['duration_hours']);

            // Calculate end date based on start date + activity period (months)
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = $startDate->copy()->addMonths($validated['activity_period']);

            $activity = Activity::create([
                'activity_name' => $validated['activity_name'],
                'activity_description' => $validated['activity_description'],
                'learning_outcomes' => $validated['learning_outcomes'] ?? null,
                'category' => $validated['category_id'],
                'centre_id' => $validated['centre_id'],
                'instructor_id' => $validated['instructor_id'],
                'activity_location' => $validated['activity_location'],
                'max_participants' => $validated['max_participants'] ?? 10,
                'duration_weeks' => $validated['activity_period'] ?? 12,
                'sessions_per_week' => $validated['sessions_per_week'] ?? 2,
                'session_duration_minutes' => $validated['session_duration'] ?? 60,
                'is_active' => true,
            ]);

            // Create activity sessions based on schedule
            $this->createActivitySessions($activity, $validated);

            // Enroll selected participants if any
            if (!empty($validated['participants'])) {
                $participantIds = explode(',', $validated['participants']);
                $this->enrollParticipants($activity, $participantIds, $validated['start_date']);
            }

            DB::commit();

            // Log activity
            \App\Models\ActivityLog::log([
                'action_type' => 'created',
                'model_type' => 'Activity',
                'model_id' => $activity->id,
                'title' => 'New Activity Created: ' . $activity->activity_name,
                'description' => 'Duration: ' . ($activity->duration_weeks ?? 'N/A') . ' weeks | Max Participants: ' . ($activity->max_participants ?? 'N/A'),
                'icon' => 'calendar-plus',
                'status' => 'success'
            ]);

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

            $activity = Activity::with(['sessions.teacher', 'enrollments.trainee', 'instructor', 'centre'])
                ->findOrFail($id);

            $role = session('role');
            $userId = session('id');

            // Teachers can view all activities but cannot edit them (UI restrictions in view)

            // Get activity statistics with error handling
            try {
                $totalSessions = $activity->sessions ? $activity->sessions->count() : 0;
                $activeSessions = $activity->sessions ? $activity->sessions->whereIn('status', ['active', 'scheduled'])->count() : 0;
                $upcomingSessions = $activity->sessions ? $activity->sessions->where('session_date', '>', now())->count() : 0;
                $completedSessions = $activity->sessions ? $activity->sessions->where('status', 'completed')->count() : 0;
                $totalEnrollments = $activity->enrollments ? $activity->enrollments->count() : 0;
                $averageAttendance = $this->calculateAverageAttendance($activity);

                $stats = [
                    'totalSessions' => $totalSessions,
                    'activeSessions' => $activeSessions,
                    'upcomingSessions' => $upcomingSessions,
                    'completedSessions' => $completedSessions,
                    'totalEnrollments' => $totalEnrollments,
                    'averageAttendance' => $averageAttendance
                ];

                Log::info('Activity stats calculated', [
                    'activity_id' => $id,
                    'stats' => $stats
                ]);
            } catch (Exception $statsError) {
                Log::error('Error calculating activity stats', [
                    'activity_id' => $id,
                    'error' => $statsError->getMessage()
                ]);
                $stats = [
                    'totalSessions' => 0,
                    'activeSessions' => 0,
                    'upcomingSessions' => 0,
                    'completedSessions' => 0,
                    'totalEnrollments' => 0,
                    'averageAttendance' => 0
                ];
            }

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

            // Try to load categories from the activity_categories table,
            // fall back to building from the activities.category enum column
            try {
                $categories = Category::active()->ordered()->get();
            } catch (\Exception $catException) {
                Log::info('Category table not available, using enum-based categories');
                $categoryNames = Activity::pluck('category')->filter()->unique()->values();
                $categories = $categoryNames->map(function ($name, $index) {
                    return (object) [
                        'id' => $index + 1,
                        'name' => $name,
                        'type' => 'general',
                        'icon_class' => 'fas fa-puzzle-piece',
                        'color_code' => '#007bff',
                        'category_status' => 'active',
                    ];
                });
            }

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
                'activity_id' => 'nullable|string|max:20',
                'activity_description' => 'required|string',
                'category_id' => 'nullable',
                'difficulty_level' => 'nullable|string',
                'age_group' => 'nullable|string',
                'activity_location' => 'required|string|max:255',
                'max_participants' => 'nullable|integer|min:1|max:50',
                'session_duration' => 'nullable|integer|min:1|max:480',
                'activity_period' => 'nullable|integer|min:1|max:52',
                'activity_goals' => 'nullable|string',
                'materials_needed' => 'nullable|string',
                'preparation_notes' => 'nullable|string',
                'is_active' => 'nullable',
            ]);

            // Check if location or max_participants changed for session sync
            $locationChanged = $activity->activity_location !== $validated['activity_location'];
            $capacityChanged = $activity->max_participants !== $validated['max_participants'];

            // Map form fields to actual database columns
            // Table columns: activity_name, activity_description, category, centre_id,
            //   duration_weeks, sessions_per_week, session_duration_minutes,
            //   max_participants, learning_outcomes, activity_location, instructor_id, is_active
            $updateData = [
                'activity_name' => $validated['activity_name'],
                'activity_description' => $validated['activity_description'],
                'activity_location' => $validated['activity_location'],
                'is_active' => $request->has('is_active') ? 1 : 0,
            ];

            // Map form field names to actual column names
            if (isset($validated['category_id'])) {
                $updateData['category'] = $validated['category_id'];
            }
            if (isset($validated['max_participants'])) {
                $updateData['max_participants'] = $validated['max_participants'];
            }
            if (isset($validated['session_duration'])) {
                $updateData['session_duration_minutes'] = $validated['session_duration'];
            }
            if (isset($validated['activity_period'])) {
                $updateData['duration_weeks'] = $validated['activity_period'];
            }
            if (isset($validated['activity_goals'])) {
                $updateData['learning_outcomes'] = $validated['activity_goals'];
            }

            $activity->update($updateData);

            // Sync changes to all future sessions
            if ($locationChanged || $capacityChanged) {
                $this->syncActivityChangesToSessions($activity, $locationChanged, $capacityChanged);
            }

            // Log activity
            \App\Models\ActivityLog::log([
                'action_type' => 'updated',
                'model_type' => 'Activity',
                'model_id' => $activity->id,
                'title' => 'Activity Updated: ' . $activity->activity_name,
                'description' => $locationChanged || $capacityChanged ? 'Location/Capacity changed - sessions updated' : 'Activity details updated',
                'icon' => 'edit',
                'status' => 'info'
            ]);

            $message = 'Activity updated successfully!';
            if ($locationChanged || $capacityChanged) {
                $message .= ' All future sessions have been updated to reflect these changes.';
            }

            return redirect()->route('activities.show', $activity->id)
                ->with('success', $message);
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

            // Delete all associated sessions before deleting the activity
            // The confirm dialog tells users "All associated sessions will also be deleted"
            $activity->sessions()->delete();

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
        // Emergency debug - should show in logs
        Log::emergency('SESSIONS METHOD CALLED', [
            'activity_id' => $id,
            'session_data' => session()->all(),
            'request_url' => request()->fullUrl()
        ]);

        // Also dump to error log
        error_log("SESSIONS METHOD DEBUG: ID=$id, User=" . session('name') . ", Role=" . session('role'));

        try {
            Log::info('Sessions method accessed', [
                'activity_id' => $id,
                'user_id' => session('id'),
                'user_role' => session('role'),
                'user_name' => session('name')
            ]);

            $activity = Activity::with(['sessions.enrollments.trainee', 'sessions.teacher'])
                ->findOrFail($id);

            $role = session('role');
            $userId = session('id');

            // Get all sessions for the activity (teachers can view all sessions, editing restricted in view)
            $sessions = $activity->sessions()
                ->with(['enrollments.trainee', 'teacher'])
                ->orderBy('session_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->paginate(10);

            // Get available teachers for session creation
            $teachers = User::where('role', 'teacher')
                ->where('status', 'active')
                ->where('centre_id', session('centre_id'))
                ->get(['id', 'name']);

            // The create-session modal has a required centre dropdown; without
            // this the select renders empty and the form can never submit.
            $centres = Centre::where('centre_status', 'active')->get();

            return view('activities.sessions', compact('activity', 'sessions', 'role', 'teachers', 'centres'));
        } catch (Exception $e) {
            Log::error('Error loading activity sessions: ' . $e->getMessage(), [
                'activity_id' => $id,
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('activities.home')
                ->with('error', 'Unable to load sessions: ' . $e->getMessage());
        }
    }

    /**
     * Create a new session for an activity
     */
    public function createSession(Request $request, $id)
    {
        $role = session('role');

        if ($role !== 'admin') {
            return redirect()->route('activities.sessions', $id)
                ->with('error', 'Only centre administrators can create sessions.');
        }

        try {
            $activity = Activity::findOrFail($id);

            $validated = $request->validate([
                'teacher_id' => 'required|exists:staffs,id',
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:15|max:240',
                'location' => 'required|string|max:255',
                'max_capacity' => 'required|integer|min:3|max:10',
                'status' => 'required|in:scheduled,active,cancelled,completed',
                'room_number' => 'nullable|string|max:50',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Business Logic Validation
            $sessionDate = Carbon::parse($validated['date']);
            $startTime = Carbon::parse($validated['start_time']);
            // Carbon 3 rejects numeric strings; the form posts duration as a string
            $endTime = $startTime->copy()->addMinutes((int) $validated['duration']);

            // Rule 1: No sessions on weekends or Malaysia public holidays
            if (MalaysiaHolidays::isNonWorkingDay($sessionDate)) {
                $reason = MalaysiaHolidays::getNonWorkingDayReason($sessionDate);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Sessions cannot be scheduled on this date. ' . $reason);
            }

            // Rule 2: Sessions must start between 9:30 AM and 3:30 PM
            if ($startTime->format('H:i') < '09:30' || $startTime->format('H:i') > '15:30') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Sessions must start between 9:30 AM and 3:30 PM. Centre operates from 9:00 AM to 4:30 PM.');
            }

            // Rule 3: All sessions must end by 4:30 PM
            if ($endTime->format('H:i') > '16:30') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Sessions must end by 4:30 PM. Please reduce the duration or start time.');
            }

            DB::beginTransaction();

            // Calculate end time from duration
            $start = Carbon::parse($validated['start_time']);
            $end = $start->copy()->addMinutes((int) $validated['duration']);

            $session = ActivitySession::create([
                'activity_id' => $activity->id,
                'session_name' => $activity->activity_name . ' — ' . $sessionDate->format('d M Y'),
                'session_date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'end_time' => $end->format('H:i:s'),
                'location' => $validated['location'],
                'instructor_id' => $validated['teacher_id'],
                'session_status' => $validated['status'],
                'session_notes' => $validated['notes'] ?? null,
                'max_participants' => $validated['max_capacity'],
                'current_participants' => 0,
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
            if ($role === 'teacher' && $session->instructor_id != $userId) {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'You can only mark attendance for your own sessions.');
            }

            // Calculate actual session status based on date/time
            $sessionDate = Carbon::parse($session->session_date);

            // Safer time parsing with error handling
            try {
                // Extract time portion only (handles datetime strings in time fields)
                $startTimeClean = $this->extractTimeOnly($session->start_time);
                $endTimeClean = $this->extractTimeOnly($session->end_time);

                // Parse times safely using extracted time portions
                $sessionStart = $sessionDate->copy()->setTimeFromTimeString($startTimeClean);
                $sessionEnd = $sessionDate->copy()->setTimeFromTimeString($endTimeClean);
            } catch (Exception $timeParseException) {
                Log::error('Time parsing error for session ' . $sessionId . ': ' . $timeParseException->getMessage(), [
                    'session_id' => $sessionId,
                    'original_start_time' => $session->start_time,
                    'original_end_time' => $session->end_time,
                    'extracted_start_time' => $this->extractTimeOnly($session->start_time),
                    'extracted_end_time' => $this->extractTimeOnly($session->end_time)
                ]);
                // Set default times if parsing fails
                $sessionStart = $sessionDate->copy()->setTime(9, 0, 0);
                $sessionEnd = $sessionDate->copy()->setTime(17, 0, 0);
            }

            $now = Carbon::now();

            // Business Rule: Prevent attendance marking on non-working days
            if (MalaysiaHolidays::isNonWorkingDay($sessionDate)) {
                $reason = MalaysiaHolidays::getNonWorkingDayReason($sessionDate);
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'Attendance cannot be marked for this session. ' . $reason);
            }

            // Calculate actual status - prioritize date/time logic over database status
            if ($session->session_status == 'cancelled') {
                $actualStatus = 'cancelled';
            } elseif ($now->greaterThan($sessionEnd)) {
                $actualStatus = 'completed';
            } elseif ($now->greaterThanOrEqualTo($sessionStart)) {
                $actualStatus = 'ongoing';
            } else {
                $actualStatus = 'scheduled';
            }

            // Allow attendance marking for scheduled, ongoing, and completed sessions (but not cancelled)
            if ($actualStatus === 'cancelled') {
                return redirect()->route('activities.sessions', $activityId)
                    ->with('error', 'Cannot mark attendance for cancelled sessions.');
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
            if ($role === 'teacher' && $session->instructor_id != $userId) {
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
            if ($session->session_status === 'scheduled') {
                $session->update([
                    'session_status' => 'ongoing'
                ]);
            }

            // Mark attendance for each trainee
            foreach ($validated['attendance'] as $traineeId => $status) {
                $enrollment = ActivityEnrollment::where('activity_id', $activityId)
                    ->where('trainee_id', $traineeId)
                    ->first();

                if ($enrollment) {
                    // Update enrollment: increment attendance_count for present/late
                    $enrollmentUpdate = [
                        'enrollment_notes' => $validated['notes'][$traineeId] ?? null
                    ];
                    if (in_array($status, ['present', 'late'])) {
                        $enrollmentUpdate['attendance_count'] = $enrollment->attendance_count + 1;
                    }
                    $enrollment->update($enrollmentUpdate);

                    // Create or update attendance record
                    Attendance::updateOrCreate([
                        'trainee_id' => $traineeId,
                        'activity_id' => $activityId,
                        'attendance_date' => $validated['attendance_date'],
                    ], [
                        'status' => $status,
                        'remarks' => $validated['notes'][$traineeId] ?? null,
                        'marked_by' => $userId,
                        'check_in_time' => $status === 'present' ? now() : null,
                    ]);
                }
            }

            // Mark session as completed after attendance processing
            $session->update(['session_status' => 'completed']);

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

            // Check if session is completed - use proper field name and include database status
            $sessionDate = Carbon::parse($session->session_date);
            $sessionEnd = $sessionDate->copy()->setTimeFromTimeString($session->end_time);
            $now = Carbon::now();

            // Session is completed if database status is 'completed' OR current time is past session end time
            $isCompleted = $session->session_status === 'completed' ||
                ($now->greaterThan($sessionEnd) && $session->session_status !== 'cancelled');

            if ($isCompleted) {
                // For completed sessions, show only attendees who were present or late
                $attendedTrainees = \DB::table('session_attendance')
                    ->join('trainees', 'session_attendance.trainee_id', '=', 'trainees.id')
                    ->where('session_attendance.session_id', $sessionId)
                    ->whereIn('session_attendance.attendance_status', ['present', 'late'])
                    ->select('trainees.*', 'session_attendance.attendance_status', 'session_attendance.check_in_time', 'session_attendance.notes')
                    ->get();

                $enrolledTrainees = $attendedTrainees;
                $eligibleTrainees = collect(); // Empty collection for completed sessions
            } else {
                // For scheduled/ongoing sessions, show enrollments that were made before or on session date
                $validEnrollments = $session->enrollments()
                    ->where('enrollment_date', '<=', $session->session_date)
                    ->with('trainee')
                    ->get();

                $enrolledTrainees = $validEnrollments->map(function ($enrollment) {
                    $enrollment->attendance_status = null; // No attendance yet
                    return $enrollment;
                });

                $enrolledTraineeIds = $validEnrollments->pluck('trainee_id');
                $eligibleTrainees = Trainee::with(['centre'])
                    ->whereNotIn('id', $enrolledTraineeIds)
                    ->where('status', 'active')
                    ->get();
            }

            return view('activities.enrollments', compact('session', 'eligibleTrainees', 'enrolledTrainees', 'isCompleted'));
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

            // Check if session is completed - use proper field name and include database status
            $sessionDate = Carbon::parse($session->session_date);
            $sessionEnd = $sessionDate->copy()->setTimeFromTimeString($session->end_time);
            $now = Carbon::now();

            // Session is completed if database status is 'completed' OR current time is past session end time
            $isCompleted = $session->session_status === 'completed' ||
                ($now->greaterThan($sessionEnd) && $session->session_status !== 'cancelled');

            if ($isCompleted) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Cannot add enrollments to a completed session.');
            }

            // Check if trainee is already enrolled in the activity
            $existingEnrollment = ActivityEnrollment::where('activity_id', $activityId)
                ->where('trainee_id', $validated['trainee_id'])
                ->first();

            if ($existingEnrollment) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Trainee is already enrolled in this activity.');
            }

            // Check session capacity based on session attendance records
            $currentSessionAttendances = \DB::table('session_attendance')->where('session_id', $sessionId)->count();
            if ($session->max_participants && $currentSessionAttendances >= $session->max_participants) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Session is at maximum capacity.');
            }

            // Create activity enrollment
            ActivityEnrollment::create([
                'activity_id' => $activityId,
                'trainee_id' => $validated['trainee_id'],
                'enrollment_status' => 'enrolled',
                'enrollment_date' => now()->toDateString()
            ]);

            // Note: Activity enrollment count is calculated dynamically when needed

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
     * Remove enrollment from a session
     */
    public function removeEnrollment($activityId, $sessionId, $traineeId)
    {
        try {
            $session = ActivitySession::findOrFail($sessionId);
            $role = session('role');

            if (!in_array($role, ['admin', 'supervisor'])) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'You do not have permission to remove enrollments.');
            }

            // Check if session is completed - use proper field name and include database status
            $sessionDate = Carbon::parse($session->session_date);
            $sessionEnd = $sessionDate->copy()->setTimeFromTimeString($session->end_time);
            $now = Carbon::now();

            // Session is completed if database status is 'completed' OR current time is past session end time
            $isCompleted = $session->session_status === 'completed' ||
                ($now->greaterThan($sessionEnd) && $session->session_status !== 'cancelled');

            if ($isCompleted) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Cannot remove enrollments from a completed session.');
            }

            // Find the enrollment
            $enrollment = ActivityEnrollment::where('activity_id', $activityId)
                ->where('trainee_id', $traineeId)
                ->first();

            if (!$enrollment) {
                return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                    ->with('error', 'Enrollment not found.');
            }

            // Remove the enrollment
            $enrollment->delete();

            // Note: Activity enrollment count is calculated dynamically when needed

            Log::info('Trainee removed from session', [
                'session_id' => $sessionId,
                'trainee_id' => $traineeId,
                'removed_by' => session('id')
            ]);

            return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                ->with('success', 'Trainee removed from session successfully.');
        } catch (Exception $e) {
            Log::error('Error removing enrollment: ' . $e->getMessage());
            return redirect()->route('activities.enrollments', [$activityId, $sessionId])
                ->with('error', 'An error occurred while removing the trainee.');
        }
    }

    /**
     * Get activity categories (enum-based)
     */
    private function getActivityCategories()
    {
        return [
            'Autism Spectrum Support',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disabilities',
            'Learning Support',
            'Speech Therapy'
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
                    $q->where('instructor_id', $userId);
                });
            }

            // Get total sessions and enrollments
            $totalSessions = DB::table('activity_occurrences')->count();
            $totalEnrollments = DB::table('activity_enrollments')->count();

            // If teacher role, filter sessions by teacher
            if ($role === 'teacher') {
                $totalSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->count();
                $totalEnrollments = DB::table('activity_enrollments')
                    ->whereIn('activity_id', function ($subQuery) use ($userId) {
                        $subQuery->select('activity_id')
                            ->from('activity_occurrences')
                            ->where('instructor_id', $userId);
                    })->count();
            }

            return [
                'total_activities' => $query->count(),
                'active_activities' => $query->where('is_active', true)->count(),
                'total_sessions' => $totalSessions,
                'total_enrollments' => $totalEnrollments,
                'total' => $query->count(), // Backward compatibility
                'active' => $query->where('is_active', true)->count(), // Backward compatibility
                'sessions' => $totalSessions, // For activities home view
                'enrollments' => $totalEnrollments, // For activities home view
                'rehabilitation' => $query->get()->filter(function ($activity) {
                    return in_array($activity->category, ['Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 'Sensory Integration']);
                })->count(),
                'academic' => $query->get()->filter(function ($activity) {
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
                    'instructor_id' => $validated['instructor_id'],
                    'session_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $sessionEnd->format('H:i:s'),
                    'location' => $activity->activity_location,
                    'max_participants' => $activity->max_participants,
                    'session_status' => 'scheduled',
                    'session_name' => $activity->activity_name . ' - Session ' . ($sessionCount + 1),
                    'session_description' => 'Session for ' . $activity->activity_name
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

        foreach ($participantIds as $traineeIdentifier) {
            if (empty($traineeIdentifier)) continue;

            try {
                // Check if trainee exists and is active
                // Handle both integer IDs and string trainee_id formats (e.g., 'LD0001')
                $trainee = null;

                if (is_numeric($traineeIdentifier)) {
                    // If it's numeric, assume it's the primary key ID
                    $trainee = Trainee::where('id', $traineeIdentifier)
                        ->where('status', 'active')
                        ->first();
                } else {
                    // If it's not numeric, assume it's the trainee_id string format
                    $trainee = Trainee::where('trainee_id', $traineeIdentifier)
                        ->where('status', 'active')
                        ->first();
                }

                if (!$trainee) {
                    Log::warning('Trainee not found or inactive', ['trainee_identifier' => $traineeIdentifier]);
                    continue;
                }

                // Always use the integer ID for database relationships
                ActivityEnrollment::create([
                    'activity_id'       => $activity->id,
                    'trainee_id'        => $trainee->id,
                    'enrollment_date'   => $enrollmentDate,
                    'enrollment_status' => 'enrolled',
                    'enrolled_by'       => session('id')
                ]);

                $enrolledCount++;
            } catch (Exception $e) {
                Log::error('Error enrolling participant', [
                    'trainee_identifier' => $traineeIdentifier,
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
        // Get total attendance records for this activity
        $totalAttendanceRecords = \App\Models\Attendance::where('activity_id', $activity->id)->count();

        if ($totalAttendanceRecords === 0) {
            return 0;
        }

        // Count present and late (both considered as attended)
        $attendedCount = \App\Models\Attendance::where('activity_id', $activity->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        // Calculate percentage
        return round(($attendedCount / $totalAttendanceRecords) * 100, 2);
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
                $query->whereHas('category', function ($q) use ($request) {
                    $q->where('category_name', $request->category);
                });
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('activity_name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('activity_id', 'LIKE', "%{$search}%");
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
            // Welcome Page Service Categories (Updated August 2025)
            'Physical Disabilities' => [
                'Physical Therapy',
                'Occupational Therapy',
                'Art & Creativity',
                'Computer Skills',
                'Mathematics',
                'Literacy',
                'Music Therapy',
                'Vocational Training',
                'Speech Therapy',
                'Life Skills'
            ],

            'Learning Support' => [
                'Mathematics',
                'Literacy',
                'Computer Skills',
                'Art & Creativity',
                'Occupational Therapy',
                'Life Skills',
                'Vocational Training',
                'Science',
                'Social Skills',
                'Music Therapy',
                'Behavioral Therapy'
            ],

            'Visual Impairment' => [
                'Music Therapy',
                'Computer Skills',
                'Mathematics',
                'Literacy',
                'Life Skills',
                'Vocational Training',
                'Physical Therapy',
                'Art & Creativity'
            ],

            'Autism Spectrum Support' => [
                'Mathematics',
                'Computer Skills',
                'Art & Creativity',
                'Music Therapy',
                'Sensory Integration',
                'Behavioral Therapy',
                'Life Skills',
                'Science',
                'Social Skills',
                'Speech Therapy'
            ],

            'Hearing Impairment' => [
                'Art & Creativity',
                'Computer Skills',
                'Mathematics',
                'Science',
                'Vocational Training',
                'Life Skills',
                'Physical Therapy',
                'Literacy'
            ],

            'Speech Therapy' => [
                'Speech Therapy',
                'Art & Creativity',
                'Music Therapy',
                'Computer Skills',
                'Social Skills',
                'Mathematics',
                'Literacy',
                'Life Skills'
            ],

            // Sensory conditions benefit from specialized interventions
            'Sensory Processing Disorder' => [
                'Sensory Integration',
                'Occupational Therapy',
                'Art & Creativity',
                'Music Therapy',
                'Physical Therapy',
                'Behavioral Therapy'
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
                'teacher_id' => 'required|exists:staffs,id',
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
     * Display activity schedule management (per-activity sessions in weekly view)
     */
    public function schedule($id)
    {
        try {
            $activity = Activity::with(['sessions', 'activeEnrollments.trainee'])->findOrFail($id);

            // Check permissions
            $role = session('role');
            $userId = session('id');

            if (!$this->canManageActivity($activity, $role, $userId)) {
                return redirect()->route('activities.home')
                    ->with('error', 'You do not have permission to manage this activity schedule.');
            }

            // Get this activity's sessions for the current week to populate the weekly calendar
            $sessions = ActivitySession::with(['activity.centre', 'enrollments'])
                ->where('activity_id', $activity->id)
                ->whereBetween('session_date', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->get();

            $centres = Centre::active()->orderBy('centre_name')->get();

            return view('activities.schedule', compact('activity', 'sessions', 'centres'));
        } catch (Exception $e) {
            Log::error('Error loading activity schedule: ' . $e->getMessage());
            return redirect()->route('activities.home')
                ->with('error', 'Unable to load activity schedule.');
        }
    }

    /**
     * Display weekly schedule overview across all activities
     */
    public function weeklySchedule()
    {
        try {
            // Determine the target week from the date query parameter (supports week navigation)
            $targetDate = request('date') ? Carbon::parse(request('date')) : Carbon::now();
            $weekStart = $targetDate->copy()->startOfWeek();
            $weekEnd = $targetDate->copy()->endOfWeek();

            // Query activity_occurrences for the target week
            $sessions = ActivitySession::with(['activity', 'activity.centre', 'enrollments'])
                ->whereBetween('session_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->get();

            $centres = Centre::active()->orderBy('centre_name')->get();

            return view('activities.schedule', compact('sessions', 'centres'));
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
            $sessions = \App\Models\ActivitySession::whereHas('activity', function ($query) use ($teacherId) {
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
            $activity = Activity::with(['activeEnrollments.trainee', 'sessions'])->findOrFail($id);

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
                        'activity_id'      => $id,
                        'trainee_id'       => $traineeId,
                        'enrollment_date'  => $request->enrollment_date,
                        'enrollment_status' => 'enrolled',
                        'enrollment_notes' => $request->goals ?? null,
                        'enrolled_by'      => session('id')
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
                'max_capacity' => 'nullable|integer|min:3|max:10',
                'teacher_id' => 'nullable|exists:staffs,id'
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
            $teacherConflicts = ActivitySession::where('instructor_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            foreach ($teacherConflicts as $session) {
                $existingStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
                $existingEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

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
                    $existingStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
                    $existingEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

                    if ($this->timesOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                        $hasConflict = true;
                        $conflicts[] = "Room conflict: {$venue} - {$roomNumber} is already booked for '{$session->activity->activity_name}' from {$session->start_time} to {$session->end_time}";
                    }
                }
            }

            // Check for break time violations (minimum 15 minutes between sessions)
            $breakTimeConflicts = ActivitySession::where('instructor_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            foreach ($breakTimeConflicts as $session) {
                $existingStart = Carbon::parse($session->session_date . ' ' . $session->start_time);
                $existingEnd = Carbon::parse($session->session_date . ' ' . $session->end_time);

                // Check if sessions are too close (less than 15 minutes apart)
                $timeBetween = min(
                    abs($newStart->diffInMinutes($existingEnd)),
                    abs($existingStart->diffInMinutes($newEnd))
                );

                if ($timeBetween < 15 && $timeBetween > 0) {
                    $conflicts[] = "Insufficient break time: Only {$timeBetween} minutes between sessions. Minimum 15 minutes required.";
                }
            }

            // Check for daily session limits (max 5 sessions per instructor per day)
            $dailySessions = ActivitySession::where('instructor_id', $teacherId)
                ->where('scheduled_date', $scheduledDate)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    return $query->where('id', '!=', $excludeSessionId);
                })
                ->get();

            $dailySessionCount = $dailySessions->count();
            if ($dailySessionCount >= 5) {
                $conflicts[] = "Daily session limit exceeded: {$dailySessionCount} sessions already scheduled (maximum 5 sessions per instructor per day)";
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

            // Get filter parameters
            $searchType = $request->get('search_type', 'activity');
            $searchValue = $request->get('search_value');
            $centreFilter = $request->get('centre');
            $categoryFilter = $request->get('category');
            $statusFilter = $request->get('status');
            $dateRangeFilter = $request->get('date_range');

            // Base query for sessions with eager loading
            $query = ActivitySession::with(['activity.centre', 'teacher', 'enrollments.trainee']);

            // Apply search filters
            if ($searchValue) {
                switch ($searchType) {
                    case 'activity':
                        $query->whereHas('activity', function ($q) use ($searchValue) {
                            $q->where('activity_name', 'LIKE', "%{$searchValue}%");
                        });
                        break;
                    case 'staff':
                        $query->whereHas('teacher', function ($q) use ($searchValue) {
                            $q->where('name', 'LIKE', "%{$searchValue}%");
                        });
                        break;
                    case 'trainee':
                        $query->whereHas('enrollments.trainee', function ($q) use ($searchValue) {
                            $q->where('trainee_first_name', 'LIKE', "%{$searchValue}%")
                                ->orWhere('trainee_last_name', 'LIKE', "%{$searchValue}%");
                        });
                        break;
                    case 'room':
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('location', 'LIKE', "%{$searchValue}%");
                        });
                        break;
                }
            }

            // Apply centre filter
            if ($centreFilter) {
                $query->whereHas('activity', function ($q) use ($centreFilter) {
                    $q->where('centre_id', $centreFilter);
                });
            }

            // Apply category filter (category is a string column on activities table, not a relationship)
            if ($categoryFilter) {
                $query->whereHas('activity', function ($q) use ($categoryFilter) {
                    $q->where('category', $categoryFilter);
                });
            }

            // Apply status filter
            if ($statusFilter) {
                switch ($statusFilter) {
                    case 'future':
                        $query->where('session_status', 'scheduled')
                            ->where('session_date', '>', Carbon::now());
                        break;
                    case 'progress':
                        $query->where('session_status', 'ongoing');
                        break;
                    case 'done':
                        $query->where('session_status', 'completed');
                        break;
                    case 'cancelled':
                        $query->where('session_status', 'cancelled');
                        break;
                }
            }

            // Apply date range filter
            if ($dateRangeFilter) {
                switch ($dateRangeFilter) {
                    case 'today':
                        $query->whereDate('session_date', Carbon::today());
                        break;
                    case 'week':
                        $query->whereBetween('session_date', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'month':
                        $query->whereBetween('session_date', [
                            Carbon::now()->startOfMonth(),
                            Carbon::now()->endOfMonth()
                        ]);
                        break;
                    case 'past':
                        $query->where('session_date', '<', Carbon::today());
                        break;
                }
            }

            // Default ordering - show upcoming sessions first, then recent past
            $sessions = $query->orderByRaw('
                CASE
                    WHEN session_date >= CURDATE() THEN 0
                    ELSE 1
                END,
                session_date ASC,
                start_time ASC
            ')->paginate(15);

            // Get filter options
            $centres = Centre::active()->orderBy('centre_name')->get();

            // Get categories from existing activities (category is an enum string, not a separate table)
            $categories = Activity::whereNotNull('category')
                ->pluck('category')
                ->unique()
                ->filter()
                ->sort()
                ->values();

            Log::info('Schedule index loaded successfully', [
                'user_id' => $userId,
                'role' => $role,
                'sessions_count' => $sessions->count(),
                'total_sessions' => $sessions->total(),
                'filters' => compact('searchType', 'searchValue', 'centreFilter', 'categoryFilter', 'statusFilter', 'dateRangeFilter')
            ]);

            return view('activities.schedule.index', compact('sessions', 'centres', 'categories', 'role'));
        } catch (Exception $e) {
            Log::error('Error loading schedule index: ' . $e->getMessage(), [
                'user_id' => $userId ?? null,
                'filters' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Unable to load schedule. Please try again.');
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
            ->where('session_date', '>=', today())
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->first();

        if (!$nextSession) {
            return null;
        }

        $sessionDate = Carbon::parse($nextSession->session_date);
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
        return $activities->filter(function ($activity) {
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
                'instructor_id' => 'required|exists:staffs,id',
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
                        $participantConflicts = ActivityEnrollment::where('trainee_id', $traineeId)
                            ->where('enrollment_status', 'enrolled')
                            ->whereHas('activity', fn($q) => $q->where('activity_status', '!=', 'cancelled'))
                            ->whereHas('activity.sessions', function($query) use ($dayOfWeek) {
                                $query->where('day_of_week', $dayOfWeek)
                                      ->whereIn('session_status', ['scheduled', 'ongoing']);
                            })
                            ->with(['activity.sessions' => function($q) use ($dayOfWeek) {
                                $q->where('day_of_week', $dayOfWeek)
                                  ->whereIn('session_status', ['scheduled', 'ongoing']);
                            }])
                            ->get();

                        foreach ($participantConflicts as $enrollment) {
                            foreach ($enrollment->activity->sessions as $session) {
                                $existingStart = Carbon::parse($session->start_time);
                                $existingEnd = Carbon::parse($session->end_time);

                                if ($this->timesOverlap($startTime, $endTime, $existingStart, $existingEnd)) {
                                    $hasConflicts = true;
                                    $trainee = Trainee::find($traineeId);
                                    $traineeName = $trainee ? $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name : "Trainee #{$traineeId}";

                                    $conflicts[] = [
                                        'type'       => 'participant',
                                        'day'        => $dayOfWeek,
                                        'trainee_id' => $traineeId,
                                        'message'    => "Participant conflict on {$dayOfWeek}: {$traineeName} is already enrolled in '{$enrollment->activity->activity_name}' from {$existingStart->format('g:i A')} to {$existingEnd->format('g:i A')}"
                                    ];
                                }
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

    // ========================================
    // ENHANCED SCHEDULE MANAGEMENT METHODS
    // ========================================

    /**
     * Display personal schedule for current user
     */
    public function personalSchedule()
    {
        try {
            $userId = session('id');
            $role = session('role');

            if (!$userId) {
                return redirect()->route('auth.loginpage');
            }

            Log::info('Loading personal schedule', ['user_id' => $userId, 'role' => $role]);

            // Get user's sessions based on role
            $query = ActivitySession::with(['activity', 'enrollments.trainee']);

            if ($role === 'teacher') {
                $query->where('instructor_id', $userId);
            } elseif ($role === 'trainee') {
                $query->whereHas('enrollments', function ($q) use ($userId) {
                    $q->where('trainee_id', $userId);
                });
            } else {
                // For other roles, show centre-specific sessions
                $centreId = session('centre_id');
                $query->whereHas('activity', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $sessions = $query->where('session_date', '>=', Carbon::now()->subDays(30))
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            return view('activities.schedule.personal', compact('sessions', 'role'));
        } catch (Exception $e) {
            Log::error('Error loading personal schedule: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load personal schedule.');
        }
    }

    /**
     * Display staff schedule by encrypted ID (admin only)
     */
    public function staffSchedule($encryptedId)
    {
        try {
            $currentRole = session('role');

            // Only admin can view other staff schedules
            if ($currentRole !== 'admin') {
                return redirect()->route('activities.schedule.personal')
                    ->with('error', 'Access denied. You can only view your own schedule.');
            }

            $staffId = decrypt($encryptedId);
            $staff = User::findOrFail($staffId);

            Log::info('Admin viewing staff schedule', [
                'admin_id' => session('id'),
                'iium_id' => $staff->iium_id,
                'staff_name' => $staff->name
            ]);

            $sessions = ActivitySession::with(['activity', 'enrollments.trainee'])
                ->where('instructor_id', $staffId)
                ->where('session_date', '>=', Carbon::now()->subDays(30))
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            return view('activities.schedule.staff', compact('sessions', 'staff'));
        } catch (Exception $e) {
            Log::error('Error loading staff schedule: ' . $e->getMessage());
            return redirect()->route('activities.schedule')
                ->with('error', 'Unable to load staff schedule.');
        }
    }

    /**
     * Display trainee schedule by encrypted ID (admin/supervisor/parent)
     */
    public function traineeSchedule($encryptedId)
    {
        try {
            $currentRole = session('role');
            $currentUserId = session('id');

            $traineeId = decrypt($encryptedId);
            $trainee = Trainee::findOrFail($traineeId);

            // Check access permissions
            if ($currentRole === 'parent') {
                // Parents can only view their own child's schedule
                // This would need parent-child relationship check
                // For now, allowing if they have the encrypted ID
            } elseif (!in_array($currentRole, ['admin', 'supervisor'])) {
                return redirect()->route('activities.schedule.personal')
                    ->with('error', 'Access denied.');
            }

            Log::info('Viewing trainee schedule', [
                'viewer_id' => $currentUserId,
                'viewer_role' => $currentRole,
                'trainee_id' => $traineeId,
                'trainee_name' => $trainee->full_name
            ]);

            $sessions = ActivitySession::with(['activity', 'enrollments'])
                ->whereHas('enrollments', function ($q) use ($traineeId) {
                    $q->where('trainee_id', $traineeId);
                })
                ->where('session_date', '>=', Carbon::now()->subDays(30))
                ->orderBy('session_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();

            return view('activities.schedule.trainee', compact('sessions', 'trainee'));
        } catch (Exception $e) {
            Log::error('Error loading trainee schedule: ' . $e->getMessage());
            return redirect()->route('activities.schedule')
                ->with('error', 'Unable to load trainee schedule.');
        }
    }

    /**
     * Get calendar data for AJAX requests
     */
    public function getCalendarData(Request $request)
    {
        try {
            $userId = session('id');
            $role = session('role');
            $start = $request->get('start');
            $end = $request->get('end');
            $view = $request->get('view', 'personal'); // personal, staff, trainee
            $targetId = $request->get('target_id'); // for staff/trainee views

            $query = ActivitySession::with(['activity']);

            // Date filtering
            if ($start && $end) {
                $query->whereBetween('session_date', [
                    Carbon::parse($start)->startOfDay(),
                    Carbon::parse($end)->endOfDay()
                ]);
            }

            // Role-based filtering
            if ($view === 'personal') {
                if ($role === 'teacher') {
                    $query->where('instructor_id', $userId);
                } elseif ($role === 'trainee') {
                    $query->whereHas('enrollments', function ($q) use ($userId) {
                        $q->where('trainee_id', $userId);
                    });
                } else {
                    $centreId = session('centre_id');
                    $query->whereHas('activity', function ($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    });
                }
            } elseif ($view === 'staff' && $role === 'admin' && $targetId) {
                $staffId = decrypt($targetId);
                $query->where('instructor_id', $staffId);
            } elseif ($view === 'trainee' && in_array($role, ['admin', 'supervisor', 'parent']) && $targetId) {
                $traineeId = decrypt($targetId);
                $query->whereHas('enrollments', function ($q) use ($traineeId) {
                    $q->where('trainee_id', $traineeId);
                });
            }

            $sessions = $query->get();

            $events = $sessions->map(function ($session) {
                return $session->calendar_event;
            });

            return response()->json($events);
        } catch (Exception $e) {
            Log::error('Error fetching calendar data: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Get dashboard schedule widget data
     */
    public function getDashboardScheduleData()
    {
        try {
            $userId = session('id');
            $role = session('role');

            $query = ActivitySession::with(['activity']);

            // Get today's and upcoming sessions
            if ($role === 'teacher') {
                $query->where('instructor_id', $userId);
            } elseif ($role === 'trainee') {
                $query->whereHas('enrollments', function ($q) use ($userId) {
                    $q->where('trainee_id', $userId);
                });
            } else {
                $centreId = session('centre_id');
                $query->whereHas('activity', function ($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $todaySessions = $query->clone()
                ->whereDate('session_date', Carbon::today())
                ->orderBy('start_time')
                ->limit(3)
                ->get();

            $upcomingSessions = $query->clone()
                ->where('session_date', '>', Carbon::today())
                ->where('session_date', '<=', Carbon::today()->addDays(7))
                ->orderBy('session_date')
                ->orderBy('start_time')
                ->limit(5)
                ->get();

            return [
                'today' => $todaySessions,
                'upcoming' => $upcomingSessions,
                'total_today' => $todaySessions->count(),
                'total_week' => $upcomingSessions->count()
            ];
        } catch (Exception $e) {
            Log::error('Error fetching dashboard schedule data: ' . $e->getMessage());
            return [
                'today' => collect([]),
                'upcoming' => collect([]),
                'total_today' => 0,
                'total_week' => 0
            ];
        }
    }

    /**
     * Enhanced validation: Check for activity conflicts and duplicates
     */
    private function validateActivityConflicts(array $validated): array
    {
        $errors = [];

        // Check for duplicate activity names in the same centre
        $duplicateName = Activity::where('activity_name', $validated['activity_name'])
            ->where('centre_id', $validated['centre_id'])
            ->where('is_active', true)
            ->exists();

        if ($duplicateName) {
            $errors['activity_name'] = 'An active activity with this name already exists in this centre.';
        }

        // Check for similar activity descriptions (potential duplicates)
        $similarActivities = Activity::where('centre_id', $validated['centre_id'])
            ->where('is_active', true)
            ->where('category', $validated['category_id'])
            ->get();

        foreach ($similarActivities as $activity) {
            $similarity = $this->calculateStringSimilarity($validated['activity_description'], $activity->activity_description);
            if ($similarity > 85) { // 85% similarity threshold
                $errors['activity_description'] = "This activity description is very similar to existing activity: '{$activity->activity_name}'. Please ensure this is not a duplicate.";
                break;
            }
        }

        // Check for maximum activities per instructor (activities table has no end_date column, check active only)
        $instructorActivityCount = Activity::where('instructor_id', $validated['instructor_id'])
            ->where('is_active', true)
            ->count();

        if ($instructorActivityCount >= 10) {
            $errors['instructor_id'] = 'This instructor already has the maximum number of active activities (10). Please choose another instructor.';
        }

        return $errors;
    }

    /**
     * Check instructor availability for scheduling conflicts
     */
    private function checkInstructorScheduleConflicts(array $validated): array
    {
        $conflicts = [];
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addHours($validated['duration_hours']);
        $scheduleDays = $validated['schedule_days'];

        // Check for time conflicts with existing sessions
        $existingSessions = ActivitySession::whereHas('activity', function ($query) use ($validated) {
            $query->where('instructor_id', $validated['instructor_id'])
                ->where('is_active', true);
        })
            ->where('session_status', '!=', 'cancelled')
            ->get();

        foreach ($existingSessions as $session) {
            $sessionDay = Carbon::parse($session->session_date)->format('l'); // Full day name

            if (in_array($sessionDay, $scheduleDays)) {
                $sessionStart = Carbon::parse($session->start_time);
                $sessionEnd = Carbon::parse($session->end_time);

                // Check for time overlap
                if (($startTime >= $sessionStart && $startTime < $sessionEnd) ||
                    ($endTime > $sessionStart && $endTime <= $sessionEnd) ||
                    ($startTime <= $sessionStart && $endTime >= $sessionEnd)
                ) {

                    $conflicts[] = "Conflict on {$sessionDay} {$sessionStart->format('H:i')}-{$sessionEnd->format('H:i')} with activity: {$session->activity->activity_name}";
                }
            }
        }

        // Check for daily hour limits (max 8 hours per day)
        foreach ($scheduleDays as $day) {
            $dailyHours = $this->calculateInstructorDailyHours($validated['instructor_id'], $day);
            $newHours = $validated['duration_hours'];

            if (($dailyHours + $newHours) > 8) {
                $conflicts[] = "Adding this activity would exceed daily hour limit (8 hours) on {$day}. Current: {$dailyHours}h, New: {$newHours}h";
            }
        }

        return $conflicts;
    }

    /**
     * Check room availability and capacity conflicts
     */
    private function checkRoomAvailability(array $validated): array
    {
        $conflicts = [];
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addHours($validated['duration_hours']);
        $scheduleDays = $validated['schedule_days'];
        $location = $validated['activity_location'];

        // Check for room booking conflicts
        $conflictingSessions = ActivitySession::whereHas('activity', function ($query) use ($validated) {
            $query->where('centre_id', $validated['centre_id'])
                ->where('is_active', true);
        })
            ->where('location', $location)
            ->where('session_status', '!=', 'cancelled')
            ->get();

        foreach ($conflictingSessions as $session) {
            $sessionDay = Carbon::parse($session->session_date)->format('l');

            if (in_array($sessionDay, $scheduleDays)) {
                $sessionStart = Carbon::parse($session->start_time);
                $sessionEnd = Carbon::parse($session->end_time);

                // Check for time overlap
                if (($startTime >= $sessionStart && $startTime < $sessionEnd) ||
                    ($endTime > $sessionStart && $endTime <= $sessionEnd) ||
                    ($startTime <= $sessionStart && $endTime >= $sessionEnd)
                ) {

                    $conflicts[] = "Room '{$location}' is already booked on {$sessionDay} {$sessionStart->format('H:i')}-{$sessionEnd->format('H:i')} for: {$session->activity->activity_name}";
                }
            }
        }

        // Check room capacity (if room data exists)
        $maxParticipants = $validated['max_participants'];
        $roomCapacity = $this->getRoomCapacity($location, $validated['centre_id']);

        if ($roomCapacity && $maxParticipants > $roomCapacity) {
            $conflicts[] = "Activity capacity ({$maxParticipants}) exceeds room capacity ({$roomCapacity}) for '{$location}'";
        }

        return $conflicts;
    }

    /**
     * Calculate string similarity percentage
     */
    private function calculateStringSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        if ($str1 === $str2) return 100;
        if (empty($str1) || empty($str2)) return 0;

        similar_text($str1, $str2, $percent);
        return round($percent, 2);
    }

    /**
     * Calculate instructor's daily hours for a specific day
     */
    private function calculateInstructorDailyHours(int $instructorId, string $day): float
    {
        $existingSessions = ActivitySession::whereHas('activity', function ($query) use ($instructorId) {
            $query->where('instructor_id', $instructorId)
                ->where('is_active', true);
        })
            ->where('session_status', '!=', 'cancelled')
            ->get()
            ->filter(function ($session) use ($day) {
                return Carbon::parse($session->session_date)->format('l') === $day;
            });

        $totalMinutes = 0;
        foreach ($existingSessions as $session) {
            $start = Carbon::parse($session->start_time);
            $end = Carbon::parse($session->end_time);
            $totalMinutes += $start->diffInMinutes($end);
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Get room capacity from database or configuration
     */
    private function getRoomCapacity(string $location, string $centreId): ?int
    {
        // Try to get from assets table if room data exists
        try {
            $room = DB::table('assets')
                ->where('asset_name', 'LIKE', "%{$location}%")
                ->where('centre_id', $centreId)
                ->first();

            if ($room && isset($room->specifications)) {
                $specs = json_decode($room->specifications, true);
                return $specs['capacity'] ?? null;
            }
        } catch (\Exception $e) {
            // Assets table schema may not match expected columns
        }

        // Default room capacities based on common room types
        $defaultCapacities = [
            'therapy room' => 6,
            'classroom' => 20,
            'large hall' => 50,
            'small room' => 8,
            'meeting room' => 12,
            'activity room' => 15
        ];

        $locationLower = strtolower($location);
        foreach ($defaultCapacities as $roomType => $capacity) {
            if (str_contains($locationLower, $roomType)) {
                return $capacity;
            }
        }

        return null; // Unknown capacity
    }

    /**
     * Sync activity changes to all future sessions
     */
    private function syncActivityChangesToSessions($activity, $locationChanged, $capacityChanged)
    {
        try {
            $updateData = [];

            if ($locationChanged) {
                $updateData['location'] = $activity->activity_location;
            }

            if ($capacityChanged) {
                $updateData['max_participants'] = $activity->max_participants;
            }

            if (!empty($updateData)) {
                // Update only future/upcoming sessions to avoid affecting completed ones
                $updatedCount = ActivitySession::where('activity_id', $activity->id)
                    ->whereIn('session_status', ['scheduled', 'active'])
                    ->where('session_date', '>=', now()->toDateString())
                    ->update($updateData);

                Log::info('Synced activity changes to sessions', [
                    'activity_id' => $activity->id,
                    'sessions_updated' => $updatedCount,
                    'changes' => $updateData
                ]);
            }
        } catch (Exception $e) {
            Log::error('Error syncing activity changes to sessions: ' . $e->getMessage(), [
                'activity_id' => $activity->id,
                'error' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Extract time portion from a datetime string or time string
     * Handles cases where time fields contain full datetime strings
     */
    private function extractTimeOnly($timeString)
    {
        if (!$timeString) {
            return '09:00:00'; // Default time
        }

        $timeString = trim($timeString);

        // If it looks like a full datetime (contains date), extract time portion
        if (preg_match('/\d{4}-\d{2}-\d{2}\s+(\d{1,2}:\d{2}:\d{2})/', $timeString, $matches)) {
            return $matches[1]; // Return just the time part (HH:MM:SS)
        }

        // If it looks like a full datetime without seconds, extract time portion
        if (preg_match('/\d{4}-\d{2}-\d{2}\s+(\d{1,2}:\d{2})/', $timeString, $matches)) {
            return $matches[1] . ':00'; // Add seconds
        }

        // If it's already just time (HH:MM:SS or HH:MM), return as is
        if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeString)) {
            // Add seconds if missing
            if (substr_count($timeString, ':') === 1) {
                return $timeString . ':00';
            }
            return $timeString;
        }

        // Fallback: try to parse with Carbon and extract time
        try {
            $parsed = Carbon::parse($timeString);
            return $parsed->format('H:i:s');
        } catch (Exception $e) {
            // Last resort: return default time
            return '09:00:00';
        }
    }
}
