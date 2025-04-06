<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    /**
     * Display a listing of activities.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // All roles can access activities but with different data and permissions
        
        // Dummy activities data
        $activities = [
            [
                'id' => 1,
                'title' => 'Annual Sports Day',
                'type' => 'Sports',
                'date' => '2023-05-20',
                'time' => '8:00 AM - 4:00 PM',
                'location' => 'IIUM Sports Complex',
                'description' => 'Annual sports day for trainees with special needs.',
                'status' => 'upcoming',
                'participants' => 45,
                'max_participants' => 80,
                'organizer' => 'Sports Committee'
            ],
            [
                'id' => 2,
                'title' => 'Art Therapy Workshop',
                'type' => 'Workshop',
                'date' => '2023-06-10',
                'time' => '10:00 AM - 12:00 PM',
                'location' => 'Art Studio',
                'description' => 'Art therapy workshop for trainees to express themselves through creative activities.',
                'status' => 'upcoming',
                'participants' => 18,
                'max_participants' => 25,
                'organizer' => 'Creative Arts Department'
            ],
            [
                'id' => 3,
                'title' => 'Field Trip to Science Museum',
                'type' => 'Field Trip',
                'date' => '2023-07-15',
                'time' => '9:00 AM - 3:00 PM',
                'location' => 'National Science Museum',
                'description' => 'Educational field trip to learn about science in an interactive environment.',
                'status' => 'upcoming',
                'participants' => 30,
                'max_participants' => 40,
                'organizer' => 'Educational Trips Committee'
            ]
        ];
        
        // For admin and supervisor, show all activities with management options
        // For teacher and ajk, show activities they can participate in
        
        Log::info('Activities listing accessed', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        return view('activities.index', [
            'activities' => $activities,
            'role' => $role
        ]);
    }
    
    /**
     * Display the specified activity.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // All roles can view activity details
        
        // Dummy activity data
        $activity = [
            'id' => $id,
            'title' => 'Annual Sports Day',
            'type' => 'Sports',
            'date' => '2023-05-20',
            'time' => '8:00 AM - 4:00 PM',
            'location' => 'IIUM Sports Complex',
            'description' => 'Annual sports day for trainees with special needs. The event includes various adapted sports activities suitable for trainees of all abilities. Parents and guardians are welcome to attend and support the participants.',
            'status' => 'upcoming',
            'participants' => 45,
            'max_participants' => 80,
            'organizer' => 'Sports Committee',
            'contact_person' => 'Ahmad Razif',
            'contact_email' => 'razif@iium.edu.my',
            'contact_phone' => '+60 12-345-6789',
            'participants_list' => [
                [
                    'id' => 1,
                    'name' => 'Dr. Nurul Hafizah',
                    'role' => 'Teacher',
                    'status' => 'confirmed'
                ],
                [
                    'id' => 2,
                    'name' => 'Mr. Ismail Rahman',
                    'role' => 'Teacher',
                    'status' => 'confirmed'
                ]
            ],
            'schedule' => [
                [
                    'time' => '8:00 AM - 8:30 AM',
                    'activity' => 'Registration',
                    'location' => 'Sports Complex Entrance'
                ],
                [
                    'time' => '8:30 AM - 9:00 AM',
                    'activity' => 'Opening Ceremony',
                    'location' => 'Main Field'
                ],
                [
                    'time' => '9:00 AM - 12:00 PM',
                    'activity' => 'Morning Activities',
                    'location' => 'Various Stations'
                ],
                [
                    'time' => '12:00 PM - 1:00 PM',
                    'activity' => 'Lunch Break',
                    'location' => 'Cafeteria'
                ],
                [
                    'time' => '1:00 PM - 3:30 PM',
                    'activity' => 'Afternoon Activities',
                    'location' => 'Various Stations'
                ],
                [
                    'time' => '3:30 PM - 4:00 PM',
                    'activity' => 'Closing Ceremony & Prize Giving',
                    'location' => 'Main Field'
                ]
            ]
        ];
        
        Log::info('Activity details accessed', [
            'user_id' => $userId,
            'role' => $role,
            'activity_id' => $id
        ]);
        
        // Check if the current user can register or is already registered
        $canRegister = in_array($role, ['teacher', 'ajk']) && 
                       $activity['status'] === 'upcoming' && 
                       $activity['participants'] < $activity['max_participants'];
        
        $isRegistered = false;
        foreach ($activity['participants_list'] as $participant) {
            if ($participant['id'] == $userId) {
                $isRegistered = true;
                break;
            }
        }
        
        return view('activities.show', [
            'activity' => $activity,
            'role' => $role,
            'canRegister' => $canRegister,
            'isRegistered' => $isRegistered
        ]);
    }
    
    /**
     * Show the form for creating a new activity.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if (!in_array($role, ['admin', 'supervisor'])) {
            Log::warning('Unauthorized access attempt to create activity', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy locations data for dropdown
        $locations = [
            'IIUM Sports Complex',
            'Art Studio',
            'Music Room',
            'Computer Lab',
            'Therapy Room',
            'School Garden',
            'Auditorium',
            'Classroom 101'
        ];
        
        // Dummy activity types for dropdown
        $activityTypes = [
            'Sports',
            'Workshop',
            'Field Trip',
            'Cultural',
            'Educational',
            'Recreation',
            'Therapy',
            'Social'
        ];
        
        return view('activities.create', [
            'locations' => $locations,
            'activityTypes' => $activityTypes
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
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if (!in_array($role, ['admin', 'supervisor'])) {
            Log::warning('Unauthorized access attempt to store activity', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'date' => 'required|date|after:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'max_participants' => 'required|integer|min:1',
            'organizer' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'contact_phone' => 'required|string|max:20'
        ]);
        
        // In a real implementation, save the activity to database
        
        Log::info('Activity created', [
            'user_id' => $userId,
            'activity_title' => $request->title,
            'activity_date' => $request->date
        ]);
        
        return redirect()->route('activities.index')
            ->with('success', 'Activity created successfully');
    }
    
    /**
     * Register participation in an activity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerParticipation(Request $request, $id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if (!in_array($role, ['teacher', 'ajk'])) {
            Log::warning('Unauthorized access attempt to register for activity', [
                'user_id' => $userId,
                'role' => $role,
                'activity_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // In a real implementation, check if the activity is full and register the user
        
        Log::info('User registered for activity', [
            'user_id' => $userId,
            'role' => $role,
            'activity_id' => $id
        ]);
        
        return redirect()->back()
            ->with('success', 'You have successfully registered for this activity');
    }
    
    /**
     * Unregister from an activity.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unregisterParticipation($id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if (!in_array($role, ['teacher', 'ajk'])) {
            Log::warning('Unauthorized access attempt to unregister from activity', [
                'user_id' => $userId,
                'role' => $role,
                'activity_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // In a real implementation, remove the user registration
        
        Log::info('User unregistered from activity', [
            'user_id' => $userId,
            'role' => $role,
            'activity_id' => $id
        ]);
        
        return redirect()->back()
            ->with('success', 'You have successfully unregistered from this activity');
    }
    
    // Other methods (edit, update, destroy) would follow a similar pattern
}