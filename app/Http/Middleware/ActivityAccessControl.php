<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\ActivityEnrollments;

class ActivityAccessControl
{
    public function handle($request, Closure $next)
    {
        $user = session();
        
        // Check if user can access this trainee's data
        if ($request->route('trainee')) {
            $traineeId = $request->route('trainee');
            
            if (!$this->canAccessTrainee($user, $traineeId)) {
                return redirect()->route('dashboard')
                    ->with('error', 'Access denied.');
            }
        }
        
        return $next($request);
    }
    
    private function canAccessTrainee($user, $traineeId)
    {
        // Implementation based on role and centre access
        return true; // Implement your logic here
    }
}