# CREAMS Activities Module Summary

## Overview
The Activities module is a comprehensive system for managing rehabilitation and educational activities, including scheduling, enrollment, session management, and attendance tracking. It supports multiple activity types, instructor assignment, and role-based access control.

## Controllers

### 1. ActivityController.php
**Purpose**: Main controller for activity management, scheduling, and enrollment

**Key Methods**:
- `index()`: List activities with role-based filtering and statistics
- `categories()`: Display activity categories for rehabilitation module
- `categoryShow()`: Show activities for a specific category
- `create()/store()`: Create new activities with validation
- `show()`: Display detailed activity information with statistics
- `edit()/update()`: Edit activity details
- `destroy()`: Delete activities with safety checks
- `sessions()`: Manage activity sessions
- `createSession()`: Create new sessions for activities
- `markAttendance()`: Display attendance marking interface
- `storeAttendance()`: Process attendance records
- `manageEnrollments()`: Manage session enrollments
- `addEnrollment()`: Add trainees to sessions
- `schedule()`: Activity schedule management
- `weeklySchedule()`: Display weekly schedule overview
- `teacherSchedule()`: Show teacher's personal schedule
- `enrollmentForm()`: Display enrollment form
- `enrollTrainees()`: Process bulk trainee enrollments
- `storeSchedule()`: Create recurring schedules

**Key Features**:
- **Role-based Access**: Different permissions for admin, supervisor, teacher, AJK
- **Conflict Detection**: Checks for scheduling conflicts (teacher, room, time)
- **Attendance Management**: Comprehensive attendance tracking with participation scores
- **Session Management**: Create and manage individual activity sessions
- **Enrollment System**: Bulk and individual trainee enrollment
- **Statistics**: Activity performance metrics and analytics
- **API Endpoints**: RESTful API for external integrations

### 2. ActivitySessionController.php
**Purpose**: Handles individual activity session operations

**Key Methods**:
- Session creation and management
- Attendance tracking for sessions
- Session-specific enrollment management
- Session status updates (scheduled, ongoing, completed, cancelled)

### 3. Activity/AttendanceController.php
**Purpose**: Specialized controller for attendance operations

**Key Methods**:
- Advanced attendance marking
- Attendance reports and analytics
- Participation score tracking
- Progress notes management

## Models

### 1. Activity.php
**Purpose**: Represents individual activities

**Key Properties**:
- `activity_id`: Unique identifier for the activity
- `activity_name`: Name of the activity
- `activity_description`: Detailed description
- `activity_type`: Type (Individual, Group, Both, Education, Therapy, Training)
- `activity_date`: Scheduled date
- `activity_start_time`/`activity_end_time`: Time slots
- `activity_location`: Venue location
- `max_participants`/`current_participants`: Capacity management
- `activity_goals`/`activity_outcomes`: Learning objectives
- `required_resources`: JSON array of needed resources
- `activity_status`: Status (scheduled, ongoing, completed, cancelled)
- `centre_id`: Associated rehabilitation centre
- `category_id`: Activity category
- `created_by`: Creator user ID
- `instructor_id`: Assigned instructor

**Key Features**:
- **Time Management**: Automatic duration calculation and conflict detection
- **Enrollment Control**: Capacity management and enrollment validation
- **Status Tracking**: Comprehensive activity lifecycle management
- **Conflict Prevention**: Built-in time and instructor conflict checking

**Key Relationships**:
- `centre()`: Belongs to rehabilitation centre
- `category()`: Belongs to activity category
- `creator()`/`instructor()`: Links to Users model
- `enrollments()`: Has many activity enrollments
- `sessions()`: Has many activity sessions
- `trainees()`: Many-to-many with trainees through enrollments

**Key Scopes**:
- `active()`: Active activities only
- `ongoing()`: Currently running activities
- `today()`: Today's activities
- `currentlyRunning()`: Real-time activity status
- `forCentre()`/`forInstructor()`: Filtered by centre/instructor

### 2. ActivitySession.php
**Purpose**: Represents individual sessions within activities

**Key Properties**:
- `session_id`: Unique session identifier
- `activity_id`: Parent activity
- `session_name`: Session title
- `session_description`: Session details
- `session_date`: Date of session
- `session_start_time`/`session_end_time`: Session timing
- `session_location`: Session venue
- `max_participants`/`current_participants`: Session capacity
- `session_objectives`: Learning objectives for session
- `session_notes`: Instructor notes
- `session_materials`: JSON array of required materials
- `session_status`: Status tracking
- `instructor_id`: Assigned instructor

