<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionDebugController extends Controller
{
    /**
     * Display detailed session information for debugging
     */
    public function debug()
    {
        $sessionInfo = [
            'session_id' => session()->getId(),
            'has_user_id' => session()->has('id'),
            'user_id' => session('id'),
            'role' => session('role'),
            'all_session_data' => session()->all(),
            'cookies' => request()->cookies->all(),
            'driver' => config('session.driver'),
            'lifetime' => config('session.lifetime')
        ];
        
        Log::info('Session debug requested', $sessionInfo);
        
        return response()->json($sessionInfo);
    }
    
    /**
     * Test setting a session value
     */
    public function setTest()
    {
        session(['test_key' => 'test_value_' . time()]);
        session()->save();
        
        return response()->json([
            'message' => 'Test session value set',
            'test_key' => session('test_key'),
            'session_id' => session()->getId()
        ]);
    }
}