<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;

class AssetManagementRegisterController extends Controller
{
    public function index()
    {
        $assets = Asset::all();
        return view('assetmanagementregister', compact('assets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required',
            'asset_name' => 'required',
            'asset_parent' => 'required',
            'asset_brand' => 'required',
            'asset_price' => 'required|numeric',
            'asset_quantity' => 'required|integer',
            'centre_name' => 'required',
            'asset_note' => 'nullable',
            'asset_avatar' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048', 'nullable'],
        ]);

        $asset = Asset::create([
            'asset_id' => $request->asset_id,
            'asset_name' => $request->asset_name,
            'asset_parent' => $request->asset_parent,
            'asset_brand' => $request->asset_brand,
            'asset_price' => $request->asset_price,
            'asset_quantity' => $request->asset_quantity,
            'centre_name' => $request->centre_name,
            'asset_note' => $request->asset_note,
        ]);

        // Handle avatar upload if a new avatar is provided
        if ($request->hasFile('asset_avatar')) {
            $avatar = $request->file('asset_avatar');
            $avatarPath = $avatar->store('asset_avatar', 'public');
            $asset->asset_avatar = asset('storage/' . $avatarPath);
            $asset->save();
        }

        return redirect()->route('assetregisterpage')->with('success', 'Asset created successfully');
    }
}
