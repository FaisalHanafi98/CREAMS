<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;

class SessionEnhancer
{
    /**
     * Handle an incoming request and enhance session data if needed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Only enhance session for authenticated users
        if (session('id') && session('role')) {
            $this->enhanceUserSession();
        }

        return $next($request);
    }

    /**
     * Enhance user session with missing data.
     */
    private function enhanceUserSession()
    {
        try {
            $userId = session('id');
            
            // Check if avatar data is missing
            if (!session('avatar') && !session('user_avatar')) {
                $user = Users::find($userId);
                
                if ($user) {
                    Log::info('Enhancing session data for user', [
                        'user_id' => $userId,
                        'role' => session('role'),
                        'avatar_exists' => !empty($user->avatar)
                    ]);
                    
                    // Add missing session data
                    $sessionData = [];
                    
                    if (!session('avatar')) {
                        $sessionData['avatar'] = $user->avatar;
                    }
                    
                    if (!session('user_avatar')) {
                        $sessionData['user_avatar'] = $user->avatar;
                    }
                    
                    if (!session('phone')) {
                        $sessionData['phone'] = $user->phone;
                    }
                    
                    if (!session('address')) {
                        $sessionData['address'] = $user->address;
                    }
                    
                    if (!session('bio')) {
                        $sessionData['bio'] = $user->bio;
                    }
                    
                    if (!session('date_of_birth')) {
                        $sessionData['date_of_birth'] = $user->date_of_birth;
                    }
                    
                    if (!session('iium_id')) {
                        $sessionData['iium_id'] = $user->iium_id;
                    }
                    
                    if (!session('centre_id')) {
                        $sessionData['centre_id'] = $user->centre_id;
                    }
                    
                    // Update session with missing data
                    if (!empty($sessionData)) {
                        session($sessionData);
                        
                        Log::info('Session data enhanced', [
                            'user_id' => $userId,
                            'enhanced_fields' => array_keys($sessionData)
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error enhancing session data', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
        }
    }
}