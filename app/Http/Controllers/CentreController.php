<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Centres;
use App\Models\Asset;
use App\Models\Users;
use App\Models\Trainee;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CentreController extends Controller
{
    /**
     * Display a listing of the centres.
     */
    public function index()
    {
        try {
            Log::info('Loading centres index page', ['user_id' => session('id')]);
            
            $centres = Centres::withCount(['users', 'trainees', 'assets'])
                ->orderBy('centre_name')
                ->get();
            
            Log::info('Successfully loaded centres', ['count' => $centres->count()]);
            
            return view('centres.index', compact('centres'));

        } catch (Exception $e) {
            Log::error('Error loading centres: ' . $e->getMessage(), [
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load centres. Please try again.');
        }
    }
    
    /**
     * Show the form for creating a new centre
     */
    public function create()
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('centres.index')
                ->with('error', 'Only administrators can create centres.');
        }

        return view('centres.create');
    }

    /**
     * Store a newly created centre
     */
    public function store(Request $request)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('centres.index')
                ->with('error', 'Only administrators can create centres.');
        }

        $validated = $request->validate([
            'centre_id' => 'required|string|max:10|unique:centres',
            'centre_name' => 'required|string|max:255|unique:centres',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postcode' => 'required|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'opening_time' => 'required|date_format:H:i',
            'closing_time' => 'required|date_format:H:i|after:opening_time'
        ]);

        try {
            $centre = Centres::create([
                'centre_id' => strtoupper($validated['centre_id']),
                'centre_name' => $validated['centre_name'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'state' => $validated['state'],
                'postcode' => $validated['postcode'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'capacity' => $validated['capacity'],
                'description' => $validated['description'],
                'opening_time' => $validated['opening_time'],
                'closing_time' => $validated['closing_time'],
                'is_active' => true
            ]);

            return redirect()->route('centres.show', $centre->centre_id)
                ->with('success', 'Centre created successfully!');

        } catch (Exception $e) {
            Log::error('Error creating centre: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the centre.');
        }
    }
    
    /**
     * Display the specified centre
     */
    public function show($id)
    {
        try {
            Log::info('Loading centre details', ['centre_id' => $id, 'user_id' => session('id')]);
            
            $centre = Centres::withCount(['users', 'trainees', 'assets'])
                ->where('centre_id', $id)
                ->firstOrFail();

            // Get centre statistics
            $stats = [
                'total_staff' => $centre->users_count,
                'total_trainees' => $centre->trainees_count,
                'total_assets' => $centre->assets_count,
                'active_sessions' => $this->getActiveSessions($id),
                'utilization_rate' => $this->calculateUtilization($centre)
            ];

            // Get recent activities
            $recentActivities = $this->getRecentActivities($id);
            
            Log::info('Successfully loaded centre details', [
                'centre_id' => $id,
                'centre_name' => $centre->centre_name,
                'stats' => $stats
            ]);
            
            return view('centres.show', compact('centre', 'stats', 'recentActivities'));

        } catch (Exception $e) {
            Log::error('Error showing centre: ' . $e->getMessage(), [
                'centre_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('centres.index')
                ->with('error', 'Centre not found or access denied.');
        }
    }

    /**
     * Show the form for editing the centre
     */
    public function edit($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('centres.show', $id)
                ->with('error', 'Only administrators can edit centres.');
        }

        try {
            $centre = Centres::where('centre_id', $id)->firstOrFail();
            return view('centres.edit', compact('centre'));

        } catch (Exception $e) {
            Log::error('Error loading centre for edit: ' . $e->getMessage());
            return redirect()->route('centres.index')
                ->with('error', 'Centre not found.');
        }
    }

    /**
     * Update the specified centre
     */
    public function update(Request $request, $id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('centres.show', $id)
                ->with('error', 'Only administrators can update centres.');
        }

        try {
            $centre = Centres::where('centre_id', $id)->firstOrFail();

            $validated = $request->validate([
                'centre_name' => 'required|string|max:255|unique:centres,centre_name,' . $centre->centre_id . ',centre_id',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:100',
                'state' => 'required|string|max:100',
                'postcode' => 'required|string|max:10',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'capacity' => 'required|integer|min:1',
                'description' => 'nullable|string',
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i|after:opening_time',
                'is_active' => 'boolean'
            ]);

            $centre->update($validated);

            return redirect()->route('centres.show', $centre->centre_id)
                ->with('success', 'Centre updated successfully!');

        } catch (Exception $e) {
            Log::error('Error updating centre: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the centre.');
        }
    }

    /**
     * Remove the specified centre
     */
    public function destroy($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('centres.index')
                ->with('error', 'Only administrators can delete centres.');
        }

        try {
            $centre = Centres::where('centre_id', $id)->firstOrFail();

            // Check if centre has users or trainees
            if ($centre->users()->count() > 0 || $centre->trainees()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete centre with active users or trainees.');
            }

            $centre->delete();

            return redirect()->route('centres.index')
                ->with('success', 'Centre deleted successfully!');

        } catch (Exception $e) {
            Log::error('Error deleting centre: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the centre.');
        }
    }
    
    /**
     * Display assets for a specific centre
     */
    public function assets($id)
    {
        try {
            Log::info('Loading centre assets', ['centre_id' => $id, 'user_id' => session('id')]);
            
            $centre = Centres::where('centre_id', $id)->firstOrFail();
            
            $assets = Asset::where('centre_name', $centre->centre_name)
                ->orderBy('asset_name')
                ->paginate(20);
            
            // Get asset statistics (simplified for current table structure)
            $totalAssets = Asset::where('centre_name', $centre->centre_name)->count();
            $stats = [
                'total_assets' => $totalAssets,
                'available' => $totalAssets, // Simplified - assume all available
                'in_use' => 0,
                'maintenance' => 0,
                'total_value' => Asset::where('centre_name', $centre->centre_name)->sum('asset_price') ?? 0
            ];
            
            Log::info('Successfully loaded centre assets', [
                'centre_id' => $id,
                'centre_name' => $centre->centre_name,
                'assets_count' => $assets->count(),
                'stats' => $stats
            ]);
            
            return view('centres.assets', compact('centre', 'assets', 'stats'));

        } catch (Exception $e) {
            Log::error('Error loading centre assets: ' . $e->getMessage(), [
                'centre_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('centres.show', $id)
                ->with('error', 'Unable to load assets. Please try again.');
        }
    }

    /**
     * Get active sessions count for a centre
     */
    private function getActiveSessions($centreId)
    {
        return DB::table('activity_sessions')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_sessions.status', 'scheduled')
            ->where('activity_sessions.scheduled_date', '>=', now())
            ->count();
    }

    /**
     * Calculate centre utilization rate
     */
    private function calculateUtilization($centre)
    {
        if ($centre->capacity == 0) {
            return 0;
        }

        return round(($centre->trainees_count / $centre->capacity) * 100, 2);
    }

    /**
     * Get recent activities for a centre
     */
    private function getRecentActivities($centreId)
    {
        return DB::table('activities')
            ->select('id', 'activity_name as name', 'created_at as date', 'is_active')
            ->where('centre_id', $centreId)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}