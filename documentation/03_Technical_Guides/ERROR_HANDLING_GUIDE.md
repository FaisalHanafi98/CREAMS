# CREAMS Error Handling & Logging System

## Overview

This document describes the comprehensive error handling and logging system implemented in the CREAMS application. The system provides extensive error catching, user-friendly error messages, detailed logging, and proactive monitoring with alerting capabilities.

## Architecture Components

### 1. Exception Handler (`app/Exceptions/Handler.php`)
- **Comprehensive Error Handling**: Handles all types of exceptions with specific responses
- **Automatic Logging**: Logs all exceptions with full context including user info, request data, and stack traces  
- **Sanitized Logging**: Removes sensitive data (passwords, tokens, etc.) from logs
- **Multi-Channel Logging**: Routes different error types to specialized log channels
- **Production Alerts**: Sends email alerts for critical errors in production
- **JSON/Web Response Handling**: Provides appropriate responses based on request type

### 2. Specialized Log Channels (`config/logging.php`)
- **database.log**: Database errors and query failures (30-day retention)
- **security.log**: Authentication and authorization issues (90-day retention)  
- **application.log**: General application errors (30-day retention)
- **validation.log**: Form validation errors (7-day retention)
- **activity.log**: User actions and audit trail (60-day retention)
- **performance.log**: Slow queries and performance issues (14-day retention)
- **files.log**: File operations and upload errors (30-day retention)

### 3. Custom Exception Classes (`app/Exceptions/CREAMSException.php`)
- **CREAMSException**: Base exception with user-friendly messages
- **DatabaseException**: Database-specific errors with user context
- **ValidationException**: Enhanced validation with detailed field info
- **AuthorizationException**: Permission and access control errors
- **FileUploadException**: File handling and upload errors
- **ModelException**: Data processing and model operation errors

### 4. Error Handling Trait (`app/Traits/HandlesErrors.php`)
- **Exception Handling**: Centralized error handling methods for controllers
- **User-Friendly Messages**: Converts technical errors to user-readable messages  
- **Response Formatting**: Handles both JSON API and web form responses
- **File Validation**: Enhanced file upload validation with error handling
- **Action Logging**: Logs user actions for audit trails
- **Role Validation**: Ensures proper user permissions with clear error messages

### 5. Base Model with Error Handling (`app/Models/BaseModel.php`)
- **Safe Operations**: Error-wrapped CRUD operations (safeCreate, safeUpdate, safeDelete)
- **Detailed Logging**: Logs all database operations with user context
- **Relationship Loading**: Safe loading of model relationships with error handling
- **Audit Trail**: Automatic logging of model events (created, updated, deleted)
- **Bulk Operations**: Safe bulk insert operations with comprehensive error handling

### 6. Enhanced Middleware

#### Error Handling Middleware (`app/Http/Middleware/ErrorHandlingMiddleware.php`)
- **Request/Response Logging**: Comprehensive logging of all HTTP requests
- **Performance Monitoring**: Tracks response times and identifies slow requests
- **Error Context**: Captures full request context when errors occur
- **Memory Usage Tracking**: Monitors memory consumption per request
- **Static Asset Filtering**: Skips logging for images, CSS, JS files

#### API Error Handling Middleware (`app/Http/Middleware/ApiErrorHandlingMiddleware.php`)
- **Rate Limiting**: Prevents API abuse with configurable limits per user type
- **Structured Error Responses**: Consistent JSON error format for APIs
- **Authentication-Based Limits**: Different rate limits for guests vs authenticated users
- **Error Response Formatting**: Standardized API error responses with error codes
- **Debug Information**: Includes stack traces in non-production environments

### 7. Enhanced Form Validation (`app/Http/Requests/BaseFormRequest.php`)
- **User-Friendly Validation**: Converts technical validation errors to readable messages
- **Field Labels**: Provides friendly names for form fields in error messages
- **Comprehensive Logging**: Logs validation failures with user context
- **Sanitized Input**: Removes sensitive data from validation logs
- **Custom Error Messages**: Tailored error messages for specific validation rules

### 8. Error Monitoring Service (`app/Services/ErrorMonitoringService.php`)
- **Threshold Monitoring**: Tracks error counts and triggers alerts when thresholds exceeded
- **Burst Detection**: Identifies error spikes (many errors in short time)
- **Email Alerts**: Sends notifications to administrators for critical issues
- **System Health**: Provides overall system health status based on error rates
- **Statistics Dashboard**: Error statistics for monitoring and analysis
- **Alert Cooldown**: Prevents alert spam with configurable cooldown periods

## Usage Examples

### Controllers Using HandlesErrors Trait

```php
<?php
namespace App\Http\Controllers;

use App\Traits\HandlesErrors;

class ExampleController extends Controller
{
    use HandlesErrors;

    public function store(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users'
            ]);

            // Create record
            $user = User::safeCreate($validated);

            // Log success
            $this->logUserAction('User created', ['user_id' => $user->id]);

            return $this->successResponse('User created successfully');

        } catch (Exception $e) {
            return $this->handleException($e, 'creating user', [
                'input_data' => $request->except(['password'])
            ]);
        }
    }
}
```

### Models Extending BaseModel

```php
<?php
namespace App\Models;

class User extends BaseModel
{
    // Use safe operations instead of regular Eloquent methods
    
    public function updateProfile(array $data)
    {
        return $this->safeUpdate($data); // Handles errors automatically
    }

    public static function createNewUser(array $data)
    {
        return static::safeCreate($data); // Logs creation with error handling
    }
}
```

### Using Custom Form Requests