**Key Features**:
- **Individual Session Management**: Each session can have different settings
- **Material Tracking**: JSON-based material requirements
- **Instructor Assignment**: Flexible instructor assignment per session
- **Status Management**: Independent status tracking for each session

### 3. ActivityEnrollment.php
**Purpose**: Manages trainee enrollments in activities

**Key Properties**:
- `activity_id`: Enrolled activity
- `trainee_id`: Enrolled trainee
- `enrollment_date`: Date of enrollment
- `start_date`: When participation begins
- `end_date`: When participation ends (nullable)
- `status`: Enrollment status (enrolled, completed, dropped, suspended)
- `goals`: Individual learning goals
- `progress_notes`: Progress tracking
- `enrolled_by`: Who enrolled the trainee

**Key Features**:
- **Individual Goal Setting**: Personalized learning objectives
- **Progress Tracking**: Detailed progress notes and monitoring
- **Status Management**: Comprehensive enrollment lifecycle
- **Audit Trail**: Tracks who enrolled each trainee

### 4. ActivityAttendance.php
**Purpose**: Tracks attendance records for activity sessions

**Key Properties**:
- `session_id`: Associated session
- `trainee_id`: Attendee
- `attendance_status`: Status (present, absent, late, excused)
- `check_in_time`: Actual arrival time
- `check_out_time`: Departure time
- `participation_score`: Performance rating (0-10)
- `progress_notes`: Session-specific notes
- `marked_by`: Who recorded attendance

**Key Features**:
- **Flexible Attendance States**: Multiple attendance status options
- **Participation Scoring**: Quantitative performance tracking
- **Time Tracking**: Precise check-in/check-out times
- **Progress Documentation**: Detailed session notes

### 5. ActivitySchedule.php
**Purpose**: Manages recurring activity schedules

**Key Properties**:
- `activity_id`: Associated activity
- `day_of_week`: Scheduled day
- `start_time`/`end_time`: Time slots
- `location`: Venue
- `room`: Specific room
- `recurring`: Recurrence pattern (weekly, biweekly, monthly, one_time)
- `start_date`/`end_date`: Schedule validity period
- `max_capacity`: Session capacity
- `status`: Schedule status (active, inactive, suspended)

**Key Features**:
- **Recurring Schedules**: Flexible recurrence patterns
- **Room Management**: Specific room assignments
- **Capacity Override**: Per-schedule capacity settings
- **Date Ranges**: Flexible schedule validity periods

## Database Schema

### activities table
```sql
- id (primary key)
- activity_id (string, unique)
- activity_name (string)
- activity_description (text)
- activity_type (string)
- activity_date (date)
- activity_start_time (time)
- activity_end_time (time)
- activity_location (string)
- max_participants (integer, default 20)
- current_participants (integer, default 0)
- activity_goals (text, nullable)
- activity_outcomes (text, nullable)
- activity_image (string, nullable)
- required_resources (json, nullable)
- activity_status (enum: scheduled, ongoing, completed, cancelled)
- centre_id (string)
- category_id (foreign key, nullable)
- created_by (foreign key)
- instructor_id (foreign key, nullable)
- timestamps
```

### activity_sessions table
```sql
- id (primary key)
- session_id (string, unique)
- activity_id (foreign key)
- session_name (string)
- session_description (text, nullable)
- session_date (date)
- session_start_time (time)
- session_end_time (time)
- session_location (string)
- max_participants (integer, default 20)
- current_participants (integer, default 0)
- session_objectives (text, nullable)
- session_notes (text, nullable)
- session_materials (json, nullable)
- session_status (enum: scheduled, ongoing, completed, cancelled)
- instructor_id (foreign key)
- timestamps
```

### activity_enrollments table
```sql
- id (primary key)
- activity_id (foreign key)
- trainee_id (foreign key)
- enrollment_date (date)
- start_date (date)
- end_date (date, nullable)
- status (enum: enrolled, completed, dropped, suspended)
- goals (text, nullable)
- progress_notes (text, nullable)
- enrolled_by (foreign key)
- timestamps
```

