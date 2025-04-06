<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CentreController extends Controller
{
    /**
     * Display a listing of centres.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Dummy data for centres (replace with actual database queries)
        $centres = [
            [
                'id' => 1,
                'name' => 'IIUM Gombak Centre',
                'address' => 'Jalan Gombak, 53100 Kuala Lumpur',
                'phone' => '+60 3-6196 4000',
                'email' => 'gombak@iium.edu.my',
                'staff_count' => 25,
                'trainee_count' => 150
            ],
            [
                'id' => 2,
                'name' => 'IIUM Kuantan Centre',
                'address' => 'Jalan Sultan Ahmad Shah, 25200 Kuantan, Pahang',
                'phone' => '+60 9-570 4000',
                'email' => 'kuantan@iium.edu.my',
                'staff_count' => 18,
                'trainee_count' => 120
            ],
            [
                'id' => 3,
                'name' => 'IIUM Pagoh Centre',
                'address' => 'KM 1, Jalan Panchor, 84600 Pagoh, Johor',
                'phone' => '+60 6-974 2800',
                'email' => 'pagoh@iium.edu.my',
                'staff_count' => 15,
                'trainee_count' => 90
            ]
        ];
        
        Log::info('Centres listing accessed', [
            'user_id' => $userId,
            'role' => $role
        ]);
        
        return view('centres.index', [
            'centres' => $centres
        ]);
    }
    
    /**
     * Display the specified centre.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Dummy data for a specific centre (replace with actual database queries)
        $centre = [
            'id' => $id,
            'name' => 'IIUM Gombak Centre',
            'address' => 'Jalan Gombak, 53100 Kuala Lumpur',
            'phone' => '+60 3-6196 4000',
            'email' => 'gombak@iium.edu.my',
            'website' => 'https://www.iium.edu.my',
            'description' => 'The IIUM Gombak Centre provides rehabilitation services and special education programs for trainees with diverse needs.',
            'facilities' => [
                'Speech Therapy Rooms',
                'Sensory Integration Room',
                'Fine Motor Skills Lab',
                'Computer Lab',
                'Music Therapy Room',
                'Outdoor Playground'
            ],
            'staff_count' => 25,
            'trainee_count' => 150,
            'staff' => [
                [
                    'id' => 1,
                    'name' => 'Dr. Ahmad Razif',
                    'role' => 'Admin',
                    'email' => 'razif@iium.edu.my'
                ],
                [
                    'id' => 2,
                    'name' => 'Dr. Nurul Hafizah',
                    'role' => 'Teacher',
                    'email' => 'nurulh@iium.edu.my'
                ]
            ]
        ];
        
        Log::info('Centre details accessed', [
            'user_id' => $userId,
            'role' => $role,
            'centre_id' => $id
        ]);
        
        return view('centres.show', [
            'centre' => $centre
        ]);
    }
    
    /**
     * Display admin-specific listing of centres.
     *
     * @return \Illuminate\View\View
     */
    public function adminIndex()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to admin centres', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Same data as index, but with admin-specific options
        $centres = [
            [
                'id' => 1,
                'name' => 'IIUM Gombak Centre',
                'address' => 'Jalan Gombak, 53100 Kuala Lumpur',
                'phone' => '+60 3-6196 4000',
                'email' => 'gombak@iium.edu.my',
                'staff_count' => 25,
                'trainee_count' => 150,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'IIUM Kuantan Centre',
                'address' => 'Jalan Sultan Ahmad Shah, 25200 Kuantan, Pahang',
                'phone' => '+60 9-570 4000',
                'email' => 'kuantan@iium.edu.my',
                'staff_count' => 18,
                'trainee_count' => 120,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'IIUM Pagoh Centre',
                'address' => 'KM 1, Jalan Panchor, 84600 Pagoh, Johor',
                'phone' => '+60 6-974 2800',
                'email' => 'pagoh@iium.edu.my',
                'staff_count' => 15,
                'trainee_count' => 90,
                'status' => 'active'
            ]
        ];
        
        return view('centres.admin.index', [
            'centres' => $centres
        ]);
    }
    
    /**
     * Show the form for creating a new centre.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to create centre', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        return view('centres.admin.create');
    }
    
    /**
     * Store a newly created centre in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to store centre', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'phone' => 'required|string|max:20',
            'email' => 'required|email',
            'description' => 'nullable|string'
        ]);
        
        // In a real implementation, save the centre to database
        
        Log::info('Centre created', [
            'user_id' => session('id'),
            'centre_name' => $request->name
        ]);
        
        return redirect()->route('admin.centres.index')
            ->with('success', 'Centre created successfully');
    }
    
    // Additional methods (edit, update, destroy) would be similar
}