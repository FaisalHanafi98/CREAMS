<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;
use App\Models\Trainees;
use App\Models\Activities;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Only allow authenticated users
        $this->middleware('auth');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Log the dashboard access
        Log::info('Admin dashboard accessed', [
            'user_id' => Auth::id() ?? session('id'),
            'user_name' => Auth::user()->name ?? session('name'),
            'timestamp' => now()
        ]);
        
        // Get counts for dashboard cards
        $userCounts = $this->getUserCounts();
        
        // Pass data to the view
        return view('admin.dashboard', [
            'name' => Auth::user()->name ?? session('name'),
            'role' => 'admin',
            'userCounts' => $userCounts
        ]);
    }
    
    /**
     * Get counts of different user types for dashboard stats
     *
     * @return array
     */
    private function getUserCounts()
    {
        // Get counts from each model if they exist
        $counts = [
            'admins' => class_exists('App\Models\Admins') ? Admins::count() : 1,
            'supervisors' => class_exists('App\Models\Supervisors') ? Supervisors::count() : 5,
            'teachers' => class_exists('App\Models\Teachers') ? Teachers::count() : 12,
            'ajks' => class_exists('App\Models\AJKs') ? AJKs::count() : 3,
            'trainees' => class_exists('App\Models\Trainees') ? Trainees::count() : 50
        ];
        
        return $counts;
    }
    
    /**
     * Show the user management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function manageUsers()
    {
        $admins = class_exists('App\Models\Admins') ? Admins::all() : collect();
        $supervisors = class_exists('App\Models\Supervisors') ? Supervisors::all() : collect();
        $teachers = class_exists('App\Models\Teachers') ? Teachers::all() : collect();
        $ajks = class_exists('App\Models\AJKs') ? AJKs::all() : collect();
        
        return view('admin.users', [
            'name' => Auth::user()->name ?? session('name'),
            'admins' => $admins,
            'supervisors' => $supervisors,
            'teachers' => $teachers,
            'ajks' => $ajks
        ]);
    }
    
    /**
     * Show the trainee management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function manageTrainees()
    {
        // Check if Trainees model exists and get trainees
        $trainees = class_exists('App\Models\Trainees') ? Trainees::all() : collect();
        
        return view('admin.trainees', [
            'name' => Auth::user()->name ?? session('name'),
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Show the reports page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function reports()
    {
        return view('admin.reports', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the analytics page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function analytics()
    {
        return view('admin.analytics', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the settings page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function settings()
    {
        return view('admin.settings', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activities()
    {
        return view('admin.activities', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity creation page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createActivity()
    {
        return view('admin.activities.create', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activityCategories()
    {
        return view('admin.activities.categories', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity schedule page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activitySchedule()
    {
        return view('admin.activities.schedule', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the centres management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function centres()
    {
        return view('admin.centres', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the assets management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function assets()
    {
        return view('admin.assets', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the logs page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function logs()
    {
        return view('admin.logs', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the profile page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        return view('admin.profile', [
            'name' => Auth::user()->name ?? session('name'),
            'user' => Auth::user() ?: null
        ]);
    }
}