### activity_schedules table
```sql
- id (primary key)
- activity_id (foreign key)
- day_of_week (enum: Monday-Sunday)
- start_time (time)
- end_time (time)
- location (string, nullable)
- room (string, nullable)
- recurring (enum: weekly, biweekly, monthly, one_time)
- start_date (date, nullable)
- end_date (date, nullable)
- max_capacity (integer, nullable)
- status (enum: active, inactive, suspended)
- timestamps
```

## Views

### 1. activities/index.blade.php
**Purpose**: Main activities listing page
**Features**:
- Paginated activity list with role-based filtering
- Search and filter functionality
- Activity statistics dashboard
- Quick action buttons (create, edit, view)
- Category-based organization

### 2. activities/create.blade.php
**Purpose**: Activity creation form
**Features**:
- Comprehensive activity details form
- Resource requirements specification
- Instructor assignment
- Conflict detection warnings
- Category selection

### 3. activities/show.blade.php
**Purpose**: Detailed activity view
**Features**:
- Complete activity information display
- Enrolled trainees list
- Session schedule overview
- Performance statistics
- Action buttons for management

### 4. activities/edit.blade.php
**Purpose**: Activity editing form
**Features**:
- Pre-populated form with existing data
- Update validation
- Status change options
- Enrollment impact warnings

### 5. activities/sessions.blade.php
**Purpose**: Session management interface
**Features**:
- List of all sessions for an activity
- Session creation form
- Attendance tracking links
- Session status management
- Enrollment management per session

### 6. activities/attendance.blade.php
**Purpose**: Attendance marking interface
**Features**:
- Enrolled trainees list
- Attendance status selection (present, absent, late, excused)
- Participation score input (0-10 scale)
- Progress notes for each trainee
- Bulk attendance operations

### 7. activities/enrollments.blade.php
**Purpose**: Enrollment management
**Features**:
- Current enrollments display
- Available trainees list
- Bulk enrollment functionality
- Individual enrollment management
- Capacity monitoring

### 8. activities/schedule.blade.php
**Purpose**: Activity scheduling interface
**Features**:
- Recurring schedule creation
- Conflict detection and warnings
- Weekly schedule overview
- Room and resource management
- Schedule modification tools

### 9. activities/activitiesteacherschedule.blade.php
**Purpose**: Teacher's personal schedule view
**Features**:
- Weekly schedule layout
- Personal activity assignments
- Time conflict indicators
- Session preparation reminders
- Quick access to attendance marking

## Routes

### Web Routes (in routes/web.php)
```php
// Main Activity Routes
Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');
Route::get('/activities/{id}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
Route::put('/activities/{id}', [ActivityController::class, 'update'])->name('activities.update');
Route::delete('/activities/{id}', [ActivityController::class, 'destroy'])->name('activities.destroy');

// Session Management
Route::get('/activities/{id}/sessions', [ActivityController::class, 'sessions'])->name('activities.sessions');
Route::post('/activities/{id}/sessions', [ActivityController::class, 'createSession'])->name('activities.sessions.create');

// Attendance Management
Route::get('/activities/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'markAttendance'])->name('activities.attendance.mark');
Route::post('/activities/{activityId}/sessions/{sessionId}/attendance', [ActivityController::class, 'storeAttendance'])->name('activities.attendance.store');

// Enrollment Management
Route::get('/activities/{activityId}/sessions/{sessionId}/enrollments', [ActivityController::class, 'manageEnrollments'])->name('activities.enrollments');
Route::post('/activities/{activityId}/sessions/{sessionId}/enrollments', [ActivityController::class, 'addEnrollment'])->name('activities.enrollments.add');

// Schedule Management
Route::get('/activities/{id}/schedule', [ActivityController::class, 'schedule'])->name('activities.schedule');
Route::post('/activities/{id}/schedule', [ActivityController::class, 'storeSchedule'])->name('activities.schedule.store');
Route::get('/activities/schedule/weekly', [ActivityController::class, 'weeklySchedule'])->name('activities.schedule.weekly');
Route::get('/teachers/{teacherId}/schedule', [ActivityController::class, 'teacherSchedule'])->name('activities.teacher.schedule');

// Enrollment Forms
Route::get('/activities/{id}/enroll', [ActivityController::class, 'enrollmentForm'])->name('activities.enroll');
Route::post('/activities/{id}/enroll', [ActivityController::class, 'enrollTrainees'])->name('activities.enroll.store');

// Rehabilitation Module Routes
Route::get('/rehabilitation/categories', [ActivityController::class, 'categories'])->name('rehabilitation.categories');
Route::get('/rehabilitation/categories/{slug}', [ActivityController::class, 'categoryShow'])->name('rehabilitation.category.show');

// API Routes
Route::get('/api/activities', [ActivityController::class, 'apiIndex'])->name('api.activities.index');
Route::get('/api/activities/categories', [ActivityController::class, 'getCategories'])->name('api.activities.categories');
Route::post('/api/activities/conflicts', [ActivityController::class, 'apiCheckConflicts'])->name('api.activities.conflicts');
```

