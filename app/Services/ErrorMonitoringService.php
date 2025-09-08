<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Exception;

class ErrorMonitoringService
{
    private const ERROR_THRESHOLD_CACHE_KEY = 'error_threshold_';
    private const ERROR_BURST_CACHE_KEY = 'error_burst_';
    private const ALERT_COOLDOWN_CACHE_KEY = 'alert_cooldown_';
    
    /**
     * Track error occurrence and trigger alerts if thresholds are exceeded
     */
    public static function trackError(string $errorType, string $message, array $context = []): void
    {
        $service = new self();
        
        try {
            // Track error occurrence
            $service->incrementErrorCount($errorType);
            
            // Check if we should send an alert
            if ($service->shouldSendAlert($errorType)) {
                $service->sendErrorAlert($errorType, $message, $context);
            }
            
            // Check for error bursts (many errors in short time)
            if ($service->detectErrorBurst($errorType)) {
                $service->sendBurstAlert($errorType, $context);
            }
            
            // Log to monitoring channel
            Log::channel('application')->info('Error tracked by monitoring service', [
                'error_type' => $errorType,
                'message' => $message,
                'context' => $context,
                'timestamp' => now()
            ]);
            
        } catch (Exception $e) {
            // Fail silently to avoid recursive errors
            Log::emergency('Error monitoring service failed', [
                'original_error' => $message,
                'monitoring_error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Increment error count for a specific error type
     */
    private function incrementErrorCount(string $errorType): int
    {
        $key = self::ERROR_THRESHOLD_CACHE_KEY . $errorType;
        $ttl = 3600; // 1 hour window
        
        return Cache::remember($key, $ttl, function () {
            return 0;
        }) + Cache::increment($key, 1);
    }
    
    /**
     * Check if we should send an alert based on error thresholds
     */
    private function shouldSendAlert(string $errorType): bool
    {
        $count = Cache::get(self::ERROR_THRESHOLD_CACHE_KEY . $errorType, 0);
        $threshold = $this->getErrorThreshold($errorType);
        
        // Check if alert is in cooldown
        $cooldownKey = self::ALERT_COOLDOWN_CACHE_KEY . $errorType;
        if (Cache::has($cooldownKey)) {
            return false;
        }
        
        return $count >= $threshold;
    }
    
    /**
     * Get error threshold based on error type
     */
    private function getErrorThreshold(string $errorType): int
    {
        $thresholds = [
            'DATABASE_ERROR' => 5,        // 5 database errors per hour
            'AUTHORIZATION_ERROR' => 10,   // 10 auth errors per hour
            'VALIDATION_ERROR' => 50,      // 50 validation errors per hour
            'FILE_UPLOAD_ERROR' => 10,     // 10 file upload errors per hour
            'MODEL_ERROR' => 8,            // 8 model errors per hour
            'CREAMS_ERROR' => 3,           // 3 critical CREAMS errors per hour
            'default' => 15                // Default threshold
        ];
        
        return $thresholds[$errorType] ?? $thresholds['default'];
    }
    
    /**
     * Detect error bursts (many errors in short time period)
     */
    private function detectErrorBurst(string $errorType): bool
    {
        $burstKey = self::ERROR_BURST_CACHE_KEY . $errorType;
        $burstWindow = 300; // 5 minutes
        $burstThreshold = 10; // 10 errors in 5 minutes
        
        $burstCount = Cache::remember($burstKey, $burstWindow, function () {
            return 0;
        });
        
        $burstCount = Cache::increment($burstKey, 1);
        
        return $burstCount >= $burstThreshold;
    }
    
    /**
     * Send error alert to administrators
     */
    private function sendErrorAlert(string $errorType, string $message, array $context): void
    {
        try {
            $adminEmails = Config::get('mail.admin_emails', []);
            $count = Cache::get(self::ERROR_THRESHOLD_CACHE_KEY . $errorType, 0);
            
            if (empty($adminEmails)) {
                return;
            }
            
            $alertData = [
                'error_type' => $errorType,
                'error_count' => $count,
                'time_window' => '1 hour',
                'message' => $message,
                'context' => $this->sanitizeContext($context),
                'timestamp' => now(),
                'server' => request()->getHost(),
                'environment' => app()->environment()
            ];
            
            // Set cooldown to prevent spam (1 hour)
            Cache::put(self::ALERT_COOLDOWN_CACHE_KEY . $errorType, true, 3600);
            
            // Log the alert
            Log::channel('application')->critical('Error threshold exceeded - Alert sent', $alertData);
            
            // TODO: Implement actual email sending
            // Mail::to($adminEmails)->send(new ErrorThresholdAlert($alertData));
            
        } catch (Exception $e) {
            Log::emergency('Failed to send error alert', [
                'error_type' => $errorType,
                'alert_error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send burst alert when many errors occur quickly
     */
    private function sendBurstAlert(string $errorType, array $context): void
    {
        try {
            $adminEmails = Config::get('mail.admin_emails', []);
            $burstCount = Cache::get(self::ERROR_BURST_CACHE_KEY . $errorType, 0);
            
            if (empty($adminEmails)) {
                return;
            }
            
            // Only send burst alert once per burst window
            $burstAlertKey = 'burst_alert_sent_' . $errorType;
            if (Cache::has($burstAlertKey)) {
                return;
            }
            
            Cache::put($burstAlertKey, true, 300); // 5 minute cooldown
            
            $alertData = [
                'error_type' => $errorType,
                'burst_count' => $burstCount,
                'time_window' => '5 minutes',
                'context' => $this->sanitizeContext($context),
                'timestamp' => now(),
                'server' => request()->getHost(),
                'environment' => app()->environment(),
                'alert_type' => 'BURST'
            ];
            
            Log::channel('application')->critical('Error burst detected - Alert sent', $alertData);
            
            // TODO: Implement actual email sending
            // Mail::to($adminEmails)->send(new ErrorBurstAlert($alertData));
            
        } catch (Exception $e) {
            Log::emergency('Failed to send burst alert', [
                'error_type' => $errorType,
                'burst_error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get error statistics for dashboard
     */
    public static function getErrorStats(int $hours = 24): array
    {
        $service = new self();
        
        try {
            $errorTypes = [
                'DATABASE_ERROR', 'AUTHORIZATION_ERROR', 'VALIDATION_ERROR',
                'FILE_UPLOAD_ERROR', 'MODEL_ERROR', 'CREAMS_ERROR'
            ];
            
            $stats = [
                'total_errors' => 0,
                'error_types' => [],
                'time_period' => "{$hours} hours",
                'generated_at' => now()
            ];
            
            foreach ($errorTypes as $errorType) {
                $count = Cache::get(self::ERROR_THRESHOLD_CACHE_KEY . $errorType, 0);
                $stats['error_types'][$errorType] = $count;
                $stats['total_errors'] += $count;
            }
            
            return $stats;
            
        } catch (Exception $e) {
            Log::error('Failed to generate error stats', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'total_errors' => 0,
                'error_types' => [],
                'error' => 'Failed to retrieve error statistics',
                'generated_at' => now()
            ];
        }
    }
    
    /**
     * Clear error counts (useful for testing or manual reset)
     */
    public static function clearErrorCounts(string $errorType = null): void
    {
        try {
            if ($errorType) {
                Cache::forget(self::ERROR_THRESHOLD_CACHE_KEY . $errorType);
                Cache::forget(self::ERROR_BURST_CACHE_KEY . $errorType);
                Cache::forget(self::ALERT_COOLDOWN_CACHE_KEY . $errorType);
                
                Log::info('Error counts cleared for type', ['error_type' => $errorType]);
            } else {
                // Clear all error monitoring caches
                $errorTypes = [
                    'DATABASE_ERROR', 'AUTHORIZATION_ERROR', 'VALIDATION_ERROR',
                    'FILE_UPLOAD_ERROR', 'MODEL_ERROR', 'CREAMS_ERROR'
                ];
                
                foreach ($errorTypes as $type) {
                    Cache::forget(self::ERROR_THRESHOLD_CACHE_KEY . $type);
                    Cache::forget(self::ERROR_BURST_CACHE_KEY . $type);
                    Cache::forget(self::ALERT_COOLDOWN_CACHE_KEY . $type);
                }
                
                Log::info('All error monitoring counts cleared');
            }
            
        } catch (Exception $e) {
            Log::error('Failed to clear error counts', [
                'error' => $e->getMessage(),
                'error_type' => $errorType
            ]);
        }
    }
    
    /**
     * Check system health based on error rates
     */
    public static function getSystemHealthStatus(): array
    {
        try {
            $stats = self::getErrorStats();
            $totalErrors = $stats['total_errors'];
            
            // Determine health status based on error counts
            if ($totalErrors === 0) {
                $status = 'HEALTHY';
                $level = 'success';
            } elseif ($totalErrors < 10) {
                $status = 'GOOD';
                $level = 'info';
            } elseif ($totalErrors < 50) {
                $status = 'WARNING';
                $level = 'warning';
            } else {
                $status = 'CRITICAL';
                $level = 'danger';
            }
            
            return [
                'status' => $status,
                'level' => $level,
                'total_errors' => $totalErrors,
                'error_breakdown' => $stats['error_types'],
                'checked_at' => now(),
                'time_period' => $stats['time_period']
            ];
            
        } catch (Exception $e) {
            return [
                'status' => 'UNKNOWN',
                'level' => 'warning',
                'error' => 'Unable to determine system health',
                'checked_at' => now()
            ];
        }
    }
    
    /**
     * Sanitize context data for alerts (remove sensitive information)
     */
    private function sanitizeContext(array $context): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'api_secret', 'authorization',
            'ic_number', 'credit_card', 'cvv', 'ssn'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($context[$field])) {
                $context[$field] = '[REDACTED]';
            }
        }
        
        // Also sanitize nested arrays
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = $this->sanitizeContext($value);
            }
        }
        
        return $context;
    }
}