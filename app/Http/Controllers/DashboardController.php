<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardServiceFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardController extends Controller
{
    private DashboardServiceFactory $dashboardServiceFactory;

    public function __construct(DashboardServiceFactory $dashboardServiceFactory)
    {
        $this->dashboardServiceFactory = $dashboardServiceFactory;
    }

    /**
     * Display the main dashboard based on user role
     */
    public function index(Request $request)
    {
        try {
            // Validate session
            if (!session('id') || !session('role')) {
                Log::warning('Invalid session in dashboard access', [
                    'session_id' => session()->getId(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Please log in to access the dashboard.');
            }

            $role = session('role');
            $userId = session('id');

            // Log dashboard access
            Log::info('Dashboard accessed', [
                'user_id' => $userId,
                'role' => $role,
                'timestamp' => now(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Validate role is supported
            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                Log::error('Unsupported role attempting dashboard access', [
                    'role' => $role,
                    'user_id' => $userId
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Your role does not have dashboard access.');
            }

            // Get role-specific dashboard service
            $dashboardService = $this->dashboardServiceFactory->make($role);
            
            // Get dashboard data
            $data = $dashboardService->getDashboardData($userId);
            
            // Add role and user info to data
            $data['role'] = $role;
            $data['user_id'] = $userId;
            $data['user_name'] = session('name');
            
            // Extract all data sections for the view (for compatibility with existing view)
            $stats = $data['stats'] ?? [];
            $charts = $data['charts'] ?? [];
            $recentActivities = $data['recent_activities'] ?? [];
            $upcomingEvents = $data['upcoming_events'] ?? [];
            $notifications = $data['notifications'] ?? [];
            $quickActions = $data['quick_actions'] ?? [];
            $systemHealth = $data['system_health'] ?? [];
            
            // Extract individual variables that the view partials expect
            $totalUsers = $stats['total_users'] ?? 0;
            $totalTrainees = $stats['total_trainees'] ?? 0;
            $totalActivities = $stats['total_activities'] ?? 0;
            $activeSessions = $stats['active_sessions'] ?? 0;
            
            // Role-specific variables
            $mySessions = $stats['my_sessions'] ?? 0;
            $myTrainees = $stats['my_trainees'] ?? 0;
            $upcomingClasses = $stats['upcoming_classes'] ?? 0;
            $completedSessions = $stats['completed_sessions'] ?? 0;
            
            // Return unified dashboard view
            return view('dashboard.index', compact(
                'data', 'role', 'stats', 'charts', 'recentActivities', 'upcomingEvents', 
                'notifications', 'quickActions', 'systemHealth',
                'totalUsers', 'totalTrainees', 'totalActivities', 'activeSessions',
                'mySessions', 'myTrainees', 'upcomingClasses', 'completedSessions'
            ));

        } catch (Exception $e) {
            Log::critical('Critical error in dashboard', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);

            return redirect()->route('login')
                ->with('error', 'An error occurred loading the dashboard. Please try again.');
        }
    }

    /**
     * Get dashboard statistics via API (for AJAX requests)
     */
    public function getStats(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            $data = $dashboardService->getDashboardData($userId);

            return response()->json([
                'stats' => $data['stats'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting dashboard stats', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load statistics'], 500);
        }
    }

    /**
     * Get dashboard charts via API (for AJAX requests)
     */
    public function getCharts(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            $data = $dashboardService->getDashboardData($userId);

            return response()->json([
                'charts' => $data['charts'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting dashboard charts', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load charts'], 500);
        }
    }

    /**
     * Get dashboard notifications via API (for AJAX requests)
     */
    public function getNotifications(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            $data = $dashboardService->getDashboardData($userId);

            return response()->json([
                'notifications' => $data['notifications'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting dashboard notifications', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to load notifications'], 500);
        }
    }

    /**
     * Clear dashboard cache for current user
     */
    public function clearCache(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            $dashboardService->clearUserCache($userId, $role);

            Log::info('Dashboard cache cleared', [
                'user_id' => $userId,
                'role' => $role
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully',
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error clearing dashboard cache', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to clear cache'], 500);
        }
    }

    /**
     * Refresh dashboard data (clears cache and returns fresh data)
     */
    public function refresh(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            
            // Clear cache first
            $dashboardService->clearUserCache($userId, $role);
            
            // Get fresh data
            $data = $dashboardService->getDashboardData($userId);

            Log::info('Dashboard data refreshed', [
                'user_id' => $userId,
                'role' => $role
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error refreshing dashboard data', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to refresh data'], 500);
        }
    }

    /**
     * Get dashboard health status
     */
    public function health(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $role = session('role');
            $userId = session('id');

            if (!$this->dashboardServiceFactory->isRoleSupported($role)) {
                return response()->json(['error' => 'Unsupported role'], 403);
            }

            $dashboardService = $this->dashboardServiceFactory->make($role);
            $health = $dashboardService->getSystemHealth();

            return response()->json([
                'health' => $health,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error getting dashboard health', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'health' => [
                    'overall' => 'unhealthy',
                    'error' => 'Failed to check system health'
                ],
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Save dashboard customization settings
     */
    public function saveCustomization(Request $request)
    {
        try {
            if (!session('id') || !session('role')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $userId = session('id');
            $role = session('role');

            // Validate customization data
            $customizationData = $request->validate([
                'theme' => 'in:light,dark,auto',
                'refresh_interval' => 'integer|min:0|max:600',
                'widgets' => 'array',
                'widgets.*' => 'boolean'
            ]);

            // Save to session for now (could be extended to save to database)
            session([
                'dashboard_customization' => $customizationData,
                'dashboard_customization_updated' => now()->toISOString()
            ]);

            Log::info('Dashboard customization saved', [
                'user_id' => $userId,
                'role' => $role,
                'customization' => $customizationData
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customization saved successfully',
                'data' => $customizationData,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (Exception $e) {
            Log::error('Error saving dashboard customization', [
                'user_id' => session('id'),
                'role' => session('role'),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to save customization'], 500);
        }
    }
}