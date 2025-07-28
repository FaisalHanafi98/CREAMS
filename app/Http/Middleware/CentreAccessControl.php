<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Activity;
use App\Models\Trainee;
use App\Models\Asset;
use App\Models\Centre;

class CentreAccessControl
{
    /**
     * Handle an incoming request to enforce centre-based access control.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $resource  The resource type being accessed (activity, trainee, asset, etc.)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $resource = null)
    {
        $userRole = session('role');
        $userId = session('id');
        $userCentreId = session('centre_id');

        // Log access attempt for audit
        Log::info('Centre access control check', [
            'user_id' => $userId,
            'user_role' => $userRole,
            'user_centre_id' => $userCentreId,
            'resource' => $resource,
            'url' => $request->fullUrl(),
            'route_parameters' => $request->route()->parameters()
        ]);

        // Admin users have access to all centres
        if ($userRole === 'admin') {
            Log::info('Admin access granted - bypassing centre restrictions');
            return $next($request);
        }

        // Ensure user has a centre assigned
        if (!$userCentreId) {
            Log::warning('User without centre assignment attempting access', [
                'user_id' => $userId,
                'url' => $request->fullUrl()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Your account is not assigned to a centre. Please contact the administrator.');
        }

        // Check resource-specific access if resource type specified
        if ($resource) {
            $accessGranted = $this->checkResourceAccess($request, $resource, $userCentreId, $userRole);
            
            if (!$accessGranted) {
                Log::warning('Centre access denied', [
                    'user_id' => $userId,
                    'user_centre_id' => $userCentreId,
                    'resource' => $resource,
                    'url' => $request->fullUrl()
                ]);
                
                return redirect()->route('dashboard')
                    ->with('error', 'You do not have permission to access this resource from another centre.');
            }
        }

        return $next($request);
    }

    /**
     * Check if user can access specific resource based on centre
     */
    private function checkResourceAccess($request, $resource, $userCentreId, $userRole)
    {
        switch ($resource) {
            case 'activity':
                return $this->checkActivityAccess($request, $userCentreId, $userRole);
                
            case 'trainee':
                return $this->checkTraineeAccess($request, $userCentreId, $userRole);
                
            case 'asset':
                return $this->checkAssetAccess($request, $userCentreId, $userRole);
                
            case 'centre':
                return $this->checkCentreAccess($request, $userCentreId, $userRole);
                
            default:
                // If resource type not specified, allow access (basic centre restriction applies)
                return true;
        }
    }

    /**
     * Check activity access based on centre
     */
    private function checkActivityAccess($request, $userCentreId, $userRole)
    {
        $activityId = $request->route('id') ?? $request->route('activity');
        
        if (!$activityId) {
            return true; // No specific activity ID, allow general access
        }

        try {
            $activity = Activity::find($activityId);
            
            if (!$activity) {
                return false; // Activity not found
            }

            // Check if activity belongs to user's centre
            $hasAccess = $activity->centre_id === $userCentreId;
            
            Log::info('Activity access check', [
                'activity_id' => $activityId,
                'activity_centre_id' => $activity->centre_id,
                'user_centre_id' => $userCentreId,
                'access_granted' => $hasAccess
            ]);
            
            return $hasAccess;
            
        } catch (\Exception $e) {
            Log::error('Error checking activity access', [
                'activity_id' => $activityId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check trainee access based on centre
     */
    private function checkTraineeAccess($request, $userCentreId, $userRole)
    {
        $traineeId = $request->route('id') ?? $request->route('trainee');
        
        if (!$traineeId) {
            return true; // No specific trainee ID, allow general access
        }

        try {
            $trainee = Trainee::find($traineeId);
            
            if (!$trainee) {
                return false; // Trainee not found
            }

            // Check if trainee belongs to user's centre
            // Handle both centre_name and centre_id relationships
            $traineeCentre = $this->getTraineeCentreId($trainee);
            $hasAccess = $traineeCentre === $userCentreId;
            
            Log::info('Trainee access check', [
                'trainee_id' => $traineeId,
                'trainee_centre' => $traineeCentre,
                'user_centre_id' => $userCentreId,
                'access_granted' => $hasAccess
            ]);
            
            return $hasAccess;
            
        } catch (\Exception $e) {
            Log::error('Error checking trainee access', [
                'trainee_id' => $traineeId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check asset access based on centre
     */
    private function checkAssetAccess($request, $userCentreId, $userRole)
    {
        $assetId = $request->route('id') ?? $request->route('asset');
        
        if (!$assetId) {
            return true; // No specific asset ID, allow general access
        }

        try {
            $asset = Asset::find($assetId);
            
            if (!$asset) {
                return false; // Asset not found
            }

            // Check if asset belongs to user's centre
            // Handle both centre_name and centre_id relationships
            $assetCentre = $this->getAssetCentreId($asset);
            $hasAccess = $assetCentre === $userCentreId;
            
            Log::info('Asset access check', [
                'asset_id' => $assetId,
                'asset_centre' => $assetCentre,
                'user_centre_id' => $userCentreId,
                'access_granted' => $hasAccess
            ]);
            
            return $hasAccess;
            
        } catch (\Exception $e) {
            Log::error('Error checking asset access', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check centre access - supervisors can only access their own centre
     */
    private function checkCentreAccess($request, $userCentreId, $userRole)
    {
        $centreId = $request->route('id') ?? $request->route('centre');
        
        if (!$centreId) {
            return true; // No specific centre ID, allow general access
        }

        // Supervisor and teachers can only access their own centre
        if (in_array($userRole, ['supervisor', 'teacher', 'ajk'])) {
            $hasAccess = $centreId === $userCentreId;
            
            Log::info('Centre access check', [
                'requested_centre_id' => $centreId,
                'user_centre_id' => $userCentreId,
                'user_role' => $userRole,
                'access_granted' => $hasAccess
            ]);
            
            return $hasAccess;
        }

        return true; // Admin access already handled above
    }

    /**
     * Get trainee's centre ID, handling both centre_name and centre_id relationships
     */
    private function getTraineeCentreId($trainee)
    {
        // If trainee has centre_id, use it directly
        if (isset($trainee->centre_id) && $trainee->centre_id) {
            return $trainee->centre_id;
        }

        // If trainee has centre_name, convert to centre_id
        if (isset($trainee->centre_name) && $trainee->centre_name) {
            $centre = Centre::where('centre_name', $trainee->centre_name)->first();
            return $centre ? $centre->centre_id : null;
        }

        return null;
    }

    /**
     * Get asset's centre ID, handling both centre_name and centre_id relationships
     */
    private function getAssetCentreId($asset)
    {
        // If asset has centre_id, use it directly
        if (isset($asset->centre_id) && $asset->centre_id) {
            return $asset->centre_id;
        }

        // If asset has centre_name, convert to centre_id
        if (isset($asset->centre_name) && $asset->centre_name) {
            $centre = Centre::where('centre_name', $asset->centre_name)->first();
            return $centre ? $centre->centre_id : null;
        }

        return null;
    }
}