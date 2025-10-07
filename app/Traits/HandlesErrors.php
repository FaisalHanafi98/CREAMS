<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\CREAMSException;
use App\Exceptions\DatabaseException;
use App\Exceptions\FileUploadException;

trait HandlesErrors
{
    /**
     * Handle exceptions with appropriate user messages and logging
     */
    protected function handleException(Exception $e, string $operation = 'operation', array $context = [])
    {
        // Add standard context
        $context = array_merge([
            'operation' => $operation,
            'user_id' => session('id'),
            'url' => request()->fullUrl(),
            'ip' => request()->ip()
        ], $context);

        // Handle different types of exceptions
        if ($e instanceof CREAMSException) {
            $e->logError();
            return $this->errorResponse($e->getUserMessage(), $context);
        } 
        elseif ($e instanceof QueryException) {
            return $this->handleDatabaseException($e, $operation, $context);
        } 
        elseif ($e instanceof ValidationException) {
            return $this->handleValidationException($e, $context);
        } 
        else {
            return $this->handleGenericException($e, $operation, $context);
        }
    }

    /**
     * Handle database exceptions
     */
    protected function handleDatabaseException(QueryException $e, string $operation, array $context = [])
    {
        $context['sql'] = $e->getSql();
        $context['bindings'] = $e->getBindings();
        $context['code'] = $e->getCode();

        // Log detailed database error
        Log::error("Database error while {$operation}", [
            'error' => $e->getMessage(),
            'sql' => $e->getSql(),
            'bindings' => $e->getBindings(),
            'code' => $e->getCode(),
            'user_id' => session('id')
        ]);

        $userMessage = $this->getUserFriendlyDatabaseMessage($e);
        return $this->errorResponse($userMessage, $context);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(ValidationException $e, array $context = [])
    {
        Log::info('Validation error occurred', [
            'errors' => $e->errors(),
            'user_id' => session('id'),
            'context' => $context
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

        return redirect()->back()
            ->withErrors($e->errors())
            ->withInput();
    }

    /**
     * Handle generic exceptions
     */
    protected function handleGenericException(Exception $e, string $operation, array $context = [])
    {
        Log::error("Error during {$operation}", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => session('id'),
            'context' => $context
        ]);

        $userMessage = 'An unexpected error occurred. Please try again or contact support if the problem persists.';
        return $this->errorResponse($userMessage, $context);
    }

    /**
     * Return error response (JSON or redirect)
     */
    protected function errorResponse(string $message, array $context = [])
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_id' => uniqid('err_')
            ], 500);
        }

        return redirect()->back()
            ->with('error', $message)
            ->withInput();
    }

    /**
     * Return success response (JSON or redirect)
     */
    protected function successResponse(string $message, $data = null, ?string $redirectRoute = null)
    {
        if (request()->expectsJson()) {
            $response = [
                'success' => true,
                'message' => $message
            ];
            
            if ($data !== null) {
                $response['data'] = $data;
            }
            
            return response()->json($response);
        }

        if ($redirectRoute) {
            return redirect()->route($redirectRoute)->with('success', $message);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Get user-friendly database error messages
     */
    protected function getUserFriendlyDatabaseMessage(QueryException $e): string
    {
        $errorCode = $e->getCode();
        $message = $e->getMessage();

        // Handle specific database error codes
        if (str_contains($message, 'Duplicate entry')) {
            if (str_contains($message, 'email')) {
                return 'This email address is already registered in our system.';
            }
            return 'This record already exists in our system.';
        }

        if (str_contains($message, 'Column not found')) {
            return 'A database structure error occurred. Please contact system administrator.';
        }

        if (str_contains($message, 'Table') && str_contains($message, "doesn't exist")) {
            return 'A database table is missing. Please contact system administrator.';
        }

        if (str_contains($message, 'foreign key constraint')) {
            return 'Cannot perform this action due to related data dependencies.';
        }

        if (str_contains($message, 'Data too long')) {
            return 'One or more fields contain too much text. Please reduce the content and try again.';
        }

        // Default database error message
        return 'A database error occurred. Please contact system administrator.';
    }

    /**
     * Validate file upload with enhanced error handling
     */
    protected function validateFileUpload($file, array $rules = [])
    {
        if (!$file) {
            return null;
        }

        $defaultRules = [
            'max_size' => 2048, // 2MB
            'allowed_types' => ['jpeg', 'png', 'jpg', 'gif'],
            'required' => false
        ];

        $rules = array_merge($defaultRules, $rules);

        try {
            // Check file size
            if ($file->getSize() > ($rules['max_size'] * 1024)) {
                throw new FileUploadException(
                    "File size exceeds limit: " . $file->getSize() . " bytes",
                    "File size must be less than " . ($rules['max_size'] / 1024) . "MB"
                );
            }

            // Check file type
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $rules['allowed_types'])) {
                throw new FileUploadException(
                    "Invalid file type: {$extension}",
                    "File must be one of: " . implode(', ', $rules['allowed_types'])
                );
            }

            // Check if file is actually an image (if image types are allowed)
            $imageTypes = ['jpeg', 'png', 'jpg', 'gif'];
            if (array_intersect($rules['allowed_types'], $imageTypes)) {
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo === false) {
                    throw new FileUploadException(
                        "File is not a valid image",
                        "Please upload a valid image file"
                    );
                }
            }

            return true;

        } catch (Exception $e) {
            if ($e instanceof FileUploadException) {
                throw $e;
            }
            
            throw new FileUploadException(
                "File validation error: " . $e->getMessage(),
                "File upload validation failed. Please try again with a different file."
            );
        }
    }

    /**
     * Log user action with context
     */
    protected function logUserAction(string $action, array $context = [])
    {
        Log::info("User action: {$action}", array_merge([
            'user_id' => session('id'),
            'user_role' => session('role'),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now()
        ], $context));
    }

    /**
     * Check if user has required role(s)
     */
    protected function requireRole($roles)
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        $userRole = session('role');
        
        if (!in_array($userRole, $roles)) {
            throw new \App\Exceptions\AuthorizationException(
                "User role '{$userRole}' not in required roles: " . implode(', ', $roles),
                "You do not have permission to perform this action."
            );
        }

        return true;
    }
}