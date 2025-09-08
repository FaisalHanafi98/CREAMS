<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Exception;
use Throwable;

class ApiErrorHandlingMiddleware
{
    /**
     * Handle an incoming API request with comprehensive error handling and rate limiting
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $rateLimitKey = $this->getRateLimitKey($request);
        
        try {
            // Apply rate limiting
            if (!$this->checkRateLimit($request, $rateLimitKey)) {
                return $this->rateLimitExceededResponse($request);
            }
            
            // Log API request
            $this->logApiRequest($request);
            
            // Process request
            $response = $next($request);
            
            // Log API response
            $this->logApiResponse($request, $response, $startTime);
            
            return $response;
            
        } catch (Throwable $e) {
            // Log API error
            $this->logApiError($request, $e, $startTime);
            
            // Return formatted API error response
            return $this->formatApiErrorResponse($e, $request);
        }
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimit(Request $request, string $key): bool
    {
        // Different rate limits based on authentication
        $maxAttempts = $this->getMaxAttempts($request);
        $decayMinutes = $this->getDecayMinutes($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        return true;
    }
    
    /**
     * Get rate limit key for the request
     */
    private function getRateLimitKey(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $ip = $request->ip();
        
        return "api_rate_limit:{$userId}:{$ip}";
    }
    
    /**
     * Get maximum attempts based on user type
     */
    private function getMaxAttempts(Request $request): int
    {
        if ($request->user()) {
            // Authenticated users get higher limits
            return match($request->user()->role ?? 'user') {
                'admin', 'supervisor' => 1000, // Higher limits for admins
                'teacher', 'ajk' => 500,      // Medium limits for staff
                default => 200                 // Standard limits for others
            };
        }
        
        return 60; // Guest users get lower limits
    }
    
    /**
     * Get decay minutes based on user type
     */
    private function getDecayMinutes(Request $request): int
    {
        return $request->user() ? 60 : 15; // Authenticated users: 1 hour, guests: 15 minutes
    }
    
    /**
     * Return rate limit exceeded response
     */
    private function rateLimitExceededResponse(Request $request): Response
    {
        $rateLimitKey = $this->getRateLimitKey($request);
        $retryAfter = RateLimiter::availableIn($rateLimitKey);
        
        Log::channel('security')->warning('API rate limit exceeded', [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'retry_after' => $retryAfter,
            'timestamp' => now()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'error_code' => 'RATE_LIMIT_EXCEEDED',
            'retry_after' => $retryAfter,
            'error_id' => uniqid('rate_limit_')
        ], 429)->header('Retry-After', $retryAfter);
    }
    
    /**
     * Log API request
     */
    private function logApiRequest(Request $request): void
    {
        Log::channel('activity')->info('API request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'request_data' => $this->sanitizeApiData($request->all()),
            'timestamp' => now(),
            'request_id' => uniqid('api_req_')
        ]);
    }
    
    /**
     * Log API response
     */
    private function logApiResponse(Request $request, Response $response, float $startTime): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        $logData = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $request->getPathInfo(),
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
            'user_id' => $request->user()?->id,
            'memory_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'timestamp' => now()
        ];
        
        // Log based on response status
        if ($response->getStatusCode() >= 400) {
            Log::channel('application')->warning('API request completed with error', $logData);
        } elseif ($responseTime > 2000) {
            Log::channel('performance')->warning('Slow API response', $logData);
        } else {
            Log::channel('activity')->debug('API request completed', $logData);
        }
    }
    
    /**
     * Log API error
     */
    private function logApiError(Request $request, Throwable $e, float $startTime): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        Log::channel('application')->error('API request failed', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'endpoint' => $request->getPathInfo(),
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_time_ms' => $responseTime,
            'memory_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'exception_type' => get_class($e),
            'exception_message' => $e->getMessage(),
            'exception_file' => $e->getFile(),
            'exception_line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'request_data' => $this->sanitizeApiData($request->all()),
            'timestamp' => now(),
            'error_id' => uniqid('api_err_')
        ]);
    }
    
    /**
     * Format API error response
     */
    private function formatApiErrorResponse(Throwable $e, Request $request): Response
    {
        $errorId = uniqid('api_err_');
        
        // Determine status code based on exception type
        $statusCode = match(get_class($e)) {
            'Illuminate\\Auth\\AuthenticationException' => 401,
            'Illuminate\\Auth\\Access\\AuthorizationException' => 403,
            'Illuminate\\Database\\Eloquent\\ModelNotFoundException' => 404,
            'Illuminate\\Validation\\ValidationException' => 422,
            'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException' => 404,
            'Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException' => 405,
            default => 500
        };
        
        // Get user-friendly error message
        $message = $this->getUserFriendlyApiMessage($e, $statusCode);
        
        $response = [
            'success' => false,
            'message' => $message,
            'error_code' => $this->getApiErrorCode($e),
            'error_id' => $errorId,
            'timestamp' => now()->toISOString()
        ];
        
        // Add additional data for specific error types
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $response['errors'] = $e->errors();
        }
        
        // Add debug information in non-production environments
        if (!app()->environment('production')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray()
            ];
        }
        
        return response()->json($response, $statusCode);
    }
    
    /**
     * Get user-friendly API error message
     */
    private function getUserFriendlyApiMessage(Throwable $e, int $statusCode): string
    {
        return match($statusCode) {
            401 => 'Authentication required. Please provide valid credentials.',
            403 => 'You do not have permission to access this resource.',
            404 => 'The requested resource was not found.',
            405 => 'Method not allowed for this endpoint.',
            422 => 'The provided data is invalid.',
            429 => 'Too many requests. Please try again later.',
            500 => 'An internal server error occurred. Please try again or contact support.',
            default => 'An error occurred while processing your request.'
        };
    }
    
    /**
     * Get API error code
     */
    private function getApiErrorCode(Throwable $e): string
    {
        return match(get_class($e)) {
            'Illuminate\\Auth\\AuthenticationException' => 'AUTHENTICATION_REQUIRED',
            'Illuminate\\Auth\\Access\\AuthorizationException' => 'AUTHORIZATION_FAILED',
            'Illuminate\\Database\\Eloquent\\ModelNotFoundException' => 'RESOURCE_NOT_FOUND',
            'Illuminate\\Validation\\ValidationException' => 'VALIDATION_ERROR',
            'Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException' => 'ENDPOINT_NOT_FOUND',
            'Symfony\\Component\\HttpKernel\\Exception\\MethodNotAllowedHttpException' => 'METHOD_NOT_ALLOWED',
            default => 'INTERNAL_SERVER_ERROR'
        };
    }
    
    /**
     * Sanitize API data for logging
     */
    private function sanitizeApiData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'api_secret', 'authorization',
            'x-api-key', 'x-auth-token', 'bearer', '_token'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        
        return $data;
    }
}