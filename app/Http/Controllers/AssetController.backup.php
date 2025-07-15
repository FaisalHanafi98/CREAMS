<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the assets.
     */
    public function index()
    {
        // Placeholder data for assets
        $assets = [
            [
                'id' => 1,
                'name' => 'Desktop Computer',
                'type' => 'Computer',
                'quantity' => 15,
                'centre_name' => 'Main Training Centre',
                'status' => 'available'
            ],
            [
                'id' => 2,
                'name' => 'Office Chairs',
                'type' => 'Furniture',
                'quantity' => 30,
                'centre_name' => 'East Branch',
                'status' => 'available'
            ],
            [
                'id' => 3,
                'name' => 'Projector',
                'type' => 'Equipment',
                'quantity' => 5,
                'centre_name' => 'South Campus',
                'status' => 'available'
            ],
            [
                'id' => 4,
                'name' => 'Training Books',
                'type' => 'Books',
                'quantity' => 50,
                'centre_name' => 'Main Training Centre',
                'status' => 'available'
            ],
            [
                'id' => 5,
                'name' => 'Van',
                'type' => 'Vehicle',
                'quantity' => 2,
                'centre_name' => 'North Extension',
                'status' => 'maintenance'
            ]
        ];
        
        return view('assets.index', compact('assets'));
    }
    
    /**
     * Display the specified asset.
     */
    public function show($role, $id)
    {
        // Placeholder data for a single asset
        $asset = [
            'id' => $id,
            'name' => 'Asset ' . $id,
            'type' => 'Equipment',
            'quantity' => 10,
            'centre_name' => 'Main Training Centre',
            'status' => 'available',
            'description' => 'This is a sample asset description.'
        ];
        
        return view('assets.show', compact('asset'));
    }
}