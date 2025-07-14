<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assets;

class AssetManagementController extends Controller
{
    public function index()
    {
        $assets = Assets::all();
        return view('assetmanagement', compact('assets'));
    }

}
