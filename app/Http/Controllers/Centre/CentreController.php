<?php

namespace App\Http\Controllers\Centre;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Centre;
use App\Models\Asset;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\AssetParent;
use App\Models\CentreAuditLog;
use App\Models\CentreStatistics;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;

class CentreController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // All authenticated users can view centres, but only admin can perform CRUD operations
        $this->middleware('enhanced.role:admin,supervisor,teacher,ajk');

        // Only admin can create/edit/delete centres
        $this->middleware('enhanced.role:admin')->only(['create', 'store', 'edit', 'update', 'destroy', 'refreshStatistics']);
    }

    /**
     * Display a listing of the centres.
     */
    public function index()
    {
        try {
            Log::info('Loading centres index page', ['user_id' => session('id')]);

            $centres = Centre::withCount(['users', 'trainees', 'assets'])
                ->orderBy('centre_name')
                ->get();

            Log::info('Successfully loaded centres', ['count' => $centres->count()]);

            return view('centres.home', compact('centres'));
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
            'centre_address' => 'required|string',
            'centre_phone' => 'nullable|string|max:20',
            'centre_email' => 'nullable|email|max:255',
            'centre_capacity' => 'required|string|max:10',
            'centre_manager' => 'nullable|string|max:255',
            'centre_manager_contact' => 'nullable|string|max:20',
            'centre_status' => 'required|in:active,inactive,maintenance',
            'centre_description' => 'nullable|string',
            'centre_facilities' => 'nullable|string'
        ]);

        try {
            $centre = Centre::create([
                'centre_id' => strtoupper($validated['centre_id']),
                'centre_name' => $validated['centre_name'],
                'centre_address' => $validated['centre_address'],
                'centre_phone' => $validated['centre_phone'],
                'centre_email' => $validated['centre_email'],
                'centre_capacity' => $validated['centre_capacity'],
                'centre_manager' => $validated['centre_manager'],
                'centre_manager_contact' => $validated['centre_manager_contact'],
                'centre_status' => $validated['centre_status'],
                'centre_description' => $validated['centre_description'],
                'centre_facilities' => $validated['centre_facilities'],
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

            $centre = Centre::withCount(['users', 'trainees', 'assets'])
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
            $centre = Centre::where('centre_id', $id)->firstOrFail();
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
            $centre = Centre::where('centre_id', $id)->firstOrFail();

            $validated = $request->validate([
                'centre_name' => 'required|string|max:255|unique:centres,centre_name,' . $centre->centre_id . ',centre_id',
                'centre_description' => 'nullable|string',
                'address' => 'required|string',
                'centre_phone' => 'nullable|string|max:20',
                'centre_email' => 'nullable|email|max:255',
                'centre_capacity' => 'required|string|max:10',
                'centre_manager' => 'nullable|string|max:255',
                'centre_manager_contact' => 'nullable|string|max:20',
                'centre_status' => 'required|in:active,inactive,maintenance',
                'centre_facilities' => 'nullable|string'
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
            $centre = Centre::where('centre_id', $id)->firstOrFail();

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
    /* public function assets($id)
    {
        try {
            Log::info('Loading centre assets', ['centre_id' => $id, 'user_id' => session('id')]);

            $centre = Centre::where('centre_id', $id)->firstOrFail();

            $assets = Asset::where('centre_id', $centre->centre_id)
                ->orderBy('asset_name')
                ->paginate(20);

            // Get real asset statistics from database
            $baseQuery = Asset::where('centre_id', $centre->centre_id);
            $stats = [
                'total_assets' => $baseQuery->count(),
                'available' => $baseQuery->clone()->where('status', 'available')->count(),
                'in_use' => $baseQuery->clone()->where('status', 'in_use')->count(),
                'maintenance' => $baseQuery->clone()->where('status', 'maintenance')->count(),
                'disposed' => $baseQuery->clone()->where('status', 'disposed')->count(),
                'damaged' => $baseQuery->clone()->where('status', 'damaged')->count(),
                'total_value' => $baseQuery->clone()->sum('purchase_price') ?? 0
            ];

            Log::info('Successfully loaded centre assets', [
                'centre_id' => $id,
                'centre_name' => $centre->centre_name,
                'assets_count' => $assets->count(),
                'stats' => $stats
            ]);

            return view('centres.asset-parents', compact('centre', 'assets', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading centre assets: ' . $e->getMessage(), [
                'centre_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('centres.show', $id)
                ->with('error', 'Unable to load assets. Please try again.');
        }
    } */

    /**
     * Display asset parents for a specific centre
     */
    public function assetParents($id)
    {
        try {
            Log::info('Loading centre assets', ['centre_id' => $id, 'user_id' => session('id')]);

            $centre = Centre::where('centre_id', $id)->firstOrFail();

            $asset_parents = AssetParent::where('centre_id', $centre->centre_id)
                ->orderBy('name')
                ->paginate(20);

            // Get real asset statistics from database
            $baseQuery = Asset::where('centre_id', $centre->centre_id);
            $stats = [
                'total_asset_parents' => $baseQuery->count(),
                'available' => $baseQuery->clone()->where('status', 'available')->count(),
                'in_use' => $baseQuery->clone()->where('status', 'in_use')->count(),
                'maintenance' => $baseQuery->clone()->where('status', 'maintenance')->count(),
                'disposed' => $baseQuery->clone()->where('status', 'disposed')->count(),
                'damaged' => $baseQuery->clone()->where('status', 'damaged')->count(),
                'total_value' => $baseQuery->clone()->sum('purchase_price') ?? 0
            ];

            Log::info('Successfully loaded centre asset parents', [
                'centre_id' => $id,
                'centre_name' => $centre->centre_name,
                'asset_parents_count' => $asset_parents->count(),
                'stats' => $stats
            ]);

            return view('centres.asset-parents', compact('centre', 'asset_parents', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading centre asset parents: ' . $e->getMessage(), [
                'centre_id' => $id,
                'user_id' => session('id'),
                'error' => $e->getTraceAsString()
            ]);
            return redirect()->route('centres.show', $id)
                ->with('error', 'Unable to load asset parents. Please try again.');
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
            ->where('activity_sessions.session_status', 'scheduled')
            ->where('activity_sessions.session_date', '>=', now())
            ->count();
    }

    /**
     * Calculate centre utilization rate
     */
    private function calculateUtilization($centre)
    {
        $capacity = is_numeric($centre->centre_capacity) ? (int)$centre->centre_capacity : 0;

        if ($capacity == 0) {
            return 0;
        }

        return round(($centre->trainees_count / $capacity) * 100, 2);
    }

    /**
     * Get cached centre statistics
     */
    private function getCachedCentreStats($centreId, $forceRefresh = false)
    {
        $cacheKey = "centre_stats_{$centreId}";

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, function () use ($centreId) {
            $centre = Centre::withCount(['users', 'trainees', 'assets'])->find($centreId);

            if (!$centre) {
                return [];
            }

            return [
                'total_staff' => $centre->users_count,
                'total_trainees' => $centre->trainees_count,
                'total_assets' => $centre->assets_count,
                'active_sessions' => $this->getActiveSessions($centreId),
                'utilization_rate' => $this->calculateUtilization($centre),
                'capacity' => $centre->centre_capacity ?? 0,
                'available_capacity' => max(0, ($centre->centre_capacity ?? 0) - $centre->trainees_count)
            ];
        });
    }

    /**
     * Get recent activities for a centre
     */
    private function getRecentActivities($centreId)
    {
        try {
            return DB::table('activities')
                ->select('id', 'activity_name as name', 'created_at as date', 'is_active', 'activity_status')
                ->where('centre_id', $centreId)
                ->where('is_active', true) // Use is_active boolean field
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        } catch (Exception $e) {
            Log::warning('Error getting recent activities: ' . $e->getMessage());
            return collect([]); // Return empty collection on error
        }
    }

    /**
     * Get centre performance metrics API endpoint
     */
    public function getMetrics($id)
    {
        try {
            // Check authentication and authorization
            if (!session()->has('id')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'centre_id' => session('centre_id')
            ];

            // Centre access validation
            if (!in_array($user->role, ['admin']) && $user->centre_id != $id) {
                return response()->json(['message' => 'Access denied'], 403);
            }

            $centre = Centre::findOrFail($id);
            $stats = $this->getCachedCentreStats($id, true);

            // Get historical data for trends
            $historicalStats = $this->getHistoricalStats($id);

            return response()->json([
                'success' => true,
                'centre' => [
                    'id' => $centre->id,
                    'name' => $centre->centre_name,
                    'status' => $centre->centre_status
                ],
                'current_stats' => $stats,
                'historical_stats' => $historicalStats,
                'generated_at' => now()->toISOString()
            ]);
        } catch (Exception $e) {
            Log::error('Error getting centre metrics', [
                'centre_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve metrics'
            ], 500);
        }
    }

    /**
     * Get historical statistics for trends
     */
    private function getHistoricalStats($centreId)
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        return CentreStatistics::where('centre_id', $centreId)
            ->where('last_calculated', '>=', $thirtyDaysAgo)
            ->orderBy('last_calculated')
            ->get()
            ->map(function ($record) {
                return [
                    'date' => $record->last_calculated->toDateString(),
                    'utilization_rate' => $record->utilization_rate,
                    'attendance_rate' => $record->attendance_rate,
                    'total_users' => $record->total_users,
                    'total_trainees' => $record->total_trainees,
                    'total_activities' => $record->total_activities
                ];
            });
    }

    /**
     * Refresh centre statistics on demand
     */
    public function refreshStatistics($id)
    {
        try {
            // Check authentication and authorization
            if (!session()->has('id')) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'centre_id' => session('centre_id')
            ];

            // Centre access validation
            if (!in_array($user->role, ['admin', 'supervisor']) && $user->centre_id != $id) {
                return response()->json(['message' => 'Access denied'], 403);
            }

            $centre = Centre::findOrFail($id);

            // Force refresh statistics
            $stats = $this->getCachedCentreStats($id, true);

            // Clear all related caches
            Cache::forget("centre_details_{$id}");
            Cache::forget("centres_summary_{$user->id}_{$user->role}");

            Log::info('Centre statistics refreshed', [
                'centre_id' => $id,
                'refreshed_by' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Statistics refreshed successfully',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Error refreshing centre statistics', [
                'centre_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh statistics'
            ], 500);
        }
    }
}
