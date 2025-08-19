<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleTestController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Test role-based access control
     */
    public function testRoleAccess(Request $request)
    {
        $userRole = session('role');
        $userId = session('id');
        $userName = session('name');

        $testResults = [
            'user_info' => [
                'id' => $userId,
                'name' => $userName,
                'role' => $userRole,
                'centre_id' => session('centre_id')
            ],
            'role_permissions' => $this->getRolePermissions($userRole),
            'accessible_routes' => $this->getAccessibleRoutes($userRole),
            'test_timestamp' => now()->toDateTimeString()
        ];

        Log::info('Role access test performed', [
            'user_id' => $userId,
            'role' => $userRole,
            'test_results' => $testResults
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role access test completed successfully',
            'data' => $testResults
        ]);
    }

    /**
     * Get role-specific permissions
     */
    private function getRolePermissions($role)
    {
        $permissions = [
            'admin' => [
                'dashboard' => true,
                'users' => ['view', 'create', 'edit', 'delete'],
                'trainees' => ['view', 'create', 'edit', 'delete'],
                'activities' => ['view', 'create', 'edit', 'delete'],
                'centres' => ['view', 'create', 'edit', 'delete'],
                'assets' => ['view', 'create', 'edit', 'delete'],
                'reports' => ['view', 'generate'],
                'settings' => ['view', 'edit']
            ],
            'supervisor' => [
                'dashboard' => true,
                'trainees' => ['view', 'create', 'edit'],
                'activities' => ['view', 'create_sessions'],
                'centres' => ['view'],
                'assets' => ['view', 'manage'],
                'reports' => ['view', 'limited'],
                'staff' => ['view', 'manage_centre_staff']
            ],
            'teacher' => [
                'dashboard' => true,
                'trainees' => ['view', 'edit_assigned'],
                'activities' => ['view', 'manage_assigned'],
                'sessions' => ['conduct', 'record_attendance'],
                'reports' => ['view_own', 'generate_progress']
            ],
            'ajk' => [
                'dashboard' => true,
                'activities' => ['view'],
                'reports' => ['view', 'limited'],
                'events' => ['manage'],
                'volunteers' => ['manage']
            ]
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Get accessible routes for role
     */
    private function getAccessibleRoutes($role)
    {
        $routes = [
            'admin' => [
                '/dashboard',
                '/admin/users',
                '/admin/centres',
                '/admin/assets',
                '/activities',
                '/trainees',
                '/reports',
                '/settings'
            ],
            'supervisor' => [
                '/dashboard',
                '/supervisor/trainees',
                '/supervisor/staff',
                '/activities',
                '/centres',
                '/assets',
                '/reports'
            ],
            'teacher' => [
                '/dashboard',
                '/teacher/activities',
                '/teacher/trainees',
                '/activities/sessions',
                '/attendance',
                '/reports'
            ],
            'ajk' => [
                '/dashboard',
                '/ajk/events',
                '/ajk/volunteers',
                '/activities',
                '/reports'
            ]
        ];

        return $routes[$role] ?? [];
    }

    /**
     * Test specific middleware access
     */
    public function testMiddleware(Request $request, $middleware, $role = null)
    {
        $userRole = session('role');
        $testRole = $role ?? $userRole;

        $middlewareTests = [
            'enhanced.role:admin' => in_array($testRole, ['admin']),
            'enhanced.role:admin,supervisor' => in_array($testRole, ['admin', 'supervisor']),
            'enhanced.role:admin,supervisor,teacher' => in_array($testRole, ['admin', 'supervisor', 'teacher']),
            'enhanced.role:admin,supervisor,teacher,ajk' => in_array($testRole, ['admin', 'supervisor', 'teacher', 'ajk'])
        ];

        $result = [
            'user_role' => $userRole,
            'test_role' => $testRole,
            'middleware' => $middleware,
            'access_granted' => $middlewareTests[$middleware] ?? false,
            'test_time' => now()->toDateTimeString()
        ];

        return response()->json([
            'success' => true,
            'test_result' => $result
        ]);
    }
}