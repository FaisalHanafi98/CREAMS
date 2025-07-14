<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Display a listing of events.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'ajk') {
            Log::warning('Unauthorized access attempt to events index', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy events data
        $events = [
            [
                'id' => 1,
                'title' => 'Community Awareness Day',
                'date' => '2023-04-25',
                'time' => '9:00 AM - 5:00 PM',
                'location' => 'IIUM Main Auditorium',
                'description' => 'A day dedicated to raising awareness about rehabilitation services in the community.',
                'status' => 'upcoming',
                'participants' => 32,
                'max_participants' => 50
            ],
            [
                'id' => 2,
                'title' => 'Parents Workshop',
                'date' => '2023-05-10',
                'time' => '10:00 AM - 2:00 PM',
                'location' => 'IIUM Conference Room',
                'description' => 'Workshop for parents of trainees with special needs.',
                'status' => 'upcoming',
                'participants' => 18,
                'max_participants' => 30
            ],
            [
                'id' => 3,
                'title' => 'Teacher Training Seminar',
                'date' => '2023-03-15',
                'time' => '9:00 AM - 4:00 PM',
                'location' => 'IIUM Training Centre',
                'description' => 'Professional development seminar for special education teachers.',
                'status' => 'completed',
                'participants' => 45,
                'max_participants' => 45
            ]
        ];
        
        return view('events.index', [
            'events' => $events
        ]);
    }
    
    /**
     * Display the specified event.
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
        if ($role !== 'ajk') {
            Log::warning('Unauthorized access attempt to event details', [
                'user_id' => $userId,
                'role' => $role,
                'event_id' => $id
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy event data
        $event = [
            'id' => $id,
            'title' => 'Community Awareness Day',
            'date' => '2023-04-25',
            'time' => '9:00 AM - 5:00 PM',
            'location' => 'IIUM Main Auditorium',
            'description' => 'A day dedicated to raising awareness about rehabilitation services in the community. The event will feature exhibitions, talks, and interactive activities for attendees to learn about different aspects of rehabilitation services.',
            'status' => 'upcoming',
            'organizer' => 'Community Outreach Committee',
            'contact_person' => 'Ahmad Razif',
            'contact_email' => 'razif@iium.edu.my',
            'contact_phone' => '+60 12-345-6789',
            'participants' => [
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
                ],
                [
                    'id' => 3,
                    'name' => 'Ms. Sarah Tan',
                    'role' => 'Teacher',
                    'status' => 'pending'
                ]
            ],
            'volunteers' => [
                [
                    'id' => 1,
                    'name' => 'Ali Hassan',
                    'role' => 'Event Support',
                    'status' => 'confirmed'
                ],
                [
                    'id' => 2,
                    'name' => 'Mei Ling',
                    'role' => 'Registration Desk',
                    'status' => 'confirmed'
                ]
            ],
            'schedule' => [
                [
                    'time' => '9:00 AM - 9:30 AM',
                    'activity' => 'Registration',
                    'location' => 'Main Entrance'
                ],
                [
                    'time' => '9:30 AM - 10:30 AM',
                    'activity' => 'Opening Ceremony',
                    'location' => 'Main Auditorium'
                ],
                [
                    'time' => '10:30 AM - 12:00 PM',
                    'activity' => 'Exhibition & Booths',
                    'location' => 'Exhibition Hall'
                ],
                [
                    'time' => '12:00 PM - 1:00 PM',
                    'activity' => 'Lunch Break',
                    'location' => 'Cafeteria'
                ],
                [
                    'time' => '1:00 PM - 3:00 PM',
                    'activity' => 'Workshops',
                    'location' => 'Workshop Rooms'
                ],
                [
                    'time' => '3:00 PM - 4:30 PM',
                    'activity' => 'Panel Discussion',
                    'location' => 'Main Auditorium'
                ],
                [
                    'time' => '4:30 PM - 5:00 PM',
                    'activity' => 'Closing Ceremony',
                    'location' => 'Main Auditorium'
                ]
            ]
        ];
        
        return view('events.show', [
            'event' => $event
        ]);
    }
    
    /**
     * Show the form for creating a new event.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get user ID and role
        $userId = session('id');
        $role = session('role');
        
        // Check role access
        if ($role !== 'ajk') {
            Log::warning('Unauthorized access attempt to create event', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy locations data for dropdown
        $locations = [
            'IIUM Main Auditorium',
            'IIUM Conference Room',
            'IIUM Training Centre',
            'IIUM Exhibition Hall',
            'IIUM Sports Complex',
            'Community Centre'
        ];
        
        return view('events.create', [
            'locations' => $locations
        ]);
    }
    
    /**
     * Store a newly created event in storage.
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
        if ($role !== 'ajk') {
            Log::warning('Unauthorized access attempt to store event', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'title' => 'required|string|max:255',
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
        
        // In a real implementation, save the event to database
        
        Log::info('Event created', [
            'user_id' => $userId,
            'event_title' => $request->title,
            'event_date' => $request->date
        ]);
        
        return redirect()->route('events.index')
            ->with('success', 'Event created successfully');
    }
    
    // Other methods (edit, update, destroy) would follow a similar pattern
}