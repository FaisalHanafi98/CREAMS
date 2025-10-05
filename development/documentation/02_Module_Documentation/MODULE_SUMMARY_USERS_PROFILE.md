# CREAMS Users/Profile Module Summary

## Overview
The Users/Profile module manages user accounts, authentication, role-based access control, and personal profile management within the CREAMS system. It handles user creation, updates, password management, avatar uploads, and maintains a comprehensive audit trail.

## Controllers

### 1. UserController.php
**Purpose**: Administrative user management with role-based hierarchy

**Key Methods**:
- `index()`: List users based on role hierarchy (Admin sees all, Supervisor sees AJK/Teachers, etc.)
- `create()/store()`: Create new users with role validation and hierarchy enforcement
- `show()`: Display user details with audit history
- `edit()/update()`: Update user information with change tracking
- `destroy()`: Delete users with permission checks
- `resetPassword()`: Administrative password reset functionality
- `changeStatus()`: Change user status (active/inactive/suspended)

**Key Features**:
- **Role Hierarchy System**: 4-level hierarchy (Admin > Supervisor > AJK > Teacher)
- **Permission Validation**: Users can only manage roles below their hierarchy level
- **Comprehensive Audit Logging**: All actions logged with user attribution
- **Input Validation**: Strict validation for IIUM ID format, email uniqueness, password complexity
- **Change Tracking**: Monitors and logs all modifications with before/after values

**Role Hierarchy**:
```php
private $roleHierarchy = [
    'admin' => 4,        // Can manage all roles
    'supervisor' => 3,   // Can manage AJK and Teacher
    'ajk' => 2,         // Can manage Teacher only
    'teacher' => 1      // Cannot manage other users
];
```

### 2. UserProfileController.php
**Purpose**: Personal profile management for individual users

**Key Methods**:
- `showProfile()`: Display user's personal profile with letter integration for admins
- `updateProfile()`: Update personal information (name, email, contact info, education)
- `changePassword()`: Secure password change with current password verification
- `uploadAvatar()`: Profile photo upload with file validation and storage management

**Key Features**:
- **Comprehensive Profile Data**: Personal info, education, specializations, contact details
- **Password Security**: Strong password requirements (8+ chars, uppercase, lowercase, numbers, special chars)
- **Avatar Management**: Secure file upload with automatic cleanup of old avatars
- **Session Integration**: Updates session data when profile changes
- **Letter Integration**: Admin users can manage letter templates and view recent letters
- **Dual Update Strategy**: Uses both Eloquent and direct DB queries for reliability

**Profile Fields**:
- Basic Info: Name, email, phone, address, date of birth
- Education: Level, specialization, teaching specialization, position
- System Fields: Avatar, about/bio, status, centre assignment
- Audit Fields: Last accessed, updated by, review notes

## Models

### 1. Users.php
**Purpose**: Core user model extending Laravel's Authenticatable

**Key Properties**:
- `iium_id`: Unique IIUM identifier (format: AAAA1234)
- `name`: Full name
- `email`: Unique email address
- `password`: Hashed password
- `phone`: Contact phone number
- `address`: Physical address
- `position`: Job position/title
- `education_level`: Education level (Diploma, Bachelor's, Master's, PhD)
- `education_specialization`: Field of study
- `teaching_specialization`: Teaching expertise area
- `avatar`: Profile photo filename
- `about`: Personal biography/description
- `date_of_birth`: Date of birth
- `role`: User role (admin, supervisor, teacher, ajk)
- `status`: Account status (active, inactive, pending, suspended)
- `centre_id`: Associated rehabilitation centre
- `centre_location`: Centre location reference
- `user_last_accessed_at`: Last login timestamp
- `review`: Performance review notes
- `updated_by`: User who last updated the record

**Key Features**:
- **Role-based Methods**: hasRole(), isAdmin(), isSupervisor(), isTeacher(), isAJK()
- **Education Formatting**: Computed attributes for education display
- **Avatar URL Generation**: Automatic URL generation for profile photos
- **Secure Updates**: Dedicated methods for status, role, and centre changes
- **Relationship Management**: Links to activities, trainees, assets, centres
- **Auto-formatting**: Automatic uppercase conversion for IIUM IDs
- **Audit Logging**: Automatic logging of create/update events

**Key Relationships**:
- `centre()`: Belongs to rehabilitation centre
- `centres()`: Manages centres (for admin/supervisor)
- `activities()`: Created activities
- `trainees()`: Managed trainees
- `assets()`: Managed assets
- `classes()`: Taught classes (for teachers)
- `courses()`: Associated courses
- `events()`: Organized events (for AJK)
- `notifications()`: User notifications

**Security Features**:
- **Guarded Fields**: Password, status, role, centre_id require explicit authorization
- **Hidden Fields**: Password and remember_token hidden in serialization
- **Secure Methods**: updateStatus(), updateRole(), assignToCentre() with admin verification
- **Input Validation**: Built-in validation for sensitive field changes

## Database Schema