## Key Features

### 1. Role-Based Access Control
- **Admin**: Full access to all activities across centres
- **Supervisor**: Manage activities within their centre
- **Teacher**: Access to assigned activities and sessions
- **AJK**: View-only access to centre activities
- **Trainee/Parent**: Limited access to enrolled activities

### 2. Comprehensive Scheduling System
- **Conflict Detection**: Automatic detection of teacher, room, and time conflicts
- **Recurring Schedules**: Support for weekly, biweekly, monthly patterns
- **Break Time Validation**: Ensures minimum 15-minute breaks between sessions
- **Workload Limits**: Maximum 8 hours per day per teacher
- **Room Management**: Tracks room availability and conflicts

### 3. Advanced Enrollment Management
- **Bulk Enrollment**: Add multiple trainees simultaneously
- **Capacity Management**: Automatic capacity tracking and enforcement
- **Individual Goals**: Personalized learning objectives per trainee
- **Status Tracking**: Comprehensive enrollment lifecycle management
- **Waiting Lists**: Handles over-capacity enrollment requests

### 4. Detailed Attendance Tracking
- **Multiple Status Types**: Present, absent, late, excused
- **Participation Scoring**: 0-10 scale performance tracking
- **Progress Notes**: Session-specific trainee progress documentation
- **Time Tracking**: Precise check-in/check-out timestamps
- **Attendance Analytics**: Statistical analysis and reporting

### 5. Activity Categories and Types
- **Rehabilitation Categories**: Physical therapy, occupational therapy, speech therapy, etc.
- **Educational Categories**: Mathematics, literacy, science, computer skills
- **Activity Types**: Individual, group, both, education, therapy, training
- **Custom Categories**: Flexible category system with custom attributes

### 6. Resource Management
- **Required Resources**: JSON-based resource specification
- **Material Tracking**: Per-session material requirements
- **Equipment Scheduling**: Resource conflict detection
- **Inventory Integration**: Links to asset management system

### 7. Performance Analytics
- **Activity Statistics**: Participation rates, completion rates, outcomes
- **Teacher Performance**: Session load, attendance rates, outcomes
- **Trainee Progress**: Individual progress tracking and analytics
- **Centre Metrics**: Cross-centre performance comparisons

### 8. Integration Features
- **Centre Integration**: Full integration with centre management
- **User System**: Seamless integration with CREAMS user roles
- **Asset Management**: Links to equipment and resource tracking
- **Reporting System**: Integration with comprehensive reporting module

## Security Features

### 1. Access Control
- **Role-based Permissions**: Granular permission system
- **Centre Isolation**: Users can only access their centre's data
- **Instructor Validation**: Teachers can only manage assigned sessions
- **Enrollment Verification**: Prevents unauthorized enrollments

### 2. Data Validation
- **Input Sanitization**: Comprehensive form validation
- **Conflict Prevention**: Automatic scheduling conflict detection
- **Capacity Enforcement**: Prevents over-enrollment
- **Time Validation**: Ensures logical time sequences

### 3. Audit Trail
- **Activity Logging**: Comprehensive action logging
- **Change Tracking**: Tracks all modifications with user attribution
- **Attendance Records**: Immutable attendance history
- **Enrollment History**: Complete enrollment lifecycle tracking

## Recent Updates

### 1. Enhanced Scheduling
- **Conflict Detection**: Improved teacher and room conflict detection
- **Recurring Schedules**: Added support for flexible recurrence patterns
- **Break Time Validation**: Ensures adequate breaks between sessions
- **Workload Management**: Daily and weekly workload limits

