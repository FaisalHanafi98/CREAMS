<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Throwable;

class ErrorHandlingMiddleware
{
    /**
     * Handle an incoming request with comprehensive error tracking
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        try {
            // Log incoming request
            $this->logIncomingRequest($request);
            
            // Process request
            $response = $next($request);
            
            // Log response time for performance monitoring
            $this->logPerformance($request, $startTime);
            
            return $response;
            
        } catch (Throwable $e) {
            // Log the error with full context
            $this->logRequestError($request, $e, $startTime);
            
            // Re-throw to let the global exception handler deal with it
            throw $e;
        }
    }
    
    /**
     * Log incoming request details
     */
    private function logIncomingRequest(Request $request): void
    {
        // Skip logging for static assets and health checks
        if ($this->shouldSkipLogging($request)) {
            return;
        }
        
        Log::channel('activity')->info('Incoming request', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => session('id'),
            'user_role' => session('role'),
            'session_id' => $request->session()->getId(),
            'timestamp' => now(),
            'request_id' => uniqid('req_')
        ]);
    }
    
    /**
     * Log performance metrics
     */
    private function logPerformance(Request $request, float $startTime): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log slow requests
        if ($responseTime > 1000) { // Requests over 1 second
            Log::channel('performance')->warning('Slow request detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route() ? $request->route()->getName() : 'unknown',
                'response_time_ms' => $responseTime,
                'user_id' => session('id'),
                'memory_usage' => round(memory_get_peak_usage() / 1024 / 1024, 2) . 'MB',
                'timestamp' => now()
            ]);
        }
        
        // Log general performance metrics for analysis
        if (!$this->shouldSkipLogging($request)) {
            Log::channel('performance')->debug('Request completed', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'response_time_ms' => $responseTime,
                'memory_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
                'user_id' => session('id')
            ]);
        }
    }
    
    /**
     * Log request errors with comprehensive context
     */
    private function logRequestError(Request $request, Throwable $e, float $startTime): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $errorContext = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'controller' => $request->route() ? $request->route()->getActionName() : 'unknown',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => session('id'),
            'user_role' => session('role'),
            'session_id' => $request->session()->getId(),
            'response_time_ms' => $responseTime,
            'memory_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'exception_type' => get_class($e),
            'exception_message' => $e->getMessage(),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
            'request_data' => $this->sanitizeRequestData($request->all()),
            'timestamp' => now(),
            'error_id' => uniqid('err_')
        ];
        
        Log::channel('application')->error('Request failed with exception', $errorContext);
    }
    
    /**
     * Determine if request should be skipped from logging
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPatterns = [
            '/health',
            '/ping',
            '/_debugbar',
            '/telescope',
            '/horizon',
            '/favicon.ico',
            '/robots.txt',
            '.css',
            '.js',
            '.png',
            '.jpg',
            '.jpeg',
            '.gif',
            '.svg',
            '.ico',
            '.woff',
            '.woff2',
            '.ttf',
            '.eot'
        ];
        
        $path = $request->getPathInfo();
        
        foreach ($skipPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize request data for logging
     */
    private function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'api_secret', 'credit_card',
            'cvv', 'ssn', 'ic_number', '_token'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        return $data;
    }
}