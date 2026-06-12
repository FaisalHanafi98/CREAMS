<?php

namespace App\Http\Controllers\Centre;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\User;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class AssetController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        // Admin and Supervisors can manage assets
        $this->middleware('enhanced.role:admin,supervisor');

        // Only admin can create/edit/delete assets
        $this->middleware('enhanced.role:admin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display assets for a specific centre
     */
    public function centreAssets(Request $request, $centreId)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
            }

            $role = session('role');

            // Check if user can view this centre's assets
            if ($role !== 'admin' && session('centre_id') !== $centreId) {
                abort(403, 'Unauthorized access to centre assets');
            }

            // Get centre information
            $centre = Centre::where('centre_id', $centreId)->firstOrFail();

            // Get filter parameters
            $search = $request->get('search');
            $category = $request->get('category');
            $status = $request->get('status');
            $condition = $request->get('condition');

            // Build asset query for this specific centre
            $assetsQuery = Asset::with(['category', 'centre', 'assignedTo', 'latestMaintenance'])
                ->where('centre_id', $centreId);

            // Apply filters
            if ($search) {
                $assetsQuery->where(function ($query) use ($search) {
                    $query->where('asset_name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('asset_description', 'like', "%{$search}%");
                });
            }

            if ($category) {
                $assetsQuery->where('category_id', $category);
            }

            if ($status) {
                $assetsQuery->where('status', $status);
            }

            if ($condition) {
                $assetsQuery->where('condition', $condition);
            }

            $assets = $assetsQuery->paginate(20);

            // Get statistics for this centre
            $statsQuery = Asset::where('centre_id', $centreId);
            $statistics = [
                'total_assets' => $statsQuery->count(),
                'available' => $statsQuery->clone()->where('status', 'available')->count(),
                'in_use' => $statsQuery->clone()->where('status', 'in_use')->count(),
                'maintenance' => $statsQuery->clone()->where('status', 'maintenance')->count(),
                'disposed' => $statsQuery->clone()->where('status', 'disposed')->count(),
            ];

            // Get categories for filter dropdown
            // asset_categories has no status column — the model's active() scope uses is_active
            $categories = AssetCategory::active()->get();

            return view('centres.asset-parents.index', compact('assets', 'centre', 'categories', 'statistics'));
        } catch (Exception $e) {
            Log::error('Error displaying centre assets: ' . $e->getMessage(), [
                'centre_id' => $centreId,
                'user_id' => session('id')
            ]);

            return redirect()->back()->with('error', 'Unable to load centre assets.');
        }
    }

    /**
     * Display asset dashboard based on user role
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
            }

            $role = session('role');
            $centreId = session('centre_id');

            // Get filter parameters
            $search = $request->get('search');
            $category = $request->get('category');
            $status = $request->get('status');
            $condition = $request->get('condition');
            $centreFilter = $request->get('centre');

            // Build asset query
            $assetsQuery = Asset::with(['category', 'centre', 'assignedTo', 'latestMaintenance']);

            // Apply centre scoping for non-admin users
            if ($role !== 'admin') {
                $assetsQuery->where('centre_id', $centreId);
            } elseif ($centreFilter) {
                // Admin users can filter by centre
                $assetsQuery->where('centre_id', $centreFilter);
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

            // Get recent activity (asset_movements table may not exist in all environments)
            try {
                $recentActivity = AssetMovement::with(['asset', 'fromUser', 'toUser', 'performedBy'])
                    ->when($role !== 'admin', function ($query) use ($centreId) {
                        return $query->whereHas('asset', function ($q) use ($centreId) {
                            $q->where('centre_id', $centreId);
                        });
                    })
                    ->recent(7)
                    ->orderBy('movement_date', 'desc')
                    ->limit(10)
                    ->get();
            } catch (Exception $e) {
                Log::warning('asset_movements table unavailable: ' . $e->getMessage());
                $recentActivity = collect();
            }

            // Get maintenance alerts
            $maintenanceAlerts = AssetMaintenance::getRequiringAttention($centreId);

            // Prepare stats for the dashboard view ($statistics keys: available, in_use, maintenance)
            $stats = [
                'total' => $statistics['total_assets'] ?? 0,
                'available' => $statistics['available'] ?? 0,
                'rented' => $statistics['in_use'] ?? 0,
                'maintenance' => $statistics['maintenance'] ?? 0
            ];

            return view('assets.dashboard', compact(
                'assets',
                'statistics',
                'stats',
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
    public function create(Request $request)
    {
        if (!session()->has('id')) {
            return redirect()->route('auth.loginpage');
        }

        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            abort(403, 'Unauthorized access');
        }

        // Get pre-selected centre from URL parameter
        $selectedCentre = $request->get('centre');

        $categories = AssetCategory::active()->get();
        $centres = session('role') === 'admin' ? Centre::all() : Centre::where('centre_id', session('centre_id'))->get();

        return view('assets.create', compact('categories', 'centres', 'selectedCentre'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            // Determine centre_id for validation and assignment
            $userRole = session('role');
            $sessionCentreId = session('centre_id');

            // For non-admin users, force centre_id to their assigned centre
            if ($userRole !== 'admin' && $sessionCentreId) {
                $request->merge(['centre_id' => $sessionCentreId]);
            }

            // Validate input
            $validated = $request->validate([
                'asset_name' => 'required|string|max:255',
                'asset_description' => 'nullable|string',
                'category_id' => 'required|exists:asset_categories,id',
                'centre_id' => 'required|exists:centres,centre_id',
                'asset_tag' => 'nullable|string|max:255',
                'manufacturer' => 'nullable|string|max:255',
                'model_number' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'warranty_months' => 'nullable|integer|min:0|max:120',
                'condition' => 'required|in:new,excellent,good,fair,poor,broken',
                'status' => 'required|in:available,in_use,maintenance,retired',
                'location_id' => 'nullable|exists:asset_locations,id',
                'type_id' => 'nullable|exists:asset_parents,id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string'
            ]);

            // Generate asset tag if not provided
            $assetTag = $validated['asset_tag'] ?? null;
            if (empty($assetTag)) {
                $category = AssetCategory::find($validated['category_id']);
                $assetTag = $this->generateAssetCode($category->code ?? 'AST');
            }

            // Handle warranty expiry calculation
            $warrantyExpiry = null;
            if (isset($validated['purchase_date']) && isset($validated['warranty_months'])) {
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
                'asset_tag' => $assetTag,
                'asset_name' => $validated['asset_name'],
                'asset_description' => $validated['asset_description'] ?? null,
                'category_id' => $validated['category_id'],
                'type_id' => $validated['type_id'] ?? null,
                'centre_id' => $validated['centre_id'],
                'location_id' => $validated['location_id'] ?? null,
                'manufacturer' => $validated['manufacturer'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'purchase_price' => $validated['purchase_price'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'warranty_expiry' => $warrantyExpiry,
                'condition' => $validated['condition'],
                'status' => $validated['status'],
                'images' => $images,
                'notes' => $validated['notes'] ?? null,
                'is_active' => true
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
    public function show($id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
            }

            $asset = Asset::findOrFail($id);

            // Check centre access
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                abort(403, 'Unauthorized access to this asset');
            }

            // Load relationships
            $asset->load([
                'category',
                'centre',
                'assignedTo',
                'maintenance' => function ($query) {
                    $query->orderBy('scheduled_date', 'desc');
                },
                'movements' => function ($query) {
                    $query->orderBy('movement_date', 'desc');
                }
            ]);

            // Resolve moved_by user names for movement history
            $movedByIds = $asset->movements->pluck('moved_by_user_id')->filter()->unique();
            $movedByUsers = User::whereIn('id', $movedByIds)->pluck('name', 'id');

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
                'upcomingMaintenance',
                'movedByUsers'
            ));
        } catch (Exception $e) {
            Log::error('Error showing asset', [
                'asset_id' => $id ?? 'unknown',
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('assets.index')->with('error', 'Error loading asset details');
        }
    }

    /**
     * Show the form for editing the specified asset
     */
    public function edit($id)
    {
        if (!session()->has('id')) {
            return redirect()->route('auth.loginpage');
        }

        $asset = Asset::findOrFail($id);

        if (!in_array(session('role'), ['admin', 'supervisor'])) {
            abort(403, 'Unauthorized access');
        }

        // Check centre access
        if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
            abort(403, 'Unauthorized access to this asset');
        }

        $categories = AssetCategory::active()->get();
        $centres = session('role') === 'admin'
            ? Centre::all()
            : Centre::where('centre_id', session('centre_id'))->get();
        $users = User::where('centre_id', $asset->centre_id)->get();

        return view('assets.edit', compact('asset', 'categories', 'centres', 'users'));
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, $id)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
            }

            $asset = Asset::findOrFail($id);

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            // Check centre access
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                abort(403, 'Unauthorized access to this asset');
            }

            // Validate input
            $validated = $request->validate([
                'asset_name' => 'required|string|max:255',
                'asset_description' => 'nullable|string',
                'category_id' => 'required|exists:asset_categories,id',
                'manufacturer' => 'nullable|string|max:255',
                'model_number' => 'nullable|string|max:255',
                'serial_number' => 'nullable|string|max:255',
                'purchase_price' => 'nullable|numeric|min:0',
                'purchase_date' => 'nullable|date',
                'warranty_months' => 'nullable|integer|min:0|max:120',
                'condition' => 'required|in:new,good,fair,poor,broken',
                'status' => 'required|in:available,in_use,maintenance,disposed',
                'location_id' => 'nullable|exists:asset_locations,id',
                'type_id' => 'nullable|exists:asset_parents,id',
                'assigned_to_user' => 'nullable|exists:staffs,id',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'notes' => 'nullable|string'
            ]);

            // Handle warranty expiry calculation
            $warrantyExpiry = $asset->warranty_expiry;
            if (isset($validated['purchase_date']) && isset($validated['warranty_months'])) {
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
            $oldAssignedTo = $asset->assigned_to_user;
            $newAssignedTo = $validated['assigned_to_user'] ?? null;

            // Update asset
            $asset->update([
                'asset_name' => $validated['asset_name'],
                'asset_description' => $validated['asset_description'] ?? null,
                'category_id' => $validated['category_id'],
                'type_id' => $validated['type_id'] ?? null,
                'manufacturer' => $validated['manufacturer'] ?? null,
                'model_number' => $validated['model_number'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'purchase_price' => $validated['purchase_price'] ?? null,
                'purchase_date' => $validated['purchase_date'] ?? null,
                'warranty_expiry' => $warrantyExpiry,
                'condition' => $validated['condition'],
                'status' => $validated['status'],
                'location_id' => $validated['location_id'] ?? null,
                'assigned_to_user' => $newAssignedTo,
                'images' => $images,
                'notes' => $validated['notes'] ?? null
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
                return redirect()->route('auth.loginpage');
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
     * Display all maintenance records
     */
    public function maintenance(Request $request)
    {
        $centreId = session('centre_id');
        $role = session('role');

        $query = \App\Models\AssetMaintenance::with('asset');

        if ($role !== 'admin') {
            $query->whereHas('asset', fn($q) => $q->where('centre_id', $centreId));
        }

        $records = $query->orderByDesc('scheduled_date')->get();

        return view('assets.maintenance', compact('records'));
    }

    /**
     * Display all movement records
     */
    public function movements(Request $request)
    {
        $centreId = session('centre_id');
        $role = session('role');

        $query = \App\Models\AssetMovement::with('asset');

        if ($role !== 'admin') {
            $query->whereHas('asset', fn($q) => $q->where('centre_id', $centreId));
        }

        $records = $query->orderByDesc('movement_date')->get();

        return view('assets.movements', compact('records'));
    }

    /**
     * Display asset reports
     */
    public function reports(Request $request)
    {
        $centreId = session('centre_id');
        $role = session('role');

        $baseQuery = Asset::query();
        if ($role !== 'admin') {
            $baseQuery->where('centre_id', $centreId);
        }

        $summary = [
            'total_assets' => $baseQuery->count(),
            'available' => $baseQuery->where('status', 'available')->count(),
            'in_use' => $baseQuery->where('status', 'in_use')->count(),
            'total_value' => $baseQuery->sum('purchase_price'),
        ];

        $byCategory = DB::table('assets')
            ->when($role !== 'admin', fn($q) => $q->where('centre_id', $centreId))
            ->select(
                'category_name',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(purchase_price) as total_value')
            )
            ->groupBy('category_name')
            ->orderByDesc('count')
            ->get();

        $byStatus = DB::table('assets')
            ->when($role !== 'admin', fn($q) => $q->where('centre_id', $centreId))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        return view('assets.reports', compact('summary', 'byCategory', 'byStatus'));
    }

    /**
     * Schedule maintenance for asset
     */
    public function scheduleMaintenance(Request $request, Asset $asset)
    {
        try {
            if (!session()->has('id')) {
                return redirect()->route('auth.loginpage');
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
                return redirect()->route('auth.loginpage');
            }

            if (!in_array(session('role'), ['admin', 'supervisor'])) {
                abort(403, 'Unauthorized access');
            }

            $validated = $request->validate([
                'asset_ids' => 'required|array',
                'asset_ids.*' => 'exists:assets,id',
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

    /**
     * Rent/Use an asset
     */
    public function rentAsset(Request $request, $id)
    {
        try {
            $asset = Asset::findOrFail($id);

            $validated = $request->validate([
                'user_name' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'expected_return_date' => 'nullable|date|after:today',
                'purpose' => 'nullable|string|max:500'
            ]);

            // Check if enough quantity is available
            $availableQuantity = $asset->quantity - ($asset->rented_quantity ?? 0);
            if ($validated['quantity'] > $availableQuantity) {
                return back()->with('error', "Only {$availableQuantity} units available for use.");
            }

            // Update asset rental status
            $asset->rented_quantity = ($asset->rented_quantity ?? 0) + $validated['quantity'];
            $asset->current_user = $validated['user_name'];
            $asset->status = $asset->rented_quantity >= $asset->quantity ? 'rented' : 'partially_rented';
            $asset->save();

            // Log the rental activity
            Log::info('Asset rented', [
                'asset_id' => $asset->id,
                'user_name' => $validated['user_name'],
                'quantity' => $validated['quantity'],
                'rented_by' => session('id')
            ]);

            return back()->with('success', "Asset successfully assigned to {$validated['user_name']}");
        } catch (Exception $e) {
            Log::error('Error renting asset', [
                'asset_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return back()->with('error', 'Failed to assign asset: ' . $e->getMessage());
        }
    }

    /**
     * Return an asset
     */
    public function returnAsset(Request $request, $id)
    {
        try {
            $asset = Asset::findOrFail($id);

            // Reset rental status
            $asset->rented_quantity = 0;
            $asset->current_user = null;
            $asset->status = 'available';
            $asset->save();

            // Log the return activity
            Log::info('Asset returned', [
                'asset_id' => $asset->id,
                'returned_by' => session('id')
            ]);

            return back()->with('success', 'Asset marked as returned and available for use');
        } catch (Exception $e) {
            Log::error('Error returning asset', [
                'asset_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return back()->with('error', 'Failed to return asset: ' . $e->getMessage());
        }
    }
}
