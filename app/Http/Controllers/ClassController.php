<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to classes index', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy classes data
        $classes = [
            [
                'id' => 1,
                'name' => 'Basic Communication Skills',
                'course_id' => 1,
                'course_name' => 'Communication Development',
                'schedule' => 'Monday, Wednesday, Friday - 9:00 AM to 10:30 AM',
                'location' => 'Room 102',
                'trainee_count' => 15,
                'start_date' => '2023-01-15',
                'end_date' => '2023-04-15'
            ],
            [
                'id' => 2,
                'name' => 'Motor Skills Development',
                'course_id' => 2,
                'course_name' => 'Physical Development',
                'schedule' => 'Tuesday, Thursday - 11:00 AM to 12:30 PM',
                'location' => 'Activity Room',
                'trainee_count' => 12,
                'start_date' => '2023-01-16',
                'end_date' => '2023-04-16'
            ],
            [
                'id' => 3,
                'name' => 'Cognitive Development',
                'course_id' => 3,
                'course_name' => 'Cognitive Skills',
                'schedule' => 'Monday, Wednesday - 1:00 PM to 2:30 PM',
                'location' => 'Room 105',
                'trainee_count' => 10,
                'start_date' => '2023-01-16',
                'end_date' => '2023-04-16'
            ]
        ];
        
        return view('classes.index', [
            'classes' => $classes
        ]);
    }
    
    /**
     * Display the specified class.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to class details', [
                'user_id' => $userId,
                'role' => $role,
                'class_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy class data
        $class = [
            'id' => $id,
            'name' => 'Basic Communication Skills',
            'course_id' => 1,
            'course_name' => 'Communication Development',
            'description' => 'This class focuses on developing basic communication skills for trainees with special needs, including verbal and non-verbal communication techniques.',
            'schedule' => 'Monday, Wednesday, Friday - 9:00 AM to 10:30 AM',
            'location' => 'Room 102',
            'start_date' => '2023-01-15',
            'end_date' => '2023-04-15',
            'trainees' => [
                [
                    'id' => 1,
                    'name' => 'Ahmad Ismail',
                    'attendance_rate' => 95,
                    'progress' => 75
                ],
                [
                    'id' => 2,
                    'name' => 'Siti Aminah',
                    'attendance_rate' => 88,
                    'progress' => 82
                ],
                [
                    'id' => 3,
                    'name' => 'Raj Kumar',
                    'attendance_rate' => 92,
                    'progress' => 68
                ]
            ]
        ];
        
        return view('classes.show', [
            'class' => $class
        ]);
    }
    
    /**
     * Show the form for creating a new class.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to create class', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy courses data for dropdown
        $courses = [
            1 => 'Communication Development',
            2 => 'Physical Development',
            3 => 'Cognitive Skills',
            4 => 'Social Skills'
        ];
        
        // Dummy rooms data for dropdown
        $rooms = [
            'Room 101',
            'Room 102',
            'Room 103',
            'Room 104',
            'Room 105',
            'Activity Room',
            'Computer Lab',
            'Sensory Room'
        ];
        
        return view('classes.create', [
            'courses' => $courses,
            'rooms' => $rooms
        ]);
    }
    
    /**
     * Store a newly created class in storage.
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
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to store class', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|integer',
            'description' => 'nullable|string',
            'schedule_days' => 'required|array',
            'schedule_start_time' => 'required|string',
            'schedule_end_time' => 'required|string',
            'location' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date'
        ]);
        
        // In a real implementation, save the class to database
        
        Log::info('Class created', [
            'user_id' => $userId,
            'class_name' => $request->name
        ]);
        
        return redirect()->route('classes.index')
            ->with('success', 'Class created successfully');
    }
    
    /**
     * Update attendance for a specific class.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAttendance(Request $request, $id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to update attendance', [
                'user_id' => $userId,
                'role' => $role,
                'class_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'boolean'
        ]);
        
        // In a real implementation, save attendance records to database
        
        Log::info('Attendance updated', [
            'user_id' => $userId,
            'class_id' => $id,
            'date' => $request->date,
            'trainee_count' => count($request->attendance)
        ]);
        
        return redirect()->back()
            ->with('success', 'Attendance updated successfully');
    }
    
    /**
     * Display teacher's schedule.
     *
     * @return \Illuminate\View\View
     */
    public function schedule()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'teacher') {
            Log::warning('Unauthorized access attempt to teacher schedule', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy schedule data
        $schedule = [
            'Monday' => [
                [
                    'id' => 1,
                    'name' => 'Basic Communication Skills',
                    'time' => '9:00 AM - 10:30 AM',
                    'location' => 'Room 102',
                    'trainee_count' => 15
                ],
                [
                    'id' => 3,
                    'name' => 'Cognitive Development',
                    'time' => '1:00 PM - 2:30 PM',
                    'location' => 'Room 105',
                    'trainee_count' => 10
                ]
            ],
            'Tuesday' => [
                [
                    'id' => 2,
                    'name' => 'Motor Skills Development',
                    'time' => '11:00 AM - 12:30 PM',
                    'location' => 'Activity Room',
                    'trainee_count' => 12
                ]
            ],
            'Wednesday' => [
                [
                    'id' => 1,
                    'name' => 'Basic Communication Skills',
                    'time' => '9:00 AM - 10:30 AM',
                    'location' => 'Room 102',
                    'trainee_count' => 15
                ],
                [
                    'id' => 3,
                    'name' => 'Cognitive Development',
                    'time' => '1:00 PM - 2:30 PM',
                    'location' => 'Room 105',
                    'trainee_count' => 10
                ]
            ],
            'Thursday' => [
                [
                    'id' => 2,
                    'name' => 'Motor Skills Development',
                    'time' => '11:00 AM - 12:30 PM',
                    'location' => 'Activity Room',
                    'trainee_count' => 12
                ]
            ],
            'Friday' => [
                [
                    'id' => 1,
                    'name' => 'Basic Communication Skills',
                    'time' => '9:00 AM - 10:30 AM',
                    'location' => 'Room 102',
                    'trainee_count' => 15
                ]
            ]
        ];
        
        return view('classes.schedule', [
            'schedule' => $schedule
        ]);
    }
    
    // Other methods (edit, update, destroy) would follow a similar pattern
}