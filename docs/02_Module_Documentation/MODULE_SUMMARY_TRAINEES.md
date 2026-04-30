# CREAMS Trainees Module Summary

## Overview
The Trainees module manages the complete lifecycle of rehabilitation program participants, from registration and enrollment to progress tracking and graduation. It provides comprehensive trainee information management including personal details, medical history, guardian information, and educational progress tracking with role-based access control.

## Controllers

### 1. TraineeController.php
**Purpose**: Primary trainee management with full CRUD operations and comprehensive data handling

**Key Methods**:
- `index()`: Trainee listing with pagination, statistics, and role-based filtering
- `create()`: Registration form with centre and permission validation
- `store()`: New trainee registration with comprehensive validation and file upload
- `show()`: Detailed trainee profile with statistics and relationship data
- `edit()`: Edit form with permission checking and data loading
- `update()`: Trainee information update with avatar management
- `destroy()`: Trainee deletion (admin-only) with file cleanup
- `calculateAttendanceRate()`: Private method for attendance rate calculations

**Key Features**:
- **Comprehensive Registration**: Complete trainee onboarding with all required information
- **Role-based Access**: Different access levels for admin, supervisor, teacher, and AJK roles
- **Centre Isolation**: Users see only trainees from their assigned centre
- **Avatar Management**: Profile photo upload and management with automatic cleanup
- **Guardian Information**: Complete guardian and emergency contact management
- **Medical History**: Detailed medical condition and history tracking
- **Validation System**: Comprehensive input validation with custom error messages
- **Audit Logging**: Detailed logging of all trainee operations for audit purposes

### 2. TraineeHomeController.php
**Purpose**: Main trainee dashboard and overview interface
**Features**: Dashboard display, quick statistics, recent activities

### 3. TraineeProfileController.php
**Purpose**: Detailed trainee profile management and progress tracking
**Features**: Profile viewing, progress updates, attendance recording, activity management

### 4. TraineeRegistrationController.php
**Purpose**: Alternative registration workflow for specific use cases
**Features**: Registration form handling, validation, and storage

## Models

### 1. Trainees.php
**Purpose**: Core trainee data model with comprehensive information management

**Key Properties**:
- `trainee_id`: Unique trainee identifier (string)
- `trainee_first_name`: First name
- `trainee_last_name`: Last name
- `trainee_email`: Email address (unique)
- `ic_number`: Identity card number (unique)
- `trainee_phone_number`: Contact phone number
- `trainee_date_of_birth`: Date of birth
- `gender`: Gender (Male, Female, Other)
- `trainee_address`: Home address
- `avatar`: Profile photo filename
- `trainee_condition`: Medical condition/disability type
- `centre_name`: Associated rehabilitation centre
- `centre_id`: Centre identifier for relationships
- `course_id`: Enrolled course reference
- `status`: Current status (active, inactive, suspended, graduated)
- `photo_consent`: Photo usage consent
- `services_consent`: Services consent
- `medical_history`: Detailed medical history
- `additional_notes`: Additional notes and observations

**Guardian Information**:
- `guardian_name`: Guardian's full name
- `guardian_phone`: Guardian's contact number
- `guardian_email`: Guardian's email address
- `guardian_relationship`: Relationship to trainee
- `guardian_address`: Guardian's address

**Emergency Contact**:
- `emergency_contact_name`: Emergency contact name
- `emergency_contact_phone`: Emergency contact number
- `emergency_contact_relationship`: Relationship to trainee

**Key Features**:
- **Condition Mapping**: Intelligent condition badge class mapping for UI display
- **Age Calculation**: Automatic age calculation from date of birth
- **Avatar Management**: Intelligent avatar URL generation with gender-based defaults
- **Consent Tracking**: Boolean fields for photo and services consent
- **Comprehensive Validation**: Field validation with medical condition categorization

**Key Relationships**:
- `centre()`: Belongs to rehabilitation centre
- `course()`: Belongs to enrolled course
- `activities()`: Many-to-many with activities through enrollments
- `enrollments()`: Has many activity enrollments
- `attendances()`: Has many attendance records
- `classes()`: Many-to-many with classes
- `profile()`: Has one detailed trainee profile

