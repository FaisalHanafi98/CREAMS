<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Activity;
use App\Models\RehabilitationCategory;
use App\Models\Trainees;

class ActivityController extends Controller
{
    /**
     * Display a listing of rehabilitation categories
     *
     * @return \Illuminate\View\View
     */
    public function categories()
    {
        Log::info('Accessing rehabilitation categories');
        
        try {
            // Get all categories with their activities
            $categories = $this->getCategoriesWithActivities();
            
            // Get statistics
            $stats = $this->getCategoryStatistics();
            
            return view('rehabilitation.categories', compact('categories', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error displaying rehabilitation categories: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'An error occurred while loading rehabilitation categories. Please try again.');
        }
    }
    
    /**
     * Display activities for a specific category
     *
     * @param string $category
     * @return \Illuminate\View\View
     */
    public function categoryShow($category)
    {
        Log::info('Accessing category details', ['category' => $category]);
        
        try {
            // Get category information
            $categoryInfo = $this->getCategoryInfo($category);
            
            if (!$categoryInfo) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Category not found.');
            }
            
            // Get activities for this category
            $activities = $this->getActivitiesByCategory($category);
            
            return view('rehabilitation.category', compact('categoryInfo', 'activities'));
        } catch (\Exception $e) {
            Log::error('Error displaying category: ' . $e->getMessage());
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading category details. Please try again.');
        }
    }
    
    /**
     * Show the form for creating a new activity
     *
     * @return \Illuminate\View\View
     */
    public function createActivity()
    {
        Log::info('Accessing create activity form');
        
        // Check if user has permission
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to create activities.');
        }
        
        // Get categories for dropdown
        $categories = $this->getCategories();
        
        // Get available resources
        $resources = $this->getAvailableResources();
        