### users table
```sql
- id (primary key)
- iium_id (string, unique) - IIUM identifier
- name (string) - Full name
- email (string, unique) - Email address
- email_verified_at (timestamp, nullable) - Email verification
- password (string) - Hashed password
- phone (string, nullable) - Contact phone
- address (text, nullable) - Physical address
- position (string, nullable) - Job position
- education_level (string, nullable) - Education level
- education_specialization (string, nullable) - Field of study
- teaching_specialization (string, nullable) - Teaching expertise
- avatar (string, nullable) - Profile photo filename
- about (text, nullable) - Biography/description
- date_of_birth (date, nullable) - Date of birth
- role (enum: admin, supervisor, teacher, ajk) - User role
- status (enum: active, inactive, pending, suspended) - Account status
- centre_id (string, nullable) - Associated centre
- centre_location (string, nullable) - Centre location
- user_last_accessed_at (timestamp, nullable) - Last login
- review (text, nullable) - Performance review
- updated_by (foreign key, nullable) - Last updater
- remember_token - Laravel auth token
- timestamps
```

**Indexes**:
- `role, status` (composite index for role-based queries)
- `centre_id` (for centre-based filtering)
- `iium_id` (unique constraint)
- `email` (unique constraint)

## Views

### 1. profile.blade.php
**Purpose**: Personal profile management interface
**Features**:
- Tabbed interface for different profile sections
- Personal Information tab with contact details
- Education Information tab with qualifications
- Password Change tab with security requirements
- Avatar Upload tab with image preview
- Letter Management tab (admin only)
- Real-time validation and feedback

### 2. users/index.blade.php
**Purpose**: Administrative user listing
**Features**:
- Role-based user listings (separate sections for each role)
- Search and filter functionality
- Quick actions (view, edit, delete, reset password)
- User status indicators
- Permission-based action visibility

### 3. users/create.blade.php
**Purpose**: New user creation form
**Features**:
- Comprehensive user information form
- Role selection based on current user's permissions
- IIUM ID validation with format checking
- Password strength requirements
- Centre assignment dropdown
- Real-time validation feedback

### 4. users/show.blade.php
**Purpose**: Individual user profile display
**Features**:
- Complete user information display
- Audit history showing all changes
- Action buttons for edit, delete, password reset
- Status change controls
- Permission-based access controls

### 5. users/edit.blade.php
**Purpose**: User information editing
**Features**:
- Pre-populated form with current user data
- Field-level validation
- Change tracking and confirmation
- Permission validation before updates
- Cancel/save options with change preview

## Routes

### Web Routes (in routes/web.php)
```php
// Profile Management
Route::get('/profile', [UserProfileController::class, 'showProfile'])->name('profile');
Route::post('/profile/update', [UserProfileController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/change-password', [UserProfileController::class, 'changePassword'])->name('profile.change-password');
Route::post('/profile/upload-avatar', [UserProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');

// User Management (Admin/Supervisor)
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{role}/{id}', [UserController::class, 'show'])->name('users.show');
Route::get('/users/{role}/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{role}/{id}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{role}/{id}', [UserController::class, 'destroy'])->name('users.destroy');

// Administrative Actions
Route::post('/users/{role}/{id}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
Route::post('/users/{role}/{id}/change-status', [UserController::class, 'changeStatus'])->name('users.change-status');
```

## Key Features

### 1. Role-Based Access Control
- **Hierarchical Permissions**: 4-level role hierarchy with cascading permissions
- **Action Authorization**: Each action validates user's permission level
- **Centre Isolation**: Users can only manage within their assigned centre
- **Resource Filtering**: Data filtered based on user role and permissions

### 2. Comprehensive User Management
- **CRUD Operations**: Full create, read, update, delete functionality
- **Bulk Operations**: Mass user actions for administrators
- **Status Management**: Active/inactive/pending/suspended status tracking
- **Role Assignment**: Secure role changes with audit trails

### 3. Secure Authentication & Profiles
- **Strong Password Requirements**: 8+ characters with mixed case, numbers, special chars
- **Avatar Management**: Secure file upload with automatic cleanup
- **Session Integration**: Profile changes immediately update session data
- **Dual Update Strategy**: Reliable database updates with fallback mechanisms

### 4. Audit & Compliance
- **Complete Audit Trail**: All user actions logged with timestamps and attributions
- **Change Tracking**: Before/after values for all modifications
- **Access Logging**: Login times and activity tracking
- **Compliance Reports**: Audit history accessible for compliance purposes

### 5. Education & Professional Data
- **Education Levels**: Diploma, Bachelor's, Master's, PhD tracking
- **Specializations**: Both education and teaching specialization fields
- **Position Tracking**: Job titles and responsibilities
- **Professional Development**: Review notes and performance tracking

### 6. Integration Features
- **Centre Management**: Full integration with rehabilitation centres
- **Activity Assignment**: Links users to activities and teaching assignments
- **Asset Management**: Connects users to managed assets and resources
- **Notification System**: Integrated notification delivery and management

## Security Features

### 1. Authentication Security
- **Password Hashing**: Laravel's built-in password hashing
- **Session Management**: Secure session handling with timeout
- **Remember Token**: Secure "remember me" functionality
- **Password Strength**: Enforced complexity requirements

