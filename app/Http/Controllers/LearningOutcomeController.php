<?php

namespace App\Http\Controllers;

use App\Models\LearningOutcome;
use App\Models\Activity;
use App\Models\TraineeCompetencyProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearningOutcomeController extends Controller
{
    public function __construct()
    {
        // Ensure user is authenticated and has appropriate role
        $this->middleware(function ($request, $next) {
            if (!session('user_id') || !in_array(session('role'), ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('login.form')
                    ->with('error', 'Unauthorized access. Please login with appropriate permissions.');
            }
            return $next($request);
        });
    }

    /**
     * Display listing of learning outcomes for an activity
     */
    public function index(Request $request, $activityId = null)
    {
        $centreId = session('centre_id');
        
        // Get activity if specified
        $activity = null;
        if ($activityId) {
            $activity = Activity::where('centre_id', $centreId)->findOrFail($activityId);
        }

        // Base query for learning outcomes
        $query = LearningOutcome::with(['activity', 'creator'])
            ->whereHas('activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });

        // Filter by activity if specified
        if ($activity) {
            $query->where('activity_id', $activity->id);
        }

        // Apply filters
        if ($request->filled('competency_level')) {
            $query->where('competency_level', $request->competency_level);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('outcome_title', 'like', "%{$search}%")
                  ->orWhere('outcome_description', 'like', "%{$search}%");
            });
        }

        $outcomes = $query->orderBy('display_order')->paginate(15);

        // Get activities for filter dropdown
        $activities = Activity::where('centre_id', $centreId)
            ->where('is_active', true)
            ->orderBy('activity_name')
            ->get(['id', 'activity_name']);

        // Get statistics
        $stats = $this->getLearningOutcomeStats($centreId, $activityId);

        return view('learning-outcomes.index', compact('outcomes', 'activities', 'activity', 'stats'));
    }

    /**
     * Show form for creating new learning outcome
     */
    public function create(Request $request)
    {
        $centreId = session('centre_id');
        $activityId = $request->get('activity_id');
        
        $activities = Activity::where('centre_id', $centreId)
            ->where('is_active', true)
            ->orderBy('activity_name')
            ->get(['id', 'activity_name']);

        $selectedActivity = null;
        if ($activityId) {
            $selectedActivity = Activity::where('centre_id', $centreId)->findOrFail($activityId);
        }

        return view('learning-outcomes.create', compact('activities', 'selectedActivity'));
    }

    /**
     * Store new learning outcome
     */
    public function store(Request $request)
    {
        $centreId = session('centre_id');
        
        $validated = $request->validate([
            'activity_id' => [
                'required',
                'exists:activities,id',
                function ($attribute, $value, $fail) use ($centreId) {
                    $activity = Activity::find($value);
                    if (!$activity || $activity->centre_id != $centreId) {
                        $fail('The selected activity is invalid.');
                    }
                }
            ],
            'outcome_title' => 'required|string|max:200',
            'outcome_description' => 'nullable|string',
            'competency_level' => 'required|in:Beginner,Intermediate,Advanced',
            'assessment_criteria' => 'nullable|array',
            'assessment_criteria.*' => 'string|max:500',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        // Set default values
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = session('user_id');
        
        // Set display order if not provided
        if (!isset($validated['display_order'])) {
            $validated['display_order'] = LearningOutcome::where('activity_id', $validated['activity_id'])->max('display_order') + 1;
        }

        // Process assessment criteria
        if (isset($validated['assessment_criteria'])) {
            $validated['assessment_criteria'] = array_filter($validated['assessment_criteria']);
        }

        DB::beginTransaction();
        try {
            $outcome = LearningOutcome::create($validated);
            
            DB::commit();
            
            return redirect()
                ->route('learning-outcomes.show', $outcome->id)
                ->with('success', 'Learning outcome created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Failed to create learning outcome: ' . $e->getMessage());
        }
    }

    /**
     * Display specific learning outcome
     */
    public function show($id)
    {
        $centreId = session('centre_id');
        
        $outcome = LearningOutcome::with(['activity', 'creator', 'competencyProgress.trainee', 'competencyProgress.assessor'])
            ->whereHas('activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->findOrFail($id);

        // Get progress statistics
        $progressStats = $outcome->progress_statistics;
        
        // Get recent progress updates
        $recentProgress = $outcome->competencyProgress()
            ->with(['trainee', 'assessor'])
            ->orderBy('last_assessed_at', 'desc')
            ->take(10)
            ->get();

        return view('learning-outcomes.show', compact('outcome', 'progressStats', 'recentProgress'));
    }

    /**
     * Show form for editing learning outcome
     */
    public function edit($id)
    {
        $centreId = session('centre_id');
        
        $outcome = LearningOutcome::whereHas('activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->findOrFail($id);

        $activities = Activity::where('centre_id', $centreId)
            ->where('is_active', true)
            ->orderBy('activity_name')
            ->get(['id', 'activity_name']);

        return view('learning-outcomes.edit', compact('outcome', 'activities'));
    }

    /**
     * Update learning outcome
     */
    public function update(Request $request, $id)
    {
        $centreId = session('centre_id');
        
        $outcome = LearningOutcome::whereHas('activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'activity_id' => [
                'required',
                'exists:activities,id',
                function ($attribute, $value, $fail) use ($centreId) {
                    $activity = Activity::find($value);
                    if (!$activity || $activity->centre_id != $centreId) {
                        $fail('The selected activity is invalid.');
                    }
                }
            ],
            'outcome_title' => 'required|string|max:200',
            'outcome_description' => 'nullable|string',
            'competency_level' => 'required|in:Beginner,Intermediate,Advanced',
            'assessment_criteria' => 'nullable|array',
            'assessment_criteria.*' => 'string|max:500',
            'display_order' => 'nullable|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Process assessment criteria
        if (isset($validated['assessment_criteria'])) {
            $validated['assessment_criteria'] = array_filter($validated['assessment_criteria']);
        }

        DB::beginTransaction();
        try {
            $outcome->update($validated);
            
            DB::commit();
            
            return redirect()
                ->route('learning-outcomes.show', $outcome->id)
                ->with('success', 'Learning outcome updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Failed to update learning outcome: ' . $e->getMessage());
        }
    }

    /**
     * Delete learning outcome
     */
    public function destroy($id)
    {
        $centreId = session('centre_id');
        
        $outcome = LearningOutcome::whereHas('activity', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->findOrFail($id);

        // Check if there are any progress records
        $progressCount = $outcome->competencyProgress()->count();
        
        if ($progressCount > 0) {
            return back()->with('error', 
                "Cannot delete learning outcome with {$progressCount} progress records. Consider deactivating instead.");
        }

        DB::beginTransaction();
        try {
            $outcome->delete();
            
            DB::commit();
            
            return redirect()
                ->route('learning-outcomes.index')
                ->with('success', 'Learning outcome deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to delete learning outcome: ' . $e->getMessage());
        }
    }

    /**
     * Bulk update display order
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'integer|exists:learning_outcomes,id'
        ]);

        $centreId = session('centre_id');

        DB::beginTransaction();
        try {
            foreach ($validated['orders'] as $order => $outcomeId) {
                LearningOutcome::whereHas('activity', function ($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })
                    ->where('id', $outcomeId)
                    ->update(['display_order' => $order + 1]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get learning outcome statistics
     */
    private function getLearningOutcomeStats($centreId, $activityId = null)
    {
        $query = LearningOutcome::whereHas('activity', function ($q) use ($centreId) {
            $q->where('centre_id', $centreId);
        });

        if ($activityId) {
            $query->where('activity_id', $activityId);
        }

        $totalOutcomes = $query->count();
        $activeOutcomes = $query->where('is_active', true)->count();
        
        $levelCounts = $query->select('competency_level', DB::raw('count(*) as count'))
            ->groupBy('competency_level')
            ->pluck('count', 'competency_level')
            ->toArray();

        return [
            'total' => $totalOutcomes,
            'active' => $activeOutcomes,
            'inactive' => $totalOutcomes - $activeOutcomes,
            'by_level' => [
                'Beginner' => $levelCounts['Beginner'] ?? 0,
                'Intermediate' => $levelCounts['Intermediate'] ?? 0,
                'Advanced' => $levelCounts['Advanced'] ?? 0
            ]
        ];
    }
}