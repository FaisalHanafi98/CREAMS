<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\ActivityScheduleTemplate;
use App\Models\ActivityTemplateApplication;
use App\Models\LearningOutcome;
use App\Models\ActivityPrerequisite;
use App\Models\TraineeEducationPlan;
use App\Models\IepActivityGoal;
use App\Models\Category;
use App\Models\Centre;
use App\Models\Users;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class ActivityWizardController extends Controller
{
    /**
     * Display the activity creation wizard
     */
    public function index()
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            // Check authorization - only admin, supervisor, and teacher can create activities
            $role = session('role');
            if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
                abort(403, 'Unauthorized to create activities');
            }

            $centreId = session('centre_id');
            
            // Get wizard initialization data
            $categories = Category::where('category_status', 'active')
                                ->orderBy('sort_order')
                                ->get();
            
            $centres = ($role === 'admin') 
                ? Centre::where('centre_status', 'active')->get()
                : Centre::where('centre_id', $centreId)->get();
            
            $scheduleTemplates = ActivityScheduleTemplate::where('is_active', true)
                                                        ->when($role !== 'admin', function($query) use ($centreId) {
                                                            return $query->where('centre_id', $centreId);
                                                        })
                                                        ->orderBy('template_name')
                                                        ->get();
            
            // Get prerequisite activities for selection
            $availableActivities = Activity::where('is_active', true)
                                          ->when($role !== 'admin', function($query) use ($centreId) {
                                              return $query->where('centre_id', $centreId);
                                          })
                                          ->orderBy('activity_name')
                                          ->get(['id', 'activity_name', 'category']);
            
            // Get active IEP plans for mapping
            $activeIepPlans = TraineeEducationPlan::with('trainee')
                                                 ->where('status', 'Active')
                                                 ->when($role !== 'admin', function($query) use ($centreId) {
                                                     return $query->whereHas('trainee', function($q) use ($centreId) {
                                                         $q->where('centre_id', $centreId);
                                                     });
                                                 })
                                                 ->get();

            return view('activities.wizard.index', compact(
                'categories', 
                'centres', 
                'scheduleTemplates', 
                'availableActivities',
                'activeIepPlans'
            ));

        } catch (Exception $e) {
            Log::error('Error loading activity wizard: ' . $e->getMessage(), [
                'user_id' => session('id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('activities.index')
                           ->with('error', 'Unable to load activity creation wizard.');
        }
    }

    /**
     * Process wizard step validation via AJAX
     */
    public function validateStep(Request $request)
    {
        try {
            $step = $request->input('step');
            $data = $request->all();
            
            switch ($step) {
                case 1:
                    return $this->validateBasicInformation($data);
                case 2:
                    return $this->validateLearningOutcomes($data);
                case 3:
                    return $this->validateScheduleConfiguration($data);
                case 4:
                    return $this->validatePrerequisites($data);
                case 5:
                    return $this->validateIepIntegration($data);
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid step']);
            }

        } catch (Exception $e) {
            Log::error('Wizard validation error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Validation failed']);
        }
    }

    /**
     * Validate Step 1: Basic Information
     */
    private function validateBasicInformation($data)
    {
        $validator = Validator::make($data, [
            'activity_name' => 'required|string|max:255',
            'activity_description' => 'required|string|max:1000',
            'category_id' => 'required|exists:categories,id',
            'centre_id' => 'required|exists:centres,centre_id',
            'activity_type' => 'required|in:Individual,Group,Both',
            'difficulty_level' => 'required|in:Beginner,Intermediate,Advanced',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|gte:min_participants',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'age_group' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Additional validation - check for duplicate activity names in the same centre
        $existingActivity = Activity::where('activity_name', $data['activity_name'])
                                  ->where('centre_id', $data['centre_id'])
                                  ->where('is_active', true)
                                  ->first();

        if ($existingActivity) {
            return response()->json([
                'success' => false,
                'errors' => ['activity_name' => ['An active activity with this name already exists in the selected centre.']]
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate Step 2: Learning Outcomes
     */
    private function validateLearningOutcomes($data)
    {
        $validator = Validator::make($data, [
            'learning_outcomes' => 'required|array|min:1',
            'learning_outcomes.*.outcome_title' => 'required|string|max:200',
            'learning_outcomes.*.outcome_description' => 'required|string|max:500',
            'learning_outcomes.*.competency_level' => 'required|in:Beginner,Intermediate,Advanced',
            'learning_outcomes.*.assessment_criteria' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate Step 3: Schedule Configuration
     */
    private function validateScheduleConfiguration($data)
    {
        $validator = Validator::make($data, [
            'schedule_type' => 'required|in:template,custom',
            'template_id' => 'required_if:schedule_type,template|nullable|exists:activity_schedule_templates,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'venue' => 'required|string|max:100',
            'room_number' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Additional validation for custom schedules
        if ($data['schedule_type'] === 'custom') {
            $customValidator = Validator::make($data, [
                'sessions_per_week' => 'required|integer|min:1|max:7',
                'session_length_minutes' => 'required|integer|min:15|max:480',
                'days_of_week' => 'required|array|min:1',
                'session_time' => 'required|date_format:H:i'
            ]);

            if ($customValidator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $customValidator->errors()
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate Step 4: Prerequisites
     */
    private function validatePrerequisites($data)
    {
        if (isset($data['prerequisites']) && is_array($data['prerequisites'])) {
            $validator = Validator::make($data, [
                'prerequisites' => 'array',
                'prerequisites.*.prerequisite_activity_id' => 'required|exists:activities,id',
                'prerequisites.*.minimum_completion_percentage' => 'required|numeric|min:0|max:100',
                'prerequisites.*.required_competency_level' => 'required|in:Beginner,Intermediate,Advanced'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Validate Step 5: IEP Integration
     */
    private function validateIepIntegration($data)
    {
        if (isset($data['iep_goals']) && is_array($data['iep_goals'])) {
            $validator = Validator::make($data, [
                'iep_goals' => 'array',
                'iep_goals.*.iep_id' => 'required|exists:trainee_education_plans,id',
                'iep_goals.*.goal_description' => 'required|string|max:500',
                'iep_goals.*.target_completion_date' => 'required|date|after:today',
                'iep_goals.*.progress_tracking_method' => 'required|in:Attendance,Competency,Assessment'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Store the complete activity with all components
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validate all steps
            $allStepsValid = true;
            for ($step = 1; $step <= 5; $step++) {
                $validationResult = $this->validateStep(
                    new Request(array_merge($request->all(), ['step' => $step]))
                );
                
                $responseData = json_decode($validationResult->getContent(), true);
                if (!$responseData['success']) {
                    $allStepsValid = false;
                    break;
                }
            }

            if (!$allStepsValid) {
                DB::rollBack();
                return redirect()->back()
                               ->withInput()
                               ->with('error', 'Please complete all wizard steps properly.');
            }

            // Create the main activity
            $activity = $this->createActivity($request);

            // Create learning outcomes
            $this->createLearningOutcomes($activity, $request->input('learning_outcomes', []));

            // Apply schedule template or create custom schedule
            $this->createSchedule($activity, $request);

            // Create prerequisites
            $this->createPrerequisites($activity, $request->input('prerequisites', []));

            // Create IEP goal mappings
            $this->createIepGoals($activity, $request->input('iep_goals', []));

            DB::commit();

            Log::info('Activity created successfully via wizard', [
                'activity_id' => $activity->id,
                'created_by' => session('id'),
                'activity_name' => $activity->activity_name
            ]);

            return redirect()->route('activities.show', $activity->id)
                           ->with('success', 'Activity created successfully with all educational components!');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating activity via wizard: ' . $e->getMessage(), [
                'user_id' => session('id'),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create activity. Please try again.');
        }
    }

    /**
     * Create the main activity record
     */
    private function createActivity(Request $request)
    {
        return Activity::create([
            'activity_name' => $request->input('activity_name'),
            'activity_description' => $request->input('activity_description'),
            'category_id' => $request->input('category_id'),
            'centre_id' => $request->input('centre_id'),
            'activity_type' => $request->input('activity_type'),
            'difficulty_level' => $request->input('difficulty_level'),
            'min_participants' => $request->input('min_participants'),
            'max_participants' => $request->input('max_participants'),
            'duration_minutes' => $request->input('duration_minutes'),
            'age_group' => $request->input('age_group'),
            'activity_goals' => $request->input('activity_goals', ''),
            'activity_outcomes' => $request->input('activity_outcomes', ''),
            'required_resources' => json_encode($request->input('required_resources', [])),
            'created_by' => session('id'),
            'is_active' => true,
            'activity_status' => 'draft'
        ]);
    }

    /**
     * Create learning outcomes for the activity
     */
    private function createLearningOutcomes($activity, $outcomes)
    {
        foreach ($outcomes as $outcome) {
            LearningOutcome::create([
                'activity_id' => $activity->id,
                'outcome_title' => $outcome['outcome_title'],
                'outcome_description' => $outcome['outcome_description'],
                'competency_level' => $outcome['competency_level'],
                'assessment_criteria' => json_encode($outcome['assessment_criteria'])
            ]);
        }
    }

    /**
     * Create schedule based on template or custom configuration
     */
    private function createSchedule($activity, Request $request)
    {
        if ($request->input('schedule_type') === 'template') {
            // Apply schedule template
            $templateId = $request->input('template_id');
            $startDate = $request->input('start_date');
            
            ActivityTemplateApplication::create([
                'activity_id' => $activity->id,
                'template_id' => $templateId,
                'start_date' => $startDate,
                'end_date' => $request->input('end_date'),
                'customizations' => json_encode([
                    'venue' => $request->input('venue'),
                    'room_number' => $request->input('room_number')
                ])
            ]);
        }
        // Custom schedule creation would be handled here
        // Implementation depends on specific requirements
    }

    /**
     * Create activity prerequisites
     */
    private function createPrerequisites($activity, $prerequisites)
    {
        foreach ($prerequisites as $prerequisite) {
            ActivityPrerequisite::create([
                'activity_id' => $activity->id,
                'prerequisite_activity_id' => $prerequisite['prerequisite_activity_id'],
                'minimum_completion_percentage' => $prerequisite['minimum_completion_percentage'],
                'required_competency_level' => $prerequisite['required_competency_level']
            ]);
        }
    }

    /**
     * Create IEP goal mappings
     */
    private function createIepGoals($activity, $iepGoals)
    {
        foreach ($iepGoals as $goal) {
            IepActivityGoal::create([
                'iep_id' => $goal['iep_id'],
                'activity_id' => $activity->id,
                'goal_description' => $goal['goal_description'],
                'target_completion_date' => $goal['target_completion_date'],
                'progress_tracking_method' => $goal['progress_tracking_method']
            ]);
        }
    }

    /**
     * Get template preview data for AJAX
     */
    public function getTemplatePreview(Request $request)
    {
        try {
            $templateId = $request->input('template_id');
            $template = ActivityScheduleTemplate::with(['applications.activity'])
                                                ->findOrFail($templateId);

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->template_name,
                    'description' => $template->description,
                    'sessions_per_week' => $template->sessions_per_week,
                    'duration_weeks' => $template->duration_weeks,
                    'session_length_minutes' => $template->session_length_minutes,
                    'days_of_week' => $template->days_of_week,
                    'total_sessions' => $template->sessions_per_week * $template->duration_weeks,
                    'usage_count' => $template->applications->count()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template not found'
            ]);
        }
    }

    /**
     * Get suggested IEP goals based on activity category
     */
    public function getSuggestedIepGoals(Request $request)
    {
        try {
            $categoryId = $request->input('category_id');
            $centreId = session('centre_id');
            $role = session('role');

            // Get category information
            $category = Category::findOrFail($categoryId);

            // Get IEP plans that could benefit from this activity category
            $suggestedGoals = TraineeEducationPlan::with('trainee')
                                                ->where('status', 'Active')
                                                ->when($role !== 'admin', function($query) use ($centreId) {
                                                    return $query->whereHas('trainee', function($q) use ($centreId) {
                                                        $q->where('centre_id', $centreId);
                                                    });
                                                })
                                                ->get()
                                                ->map(function($plan) use ($category) {
                                                    return [
                                                        'iep_id' => $plan->id,
                                                        'trainee_name' => $plan->trainee->trainee_name,
                                                        'plan_name' => $plan->plan_name,
                                                        'suggested_goal' => $this->generateSuggestedGoal($category, $plan),
                                                        'target_date' => now()->addMonths(3)->format('Y-m-d')
                                                    ];
                                                });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestedGoals
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load suggestions'
            ]);
        }
    }

    /**
     * Generate suggested IEP goal based on category and plan
     */
    private function generateSuggestedGoal($category, $plan)
    {
        $suggestions = [
            'rehabilitation' => "Improve {$category->category_name} skills through structured activities",
            'academic' => "Develop {$category->category_name} competencies aligned with individual learning pace",
            'creative_social' => "Enhance {$category->category_name} abilities for improved social integration",
            'faith' => "Strengthen {$category->category_name} understanding and practice"
        ];

        return $suggestions[$category->category_type] ?? "Develop skills through {$category->category_name} activities";
    }
}