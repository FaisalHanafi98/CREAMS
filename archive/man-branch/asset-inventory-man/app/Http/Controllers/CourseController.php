<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
            Log::warning('Unauthorized access attempt to courses index', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy courses data
        $courses = [
            [
                'id' => 1,
                'name' => 'Communication Development',
                'code' => 'COM101',
                'description' => 'Develop verbal and non-verbal communication skills for trainees with special needs.',
                'class_count' => 3,
                'trainee_count' => 35
            ],
            [
                'id' => 2,
                'name' => 'Physical Development',
                'code' => 'PHY101',
                'description' => 'Develop fine and gross motor skills for trainees with physical challenges.',
                'class_count' => 2,
                'trainee_count' => 28
            ],
            [
                'id' => 3,
                'name' => 'Cognitive Skills',
                'code' => 'COG101',
                'description' => 'Develop problem-solving, memory, and attention skills.',
                'class_count' => 2,
                'trainee_count' => 24
            ],
            [
                'id' => 4,
                'name' => 'Social Skills',
                'code' => 'SOC101',
                'description' => 'Develop appropriate social interaction and relationship-building skills.',
                'class_count' => 2,
                'trainee_count' => 30
            ]
        ];
        
        return view('courses.index', [
            'courses' => $courses
        ]);
    }
    
    /**
     * Display the specified course.
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
        if (!in_array($role, ['admin', 'supervisor', 'teacher'])) {
            Log::warning('Unauthorized access attempt to course details', [
                'user_id' => $userId,
                'role' => $role,
                'course_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy course data
        $course = [
            'id' => $id,
            'name' => 'Communication Development',
            'code' => 'COM101',
            'description' => 'This course focuses on developing verbal and non-verbal communication skills for trainees with special needs. It covers basic language development, alternative communication methods, and social communication skills.',
            'objectives' => [
                'Develop basic verbal communication skills',
                'Learn alternative communication methods',
                'Enhance listening and comprehension skills',
                'Improve social communication abilities'
            ],
            'prerequisites' => 'None',
            'duration' => '12 weeks',
            'classes' => [
                [
                    'id' => 1,
                    'name' => 'Basic Communication Skills',
                    'teacher' => 'Dr. Nurul Hafizah',
                    'schedule' => 'Monday, Wednesday, Friday - 9:00 AM to 10:30 AM',
                    'trainee_count' => 15
                ],
                [
                    'id' => 4,
                    'name' => 'Advanced Communication',
                    'teacher' => 'Mr. Ismail Rahman',
                    'schedule' => 'Tuesday, Thursday - 2:00 PM to 3:30 PM',
                    'trainee_count' => 10
                ],
                [
                    'id' => 7,
                    'name' => 'Social Communication',
                    'teacher' => 'Ms. Sarah Tan',
                    'schedule' => 'Monday, Wednesday - 11:00 AM to 12:30 PM',
                    'trainee_count' => 10
                ]
            ]
        ];
        
        return view('courses.show', [
            'course' => $course
        ]);
    }
    
    /**
     * Show the form for creating a new course.
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
            Log::warning('Unauthorized access attempt to create course', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        return view('courses.create');
    }
    
    /**
     * Store a newly created course in storage.
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
            Log::warning('Unauthorized access attempt to store course', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'required|string',
            'objectives' => 'required|string',
            'prerequisites' => 'nullable|string|max:255',
            'duration' => 'required|string|max:100'
        ]);
        
        // In a real implementation, save the course to database
        
        Log::info('Course created', [
            'user_id' => $userId,
            'course_name' => $request->name,
            'course_code' => $request->code
        ]);
        
        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully');
    }
    
    // Other methods (edit, update, destroy) would follow a similar pattern
}