### 2. Authorization & Access Control
- **Role-based Permissions**: Granular permission system
- **Hierarchical Access**: Users can only manage lower-hierarchy roles
- **Resource Ownership**: Users can only modify their own profiles
- **Administrative Override**: Secure admin override capabilities

### 3. Data Protection
- **Input Validation**: Comprehensive validation on all inputs
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Prevention**: Proper output escaping in views
- **File Upload Security**: Validated file types, sizes, and storage

### 4. Audit & Monitoring
- **Action Logging**: All user actions logged with context
- **Change Detection**: Automatic change tracking and logging
- **IP Tracking**: Request IP addresses logged for audit
- **User Agent Tracking**: Browser/device information logged

## Recent Updates

### 1. Enhanced Profile Management
- **Education Fields**: Added comprehensive education and specialization tracking
- **Professional Data**: Enhanced position and qualification management
- **Avatar System**: Improved avatar upload with better error handling
- **Session Sync**: Better synchronization between database and session data

### 2. Security Enhancements
- **Password Requirements**: Strengthened password complexity requirements
- **Audit Improvements**: Enhanced audit logging with more detailed tracking
- **Permission Validation**: Improved role-based access control validation
- **File Security**: Better file upload validation and storage security

### 3. User Experience Improvements
- **Form Validation**: Enhanced real-time validation feedback
- **Error Handling**: Better error messages and recovery options
- **Profile Interface**: Improved tabbed interface for profile management
- **Administrative Tools**: Enhanced user management interface for admins

## Dependencies

### 1. Laravel Packages
- **Laravel Authentication**: Built-in authentication system
- **Laravel Storage**: File storage and management
- **Laravel Validation**: Form and input validation
- **Carbon**: Date/time handling and formatting

### 2. Frontend Dependencies
- **Bootstrap**: UI framework and components
- **jQuery**: JavaScript functionality and AJAX
- **File Upload Libraries**: Avatar upload and preview functionality
- **Form Validation**: Client-side validation enhancement

### 3. System Dependencies
- **PHP 8.1+**: Core runtime requirements
- **MySQL/MariaDB**: Database storage
- **GD Extension**: Image processing for avatars
- **File System**: Storage permissions for avatar uploads

## Usage Patterns

### 1. User Registration Flow
1. Admin/Supervisor accesses user creation form
2. Enters user details including IIUM ID and role
3. System validates permissions and data format
4. Creates user account with "pending" status
5. User receives login credentials
6. First login prompts profile completion
7. Admin activates account after verification

### 2. Profile Management Flow
1. User accesses personal profile page
2. Updates personal information as needed
3. Changes password with current password verification
4. Uploads profile photo with automatic resizing
5. System updates session data immediately
6. All changes logged for audit purposes

### 3. Administrative User Management
1. Admin views user list filtered by role hierarchy
2. Selects user for modification
3. Updates user information or status
4. System validates permissions and logs changes
5. User receives notification of changes
6. Audit trail updated with admin attribution

### 4. Role Assignment Process
1. Admin identifies user requiring role change
2. Validates new role assignment permissions
3. Updates user role with audit logging
4. System adjusts user's access permissions
5. User notified of role change
6. New permissions take effect immediately

## Known Issues & Solutions

### 1. Session Synchronization
- **Issue**: Profile changes not immediately reflected in session
- **Solution**: Dual update strategy updating both database and session
- **Status**: Resolved with enhanced session management

### 2. Avatar Upload Reliability
- **Issue**: File upload failures in certain server configurations
- **Solution**: Multiple upload strategies with fallback mechanisms
- **Status**: Improved with enhanced error handling

### 3. Permission Edge Cases
- **Issue**: Complex permission scenarios in role hierarchy
- **Solution**: Comprehensive permission validation matrix
- **Status**: Addressed with detailed permission checking

### 4. Audit Log Performance
- **Issue**: Audit logging causing performance impact
- **Solution**: Optimized logging with background processing
- **Status**: Improved with selective logging strategies

## Future Enhancements

### 1. Advanced User Management
- **Bulk Operations**: Mass user import/export capabilities
- **Advanced Search**: Complex search and filtering options
- **User Templates**: Predefined user role templates
- **Automated Workflows**: Approval workflows for user changes

### 2. Enhanced Security
- **Two-Factor Authentication**: SMS/email-based 2FA
- **Single Sign-On**: Integration with external authentication systems
- **Advanced Audit**: Enhanced audit reporting and analysis
- **Security Monitoring**: Real-time security event detection

### 3. Profile Enhancements
- **Social Features**: User connections and messaging
- **Skill Tracking**: Detailed skill and competency management
- **Calendar Integration**: Personal calendar and scheduling
- **Document Management**: Personal document storage and sharing

### 4. Integration Improvements
- **HR System Integration**: Connection with external HR systems
- **Learning Management**: Integration with LMS platforms
- **Mobile Applications**: Native mobile app support
- **API Expansion**: RESTful API for external integrations

This module provides a robust foundation for user management within the CREAMS system, supporting comprehensive user lifecycle management, secure authentication, detailed profile management, and full audit compliance with role-based access control.