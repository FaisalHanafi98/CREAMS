<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
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
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required',
                    'redirect' => route('login')
                ], 401);
            }
            
            return redirect()->route('login')
                ->with('error', 'Please login to access this resource.');
        }

        $userRole = session('role');

        // Check if user has required role
        if (!empty($roles) && !in_array($userRole, $roles)) {
            \Log::warning('Unauthorized role access attempt', [
                'user_id' => session('id'),
                'user_role' => $userRole,
                'required_roles' => $roles,
                'route' => $request->route()->getName(),
                'ip' => $request->ip()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient permissions',
                    'required_roles' => $roles,
                    'user_role' => $userRole
                ], 403);
            }

            abort(403, 'Unauthorized. Required roles: ' . implode(', ', $roles));
        }

        // Log successful access for audit trail
        \Log::info('Role-based access granted', [
            'user_id' => session('id'),
            'user_role' => $userRole,
            'route' => $request->route()->getName(),
            'method' => $request->method()
        ]);

        return $next($request);
    }
}