<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class OptimizedDashboardController extends Controller
{
    protected $dashboardService;
    
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    /**
     * Display role-specific optimized dashboard
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Check if mobile request or force mobile
            $isMobile = $this->isMobileDevice($request) || $request->get('mobile') === 'true';
            
            // Start performance tracking
            $startTime = microtime(true);
            
            // Get dashboard data with caching
            $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
            
            // Add performance metrics
            $dashboardData['performance'] = [
                'load_time' => round((microtime(true) - $startTime) * 1000, 2),
                'cache_status' => Cache::has("dashboard_{$role}_{$userId}_{$centreId}") ? 'hit' : 'miss',
                'last_updated' => now()->toISOString()
            ];
            
            // Determine view based on role and device
            if ($isMobile) {
                $view = 'dashboard.mobile';
                // Simplify data for mobile
                $dashboardData = $this->prepareMobileData($dashboardData, $role);
            } else {
                $view = $this->getViewForRole($role);
            }
            
            // Add common data for all dashboards
            $dashboardData['user'] = [
                'id' => $userId,
                'name' => session('name'),
                'role' => $role,
                'centre_id' => $centreId,
                'last_login' => session('last_login')
            ];
            
            // Set cache headers for better performance
            return response()
                ->view($view, $dashboardData)
                ->header('Cache-Control', 'public, max-age=300')
                ->header('X-Load-Time', $dashboardData['performance']['load_time'] . 'ms')
                ->header('X-Device-Type', $isMobile ? 'mobile' : 'desktop');
                
        } catch (\Exception $e) {
            Log::error('Dashboard loading failed', [
                'user_id' => session('id') ?? 'unknown',
                'role' => session('role') ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleDashboardError($e);
        }
    }
    
    /**
     * Get real-time updates via AJAX
     */
    public function getUpdates(Request $request)
    {
        try {
            $lastUpdate = $request->get('last_update', 0);
            $role = session('role');
            $userId = session('id');
            $centreId = session('centre_id');
            $isMobile = $request->boolean('mobile', false);
            
            // Get updates since last check
            $updates = $this->getRecentUpdates($lastUpdate, $role, $userId, $centreId);
            
            // Get fresh stats if requested
            $includeStats = $request->boolean('include_stats', false);
            $stats = null;
            
            if ($includeStats) {
                $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
                if ($isMobile) {
                    // Simplified stats for mobile
                    $stats = array_slice($dashboardData['stats'] ?? [], 0, 4);
                } else {
                    $stats = $dashboardData['stats'] ?? [];
                }
            }
            
            $response = [
                'success' => true,
                'updates' => $updates,
                'stats' => $stats,
                'timestamp' => time(),
                'server_time' => now()->toISOString()
            ];
            
            // Add mobile-specific optimizations
            if ($isMobile) {
                $response['mobile_optimized'] = true;
                $response['data_compressed'] = true;
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Dashboard updates failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get updates'
            ], 500);
        }
    }
    
    /**
     * Refresh dashboard statistics
     */
    public function refreshStats(Request $request)
    {
        try {
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Clear cache for this user
            $this->dashboardService->clearUserCache($userId, $role, $centreId);
            
            // Get fresh data
            $startTime = microtime(true);
            $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
            $loadTime = round((microtime(true) - $startTime) * 1000, 2);
            
            return response()->json([
                'success' => true,
                'stats' => $dashboardData['stats'] ?? [],
                'charts' => $dashboardData['charts'] ?? [],
                'load_time' => $loadTime,
                'timestamp' => time()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard refresh failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh dashboard'
            ], 500);
        }
    }
    
    /**
     * Get widget data for AJAX loading
     */
    public function getWidget(Request $request, $widget)
    {
        try {
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            $data = $this->getWidgetData($widget, $userId, $role, $centreId);
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'widget' => $widget
            ]);
            
        } catch (\Exception $e) {
            Log::error('Widget loading failed', [
                'widget' => $widget,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load widget'
            ], 500);
        }
    }
    
    /**
     * Export dashboard data
     */
    public function export(Request $request, $format = 'json')
    {
        try {
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
            
            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($dashboardData);
                case 'pdf':
                    return $this->exportToPdf($dashboardData);
                case 'excel':
                    return $this->exportToExcel($dashboardData);
                default:
                    return response()->json($dashboardData);
            }
            
        } catch (\Exception $e) {
            Log::error('Dashboard export failed', [
                'format' => $format,
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Export failed'
            ], 500);
        }
    }
    
    /**
     * Clear dashboard cache (admin only)
     */
    public function clearCache(Request $request)
    {
        try {
            // Check admin permission
            if (session('role') !== 'admin') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
            }
            
            $this->dashboardService->clearAllCaches();
            
            Log::info('Dashboard cache cleared', [
                'admin_id' => session('id')
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache clearing failed', [
                'admin_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache'
            ], 500);
        }
    }
    
    /**
     * Get mobile-optimized dashboard
     */
    public function mobile(Request $request)
    {
        try {
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Get simplified data for mobile
            $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
            
            // Simplify data for mobile view
            $mobileData = [
                'stats' => $dashboardData['stats'] ?? [],
                'recent' => [
                    'activities' => array_slice($dashboardData['recent']['activities'] ?? [], 0, 3),
                    'notifications' => array_slice($dashboardData['alerts'] ?? [], 0, 5)
                ],
                'quick_actions' => $this->getMobileQuickActions($role)
            ];
            
            return view('dashboard.mobile', $mobileData);
            
        } catch (\Exception $e) {
            Log::error('Mobile dashboard failed', [
                'user_id' => session('id'),
                'error' => $e->getMessage()
            ]);
            
            return $this->handleDashboardError($e);
        }
    }
    
    // =============================================
    // PRIVATE HELPER METHODS
    // =============================================
    
    /**
     * Get view name based on role
     */
    private function getViewForRole($role): string
    {
        $views = [
            'admin' => 'dashboard.admin',
            'supervisor' => 'dashboard.supervisor', 
            'teacher' => 'dashboard.teacher',
            'ajk' => 'dashboard.ajk'
        ];
        
        $view = $views[$role] ?? 'dashboard.default';
        
        // Check if role-specific view exists, fallback to default
        if (!view()->exists($view)) {
            $view = 'dashboard.default';
        }
        
        return $view;
    }
    
    /**
     * Get recent updates based on role and timestamp
     */
    private function getRecentUpdates($lastUpdate, $role, $userId, $centreId): array
    {
        $updates = [];
        $since = Carbon::createFromTimestamp($lastUpdate);
        
        try {
            switch ($role) {
                case 'admin':
                    $updates = $this->getAdminUpdates($since);
                    break;
                case 'supervisor':
                    $updates = $this->getSupervisorUpdates($since, $centreId);
                    break;
                case 'teacher':
                    $updates = $this->getTeacherUpdates($since, $userId);
                    break;
                case 'ajk':
                    $updates = $this->getAjkUpdates($since, $centreId);
                    break;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get role-specific updates', [
                'role' => $role,
                'error' => $e->getMessage()
            ]);
        }
        
        return $updates;
    }
    
    /**
     * Get admin-specific updates
     */
    private function getAdminUpdates($since): array
    {
        $updates = [];
        
        // Check for new users
        $newUsers = \App\Models\Users::where('created_at', '>', $since)->count();
        if ($newUsers > 0) {
            $updates[] = [
                'type' => 'info',
                'icon' => 'fas fa-user-plus',
                'message' => "{$newUsers} new user(s) registered",
                'action' => route('users.index'),
                'timestamp' => now()->toISOString()
            ];
        }
        
        // Check for new trainees
        $newTrainees = \App\Models\Trainee::where('created_at', '>', $since)->count();
        if ($newTrainees > 0) {
            $updates[] = [
                'type' => 'success',
                'icon' => 'fas fa-user-graduate',
                'message' => "{$newTrainees} new trainee(s) enrolled",
                'action' => route('trainees.index'),
                'timestamp' => now()->toISOString()
            ];
        }
        
        // Check for system alerts
        $criticalAlerts = \App\Models\AssetMaintenance::where('scheduled_date', '<', now())
            ->where('status', 'scheduled')
            ->where('updated_at', '>', $since)
            ->count();
            
        if ($criticalAlerts > 0) {
            $updates[] = [
                'type' => 'warning',
                'icon' => 'fas fa-exclamation-triangle',
                'message' => "{$criticalAlerts} maintenance task(s) are now overdue",
                'action' => route('assets.maintenance'),
                'timestamp' => now()->toISOString()
            ];
        }
        
        return $updates;
    }
    
    /**
     * Get teacher-specific updates
     */
    private function getTeacherUpdates($since, $userId): array
    {
        $updates = [];
        
        // Check for new enrollments in teacher's sessions
        $newEnrollments = \App\Models\ActivityEnrollment::whereHas('session', function($q) use ($userId) {
                $q->where('teacher_id', $userId);
            })
            ->where('created_at', '>', $since)
            ->count();
            
        if ($newEnrollments > 0) {
            $updates[] = [
                'type' => 'info',
                'icon' => 'fas fa-user-check',
                'message' => "{$newEnrollments} new enrollment(s) in your sessions",
                'action' => route('activities.index'),
                'timestamp' => now()->toISOString()
            ];
        }
        
        // Check for sessions requiring attendance
        $pendingAttendance = \App\Models\ActivitySession::where('teacher_id', $userId)
            ->where('status', 'scheduled')
            ->where('session_date', '<', now())
            ->where('updated_at', '>', $since)
            ->count();
            
        if ($pendingAttendance > 0) {
            $updates[] = [
                'type' => 'warning',
                'icon' => 'fas fa-clipboard-check',
                'message' => "{$pendingAttendance} session(s) need attendance marking",
                'action' => route('activities.index'),
                'timestamp' => now()->toISOString()
            ];
        }
        
        return $updates;
    }
    
    /**
     * Get supervisor-specific updates
     */
    private function getSupervisorUpdates($since, $centreId): array
    {
        // Implementation similar to teacher updates but centre-specific
        return [];
    }
    
    /**
     * Get AJK-specific updates
     */
    private function getAjkUpdates($since, $centreId): array
    {
        // Implementation for AJK role updates
        return [];
    }
    
    /**
     * Get widget-specific data
     */
    private function getWidgetData($widget, $userId, $role, $centreId): array
    {
        $cacheKey = "widget_{$widget}_{$role}_{$userId}_{$centreId}";
        
        return Cache::remember($cacheKey, 60, function() use ($widget, $userId, $role, $centreId) {
            switch ($widget) {
                case 'quick-stats':
                    return $this->getQuickStatsWidget($userId, $role, $centreId);
                case 'recent-activity':
                    return $this->getRecentActivityWidget($userId, $role, $centreId);
                case 'schedule':
                    return $this->getScheduleWidget($userId, $role, $centreId);
                case 'alerts':
                    return $this->getAlertsWidget($userId, $role, $centreId);
                default:
                    return [];
            }
        });
    }
    
    /**
     * Get mobile quick actions based on role
     */
    private function getMobileQuickActions($role): array
    {
        $actions = [
            'admin' => [
                ['icon' => 'fas fa-user-plus', 'label' => 'Add User', 'route' => 'users.create'],
                ['icon' => 'fas fa-chart-bar', 'label' => 'Reports', 'route' => 'reports.index'],
                ['icon' => 'fas fa-cog', 'label' => 'Settings', 'route' => 'settings.index']
            ],
            'teacher' => [
                ['icon' => 'fas fa-clipboard-check', 'label' => 'Attendance', 'route' => 'activities.index'],
                ['icon' => 'fas fa-calendar', 'label' => 'Schedule', 'route' => 'activities.schedule'],
                ['icon' => 'fas fa-user-graduate', 'label' => 'Trainees', 'route' => 'trainees.index']
            ],
            'supervisor' => [
                ['icon' => 'fas fa-users', 'label' => 'Team', 'route' => 'users.index'],
                ['icon' => 'fas fa-chart-line', 'label' => 'Performance', 'route' => 'reports.index'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Schedule', 'route' => 'activities.index']
            ],
            'ajk' => [
                ['icon' => 'fas fa-tools', 'label' => 'Maintenance', 'route' => 'assets.maintenance'],
                ['icon' => 'fas fa-box', 'label' => 'Assets', 'route' => 'assets.index'],
                ['icon' => 'fas fa-bell', 'label' => 'Alerts', 'route' => 'notifications.index']
            ]
        ];
        
        return $actions[$role] ?? [];
    }
    
    /**
     * Detect if the request is from a mobile device
     */
    private function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->header('User-Agent');
        
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
            'Windows Phone', 'Opera Mini', 'IEMobile', 'Mobile Safari'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        // Check for small screen size indicators
        $mobileRegex = '/(' . implode('|', [
            'android', 'webos', 'iphone', 'ipad', 'ipod', 'blackberry', 'iemobile', 'opera mini'
        ]) . ')/i';
        
        return preg_match($mobileRegex, $userAgent);
    }
    
    /**
     * Prepare data specifically for mobile view
     */
    private function prepareMobileData(array $dashboardData, string $role): array
    {
        // Simplify and optimize data for mobile
        $mobileData = [
            'stats' => $dashboardData['stats'] ?? [],
            'performance' => $dashboardData['performance'] ?? [],
            'recent' => [
                'activities' => array_slice($dashboardData['recent']['activities'] ?? [], 0, 3),
                'notifications' => array_slice($dashboardData['alerts'] ?? [], 0, 5)
            ],
            'quick_actions' => $this->getMobileQuickActions($role)
        ];
        
        // Add mobile-specific optimizations
        $mobileData['mobile_optimized'] = true;
        $mobileData['compression_applied'] = true;
        
        return $mobileData;
    }
    
    /**
     * Handle dashboard errors gracefully
     */
    private function handleDashboardError(\Exception $e): \Illuminate\Http\Response
    {
        $errorData = [
            'error' => true,
            'message' => 'Dashboard temporarily unavailable',
            'details' => app()->environment('local') ? $e->getMessage() : null,
            'stats' => [
                'total_users' => 0,
                'total_trainees' => 0,
                'total_activities' => 0,
                'total_sessions' => 0
            ],
            'alerts' => [
                [
                    'type' => 'danger',
                    'message' => 'Dashboard data is temporarily unavailable. Please refresh the page.',
                    'action' => '#'
                ]
            ]
        ];
        
        return response()->view('dashboard.error', $errorData, 200);
    }
    
    // =============================================
    // EXPORT FUNCTIONALITY
    // =============================================
    
    private function exportToCsv($data) 
    {
        $filename = 'dashboard_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['Metric', 'Value', 'Description']);
            
            // Add stats data
            if (isset($data['stats'])) {
                foreach ($data['stats'] as $key => $value) {
                    fputcsv($file, [
                        ucwords(str_replace('_', ' ', $key)),
                        $value,
                        'Dashboard statistic'
                    ]);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportToPdf($data) 
    {
        // For future implementation with a PDF library like DomPDF
        return response()->json([
            'success' => false,
            'message' => 'PDF export functionality coming soon'
        ]);
    }
    
    private function exportToExcel($data) 
    {
        // For future implementation with PhpSpreadsheet
        return response()->json([
            'success' => false,
            'message' => 'Excel export functionality coming soon'
        ]);
    }
    
    // =============================================
    // WIDGET METHODS
    // =============================================
    
    private function getQuickStatsWidget($userId, $role, $centreId) 
    {
        return [
            'stats' => $this->dashboardService->getDashboardData($userId, $role, $centreId)['stats'] ?? [],
            'last_updated' => now()->toISOString()
        ];
    }
    
    private function getRecentActivityWidget($userId, $role, $centreId) 
    {
        $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
        
        return [
            'activities' => $dashboardData['recent']['activities'] ?? [],
            'count' => count($dashboardData['recent']['activities'] ?? []),
            'last_updated' => now()->toISOString()
        ];
    }
    
    private function getScheduleWidget($userId, $role, $centreId) 
    {
        $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
        
        return [
            'today' => $dashboardData['schedule']['today'] ?? [],
            'upcoming' => array_slice($dashboardData['schedule']['upcoming'] ?? [], 0, 5),
            'last_updated' => now()->toISOString()
        ];
    }
    
    private function getAlertsWidget($userId, $role, $centreId) 
    {
        $dashboardData = $this->dashboardService->getDashboardData($userId, $role, $centreId);
        
        return [
            'alerts' => $dashboardData['alerts'] ?? [],
            'count' => count($dashboardData['alerts'] ?? []),
            'critical_count' => count(array_filter($dashboardData['alerts'] ?? [], function($alert) {
                return ($alert['type'] ?? '') === 'danger';
            })),
            'last_updated' => now()->toISOString()
        ];
    }
}