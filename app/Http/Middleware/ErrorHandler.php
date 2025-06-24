<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ErrorHandler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Log successful requests for monitoring
            if ($request->route()) {
                Log::info('Request processed', [
                    'method' => $request->method(),
                    'url' => $request->fullUrl(),
                    'user_id' => session('id'),
                    'user_role' => session('role'),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'status' => $response->getStatusCode()
                ]);
            }
            
            return $response;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Request failed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'user_id' => session('id'),
                'user_role' => session('role'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return user-friendly error page
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'An error occurred processing your request.',
                    'message' => config('app.debug') ? $e->getMessage() : 'Please try again later.'
                ], 500);
            }
            
            return response()->view('errors.500', [
                'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred.'
            ], 500);
        }
    }
}