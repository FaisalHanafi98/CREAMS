<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException as LaravelAuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Throwable;
use Exception;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'token',
        'api_key',
        'api_secret',
    ];

    /**
     * List of exceptions that should not be reported
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        AuthenticationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logException($e);
            
            // Send critical error alerts for production
            if (app()->environment('production') && $this->shouldSendAlert($e)) {
                $this->sendErrorAlert($e);
            }
        });

        // Handle specific exception types with custom responses
        $this->renderable(function (CREAMSException $e, Request $request) {
            return $this->handleCREAMSException($e, $request);
        });

        $this->renderable(function (QueryException $e, Request $request) {
            return $this->handleDatabaseException($e, $request);
        });

        $this->renderable(function (TokenMismatchException $e, Request $request) {
            return $this->handleCSRFException($e, $request);
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            return $this->handleAuthException($e, $request);
        });

        $this->renderable(function (LaravelAuthorizationException $e, Request $request) {
            return $this->handleAuthorizationException($e, $request);
        });
    }

    /**
     * Log exception with comprehensive context
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'exception_type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString(),
            'user_id' => session('id'),
            'user_role' => session('role'),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
            'environment' => app()->environment(),
        ];

        // Add request data if available (excluding sensitive info)
        if (request()) {
            $context['request_data'] = $this->sanitizeRequestData(request()->all());
            $context['headers'] = $this->sanitizeHeaders(request()->headers->all());
        }

        // Use different log channels based on exception type
        if ($e instanceof QueryException) {
            Log::channel('database')->error('Database Exception', $context);
        } elseif ($e instanceof ValidationException) {
            Log::channel('validation')->info('Validation Exception', $context);
        } elseif ($e instanceof AuthenticationException) {
            Log::channel('security')->warning('Authentication Exception', $context);
        } elseif ($e instanceof LaravelAuthorizationException) {
            Log::channel('security')->warning('Authorization Exception', $context);
        } elseif ($e instanceof CREAMSException) {
            Log::channel('application')->error('CREAMS Exception', $context);
        } else {
            Log::error('Unhandled Exception', $context);
        }
    }

    /**
     * Handle CREAMS custom exceptions
     */
    protected function handleCREAMSException(CREAMSException $e, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getUserMessage(),
                'error_code' => $e->getErrorCode(),
                'error_id' => uniqid('err_')
            ], 500);
        }

        return back()->withInput()->with('error', $e->getUserMessage());
    }

    /**
     * Handle database exceptions
     */
    protected function handleDatabaseException(QueryException $e, Request $request)
    {
        $userMessage = $this->getUserFriendlyDatabaseMessage($e);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_code' => 'DATABASE_ERROR',
                'error_id' => uniqid('db_err_')
            ], 500);
        }

        return back()->withInput()->with('error', $userMessage);
    }

    /**
     * Handle CSRF token mismatch
     */
    protected function handleCSRFException(TokenMismatchException $e, Request $request)
    {
        $message = 'Your session has expired. Please refresh the page and try again.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'CSRF_ERROR',
                'reload_required' => true
            ], 419);
        }

        return redirect()->back()
            ->with('error', $message)
            ->with('reload_required', true);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthException(AuthenticationException $e, Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
                'error_code' => 'AUTH_REQUIRED',
                'redirect_url' => route('login')
            ], 401);
        }

        return redirect()->guest(route('login'))
            ->with('error', 'You must be logged in to access this page.');
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleAuthorizationException(LaravelAuthorizationException $e, Request $request)
    {
        $message = 'You do not have permission to perform this action.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'error_code' => 'AUTHORIZATION_ERROR'
            ], 403);
        }

        return back()->with('error', $message);
    }

    /**
     * Get user-friendly database error messages
     */
    protected function getUserFriendlyDatabaseMessage(QueryException $e): string
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Duplicate entry')) {
            if (str_contains($message, 'email')) {
                return 'This email address is already registered.';
            }
            if (str_contains($message, 'phone')) {
                return 'This phone number is already registered.';
            }
            return 'This record already exists in our system.';
        }

        if (str_contains($message, 'foreign key constraint')) {
            return 'Cannot perform this action due to related data dependencies.';
        }

        if (str_contains($message, 'Data too long')) {
            return 'One or more fields contain too much text. Please reduce the content.';
        }

        if (str_contains($message, "doesn't exist")) {
            return 'Required data is missing. Please contact system administrator.';
        }

        return 'A database error occurred. Please try again or contact support.';
    }

    /**
     * Determine if error alert should be sent
     */
    protected function shouldSendAlert(Throwable $e): bool
    {
        $criticalExceptions = [
            QueryException::class,
            CREAMSException::class,
            'Error',
            'ParseError',
            'TypeError',
        ];

        foreach ($criticalExceptions as $exceptionType) {
            if ($e instanceof $exceptionType || str_contains(get_class($e), $exceptionType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send error alert to administrators
     */
    protected function sendErrorAlert(Throwable $e): void
    {
        try {
            $adminEmails = Config::get('mail.admin_emails', []);
            
            if (!empty($adminEmails)) {
                $errorData = [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => request()->fullUrl(),
                    'user_id' => session('id'),
                    'timestamp' => now(),
                ];

                // Note: Implement email template for error alerts
                // Mail::to($adminEmails)->send(new ErrorAlert($errorData));
            }
        } catch (Exception $mailException) {
            Log::error('Failed to send error alert email', [
                'original_error' => $e->getMessage(),
                'mail_error' => $mailException->getMessage()
            ]);
        }
    }

    /**
     * Sanitize request data for logging (remove sensitive info)
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password',
            'token', 'api_key', 'api_secret', 'credit_card',
            'cvv', 'ssn', 'ic_number'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Sanitize headers for logging
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization', 'x-api-key', 'x-auth-token'
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = '[REDACTED]';
            }
        }

        return $headers;
    }

    /**
     * Create a response instance from the given validation exception.
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
            'error_code' => 'VALIDATION_ERROR'
        ], $exception->status);
    }

    /**
     * Convert a validation exception into a JSON response.
     */
    protected function invalid($request, ValidationException $exception)
    {
        return redirect($exception->redirectTo ?? url()->previous())
            ->withInput($request->except($this->dontFlash))
            ->withErrors($exception->errors(), $exception->errorBag);
    }
}
