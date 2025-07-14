<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Users;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\Centres;

class ValidateRouteParameters
{
    /**
     * Handle an incoming request to validate route parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $route = $request->route();
        
        if (!$route) {
            return $next($request);
        }
        
        $parameters = $route->parameters();
        
        foreach ($parameters as $key => $value) {
            if (!$this->validateParameter($key, $value, $request)) {
                Log::warning('Invalid route parameter detected', [
                    'parameter' => $key,
                    'value' => $value,
                    'user_id' => session('id'),
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip()
                ]);
                
                abort(404, 'Resource not found');
            }
        }
        
        return $next($request);
    }
    
    /**
     * Validate a specific route parameter.
     *
     * @param string $key
     * @param mixed $value
     * @param Request $request
     * @return bool
     */
    private function validateParameter($key, $value, $request)
    {
        switch ($key) {
            case 'id':
                return $this->validateId($value, $request);
                
            case 'user':
            case 'userId':
            case 'staff':
            case 'staffId':
                return $this->validateUserId($value);
                
            case 'trainee':
            case 'traineeId':
                return $this->validateTraineeId($value);
                
            case 'activity':
            case 'activityId':
                return $this->validateActivityId($value);
                
            case 'asset':
            case 'assetId':
                return $this->validateAssetId($value);
                
            case 'centre':
            case 'centreId':
                return $this->validateCentreId($value);
                
            case 'category':
            case 'categorySlug':
                return $this->validateCategorySlug($value);
                
            default:
                return $this->validateGenericId($value);
        }
    }
    
    /**
     * Validate generic ID parameter based on route context.
     */
    private function validateId($value, $request)
    {
        $path = $request->path();
        
        // [ClaudeFix: 2025-07-07] Check centre paths first (more specific matching)
        if (str_contains($path, 'centres/') || str_contains($path, 'centre/')) {
            return $this->validateCentreId($value);
        }
        
        if (str_contains($path, 'staff') || str_contains($path, 'user')) {
            return $this->validateUserId($value);
        }
        
        if (str_contains($path, 'trainee')) {
            return $this->validateTraineeId($value);
        }
        
        if (str_contains($path, 'activit')) {
            return $this->validateActivityId($value);
        }
        
        if (str_contains($path, 'asset') && !str_contains($path, 'centres/')) {
            return $this->validateAssetId($value);
        }
        
        return $this->validateGenericId($value);
    }
    
    /**
     * Validate user ID parameter.
     */
    private function validateUserId($value)
    {
        if (!$this->isValidIdFormat($value)) {
            return false;
        }
        
        return Users::where('id', $value)->exists();
    }
    
    /**
     * Validate trainee ID parameter.
     */
    private function validateTraineeId($value)
    {
        if (!$this->isValidIdFormat($value)) {
            return false;
        }
        
        return Trainee::where('id', $value)->exists();
    }
    
    /**
     * Validate activity ID parameter.
     */
    private function validateActivityId($value)
    {
        if (!$this->isValidIdFormat($value)) {
            return false;
        }
        
        return Activity::where('id', $value)->exists();
    }
    
    /**
     * Validate asset ID parameter.
     */
    private function validateAssetId($value)
    {
        if (!$this->isValidIdFormat($value)) {
            return false;
        }
        
        // [ClaudeFix: 2025-07-07] Asset model uses asset_id as primary key, not id
        return Asset::where('asset_id', $value)->exists();
    }
    
    /**
     * Validate centre ID parameter.
     */
    private function validateCentreId($value)
    {
        // [ClaudeFix: 2025-07-07] Simplified and robust centre ID validation
        try {
            // First try exact match (handles '01', '02', etc.)
            $exists = Centres::where('centre_id', $value)->exists();
            if ($exists) {
                return true;
            }
            
            // Also try as integer for backwards compatibility (handles 1, 2, etc.)
            if (is_numeric($value)) {
                $intValue = (int)$value;
                return Centres::where('centre_id', $intValue)->exists();
            }
            
            return false;
        } catch (\Exception $e) {
            \Log::error('Error validating centre ID', [
                'value' => $value,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Validate category slug parameter.
     */
    private function validateCategorySlug($value)
    {
        // [ClaudeFix: 2025-07-07] Category slugs are strings like "physical-therapy", not numeric IDs
        // Use category-specific validation instead of generic SQL injection check
        if ($this->containsCategorySpecificThreats($value)) {
            return false;
        }
        
        // Must be a valid slug format (letters, numbers, hyphens, max 100 chars)
        if (!preg_match('/^[a-z0-9\-]{1,100}$/i', $value)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate generic ID format and basic SQL injection protection.
     */
    private function validateGenericId($value)
    {
        return $this->isValidIdFormat($value);
    }
    
    /**
     * Check if the value is a valid ID format.
     */
    private function isValidIdFormat($value)
    {
        // Check for basic SQL injection patterns
        if ($this->containsSqlInjectionPatterns($value)) {
            return false;
        }
        
        // Must be numeric and positive
        if (!is_numeric($value)) {
            return false;
        }
        
        $numericValue = (int)$value;
        
        // Must be a positive integer
        if ($numericValue <= 0) {
            return false;
        }
        
        // Reasonable upper limit to prevent abuse
        if ($numericValue > 2147483647) { // 32-bit integer limit
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for common SQL injection patterns.
     */
    private function containsSqlInjectionPatterns($value)
    {
        $value = strtolower((string)$value);
        
        $patterns = [
            'union',
            'select',
            'insert',
            'update',
            'delete',
            'drop',
            'create',
            'alter',
            'exec',
            'execute',
            'sp_',
            'xp_',
            '--',
            ';',
            '/*',
            '*/',
            'char(',
            'nchar(',
            'varchar(',
            'nvarchar(',
            'ascii(',
            'substring(',
            'cast(',
            'convert(',
            'or 1=1',
            'and 1=1',
            '\''
        ];
        
        foreach ($patterns as $pattern) {
            if (str_contains($value, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for threats specific to category slugs (excluding legitimate hyphens).
     */
    private function containsCategorySpecificThreats($value)
    {
        $value = strtolower((string)$value);
        
        $threats = [
            'union',
            'select',
            'insert',
            'update',
            'delete',
            'drop',
            'create',
            'alter',
            'exec',
            'execute',
            'sp_',
            'xp_',
            ';',
            '/*',
            '*/',
            'script',
            '<script',
            '</script',
            'javascript:',
            'vbscript:',
            'onload=',
            'onerror=',
            'eval(',
            'expression(',
            '\''
        ];
        
        foreach ($threats as $threat) {
            if (str_contains($value, $threat)) {
                return true;
            }
        }
        
        return false;
    }
}