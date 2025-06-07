<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Activities;
use App\Models\ActivitySessions;
use App\Models\ActivityAttendances;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $role = session('role') ?? (Auth::user() ? Auth::user()->role : 'admin');
        $userId = session('id');
        session(['role' => $role]);

        $data = $this->getCommonData();
        $lastAccessedData = [
            'system_activities' => collect([
                [
                    'type' => 'system_activity',
                    'user' => 'System',
                    'action' => 'Updated',
                    'entity' => 'trainee',
                    'timestamp' => '5 minutes ago',
                    'details' => 'Updated trainee profile information'
                ],
                [
                    'type' => 'system_activity',
                    'user' => 'Admin',
                    'action' => 'Created',
                    'entity' => 'class',
                    'timestamp' => '2 hours ago',
                    'details' => 'Created new class "Speech Therapy 101"'
                ]
            ]),
            'user_activities' => collect([
                [
                    'type' => 'user_activity',
                    'action' => 'Viewed',
                    'entity' => 'trainee',
                    'timestamp' => '10 minutes ago',
                    'details' => 'Viewed trainee profile for Ahmad Razif'
                ]
            ]),
            'last_login' => 'First login'
        ];

        $traineeStats = $this->getTraineeStats();
        $rehabStats = $this->getRehabStats();
        $recentActivities = $this->getRecentActivities();
        $centreStats = $this->getCentreStats();
        $recentAssets = $this->getRecentAssets();
        $todayClasses = $this->getTodayClasses();
        $activityStats = $this->getActivityStats();

        if ($role === 'teacher') {
            $todaySessions = ActivitySession::where('teacher_id', $userId)
                ->where('day_of_week', Carbon::now()->format('l'))
                ->where('is_active', true)
                ->with('activity')
                ->get();

            $data['todaySessions'] = $todaySessions;
        }

        $this->updateLastAccessTimestamp($userId, $role);

        return view('dashboard', compact(
            'data',
            'traineeStats',
            'rehabStats',
            'recentActivities',
            'centreStats',
            'recentAssets',
            'todayClasses',
            'lastAccessedData',
            'activityStats'
        ));
    }

    /**
     * Get key statistics about activities and attendance
     */
    private function getActivityStats()
    {
        return [
            'total_activities' => Activities::count(),
            'active_sessions' => ActivitySessions::where('is_active', true)->count(),
            'todays_sessions' => ActivitySessions::where('day_of_week', Carbon::now()->format('l'))
                ->where('is_active', true)
                ->count(),
            'attendance_today' => ActivityAttendances::whereDate('attendance_date', today())
                ->where('status', 'Present')
                ->count()
        ];
    }

    /**
     * Update user's last accessed timestamp
     * 
     * @param int $userId
     * @param string $role
     * @return void
     */
    private function updateLastAccessTimestamp($userId, $role)
    {
        try {
            if (!empty($userId)) {
                $user = Users::find($userId);
                if ($user) {
                    $user->user_last_accessed_at = now();
                    $user->save();
                    
                    // Also update session value
                    session(['user_last_accessed_at' => now()->format('Y-m-d H:i:s')]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update user last access time', [
                'user_id' => $userId,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get common data for the dashboard
     */
    private function getCommonData()
    {
        // Check if Users model exists and table is available
        try {
            // Try to get actual user counts
            $supervisorCount = Users::where('role', 'supervisor')->count();
            $teacherCount = Users::where('role', 'teacher')->count();
            $ajkCount = Users::where('role', 'ajk')->count();
            $totalUsers = Users::count();
            
            // Get recent users
            $recentUsers = Users::latest()->take(5)->get();
        } catch (\Exception $e) {
            // Use placeholder data if there's an error
            $supervisorCount = 8;
            $teacherCount = 24;
            $ajkCount = 12;
            $totalUsers = 45;
            
            // Create placeholder users
            $recentUsers = $this->getPlaceholderUsers();
        }
        
        // Build stats array
        $stats = [
            [
                'title' => 'Supervisors',
                'value' => $supervisorCount,
                'icon' => 'user-tie',
                'change' => '+5% from last month'
            ],
            [
                'title' => 'Teachers',
                'value' => $teacherCount,
                'icon' => 'chalkboard-teacher',
                'change' => '+2% from last month'
            ],
            [
                'title' => 'AJKs',
                'value' => $ajkCount,
                'icon' => 'user-friends',
                'change' => 'Same as last month'
            ],
            [
                'title' => 'Total Users',
                'value' => $totalUsers,
                'icon' => 'users',
                'change' => '+3% from last month'
            ]
        ];
        
        // Build data array
        $data = [
            'stats' => $stats,
            'userManagement' => [
                'recentUsers' => $recentUsers
            ]
        ];
        
        return $data;
    }
    
    /**
     * Create placeholder users for development
     */
    private function getPlaceholderUsers()
    {
        $users = [];
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
        $names = [
            'John Doe',
            'Jane Smith',
            'Ahmed Khan',
            'Lisa Wong',
            'Michael Brown'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $user = new \stdClass();
            $user->id = $i + 1;
            $user->name = $names[$i];
            $user->email = strtolower(str_replace(' ', '.', $names[$i])) . '@example.com';
            $user->role = $roles[array_rand($roles)];
            $user->status = rand(0, 10) > 2 ? 'active' : 'inactive';
            $user->profile_picture = null;
            $user->created_at = Carbon::now()->subDays(rand(1, 30));
            
            $users[] = $user;
        }
        
        return $users;
    }
    
    /**
     * Get trainee statistics
     */
    private function getTraineeStats()
    {
        // Try to get actual trainee data if the model exists
        try {
            if (class_exists('App\Models\Trainee')) {
                $traineeModel = app('App\Models\Trainee');
                $totalTrainees = $traineeModel::count();
                $newTrainees = $traineeModel::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
            } else {
                throw new \Exception('Trainee model not found');
            }
        } catch (\Exception $e) {
            // Use placeholder data
            $totalTrainees = 124;
            $newTrainees = 18;
        }
        
        // Return trainee stats
        return [
            'total' => $totalTrainees,
            'new' => $newTrainees,
            'attendance' => '85%',
            'activities' => 12
        ];
    }
    
    /**
     * Get rehabilitation statistics for trainees
     */
    private function getRehabStats()
    {
        // Try to get actual trainee data if the model exists
        try {
            if (class_exists('App\Models\Trainees')) {
                $traineesModel = app('App\Models\Trainees');
                $trainees = $traineesModel::all();
                
                return [
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
            } else {
                throw new \Exception('Trainees model not found');
            }
        } catch (\Exception $e) {
            // Use placeholder data if there's an error
            return [
                'autism' => 12,
                'hearing' => 8,
                'visual' => 5,
                'physical' => 15,
                'learning' => 10,
                'speech' => 6,
                'down' => 7,
                'cerebral' => 9,
                'intellectual' => 11,
                'multiple' => 4,
                'other' => 3
            ];
        }
    }
    
    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        // Try to get actual activity data if the model exists
        try {
            if (class_exists('App\Models\Activity')) {
                $activityModel = app('App\Models\Activity');
                $activities = $activityModel::latest()->take(5)->get();
                
                // Process activities to add required fields
                foreach ($activities as $activity) {
                    // Set status color based on status
                    $activity->status_color = $this->getStatusColor($activity->status);
                    
                    // Ensure date is a Carbon instance
                    if (!$activity->date instanceof Carbon) {
                        $activity->date = Carbon::parse($activity->date);
                    }
                }
                
                return $activities;
            } else {
                throw new \Exception('Activity model not found');
            }
        } catch (\Exception $e) {
            // Use placeholder data
            return $this->getPlaceholderActivities();
        }
    }
    
    /**
     * Create placeholder activities for development
     */
    private function getPlaceholderActivities()
    {
        $activities = [];
        $statuses = ['completed', 'ongoing', 'upcoming', 'cancelled'];
        $types = ['Workshop', 'Seminar', 'Training', 'Field Trip', 'Assessment'];
        $names = [
            'Basic Computing Skills',
            'English Language Proficiency',
            'Technical Skills Workshop',
            'Leadership Training',
            'Career Development Seminar'
        ];
        
        for ($i = 0; $i < 5; $i++) {
            $status = $statuses[array_rand($statuses)];
            $activity = new \stdClass();
            $activity->id = $i + 1;
            $activity->name = $names[$i];
            $activity->type = $types[array_rand($types)];
            $activity->date = Carbon::now()->addDays(rand(-10, 20));
            $activity->participants_count = rand(10, 50);
            $activity->status = $status;
            $activity->status_color = $this->getStatusColor($status);
            
            $activities[] = $activity;
        }
        
        return $activities;
    }
    
    /**
     * Get status color for badges
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'completed':
                return 'success';
            case 'ongoing':
                return 'primary';
            case 'upcoming':
                return 'info';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }
    
    /**
     * Get centre statistics
     */
    private function getCentreStats()
    {
        // Use placeholder data for centres
        return [
            [
                'id' => 1,
                'name' => 'Main Training Centre',
                'staff_count' => 12,
                'trainee_count' => 78,
                'asset_count' => 45,
                'capacity' => 85
            ],
            [
                'id' => 2,
                'name' => 'East Branch',
                'staff_count' => 8,
                'trainee_count' => 54,
                'asset_count' => 32,
                'capacity' => 72
            ],
            [
                'id' => 3,
                'name' => 'South Campus',
                'staff_count' => 6,
                'trainee_count' => 42,
                'asset_count' => 28,
                'capacity' => 60
            ],
            [
                'id' => 4,
                'name' => 'North Extension',
                'staff_count' => 5,
                'trainee_count' => 36,
                'asset_count' => 22,
                'capacity' => 48
            ]
        ];
    }
    
    /**
     * Get recent assets
     */
    private function getRecentAssets()
    {
        // Use placeholder data for assets
        $assets = [];
        $types = ['Computer', 'Furniture', 'Equipment', 'Vehicle', 'Books'];
        $centres = ['Main Training Centre', 'East Branch', 'South Campus', 'North Extension'];
        
        for ($i = 0; $i < 5; $i++) {
            $asset = new \stdClass();
            $asset->id = $i + 1;
            $asset->name = $types[array_rand($types)] . ' ' . chr(65 + $i);
            $asset->type = $types[array_rand($types)];
            $asset->centre_name = $centres[array_rand($centres)];
            $asset->quantity = rand(0, 20);
            
            $assets[] = $asset;
        }
        
        return $assets;
    }
    
    /**
     * Get today's classes (placeholder method)
     */
    private function getTodayClasses()
    {
        // Placeholder for today's classes
        return [
            [
                'id' => 1,
                'name' => 'Digital Skills for Autism',
                'time' => '09:00 AM',
                'instructor' => 'John Doe',
                'location' => 'Main Training Centre'
            ],
            [
                'id' => 2,
                'name' => 'Communication Workshop',
                'time' => '11:00 AM',
                'instructor' => 'Jane Smith',
                'location' => 'East Branch'
            ],
            // Add more placeholder classes as needed
        ];
    }
}