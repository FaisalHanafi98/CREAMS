<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Custom exception class for CREAMS system
 */
class CREAMSException extends Exception
{
    protected $errorCode;
    protected $userMessage;
    protected $context;

    public function __construct($message = "", $userMessage = null, $errorCode = 'CREAMS_ERROR', $context = [], $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        
        $this->errorCode = $errorCode;
        $this->userMessage = $userMessage ?? 'An unexpected error occurred. Please try again or contact support if the problem persists.';
        $this->context = $context;
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Get context information
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Log the exception with context
     */
    public function logError(): void
    {
        Log::error($this->errorCode . ': ' . $this->getMessage(), [
            'error_code' => $this->errorCode,
            'user_message' => $this->userMessage,
            'context' => $this->context,
            'stack_trace' => $this->getTraceAsString(),
            'user_id' => session('id'),
            'url' => request()->fullUrl(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}

/**
 * Database-related exceptions
 */
class DatabaseException extends CREAMSException
{
    public function __construct($message = "", $userMessage = null, $context = [], $code = 0, Exception $previous = null)
    {
        $defaultUserMessage = 'A database error occurred. Please contact system administrator.';
        parent::__construct($message, $userMessage ?? $defaultUserMessage, 'DATABASE_ERROR', $context, $code, $previous);
    }
}

/**
 * Validation-related exceptions
 */
class ValidationException extends CREAMSException
{
    public function __construct($message = "", $userMessage = null, $context = [], $code = 0, Exception $previous = null)
    {
        $defaultUserMessage = 'The provided information is invalid. Please check your input and try again.';
        parent::__construct($message, $userMessage ?? $defaultUserMessage, 'VALIDATION_ERROR', $context, $code, $previous);
    }
}

/**
 * Authorization-related exceptions
 */
class AuthorizationException extends CREAMSException
{
    public function __construct($message = "", $userMessage = null, $context = [], $code = 0, Exception $previous = null)
    {
        $defaultUserMessage = 'You do not have permission to perform this action.';
        parent::__construct($message, $userMessage ?? $defaultUserMessage, 'AUTHORIZATION_ERROR', $context, $code, $previous);
    }
}

/**
 * File upload-related exceptions
 */
class FileUploadException extends CREAMSException
{
    public function __construct($message = "", $userMessage = null, $context = [], $code = 0, Exception $previous = null)
    {
        $defaultUserMessage = 'File upload failed. Please check the file size and format.';
        parent::__construct($message, $userMessage ?? $defaultUserMessage, 'FILE_UPLOAD_ERROR', $context, $code, $previous);
    }
}

/**
 * Model-related exceptions
 */
class ModelException extends CREAMSException
{
    public function __construct($message = "", $userMessage = null, $context = [], $code = 0, Exception $previous = null)
    {
        $defaultUserMessage = 'A data processing error occurred. Please try again.';
        parent::__construct($message, $userMessage ?? $defaultUserMessage, 'MODEL_ERROR', $context, $code, $previous);
    }
}