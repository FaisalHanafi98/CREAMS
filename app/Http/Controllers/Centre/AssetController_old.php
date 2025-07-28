<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Centre;
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
                $query->search($search);
            }

            if ($request->has('type') && $request->type !== '') {
                $query->ofType($request->type);
            }

            if ($request->has('centre') && $request->centre !== '') {
                $query->forCentre($request->centre);
            }

            $assets = $query->orderBy('created_at', 'desc')->paginate(20);

            // Get statistics for current asset structure
            $stats = [
                'total' => Asset::count(),
                'types' => Asset::distinct('asset_type')->count('asset_type'),
                'centres' => Asset::distinct('centre_id')->count('centre_id'),
                'total_quantity' => Asset::count(), // assets_enhanced doesn't have quantity field
                'total_value' => Asset::sum('purchase_price')
            ];

            $centres = Centre::all();

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

        $centres = Centre::all();
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
            'asset_type' => 'required|string|max:255',
            'asset_brand' => 'nullable|string|max:255',
            'centre_name' => 'required|string|exists:centres,centre_name',
            'asset_price' => 'nullable|numeric|min:0',
            'asset_quantity' => 'required|integer|min:1',
            'asset_note' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Generate unique asset ID
            $assetId = 'AST-' . strtoupper(uniqid());
            
            $asset = Asset::create([
                'asset_id' => $assetId,
                'asset_name' => $validated['asset_name'],
                'asset_type' => $validated['asset_type'],
                'asset_brand' => $validated['asset_brand'],
                'centre_name' => $validated['centre_name'],
                'asset_price' => $validated['asset_price'] ?? 0,
                'asset_quantity' => $validated['asset_quantity'],
                'asset_note' => $validated['asset_note'],
                'asset_avatar' => 'default-asset.png',
                'asset_last_updated' => now()
            ]);

            DB::commit();

            return redirect()->route('assets.index')
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
            $asset = Asset::with(['centre'])->where('asset_id', $id)->firstOrFail();
            
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
            $asset = Asset::where('asset_id', $id)->firstOrFail();
            $centres = Centre::where('is_active', true)->get();
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
            $asset = Asset::where('asset_id', $id)->firstOrFail();

            $validated = $request->validate([
                'asset_name' => 'required|string|max:255',
                'asset_type' => 'required|string|max:255',
                'asset_brand' => 'nullable|string|max:255',
                'centre_name' => 'required|string|exists:centres,centre_name',
                'asset_price' => 'nullable|numeric|min:0',
                'asset_quantity' => 'required|integer|min:0',
                'asset_note' => 'nullable|string'
            ]);

            $asset->update([
                'asset_name' => $validated['asset_name'],
                'asset_type' => $validated['asset_type'],
                'asset_brand' => $validated['asset_brand'],
                'centre_name' => $validated['centre_name'],
                'asset_price' => $validated['asset_price'] ?? 0,
                'asset_quantity' => $validated['asset_quantity'],
                'asset_note' => $validated['asset_note'],
                'asset_last_updated' => now()
            ]);

            return redirect()->route('assets.show', $asset->asset_id)
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
            $asset = Asset::where('asset_id', $id)->firstOrFail();
            
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

    /**
     * Show asset reports and analytics
     */
    public function reports(Request $request)
    {
        try {
            $centres = Centre::all();
            $assetTypes = Asset::distinct('asset_type')->whereNotNull('asset_type')->pluck('asset_type')->sort();

            // Basic Analytics
            $analytics = [
                'total_assets' => Asset::count(),
                'total_value' => Asset::sum('purchase_price'),
                'utilization_rate' => $this->calculateUtilizationRate(),
                'maintenance_due' => Asset::where('asset_quantity', '<=', 5)->count(),
                'active_centres' => Centre::count(),
                'asset_categories' => Asset::distinct('asset_type')->count('asset_type')
            ];

            // Chart Data
            $chartData = [
                'status' => [
                    'available' => Asset::where('asset_quantity', '>', 10)->count(),
                    'in_use' => Asset::whereBetween('asset_quantity', [6, 10])->count(),
                    'maintenance' => Asset::whereBetween('asset_quantity', [1, 5])->count(),
                    'retired' => Asset::where('asset_quantity', 0)->count()
                ],
                'centres' => Asset::selectRaw('centre_id, COUNT(*) as count')
                                  ->groupBy('centre_id')
                                  ->pluck('count', 'centre_id')
                                  ->toArray(),
                'types' => Asset::selectRaw('asset_type, COUNT(*) as count')
                                ->whereNotNull('asset_type')
                                ->groupBy('asset_type')
                                ->pluck('count', 'asset_type')
                                ->toArray(),
                'values' => Asset::selectRaw('centre_id, SUM(purchase_price) as total_value')
                                 ->groupBy('centre_id')
                                 ->pluck('total_value', 'centre_id')
                                 ->toArray()
            ];

            // Utilization Report
            $utilizationReport = $this->generateUtilizationReport();

            // Centre Report
            $centreReport = $this->generateCentreReport();

            // High Value Asset
            $highValueAssets = Asset::where('asset_price', '>', 1000)
                                   ->orderBy('asset_price', 'desc')
                                   ->limit(20)
                                   ->get();

            return view('assets.reports', compact(
                'centres', 'assetTypes', 'analytics', 'chartData', 
                'utilizationReport', 'centreReport', 'highValueAssets'
            ));

        } catch (Exception $e) {
            Log::error('Error loading asset reports: ' . $e->getMessage());
            return redirect()->route('assets.index')
                ->with('error', 'Unable to load reports.');
        }
    }

    /**
     * Get report data for AJAX requests
     */
    public function getReportData(Request $request)
    {
        try {
            $query = Asset::query();

            // Apply filters
            if ($request->filled('centre')) {
                $query->where('centre_name', $request->centre);
            }

            if ($request->filled('type')) {
                $query->where('asset_type', $request->type);
            }

            if ($request->filled('date_range') && $request->date_range !== 'all') {
                $days = (int) $request->date_range;
                $query->where('created_at', '>=', now()->subDays($days));
            }

            $assets = $query->get();

            // Generate updated chart data
            $chartData = [
                'status' => [
                    'available' => $assets->where('asset_quantity', '>', 10)->count(),
                    'in_use' => $assets->whereBetween('asset_quantity', [6, 10])->count(),
                    'maintenance' => $assets->whereBetween('asset_quantity', [1, 5])->count(),
                    'retired' => $assets->where('asset_quantity', 0)->count()
                ],
                'centres' => $assets->groupBy('centre_name')->map->count()->toArray(),
                'types' => $assets->groupBy('asset_type')->map->count()->toArray(),
                'values' => $assets->groupBy('centre_name')->map(function($items) {
                    return $items->sum('asset_price');
                })->toArray()
            ];

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching report data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to fetch report data'
            ], 500);
        }
    }

    /**
     * Export reports
     */
    public function exportReports(Request $request)
    {
        try {
            $format = $request->get('format', 'pdf');
            
            // Get filtered data
            $query = Asset::query();
            
            if ($request->filled('centre')) {
                $query->where('centre_name', $request->centre);
            }

            if ($request->filled('type')) {
                $query->where('asset_type', $request->type);
            }

            $assets = $query->get();

            if ($format === 'excel') {
                return $this->exportToExcel($assets);
            } else {
                return $this->exportToPdf($assets);
            }

        } catch (Exception $e) {
            Log::error('Error exporting reports: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Unable to export report.');
        }
    }

    /**
     * Calculate utilization rate
     */
    private function calculateUtilizationRate(): int
    {
        $totalAssets = Asset::count();
        if ($totalAssets === 0) {
            return 0;
        }

        $inUseAssets = Asset::where('asset_quantity', '>', 0)->count();
        return round(($inUseAssets / $totalAssets) * 100);
    }

    /**
     * Generate utilization report
     */
    private function generateUtilizationReport(): array
    {
        $report = [];
        $assetTypes = Asset::distinct('asset_type')->whereNotNull('asset_type')->pluck('asset_type');

        foreach ($assetTypes as $type) {
            $assets = Asset::where('asset_type', $type);
            $total = $assets->count();
            $inUse = $assets->where('asset_quantity', '>', 0)->count();
            $available = $assets->where('asset_quantity', '>', 10)->count();
            
            $report[] = [
                'type' => $type,
                'total' => $total,
                'in_use' => $inUse,
                'available' => $available,
                'utilization' => $total > 0 ? round(($inUse / $total) * 100) : 0,
                'avg_age' => 'N/A', // Would need purchase_date to calculate
                'total_value' => $assets->sum('asset_price')
            ];
        }

        return $report;
    }

    /**
     * Generate centre report
     */
    private function generateCentreReport(): array
    {
        $report = [];
        $centres = Centre::all();

        foreach ($centres as $centre) {
            $assets = Asset::where('centre_id', $centre->centre_id);
            
            $report[] = [
                'name' => $centre->centre_name,
                'total_assets' => $assets->count(),
                'asset_types' => $assets->distinct('asset_type')->count('asset_type'),
                'total_value' => $assets->sum('asset_price'),
                'maintenance_due' => $assets->where('asset_quantity', '<=', 5)->count(),
                'last_updated' => $assets->latest('asset_last_updated')->value('asset_last_updated') 
                                  ? $assets->latest('asset_last_updated')->value('asset_last_updated')->format('M j, Y')
                                  : 'Never'
            ];
        }

        return $report;
    }

    /**
     * Export to Excel placeholder
     */
    private function exportToExcel($assets)
    {
        // This would use Laravel Excel package
        // For now, return CSV format
        $filename = 'asset_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($assets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Asset ID', 'Name', 'Type', 'Centre', 'Quantity', 'Price', 'Last Updated']);

            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->asset_id,
                    $asset->asset_name,
                    $asset->asset_type,
                    $asset->centre_name,
                    $asset->asset_quantity,
                    $asset->asset_price,
                    $asset->asset_last_updated ? $asset->asset_last_updated->format('Y-m-d') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF placeholder
     */
    private function exportToPdf($assets)
    {
        // This would use DomPDF or similar package
        // For now, redirect back with message
        return redirect()->back()
            ->with('info', 'PDF export feature will be available soon.');
    }

    /**
     * Show maintenance schedule
     */
    public function maintenance(Request $request)
    {
        try {
            $centres = Centre::all();
            $assetTypes = Asset::distinct('asset_type')->whereNotNull('asset_type')->pluck('asset_type')->sort();
            $assets = Asset::all();

            // Generate mock maintenance schedule data
            $maintenanceSchedule = $this->generateMaintenanceSchedule();

            // Calculate statistics
            $statistics = [
                'overdue' => count(array_filter($maintenanceSchedule, function($item) {
                    return $item['status'] === 'overdue';
                })),
                'due_soon' => count(array_filter($maintenanceSchedule, function($item) {
                    return $item['status'] === 'due_soon';
                })),
                'scheduled' => count(array_filter($maintenanceSchedule, function($item) {
                    return $item['status'] === 'scheduled';
                })),
                'completed_this_month' => count(array_filter($maintenanceSchedule, function($item) {
                    return $item['status'] === 'completed';
                }))
            ];

            return view('assets.maintenance', compact(
                'centres', 'assetTypes', 'assets', 'maintenanceSchedule', 'statistics'
            ));

        } catch (Exception $e) {
            Log::error('Error loading maintenance schedule: ' . $e->getMessage());
            return redirect()->route('assets.index')
                ->with('error', 'Unable to load maintenance schedule.');
        }
    }

    /**
     * Schedule maintenance
     */
    public function scheduleMaintenance(Request $request)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to schedule maintenance'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'maintenance_type' => 'required|in:routine,preventive,corrective,emergency,inspection',
                'scheduled_date' => 'required|date|after_or_equal:today',
                'priority' => 'required|in:low,medium,high',
                'description' => 'nullable|string|max:1000',
                'estimated_cost' => 'nullable|numeric|min:0',
                'assigned_to' => 'nullable|string|max:255'
            ]);

            // For now, just return success
            // In a real implementation, this would save to a maintenance_schedules table
            
            Log::info('Maintenance scheduled', [
                'asset_id' => $validated['asset_id'],
                'scheduled_by' => session('id'),
                'data' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance scheduled successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error scheduling maintenance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to schedule maintenance'
            ], 500);
        }
    }

    /**
     * Mark maintenance as completed
     */
    public function completeMaintenance($id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to complete maintenance'
            ], 403);
        }

        try {
            // For now, just log the completion
            // In a real implementation, this would update the maintenance record
            
            Log::info('Maintenance completed', [
                'maintenance_id' => $id,
                'completed_by' => session('id'),
                'completed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance marked as completed'
            ]);

        } catch (Exception $e) {
            Log::error('Error completing maintenance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to complete maintenance'
            ], 500);
        }
    }

    /**
     * Reschedule maintenance
     */
    public function rescheduleMaintenance(Request $request, $id)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to reschedule maintenance'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'scheduled_date' => 'required|date|after_or_equal:today'
            ]);

            // For now, just log the reschedule
            // In a real implementation, this would update the maintenance record
            
            Log::info('Maintenance rescheduled', [
                'maintenance_id' => $id,
                'new_date' => $validated['scheduled_date'],
                'rescheduled_by' => session('id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Maintenance rescheduled successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error rescheduling maintenance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to reschedule maintenance'
            ], 500);
        }
    }

    /**
     * Filter maintenance schedule
     */
    public function filterMaintenance(Request $request)
    {
        try {
            $maintenanceSchedule = $this->generateMaintenanceSchedule();

            // Apply filters
            if ($request->filled('status')) {
                $maintenanceSchedule = array_filter($maintenanceSchedule, function($item) use ($request) {
                    return $item['status'] === $request->status;
                });
            }

            if ($request->filled('priority')) {
                $maintenanceSchedule = array_filter($maintenanceSchedule, function($item) use ($request) {
                    return $item['priority'] === $request->priority;
                });
            }

            if ($request->filled('centre')) {
                $maintenanceSchedule = array_filter($maintenanceSchedule, function($item) use ($request) {
                    return $item['centre_name'] === $request->centre;
                });
            }

            if ($request->filled('type')) {
                $maintenanceSchedule = array_filter($maintenanceSchedule, function($item) use ($request) {
                    return $item['asset_type'] === $request->type;
                });
            }

            return response()->json([
                'success' => true,
                'maintenance' => array_values($maintenanceSchedule)
            ]);

        } catch (Exception $e) {
            Log::error('Error filtering maintenance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to filter maintenance'
            ], 500);
        }
    }

    /**
     * Generate mock maintenance schedule
     */
    private function generateMaintenanceSchedule(): array
    {
        $assets = Asset::limit(10)->get();
        $schedule = [];

        $maintenanceTypes = ['routine', 'preventive', 'corrective', 'inspection'];
        $priorities = ['low', 'medium', 'high'];
        $statuses = ['overdue', 'due_soon', 'scheduled', 'in_progress', 'completed'];

        foreach ($assets as $index => $asset) {
            $status = $statuses[array_rand($statuses)];
            
            // Generate dates based on status
            $dueDate = match($status) {
                'overdue' => now()->subDays(rand(1, 30)),
                'due_soon' => now()->addDays(rand(1, 7)),
                'scheduled' => now()->addDays(rand(8, 30)),
                'in_progress' => now()->subDays(rand(1, 5)),
                'completed' => now()->subDays(rand(1, 15))
            };

            $schedule[] = [
                'id' => 'MAINT-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'asset_id' => $asset->id,
                'asset_name' => $asset->asset_name,
                'asset_image' => asset('images/default-asset.png'),
                'asset_type' => $asset->asset_type,
                'centre_name' => $asset->centre_name ?? 'Unassigned',
                'maintenance_type' => $maintenanceTypes[array_rand($maintenanceTypes)],
                'status' => $status,
                'status_class' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'due_date' => $dueDate->format('M j, Y'),
                'description' => $this->getMaintenanceDescription($maintenanceTypes[array_rand($maintenanceTypes)])
            ];
        }

        // Sort by due date
        usort($schedule, function($a, $b) {
            return strtotime($a['due_date']) - strtotime($b['due_date']);
        });

        return $schedule;
    }

    /**
     * Get maintenance description
     */
    private function getMaintenanceDescription($type): string
    {
        $descriptions = [
            'routine' => 'Regular maintenance check and cleaning',
            'preventive' => 'Preventive maintenance to avoid future issues',
            'corrective' => 'Fix identified issues and problems',
            'inspection' => 'Thorough inspection and safety check'
        ];

        return $descriptions[$type] ?? 'General maintenance work';
    }

    /**
     * Show asset movements and transfers
     */
    public function movements(Request $request)
    {
        try {
            $centres = Centre::all();
            $assets = Asset::all();

            // Generate movement history
            $movements = $this->generateMovementHistory();
            $recentMovements = array_slice($movements, 0, 8);

            // Calculate statistics
            $statistics = [
                'total_movements' => count($movements),
                'pending_transfers' => count(array_filter($movements, function($item) {
                    return $item['status'] === 'pending';
                })),
                'active_assignments' => count(array_filter($movements, function($item) {
                    return $item['type'] === 'assignment' && $item['status'] === 'completed';
                })),
                'this_month' => count(array_filter($movements, function($item) {
                    return strtotime($item['date']) >= strtotime('first day of this month');
                }))
            ];

            return view('assets.movements', compact(
                'centres', 'assets', 'movements', 'recentMovements', 'statistics'
            ));

        } catch (Exception $e) {
            Log::error('Error loading asset movements: ' . $e->getMessage());
            return redirect()->route('assets.index')
                ->with('error', 'Unable to load movements.');
        }
    }

    /**
     * Record asset movement
     */
    public function recordMovement(Request $request)
    {
        $role = session('role');
        
        if (!in_array($role, ['admin', 'supervisor'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to record movements'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'asset_id' => 'required|exists:assets,id',
                'movement_type' => 'required|in:transfer,relocation,assignment,return',
                'from_location' => 'required|string|max:255',
                'to_location' => 'required|string|max:255',
                'movement_date' => 'required|date|before_or_equal:today',
                'reason' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            // For now, just log the movement
            // In a real implementation, this would save to an asset_movements table
            
            Log::info('Asset movement recorded', [
                'asset_id' => $validated['asset_id'],
                'recorded_by' => session('id'),
                'data' => $validated
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movement recorded successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error recording movement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to record movement'
            ], 500);
        }
    }

    /**
     * Approve movement
     */
    public function approveMovement($id)
    {
        $role = session('role');
        
        if ($role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can approve movements'
            ], 403);
        }

        try {
            // For now, just log the approval
            // In a real implementation, this would update the movement record
            
            Log::info('Movement approved', [
                'movement_id' => $id,
                'approved_by' => session('id'),
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Movement approved successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error approving movement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to approve movement'
            ], 500);
        }
    }

    /**
     * Filter movements
     */
    public function filterMovements(Request $request)
    {
        try {
            $movements = $this->generateMovementHistory();

            // Apply filters
            if ($request->filled('type')) {
                $movements = array_filter($movements, function($item) use ($request) {
                    return $item['type'] === $request->type;
                });
            }

            if ($request->filled('status')) {
                $movements = array_filter($movements, function($item) use ($request) {
                    return $item['status'] === $request->status;
                });
            }

            if ($request->filled('centre')) {
                $movements = array_filter($movements, function($item) use ($request) {
                    return strpos($item['from_location'], $request->centre) !== false || 
                           strpos($item['to_location'], $request->centre) !== false;
                });
            }

            if ($request->filled('date_range')) {
                $days = (int) $request->date_range;
                $cutoffDate = now()->subDays($days);
                $movements = array_filter($movements, function($item) use ($cutoffDate) {
                    return strtotime($item['date']) >= $cutoffDate->timestamp;
                });
            }

            return response()->json([
                'success' => true,
                'movements' => array_values($movements)
            ]);

        } catch (Exception $e) {
            Log::error('Error filtering movements: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to filter movements'
            ], 500);
        }
    }

    /**
     * Generate mock movement history
     */
    private function generateMovementHistory(): array
    {
        $assets = Asset::limit(15)->get();
        $movements = [];

        $movementTypes = ['transfer', 'relocation', 'assignment', 'return'];
        $statuses = ['completed', 'pending', 'in_transit'];
        $locations = [
            'Main Training Centre - Room A',
            'Main Training Centre - Room B',
            'East Branch - Storage',
            'East Branch - Classroom 1',
            'South Campus - Lab',
            'South Campus - Office',
            'North Extension - Workshop',
            'Storage Facility A',
            'Maintenance Workshop'
        ];

        foreach ($assets as $index => $asset) {
            $numMovements = rand(1, 3);
            
            for ($i = 0; $i < $numMovements; $i++) {
                $type = $movementTypes[array_rand($movementTypes)];
                $status = $statuses[array_rand($statuses)];
                $fromLocation = $locations[array_rand($locations)];
                $toLocation = $locations[array_rand($locations)];
                
                // Ensure from and to locations are different
                while ($toLocation === $fromLocation) {
                    $toLocation = $locations[array_rand($locations)];
                }

                $movementDate = now()->subDays(rand(1, 90));

                $movements[] = [
                    'id' => 'MOV-' . str_pad(($index * $numMovements + $i + 1), 4, '0', STR_PAD_LEFT),
                    'asset_id' => $asset->id,
                    'asset_name' => $asset->asset_name,
                    'asset_image' => asset('images/default-asset.png'),
                    'asset_type' => $asset->asset_type,
                    'type' => $type,
                    'status' => $status,
                    'from_location' => $fromLocation,
                    'to_location' => $toLocation,
                    'date' => $movementDate->format('M j, Y'),
                    'performed_by' => $this->getRandomUser(),
                    'notes' => $this->getMovementNotes($type)
                ];
            }
        }

        // Sort by date (newest first)
        usort($movements, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $movements;
    }

    /**
     * Get movement notes
     */
    private function getMovementNotes($type): string
    {
        $notes = [
            'transfer' => 'Asset transferred between centres for operational needs',
            'relocation' => 'Relocated to optimize space utilization',
            'assignment' => 'Assigned to staff member for project use',
            'return' => 'Returned after project completion'
        ];

        return $notes[$type] ?? 'Asset movement recorded';
    }

    /**
     * Get random user
     */
    private function getRandomUser(): string
    {
        $users = [
            'Ahmad Rahman',
            'Siti Nurhaliza', 
            'Muhammad Aziz',
            'Fatimah Abdullah',
            'Kamal Hassan',
            'Rozita Ibrahim'
        ];

        return $users[array_rand($users)];
    }

    /**
     * Get asset types for dropdowns
     */
    private function getAssetTypes(): array
    {
        // Get asset types from the existing assets
        $existingTypes = Asset::distinct('asset_type')
                             ->whereNotNull('asset_type')
                             ->pluck('asset_type')
                             ->sort()
                             ->toArray();

        // Default asset types
        $defaultTypes = [
            'Computer Equipment',
            'Furniture',
            'Medical Equipment',
            'Sports Equipment',
            'Office Supplies',
            'Vehicles',
            'Tools',
            'Electronics',
            'Kitchen Equipment',
            'Safety Equipment'
        ];

        // Merge and remove duplicates
        $allTypes = array_unique(array_merge($existingTypes, $defaultTypes));
        sort($allTypes);

        return $allTypes;
    }
}