<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetItem;
use App\Models\AssetType;

class AssetItemController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetItem::with('assetType');

        if ($request->filled('search')) {
            $query->where('tag', 'like', '%' . $request->search . '%')
                ->orWhereHas('assetType', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('category', 'like', '%' . $request->search . '%');
                });
        }

        $assetItems = $query->latest()->get();

        return view('asset-items.index', compact('assetItems'));
    }

    public function create(Request $request)
    {
        $assetTypes = AssetType::all();
        $selectedAssetType = null;

        if ($request->has('asset_type')) {
            $selectedAssetType = AssetType::find($request->asset_type);
        }

        return view('asset-items.create', compact('assetTypes', 'selectedAssetType'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'tag' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
        ]);

        $assetItem = AssetItem::create($validated);

        // Redirect back to the asset type's edit page
        return redirect()
            ->route(session('role') . '.asset-types.edit', $validated['asset_type_id'])
            ->with('success', 'Asset item added successfully.');
    }

    public function edit($id)
    {
        $assetItem = AssetItem::findOrFail($id);
        $assetTypes = AssetType::all();

        return view('asset-items.edit', compact('assetItem', 'assetTypes'));
    }

    public function update(Request $request, AssetItem $assetItem)
    {
        $validated = $request->validate([
            'asset_type_id' => 'required|exists:asset_types,id',
            'tag' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
        ]);

        $assetItem->update($validated);

        return redirect()->back()->with('success', 'Asset item updated successfully.');
    }

    public function destroy(AssetItem $assetItem)
    {
        $assetTypeId = $assetItem->asset_type_id;
        $assetItem->delete();

        return redirect()
            ->route(session('role') . '.asset-types.edit', $assetTypeId)
            ->with('success', 'Asset item deleted successfully.');
    }

    // Optional: Form specifically for a given asset type
    public function createForType(AssetType $assetType)
    {
        return view('asset-items.create', [
            'assetTypes' => AssetType::all(),
            'selectedAssetType' => $assetType
        ]);
    }
}
