# CREAMS Centres Module Summary

## Overview
The Centres module manages rehabilitation centres within the CREAMS system, providing comprehensive facility management, staff assignment, resource allocation, and operational oversight. It serves as the foundational organizational unit for the entire system.

## Controllers

### 1. CentreController.php
**Purpose**: Comprehensive centre management and administration

**Key Methods**:
- `index()`: Display all centres with statistics (user count, trainee count, asset count)
- `create()/store()`: Create new centres (admin only) with full validation
- `show()`: Display detailed centre information with operational statistics
- `edit()/update()`: Update centre information with admin authorization
- `destroy()`: Delete centres with safety checks for active users/trainees
- `assets()`: Display centre-specific asset inventory and statistics

**Key Features**:
- **Admin-Only Management**: Only administrators can create, edit, or delete centres
- **Comprehensive Statistics**: Real-time calculations of staff, trainees, assets, and utilization
- **Asset Integration**: Direct access to centre-specific asset management
- **Safety Validation**: Prevents deletion of centres with active users or trainees
- **Activity Tracking**: Monitors recent activities within each centre
- **Utilization Metrics**: Calculates centre capacity utilization rates

**Statistical Calculations**:
- Total staff members assigned to centre
- Total trainees enrolled at centre
- Total assets allocated to centre
- Active sessions currently scheduled
- Utilization rate (trainees/capacity * 100)
- Recent activities and their status

## Models

### 1. Centres.php
**Purpose**: Represents rehabilitation centres and their properties

**Key Properties**:
- `centre_id`: Unique centre identifier (string primary key)
- `centre_name`: Official centre name
- `centre_address`: Physical address
- `centre_phone`: Contact phone number
- `centre_email`: Official email address
- `centre_capacity`: Maximum capacity (trainee count)
- `centre_manager`: Manager name
- `centre_manager_contact`: Manager contact information
- `centre_status`: Operational status (active, inactive, maintenance)
- `centre_description`: Detailed description
- `centre_facilities`: JSON array of available facilities
- `centre_image`: Centre photo/image
- `centre_latitude`/`centre_longitude`: GPS coordinates
- `is_active`: Active status flag

**Key Features**:
- **Non-incrementing Primary Key**: Uses custom string IDs instead of auto-increment
- **Geographic Coordinates**: GPS location storage for mapping features
- **Flexible Facilities**: JSON-based facility tracking for expandability
- **Status Management**: Multiple status levels for operational control
- **Dropdown Support**: Built-in methods for form dropdowns

**Key Relationships**:
- `users()`: Has many users (staff members assigned to centre)
- `trainees()`: Has many trainees (enrolled at centre)
- `courses()`: Has many courses (offered at centre)
- `assets()`: Has many assets (allocated to centre)
- `activities()`: Has many activities (conducted at centre)

**Key Scopes & Methods**:
- `active()`: Filter only active centres
- `getForDropdown()`: Generate dropdown options for forms
- `getDefault()`: Get default centre (Gombak - ID '01')

## Database Schema

### centres table
```sql
- id (auto-increment primary key for internal use)
- centre_id (string, unique) - Custom centre identifier
- centre_name (string) - Official centre name
- centre_address (text) - Physical address
- centre_phone (string) - Contact phone
- centre_email (string) - Official email
- centre_capacity (string) - Maximum capacity
- centre_manager (string) - Manager name
- centre_manager_contact (string) - Manager contact
- centre_status (enum: active, inactive, maintenance) - Status
- centre_description (text, nullable) - Description
- centre_facilities (json, nullable) - Available facilities
- centre_image (string, nullable) - Photo filename
- centre_latitude (decimal 10,8, nullable) - GPS latitude
- centre_longitude (decimal 11,8, nullable) - GPS longitude
- is_active (boolean, default true) - Active flag
- timestamps
```

**Indexes**:
- `centre_id` (unique index for fast lookups)
- `centre_status` (for status-based filtering)
- `is_active` (for active/inactive filtering)

## Views

### 1. centres/index.blade.php
**Purpose**: Centre listing and overview dashboard
**Features**:
- Grid or table view of all centres
- Real-time statistics for each centre (staff, trainees, assets)
- Status indicators (active, inactive, maintenance)
- Quick action buttons (view, edit, delete, manage assets)
- Centre capacity utilization indicators
- Search and filter functionality

### 2. centres/create.blade.php
**Purpose**: New centre creation form (admin only)
**Features**:
- Comprehensive centre information form
- GPS coordinate input with map integration
- Facility selection with custom additions
- Manager contact information
- Status and capacity settings
- Image upload for centre photo
- Validation with real-time feedback

### 3. centres/show.blade.php
**Purpose**: Detailed centre information display
**Features**:
- Complete centre profile display
- Operational statistics dashboard
- Staff list with roles and contact information
- Trainee enrollment summary
- Asset inventory overview
- Recent activities and sessions
- Utilization metrics and capacity tracking
- Contact information and directions

