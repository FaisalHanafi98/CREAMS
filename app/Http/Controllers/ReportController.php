<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to reports index', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy report categories
        $reportCategories = [
            [
                'id' => 1,
                'name' => 'User Activity',
                'description' => 'Reports on user login times, page visits, and actions.',
                'icon' => 'fa-users'
            ],
            [
                'id' => 2,
                'name' => 'Tainee Progress',
                'description' => 'Reports on trainee attendance and achievement metrics.',
                'icon' => 'fa-graduation-cap'
            ],
            [
                'id' => 3,
                'name' => 'Centre Performance',
                'description' => 'Reports on centre operations, resources, and outcomes.',
                'icon' => 'fa-building'
            ],
            [
                'id' => 4,
                'name' => 'Financial Reports',
                'description' => 'Reports on budgets, expenses, and resource allocation.',
                'icon' => 'fa-chart-line'
            ]
        ];
        
        return view('reports.index', [
            'reportCategories' => $reportCategories
        ]);
    }
    
    /**
     * Show the report generation form.
     *
     * @return \Illuminate\View\View
     */
    public function generate()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to report generation', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Report types
        $reportTypes = [
            'user_activity' => 'User Activity Report',
            'trainee_progress' => 'Tainee Progress Report',
            'centre_performance' => 'Centre Performance Report',
            'financial' => 'Financial Report'
        ];
        
        // Centres
        $centres = [
            1 => 'IIUM Gombak Centre',
            2 => 'IIUM Kuantan Centre',
            3 => 'IIUM Pagoh Centre'
        ];
        
        return view('reports.generate', [
            'reportTypes' => $reportTypes,
            'centres' => $centres
        ]);
    }
    
    /**
     * Export a generated report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function export(Request $request)
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to export report', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'report_type' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'centre_id' => 'nullable|integer',
            'format' => 'required|in:pdf,excel,csv'
        ]);
        
        // In a real implementation, generate and export the report
        
        Log::info('Report exported', [
            'user_id' => session('id'),
            'report_type' => $request->report_type,
            'format' => $request->format
        ]);
        
        return redirect()->back()
            ->with('success', 'Report exported successfully. Check your downloads folder.');
    }
    
    /**
     * Display supervisor-specific reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function supervisorIndex()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'supervisor') {
            Log::warning('Unauthorized access attempt to supervisor reports', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy report categories for supervisors
        $reportCategories = [
            [
                'id' => 1,
                'name' => 'Teacher Performance',
                'description' => 'Reports on teacher activities and performance metrics.',
                'icon' => 'fa-chalkboard-teacher'
            ],
            [
                'id' => 2,
                'name' => 'Tainee Progress',
                'description' => 'Reports on trainee attendance and achievement metrics.',
                'icon' => 'fa-graduation-cap'
            ],
            [
                'id' => 3,
                'name' => 'Class Activities',
                'description' => 'Reports on class activities and curriculum progress.',
                'icon' => 'fa-book'
            ]
        ];
        
        return view('reports.supervisor.index', [
            'reportCategories' => $reportCategories
        ]);
    }
    
    // Other methods for supervisor reports would follow a similar pattern
}