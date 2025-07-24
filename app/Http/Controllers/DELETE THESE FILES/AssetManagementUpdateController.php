<?php

namespace App\Http\Controllers;

use App\Models\Assets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AssetManagementUpdateController extends Controller
{
    public function index($asset_id)
    {
        // Retrieve the asset with the given ID from the database
        $asset = Assets::find($asset_id);

        // Pass the asset data to the view
        return view('assetmanagementupdate', ['asset' => $asset]);
    }

    public function update(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'asset_name' => 'required',
            'asset_type' => 'required',
            'asset_quantity' => 'required|numeric',
            'asset_price' => 'required|numeric',
            'centre_name' => 'required',
            'asset_brand' => 'required',
            'asset_note' => 'nullable',
            'asset_avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the asset ID from the request
        $assetId = $request->input('asset_id');

        // Retrieve the asset with the given ID from the database
        $asset = Assets::find($assetId);

        // Update the asset fields with the validated data
        $asset->asset_name = $validatedData['asset_name'];
        $asset->asset_type = $validatedData['asset_type'];
        $asset->asset_quantity = $validatedData['asset_quantity'];
        $asset->asset_price = $validatedData['asset_price'];
        $asset->centre_name = $validatedData['centre_name'];
        $asset->asset_brand = $validatedData['asset_brand'];
        $asset->asset_note = $validatedData['asset_note'];

        // Handle the asset avatar upload
        if ($request->hasFile('asset_avatar')) {
            $avatar = $request->file('asset_avatar');
            $avatarPath = $avatar->store('asset_avatar', 'public');
            $asset->asset_avatar = asset('storage/' . $avatarPath);
            $asset->save();
        }


        // Save the updated asset
        $asset->save();

        // Redirect back to the asset inventory page with a success message
        return redirect()->route('assetupdatepage', ['asset_id' => $assetId])->with('success', 'Asset updated successfully');
    }
}

