<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Trainee;
use App\Models\Centre;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Traits\HandlesEncryptedIds;

class TraineeHomeController extends Controller
{
    use HandlesEncryptedIds;
    /**
     * Display a listing of trainees with filtering capabilities
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            Log::info('Accessing trainees home page', [
                'user_id' => session('id'),
                'filters' => $request->all()
            ]);
            
            // Start with a base query
            $query = Trainee::query();
            
            // Apply center filter
            if ($request->filled('centre')) {
                $query->where('centre_name', $request->input('centre'));
                Log::debug('Filter applied: center = ' . $request->input('centre'));
            }
            
            // Apply condition filter
            if ($request->filled('condition')) {
                $query->where('trainee_condition', $request->input('condition'));
                Log::debug('Filter applied: condition = ' . $request->input('condition'));
            }
            
            // Apply search term (name or email)
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('trainee_first_name', 'like', "%{$search}%")
                      ->orWhere('trainee_last_name', 'like', "%{$search}%")
                      ->orWhere('trainee_email', 'like', "%{$search}%");
                });
                Log::debug('Filter applied: search = ' . $search);
            }
            
            // Get the filtered trainees with eager loading for relationships and pagination
            $trainees = $query->with([
                'centre', 
                'activities', 
                'enrollments' => function($query) {
                    $query->whereIn('enrollment_status', ['enrolled', 'active']);
                }
            ])->paginate(8);
            
            // Get all active centers for filter dropdown
            // Check if we need to use status or centre_status based on your DB structure
            // Get centres with safe query using scope
            try {
                $centres = Centre::active()->get();
            } catch (\Exception $e) {
                // If active scope doesn't exist, get all centres
                Log::warning('Centre active scope not available, getting all centres');
                $centres = Centre::all();
            }
            
            // Get distinct condition types for filter dropdown
            $conditions = Trainee::select('trainee_condition')
                ->distinct()
                ->whereNotNull('trainee_condition')
                ->pluck('trainee_condition');
            
            // For stats calculations, we need all trainees (not paginated)
            $allTraineesQuery = Trainee::query();
            
            // Apply same filters for stats calculation
            if ($request->filled('centre')) {
                $allTraineesQuery->where('centre_name', $request->input('centre'));
            }
            if ($request->filled('condition')) {
                $allTraineesQuery->where('trainee_condition', $request->input('condition'));
            }
            if ($request->filled('search')) {
                $search = $request->input('search');
                $allTraineesQuery->where(function($q) use ($search) {
                    $q->where('trainee_first_name', 'like', "%{$search}%")
                      ->orWhere('trainee_last_name', 'like', "%{$search}%")
                      ->orWhere('trainee_email', 'like', "%{$search}%");
                });
            }
            
            $allTrainees = $allTraineesQuery->with([
                'centre', 
                'activities', 
                'enrollments' => function($query) {
                    $query->whereIn('enrollment_status', ['enrolled', 'active']);
                }
            ])->get();
            
            // Group trainees by center (use all trainees for statistics)
            $traineesByCenter = $allTrainees->groupBy('centre_name');
            
            // Count trainees for stats
            $totalTrainees = $allTrainees->count();
            $conditionTypes = $conditions->count();
            
            // Count new trainees in the last 30 days
            $newTraineesCount = Trainee::where('created_at', '>=', now()->subDays(30))->count();
            
            // Debug information about new trainees
            $recentlyCreated = Trainee::where('created_at', '>=', now()->subDays(30))->get();
            Log::debug('Recently created trainees:', [
                'count' => $recentlyCreated->count(),
                'dates' => $recentlyCreated->pluck('created_at')->toArray()
            ]);
            
            // Calculate stats for the view using proper column names and relationships
            $activeTrainees = $allTrainees->where('status', 'active')->count();
            
            // Count trainees enrolled in activities using the enrollments relationship
            $enrolledTrainees = $allTrainees->filter(function($trainee) {
                return $trainee->enrollments && $trainee->enrollments->count() > 0;
            })->count();
            
            // Transform paginated items and calculate progress
            $totalProgress = 0;
            $traineesWithProgress = 0;
            
            // Add progress data to paginated items
            $trainees->getCollection()->transform(function($trainee) use (&$totalProgress, &$traineesWithProgress) {
                // Calculate progress based on session attendance
                $sessionAttendanceStats = $trainee->getAttendanceStatistics();
                $sessionProgress = 0;
                
                if ($sessionAttendanceStats['total_sessions'] > 0) {
                    // Progress is calculated as attendance rate of sessions
                    $sessionProgress = $sessionAttendanceStats['attendance_rate'];
                    $totalProgress += $sessionProgress;
                    $traineesWithProgress++;
                }
                
                $trainee->session_progress = $sessionProgress;
                $trainee->meets_attendance_threshold = $sessionProgress >= 50;
                
                return $trainee;
            });
            
            // Calculate progress for all trainees for stats
            $allTraineesTotalProgress = 0;
            $allTraineesWithProgress = 0;
            $belowThresholdCount = 0;
            
            foreach ($allTrainees as $trainee) {
                $sessionAttendanceStats = $trainee->getAttendanceStatistics();
                $sessionProgress = 0;
                
                if ($sessionAttendanceStats['total_sessions'] > 0) {
                    $sessionProgress = $sessionAttendanceStats['attendance_rate'];
                    $allTraineesTotalProgress += $sessionProgress;
                    $allTraineesWithProgress++;
                }
                
                if ($sessionProgress < 50) {
                    $belowThresholdCount++;
                }
            }
            
            $avgProgress = $allTraineesWithProgress > 0 ? round($allTraineesTotalProgress / $allTraineesWithProgress, 1) : 0;
            
            $stats = [
                'total' => $totalTrainees,
                'active' => $activeTrainees,
                'enrolled' => $enrolledTrainees,
                'avg_progress' => $avgProgress,
                'below_threshold' => $belowThresholdCount
            ];
            
            Log::info('Trainee retrieved successfully', [
                'total_trainees' => $totalTrainees,
                'new_trainees' => $newTraineesCount,
                'stats' => $stats,
                'applied_filters' => $request->only(['search', 'centre', 'condition'])
            ]);
            
            return view('trainees.home', [
                'trainees' => $trainees, // Add the direct trainees variable that the view expects
                'traineesByCenter' => $traineesByCenter,
                'centres' => $centres,
                'conditions' => $conditions,
                'totalTrainees' => $totalTrainees,
                'conditionTypes' => $conditionTypes,
                'newTraineesCount' => $newTraineesCount,
                'stats' => $stats, // Add the missing stats array
                'currentFilters' => $request->only(['search', 'centre', 'condition'])
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving trainees', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty data and error message
            $emptyPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), // Empty collection
                0, // Total
                8, // Per page
                1, // Current page
                ['path' => request()->url()]
            );
            
            return view('trainees.home', [
                'trainees' => $emptyPaginator, // Add the direct trainees variable that the view expects
                'traineesByCenter' => collect(),
                'centres' => collect(),
                'conditions' => collect(),
                'totalTrainees' => 0,
                'conditionTypes' => 0,
                'newTraineesCount' => 0,
                'stats' => ['total' => 0, 'active' => 0, 'enrolled' => 0, 'avg_progress' => 0, 'below_threshold' => 0],
                'error' => 'An error occurred while retrieving trainees: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Filter trainees by criteria.
     * This method is a fallback for non-AJAX filtering.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function filter(Request $request)
    {
        try {
            Log::info('Using dedicated filter method', [
                'user_id' => session('id'),
                'filters' => $request->all()
            ]);
            
            $query = Trainee::query();
            
            // Apply center filter
            if ($request->filled('centre')) {
                $query->where('centre_name', $request->input('centre'));
            }
            
            // Apply condition filter
            if ($request->filled('condition')) {
                $query->where('trainee_condition', $request->input('condition'));
            }
            
            // Apply search term
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('trainee_first_name', 'like', "%{$search}%")
                      ->orWhere('trainee_last_name', 'like', "%{$search}%")
                      ->orWhere('trainee_email', 'like', "%{$search}%");
                });
            }
            
            // Get filtered trainees
            $trainees = $query->with('centre')->get();
            
            // Group trainees by center
            $traineesByCenter = $trainees->groupBy('centre_name');
            
            // Get all centers for filter dropdown - using centre_status instead of status
            // Get centres with safe query using scope
            try {
                $centres = Centre::active()->get();
            } catch (\Exception $e) {
                // If active scope doesn't exist, get all centres
                Log::warning('Centre active scope not available, getting all centres');
                $centres = Centre::all();
            }
            
            // Get condition types for filter dropdown
            $conditions = Trainee::select('trainee_condition')
                ->distinct()
                ->whereNotNull('trainee_condition')
                ->pluck('trainee_condition');
            
            // Count trainees for stats
            $totalTrainees = $trainees->count();
            $conditionTypes = $conditions->count();
            
            // Count new trainees in the last 30 days
            $newTraineesCount = Trainee::where('created_at', '>=', now()->subDays(30))->count();
            
            Log::info('Trainee filtered successfully', [
                'count' => $trainees->count(),
                'filters' => $request->all(),
                'user_id' => session('id')
            ]);
            
            return view('trainees.home', [
                'trainees' => $trainees, // Add the direct trainees variable that the view expects
                'traineesByCenter' => $traineesByCenter,
                'centres' => $centres,
                'conditions' => $conditions,
                'totalTrainees' => $totalTrainees,
                'conditionTypes' => $conditionTypes,
                'newTraineesCount' => $newTraineesCount,
                'currentFilters' => $request->only(['search', 'centre', 'condition'])
            ]);
        } catch (Exception $e) {
            Log::error('Error filtering trainees', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id'),
                'request' => $request->all()
            ]);
            
            return view('trainees.home', [
                'trainees' => collect(), // Add the direct trainees variable that the view expects
                'traineesByCenter' => collect(),
                'centres' => Centre::all(), // Safe fallback for error case
                'conditions' => collect(),
                'totalTrainees' => 0,
                'conditionTypes' => 0,
                'newTraineesCount' => 0,
                'error' => 'An error occurred while filtering trainees. Please try again later.'
            ]);
        }
    }
    
    /**
     * Export trainees data to CSV or Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        try {
            Log::info('Exporting trainees data', [
                'user_id' => session('id'),
                'format' => $request->input('format', 'csv')
            ]);
            
            // Build query based on filters
            $query = Trainee::query();
            
            // Apply filters if provided
            if ($request->filled('centre')) {
                $query->where('centre_name', $request->input('centre'));
            }
            
            if ($request->filled('condition')) {
                $query->where('trainee_condition', $request->input('condition'));
            }
            
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function($q) use ($search) {
                    $q->where('trainee_first_name', 'like', "%{$search}%")
                      ->orWhere('trainee_last_name', 'like', "%{$search}%")
                      ->orWhere('trainee_email', 'like', "%{$search}%");
                });
            }
            
            // Get trainees
            $trainees = $query->get();
            
            // Format for export
            $format = $request->input('format', 'csv');
            $fileName = 'trainees_' . date('Y-m-d') . '.' . $format;
            
            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );
            
            $columns = ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Birth Date', 'Age', 'Center', 'Condition', 'Created At'];
            
            $callback = function() use($trainees, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                
                foreach ($trainees as $trainee) {
                    $row = [
                        $trainee->id,
                        $trainee->trainee_first_name,
                        $trainee->trainee_last_name,
                        $trainee->trainee_email,
                        $trainee->trainee_phone_number,
                        $trainee->trainee_date_of_birth,
                        $trainee->getAgeAttribute(),
                        $trainee->centre_name,
                        $trainee->trainee_condition,
                        $trainee->created_at
                    ];
                    
                    fputcsv($file, $row);
                }
                
                fclose($file);
            };
            
            Log::info('Trainee data exported successfully', [
                'user_id' => session('id'),
                'count' => $trainees->count(),
                'format' => $format
            ]);
            
            return response()->stream($callback, 200, $headers);
            
        } catch (Exception $e) {
            Log::error('Error exporting trainees data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeshome')
                ->with('error', 'An error occurred while exporting trainees data: ' . $e->getMessage());
        }
    }
    
    /**
     * Display detailed trainees statistics and analytics.
     *
     * @return \Illuminate\View\View
     */
    public function statistics()
    {
        try {
            Log::info('Accessing trainees statistics', [
                'user_id' => session('id')
            ]);
            
            // Get trainees count by centre
            $traineesCountByCenter = Trainee::select('centre_name')
                ->selectRaw('count(*) as count')
                ->groupBy('centre_name')
                ->get();
            
            // Get trainees count by condition
            $traineesCountByCondition = Trainee::select('trainee_condition')
                ->selectRaw('count(*) as count')
                ->groupBy('trainee_condition')
                ->get();
            
            // Get trainees count by age group
            $traineesCountByAgeGroup = [
                '0-5' => Trainee::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 0 AND 5')->count(),
                '6-10' => Trainee::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 6 AND 10')->count(),
                '11-15' => Trainee::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 11 AND 15')->count(),
                '16-18' => Trainee::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 16 AND 18')->count(),
                '18+' => Trainee::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) > 18')->count(),
            ];
            
            // Get new trainees by month (last 12 months)
            $newTraineesByMonth = [];
            for ($i = 0; $i < 12; $i++) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $count = Trainee::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
                $newTraineesByMonth[$date->format('M Y')] = $count;
            }
            