        return view('rehabilitation.activities.create', compact('categories', 'resources'));
    }
    
    /**
     * Store a newly created activity
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeActivity(Request $request)
    {
        Log::info('Attempting to store new activity');
        
        // Check if user has permission
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to create activities.');
        }
        
        // Validate request
        $validator = $this->validateActivityRequest($request);
        
        if ($validator->fails()) {
            return redirect()->route('rehabilitation.activities.create')
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Create new activity
            $activity = $this->createActivityFromRequest($request);
            
            Log::info('Activity created successfully', ['id' => $activity->id]);
            
            return redirect()->route('rehabilitation.activities.show', $activity->id)
                ->with('success', 'Activity created successfully.');
        } catch (\Exception $e) {
            Log::error('Error storing activity: ' . $e->getMessage());
            return redirect()->route('rehabilitation.activities.create')
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
    public function showActivity($id)
    {
        Log::info('Accessing activity details', ['id' => $id]);
        
        try {
            // Get activity details
            $activity = $this->getActivityById($id);
            
            if (!$activity) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Activity not found.');
            }
            
            // Get related activities
            $relatedActivities = $this->getRelatedActivities($activity);
            
            // Get activity statistics
            $activityStats = $this->getActivityStatistics($id);
            
            return view('rehabilitation.activities.show', compact('activity', 'relatedActivities', 'activityStats'));
        } catch (\Exception $e) {
            Log::error('Error displaying activity: ' . $e->getMessage());
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading activity details. Please try again.');
        }
    }
    
    /**
     * Show the form for editing the specified activity
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function editActivity($id)
    {
        Log::info('Accessing edit activity form', ['id' => $id]);
        
        // Check if user has permission
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to edit activities.');
        }
        
        try {
            // Get activity details
            $activity = $this->getActivityById($id);
            
            if (!$activity) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Activity not found.');
            }
            
            // Get categories for dropdown
            $categories = $this->getCategories();
            
            // Get available resources
            $resources = $this->getAvailableResources();
            
            return view('rehabilitation.activities.edit', compact('activity', 'categories', 'resources'));
        } catch (\Exception $e) {
            Log::error('Error accessing edit form: ' . $e->getMessage());
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'An error occurred while loading the edit form. Please try again.');
        }
    }
    
    /**
     * Update the specified activity
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateActivity(Request $request, $id)
    {
        Log::info('Attempting to update activity', ['id' => $id]);
        
        // Check if user has permission
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to update activities.');
        }
        
        // Validate request
        $validator = $this->validateActivityRequest($request, $id);
        
        if ($validator->fails()) {
            return redirect()->route('rehabilitation.activities.edit', $id)
                ->withErrors($validator)
                ->withInput();
        }
        
        try {
            // Get activity
            $activity = $this->getActivityById($id);
            
            if (!$activity) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Activity not found.');
            }
            
            // Update activity
            $this->updateActivityFromRequest($activity, $request);
            
            Log::info('Activity updated successfully', ['id' => $id]);
            
            return redirect()->route('rehabilitation.activities.show', $id)
                ->with('success', 'Activity updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating activity: ' . $e->getMessage());
            return redirect()->route('rehabilitation.activities.edit', $id)
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
    public function destroyActivity($id)
    {
        Log::info('Attempting to delete activity', ['id' => $id]);
        
        // Check if user has permission
        if (!$this->canManageActivities()) {
            return redirect()->route('rehabilitation.categories')
                ->with('error', 'You do not have permission to delete activities.');
        }
        
        try {
            // Get activity
            $activity = $this->getActivityById($id);
            
            if (!$activity) {
                return redirect()->route('rehabilitation.categories')
                    ->with('error', 'Activity not found.');
            }
            
            // Delete activity
            $activity->delete();
            
            Log::info('Activity deleted successfully', ['id' => $id]);
            
            return redirect()->route('rehabilitation.categories')
                ->with('success', 'Activity deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting activity: ' . $e->getMessage());
            return redirect()->route('rehabilitation.activities.show', $id)
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
    
    /**
     * Validate activity request
     *
     * @param \Illuminate\Http\Request $request
     * @param int|null $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateActivityRequest(Request $request, $id = null)
    {
        $rules = [
            'activity_name' => 'required|string|max:255',
            'category' => 'required|string',
            'short_description' => 'required|string|max:255',
            'difficulty_level' => 'required|in:easy,medium,hard',
            'age_range' => 'required|string',
            'activity_type' => 'required|in:individual,group,both',
            'duration' => 'required|integer|min:5|max:120',
            'max_participants' => 'nullable|integer|min:1|max:50',
            'full_description' => 'required|string',
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
            'published' => 'nullable|boolean'
        ];
        
        return Validator::make($request->all(), $rules);
    }
    
    /**
     * Create a new activity from request
     *
     * @param \Illuminate\Http\Request $request
     * @return \App\Models\Activity
     */
    private function createActivityFromRequest(Request $request)
    {
        // Create new activity
        $activity = new Activity();
        
        // Set basic properties
        $activity->name = $request->activity_name;
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
        $activity->published = $request->has('published');
        $activity->created_by = session('id');
        
        // Save activity
        $activity->save();
        
        // Set objectives
        $this->saveActivityObjectives($activity, $request->objectives);
        
        // Set resources
        $this->saveActivityResources($activity, $request->resources);
        
        // Set implementation steps
        $this->saveActivitySteps($activity, $request->step_titles, $request->step_descriptions);
        
        // Set milestones
        $this->saveActivityMilestones($activity, $request->milestones);
        
        return $activity;
    }
    
    /**
     * Update an activity from request
     *
     * @param \App\Models\Activity $activity
     * @param \Illuminate\Http\Request $request
     * @return \App\Models\Activity
     */
    private function updateActivityFromRequest(Activity $activity, Request $request)
    {
        // Update basic properties
        $activity->name = $request->activity_name;
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
        $activity->published = $request->has('published');
        $activity->updated_by = session('id');
        
        // Save activity
        $activity->save();
        
        // Clear and update objectives
        $this->deleteActivityObjectives($activity);
        $this->saveActivityObjectives($activity, $request->objectives);
        
        // Clear and update resources
        $this->deleteActivityResources($activity);
        $this->saveActivityResources($activity, $request->resources);
        
        // Clear and update implementation steps
        $this->deleteActivitySteps($activity);
        $this->saveActivitySteps($activity, $request->step_titles, $request->step_descriptions);
        
        // Clear and update milestones
        $this->deleteActivityMilestones($activity);
        $this->saveActivityMilestones($activity, $request->milestones);
        
        return $activity;
    }
    
    /**
     * Get categories with their activities
     *
     * @return array
     */
    private function getCategoriesWithActivities()
    {
        // For now, return hardcoded categories and activities
        // This would normally be retrieved from the database
        
        return [
            'autism' => [
                'name' => 'Autism Spectrum',
                'icon' => 'brain',
                'icon_class' => 'autism',
                'activities_count' => 12,
                'sessions_count' => 48,
                'trainees_count' => 24,
                'activities' => [
                    [
                        'id' => 1,
                        'name' => 'Social Cue Recognition',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Sensory Integration Exercise',
                        'difficulty' => 'easy'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Communication Board Usage',
                        'difficulty' => 'medium'
                    ]
                ]
            ],
            'physical' => [
                'name' => 'Physical Disabilities',
                'icon' => 'walking',
                'icon_class' => 'physical',
                'activities_count' => 9,
                'sessions_count' => 36,
                'trainees_count' => 18,
                'activities' => [
                    [
                        'id' => 4,
                        'name' => 'Motor Skills Development',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 5,
                        'name' => 'Grip Strength Exercise',
                        'difficulty' => 'easy'
                    ],
                    [
                        'id' => 6,
                        'name' => 'Mobility Training',
                        'difficulty' => 'hard'
                    ]
                ]
            ],
            'speech' => [
                'name' => 'Speech & Language',
                'icon' => 'comments',
                'icon_class' => 'speech',
                'activities_count' => 15,
                'sessions_count' => 45,
                'trainees_count' => 30,
                'activities' => [
                    [
                        'id' => 7,
                        'name' => 'Articulation Practice',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 8,
                        'name' => 'Vocabulary Building',
                        'difficulty' => 'easy'
                    ],
                    [
                        'id' => 9,
                        'name' => 'Fluency Training',
                        'difficulty' => 'hard'
                    ]
                ]
            ],
            'visual' => [
                'name' => 'Visual Impairment',
                'icon' => 'eye',
                'icon_class' => 'visual',
                'activities_count' => 8,
                'sessions_count' => 24,
                'trainees_count' => 12,
                'activities' => [
                    [
                        'id' => 10,
                        'name' => 'Braille Reading',
                        'difficulty' => 'hard'
                    ],
                    [
                        'id' => 11,
                        'name' => 'Tactile Discrimination',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 12,
                        'name' => 'Orientation & Mobility',
                        'difficulty' => 'medium'
                    ]
                ]
            ],
            'hearing' => [
                'name' => 'Hearing Impairment',
                'icon' => 'deaf',
                'icon_class' => 'hearing',
                'activities_count' => 10,
                'sessions_count' => 30,
                'trainees_count' => 15,
                'activities' => [
                    [
                        'id' => 13,
                        'name' => 'Sign Language Basics',
                        'difficulty' => 'easy'
                    ],
                    [
                        'id' => 14,
                        'name' => 'Lip Reading Practice',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 15,
                        'name' => 'Auditory Training',
                        'difficulty' => 'hard'
                    ]
                ]
            ],
            'learning' => [
                'name' => 'Learning Disabilities',
                'icon' => 'book-reader',
                'icon_class' => 'learning',
                'activities_count' => 14,
                'sessions_count' => 42,
                'trainees_count' => 28,
                'activities' => [
                    [
                        'id' => 16,
                        'name' => 'Reading Comprehension',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 17,
                        'name' => 'Math Skills Development',
                        'difficulty' => 'medium'
                    ],
                    [
                        'id' => 18,
                        'name' => 'Memory Enhancement',
                        'difficulty' => 'easy'
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Get category statistics
     *
     * @return array
     */
    private function getCategoryStatistics()
    {
        // This would normally be retrieved from the database
        return [
            'total_categories' => 6,
            'total_activities' => 68,
            'total_sessions' => 225,
            'total_trainees' => 127,
            'most_popular_category' => 'Speech & Language',
            'most_difficult_category' => 'Visual Impairment',
            'recent_activities' => [
                [
                    'id' => 1,
                    'name' => 'Social Cue Recognition',
                    'category' => 'Autism Spectrum',
                    'created_at' => now()->subDays(2)
                ],
                [
                    'id' => 7,
                    'name' => 'Articulation Practice',
                    'category' => 'Speech & Language',
                    'created_at' => now()->subDays(5)
                ],
                [
                    'id' => 13,
                    'name' => 'Sign Language Basics',
                    'category' => 'Hearing Impairment',
                    'created_at' => now()->subDays(7)
                ]
            ]
        ];
    }
    
    /**
     * Get category information
     *
     * @param string $category
     * @return array|null
     */
    private function getCategoryInfo($category)
    {
        $categories = [
            'autism' => [
                'id' => 'autism',
                'name' => 'Autism Spectrum',
                'icon' => 'brain',
                'icon_class' => 'autism',
                'description' => 'Activities designed for individuals with Autism Spectrum Disorder, focusing on social, communication, and behavioral skills.',
                'color' => 'linear-gradient(135deg, #4facfe, #00f2fe)'
            ],
            'physical' => [
                'id' => 'physical',
                'name' => 'Physical Disabilities',
                'icon' => 'walking',
                'icon_class' => 'physical',
                'description' => 'Activities for individuals with physical disabilities, focusing on mobility, strength, and coordination.',
                'color' => 'linear-gradient(135deg, #f6d365, #fda085)'
            ],
            'speech' => [
                'id' => 'speech',
                'name' => 'Speech & Language',
                'icon' => 'comments',
                'icon_class' => 'speech',
                'description' => 'Activities to improve speech articulation, language comprehension, and expression.',
                'color' => 'linear-gradient(135deg, #a18cd1, #fbc2eb)'
            ],
            'visual' => [
                'id' => 'visual',
                'name' => 'Visual Impairment',
                'icon' => 'eye',
                'icon_class' => 'visual',
                'description' => 'Activities for individuals with visual impairments, focusing on orientation, mobility, and adaptation skills.',
                'color' => 'linear-gradient(135deg, #33ccff, #00c49a)'
            ],
            'hearing' => [
                'id' => 'hearing',
                'name' => 'Hearing Impairment',
                'icon' => 'deaf',
                'icon_class' => 'hearing',
                'description' => 'Activities for individuals with hearing impairments, focusing on alternative communication methods and auditory training.',
                'color' => 'linear-gradient(135deg, #ff9a9e, #fad0c4)'
            ],
            'learning' => [
                'id' => 'learning',
                'name' => 'Learning Disabilities',
                'icon' => 'book-reader',
                'icon_class' => 'learning',
                'description' => 'Activities for individuals with learning disabilities, focusing on academic skills and cognitive development.',
                'color' => 'linear-gradient(135deg, #c850c0, #4158d0)'
            ]
        ];
        
        return $categories[$category] ?? null;
    }
    
    /**
     * Get activities by category
     *
     * @param string $category
     * @return array
     */
    private function getActivitiesByCategory($category)
    {
        // This would normally be retrieved from the database
        $allActivities = $this->getCategoriesWithActivities();
        return $allActivities[$category]['activities'] ?? [];
    }
    
    /**
     * Get all categories
     *
     * @return array
     */
    private function getCategories()
    {
        return [
            'autism' => 'Autism Spectrum',
            'physical' => 'Physical Disabilities',
            'speech' => 'Speech & Language',
            'visual' => 'Visual Impairment',
            'hearing' => 'Hearing Impairment',
            'learning' => 'Learning Disabilities'
        ];
    }
    
    /**
     * Get available resources
     *
     * @return array
     */
    private function getAvailableResources()
    {
        return [
            'visual_aids' => 'Visual Aids',
            'audio_materials' => 'Audio Materials',
            'manipulatives' => 'Manipulatives',
            'sensory_items' => 'Sensory Items',
            'technology' => 'Technology',
            'games' => 'Games',
            'books' => 'Books',
            'flashcards' => 'Flashcards',
            'worksheets' => 'Worksheets'
        ];
    }
    
    /**
     * Get activity by ID
     *
     * @param int $id
     * @return \App\Models\Activity|array|null
     */
    private function getActivityById($id)
    {
        // This would normally be retrieved from the database
        // For now, using hardcoded data for demonstration
        
        if ($id == 1) {
            return [
                'id' => 1,
                'name' => 'Social Cue Recognition',
                'category' => 'autism',
                'category_name' => 'Autism Spectrum',
                'short_description' => 'Helps children recognize and interpret social cues and facial expressions',
                'full_description' => "This activity is designed to help children with autism spectrum disorders to better recognize and interpret social cues and facial expressions. Using a combination of visual aids, roleplay, and interactive games, children learn to identify different emotions and appropriate social responses.\n\nThe activity is structured to provide gradual exposure and practice in a supportive environment, with opportunities for immediate feedback and reinforcement.",
                'difficulty_level' => 'medium',
                'age_range' => '6-12 years',
                'activity_type' => 'group',
                'duration' => 45,
                'max_participants' => 8,
                'created_by' => 'Dr. Nurul Hafizah',
                'created_at' => '2023-03-15',
                'objectives' => [
                    'Recognize and identify basic emotions from facial expressions (happy, sad, angry, scared, surprised)',
                    'Understand the connection between emotions and corresponding social situations',
                    'Develop appropriate responses to different social cues',
                    'Practice initiating and maintaining simple social interactions'
                ],
                'resources' => [
                    'Emotion Flashcards',
                    'Video Materials',
                    'Social Scenario Cards',
                    'Emotion Matching Game',
                    'Social Stories Book'
                ],
                'steps' => [
                    [
                        'title' => 'Introduction',
                        'description' => 'Begin with a brief introduction about emotions and why understanding them is important in our daily interactions.',
                        'duration' => 5
                    ],
                    [
                        'title' => 'Emotion Recognition',
                        'description' => 'Use flashcards to help children identify basic emotions. Ask them to mimic the expressions shown on the cards.',
                        'duration' => 10
                    ],
                    [
                        'title' => 'Social Scenarios',
                        'description' => 'Present different social scenarios and ask children to identify appropriate emotional responses. Use visual aids and role-playing to reinforce learning.',
                        'duration' => 15
                    ],
                    [
                        'title' => 'Interactive Game',
                        'description' => 'Play an emotion matching game where children match situations with appropriate emotional responses.',
                        'duration' => 10
                    ],
                    [
                        'title' => 'Reflection and Feedback',
                        'description' => 'Discuss what children learned and provide positive reinforcement for participation and progress.',
                        'duration' => 5
                    ]
                ],
                'lower_adaptations' => 'Focus on identifying only 2-3 basic emotions with more visual supports and physical prompting. Increase session frequency rather than duration.',
                'higher_adaptations' => 'Include more complex emotions (such as confused, embarrassed, proud) and more nuanced social scenarios. Add perspective-taking components.',
                'progress_metrics' => "Track progress through observation checklists that measure:\n- Accuracy in identifying emotions (baseline vs. follow-up)\n- Response time to social cues\n- Frequency of appropriate social initiations\n- Generalization to natural settings (reports from teachers/parents)",
                'milestones' => [
                    'Recognize basic emotions',
                    'Match emotions to situations',
                    'Respond appropriately to emotions',
                    'Initiate social interactions'
                ],
                'notes' => 'This activity works best with consistent implementation over time. Consider pairing with home practice activities for reinforcement.',
                'published' => true
            ];
        }
        
        return null;
    }
    
    /**
     * Get related activities
     *
     * @param array|\App\Models\Activity $activity
     * @return array
     */
    private function getRelatedActivities($activity)
    {
        // This would normally be retrieved from the database
        if ($activity['category'] == 'autism') {
            return [
                [
                    'id' => 2,
                    'name' => 'Conversation Skills Practice',
                    'difficulty' => 'medium',
                    'icon' => 'comments'
                ],
                [
                    'id' => 3,
                    'name' => 'Emotional Regulation',
                    'difficulty' => 'hard',
                    'icon' => 'puzzle-piece'
                ],
                [
                    'id' => 4,
                    'name' => 'Group Play Skills',
                    'difficulty' => 'easy',
                    'icon' => 'users'
                ]
            ];
        }
        
        return [];
    }
    
    /**
     * Get activity statistics
     *
     * @param int $id
     * @return array
     */
    private function getActivityStatistics($id)
    {
        // This would normally be retrieved from the database
        return [
            'total_sessions' => 48,
            'trainees' => 24,
            'avg_session_duration' => 38,
            'success_rate' => '78%',
            'last_used' => 'Yesterday'
        ];
    }
    
    /**
     * Save activity objectives
     *
     * @param \App\Models\Activity $activity
     * @param array $objectives
     * @return void
     */
    private function saveActivityObjectives($activity, $objectives)
    {
        // This would normally save to a related table
        // For now, just log the action
        Log::info('Saved objectives for activity', [
            'activity_id' => $activity->id ?? 1,
            'objectives' => $objectives
        ]);
    }
    
    /**
     * Delete activity objectives
     *
     * @param \App\Models\Activity $activity
     * @return void
     */
    private function deleteActivityObjectives($activity)
    {
        // This would normally delete from a related table
        // For now, just log the action
        Log::info('Deleted objectives for activity', [
            'activity_id' => $activity->id ?? 1
        ]);
    }
    
    /**
     * Save activity resources
     *
     * @param \App\Models\Activity $activity
     * @param array $resources
     * @return void
     */
    private function saveActivityResources($activity, $resources)
    {
        // This would normally save to a related table
        // For now, just log the action
        Log::info('Saved resources for activity', [
            'activity_id' => $activity->id ?? 1,
            'resources' => $resources
        ]);
    }
    
    /**
     * Delete activity resources
     *
     * @param \App\Models\Activity $activity
     * @return void
     */
    private function deleteActivityResources($activity)
    {
        // This would normally delete from a related table
        // For now, just log the action
        Log::info('Deleted resources for activity', [
            'activity_id' => $activity->id ?? 1
        ]);
    }
    
    /**
     * Save activity steps
     *
     * @param \App\Models\Activity $activity
     * @param array $titles
     * @param array $descriptions
     * @return void
     */
    private function saveActivitySteps($activity, $titles, $descriptions)
    {
        // This would normally save to a related table
        // For now, just log the action
        $steps = [];
        foreach ($titles as $index => $title) {
            $steps[] = [
                'title' => $title,
                'description' => $descriptions[$index] ?? ''
            ];
        }
        
        Log::info('Saved steps for activity', [
            'activity_id' => $activity->id ?? 1,
            'steps' => $steps
        ]);
    }
    
    /**
     * Delete activity steps
     *
     * @param \App\Models\Activity $activity
     * @return void
     */
    private function deleteActivitySteps($activity)
    {
        // This would normally delete from a related table
        // For now, just log the action
        Log::info('Deleted steps for activity', [
            'activity_id' => $activity->id ?? 1
        ]);
    }
    
    /**
     * Save activity milestones
     *
     * @param \App\Models\Activity $activity
     * @param array $milestones
     * @return void
     */
    private function saveActivityMilestones($activity, $milestones)
    {
        // This would normally save to a related table
        // For now, just log the action
        Log::info('Saved milestones for activity', [
            'activity_id' => $activity->id ?? 1,
            'milestones' => $milestones
        ]);
    }
    
    /**
     * Delete activity milestones
     *
     * @param \App\Models\Activity $activity
     * @return void
     */
    private function deleteActivityMilestones($activity)
    {
        // This would normally delete from a related table
        // For now, just log the action
        Log::info('Deleted milestones for activity', [
            'activity_id' => $activity->id ?? 1
        ]);
    }
}