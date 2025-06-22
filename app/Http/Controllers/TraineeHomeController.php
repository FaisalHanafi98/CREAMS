<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;
use App\Models\Centres;
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
            $query = Trainees::query();
            
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
            $trainees = $query->with('centre')->get();
            
            // Get all active centers for filter dropdown
            // Check if we need to use status or centre_status based on your DB structure
            $centres = Centres::where('centre_status', 'active')->get();
            
            // Get distinct condition types for filter dropdown
            $conditions = Trainees::select('trainee_condition')
                ->distinct()
                ->whereNotNull('trainee_condition')
                ->pluck('trainee_condition');
            
            // Group trainees by center
            $traineesByCenter = $trainees->groupBy('centre_name');
            
            // Count trainees for stats
            $totalTrainees = $trainees->count();
            $conditionTypes = $conditions->count();
            
            // Count new trainees in the last 30 days
            $newTraineesCount = Trainees::where('created_at', '>=', now()->subDays(30))->count();
            
            // Debug information about new trainees
            $recentlyCreated = Trainees::where('created_at', '>=', now()->subDays(30))->get();
            Log::debug('Recently created trainees:', [
                'count' => $recentlyCreated->count(),
                'dates' => $recentlyCreated->pluck('created_at')->toArray()
            ]);
            
            Log::info('Trainees retrieved successfully', [
                'total_trainees' => $totalTrainees,
                'new_trainees' => $newTraineesCount,
                'applied_filters' => $request->only(['search', 'centre', 'condition'])
            ]);
            
            return view('trainees.home', [
                'traineesByCenter' => $traineesByCenter,
                'centres' => $centres,
                'conditions' => $conditions,
                'totalTrainees' => $totalTrainees,
                'conditionTypes' => $conditionTypes,
                'newTraineesCount' => $newTraineesCount,
                'currentFilters' => $request->only(['search', 'centre', 'condition'])
            ]);
        } catch (Exception $e) {
            Log::error('Error retrieving trainees', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return view with empty data and error message
            return view('trainees.home', [
                'traineesByCenter' => collect(),
                'centres' => collect(),
                'conditions' => collect(),
                'totalTrainees' => 0,
                'conditionTypes' => 0,
                'newTraineesCount' => 0,
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
            
            $query = Trainees::query();
            
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
            $centres = Centres::where('centre_status', 'active')->get();
            
            // Get condition types for filter dropdown
            $conditions = Trainees::select('trainee_condition')
                ->distinct()
                ->whereNotNull('trainee_condition')
                ->pluck('trainee_condition');
            
            // Count trainees for stats
            $totalTrainees = $trainees->count();
            $conditionTypes = $conditions->count();
            
            // Count new trainees in the last 30 days
            $newTraineesCount = Trainees::where('created_at', '>=', now()->subDays(30))->count();
            
            Log::info('Trainees filtered successfully', [
                'count' => $trainees->count(),
                'filters' => $request->all(),
                'user_id' => session('id')
            ]);
            
            return view('trainees.home', [
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
                'traineesByCenter' => collect(),
                'centres' => Centres::where('centre_status', 'active')->get(),
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
            $query = Trainees::query();
            
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
            
            Log::info('Trainees data exported successfully', [
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
     * Display enhanced trainee dashboard
     *
     * @return \Illuminate\View\View
     */
    public function enhancedIndex()
    {
        try {
            Log::info('Accessing enhanced trainee dashboard', [
                'user_id' => session('id')
            ]);
            
            // Get all centres for filter dropdown
            $centres = Centres::where('centre_status', 'active')->get();
            
            // Calculate statistics
            $totalTrainees = Trainees::count();
            $activeTrainees = Trainees::where('status', 'active')->count();
            $newThisMonth = Trainees::where('created_at', '>=', now()->startOfMonth())->count();
            
            // Calculate average attendance (placeholder - implement based on your attendance system)
            $avgAttendance = 85; // This should be calculated from actual attendance data
            
            $stats = [
                'total' => $totalTrainees,
                'active' => $activeTrainees,
                'new_this_month' => $newThisMonth,
                'avg_attendance' => $avgAttendance
            ];
            
            Log::info('Enhanced trainee dashboard loaded successfully', [
                'stats' => $stats,
                'centres_count' => $centres->count()
            ]);
            
            return view('trainees.enhanced-dashboard', [
                'centres' => $centres,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            Log::error('Error loading enhanced trainee dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('traineeshome')
                ->with('error', 'An error occurred while loading the dashboard.');
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
            $traineesCountByCenter = Trainees::select('centre_name')
                ->selectRaw('count(*) as count')
                ->groupBy('centre_name')
                ->get();
            
            // Get trainees count by condition
            $traineesCountByCondition = Trainees::select('trainee_condition')
                ->selectRaw('count(*) as count')
                ->groupBy('trainee_condition')
                ->get();
            
            // Get trainees count by age group
            $traineesCountByAgeGroup = [
                '0-5' => Trainees::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 0 AND 5')->count(),
                '6-10' => Trainees::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 6 AND 10')->count(),
                '11-15' => Trainees::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 11 AND 15')->count(),
                '16-18' => Trainees::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) BETWEEN 16 AND 18')->count(),
                '18+' => Trainees::whereRaw('TIMESTAMPDIFF(YEAR, trainee_date_of_birth, CURDATE()) > 18')->count(),
            ];
            
            // Get new trainees by month (last 12 months)
            $newTraineesByMonth = [];
            for ($i = 0; $i < 12; $i++) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $count = Trainees::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
                $newTraineesByMonth[$date->format('M Y')] = $count;
            }
            
            // Reverse the array to show chronological order
            $newTraineesByMonth = array_reverse($newTraineesByMonth);
            
            return view('trainees.statistics', [
                'traineesCountByCenter' => $traineesCountByCenter,
                'traineesCountByCondition' => $traineesCountByCondition,
                'traineesCountByAgeGroup' => $traineesCountByAgeGroup,
                'newTraineesByMonth' => $newTraineesByMonth,
                'totalTrainees' => Trainees::count(),
                'newTraineesCount' => Trainees::where('created_at', '>=', now()->subDays(30))->count()
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
}