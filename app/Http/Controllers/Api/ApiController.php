<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Activity;
use App\Models\Trainee;
use App\Models\ContactMessages;
use App\Models\Volunteer;

class ApiController extends Controller
{
    /**
     * Get system statistics
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'users' => [
                    'total' => User::count(),
                    'by_role' => User::selectRaw('role, COUNT(*) as count')
                        ->groupBy('role')
                        ->pluck('count', 'role')
                ],
                'activities' => [
                    'total' => Activity::count(),
                    'active' => Activity::whereIn('activity_status', ['scheduled', 'ongoing'])->count()
                ],
                'trainees' => [
                    'total' => Trainee::count(),
                    'by_centre' => Trainee::selectRaw('centre_name, COUNT(*) as count')
                        ->groupBy('centre_name')
                        ->pluck('count', 'centre_name')
                ],
                'contact_messages' => [
                    'total' => ContactMessage::count(),
                    'pending' => ContactMessage::where('message_status', 'new')->count()
                ],
                'volunteers' => [
                    'total' => Volunteer::count(),
                    'pending' => Volunteer::where('volunteer_status', 'pending')->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search across multiple models
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ], 400);
        }

        try {
            $results = [];

            if ($type === 'all' || $type === 'users') {
                $results['users'] = User::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('iium_id', 'LIKE', "%{$query}%")
                    ->select('id', 'name', 'email', 'role', 'iium_id')
                    ->limit(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'activities') {
                $results['activities'] = Activity::where('activity_name', 'LIKE', "%{$query}%")
                    ->orWhere('activity_id', 'LIKE', "%{$query}%")
                    ->orWhere('activity_type', 'LIKE', "%{$query}%")
                    ->select('id', 'activity_name', 'activity_id', 'activity_type', 'activity_status')
                    ->limit(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'trainees') {
                $results['trainees'] = Trainee::where('trainee_first_name', 'LIKE', "%{$query}%")
                    ->orWhere('trainee_last_name', 'LIKE', "%{$query}%")
                    ->orWhere('trainee_email', 'LIKE', "%{$query}%")
                    ->select('id', 'trainee_first_name', 'trainee_last_name', 'trainee_email', 'centre_name')
                    ->limit(10)
                    ->get();
            }

            return response()->json([
                'success' => true,
                'query' => $query,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Health check endpoint
     */
    public function healthCheck(): JsonResponse
    {
        try {
            // Check database connection
            \DB::connection()->getPdo();
            
            // Check critical tables exist
            $tables = ['staffs', 'activities', 'trainees', 'centres'];
            $missingTables = [];
            
            foreach ($tables as $table) {
                if (!\Schema::hasTable($table)) {
                    $missingTables[] = $table;
                }
            }

            $health = [
                'status' => empty($missingTables) ? 'healthy' : 'degraded',
                'timestamp' => now()->toISOString(),
                'database' => 'connected',
                'missing_tables' => $missingTables,
                'environment' => config('app.env'),
                'debug' => config('app.debug'),
                'version' => '1.0.0'
            ];

            return response()->json($health);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'timestamp' => now()->toISOString(),
                'database' => 'disconnected',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user dashboard data
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        $role = session('role');
        $userId = session('id');
        
        if (!$role || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        try {
            $data = [
                'user' => [
                    'id' => $userId,
                    'role' => $role,
                    'name' => session('name')
                ],
                'stats' => []
            ];

            switch ($role) {
                case 'admin':
                    $data['stats'] = [
                        'total_users' => User::count(),
                        'total_activities' => Activity::count(),
                        'total_trainees' => Trainee::count(),
                        'pending_messages' => ContactMessage::where('message_status', 'new')->count()
                    ];
                    break;

                case 'teacher':
                    $data['stats'] = [
                        'my_activities' => Activity::whereHas('sessions', function($q) use ($userId) {
                            $q->where('teacher_id', $userId);
                        })->count(),
                        'my_sessions_today' => 0, // This would need activity_occurrences with proper date filtering
                        'assigned_trainees' => 0 // This would need proper relationship
                    ];
                    break;

                default:
                    $data['stats'] = [
                        'activities_count' => Activity::whereIn('activity_status', ['scheduled', 'ongoing'])->count(),
                        'trainees_count' => Trainee::count()
                    ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}