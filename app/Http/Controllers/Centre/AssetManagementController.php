<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;

class AssetManagementController extends Controller
{
    public function index()
    {
        $assets = Asset::all();
        return view('assetmanagement', compact('assets'));
    }

}
