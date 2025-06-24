<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Centres;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class AssetController extends Controller
{
    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        try {
            $query = Asset::with(['centre']);

            // Apply filters
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('asset_name', 'LIKE', "%{$search}%")
                      ->orWhere('asset_code', 'LIKE', "%{$search}%")
                      ->orWhere('brand', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if ($request->has('centre_id') && $request->centre_id !== '') {
                $query->where('centre_id', $request->centre_id);
            }

            $assets = $query->orderBy('created_at', 'desc')->paginate(20);

            // Get statistics
            $stats = [
                'total' => Asset::count(),
                'available' => Asset::where('status', 'available')->count(),
                'in_use' => Asset::where('status', 'in-use')->count(),
                'maintenance' => Asset::where('status', 'maintenance')->count(),
                'total_value' => Asset::sum('current_value')
            ];

            $centres = Centres::all();

            return view('assets.index', compact('assets', 'stats', 'centres'));

        } catch (Exception $e) {
            Log::error('Error loading assets: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load assets.');
        }
    }

    /**
     * Show the form for creating a new asset
     */
    public function create()
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('assets.index')
                ->with('error', 'Only administrators can create assets.');
        }

        $centres = Centres::where('is_active', true)->get();
        $assetTypes = $this->getAssetTypes();
        
        return view('assets.create', compact('centres', 'assetTypes'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('assets.index')
                ->with('error', 'Only administrators can create assets.');
        }

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_code' => 'required|string|max:50|unique:assets',
            'description' => 'nullable|string',
            'centre_id' => 'required|exists:centres,centre_id',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100|unique:assets',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'warranty_date' => 'nullable|date|after:purchase_date',
            'status' => 'required|in:available,in-use,maintenance,retired,disposed'
        ]);

        try {
            DB::beginTransaction();

            $asset = Asset::create([
                'asset_code' => strtoupper($validated['asset_code']),
                'name' => $validated['asset_name'],
                'description' => $validated['description'],
                'centre_id' => $validated['centre_id'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'serial_number' => $validated['serial_number'],
                'purchase_date' => $validated['purchase_date'],
                'purchase_price' => $validated['purchase_price'],
                'current_value' => $validated['current_value'] ?? $validated['purchase_price'],
                'warranty_date' => $validated['warranty_date'],
                'status' => $validated['status']
            ]);

            DB::commit();

            return redirect()->route('assets.show', $asset->id)
                ->with('success', 'Asset created successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating asset: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the asset.');
        }
    }

    /**
     * Display the specified asset
     */
    public function show($id)
    {
        try {
            $asset = Asset::with(['centre'])->findOrFail($id);
            
            // Get asset history
            $history = $this->getAssetHistory($id);
            
            // Get maintenance schedule
            $maintenanceSchedule = $this->getMaintenanceSchedule($id);
            
            return view('assets.show', compact('asset', 'history', 'maintenanceSchedule'));

        } catch (Exception $e) {
            Log::error('Error showing asset: ' . $e->getMessage());
            return redirect()->route('assets.index')
                ->with('error', 'Asset not found.');
        }
    }

    /**
     * Show the form for editing the asset
     */
    public function edit($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('assets.show', $id)
                ->with('error', 'Only administrators can edit assets.');
        }

        try {
            $asset = Asset::findOrFail($id);
            $centres = Centres::where('is_active', true)->get();
            $assetTypes = $this->getAssetTypes();
            
            return view('assets.edit', compact('asset', 'centres', 'assetTypes'));

        } catch (Exception $e) {
            Log::error('Error loading asset for edit: ' . $e->getMessage());
            return redirect()->route('assets.index')
                ->with('error', 'Asset not found.');
        }
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, $id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('assets.show', $id)
                ->with('error', 'Only administrators can update assets.');
        }

        try {
            $asset = Asset::findOrFail($id);

            $validated = $request->validate([
                'asset_name' => 'required|string|max:255',
                'asset_code' => 'required|string|max:50|unique:assets,asset_code,' . $id,
                'description' => 'nullable|string',
                'centre_id' => 'required|exists:centres,centre_id',
                'brand' => 'nullable|string|max:100',
                'model' => 'nullable|string|max:100',
                'serial_number' => 'nullable|string|max:100|unique:assets,serial_number,' . $id,
                'purchase_date' => 'nullable|date',
                'purchase_price' => 'nullable|numeric|min:0',
                'current_value' => 'nullable|numeric|min:0',
                'warranty_date' => 'nullable|date',
                'status' => 'required|in:available,in-use,maintenance,retired,disposed'
            ]);

            $asset->update([
                'asset_code' => strtoupper($validated['asset_code']),
                'name' => $validated['asset_name'],
                'description' => $validated['description'],
                'centre_id' => $validated['centre_id'],
                'brand' => $validated['brand'],
                'model' => $validated['model'],
                'serial_number' => $validated['serial_number'],
                'purchase_date' => $validated['purchase_date'],
                'purchase_price' => $validated['purchase_price'],
                'current_value' => $validated['current_value'],
                'warranty_date' => $validated['warranty_date'],
                'status' => $validated['status']
            ]);

            return redirect()->route('assets.show', $asset->id)
                ->with('success', 'Asset updated successfully!');

        } catch (Exception $e) {
            Log::error('Error updating asset: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the asset.');
        }
    }

    /**
     * Remove the specified asset
     */
    public function destroy($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return redirect()->route('assets.index')
                ->with('error', 'Only administrators can delete assets.');
        }

        try {
            $asset = Asset::findOrFail($id);
            
            // Soft delete instead of hard delete
            $asset->delete();

            return redirect()->route('assets.index')
                ->with('success', 'Asset deleted successfully!');

        } catch (Exception $e) {
            Log::error('Error deleting asset: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while deleting the asset.');
        }
    }

    /**
     * Get asset types
     */
    private function getAssetTypes()
    {
        return [
            'Therapy Equipment',
            'Educational Materials',
            'Sensory Tools',
            'Communication Devices',
            'Mobility Aids',
            'Medical Supplies',
            'Furniture',
            'Electronics',
            'Vehicles',
            'Others'
        ];
    }

    /**
     * Get asset history
     */
    private function getAssetHistory($assetId)
    {
        // Placeholder for asset movement history
        return [];
    }

    /**
     * Get maintenance schedule
     */
    private function getMaintenanceSchedule($assetId)
    {
        // Placeholder for maintenance schedule
        return [];
    }

    /**
     * API: Get assets
     */
    public function getAssetsJson(Request $request)
    {
        try {
            $query = Asset::with(['centre']);

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('asset_code', 'LIKE', "%{$search}%");
                });
            }

            $assets = $query->where('status', '!=', 'disposed')->get();

            return response()->json([
                'success' => true,
                'data' => $assets
            ]);

        } catch (Exception $e) {
            Log::error('API Error fetching assets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch assets'
            ], 500);
        }
    }
}