**Key Scopes & Methods**:
- `active()`: Filter active trainees
- `byCentre()`: Filter by centre
- `byCourse()`: Filter by course
- `byCondition()`: Filter by medical condition
- `getFullNameAttribute()`: Full name accessor
- `getConditionBadgeClassAttribute()`: Condition badge CSS class
- `getAgeAttribute()`: Age calculation
- `getAvatarUrlAttribute()`: Avatar URL with fallbacks

## Database Schema

### trainees table
```sql
- id (auto-increment primary key)
- trainee_id (string, unique) - Custom trainee identifier
- trainee_first_name (string) - First name
- trainee_last_name (string) - Last name
- trainee_email (string, unique) - Email address
- ic_number (string, unique) - Identity card number
- trainee_date_of_birth (date) - Date of birth
- gender (enum: Male, Female, Other) - Gender
- trainee_phone_number (string, nullable) - Phone number
- trainee_address (text, nullable) - Home address
- avatar (string, nullable) - Profile photo filename
- trainee_condition (string, nullable) - Medical condition
- centre_name (string) - Associated centre name
- centre_id (string) - Centre identifier
- course_id (unsigned big integer, nullable) - Course reference
- medical_history (text, nullable) - Medical history
- additional_notes (text, nullable) - Additional notes
- photo_consent (boolean, default: false) - Photo usage consent
- services_consent (boolean, default: false) - Services consent
- status (enum: active, inactive, suspended, graduated, default: active) - Status
- guardian_name (string, nullable) - Guardian name
- guardian_phone (string, nullable) - Guardian phone
- guardian_email (string, nullable) - Guardian email
- guardian_relationship (string, nullable) - Guardian relationship
- guardian_address (text, nullable) - Guardian address
- emergency_contact_name (string, nullable) - Emergency contact name
- emergency_contact_phone (string, nullable) - Emergency contact phone
- emergency_contact_relationship (string, nullable) - Emergency contact relationship
- timestamps
```

**Indexes**:
- `trainee_id` (unique index for fast lookups)
- `centre_id` (for centre-based filtering)
- `status` (for status-based queries)
- `ic_number` (unique index for identity verification)

## Views

### 1. trainees/management.blade.php
**Purpose**: Main trainee management interface with comprehensive listing
**Features**:
- Advanced trainee listing with DataTables integration
- Condition-based color coding and badges
- Avatar display with fallback handling
- Advanced filtering by condition, centre, and status
- Real-time statistics dashboard
- Quick action buttons (view, edit, delete)
- Responsive card-based layout
- Search functionality across multiple fields

### 2. trainees/create.blade.php
**Purpose**: Comprehensive trainee registration form
**Features**:
- Multi-section registration form (personal, guardian, emergency, medical)
- File upload for trainee avatar
- Centre selection with validation
- Medical condition selection with detailed options
- Consent management checkboxes
- Real-time validation feedback
- Responsive form layout
- Progress indicators

### 3. trainees/edit.blade.php
**Purpose**: Trainee information editing interface
**Features**:
- Pre-populated form with existing data
- Avatar update with preview
- Status management (active, inactive, suspended, graduated)
- Guardian and emergency contact updates
- Medical history modifications
- Permission-based field access

### 4. trainees/show.blade.php (inferred)
**Purpose**: Detailed trainee profile display
**Features**:
- Complete trainee information display
- Activity enrollment history
- Attendance records and statistics
- Progress tracking visualization
- Guardian and emergency contact information
- Medical history display
- Photo gallery and documentation

### 5. trainees/profile.blade.php
**Purpose**: Enhanced trainee profile with activity integration
**Features**:
- Activity participation history
- Progress metrics and charts
- Attendance rate calculations
- Achievement tracking
- Photo consent status
- Download profile functionality

### 6. trainees/registration.blade.php
**Purpose**: Alternative registration interface
**Features**:
- Simplified registration workflow
- Quick trainee onboarding
- Essential information capture
- Centre assignment