### 4. centres/edit.blade.php
**Purpose**: Centre information editing (admin only)
**Features**:
- Pre-populated form with current centre data
- All fields editable except centre_id
- Status change controls
- Facility management interface
- Manager information updates
- GPS coordinate updates with map
- Change confirmation dialogs

### 5. centres/assets.blade.php
**Purpose**: Centre-specific asset management
**Features**:
- Complete asset inventory for the centre
- Asset statistics (total, available, in-use, maintenance)
- Asset search and filtering
- Asset condition tracking
- Total value calculations
- Asset utilization reports
- Quick asset management actions

## Routes

### Web Routes (in routes/web.php)
```php
// Centre Management
Route::get('/centres', [CentreController::class, 'index'])->name('centres.index');
Route::get('/centres/create', [CentreController::class, 'create'])->name('centres.create');
Route::post('/centres', [CentreController::class, 'store'])->name('centres.store');
Route::get('/centres/{id}', [CentreController::class, 'show'])->name('centres.show');
Route::get('/centres/{id}/edit', [CentreController::class, 'edit'])->name('centres.edit');
Route::put('/centres/{id}', [CentreController::class, 'update'])->name('centres.update');
Route::delete('/centres/{id}', [CentreController::class, 'destroy'])->name('centres.destroy');

// Centre Assets
Route::get('/centres/{id}/assets', [CentreController::class, 'assets'])->name('centres.assets');
```

## Key Features

### 1. Comprehensive Centre Management
- **Full CRUD Operations**: Complete create, read, update, delete functionality
- **Admin Authorization**: All management operations restricted to administrators
- **Data Validation**: Comprehensive validation for all centre information
- **Unique Identification**: Custom string-based centre IDs for flexible naming

### 2. Operational Statistics & Analytics
- **Real-time Metrics**: Live calculation of staff, trainee, and asset counts
- **Utilization Tracking**: Capacity utilization rate monitoring
- **Activity Monitoring**: Recent activity tracking and status reporting
- **Performance Analytics**: Centre performance metrics and comparisons

### 3. Geographic & Facility Management
- **GPS Integration**: Latitude/longitude storage for mapping features
- **Facility Tracking**: JSON-based facility management for flexibility
- **Image Management**: Centre photo upload and display
- **Location Services**: Address management with geographic coordinates

### 4. Integration Hub
- **Staff Assignment**: Central hub for user-centre assignments
- **Trainee Management**: Centre-based trainee enrollment and tracking
- **Asset Allocation**: Centre-specific asset inventory management
- **Activity Coordination**: Centre-based activity scheduling and management

### 5. Multi-Status Management
- **Operational Status**: Active, inactive, maintenance status tracking
- **Active Flag**: Simple active/inactive toggle for quick filtering
- **Status Transitions**: Controlled status changes with validation
- **Status-based Access**: Features enabled/disabled based on status

## Security Features

### 1. Administrative Control
- **Admin-Only Management**: Centre creation, editing, and deletion restricted to admins
- **Permission Validation**: Every management action validates user role
- **Audit Logging**: All centre changes logged with user attribution
- **Safe Deletion**: Prevents deletion of centres with active users or trainees

### 2. Data Integrity
- **Unique Constraints**: Centre IDs and names must be unique
- **Referential Integrity**: Foreign key relationships maintained
- **Validation Rules**: Comprehensive input validation for all fields
- **Status Consistency**: Status changes validated for operational consistency

### 3. Access Control
- **Role-based Access**: Different access levels based on user roles
- **Centre-based Isolation**: Users typically see only their assigned centre
- **Operation Authorization**: Each operation checks user permissions
- **Resource Protection**: Centre resources protected from unauthorized access

## Integration Points

### 1. User Management Integration
- **Staff Assignment**: Users assigned to specific centres via centre_id
- **Role-based Access**: User permissions often centre-specific
- **Session Management**: User sessions track current centre context
- **Profile Integration**: User profiles link to centre information

### 2. Trainee Management Integration
- **Enrollment Management**: Trainees enrolled at specific centres
- **Capacity Tracking**: Trainee counts tracked against centre capacity
- **Progress Monitoring**: Trainee progress tracked per centre
- **Resource Allocation**: Centre resources allocated based on trainee needs

### 3. Asset Management Integration
- **Asset Allocation**: Assets assigned to specific centres
- **Inventory Tracking**: Centre-specific asset inventories
- **Utilization Monitoring**: Asset usage tracked per centre
- **Maintenance Scheduling**: Centre-based asset maintenance

### 4. Activity Management Integration
- **Activity Scheduling**: Activities scheduled at specific centres
- **Resource Coordination**: Centre resources coordinated for activities
- **Attendance Tracking**: Activity attendance tracked per centre
- **Performance Analytics**: Activity outcomes analyzed per centre

## Usage Patterns

