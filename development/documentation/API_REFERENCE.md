# CREAMS API Reference Documentation

## Authentication System

CREAMS uses **session-based authentication** (not Laravel's built-in Auth system).

### Authentication Headers
```
Content-Type: application/json
X-CSRF-TOKEN: {csrf_token}
X-Requested-With: XMLHttpRequest
```

### Session Variables
```php
session('id')        // Current user ID
session('role')      // User role: admin, supervisor, teacher, ajk  
session('name')      // User display name
session('centre_id') // User's assigned center ID
```

## Authentication Endpoints

### POST /auth/check
**Description**: User login  
**Method**: POST  
**Status**: ✅ Working

**Request Body**:
```json
{
    "email": "user@example.com",
    "password": "password123",
    "remember": true
}
```

**Response (Success)**:
```json
{
    "success": true,
    "redirect": "/dashboard",
    "user": {
        "id": 1,
        "name": "John Doe",
        "role": "supervisor",
        "centre_id": 1
    }
}
```

**Response (Error)**:
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

### GET /logout
**Description**: User logout  
**Method**: GET  
**Status**: ✅ Working

**Response**: Redirect to login page

### POST /forgot-password
**Description**: Request password reset  
**Method**: POST  
**Status**: ❌ Broken (table name mismatch)

**Request Body**:
```json
{
    "email": "user@example.com"
}
```

**Expected Response**:
```json
{
    "success": true,
    "message": "Password reset email sent"
}
```

**Current Error**: Table 'creams.password_resets' doesn't exist

## Dashboard Endpoints

### GET /dashboard
**Description**: Role-based dashboard  
**Method**: GET  
**Status**: ⚠️ Partially working

**Response**: HTML dashboard view with statistics

**Known Issues**: Statistics broken due to database schema mismatches

## User Management Endpoints

### GET /profile
**Description**: User profile page  
**Method**: GET  
**Status**: ✅ Working

### POST /profile/update
**Description**: Update user profile  
**Method**: POST  
**Status**: ✅ Working

**Request Body**:
```json
{
    "name": "Updated Name",
    "email": "newemail@example.com",
    "phone": "012-3456789",
    "position": "Senior Teacher"
}
```

### POST /profile/upload-avatar
**Description**: Upload profile picture  
**Method**: POST  
**Status**: ✅ Working

**Request**: Multipart form data with 'avatar' file

## Letter Management Endpoints

### POST /profile/letter-generate
**Description**: Generate new letter (Direct method)  
**Method**: POST  
**Status**: ✅ Working (Recently fixed)

**Request Body**:
```json
{
    "reference_number": "LTR/2025/07/0001",
    "letter_date": "2025-07-18",
    "recipient_name": "John Doe",
    "recipient_address": "123 Main St, Kuala Lumpur",
    "subject": "Rehabilitation Program Invitation",
    "content": "Letter content here..."
}
```

**Response (Success)**:
```json
{
    "success": true,
    "message": "Letter generated successfully!",
    "reference_number": "LTR/2025/07/0001",
    "letter_id": 123,
    "download_url": "/profile/letter-download/123"
}
```

### GET /letters-archive
**Description**: View all letters archive  
**Method**: GET  
**Status**: ✅ Working (Recently added)

**Response**: HTML page with letters table

### GET /profile/letter-download/{id}
**Description**: Download letter PDF  
**Method**: GET  
**Status**: ✅ Working

**Response**: PDF file download

### POST /profile/letter-preview
**Description**: Preview letter before generation  
**Method**: POST  
**Status**: ⚠️ Slow/broken (2+ minutes, often fails)

## Trainee Management Endpoints

### GET /traineeshome
**Description**: Trainee list and management  
**Method**: GET  
**Status**: ⚠️ Loads but with unrealistic test data

### GET /traineeprofile/{id}
**Description**: View trainee profile  
**Method**: GET  
**Status**: ❌ Broken

**Error**: Unknown column 'activity_enrollments.status'

### POST /traineesregistrationstore
**Description**: Register new trainee  
**Method**: POST  
**Status**: ❌ Broken

**Error**: 'trainee_id' doesn't have a default value

**Expected Request Body**:
```json
{
    "first_name": "Ahmad",
    "last_name": "Rahman",
    "date_of_birth": "2015-06-15",
    "gender": "male",
    "ic_number": "150615-10-1234",
    "phone": "012-3456789",
    "address": "456 Jalan Makmur, Petaling Jaya",
    "emergency_contact_name": "Siti Rahman",
    "emergency_contact_phone": "013-7654321",
    "disability_type": "Autism Spectrum Disorder",
    "centre_id": 1
}
```

## Activity Management Endpoints

### GET /activities
**Description**: Activity list and management  
**Method**: GET  
**Status**: ❌ Broken (redirects to dashboard)

**Error**: Unknown column 'is_active' in where clause

### GET /activities/{id}
**Description**: View specific activity  
**Method**: GET  
**Status**: ❌ Broken

### POST /activities
**Description**: Create new activity  
**Method**: POST  
**Status**: ❌ Broken

**Expected Request Body**:
```json
{
    "activity_name": "Speech Therapy Session",
    "activity_description": "Individual speech therapy for communication skills",
    "category_id": 1,
    "centre_id": 1,
    "teacher_id": 5,
    "capacity": 8,
    "duration_minutes": 60,
    "location": "Therapy Room 1",
    "objectives": "Improve verbal communication and articulation"
}
```

## Asset Management Endpoints

### GET /assets
**Description**: Asset list and management  
**Method**: GET  
**Status**: ❌ Broken (404 error)

### GET /centres
**Description**: Centers list and management  
**Method**: GET  
**Status**: ❌ Broken (redirects to dashboard)

## Notification Endpoints

### GET /notifications
**Description**: User notifications  
**Method**: GET  
**Status**: ❌ Broken

**Error**: Column name mismatches in notifications table

### GET /notifications/unread
**Description**: Get unread notifications count  
**Method**: GET  
**Status**: ❌ Broken

**Expected Response**:
```json
{
    "count": 5,
    "notifications": [
        {
            "id": 1,
            "title": "New Trainee Registration",
            "message": "A new trainee has been registered",
            "created_at": "2025-07-18T10:30:00Z"
        }
    ]
}
```

## Search Endpoints

### GET /search
**Description**: Global search functionality  
**Method**: GET  
**Status**: ❌ Non-functional

**Query Parameters**:
- `q`: Search query string
- `type`: Filter by content type (trainees, activities, etc.)

## Report Endpoints

### GET /reports/trainees
**Description**: Trainee reports  
**Method**: GET  
**Status**: ❌ Not implemented

### GET /reports/activities
**Description**: Activity reports  
**Method**: GET  
**Status**: ❌ Not implemented

### GET /reports/centres
**Description**: Center performance reports  
**Method**: GET  
**Status**: ❌ Not implemented

## File Upload Endpoints

### POST /profile/upload-avatar
**Description**: Upload user profile picture  
**Method**: POST  
**Status**: ✅ Working

**Request**: Multipart form data
**File Requirements**: Images only, max 2MB

### POST /profile/letter-template
**Description**: Upload letter template images  
**Method**: POST  
**Status**: ✅ Working

**Request**: Multipart form data with header_image and footer_image

## Error Codes and Responses

### Standard Error Response Format
```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Validation error message"]
    },
    "code": "ERROR_CODE"
}
```

### Common Error Codes
- `AUTH_REQUIRED`: Authentication required
- `PERMISSION_DENIED`: Insufficient permissions
- `VALIDATION_ERROR`: Input validation failed
- `NOT_FOUND`: Resource not found
- `DATABASE_ERROR`: Database operation failed
- `SERVER_ERROR`: Internal server error

### HTTP Status Codes Used
- `200`: Success
- `201`: Created
- `400`: Bad Request
- `401`: Unauthorized  
- `403`: Forbidden
- `404`: Not Found
- `422`: Validation Error
- `500`: Internal Server Error

## Rate Limiting

Currently not implemented. Recommended for production:
- Login attempts: 5 per minute per IP
- API calls: 60 per minute per user
- File uploads: 10 per minute per user

## Database Schema Dependencies

### Critical Schema Issues Affecting APIs:
1. `password_resets` vs `password_reset_tokens` table name
2. `activity_sessions.scheduled_date` vs `session_date` column
3. `notifications` table column mismatches
4. Missing `activity_enrollments.status` column
5. Missing `activities.is_active` column

### Foreign Key Relationships
All major endpoints depend on proper foreign key relationships:
- Users → Centres
- Trainees → Centres, Users
- Activities → Centres, Categories, Users
- Activity Sessions → Activities
- Activity Enrollments → Trainees, Activities

## API Testing

### Working Endpoints for Testing
```bash
# Login
curl -X POST http://localhost:8000/auth/check \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@test.com","password":"password"}'

# Profile
curl -X GET http://localhost:8000/profile \
  -H "Cookie: laravel_session=..."

# Letter Generation (after login)
curl -X POST http://localhost:8000/profile/letter-generate \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: ..." \
  -d '{"reference_number":"TEST001","letter_date":"2025-07-18","recipient_name":"Test User","subject":"Test Letter","content":"Test content"}'
```

### Broken Endpoints (Will Fail)
- `/forgot-password` - Table name mismatch
- `/activities` - Column name issues
- `/traineeshome` - Profile access broken
- `/assets` - 404 error
- `/centres` - Redirect loop
- `/notifications/unread` - Schema mismatch

## Future API Enhancements

### Recommended Additions
1. **REST API**: Full RESTful API with JSON responses
2. **API Tokens**: Token-based authentication for mobile apps
3. **Webhooks**: Real-time notifications for external systems
4. **Bulk Operations**: Batch create/update operations
5. **Advanced Filtering**: Complex query support
6. **File Management API**: Comprehensive file handling
7. **Reporting API**: Automated report generation
8. **Integration API**: Third-party system integration

### Mobile App Support
Future mobile app will need:
- Token-based authentication
- Offline capability
- Push notifications
- File synchronization
- Reduced payload sizes

This API reference reflects the current state and identifies areas requiring immediate attention for full functionality.