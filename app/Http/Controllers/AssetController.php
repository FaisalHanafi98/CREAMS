<?php

namespace App\Http\Controllers;

use App\Services\Asset\AssetManagementService;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

/**
 * Enhanced Asset Controller
 * 
 * This controller provides RESTful API endpoints for asset management,
 * utilizing the AssetManagementService for business logic.
 */
class AssetController extends Controller
{
    private AssetManagementService $assetService;

    public function __construct(AssetManagementService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Get paginated assets with advanced filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'status' => 'nullable|in:available,in-use,maintenance,retired,disposed',
                'centre_id' => 'nullable|integer|exists:centres,id',
                'asset_type_id' => 'nullable|integer|exists:asset_types,id',
                'location_id' => 'nullable|integer|exists:asset_locations,id',
                'value_range' => 'nullable|array',
                'value_range.min' => 'nullable|numeric|min:0',
                'value_range.max' => 'nullable|numeric|min:0',
                'per_page' => 'nullable|integer|min:1|max:100',
                'sort_by' => 'nullable|in:name,asset_code,purchase_date,current_value,status',
                'sort_order' => 'nullable|in:asc,desc',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = $validator->validated();
            $assets = $this->assetService->searchAssets($filters);

            return response()->json([
                'success' => true,
                'data' => $assets,
                'meta' => [
                    'current_page' => $assets->currentPage(),
                    'total_pages' => $assets->lastPage(),
                    'total_items' => $assets->total(),
                    'per_page' => $assets->perPage(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching assets', [
                'error' => $e->getMessage(),
                'filters' => $request->all(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch assets',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Store new asset
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'asset_type_id' => 'required|integer|exists:asset_types,id',
                'centre_id' => 'required|integer|exists:centres,id',
                'location_id' => 'required|integer|exists:asset_locations,id',
                'assigned_to_id' => 'nullable|integer|exists:users,id',
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'serial_number' => 'nullable|string|max:100|unique:assets_enhanced',
                'purchase_date' => 'required|date|before_or_equal:today',
                'purchase_price' => 'required|numeric|min:0',
                'current_value' => 'required|numeric|min:0',
                'warranty_date' => 'nullable|date|after:purchase_date',
                'maintenance_interval' => 'nullable|integer|min:1',
                'status' => 'nullable|in:available,in-use,maintenance,retired',
                'notes' => 'nullable|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'rfid_tag' => 'nullable|string|max:50|unique:assets_enhanced',
                'barcode' => 'nullable|string|max:50|unique:assets_enhanced',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('assets', 'public');
                $validatedData['image_path'] = $imagePath;
            }

            // Set default status if not provided
            if (!isset($validatedData['status'])) {
                $validatedData['status'] = Asset::STATUS_AVAILABLE;
            }

            // If assigned to someone, set status to in-use
            if (isset($validatedData['assigned_to_id'])) {
                $validatedData['status'] = Asset::STATUS_IN_USE;
            }

            $asset = $this->assetService->createAsset($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Asset created successfully',
                'data' => $asset->load(['assetType', 'location', 'centre', 'assignedTo'])
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating asset', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Show asset details with comprehensive information
     */
    public function show(Asset $asset): JsonResponse
    {
        try {
            $asset->load([
                'assetType',
                'location',
                'centre',
                'assignedTo',
                'movements.fromLocation',
                'movements.toLocation',
                'movements.movedBy',
                'maintenanceRecords.performedBy',
                'pendingMaintenance'
            ]);

            return response()->json([
                'success' => true,
                'data' => $asset
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching asset details', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch asset details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Update asset
     */
    public function update(Request $request, Asset $asset): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|in:available,in-use,maintenance,retired,disposed',
                'location_id' => 'sometimes|required|integer|exists:asset_locations,id',
                'assigned_to_id' => 'nullable|integer|exists:users,id',
                'current_value' => 'sometimes|required|numeric|min:0',
                'warranty_date' => 'nullable|date',
                'maintenance_interval' => 'nullable|integer|min:1',
                'notes' => 'nullable|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'rfid_tag' => 'nullable|string|max:50|unique:assets_enhanced,rfid_tag,' . $asset->id,
                'barcode' => 'nullable|string|max:50|unique:assets_enhanced,barcode,' . $asset->id,
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'serial_number' => 'nullable|string|max:100|unique:assets_enhanced,serial_number,' . $asset->id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validatedData = $validator->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($asset->image_path && \Storage::disk('public')->exists($asset->image_path)) {
                    \Storage::disk('public')->delete($asset->image_path);
                }
                
                $imagePath = $request->file('image')->store('assets', 'public');
                $validatedData['image_path'] = $imagePath;
            }

            // Business logic for status changes
            if (isset($validatedData['assigned_to_id']) && $validatedData['assigned_to_id']) {
                $validatedData['status'] = Asset::STATUS_IN_USE;
            } elseif (isset($validatedData['assigned_to_id']) && is_null($validatedData['assigned_to_id'])) {
                if ($asset->status === Asset::STATUS_IN_USE) {
                    $validatedData['status'] = Asset::STATUS_AVAILABLE;
                }
            }

            $updatedAsset = $this->assetService->updateAsset($asset, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Asset updated successfully',
                'data' => $updatedAsset->load(['assetType', 'location', 'centre', 'assignedTo'])
            ]);

        } catch (Exception $e) {
            Log::error('Error updating asset', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Soft delete asset
     */
    public function destroy(Asset $asset): JsonResponse
    {
        try {
            // Check if asset can be deleted
            if ($asset->status === Asset::STATUS_IN_USE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete asset that is currently in use'
                ], 400);
            }

            if ($asset->pendingMaintenance()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete asset with pending maintenance'
                ], 400);
            }

            $asset->delete();

            Log::info('Asset deleted', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'deleted_by' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asset deleted successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting asset', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get asset dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'centre_id' => 'nullable|integer|exists:centres,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $centreId = $request->input('centre_id');
            $data = $this->assetService->getDashboardData($centreId);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching asset dashboard data', [
                'error' => $e->getMessage(),
                'centre_id' => $request->input('centre_id'),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Assign asset to user
     */
    public function assign(Request $request, Asset $asset): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'assigned_to_id' => 'required|integer|exists:users,id',
                'reason' => 'nullable|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if (!$asset->canBeAssigned()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Asset cannot be assigned in its current state'
                ], 400);
            }

            $userId = $request->input('assigned_to_id');
            $reason = $request->input('reason', 'Asset Assignment');

            $success = $asset->assignTo($userId, $reason);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Asset assigned successfully',
                    'data' => $asset->fresh()->load(['assignedTo'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign asset'
                ], 500);
            }

        } catch (Exception $e) {
            Log::error('Error assigning asset', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign asset',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get asset statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'centre_id' => 'nullable|integer|exists:centres,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $centreId = $request->input('centre_id');
            $statistics = Asset::getStatistics($centreId);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching asset statistics', [
                'error' => $e->getMessage(),
                'centre_id' => $request->input('centre_id'),
                'user_id' => auth()->id() ?? session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}