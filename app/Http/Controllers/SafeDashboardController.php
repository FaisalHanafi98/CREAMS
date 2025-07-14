<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Exception;

class SafeDashboardController extends Controller
{
    /**
     * Completely safe dashboard with only basic queries
     */
    public function index(Request $request)
    {
        try {
            // Validate session
            if (!session('id') || !session('role')) {
                return redirect()->route('login')
                    ->with('error', 'Please log in to access the dashboard.');
            }

            $role = session('role');
            $userId = session('id');

            // Get ONLY safe statistics - counts only
            $stats = $this->getSafeStats();
            
            // Log successful access
            Log::info('Safe dashboard accessed', [
                'user_id' => $userId,
                'role' => $role,
                'stats' => $stats
            ]);

            // Extract individual variables for view compatibility
            $totalUsers = $stats['total_users'] ?? 0;
            $totalTrainees = $stats['total_trainees'] ?? 0;
            $totalActivities = $stats['total_activities'] ?? 0;
            $activeSessions = $stats['active_sessions'] ?? 0;
            $totalCentres = $stats['total_centres'] ?? 0;
            $totalAssets = $stats['total_assets'] ?? 0;

            // Role-specific defaults
            $mySessions = 0;
            $myTrainees = 0;
            $upcomingClasses = 0;
            $completedSessions = 0;

            // Empty arrays for complex data to prevent errors
            $charts = [];
            $recentActivities = [];
            $upcomingEvents = [];
            $notifications = [];
            $quickActions = [];
            $systemHealth = ['overall' => 'healthy'];

            // Create data array for compatibility
            $data = [
                'stats' => $stats,
                'charts' => $charts,
                'recent_activities' => $recentActivities,
                'upcoming_events' => $upcomingEvents,
                'notifications' => $notifications,
                'quick_actions' => $quickActions,
                'system_health' => $systemHealth
            ];

            return view('dashboard.index', compact(
                'data', 'role', 'stats', 'charts', 'recentActivities', 'upcomingEvents', 
                'notifications', 'quickActions', 'systemHealth',
                'totalUsers', 'totalTrainees', 'totalActivities', 'activeSessions',
                'mySessions', 'myTrainees', 'upcomingClasses', 'completedSessions',
                'totalCentres', 'totalAssets'
            ));

        } catch (Exception $e) {
            Log::error('Safe dashboard error', [
                'user_id' => session('id') ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return redirect()->route('login')
                ->with('error', 'Dashboard temporarily unavailable. Please try again.');
        }
    }

    /**
     * Get ONLY safe statistics - basic counts with extensive error handling
     */
    private function getSafeStats(): array
    {
        $stats = [
            'total_users' => 0,
            'total_trainees' => 0,
            'total_activities' => 0,
            'active_sessions' => 0,
            'total_centres' => 0,
            'total_assets' => 0
        ];

        // Count users
        try {
            if (Schema::hasTable('users')) {
                $stats['total_users'] = DB::table('users')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting users', ['error' => $e->getMessage()]);
        }

        // Count trainees
        try {
            if (Schema::hasTable('trainees')) {
                $stats['total_trainees'] = DB::table('trainees')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting trainees', ['error' => $e->getMessage()]);
        }

        // Count activities
        try {
            if (Schema::hasTable('activities')) {
                $stats['total_activities'] = DB::table('activities')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting activities', ['error' => $e->getMessage()]);
        }

        // Count centres
        try {
            if (Schema::hasTable('centres')) {
                $stats['total_centres'] = DB::table('centres')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting centres', ['error' => $e->getMessage()]);
        }

        // Count assets (NO VALUE CALCULATIONS)
        try {
            if (Schema::hasTable('assets')) {
                $stats['total_assets'] = DB::table('assets')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting assets', ['error' => $e->getMessage()]);
        }

        // Count sessions
        try {
            if (Schema::hasTable('activity_sessions')) {
                $stats['active_sessions'] = DB::table('activity_sessions')->count();
            }
        } catch (Exception $e) {
            Log::error('Error counting sessions', ['error' => $e->getMessage()]);
        }

        return $stats;
    }

    /**
     * API endpoint for safe stats
     */
    public function getStats(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $stats = $this->getSafeStats();

            return response()->json([
                'stats' => $stats,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting safe stats API', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load statistics'], 500);
        }
    }
}