            // Reverse the array to show chronological order
            $newTraineesByMonth = array_reverse($newTraineesByMonth);
            
            return view('trainees.statistics', [
                'traineesCountByCenter' => $traineesCountByCenter,
                'traineesCountByCondition' => $traineesCountByCondition,
                'traineesCountByAgeGroup' => $traineesCountByAgeGroup,
                'newTraineesByMonth' => $newTraineesByMonth,
                'totalTrainees' => Trainee::count(),
                'newTraineesCount' => Trainee::where('created_at', '>=', now()->subDays(30))->count()
            ]);
            
        } catch (Exception $e) {
            Log::error('Error retrieving trainees statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('traineeshome')
                ->with('error', 'An error occurred while retrieving trainees statistics: ' . $e->getMessage());
        }
    }
    
    /**
     * Display a specific trainee details
     */
    public function show($encrypted_id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee details', [
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'user_id' => session('id')
            ]);

            // Get real data from database
            $totalActivities = \DB::table('activity_enrollments')
                ->where('trainee_id', $id)
                ->where('enrollment_status', 'enrolled')
                ->count();

            // Calculate real attendance rate from trainee_attendances
            $totalSessions = \DB::table('trainee_attendances')
                ->where('trainee_id', $id)
                ->count();
                
            $attendedSessions = \DB::table('trainee_attendances')
                ->where('trainee_id', $id)
                ->whereIn('status', ['present', 'late'])
                ->count();
                
            $attendanceRate = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 1) : 0;

            // Get activities this week from trainee_attendances
            $recentActivities = \DB::table('trainee_attendances')
                ->where('trainee_id', $id)
                ->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->distinct('activity_id')
                ->count('activity_id');

            // Calculate enrollment duration in months
            $enrollmentDuration = $trainee->created_at ? $trainee->created_at->diffInMonths(now()) : 0;

            // Get current activities from database
            $currentActivities = \DB::table('activities')
                ->join('activity_enrollments', 'activities.id', '=', 'activity_enrollments.activity_id')
                ->where('activity_enrollments.trainee_id', $id)
                ->where('activity_enrollments.enrollment_status', 'enrolled')
                ->select(
                    'activities.id',
                    'activities.activity_name',
                    'activities.activity_description',
                    'activities.category as category',
                    'activity_enrollments.enrollment_date',
                    'activity_enrollments.enrollment_status',
                    'activity_enrollments.trainee_id'
                )
                ->get();

            // Get recent attendance records from trainee_attendances table
            $recentAttendance = \DB::table('trainee_attendances')
                ->leftJoin('activities', 'trainee_attendances.activity_id', '=', 'activities.id')
                ->where('trainee_attendances.trainee_id', $id)
                ->where('trainee_attendances.attendance_date', '>=', now()->subDays(30))
                ->select(
                    'trainee_attendances.attendance_date as date',
                    'trainee_attendances.status',
                    'trainee_attendances.notes as remarks',
                    'activities.activity_name',
                    'trainee_attendances.trainee_id'
                )
                ->orderBy('trainee_attendances.attendance_date', 'desc')
                ->limit(10)
                ->get();

            // Add debug logging for trainee-specific data
            Log::info('Trainee profile data loaded', [
                'trainee_id' => $id,
                'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'total_activities' => $totalActivities,
                'attendance_rate' => $attendanceRate,
                'recent_activities' => $recentActivities,
                'current_activities_count' => $currentActivities->count(),
                'recent_attendance_count' => $recentAttendance->count(),
                'user_id' => session('id')
            ]);

            return view('trainees.show', compact(
                'trainee', 
                'totalActivities', 
                'attendanceRate', 
                'recentActivities', 
                'enrollmentDuration',
                'currentActivities',
                'recentAttendance'
            ));

        } catch (Exception $e) {
            Log::error('Error showing trainee details', [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('trainees.home')
                ->with('error', 'Trainee not found or an error occurred.');
        }
    }

    /**
     * Display a specific trainee's schedule
     */
    public function schedule($encrypted_id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee schedule', [
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'user_id' => session('id')
            ]);

            // Get real weekly schedule from database
            $weeklySchedule = [];
            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            
            // Initialize empty schedule
            foreach ($daysOfWeek as $day) {
                $weeklySchedule[$day] = [];
            }
            
            // Get this week's sessions for the trainee 
            // For future dates: use enrollments, For past dates: use actual attendance
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $today = now()->toDateString();
            
            $thisWeekSessions = \DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->join('activity_enrollments', function($join) use ($id) {
                    $join->on('activities.id', '=', 'activity_enrollments.activity_id')
                         ->where('activity_enrollments.trainee_id', '=', $id)
                         ->where('activity_enrollments.enrollment_status', '=', 'enrolled');
                })
                ->whereBetween('activity_occurrences.session_date', [$startOfWeek, $endOfWeek])
                ->select(
                    'activity_occurrences.session_date',
                    'activity_occurrences.start_time',
                    'activity_occurrences.end_time', 
                    'activity_occurrences.location',
                    'activities.activity_name'
                )
                ->distinct()
                ->orderBy('activity_occurrences.session_date')
                ->orderBy('activity_occurrences.start_time')
                ->get();

            // Organize sessions by day of week
            foreach ($thisWeekSessions as $session) {
                $dayName = \Carbon\Carbon::parse($session->session_date)->format('l'); // Get day name (Monday, Tuesday, etc.)
                $startTime = \Carbon\Carbon::parse($session->start_time)->format('h:i A');
                $endTime = \Carbon\Carbon::parse($session->end_time)->format('h:i A');
                
                $weeklySchedule[$dayName][] = [
                    'time' => $startTime . ' - ' . $endTime,
                    'activity' => $session->activity_name,
                    'location' => $session->location
                ];
            }

            // Get upcoming activities (next 7 days beyond current week)
            $upcomingActivities = \DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->join('activity_enrollments', function($join) use ($id) {
                    $join->on('activities.id', '=', 'activity_enrollments.activity_id')
                         ->where('activity_enrollments.trainee_id', '=', $id)
                         ->where('activity_enrollments.enrollment_status', '=', 'enrolled');
                })
                ->whereBetween('activity_occurrences.session_date', [now()->addWeek()->startOfWeek(), now()->addWeek()->endOfWeek()])
                ->select(
                    'activity_occurrences.session_date',
                    'activity_occurrences.start_time',
                    'activity_occurrences.end_time', 
                    'activity_occurrences.location',
                    'activities.activity_name'
                )
                ->distinct()
                ->orderBy('activity_occurrences.session_date')
                ->orderBy('activity_occurrences.start_time')
                ->limit(6)
                ->get();

            return view('trainees.schedule', compact('trainee', 'weeklySchedule', 'upcomingActivities'));

        } catch (Exception $e) {
            Log::error('Error showing trainee schedule', [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('trainees.home')
                ->with('error', 'Could not load schedule. Please try again.');
        }
    }

    /**
     * Display a specific trainee's attendance record
     */
    public function attendance($encrypted_id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return redirect()->route('trainees.home')->with('error', 'Invalid or expired link.');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee attendance', [
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'user_id' => session('id')
            ]);

            // Get real attendance statistics from database
            $attendanceStats = $trainee->getAttendanceStatistics();
            
            // If no attendance data exists, return default values
            if ($attendanceStats['total_sessions'] == 0) {
                $attendanceStats = [
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'excused' => 0,
                    'total_sessions' => 0,
                    'rate' => 0,
                    'meets_threshold' => false
                ];
            } else {
                // Format the data for the view 
                $attendanceStats['rate'] = round($attendanceStats['attendance_rate'], 1);
            }

            // Get real attendance history from database
            $attendanceHistory = \DB::table('trainee_attendances')
                ->leftJoin('activities', 'trainee_attendances.activity_id', '=', 'activities.id')
                ->where('trainee_attendances.trainee_id', $id)
                ->select(
                    'trainee_attendances.attendance_date as date',
                    'trainee_attendances.status',
                    'trainee_attendances.notes as remarks',
                    'activities.activity_name as activity',
                    'trainee_attendances.marked_at'
                )
                ->orderBy('trainee_attendances.attendance_date', 'desc')
                ->limit(50) // Limit to recent 50 records
                ->get();

            // Calculate monthly attendance data for chart (last 12 months)
            $monthlyData = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $startOfMonth = $month->copy()->startOfMonth();
                $endOfMonth = $month->copy()->endOfMonth();
                
                $monthAttendances = \DB::table('trainee_attendances')
                    ->where('trainee_id', $id)
                    ->whereBetween('attendance_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
                    ->get();
                
                $totalSessions = $monthAttendances->count();
                $presentSessions = $monthAttendances->whereIn('status', ['present', 'late'])->count();
                $attendanceRate = $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 0;
                
                $monthlyData[] = [
                    'month' => $month->format('M'),
                    'rate' => $attendanceRate
                ];
            }

            // Add debug logging for trainee-specific attendance data
            Log::info('Trainee attendance data loaded', [
                'trainee_id' => $id,
                'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'attendance_stats' => $attendanceStats,
                'attendance_history_count' => $attendanceHistory->count(),
                'monthly_data_points' => count($monthlyData),
                'user_id' => session('id')
            ]);

            return view('trainees.attendance', compact('trainee', 'attendanceStats', 'attendanceHistory', 'monthlyData'));

        } catch (Exception $e) {
            Log::error('Error showing trainee attendance', [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('trainees.home')
                ->with('error', 'Could not load attendance data. Please try again.');
        }
    }

    /**
     * Mark attendance for a specific trainee
     */
    public function markAttendance(Request $request, $encrypted_id)
    {
        try {
            if (!session()->has('id')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Decrypt the ID
            $id = $this->decryptId($encrypted_id);
            if (!$id) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired link.'], 400);
            }

            $trainee = Trainee::findOrFail($id);
            
            // Validate request data
            $validatedData = $request->validate([
                'trainee_id' => 'required|integer',
                'attendance_date' => 'required|date|before_or_equal:today',
                'attendance_status' => 'required|in:present,late,absent,excused',
                'check_in_time' => 'nullable|date_format:H:i',
                'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
                'attendance_remarks' => 'nullable|string|max:500',
                'activity_type' => 'nullable|string|max:255'
            ]);
            
            Log::info('Marking trainee attendance', [
                'trainee_id' => $id,
                'encrypted_id' => $encrypted_id,
                'user_id' => session('id'),
                'data' => $validatedData
            ]);
            
            // Check if attendance already exists for this date
            $existingAttendance = Attendance::where('trainee_id', $id)
                ->whereDate('attendance_date', $validatedData['attendance_date'])
                ->first();
            
            if ($existingAttendance) {
                // Update existing attendance
                $existingAttendance->update([
                    'status' => $validatedData['attendance_status'],
                    'remarks' => $validatedData['attendance_remarks'],
                    'marked_by' => session('id'),
                    'check_in_time' => $validatedData['check_in_time'],
                    'check_out_time' => $validatedData['check_out_time'],
                    'activity_type' => $validatedData['activity_type']
                ]);
                
                $message = 'Attendance updated successfully';
            } else {
                // Create new attendance record
                // BUSINESS LOGIC: Use session-based attendance for trainees
                // Remove check_in_time and check_out_time as trainees are tracked per session, not daily
                Attendance::create([
                    'trainee_id' => $id,
                    'attendance_date' => $validatedData['attendance_date'],
                    'status' => $validatedData['attendance_status'],
                    'notes' => $validatedData['attendance_remarks'],
                    'marked_by_user_id' => session('id'),
                    'marked_at' => now()
                ]);
                
                $message = 'Attendance marked successfully';
            }
            
            Log::info('Trainee attendance marked successfully', [
                'trainee_id' => $id,
                'attendance_date' => $validatedData['attendance_date'],
                'status' => $validatedData['attendance_status'],
                'marked_by' => session('id')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Error marking trainee attendance', [
                'encrypted_id' => $encrypted_id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }
    }
}