<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CentreController extends Controller
{
    /**
     * Display a listing of the centres.
     */
    public function index()
    {
        // Placeholder data for centres
        $centres = $this->getPlaceholderCentres();
        
        return view('centres.index', compact('centres'));
    }
    
    /**
     * Display the specified centre.
     */
    public function show($role, $id)
    {
        // Find centre by id from placeholder data
        $centre = $this->getPlaceholderCentre($id);
        
        return view('centres.show', compact('centre'));
    }
    
    /**
     * Display assets for a specific centre.
     */
    public function assets($role, $id)
    {
        // Find centre by id from placeholder data
        $centre = $this->getPlaceholderCentre($id);
        
        // Get assets for this centre (placeholder data)
        $assets = $this->getPlaceholderCentreAssets($id);
        
        return view('centres.assets', compact('centre', 'assets'));
    }
    
    /**
     * Get placeholder data for all centres
     */
    private function getPlaceholderCentres()
    {
        return [
            [
                'id' => 1,
                'name' => 'Main Training Centre',
                'location' => 'City Centre',
                'capacity' => 120,
                'staff_count' => 12,
                'trainee_count' => 78,
                'asset_count' => 45,
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'East Branch',
                'location' => 'East District',
                'capacity' => 80,
                'staff_count' => 8,
                'trainee_count' => 54,
                'asset_count' => 32,
                'status' => 'active'
            ],
            [
                'id' => 3,
                'name' => 'South Campus',
                'location' => 'South District',
                'capacity' => 70,
                'staff_count' => 6,
                'trainee_count' => 42,
                'asset_count' => 28,
                'status' => 'active'
            ],
            [
                'id' => 4,
                'name' => 'North Extension',
                'location' => 'North District',
                'capacity' => 60,
                'staff_count' => 5,
                'trainee_count' => 36,
                'asset_count' => 22,
                'status' => 'active'
            ]
        ];
    }
    
    /**
     * Get placeholder data for a specific centre
     */
    private function getPlaceholderCentre($id)
    {
        $centres = $this->getPlaceholderCentres();
        
        // Find centre with matching ID or return the first one as fallback
        foreach ($centres as $centre) {
            if ($centre['id'] == $id) {
                return $centre;
            }
        }
        
        return $centres[0];
    }
    
    /**
     * Get placeholder assets for a specific centre
     */
    private function getPlaceholderCentreAssets($centreId)
    {
        $assets = [];
        $types = ['Computer', 'Furniture', 'Equipment', 'Vehicle', 'Books'];
        $descriptions = [
            'Used for training purposes',
            'For administrative use',
            'For trainee practice sessions',
            'General use',
            'For specific workshops'
        ];
        
        // Generate different numbers of assets based on centre ID
        $count = 5 + ($centreId * 2);
        
        for ($i = 0; $i < $count; $i++) {
            $assets[] = [
                'id' => $i + 1,
                'name' => $types[array_rand($types)] . ' ' . chr(65 + $i),
                'type' => $types[array_rand($types)],
                'description' => $descriptions[array_rand($descriptions)],
                'quantity' => rand(1, 20),
                'status' => rand(0, 10) > 2 ? 'available' : 'maintenance'
            ];
        }
        
        return $assets;
    }
}