<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetType;
use Illuminate\Support\Facades\Storage;

class AssetTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetType::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('category', 'like', '%' . $request->search . '%');
        }

        $assetTypes = $query->latest()->get();

        return view('asset-types.home', compact('assetTypes'));
    }

    public function create()
    {
        return view('asset-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'vendor' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            $validated['image_path'] = $request->file('image_path')->store('asset-images', 'public');
        }

        $assetType = AssetType::create($validated);

        return redirect()->route(session('role') . '.asset-types.edit', $assetType->id)
            ->with('success', 'Asset type created successfully.');
    }

    public function edit($id)
    {
        $assetType = AssetType::findOrFail($id);
        return view('asset-types.edit', compact('assetType'));
    }

    public function update(Request $request, $id)
    {
        $assetType = AssetType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'vendor' => 'nullable|string|max:255',
            'image_path' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_path')) {
            if ($assetType->image_path && Storage::disk('public')->exists($assetType->image_path)) {
                Storage::disk('public')->delete($assetType->image_path);
            }

            $validated['image_path'] = $request->file('image_path')->store('asset-images', 'public');
        }

        $assetType->update($validated);

        return redirect()->route(session('role') . '.asset-types.edit', $assetType->id)
            ->with('success', 'Asset type updated successfully.');
    }

    public function destroy($id)
    {
        $assetType = AssetType::findOrFail($id);

        if ($assetType->image_path && Storage::disk('public')->exists($assetType->image_path)) {
            Storage::disk('public')->delete($assetType->image_path);
        }

        $assetType->delete();

        return redirect()->route(session('role') . '.asset-types.index')->with('success', 'Asset type deleted successfully.');
    }
}