```php
<?php
namespace App\Http\Requests;

class UserRegistrationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone_number' => ['required', new MalaysianPhoneRule()],
            'password' => 'required|string|min:8|confirmed'
        ];
    }

    // BaseFormRequest automatically provides:
    // - User-friendly error messages
    // - Comprehensive validation logging  
    // - Sanitized input data in logs
    // - Proper JSON/web response handling
}
```

### Error Monitoring Integration

```php
// Track custom errors
use App\Services\ErrorMonitoringService;

try {
    // Some risky operation
    $result = $this->performComplexOperation();
} catch (Exception $e) {
    // Track the error for monitoring
    ErrorMonitoringService::trackError('COMPLEX_OPERATION_ERROR', $e->getMessage(), [
        'operation' => 'performComplexOperation',
        'user_id' => auth()->id(),
        'parameters' => $operationParams
    ]);
    
    throw $e; // Re-throw for normal handling
}

// Get system health status
$health = ErrorMonitoringService::getSystemHealthStatus();
// Returns: ['status' => 'HEALTHY', 'level' => 'success', 'total_errors' => 0, ...]
```

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# Logging Configuration
LOG_CHANNEL=stack
LOG_LEVEL=info

# Error Monitoring
ERROR_MONITORING_ENABLED=true

# Alert Email Configuration (for production)
MAIL_ADMIN_EMAILS="admin@creams.com,supervisor@creams.com"
```

### Middleware Registration

Add to `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\ErrorHandlingMiddleware::class,
    ],
    
    'api' => [
        // ... existing middleware  
        \App\Http\Middleware\ApiErrorHandlingMiddleware::class,
    ],
];
```

## Error Response Formats

### Web Form Errors
- Redirect back with error flash message
- Validation errors displayed next to form fields
- User-friendly error messages
- Form input preserved for user convenience

### JSON API Errors
```json
{
    "success": false,
    "message": "The provided data is invalid.",
    "error_code": "VALIDATION_ERROR", 
    "error_id": "val_err_64f1a2b3c4d5e",
    "errors": {
        "email": {
            "messages": ["The email field is required."],
            "friendly_message": "Please provide your Email Address.",
            "field_label": "Email Address"
        }
    }
}
```

## Monitoring and Alerts

### Error Thresholds (per hour)
- **Database Errors**: 5 errors trigger alert
- **Authorization Errors**: 10 errors trigger alert  
- **Validation Errors**: 50 errors trigger alert
- **File Upload Errors**: 10 errors trigger alert
- **Model Errors**: 8 errors trigger alert
- **Critical CREAMS Errors**: 3 errors trigger alert

### Burst Detection
- **Burst Threshold**: 10 errors in 5 minutes
- **Alert Cooldown**: 1 hour between similar alerts
- **Burst Cooldown**: 5 minutes between burst alerts

### System Health Levels
- **HEALTHY**: 0 errors in last 24 hours
- **GOOD**: 1-9 errors in last 24 hours  
- **WARNING**: 10-49 errors in last 24 hours
- **CRITICAL**: 50+ errors in last 24 hours

## Log File Locations

All logs are stored in `storage/logs/`:
- `laravel.log` - General application logs
- `database.log` - Database-related errors
- `security.log` - Authentication and authorization
- `application.log` - Application-specific errors
- `validation.log` - Form validation failures
- `activity.log` - User actions and audit trail
- `performance.log` - Performance and slow queries
- `files.log` - File operations and uploads

## Security Considerations

### Data Sanitization
All sensitive data is automatically removed from logs:
- Passwords and password confirmations
- API keys and tokens  
- Credit card numbers and CVV codes
- IC numbers and SSNs
- Authorization headers

### Rate Limiting
API rate limits by user type:
- **Admin/Supervisor**: 1000 requests/hour
- **Teacher/AJK**: 500 requests/hour
- **Regular Users**: 200 requests/hour
- **Guests**: 60 requests/hour

## Troubleshooting

### Common Issues

1. **Logs not appearing in specialized channels**
   - Check `config/logging.php` channel configuration
   - Verify storage/logs directory permissions
   - Ensure LOG_CHANNEL is set to 'stack' in .env

2. **Error alerts not being sent**
   - Verify MAIL_ADMIN_EMAILS is set in .env
   - Check mail configuration in config/mail.php
   - Review application.log for mail sending errors

3. **Rate limiting too aggressive**
   - Adjust limits in ApiErrorHandlingMiddleware
   - Clear rate limit cache: `php artisan cache:clear`
   - Check user role assignment

### Maintenance Commands

```bash
# Clear error monitoring counts
ErrorMonitoringService::clearErrorCounts();

# Get current error statistics  
$stats = ErrorMonitoringService::getErrorStats(24);

# Check system health
$health = ErrorMonitoringService::getSystemHealthStatus();

# Clear application cache (includes rate limits)
php artisan cache:clear

# View recent logs
tail -f storage/logs/application.log
tail -f storage/logs/database.log
tail -f storage/logs/security.log
```

## Best Practices

1. **Always use safe model operations** (`safeCreate`, `safeUpdate`, `safeDelete`) instead of direct Eloquent methods
2. **Extend BaseFormRequest** for all form validations to get enhanced error handling
3. **Use HandlesErrors trait** in all controllers for consistent error handling
4. **Log user actions** using `logUserAction()` for important operations
5. **Monitor error statistics** regularly through the monitoring service
6. **Test error handling** in development using intentional failures
7. **Review logs regularly** to identify patterns and recurring issues
8. **Keep sensitive data out of logs** by using the built-in sanitization
9. **Set appropriate log retention periods** based on compliance requirements
10. **Configure email alerts** for production environments

This comprehensive error handling system ensures that all errors in the CREAMS application are properly caught, logged with full context, and handled gracefully with user-friendly messages while providing administrators with detailed monitoring and alerting capabilities.