### 2. Improved Enrollment System
- **Bulk Operations**: Streamlined bulk enrollment process
- **Capacity Management**: Real-time capacity tracking
- **Individual Goals**: Personalized learning objective setting
- **Status Management**: Enhanced enrollment status tracking

### 3. Advanced Attendance
- **Participation Scoring**: Quantitative performance tracking
- **Progress Documentation**: Detailed session-specific notes
- **Time Tracking**: Precise attendance time logging
- **Analytics Integration**: Statistical analysis and reporting

## Dependencies

### 1. Laravel Packages
- **Carbon**: Advanced date/time handling
- **Collection**: Data manipulation and filtering
- **Eloquent**: Database ORM and relationships

### 2. Frontend Dependencies
- **Bootstrap**: UI framework and components
- **jQuery**: JavaScript functionality and AJAX
- **Chart.js**: Statistics and analytics visualization
- **FullCalendar**: Schedule and calendar views

### 3. System Dependencies
- **MySQL/MariaDB**: Primary database storage
- **PHP 8.1+**: Core runtime environment
- **Laravel 10+**: Framework requirements

## Usage Patterns

### 1. Activity Creation Flow
1. Admin/Supervisor creates new activity
2. Sets basic details (name, description, type, timing)
3. Assigns category and instructor
4. Sets capacity and resource requirements
5. Creates recurring schedule (optional)
6. System validates for conflicts
7. Activity becomes available for enrollment

### 2. Enrollment Process
1. Staff accesses activity enrollment form
2. Selects trainees from available list
3. Sets individual goals (optional)
4. System checks capacity and conflicts
5. Creates enrollment records
6. Updates activity participant count
7. Trainees can attend scheduled sessions

### 3. Session Management
1. Instructor accesses assigned sessions
2. Reviews enrolled trainees list
3. Marks attendance during/after session
4. Records participation scores
5. Adds progress notes
6. Updates session status
7. System generates attendance reports

### 4. Schedule Management
1. Create recurring activity schedules
2. System checks for teacher/room conflicts
3. Generates individual session instances
4. Handles schedule modifications
5. Manages cancellations and rescheduling
6. Updates all stakeholders automatically

## Known Issues & Solutions

### 1. Performance Optimization
- **N+1 Query Prevention**: Eager loading relationships
- **Caching Strategy**: Cache frequently accessed data
- **Database Indexing**: Optimized indexes for common queries
- **Pagination**: Efficient pagination for large datasets

### 2. Conflict Resolution
- **Scheduling Conflicts**: Comprehensive conflict detection
- **Capacity Overflows**: Real-time capacity monitoring
- **Resource Conflicts**: Equipment and room availability checking
- **Time Validation**: Logical time sequence enforcement

### 3. Data Integrity
- **Foreign Key Constraints**: Maintain referential integrity
- **Transaction Management**: Atomic operations for critical processes
- **Validation Rules**: Comprehensive input validation
- **Error Handling**: Graceful error recovery

## Future Enhancements

### 1. Advanced Scheduling
- **AI-Powered Scheduling**: Automatic optimal schedule generation
- **Predictive Conflicts**: Machine learning-based conflict prediction
- **Resource Optimization**: Intelligent resource allocation
- **Multi-centre Coordination**: Cross-centre activity coordination

### 2. Enhanced Analytics
- **Predictive Analytics**: Outcome prediction and optimization
- **Performance Dashboards**: Real-time performance monitoring
- **Trend Analysis**: Long-term trend identification
- **Comparative Analysis**: Benchmark against best practices

### 3. Mobile Integration
- **Mobile Attendance**: Smartphone-based attendance marking
- **Push Notifications**: Real-time updates and reminders
- **Offline Capability**: Offline attendance and sync
- **Parent Portal**: Mobile access for parents

### 4. External Integrations
- **Calendar Systems**: Integration with external calendar applications
- **LMS Integration**: Learning management system connectivity
- **Health Systems**: Integration with health monitoring systems
- **Reporting Tools**: Advanced reporting and analytics platforms

This module provides a comprehensive foundation for activity management within the CREAMS system, supporting the full lifecycle of rehabilitation and educational activities from creation through completion, with robust scheduling, enrollment, and attendance tracking capabilities.