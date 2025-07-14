<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\RehabilitationActivity;
use App\Models\RehabilitationObjective;
use App\Models\RehabilitationResource;
use App\Models\RehabilitationStep;
use App\Models\RehabilitationMilestone;
use App\Models\RehabilitationSchedule;
use App\Models\RehabilitationParticipant;
use App\Models\Activities;
use App\Models\Trainees;
use App\Models\Centres;
use App\Models\Users;

class RehabilitationActivityController extends Controller
{
    /**
     * Display the rehabilitation categories dashboard
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        try {
            // Get rehabilitation statistics
            $rehabStats = $this->getCategoryStatistics();
            
            // Get all categories with their activities
            $categories = $this->getCategories();
            
            // Get recent activities
            $recentActivities = RehabilitationActivity::with('creator')
                ->published()
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
            
            return view('rehabilitation.categories', compact('rehabStats', 'categories', 'recentActivities'));
        } catch (\Exception $e) {
            Log::error('Error displaying rehabilitation categories: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while loading rehabilitation categories. Please try again.');
        }
    }
    
    /**
     * Get category statistics for the dashboard
     *
     * @return array
     */
    private function getCategoryStatistics()
    {
        try {
            // Get count of trainees by condition
            $trainees = Trainees::all();
            
            $stats = [
                'autism' => $trainees->where('trainee_condition', 'Autism Spectrum Disorder')->count(),
                'hearing' => $trainees->where('trainee_condition', 'Hearing Impairment')->count(),
                'visual' => $trainees->where('trainee_condition', 'Visual Impairment')->count(),
                'physical' => $trainees->where('trainee_condition', 'Physical Disability')->count(),
                'learning' => $trainees->where('trainee_condition', 'Learning Disability')->count(),
                'speech' => $trainees->where('trainee_condition', 'Speech and Language Disorder')->count(),
                'down' => $trainees->where('trainee_condition', 'Down Syndrome')->count(),
                'cerebral' => $trainees->where('trainee_condition', 'Cerebral Palsy')->count(),
                'intellectual' => $trainees->where('trainee_condition', 'Intellectual Disability')->count(),
                'multiple' => $trainees->where('trainee_condition', 'Multiple Disabilities')->count(),
                'other' => $trainees->whereNotIn('trainee_condition', [
                    'Autism Spectrum Disorder', 
                    'Hearing Impairment', 
                    'Visual Impairment', 
                    'Physical Disability', 
                    'Learning Disability', 
                    'Speech and Language Disorder',
                    'Down Syndrome',
                    'Cerebral Palsy',
                    'Intellectual Disability',
                    'Multiple Disabilities'
                ])->count()
            ];
            
            // Add activity counts for each category
            foreach (array_keys($stats) as $category) {
                $activityCount = RehabilitationActivity::where('category', $category)
                    ->published()
                    ->count();
                
                $stats[$category . '_activities'] = $activityCount;
            }
            
            // Add totals
            $stats['total_trainees'] = $trainees->count();
            $stats['total_activities'] = RehabilitationActivity::published()->count();
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error getting category statistics: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all categories with their info
     *
     * @return array
     */
    private function getCategories()
    {
        return [
            'autism' => [
                'id' => 'autism',
                'name' => 'Autism Spectrum',
                'icon' => 'brain',
                'icon_class' => 'autism',
                'description' => 'Activities designed for individuals with Autism Spectrum Disorder, focusing on social, communication, and behavioral skills.',
                'color' => 'linear-gradient(135deg, #4facfe, #00f2fe)',
                'badge_color' => 'primary'
            ],
            'physical' => [
                'id' => 'physical',
                'name' => 'Physical Disabilities',
                'icon' => 'walking',
                'icon_class' => 'physical',
                'description' => 'Activities for individuals with physical disabilities, focusing on mobility, strength, and coordination.',
                'color' => 'linear-gradient(135deg, #f6d365, #fda085)',
                'badge_color' => 'success'
            ],
            'speech' => [
                'id' => 'speech',
                'name' => 'Speech & Language',
                'icon' => 'comments',
                'icon_class' => 'speech',
                'description' => 'Activities to improve speech articulation, language comprehension, and expression.',
                'color' => 'linear-gradient(135deg, #a18cd1, #fbc2eb)',
                'badge_color' => 'info'
            ],
            'visual' => [
                'id' => 'visual',
                'name' => 'Visual Impairment',
                'icon' => 'eye',
                'icon_class' => 'visual',
                'description' => 'Activities for individuals with visual impairments, focusing on orientation, mobility, and adaptation skills.',
                'color' => 'linear-gradient(135deg, #33ccff, #00c49a)',
                'badge_color' => 'warning'
            ],
            'hearing' => [
                'id' => 'hearing',
                'name' => 'Hearing Impairment',
                'icon' => 'deaf',
                'icon_class' => 'hearing',
                'description' => 'Activities for individuals with hearing impairments, focusing on alternative communication methods and auditory training.',
                'color' => 'linear-gradient(135deg, #ff9a9e, #fad0c4)',
                'badge_color' => 'danger'
            ],
            'learning' => [
                'id' => 'learning',
                'name' => 'Learning Disabilities',
                'icon' => 'book-reader',
                'icon_class' => 'learning',
                'description' => 'Activities for individuals with learning disabilities, focusing on academic skills and cognitive development.',
                'color' => 'linear-gradient(135deg, #c850c0, #4158d0)',
                'badge_color' => 'secondary'
            ],
            'down' => [
                'id' => 'down',
                'name' => 'Down Syndrome',
                'icon' => 'puzzle-piece',
                'icon_class' => 'down',
                'description' => 'Activities tailored for individuals with Down Syndrome, addressing cognitive, physical, and social development needs.',
                'color' => 'linear-gradient(135deg, #fddb92, #d1fdff)',
                'badge_color' => 'warning'
            ],
            'cerebral' => [
                'id' => 'cerebral',
                'name' => 'Cerebral Palsy',
                'icon' => 'hands-helping',
                'icon_class' => 'cerebral',
                'description' => 'Activities designed for individuals with Cerebral Palsy, focusing on motor skills, coordination, and mobility.',
                'color' => 'linear-gradient(135deg, #9890e3, #b1f4cf)',
                'badge_color' => 'info'
            ],
            'intellectual' => [
                'id' => 'intellectual',
                'name' => 'Intellectual Disability',
                'icon' => 'lightbulb',
                'icon_class' => 'intellectual',
                'description' => 'Activities for individuals with intellectual disabilities, focusing on adaptive skills, functional academics, and daily living.',
                'color' => 'linear-gradient(135deg, #a8edea, #fed6e3)',
                'badge_color' => 'primary'
            ],
            'multiple' => [
                'id' => 'multiple',
                'name' => 'Multiple Disabilities',
                'icon' => 'layer-group',
                'icon_class' => 'multiple',
                'description' => 'Activities designed for individuals with multiple disabilities, featuring adaptable approaches to address diverse needs simultaneously.',
                'color' => 'linear-gradient(135deg, #5ee7df, #b490ca)',
                'badge_color' => 'dark'
            ]
        ];
    }
    
    /**
     * Display activities in a specific category
     *
     * @param string $category
     * @return \Illuminate\View\View
     */
    public function categoryActivities($category)
    {
        try {
            $categories = $this->getCategories();
            
            if (!isset($categories[$category])) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Category not found.');
            }
            
            $categoryInfo = $categories[$category];
            
            // Get activities in this category
            $activities = RehabilitationActivity::where('category', $category)
                ->published()
                ->orderBy('name')
                ->paginate(12);
            
            // Get trainee count for this category
            $traineesCount = Trainees::where('trainee_condition', $categoryInfo['name'])
                ->count();
            
            return view('rehabilitation.category', compact('categoryInfo', 'activities', 'traineesCount'));
        } catch (\Exception $e) {
            Log::error('Error displaying category activities: ' . $e->getMessage(), [
                'category' => $category,
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading category activities. Please try again.');
        }
    }
    
    /**
     * Display a listing of all rehabilitation activities
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Build query with filters
            $query = RehabilitationActivity::query()->with('creator');
            
            // Apply category filter
            if ($request->filled('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }
            
            // Apply difficulty filter
            if ($request->filled('difficulty')) {
                $query->where('difficulty_level', $request->difficulty);
            }
            
            // Apply age range filter
            if ($request->filled('age_range')) {
                $query->where('age_range', $request->age_range);
            }
            
            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%")
                      ->orWhere('full_description', 'like', "%{$search}%");
                });
            }
            
            // Apply status filter (default to published)
            $query->where('status', $request->status ?? 'published');
            
            // Get paginated results
            $activities = $query->orderBy('name')->paginate(10);
            
            // Get categories, difficulty levels and age ranges for filter dropdowns
            $categories = $this->getCategories();
            
            $difficultyLevels = [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ];
            
            $ageRanges = [
                '0-3' => 'Early Childhood (0-3)',
                '4-6' => 'Preschool (4-6)',
                '7-12' => 'School Age (7-12)',
                '13-18' => 'Adolescent (13-18)',
                'all' => 'All Ages'
            ];
            
            return view('rehabilitation.activities.index', compact(
                'activities',
                'categories',
                'difficultyLevels',
                'ageRanges',
                'request'
            ));
        } catch (\Exception $e) {
            Log::error('Error displaying rehabilitation activities: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading activities. Please try again.');
        }
    }
    
    /**
     * Show the form for creating a new activity
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to create activities.');
        }
        
        try {
            // Get categories for dropdown
            $categories = $this->getCategories();
            
            // Get data for dropdown menus
            $difficultyLevels = [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ];
            
            $ageRanges = [
                '0-3' => 'Early Childhood (0-3)',
                '4-6' => 'Preschool (4-6)',
                '7-12' => 'School Age (7-12)',
                '13-18' => 'Adolescent (13-18)',
                'all' => 'All Ages'
            ];
            
            $activityTypes = [
                'individual' => 'Individual',
                'group' => 'Group',
                'both' => 'Both Individual & Group'
            ];
            
            return view('rehabilitation.activities.create', compact(
                'categories',
                'difficultyLevels',
                'ageRanges',
                'activityTypes'
            ));
        } catch (\Exception $e) {
            Log::error('Error accessing activity creation form: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading the creation form. Please try again.');
        }
    }
    
    /**
     * Store a newly created activity
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to create activities.');
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'short_description' => 'required|string|max:255',
            'full_description' => 'required|string',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'age_range' => 'required|string|max:50',
            'activity_type' => 'required|in:individual,group,both',
            'duration' => 'required|integer|min:5|max:180',
            'max_participants' => 'nullable|integer|min:1|max:50',
            'objectives' => 'required|array|min:1',
            'objectives.*' => 'required|string',
            'resources' => 'required|array|min:1',
            'resources.*' => 'required|string',
            'step_titles' => 'required|array|min:1',
            'step_titles.*' => 'required|string',
            'step_descriptions' => 'required|array|min:1',
            'step_descriptions.*' => 'required|string',
            'lower_adaptations' => 'nullable|string',
            'higher_adaptations' => 'nullable|string',
            'progress_metrics' => 'required|string',
            'milestones' => 'required|array|min:1',
            'milestones.*' => 'required|string',
            'notes' => 'nullable|string',
            'published' => 'nullable'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Create the activity
            $activity = new RehabilitationActivity();
            $activity->name = $request->name;
            $activity->category = $request->category;
            $activity->short_description = $request->short_description;
            $activity->full_description = $request->full_description;
            $activity->difficulty_level = $request->difficulty_level;
            $activity->age_range = $request->age_range;
            $activity->activity_type = $request->activity_type;
            $activity->duration = $request->duration;
            $activity->max_participants = $request->max_participants;
            $activity->lower_adaptations = $request->lower_adaptations;
            $activity->higher_adaptations = $request->higher_adaptations;
            $activity->progress_metrics = $request->progress_metrics;
            $activity->notes = $request->notes;
            $activity->status = $request->has('published') ? 'published' : 'draft';
            $activity->created_by = session('id');
            $activity->updated_by = session('id');
            $activity->save();
            
            // Save objectives
            foreach ($request->objectives as $index => $objective) {
                $obj = new RehabilitationObjective();
                $obj->activity_id = $activity->id;
                $obj->description = $objective;
                $obj->order = $index + 1;
                $obj->save();
            }
            
            // Save resources
            foreach ($request->resources as $index => $resource) {
                $res = new RehabilitationResource();
                $res->activity_id = $activity->id;
                $res->name = $resource;
                $res->save();
            }
            
            // Save steps
            foreach ($request->step_titles as $index => $title) {
                $step = new RehabilitationStep();
                $step->activity_id = $activity->id;
                $step->title = $title;
                $step->description = $request->step_descriptions[$index] ?? '';
                $step->order = $index + 1;
                $step->save();
            }
            
            // Save milestones
            foreach ($request->milestones as $index => $milestone) {
                $ms = new RehabilitationMilestone();
                $ms->activity_id = $activity->id;
                $ms->description = $milestone;
                $ms->order = $index + 1;
                $ms->save();
            }
            
            DB::commit();
            
            Log::info('Rehabilitation activity created successfully', [
                'id' => $activity->id,
                'name' => $activity->name,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.activities.show', $activity->id)
                ->with('success', 'Activity created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating rehabilitation activity: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while creating the activity. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified activity
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        try {
            $activity = RehabilitationActivity::with([
                'creator',
                'objectives',
                'resources',
                'steps',
                'milestones'
            ])->findOrFail($id);
            
            // Get related activities in the same category
            $relatedActivities = RehabilitationActivity::where('id', '!=', $id)
                ->where('category', $activity->category)
                ->published()
                ->orderBy('name')
                ->take(5)
                ->get();
            
            // Get activity statistics
            $stats = $this->getActivityStatistics($id);
            
            // Get categories for reference
            $categories = $this->getCategories();
            $categoryInfo = $categories[$activity->category] ?? null;
            
            return view('rehabilitation.activities.show', compact(
                'activity',
                'relatedActivities',
                'stats',
                'categoryInfo'
            ));
        } catch (\Exception $e) {
            Log::error('Error displaying activity: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.activities.index')
                ->with('error', 'An error occurred while loading the activity. Please try again.');
        }
    }
    
    /**
     * Get activity statistics
     *
     * @param int $id
     * @return array
     */
    private function getActivityStatistics($id)
    {
        try {
            // Get number of schedules for this activity
            $schedulesCount = RehabilitationSchedule::where('activity_id', $id)->count();
            
            // Get number of trainee activities based on this template
            $traineeActivitiesCount = Activities::where('rehab_activity_id', $id)->count();
            
            // Get count of unique trainees
            $uniqueTraineesCount = RehabilitationParticipant::whereHas('schedule', function($query) use ($id) {
                $query->where('activity_id', $id);
            })->distinct('trainee_id')->count();
            
            // Get average duration
            $avgDuration = RehabilitationSchedule::where('activity_id', $id)
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as avg_duration')
                ->first();
            
            $avgDurationMinutes = $avgDuration ? round($avgDuration->avg_duration) : 0;
            
            // Get latest schedule
            $latestSchedule = RehabilitationSchedule::where('activity_id', $id)
                ->orderBy('start_time', 'desc')
                ->first();
            
            $lastUsed = $latestSchedule ? $latestSchedule->start_time->diffForHumans() : 'Never';
            
            // Calculate success rate based on progress records
            $progressRecords = RehabilitationParticipant::whereHas('schedule', function($query) use ($id) {
                $query->where('activity_id', $id);
            })->whereNotNull('progress_rating')->get();
            
            $successRate = $progressRecords->count() > 0 
                ? round(($progressRecords->where('progress_rating', '>=', 3)->count() / $progressRecords->count()) * 100) 
                : 0;
            
            return [
                'total_sessions' => $schedulesCount,
                'trainees' => $uniqueTraineesCount,
                'avg_session_duration' => $avgDurationMinutes,
                'success_rate' => $successRate . '%',
                'last_used' => $lastUsed,
                'trainee_activities' => $traineeActivitiesCount
            ];
        } catch (\Exception $e) {
            Log::error('Error getting activity statistics: ' . $e->getMessage(), [
                'activity_id' => $id
            ]);
            
            return [
                'total_sessions' => 0,
                'trainees' => 0,
                'avg_session_duration' => 0,
                'success_rate' => '0%',
                'last_used' => 'Never',
                'trainee_activities' => 0
            ];
        }
    }
    
    /**
     * Show the form for editing the specified activity
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to edit activities.');
        }
        
        try {
            $activity = RehabilitationActivity::with([
                'objectives',
                'resources',
                'steps',
                'milestones'
            ])->findOrFail($id);
            
            // Get data for dropdown menus
            $categories = $this->getCategories();
            
            $difficultyLevels = [
                'easy' => 'Easy',
                'medium' => 'Medium',
                'hard' => 'Hard'
            ];
            
            $ageRanges = [
                '0-3' => 'Early Childhood (0-3)',
                '4-6' => 'Preschool (4-6)',
                '7-12' => 'School Age (7-12)',
                '13-18' => 'Adolescent (13-18)',
                'all' => 'All Ages'
            ];
            
            $activityTypes = [
                'individual' => 'Individual',
                'group' => 'Group',
                'both' => 'Both Individual & Group'
            ];
            
            return view('rehabilitation.activities.edit', compact(
                'activity',
                'categories',
                'difficultyLevels',
                'ageRanges',
                'activityTypes'
            ));
        } catch (\Exception $e) {
            Log::error('Error accessing activity edit form: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.activities.index')
                ->with('error', 'An error occurred while loading the edit form. Please try again.');
        }
    }
    
    /**
     * Update the specified activity
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to update activities.');
        }
        
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'short_description' => 'required|string|max:255',
            'full_description' => 'required|string',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'age_range' => 'required|string|max:50',
            'activity_type' => 'required|in:individual,group,both',
            'duration' => 'required|integer|min:5|max:180',
            'max_participants' => 'nullable|integer|min:1|max:50',
            'objectives' => 'required|array|min:1',
            'objectives.*' => 'required|string',
            'resources' => 'required|array|min:1',
            'resources.*' => 'required|string',
            'step_titles' => 'required|array|min:1',
            'step_titles.*' => 'required|string',
            'step_descriptions' => 'required|array|min:1',
            'step_descriptions.*' => 'required|string',
            'lower_adaptations' => 'nullable|string',
            'higher_adaptations' => 'nullable|string',
            'progress_metrics' => 'required|string',
            'milestones' => 'required|array|min:1',
            'milestones.*' => 'required|string',
            'notes' => 'nullable|string',
            'published' => 'nullable'
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Get the activity
            $activity = RehabilitationActivity::findOrFail($id);
            
            // Update the activity
            $activity->name = $request->name;
            $activity->category = $request->category;
            $activity->short_description = $request->short_description;
            $activity->full_description = $request->full_description;
            $activity->difficulty_level = $request->difficulty_level;
            $activity->age_range = $request->age_range;
            $activity->activity_type = $request->activity_type;
            $activity->duration = $request->duration;
            $activity->max_participants = $request->max_participants;
            $activity->lower_adaptations = $request->lower_adaptations;
            $activity->higher_adaptations = $request->higher_adaptations;
            $activity->progress_metrics = $request->progress_metrics;
            $activity->notes = $request->notes;
            $activity->status = $request->has('published') ? 'published' : 'draft';
            $activity->updated_by = session('id');
            $activity->save();
            
            // Clear existing related items
            RehabilitationObjective::where('activity_id', $id)->delete();
            RehabilitationResource::where('activity_id', $id)->delete();
            RehabilitationStep::where('activity_id', $id)->delete();
            RehabilitationMilestone::where('activity_id', $id)->delete();
            
            // Save objectives
            foreach ($request->objectives as $index => $objective) {
                $obj = new RehabilitationObjective();
                $obj->activity_id = $activity->id;
                $obj->description = $objective;
                $obj->order = $index + 1;
                $obj->save();
            }
            
            // Save resources
            foreach ($request->resources as $index => $resource) {
                $res = new RehabilitationResource();
                $res->activity_id = $activity->id;
                $res->name = $resource;
                $res->save();
            }
            
            // Save steps
            foreach ($request->step_titles as $index => $title) {
                $step = new RehabilitationStep();
                $step->activity_id = $activity->id;
                $step->title = $title;
                $step->description = $request->step_descriptions[$index] ?? '';
                $step->order = $index + 1;
                $step->save();
            }
            
            // Save milestones
            foreach ($request->milestones as $index => $milestone) {
                $ms = new RehabilitationMilestone();
                $ms->activity_id = $activity->id;
                $ms->description = $milestone;
                $ms->order = $index + 1;
                $ms->save();
            }
            
            DB::commit();
            
            Log::info('Rehabilitation activity updated successfully', [
                'id' => $activity->id,
                'name' => $activity->name,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.activities.show', $activity->id)
                ->with('success', 'Activity updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating rehabilitation activity: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while updating the activity. Please try again.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified activity
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to delete activities.');
        }
        
        try {
            $activity = RehabilitationActivity::findOrFail($id);
            
            // Check if activity is currently being used in any schedules
            $schedulesCount = RehabilitationSchedule::where('activity_id', $id)->count();
            
            if ($schedulesCount > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete this activity as it is currently scheduled. Please remove all scheduled sessions first.');
            }
            
            // Check if activity is used in any trainee activities
            $traineeActivitiesCount = Activities::where('rehab_activity_id', $id)->count();
            
            if ($traineeActivitiesCount > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete this activity as it is being used in trainee activities. Please remove those activities first.');
            }
            
            // Delete related records
            RehabilitationObjective::where('activity_id', $id)->delete();
            RehabilitationResource::where('activity_id', $id)->delete();
            RehabilitationStep::where('activity_id', $id)->delete();
            RehabilitationMilestone::where('activity_id', $id)->delete();
            
            // Delete the activity
            $activity->delete();
            
            Log::info('Rehabilitation activity deleted successfully', [
                'id' => $id,
                'name' => $activity->name,
                'user_id' => session('id')
            ]);
            
            return redirect()->route('rehabilitation.activities.index')
                ->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting rehabilitation activity: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the activity. Please try again.');
        }
    }
    
    /**
     * Check if the current user can manage activities
     *
     * @return bool
     */
    private function canManageActivities()
    {
        $role = session('role');
        return in_array($role, ['admin', 'supervisor']);
    }
}