<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Auth\ForgotPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Dashboard API endpoints
Route::middleware(['web', 'auth'])->group(function () {
    // Global search endpoint
    Route::get('/search', function (Request $request) {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }
        
        $results = [];
        
        // Search trainees (with centre isolation)
        $centreId = session('centre_id');
        $role = session('role');
        
        try {
            $traineesQuery = DB::table('trainees')
                ->select('id', 'trainee_name as name', 'trainee_ic as identifier')
                ->where('trainee_name', 'like', "%{$query}%")
                ->limit(5);
                
            if ($role !== 'admin' && $centreId) {
                $traineesQuery->where('centre_id', $centreId);
            }
            
            $trainees = $traineesQuery->get();
            foreach ($trainees as $trainee) {
                $results[] = [
                    'type' => 'trainee',
                    'name' => $trainee->name,
                    'identifier' => $trainee->identifier,
                    'id' => $trainee->id,
                    'url' => "/trainees/{$trainee->id}"
                ];
            }
            
            // Search activities
            $activitiesQuery = DB::table('activities')
                ->select('id', 'activity_name as name', 'category')
                ->where('activity_name', 'like', "%{$query}%")
                ->limit(5);
                
            if ($role !== 'admin' && $centreId) {
                $activitiesQuery->where('centre_id', $centreId);
            }
            
            $activities = $activitiesQuery->get();
            foreach ($activities as $activity) {
                $results[] = [
                    'type' => 'activity',
                    'name' => $activity->name,
                    'identifier' => $activity->category,
                    'id' => $activity->id,
                    'url' => "/activities/{$activity->id}"
                ];
            }
            
            // Search users (admin/supervisor only)
            if (in_array($role, ['admin', 'supervisor'])) {
                $usersQuery = DB::table('users')
                    ->select('id', 'name', 'role')
                    ->where('name', 'like', "%{$query}%")
                    ->limit(3);
                    
                if ($role === 'supervisor' && $centreId) {
                    $usersQuery->where('centre_id', $centreId);
                }
                
                $users = $usersQuery->get();
                foreach ($users as $user) {
                    $results[] = [
                        'type' => 'user',
                        'name' => $user->name,
                        'identifier' => ucfirst($user->role),
                        'id' => $user->id,
                        'url' => "/users/{$user->id}"
                    ];
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Search API error', ['error' => $e->getMessage()]);
        }
        
        return response()->json(['results' => $results]);
    });
    
    // Notifications check endpoint
    Route::get('/notifications/check', function (Request $request) {
        $userId = session('id');
        
        try {
            $count = DB::table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->count();
                
            $latest = null;
            if ($count > 0) {
                $latest = DB::table('notifications')
                    ->where('user_id', $userId)
                    ->where('is_read', false)
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            return response()->json([
                'hasNew' => $count > 0,
                'count' => $count,
                'latest' => $latest ? [
                    'message' => $latest->notification_message ?? $latest->message,
                    'type' => $latest->notification_type ?? $latest->type ?? 'info'
                ] : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'hasNew' => false,
                'count' => 0,
                'latest' => null
            ]);
        }
    });
});

Route::post('/forgot-password', [ForgotPasswordController::class, 'forgot'])->name('auth.forgotpassword');

// API Controller for system endpoints
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\InstructorCompatibilityController;

// Public API endpoints
Route::get('/health', [ApiController::class, 'healthCheck']);
Route::get('/stats', [ApiController::class, 'getStats']);
Route::get('/search', [ApiController::class, 'search']);

// Protected API endpoints (require session authentication)
Route::middleware(['web'])->group(function () {
    Route::get('/dashboard-data', [ApiController::class, 'getDashboardData']);
    Route::get('/instructors/{instructorId}/compatibility', [InstructorCompatibilityController::class, 'checkCompatibility']);
});