## Routes

### Web Routes (in routes/web.php)
```php
// Main Trainee Management (middleware protected)
Route::middleware(['auth', 'centre.access:trainee'])->prefix('trainees')->name('trainees.')->group(function () {
    Route::get('/', [TraineeController::class, 'index'])->name('index');
    Route::get('/create', [TraineeController::class, 'create'])->name('create');
    Route::post('/', [TraineeController::class, 'store'])->name('store');
    Route::get('/{id}', [TraineeController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [TraineeController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TraineeController::class, 'update'])->name('update');
    Route::delete('/{id}', [TraineeController::class, 'destroy'])->name('destroy');
});

// Trainee Dashboard and Profile Management
Route::middleware(['auth', 'centre.access:trainee'])->group(function () {
    Route::get('/traineeshome', [TraineeHomeController::class, 'index'])->name('traineeshome');
    
    // Profile Management
    Route::get('/traineeprofile/{id}', [TraineeProfileController::class, 'index'])->name('traineeprofile');
    Route::get('/traineeprofile/{id}/edit', [TraineeProfileController::class, 'edit'])->name('traineeprofile.edit');
    Route::put('/traineeprofile/{id}', [TraineeProfileController::class, 'update'])->name('traineeprofile.update');
    Route::post('/traineeprofile/{id}/progress', [TraineeProfileController::class, 'updateProgress'])->name('traineeprofile.progress');
    Route::post('/traineeprofile/{id}/attendance', [TraineeProfileController::class, 'recordAttendance'])->name('traineeprofile.attendance');
    Route::post('/traineeprofile/{id}/activity', [TraineeProfileController::class, 'addActivity'])->name('traineeprofile.addActivity');
    Route::get('/traineeprofile/{id}/download', [TraineeProfileController::class, 'downloadProfile'])->name('traineeprofile.download');
    Route::delete('/traineeprofile/{id}', [TraineeProfileController::class, 'destroy'])->name('traineeprofile.destroy');
    
    // Registration
    Route::get('/traineesregistrationpage', [TraineeRegistrationController::class, 'index'])->name('traineesregistrationpage');
    Route::post('/traineesregistrationstore', [TraineeRegistrationController::class, 'store'])->name('traineesregistrationstore');
});

// Role-based Trainee Access
Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('admin.trainees');
});

Route::group(['middleware' => ['role:supervisor']], function () {
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('supervisor.trainees');
});

Route::group(['middleware' => ['role:teacher']], function () {
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('teacher.trainees');
});

Route::group(['middleware' => ['role:ajk']], function () {
    Route::get('/trainees', function() { return redirect()->route('traineeshome'); })->name('ajk.trainees');
});
```

## Key Features

### 1. Comprehensive Registration System
- **Multi-step Registration**: Complete trainee onboarding with personal, guardian, and medical information
- **Document Upload**: Avatar and document upload with validation
- **Consent Management**: Photo and services consent tracking
- **Guardian Information**: Complete guardian and emergency contact management
- **Medical History**: Detailed medical condition and history recording

### 2. Role-based Access Control
- **Hierarchical Permissions**: Different access levels for admin, supervisor, teacher, and AJK
- **Centre Isolation**: Users see only trainees from their assigned centre
- **Operation Restrictions**: Create, edit, delete permissions based on role
- **Audit Trail**: Complete logging of all trainee operations

### 3. Medical Condition Management
- **Condition Categorization**: Comprehensive disability type categorization
- **Badge System**: Visual condition indicators with color coding
- **Medical History**: Detailed medical history and notes tracking
- **Consent Tracking**: Photo and services consent management

### 4. Progress Tracking & Analytics
- **Attendance Calculation**: Automatic attendance rate calculation
- **Activity Participation**: Track activity enrollment and participation
- **Progress Metrics**: Individual progress tracking and reporting
- **Statistics Dashboard**: Real-time trainee statistics and analytics

