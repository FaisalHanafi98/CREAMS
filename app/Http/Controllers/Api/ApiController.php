<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Users;
use App\Models\Activity;
use App\Models\Trainees;
use App\Models\ContactMessages;
use App\Models\Volunteers;

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
                    'total' => Users::count(),
                    'by_role' => Users::selectRaw('role, COUNT(*) as count')
                        ->groupBy('role')
                        ->pluck('count', 'role')
                ],
                'activities' => [
                    'total' => Activity::count(),
                    'active' => Activity::where('is_active', true)->count()
                ],
                'trainees' => [
                    'total' => Trainees::count(),
                    'by_centre' => Trainees::selectRaw('centre_name, COUNT(*) as count')
                        ->groupBy('centre_name')
                        ->pluck('count', 'centre_name')
                ],
                'contact_messages' => [
                    'total' => ContactMessages::count(),
                    'pending' => ContactMessages::where('status', 'new')->count()
                ],
                'volunteers' => [
                    'total' => Volunteers::count(),
                    'pending' => Volunteers::where('status', 'pending')->count()
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
                $results['users'] = Users::where('name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('iium_id', 'LIKE', "%{$query}%")
                    ->select('id', 'name', 'email', 'role', 'iium_id')
                    ->limit(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'activities') {
                $results['activities'] = Activity::where('activity_name', 'LIKE', "%{$query}%")
                    ->orWhere('activity_code', 'LIKE', "%{$query}%")
                    ->orWhere('category', 'LIKE', "%{$query}%")
                    ->select('id', 'activity_name', 'activity_code', 'category', 'is_active')
                    ->limit(10)
                    ->get();
            }

            if ($type === 'all' || $type === 'trainees') {
                $results['trainees'] = Trainees::where('trainee_first_name', 'LIKE', "%{$query}%")
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
            $tables = ['users', 'activities', 'trainees', 'centres'];
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
                        'total_users' => Users::count(),
                        'total_activities' => Activity::count(),
                        'total_trainees' => Trainees::count(),
                        'pending_messages' => ContactMessages::where('status', 'new')->count()
                    ];
                    break;

                case 'teacher':
                    $data['stats'] = [
                        'my_activities' => Activity::whereHas('sessions', function($q) use ($userId) {
                            $q->where('teacher_id', $userId);
                        })->count(),
                        'my_sessions_today' => 0, // This would need activity_sessions with proper date filtering
                        'assigned_trainees' => 0 // This would need proper relationship
                    ];
                    break;

                default:
                    $data['stats'] = [
                        'activities_count' => Activity::where('is_active', true)->count(),
                        'trainees_count' => Trainees::count()
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