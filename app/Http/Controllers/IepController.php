<?php

namespace App\Http\Controllers;

use App\Models\TraineeEducationPlan;
use App\Models\IepActivityGoal;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\LearningOutcome;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IepController extends Controller
{
    public function __construct()
    {
        // Ensure user is authenticated and has appropriate role
        $this->middleware(function ($request, $next) {
            if (!session('id') || !in_array(session('role'), ['admin', 'supervisor', 'teacher'])) {
                return redirect()->route('auth.loginpage')
                    ->with('error', 'Unauthorized access. Please login with appropriate permissions.');
            }
            return $next($request);
        });
    }

    /**
     * Display listing of IEPs for a centre
     */
    public function index(Request $request)
    {
        $centreId = session('centre_id');
        
        // Base query for IEPs
        $query = TraineeEducationPlan::with(['trainee', 'creator', 'goals'])
            ->whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan_type')) {
            $query->where('plan_type', $request->plan_type);
        }

        if ($request->filled('trainee_id')) {
            $query->where('trainee_id', $request->trainee_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('plan_description', 'like', "%{$search}%")
                  ->orWhereHas('trainee', function ($traineeQuery) use ($search) {
                      $traineeQuery->where('trainee_first_name', 'like', "%{$search}%")
                                   ->orWhere('trainee_last_name', 'like', "%{$search}%");
                  });
            });
        }

        $ieps = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get trainees for filter dropdown
        $trainees = Trainee::where('status', 'active');
        if (session('role') !== 'admin') {
            $trainees->where('centre_id', $centreId);
        }
        $trainees = $trainees->orderBy('trainee_first_name')->get(['id', 'trainee_first_name', 'trainee_last_name']);

        // Get statistics
        $stats = $this->getIepStats($centreId);

        return view('iep.index', compact('ieps', 'trainees', 'stats'));
    }

    /**
     * Show form for creating new IEP
     */
    public function create(Request $request)
    {
        $centreId = session('centre_id');
        $traineeId = $request->get('trainee_id');
        
        $trainees = Trainee::where('status', 'active');
        if (session('role') !== 'admin') {
            $trainees->where('centre_id', $centreId);
        }
        $trainees = $trainees->orderBy('trainee_first_name')->get(['id', 'trainee_first_name', 'trainee_last_name']);

        $selectedTrainee = null;
        if ($traineeId) {
            $selectedTrainee = Trainee::findOrFail($traineeId);
        }

        return view('iep.create', compact('trainees', 'selectedTrainee'));
    }

    /**
     * Store new IEP
     */
    public function store(Request $request)
    {
        $centreId = session('centre_id');
        
        $validated = $request->validate([
            'trainee_id' => [
                'required',
                'exists:trainees,id',
                function ($attribute, $value, $fail) use ($centreId) {
                    $trainee = Trainee::find($value);
                    if (!$trainee || (session('role') !== 'admin' && $trainee->centre_id != $centreId)) {
                        $fail('The selected trainee is invalid.');
                    }
                }
            ],
            'plan_name' => 'required|string|max:200',
            'plan_description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'plan_type' => 'required|in:Annual,Quarterly,Custom',
            'overall_goals' => 'nullable|array',
            'overall_goals.*' => 'string|max:500',
            'strengths' => 'nullable|array',
            'strengths.*' => 'string|max:500',
            'challenges' => 'nullable|array',
            'challenges.*' => 'string|max:500',
            'support_services' => 'nullable|array',
            'support_services.*' => 'string|max:500',
            'target_completion_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string'
        ]);

        // Set default values
        $validated['created_by'] = session('id');
        $validated['status'] = 'Active';
        
        // Filter out empty array values
        $arrayFields = ['overall_goals', 'strengths', 'challenges', 'support_services'];
        foreach ($arrayFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = array_filter($validated[$field]);
            }
        }

        DB::beginTransaction();
        try {
            $iep = TraineeEducationPlan::create($validated);
            
            DB::commit();
            
            return redirect()
                ->route('iep.show', $iep->id)
                ->with('success', 'Individual Education Plan created successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Failed to create IEP: ' . $e->getMessage());
        }
    }

    /**
     * Display specific IEP
     */
    public function show($id)
    {
        $centreId = session('centre_id');
        
        $iep = TraineeEducationPlan::with([
                'trainee', 
                'creator', 
                'lastUpdater',
                'goals.activity',
                'goals.learningOutcome',
                'goals.assignedStaff',
                'progressReports'
            ])
            ->whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($id);

        // Get progress summary
        $progressSummary = $iep->overall_progress_summary;
        
        // Get upcoming deadlines
        $upcomingDeadlines = $iep->getUpcomingDeadlines(30);
        $overdueGoals = $iep->getOverdueGoals();

        // Get available activities for adding goals
        $activities = Activity::where('is_active', true);
        if (session('role') !== 'admin') {
            $activities->where('centre_id', $centreId);
        }
        $activities = $activities->orderBy('activity_name')->get(['id', 'activity_name']);

        return view('iep.show', compact('iep', 'progressSummary', 'upcomingDeadlines', 'overdueGoals', 'activities'));
    }

    /**
     * Show form for editing IEP
     */
    public function edit($id)
    {
        $centreId = session('centre_id');
        
        $iep = TraineeEducationPlan::whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($id);

        $trainees = Trainee::where('status', 'active');
        if (session('role') !== 'admin') {
            $trainees->where('centre_id', $centreId);
        }
        $trainees = $trainees->orderBy('trainee_first_name')->get(['id', 'trainee_first_name', 'trainee_last_name']);

        return view('iep.edit', compact('iep', 'trainees'));
    }

    /**
     * Update IEP
     */
    public function update(Request $request, $id)
    {
        $centreId = session('centre_id');
        
        $iep = TraineeEducationPlan::whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'plan_name' => 'required|string|max:200',
            'plan_description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'plan_type' => 'required|in:Annual,Quarterly,Custom',
            'overall_goals' => 'nullable|array',
            'overall_goals.*' => 'string|max:500',
            'strengths' => 'nullable|array',
            'strengths.*' => 'string|max:500',
            'challenges' => 'nullable|array',
            'challenges.*' => 'string|max:500',
            'support_services' => 'nullable|array',
            'support_services.*' => 'string|max:500',
            'target_completion_percentage' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:Active,Completed,Suspended,Under Review',
            'notes' => 'nullable|string'
        ]);

        // Filter out empty array values
        $arrayFields = ['overall_goals', 'strengths', 'challenges', 'support_services'];
        foreach ($arrayFields as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = array_filter($validated[$field]);
            }
        }

        DB::beginTransaction();
        try {
            $iep->update($validated);
            
            DB::commit();
            
            return redirect()
                ->route('iep.show', $iep->id)
                ->with('success', 'IEP updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with('error', 'Failed to update IEP: ' . $e->getMessage());
        }
    }

    /**
     * Delete IEP
     */
    public function destroy($id)
    {
        $centreId = session('centre_id');
        
        $iep = TraineeEducationPlan::whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($id);

        // Check if there are any goals or progress reports
        $goalsCount = $iep->goals()->count();
        $reportsCount = $iep->progressReports()->count();
        
        if ($goalsCount > 0 || $reportsCount > 0) {
            return back()->with('error', 
                "Cannot delete IEP with {$goalsCount} goals and {$reportsCount} progress reports. Consider marking as completed instead.");
        }

        DB::beginTransaction();
        try {
            $iep->delete();
            
            DB::commit();
            
            return redirect()
                ->route('iep.index')
                ->with('success', 'IEP deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to delete IEP: ' . $e->getMessage());
        }
    }

    /**
     * Create a new goal for an IEP
     */
    public function storeGoal(Request $request, $iepId)
    {
        $centreId = session('centre_id');
        
        $iep = TraineeEducationPlan::whereHas('trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($iepId);

        $validated = $request->validate([
            'activity_id' => [
                'required',
                'exists:activities,id',
                function ($attribute, $value, $fail) use ($centreId) {
                    $activity = Activity::find($value);
                    if (!$activity || (session('role') !== 'admin' && $activity->centre_id != $centreId)) {
                        $fail('The selected activity is invalid.');
                    }
                }
            ],
            'learning_outcome_id' => 'nullable|exists:learning_outcomes,id',
            'goal_title' => 'required|string|max:200',
            'goal_description' => 'required|string',
            'target_start_date' => 'required|date',
            'target_completion_date' => 'required|date|after:target_start_date',
            'progress_tracking_method' => 'required|in:Attendance,Competency,Assessment,Mixed',
            'target_percentage' => 'required|numeric|min:0|max:100',
            'priority_level' => 'required|in:Low,Medium,High,Critical',
            'success_criteria' => 'nullable|array',
            'success_criteria.*' => 'string|max:500',
            'accommodation_strategies' => 'nullable|array',
            'accommodation_strategies.*' => 'string|max:500',
            'assigned_user_id' => 'nullable|exists:staffs,id',
            'notes' => 'nullable|string'
        ]);

        // Filter out empty array values
        if (isset($validated['success_criteria'])) {
            $validated['success_criteria'] = array_filter($validated['success_criteria']);
        }
        if (isset($validated['accommodation_strategies'])) {
            $validated['accommodation_strategies'] = array_filter($validated['accommodation_strategies']);
        }

        DB::beginTransaction();
        try {
            $goal = $iep->createGoal($validated);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Goal created successfully.',
                'goal' => $goal->load(['activity', 'learningOutcome', 'assignedStaff'])
            ]);
                
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create goal: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update goal progress
     */
    public function updateGoalProgress(Request $request, $goalId)
    {
        $centreId = session('centre_id');
        
        $goal = IepActivityGoal::whereHas('iep.trainee', function ($q) use ($centreId) {
                if (session('role') !== 'admin') {
                    $q->where('centre_id', $centreId);
                }
            })
            ->findOrFail($goalId);

        $validated = $request->validate([
            'current_progress_percentage' => 'required|numeric|min:0|max:100',
            'goal_status' => 'required|in:Not Started,In Progress,Achieved,Modified,Discontinued',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $goal->updateProgress(
                $validated['current_progress_percentage'],
                $validated['notes'] ?? null
            );
            
            if ($validated['goal_status'] !== $goal->goal_status) {
                $goal->update(['goal_status' => $validated['goal_status']]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Goal progress updated successfully.',
                'goal' => $goal->fresh()
            ]);
                
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update goal progress: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get IEP statistics
     */
    private function getIepStats($centreId)
    {
        $query = TraineeEducationPlan::query();
        
        if (session('role') !== 'admin') {
            $query->whereHas('trainee', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }

        $totalIeps = $query->count();
        $activeIeps = (clone $query)->where('status', 'Active')->count();
        $completedIeps = (clone $query)->where('status', 'Completed')->count();
        $dueForReview = (clone $query)->dueForReview()->count();
        $overdue = (clone $query)->overdue()->count();

        $typeBreakdown = (clone $query)->select('plan_type', DB::raw('count(*) as count'))
            ->groupBy('plan_type')
            ->pluck('count', 'plan_type')
            ->toArray();

        return [
            'total' => $totalIeps,
            'active' => $activeIeps,
            'completed' => $completedIeps,
            'suspended' => (clone $query)->where('status', 'Suspended')->count(),
            'due_for_review' => $dueForReview,
            'overdue' => $overdue,
            'by_type' => [
                'Annual' => $typeBreakdown['Annual'] ?? 0,
                'Quarterly' => $typeBreakdown['Quarterly'] ?? 0,
                'Custom' => $typeBreakdown['Custom'] ?? 0
            ]
        ];
    }
}