<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activities;
use App\Models\Trainees;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TraineeActivityController extends Controller
{
    /**
     * Display a listing of all activities.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $activities = Activities::with('trainee')
                              ->orderBy('activity_date', 'desc')
                              ->get();
        $trainees = Trainees::all();
        
        Log::info('Activities index accessed', [
            'user_id' => session('id'),
            'activities_count' => $activities->count()
        ]);
        
        return view('activities.index', [
            'activities' => $activities,
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Show the form for creating a new activity.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $trainees = Trainees::all();
        
        return view('activities.create', [
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Store a newly created activity in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request data
            $validator = Validator::make($request->all(), [
                'trainee_id' => 'required|exists:trainees,id',
                'activity_name' => 'required|string|max:255',
                'activity_type' => 'required|string|max:255',
                'activity_date' => 'required|date',
                'activity_description' => 'required|string',
                'activity_goals' => 'nullable|string',
                'activity_outcomes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Create new activity
            $activity = new Activities();
            $activity->trainee_id = $request->input('trainee_id');
            $activity->activity_name = $request->input('activity_name');
            $activity->activity_type = $request->input('activity_type');
            $activity->activity_date = $request->input('activity_date');
            $activity->activity_description = $request->input('activity_description');
            $activity->activity_goals = $request->input('activity_goals');
            $activity->activity_outcomes = $request->input('activity_outcomes');
            $activity->created_by = session('id');
            
            // Save the activity
            $activity->save();
            
            // Log successful creation
            Log::info('Activity created successfully', [
                'user_id' => session('id'),
                'activity_id' => $activity->id,
                'trainee_id' => $activity->trainee_id
            ]);
            
            return redirect()->route('activities.index')
                ->with('success', 'Activity added successfully!');
        } catch (\Exception $e) {
            // Log error
            Log::error('Error creating activity', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while adding the activity. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified activity.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $activity = Activities::with('trainee')->findOrFail($id);
        
        return view('activities.show', [
            'activity' => $activity
        ]);
    }
    
    /**
     * Show the form for editing the specified activity.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $activity = Activities::findOrFail($id);
        $trainees = Trainees::all();
        
        return view('activities.edit', [
            'activity' => $activity,
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Update the specified activity in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the activity
            $activity = Activities::findOrFail($id);
            
            // Validate request data
            $validator = Validator::make($request->all(), [
                'trainee_id' => 'required|exists:trainees,id',
                'activity_name' => 'required|string|max:255',
                'activity_type' => 'required|string|max:255',
                'activity_date' => 'required|date',
                'activity_description' => 'required|string',
                'activity_goals' => 'nullable|string',
                'activity_outcomes' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            // Update the activity
            $activity->trainee_id = $request->input('trainee_id');
            $activity->activity_name = $request->input('activity_name');
            $activity->activity_type = $request->input('activity_type');
            $activity->activity_date = $request->input('activity_date');
            $activity->activity_description = $request->input('activity_description');
            $activity->activity_goals = $request->input('activity_goals');
            $activity->activity_outcomes = $request->input('activity_outcomes');
            $activity->updated_by = session('id');
            
            // Save the activity
            $activity->save();
            
            // Log successful update
            Log::info('Activity updated successfully', [
                'user_id' => session('id'),
                'activity_id' => $activity->id
            ]);
            
            return redirect()->route('activities.index')
                ->with('success', 'Activity updated successfully!');
        } catch (\Exception $e) {
            // Log error
            Log::error('Error updating activity', [
                'user_id' => session('id'),
                'activity_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while updating the activity. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified activity from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Find the activity
            $activity = Activities::findOrFail($id);
            
            // Store activity details for logging
            $activityDetails = [
                'id' => $activity->id,
                'name' => $activity->activity_name,
                'trainee_id' => $activity->trainee_id
            ];
            
            // Delete the activity
            $activity->delete();
            
            // Log successful deletion
            Log::info('Activity deleted successfully', [
                'user_id' => session('id'),
                'activity' => $activityDetails
            ]);
            
            return redirect()->route('activities.index')
                ->with('success', 'Activity deleted successfully!');
        } catch (\Exception $e) {
            // Log error
            Log::error('Error deleting activity', [
                'user_id' => session('id'),
                'activity_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('activities.index')
                ->with('error', 'An error occurred while deleting the activity. Please try again.');
        }
    }
    
    /**
     * Filter activities by criteria.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function filter(Request $request)
    {
        $query = Activities::query()->with('trainee');
        
        // Apply trainee filter
        if ($request->filled('trainee_id')) {
            $query->where('trainee_id', $request->input('trainee_id'));
        }
        
        // Apply activity type filter
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->input('activity_type'));
        }
        
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('activity_date', '>=', $request->input('date_from'));
        }
        
        if ($request->filled('date_to')) {
            $query->where('activity_date', '<=', $request->input('date_to'));
        }
        
        // Get filtered activities
        $activities = $query->orderBy('activity_date', 'desc')->get();
        
        $trainees = Trainees::all();
        $activityTypes = Activities::select('activity_type')->distinct()->pluck('activity_type');
        
        return view('activities.filter', [
            'activities' => $activities,
            'trainees' => $trainees,
            'activityTypes' => $activityTypes,
            'filterParams' => $request->all()
        ]);
    }
    
    /**
     * Get activities for a specific trainee.
     *
     * @param  int  $traineeId
     * @return \Illuminate\View\View
     */
    public function traineeActivities($traineeId)
    {
        $trainee = Trainees::findOrFail($traineeId);
        $activities = Activities::where('trainee_id', $traineeId)
                              ->orderBy('activity_date', 'desc')
                              ->get();
        
        return view('activities.trainee', [
            'trainee' => $trainee,
            'activities' => $activities
        ]);
    }
    
    /**
     * Get activity details via AJAX.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityDetails($id)
    {
        try {
            $activity = Activities::with('trainee')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $activity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found or an error occurred.'
            ], 404);
        }
    }
    
    /**
     * Export activities to CSV.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        $activities = Activities::with('trainee')->orderBy('activity_date', 'desc')->get();
        $fileName = 'activities_' . Carbon::now()->format('Y-m-d') . '.csv';
        
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );
        
        $columns = ['ID', 'Date', 'Activity Name', 'Type', 'Trainee Name', 'Description', 'Goals', 'Outcomes'];
        
        $callback = function() use($activities, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($activities as $activity) {
                $traineeName = $activity->trainee ? $activity->trainee->trainee_first_name . ' ' . $activity->trainee->trainee_last_name : 'N/A';
                
                $row = [
                    $activity->id,
                    $activity->activity_date,
                    $activity->activity_name,
                    $activity->activity_type,
                    $traineeName,
                    $activity->activity_description,
                    $activity->activity_goals,
                    $activity->activity_outcomes
                ];
                
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        Log::info('Activities data exported', [
            'user_id' => session('id'),
            'count' => $activities->count()
        ]);
        
        return response()->stream($callback, 200, $headers);
    }
}