### 5. Data Validation & Security
- **Comprehensive Validation**: Multi-level validation for all inputs
- **Unique Constraints**: Email and IC number uniqueness enforcement
- **File Security**: Secure file upload with type and size validation
- **Data Protection**: GDPR-compliant data handling and consent management

## Statistical Calculations

### 1. Trainee Statistics
```php
// Basic trainee statistics
$stats = [
    'total_trainees' => Trainees::count(),
    'active_trainees' => Trainees::where('status', 'active')->count(),
    'new_this_month' => Trainees::where('created_at', '>=', now()->subMonth())->count(),
    'centres_count' => Centres::count()
];

// Role-based filtering
if (in_array($role, ['teacher', 'supervisor', 'ajk'])) {
    $userCentreId = session('centre_id');
    $stats['centre_trainees'] = Trainees::where('centre_id', $userCentreId)->count();
}
```

### 2. Attendance Rate Calculation
```php
// Attendance rate calculation
private function calculateAttendanceRate($trainee)
{
    $totalSessions = $trainee->activities->sum('total_sessions') ?? 0;
    $attendedSessions = $trainee->attendances->where('status', 'present')->count();
    
    if ($totalSessions == 0) {
        return 0;
    }
    
    return round(($attendedSessions / $totalSessions) * 100, 2);
}
```

### 3. Condition Distribution
```php
// Condition distribution analysis
$conditionStats = Trainees::selectRaw('trainee_condition, COUNT(*) as count')
    ->groupBy('trainee_condition')
    ->orderBy('count', 'desc')
    ->get();

// Age distribution
$ageStats = Trainees::selectRaw('
    CASE 
        WHEN YEAR(CURDATE()) - YEAR(trainee_date_of_birth) < 5 THEN "Under 5"
        WHEN YEAR(CURDATE()) - YEAR(trainee_date_of_birth) < 10 THEN "5-9"
        WHEN YEAR(CURDATE()) - YEAR(trainee_date_of_birth) < 15 THEN "10-14"
        WHEN YEAR(CURDATE()) - YEAR(trainee_date_of_birth) < 20 THEN "15-19"
        ELSE "20+"
    END as age_group,
    COUNT(*) as count
')->groupBy('age_group')->get();
```

## Condition Badge System

### 1. Medical Condition Categories
```php
$conditionMap = [
    'Autism Spectrum Disorder' => 'info',      // Blue badge
    'Down Syndrome' => 'primary',              // Dark blue badge
    'Cerebral Palsy' => 'warning',             // Yellow badge
    'ADHD' => 'success',                       // Green badge
    'Learning Disabilities' => 'secondary',     // Gray badge
    'Intellectual Disability' => 'danger',     // Red badge
    'Speech and Language Disorders' => 'light', // Light badge
    'Hearing Impairment' => 'secondary',       // Gray badge
    'Visual Impairment' => 'secondary',        // Gray badge
    'Physical Disability' => 'dark',           // Dark badge
    'Multiple Disabilities' => 'danger',       // Red badge
    'Others' => 'secondary'                    // Gray badge (default)
];
```

### 2. CSS Badge Classes
```css
.badge-condition {
    font-size: 85%;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 15px;
}

.badge-cerebral-palsy {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-autism {
    background-color: #d1ecf1;
    color: #0c5460;
}

.badge-down-syndrome {
    background-color: #fff3cd;
    color: #856404;
}
```

## Security Features

### 1. Authentication & Authorization
- **Session-based Auth**: Custom session-based authentication system
- **Role-based Access**: Four-tier role system with specific permissions
- **Centre-based Isolation**: Data segregation by rehabilitation centre
- **Operation Permissions**: Create, edit, delete based on user role

### 2. Data Validation & Protection
- **Input Sanitization**: Comprehensive validation for all trainee fields
- **File Upload Security**: Image validation with type, size, and content checks
- **Unique Constraints**: Email and IC number uniqueness enforcement
- **Consent Management**: GDPR-compliant consent tracking