### 1. Centre Creation Process
1. Admin accesses centre creation form
2. Enters comprehensive centre information
3. Sets GPS coordinates and uploads photo
4. Configures facilities and capacity
5. Assigns manager and contact information
6. System validates and creates centre
7. Centre becomes available for assignments

### 2. Daily Operations Monitoring
1. Staff access centre dashboard
2. Review operational statistics
3. Monitor capacity utilization
4. Check recent activities
5. Manage asset assignments
6. Coordinate resource allocation
7. Update status as needed

### 3. Centre Maintenance Process
1. Admin changes centre status to "maintenance"
2. System restricts new enrollments/activities
3. Existing activities handled based on policy
4. Maintenance work conducted
5. Admin changes status back to "active"
6. Normal operations resume

### 4. Resource Management Flow
1. Admin reviews centre resource needs
2. Allocates staff, assets, and facilities
3. Monitors utilization and capacity
4. Adjusts allocations based on demand
5. Tracks performance metrics
6. Reports on centre efficiency

## Statistical Calculations

### 1. Utilization Metrics
```php
// Capacity utilization rate
$utilizationRate = ($centre->trainees_count / $centre->capacity) * 100;

// Asset utilization
$assetUtilization = ($activeAssets / $totalAssets) * 100;

// Staff-to-trainee ratio
$staffRatio = $centre->users_count / max($centre->trainees_count, 1);
```

### 2. Activity Metrics
```php
// Active sessions count
$activeSessions = DB::table('activity_sessions')
    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
    ->where('activities.centre_id', $centreId)
    ->where('activity_sessions.status', 'scheduled')
    ->count();

// Recent activities
$recentActivities = DB::table('activities')
    ->where('centre_id', $centreId)
    ->where('is_active', true)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();
```

## Recent Updates

### 1. Enhanced Statistics
- **Real-time Calculations**: Improved performance for statistical queries
- **Utilization Tracking**: Added comprehensive utilization metrics
- **Asset Integration**: Enhanced asset tracking and management
- **Activity Monitoring**: Improved activity and session tracking

### 2. Geographic Features
- **GPS Coordinates**: Added latitude/longitude storage
- **Mapping Integration**: Support for map-based centre location
- **Address Validation**: Enhanced address formatting and validation
- **Location Services**: Preparation for location-based features

### 3. Facility Management
- **JSON Facilities**: Flexible facility tracking system
- **Custom Facilities**: Support for centre-specific facilities
- **Facility Search**: Enhanced facility-based search and filtering
- **Capacity Planning**: Improved capacity management tools

## Dependencies

### 1. Laravel Packages
- **Eloquent ORM**: Database relationships and queries
- **Laravel Validation**: Input validation and rules
- **Laravel Storage**: File storage for centre images
- **Carbon**: Date/time handling for statistics

### 2. Frontend Dependencies
- **Bootstrap**: UI framework and components
- **jQuery**: JavaScript functionality and AJAX
- **Mapping Libraries**: GPS coordinate input and display
- **Chart Libraries**: Statistical visualization

### 3. System Dependencies
- **MySQL/MariaDB**: Database storage
- **PHP 8.1+**: Core runtime requirements
- **GD Extension**: Image processing for centre photos
- **JSON Support**: Facility data storage

## Known Issues & Solutions

### 1. Primary Key Complexity
- **Issue**: Non-incrementing string primary key causes some ORM complications
- **Solution**: Careful relationship definitions with explicit foreign keys
- **Status**: Managed with proper model configuration

### 2. Statistical Performance
- **Issue**: Real-time statistics can be slow with large datasets
- **Solution**: Optimized queries with proper indexing and caching
- **Status**: Improved with query optimization

### 3. Centre Isolation
- **Issue**: Ensuring proper centre-based data isolation
- **Solution**: Comprehensive scope implementation and validation
- **Status**: Addressed with enhanced access control

## Future Enhancements

### 1. Advanced Analytics
- **Performance Dashboards**: Comprehensive centre performance analytics
- **Comparative Analysis**: Cross-centre performance comparisons
- **Predictive Analytics**: Capacity planning and resource optimization
- **Trend Analysis**: Long-term trend identification and reporting

### 2. Geographic Features
- **Interactive Mapping**: Full map integration with centre locations
- **Distance Calculations**: Travel time and distance calculations
- **Route Planning**: Optimal routing between centres
- **Geofencing**: Location-based notifications and features

### 3. Resource Optimization
- **Capacity Planning**: AI-driven capacity optimization
- **Resource Sharing**: Inter-centre resource sharing capabilities
- **Demand Forecasting**: Predictive demand analysis
- **Efficiency Metrics**: Advanced efficiency measurement tools

### 4. Integration Expansion
- **External Systems**: Integration with external facility management systems
- **IoT Integration**: Smart building and IoT device integration
- **Mobile Applications**: Mobile centre management capabilities
- **API Development**: RESTful API for external integrations

This module provides the foundational organizational structure for the CREAMS system, enabling comprehensive facility management, operational oversight, and resource coordination across all rehabilitation centres.