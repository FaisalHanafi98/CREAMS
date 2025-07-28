<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Trainee;
use App\Models\Centre;
use Illuminate\Support\Facades\Log;
use Exception;

class TraineeHomeController extends Controller
{
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
            
            // Get the filtered trainees with eager loading
            $trainees = $query->with(['centre', 'activities', 'enrollments'])->get();
            
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
            
            // Group trainees by center
            $traineesByCenter = $trainees->groupBy('centre_name');
            
            // Count trainees for stats
            $totalTrainees = $trainees->count();
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
            $activeTrainees = $trainees->where('status', 'active')->count();
            
            // Count trainees enrolled in activities using the enrollments relationship
            $enrolledTrainees = $trainees->filter(function($trainee) {
                return $trainee->enrollments && $trainee->enrollments->where('enrollment_status', 'enrolled')->count() > 0;
            })->count();
            
            // Calculate average progress based on actual enrollment data
            $totalProgress = 0;
            $traineesWithProgress = 0;
            
            foreach ($trainees as $trainee) {
                if ($trainee->enrollments && $trainee->enrollments->count() > 0) {
                    $enrolledActivities = $trainee->enrollments->where('enrollment_status', 'enrolled');
                    if ($enrolledActivities->count() > 0) {
                        $avgTraineeProgress = $enrolledActivities->avg('progress_percentage') ?? 0;
                        $totalProgress += $avgTraineeProgress;
                        $traineesWithProgress++;
                    }
                }
            }
            
            $avgProgress = $traineesWithProgress > 0 ? round($totalProgress / $traineesWithProgress) : 0;
            
            $stats = [
                'total' => $totalTrainees,
                'active' => $activeTrainees,
                'enrolled' => $enrolledTrainees,
                'avg_progress' => $avgProgress
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
            return view('trainees.home', [
                'trainees' => collect(), // Add the direct trainees variable that the view expects
                'traineesByCenter' => collect(),
                'centres' => collect(),
                'conditions' => collect(),
                'totalTrainees' => 0,
                'conditionTypes' => 0,
                'newTraineesCount' => 0,
                'stats' => ['total' => 0, 'active' => 0, 'enrolled' => 0, 'avg_progress' => 0],
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
    public function show($id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee details', [
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);

            return view('trainees.show', compact('trainee'));

        } catch (Exception $e) {
            Log::error('Error showing trainee details', [
                'trainee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('traineeshome')
                ->with('error', 'Trainee not found or an error occurred.');
        }
    }

    /**
     * Display a specific trainee's schedule
     */
    public function schedule($id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee schedule', [
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);

            // Sample weekly schedule data - this would come from database in real implementation
            $weeklySchedule = [
                'Monday' => [
                    ['time' => '09:00 AM', 'activity' => 'Physical Therapy', 'location' => 'Therapy Room A'],
                    ['time' => '02:00 PM', 'activity' => 'Group Session', 'location' => 'Main Hall']
                ],
                'Tuesday' => [
                    ['time' => '10:00 AM', 'activity' => 'Individual Counseling', 'location' => 'Counseling Room'],
                    ['time' => '03:00 PM', 'activity' => 'Assessment', 'location' => 'Assessment Center']
                ],
                'Wednesday' => [
                    ['time' => '09:30 AM', 'activity' => 'Therapy Session', 'location' => 'Therapy Room B'],
                ],
                'Thursday' => [
                    ['time' => '11:00 AM', 'activity' => 'Progress Review', 'location' => 'Office'],
                    ['time' => '02:30 PM', 'activity' => 'Group Activity', 'location' => 'Recreation Hall']
                ],
                'Friday' => [
                    ['time' => '09:00 AM', 'activity' => 'Final Assessment', 'location' => 'Assessment Center'],
                ],
                'Saturday' => [],
                'Sunday' => []
            ];

            return view('trainees.schedule', compact('trainee', 'weeklySchedule'));

        } catch (Exception $e) {
            Log::error('Error showing trainee schedule', [
                'trainee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('trainees.show', $id)
                ->with('error', 'Could not load schedule. Please try again.');
        }
    }

    /**
     * Display a specific trainee's attendance record
     */
    public function attendance($id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $trainee = Trainee::findOrFail($id);
            
            Log::info('Viewing trainee attendance', [
                'trainee_id' => $id,
                'user_id' => session('id')
            ]);

            // Sample attendance statistics - this would come from database in real implementation
            $attendanceStats = [
                'present' => 18,
                'late' => 3,
                'absent' => 2,
                'rate' => 92
            ];

            return view('trainees.attendance', compact('trainee', 'attendanceStats'));

        } catch (Exception $e) {
            Log::error('Error showing trainee attendance', [
                'trainee_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return redirect()->route('trainees.show', $id)
                ->with('error', 'Could not load attendance data. Please try again.');
        }
    }
}