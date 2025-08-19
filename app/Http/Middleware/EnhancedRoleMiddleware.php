<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnhancedRoleMiddleware
{
    /**
     * Handle an incoming request with enhanced error handling.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Check if user is authenticated (has session)
        if (!session()->has('id') || !session()->has('role')) {
            \Log::warning('Unauthenticated access attempt', [
                'route' => $request->route()?->getName() ?? 'unknown',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required',
                    'message' => 'Please login to access this resource.',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Please login to access this resource.')
                ->with('intended_url', $request->fullUrl());
        }

        $userRole = session('role');
        $userName = session('name', 'Unknown User');
        $userId = session('id');

        // Check if user has required role
        if (!empty($roles) && !in_array($userRole, $roles)) {
            \Log::warning('Unauthorized role access attempt', [
                'user_id' => $userId,
                'user_name' => $userName,
                'user_role' => $userRole,
                'required_roles' => $roles,
                'route' => $request->route()?->getName() ?? 'unknown',
                'url' => $request->fullUrl(),
                'ip' => $request->ip()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient permissions',
                    'message' => 'You do not have permission to access this resource.',
                    'required_roles' => $roles,
                    'user_role' => $userRole,
                    'redirect' => $this->getRoleBasedDashboard($userRole)
                ], 403);
            }

            // Create user-friendly error message
            $roleNames = [
                'admin' => 'Administrator',
                'supervisor' => 'Supervisor', 
                'teacher' => 'Teacher',
                'ajk' => 'AJK Committee Member'
            ];

            $requiredRoleNames = array_map(function($role) use ($roleNames) {
                return $roleNames[$role] ?? ucfirst($role);
            }, $roles);

            $userRoleName = $roleNames[$userRole] ?? ucfirst($userRole);

            $errorMessage = count($requiredRoleNames) === 1 
                ? "This page requires {$requiredRoleNames[0]} access. You are currently logged in as a {$userRoleName}."
                : "This page requires one of the following roles: " . implode(', ', $requiredRoleNames) . ". You are currently logged in as a {$userRoleName}.";

            return redirect()->route($userRole . '.dashboard')
                ->with('warning', $errorMessage)
                ->with('access_denied', true);
        }

        // Log successful access for audit trail
        \Log::info('Role-based access granted', [
            'user_id' => $userId,
            'user_name' => $userName,
            'user_role' => $userRole,
            'route' => $request->route()?->getName() ?? 'unknown',
            'method' => $request->method(),
            'url' => $request->fullUrl()
        ]);

        return $next($request);
    }

    /**
     * Get the appropriate dashboard route for a given role
     *
     * @param string $role
     * @return string
     */
    private function getRoleBasedDashboard(string $role): string
    {
        $dashboardRoutes = [
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'teacher' => 'teacher.dashboard',
            'ajk' => 'ajk.dashboard'
        ];

        return route($dashboardRoutes[$role] ?? 'dashboard');
    }
}