### 3. Privacy & Compliance
- **Photo Consent**: Explicit consent for photo usage
- **Services Consent**: Consent for rehabilitation services
- **Data Anonymization**: Secure data handling and storage
- **Audit Logging**: Complete audit trail for all operations

## Integration Points

### 1. Centre Management Integration
- **Centre Assignment**: Trainees assigned to specific centres
- **Centre-based Filtering**: Access control based on centre assignment
- **Resource Allocation**: Centre capacity and resource planning
- **Staff Assignment**: Trainee-staff relationship management

### 2. Activity Management Integration
- **Activity Enrollment**: Trainee enrollment in rehabilitation activities
- **Progress Tracking**: Activity-based progress monitoring
- **Attendance Integration**: Activity attendance tracking
- **Session Management**: Individual session participation

### 3. User Management Integration
- **Staff-Trainee Relationships**: Teacher and supervisor assignments
- **Permission Integration**: Role-based access to trainee data
- **Guardian Communication**: Guardian contact management
- **Emergency Contacts**: Emergency contact integration

### 4. Assessment & Progress Integration
- **Progress Tracking**: Individual and group progress monitoring
- **Assessment Results**: Integration with assessment systems
- **Goal Setting**: Individual development plan integration
- **Reporting**: Comprehensive progress reporting

## Performance Optimization

### 1. Database Optimization
```php
// Optimized queries with proper indexing
$trainees = Trainees::with(['centre', 'activities', 'attendances'])
    ->where('centre_id', $centreId)
    ->orderBy('created_at', 'desc')
    ->paginate(12);

// Efficient statistics queries
$stats = Trainees::selectRaw('
    COUNT(*) as total,
    SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_month
', [now()->subMonth()])->first();
```

### 2. File Management
- **Optimized Uploads**: Efficient file upload with validation
- **Image Processing**: Automatic image optimization and resizing
- **Storage Management**: Organized file storage with cleanup
- **CDN Integration**: Content delivery network for static assets

### 3. Caching Strategy
- **Query Caching**: Cached trainee statistics and counts
- **Session Caching**: User-specific data caching
- **Image Caching**: Avatar and document caching
- **Search Results**: Cached search results for common queries

## Recent Updates

### 1. Enhanced Validation System
- **Field Validation**: Updated validation to match database schema
- **Error Messages**: Custom validation error messages
- **File Validation**: Enhanced file upload validation
- **Consent Validation**: Required consent validation

### 2. Avatar Management System
- **Upload Processing**: Improved avatar upload handling
- **Storage Organization**: Organized storage in trainee-avatars directory
- **Fallback System**: Gender-based default avatar system
- **Cleanup Process**: Automatic cleanup of old avatars

### 3. Medical Condition Enhancement
- **Condition Mapping**: Comprehensive condition categorization
- **Badge System**: Visual condition indicators
- **Medical History**: Enhanced medical history tracking
- **Consent Management**: Improved consent tracking system

## Future Enhancements

### 1. Advanced Features
- **Digital Signatures**: Electronic consent and document signing
- **Biometric Integration**: Fingerprint or facial recognition for attendance
- **Mobile App**: Dedicated mobile app for guardians and staff
- **QR Code System**: QR code-based trainee identification

### 2. Analytics & Reporting
- **Progress Analytics**: AI-driven progress analysis and predictions
- **Custom Reports**: User-generated custom reports
- **Data Visualization**: Advanced charts and graphs
- **Trend Analysis**: Long-term trend analysis and insights

### 3. Communication Enhancement
- **Guardian Portal**: Dedicated guardian access portal
- **Automated Notifications**: SMS and email notifications for guardians
- **Progress Reports**: Automated progress report generation
- **Communication Log**: Complete communication history tracking

### 4. Integration Expansion
- **Medical Records**: Integration with medical record systems
- **Educational Systems**: Integration with educational management systems
- **Government Databases**: Integration with government disability databases
- **Third-party APIs**: RESTful API for external integrations

This module provides comprehensive trainee management capabilities essential for rehabilitation centre operations, ensuring efficient participant tracking, progress monitoring, and family communication while maintaining strict data privacy and security standards.