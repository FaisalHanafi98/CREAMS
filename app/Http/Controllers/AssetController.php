<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssetController extends Controller
{
    /**
     * Display a listing of assets.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to assets index', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        // Dummy asset data
        $assets = [
            [
                'id' => 1,
                'name' => 'Laptop HP EliteBook',
                'category' => 'Electronics',
                'serial_number' => 'HP2023456789',
                'acquisition_date' => '2022-01-15',
                'status' => 'In Use',
                'assigned_to' => 'Dr. Nurul Hafizah',
                'location' => 'IIUM Gombak Centre'
            ],
            [
                'id' => 2,
                'name' => 'Projector Epson EB-X51',
                'category' => 'Electronics',
                'serial_number' => 'EPS20238765',
                'acquisition_date' => '2022-02-20',
                'status' => 'In Use',
                'assigned_to' => 'Classroom 101',
                'location' => 'IIUM Gombak Centre'
            ],
            [
                'id' => 3,
                'name' => 'Therapy Equipment Set',
                'category' => 'Medical',
                'serial_number' => 'MED20231234',
                'acquisition_date' => '2022-03-10',
                'status' => 'In Use',
                'assigned_to' => 'Therapy Room',
                'location' => 'IIUM Kuantan Centre'
            ]
        ];
        
        return view('assets.index', [
            'assets' => $assets
        ]);
    }
    
    /**
     * Show the form for creating a new asset.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to create asset', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to access this page');
        }
        
        return view('assets.create');
    }
    
    /**
     * Store a newly created asset in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Check role access
        $role = session('role');
        if ($role !== 'admin') {
            Log::warning('Unauthorized access attempt to store asset', [
                'user_id' => session('id'),
                'role' => $role
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'You do not have permission to perform this action');
        }
        
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'serial_number' => 'required|string|max:50',
            'acquisition_date' => 'required|date',
            'status' => 'required|string|max:50',
            'assigned_to' => 'nullable|string|max:255',
            'location' => 'required|string|max:255'
        ]);
        
        // In a real implementation, save the asset to database
        
        Log::info('Asset created', [
            'user_id' => session('id'),
            'asset_name' => $request->name
        ]);
        
        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset created successfully');
    }
    
    // Other methods (show, edit, update, destroy) would follow a similar pattern
}