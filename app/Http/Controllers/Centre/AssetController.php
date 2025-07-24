<?php

namespace App\Http\Controllers\Centre;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\Users;
use App\Models\Centres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AssetController extends Controller
{
    /**
     * Display asset dashboard based on user role
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $role = session('role');
            $centreId = session('centre_id');

            // Get filter parameters
            $search = $request->get('search');
            $category = $request->get('category');
            $status = $request->get('status');
            $condition = $request->get('condition');

            // Build asset query
            $assetsQuery = Asset::with(['category', 'centre', 'assignedTo', 'latestMaintenance']);

            // Apply centre scoping for non-admin users
            if ($role !== 'admin') {
                $assetsQuery->where('centre_id', $centreId);
            }

            // Apply filters
            if ($search) {
                $assetsQuery->search($search);
            }
            if ($category) {
                $assetsQuery->byCategory($category);
            }
            if ($status) {
                $assetsQuery->byStatus($status);
            }
            if ($condition) {
                $assetsQuery->byCondition($condition);
            }

            $assets = $assetsQuery->paginate(20);

            // Get dashboard statistics
            $statsQuery = Asset::query();
            if ($role !== 'admin') {
                $statsQuery->where('centre_id', $centreId);
            }

            $statistics = [
                'total_assets' => $statsQuery->count(),
                'available' => $statsQuery->clone()->byStatus('available')->count(),
                'in_use' => $statsQuery->clone()->byStatus('in_use')->count(),
                'maintenance' => $statsQuery->clone()->byStatus('maintenance')->count(),
                'disposed' => $statsQuery->clone()->byStatus('disposed')->count(),
                'warranty_expiring' => $statsQuery->clone()->warrantyExpiring()->count(),
                'requires_maintenance' => $statsQuery->clone()->requiresMaintenance()->count(),
            ];

            // Get categories for filter dropdown
            $categories = AssetCategory::active()->get();

            // Get recent activity
            $recentActivity = AssetMovement::with(['asset', 'fromUser', 'toUser', 'performedBy'])
                ->when($role !== 'admin', function($query) use ($centreId) {
                    return $query->whereHas('asset', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    });
                })
                ->recent(7)
                ->orderBy('movement_date', 'desc')
                ->limit(10)
                ->get();

            // Get maintenance alerts
            $maintenanceAlerts = AssetMaintenance::getRequiringAttention($centreId);

            return view('assets.dashboard', compact(
                'assets',
                'statistics',
                'categories',
                'recentActivity',
                'maintenanceAlerts',
                'search',
                'category',
                'status',
                'condition'
            ));

        } catch (Exception $e) {
            Log::error('Error in asset dashboard', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error loading asset dashboard');
        }
    }

    /**
     * Show the form for creating a new asset
     */
    public function create()
    {
        if (!session()->has('id')) {
            return redirect()->route('login');
        }

        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            abort(403, 'Unauthorized access');
        }

        $categories = AssetCategory::active()->get();
        $centres = session('role') === 'admin' ? Centres::all() : Centres::where('id', session('centre_id'))->get();

        return view('assets.create', compact('categories', 'centres'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:asset_categories,id',
                'centre_id' => 'required|exists:centres,id',
                'brand' => 'nullable|string|max:255',
                'model' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'warranty_months' => 'nullable|integer|min:0|max:120',
                'condition' => 'required|in:new,good,fair,poor,broken',
                'location' => 'nullable|string|max:255',
                'specifications' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string'
            ]);

            // Generate asset code
            $category = AssetCategory::find($validated['category_id']);
            $assetCode = $this->generateAssetCode($category->code);

            // Handle warranty expiry calculation
            $warrantyExpiry = null;
            if ($validated['purchase_date'] && $validated['warranty_months']) {
                $warrantyExpiry = Carbon::parse($validated['purchase_date'])
                    ->addMonths($validated['warranty_months']);
            }

            // Handle image uploads
            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('assets', $filename, 'public');
                    $images[] = $path;
                }
            }

            // Create asset
            $asset = Asset::create([
                'asset_code' => $assetCode,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'centre_id' => $validated['centre_id'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'serial_number' => $validated['serial_number'],
                'purchase_price' => $validated['purchase_price'],
                'purchase_date' => $validated['purchase_date'],
                'warranty_months' => $validated['warranty_months'] ?? 12,
                'warranty_expiry' => $warrantyExpiry,
                'condition' => $validated['condition'],
                'status' => 'available',
                'location' => $validated['location'],
                'depreciation_rate' => $category->depreciation_rate ?? 20,
                'current_value' => $validated['purchase_price'],
                'specifications' => $validated['specifications'] ?? [],
                'images' => $images,
                'notes' => $validated['notes'],
                'created_by' => session('id')
            ]);

            // Log asset creation
            Log::info('Asset created', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'created_by' => session('id')
            ]);

            return redirect()->route('assets.show', $asset->id)
                ->with('success', 'Asset created successfully');

        } catch (Exception $e) {
            Log::error('Error creating asset', [
                'user_id' => session('id'),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return back()->withInput()
                ->with('error', 'Error creating asset: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified asset
     */
    public function show(Asset $asset)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            // Check centre access
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                abort(403, 'Unauthorized access to this asset');
            }

            // Load relationships
            $asset->load([
                'category',
                'centre',
                'assignedTo',
                'creator',
                'maintenance' => function($query) {
                    $query->orderBy('scheduled_date', 'desc');
                },
                'movements' => function($query) {
                    $query->with(['fromUser', 'toUser', 'performedBy'])
                          ->orderBy('movement_date', 'desc');
                }
            ]);

            // Get maintenance statistics
            $maintenanceStats = [
                'total' => $asset->maintenance()->count(),
                'completed' => $asset->maintenance()->where('status', 'completed')->count(),
                'pending' => $asset->maintenance()->where('status', 'scheduled')->count(),
                'overdue' => $asset->maintenance()->where('scheduled_date', '<', now())
                    ->whereIn('status', ['scheduled', 'in_progress'])->count(),
                'total_cost' => $asset->maintenance()->where('status', 'completed')->sum('cost')
            ];

            // Get upcoming maintenance
            $upcomingMaintenance = $asset->maintenance()
                ->where('status', 'scheduled')
                ->where('scheduled_date', '>=', now())
                ->orderBy('scheduled_date')
                ->first();

            return view('assets.show', compact(
                'asset',
                'maintenanceStats',
                'upcomingMaintenance'
            ));

        } catch (Exception $e) {
            Log::error('Error showing asset', [
                'asset_id' => $asset->id ?? 'unknown',
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error loading asset details');
        }
    }

    /**
     * Show the form for editing the specified asset
     */
    public function edit(Asset $asset)
    {
        if (!session()->has('id')) {
            return redirect()->route('login');
        }

        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            abort(403, 'Unauthorized access');
        }

        // Check centre access
        if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
            abort(403, 'Unauthorized access to this asset');
        }

        $categories = AssetCategory::active()->get();
        $centres = session('role') === 'admin' ? Centres::all() : Centres::where('id', session('centre_id'))->get();
        $users = Users::where('centre_id', $asset->centre_id)->get();

        return view('assets.edit', compact('asset', 'categories', 'centres', 'users'));
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, Asset $asset)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            // Check centre access
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                abort(403, 'Unauthorized access to this asset');
            }

            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_id' => 'required|exists:asset_categories,id',
                'brand' => 'nullable|string|max:255',
                'model' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'warranty_months' => 'nullable|integer|min:0|max:120',
                'condition' => 'required|in:new,good,fair,poor,broken',
                'status' => 'required|in:available,in_use,maintenance,disposed',
                'location' => 'nullable|string|max:255',
                'assigned_to' => 'nullable|exists:users,id',
                'specifications' => 'nullable|array',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string'
            ]);

            // Handle warranty expiry calculation
            $warrantyExpiry = $asset->warranty_expiry;
            if ($validated['purchase_date'] && $validated['warranty_months']) {
                $warrantyExpiry = Carbon::parse($validated['purchase_date'])
                    ->addMonths($validated['warranty_months']);
            }

            // Handle new image uploads
            $images = $asset->images ?? [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('assets', $filename, 'public');
                    $images[] = $path;
                }
            }

            // Handle assignment changes
            $oldAssignedTo = $asset->assigned_to;
            $newAssignedTo = $validated['assigned_to'];

            // Update asset
            $asset->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'serial_number' => $validated['serial_number'],
                'purchase_price' => $validated['purchase_price'],
                'purchase_date' => $validated['purchase_date'],
                'warranty_months' => $validated['warranty_months'],
                'warranty_expiry' => $warrantyExpiry,
                'condition' => $validated['condition'],
                'status' => $validated['status'],
                'location' => $validated['location'],
                'assigned_to' => $newAssignedTo,
                'assigned_date' => $newAssignedTo ? ($oldAssignedTo != $newAssignedTo ? now() : $asset->assigned_date) : null,
                'specifications' => $validated['specifications'] ?? [],
                'images' => $images,
                'notes' => $validated['notes']
            ]);

            // Create movement record if assignment changed
            if ($oldAssignedTo != $newAssignedTo) {
                if ($newAssignedTo) {
                    // Asset assigned
                    AssetMovement::createAssignment(
                        $asset->id,
                        $newAssignedTo,
                        $validated['location'],
                        'Asset assignment via edit',
                        session('id')
                    );
                } elseif ($oldAssignedTo) {
                    // Asset returned
                    AssetMovement::createReturn(
                        $asset->id,
                        $oldAssignedTo,
                        'Asset return via edit',
                        session('id')
                    );
                }
            }

            // Update current value based on depreciation
            $asset->updateDepreciatedValue();

            Log::info('Asset updated', [
                'asset_id' => $asset->id,
                'updated_by' => session('id'),
                'assignment_changed' => $oldAssignedTo != $newAssignedTo
            ]);

            return redirect()->route('assets.show', $asset->id)
                ->with('success', 'Asset updated successfully');

        } catch (Exception $e) {
            Log::error('Error updating asset', [
                'asset_id' => $asset->id,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return back()->withInput()
                ->with('error', 'Error updating asset: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified asset
     */
    public function destroy(Asset $asset)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            if (!in_array(session('role'), ['admin'])) {
                abort(403, 'Only administrators can delete assets');
            }

            // Check centre access
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                abort(403, 'Unauthorized access to this asset');
            }

            // Check if asset is in use
            if ($asset->status === 'in_use' && $asset->assigned_to) {
                return back()->with('error', 'Cannot delete asset that is currently assigned to a user');
            }

            // Delete associated images
            if ($asset->images) {
                foreach ($asset->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Create disposal movement record
            AssetMovement::create([
                'asset_id' => $asset->id,
                'type' => AssetMovement::TYPE_DISPOSAL,
                'reason' => 'Asset deleted from system',
                'performed_by' => session('id'),
                'movement_date' => now()
            ]);

            $assetCode = $asset->asset_code;
            $asset->delete();

            Log::info('Asset deleted', [
                'asset_code' => $assetCode,
                'deleted_by' => session('id')
            ]);

            return redirect()->route('assets.index')
                ->with('success', 'Asset deleted successfully');

        } catch (Exception $e) {
            Log::error('Error deleting asset', [
                'asset_id' => $asset->id,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error deleting asset');
        }
    }

    /**
     * Schedule maintenance for asset
     */
    public function scheduleMaintenance(Request $request, Asset $asset)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            $validated = $request->validate([
                'type' => 'required|in:preventive,corrective,inspection',
                'scheduled_date' => 'required|date|after:today',
                'description' => 'required|string',
                'performed_by' => 'nullable|string|max:255',
                'notes' => 'nullable|string'
            ]);

            AssetMaintenance::create([
                'asset_id' => $asset->id,
                'type' => $validated['type'],
                'scheduled_date' => $validated['scheduled_date'],
                'status' => 'scheduled',
                'performed_by' => $validated['performed_by'],
                'description' => $validated['description'],
                'notes' => $validated['notes'],
                'created_by' => session('id')
            ]);

            return back()->with('success', 'Maintenance scheduled successfully');

        } catch (Exception $e) {
            Log::error('Error scheduling maintenance', [
                'asset_id' => $asset->id,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error scheduling maintenance');
        }
    }

    /**
     * Generate unique asset code
     */
    private function generateAssetCode(string $categoryPrefix): string
    {
        $year = date('y');
        $sequence = Asset::whereYear('created_at', date('Y'))->count() + 1;
        
        return sprintf("%s-%s-%05d", $categoryPrefix, $year, $sequence);
    }

    /**
     * Bulk operations
     */
    public function bulkUpdate(Request $request)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            $validated = $request->validate([
                'asset_ids' => 'required|array',
                'asset_ids.*' => 'exists:assets_enhanced,id',
                'action' => 'required|in:update_status,update_location,schedule_maintenance',
                'status' => 'required_if:action,update_status|in:available,in_use,maintenance,disposed',
                'location' => 'required_if:action,update_location|string|max:255',
                'maintenance_type' => 'required_if:action,schedule_maintenance|in:preventive,corrective,inspection',
                'maintenance_date' => 'required_if:action,schedule_maintenance|date|after:today'
            ]);

            $assets = Asset::whereIn('id', $validated['asset_ids']);

            // Apply centre scoping for non-admin users
            if (session('role') !== 'admin') {
                $assets->where('centre_id', session('centre_id'));
            }

            $assets = $assets->get();
            $updatedCount = 0;

            foreach ($assets as $asset) {
                switch ($validated['action']) {
                    case 'update_status':
                        $asset->update(['status' => $validated['status']]);
                        $updatedCount++;
                        break;

                    case 'update_location':
                        $asset->update(['location' => $validated['location']]);
                        $updatedCount++;
                        break;

                    case 'schedule_maintenance':
                        AssetMaintenance::create([
                            'asset_id' => $asset->id,
                            'type' => $validated['maintenance_type'],
                            'scheduled_date' => $validated['maintenance_date'],
                            'status' => 'scheduled',
                            'description' => 'Bulk scheduled maintenance',
                            'created_by' => session('id')
                        ]);
                        $updatedCount++;
                        break;
                }
            }

            return back()->with('success', "Successfully updated {$updatedCount} assets");

        } catch (Exception $e) {
            Log::error('Error in bulk update', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error performing bulk operation');
        }
    }
}