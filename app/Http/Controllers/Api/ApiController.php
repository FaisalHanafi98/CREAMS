<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Activity;
use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
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

        $centreId = session('centre_id');
        if (!in_array($role, ['admin', 'supervisor', 'teacher', 'ajk'], true)
            || ($role !== 'admin' && blank($centreId))) {
            return response()->json([
                'success' => false,
                'message' => 'A valid role and centre assignment are required',
            ], 403);
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
                        'pending_messages' => ContactMessages::new()->count()
                    ];
                    break;

                case 'teacher':
                    $assignedActivityIds = Activity::whereHas('sessions', function ($query) use ($userId) {
                        $query->where('instructor_id', $userId);
                    })->pluck('id');
                    $data['stats'] = [
                        'my_activities' => $assignedActivityIds->count(),
                        'my_sessions_today' => ActivitySession::whereIn('activity_id', $assignedActivityIds)
                            ->where('instructor_id', $userId)
                            ->whereDate('session_date', today())
                            ->where('session_status', '!=', 'cancelled')
                            ->count(),
                        'assigned_trainees' => ActivityEnrollment::whereIn('activity_id', $assignedActivityIds)
                            ->where('enrollment_status', 'enrolled')
                            ->whereHas('trainee', fn ($query) => $query->where('centre_id', $centreId))
                            ->distinct()->count('trainee_id'),
                    ];
                    break;

                default:
                    $data['stats'] = [
                        'activities_count' => Activity::where('is_active', true)->count(),
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
