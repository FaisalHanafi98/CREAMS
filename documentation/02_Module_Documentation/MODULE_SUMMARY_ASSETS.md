# CREAMS Assets Module Summary

## Overview
The Assets module provides comprehensive asset and equipment management for rehabilitation centres, including inventory tracking, maintenance scheduling, movement monitoring, and detailed analytics. It manages the complete lifecycle of physical assets from acquisition to disposal.

## Controllers

### 1. AssetController.php
**Purpose**: Comprehensive asset management with full CRUD operations and advanced features

**Key Methods**:
- `index()`: Asset listing with search, filtering, and statistics
- `create()/store()`: Asset creation with validation and auto-ID generation
- `show()`: Detailed asset view with history and maintenance schedule
- `edit()/update()`: Asset modification with audit trail
- `destroy()`: Asset deletion with safety checks
- `reports()`: Comprehensive asset analytics and reporting
- `getReportData()`: AJAX endpoint for filtered report data
- `exportReports()`: Export functionality (CSV/PDF)
- `maintenance()`: Maintenance scheduling and tracking
- `scheduleMaintenance()`: Schedule maintenance tasks
- `completeMaintenance()`: Mark maintenance as completed
- `rescheduleMaintenance()`: Reschedule maintenance tasks
- `movements()`: Asset movement and transfer tracking
- `recordMovement()`: Record asset movements
- `approveMovement()`: Approve asset transfers
- `getAssetsJson()`: API endpoint for asset data

**Key Features**:
- **Admin-Only Management**: Asset creation, editing, and deletion restricted to administrators
- **Advanced Search**: Multi-field search across name, model, brand, serial number
- **Comprehensive Filtering**: Filter by type, centre, status, condition
- **Asset Analytics**: Detailed statistics and utilization metrics
- **Maintenance Management**: Complete maintenance lifecycle tracking
- **Movement Tracking**: Asset transfer and location history
- **Report Generation**: Customizable reports with export functionality
- **Mock Data Generation**: Sample data generation for testing and development

## Models

### 1. Asset.php
**Purpose**: Represents physical assets and equipment

**Key Properties**:
- `asset_id`: Unique asset identifier (string primary key)
- `asset_name`: Asset name/title
- `asset_description`: Detailed description
- `asset_type_id`: Asset type/category reference
- `asset_model`: Model number/specification
- `asset_brand`: Manufacturer/brand name
- `asset_serial_number`: Serial number for tracking
- `asset_value`: Purchase/current value (decimal)
- `purchase_date`: Date of purchase
- `supplier`: Supplier/vendor information
- `warranty_info`: Warranty details
- `asset_condition`: Physical condition (excellent, good, fair, poor, damaged)
- `asset_status`: Current status (available, in_use, maintenance, damaged, disposed)
- `asset_location`: Current physical location
- `centre_id`: Associated rehabilitation centre
- `assigned_to`: Current user assignment
- `maintenance_notes`: Maintenance history notes
- `last_maintenance_date`: Last maintenance date
- `next_maintenance_date`: Scheduled next maintenance
- `asset_image`: Asset photo/image
- `asset_attributes`: JSON array for custom attributes

**Key Features**:
- **Financial Tracking**: Precise value tracking with decimal support
- **Condition Management**: Multi-level condition tracking
- **Status Lifecycle**: Complete status management from acquisition to disposal
- **Maintenance Scheduling**: Built-in maintenance date tracking
- **Custom Attributes**: Flexible JSON-based attribute system
- **Location Tracking**: Detailed location and assignment tracking

**Key Relationships**:
- `centre()`: Belongs to rehabilitation centre
- Implicit relationships with maintenance records, movement history

**Key Scopes & Methods**:
- `search()`: Multi-field search functionality
- `forCentre()`: Filter by centre
- `byStatus()`: Filter by asset status
- `byCondition()`: Filter by asset condition
- `getFormattedValueAttribute()`: Formatted currency display
- `getImageUrlAttribute()`: Asset image URL with fallback

## Database Schema

### assets table
```sql
- id (auto-increment primary key)
- asset_id (string, unique) - Custom asset identifier
- asset_name (string) - Asset name
- asset_description (text, nullable) - Detailed description
- asset_type_id (foreign key) - Asset type reference
- asset_model (string, nullable) - Model information
- asset_brand (string, nullable) - Brand/manufacturer
- asset_serial_number (string, nullable) - Serial number
- asset_value (decimal 10,2, nullable) - Financial value
- purchase_date (date, nullable) - Purchase date
- supplier (string, nullable) - Supplier information
- warranty_info (text, nullable) - Warranty details
- asset_condition (enum: excellent, good, fair, poor, damaged) - Condition
- asset_status (enum: available, in_use, maintenance, damaged, disposed) - Status
- asset_location (string) - Current location
- centre_id (string) - Associated centre
- assigned_to (foreign key, nullable) - Current assignment
- maintenance_notes (text, nullable) - Maintenance history
- last_maintenance_date (date, nullable) - Last maintenance
- next_maintenance_date (date, nullable) - Next maintenance
- asset_image (string, nullable) - Image filename
- asset_attributes (json, nullable) - Custom attributes
- timestamps
```

**Indexes**:
- `asset_id` (unique index for fast lookups)
- `asset_type_id` (for type-based filtering)
- `asset_status` (for status-based queries)
- `centre_id` (for centre-based filtering)
- `assigned_to` (for assignment tracking)

## Views

### 1. assets/index.blade.php
**Purpose**: Main asset listing and management interface
**Features**:
- Comprehensive asset table with sorting and pagination
- Advanced search and filtering capabilities
- Real-time statistics dashboard (total assets, types, centres, quantity, value)
- Quick action buttons (view, edit, delete, maintain)
- Status and condition indicators
- Centre-based filtering
- Asset type categorization

### 2. assets/create.blade.php
**Purpose**: Asset creation form (admin only)
**Features**:
- Comprehensive asset information form
- Auto-generated unique asset ID
- Asset type and centre selection
- Financial information (value, supplier, warranty)
- Image upload functionality
- Custom attributes management
- Validation with real-time feedback

### 3. assets/reports.blade.php
**Purpose**: Asset analytics and reporting dashboard
**Features**:
- Comprehensive analytics dashboard
- Interactive charts and visualizations
- Utilization rate calculations
- Centre-wise asset distribution
- Asset type breakdown
- Value analysis and trends
- Export functionality (CSV/PDF)
- Customizable date ranges and filters
- High-value asset tracking

### 4. assets/maintenance.blade.php
**Purpose**: Maintenance scheduling and tracking
**Features**:
- Maintenance schedule overview
- Task status tracking (overdue, due soon, scheduled, completed)
- Priority-based organization
- Maintenance type management (routine, preventive, corrective, inspection)
- Calendar integration
- Technician assignment
- Cost tracking and estimation
- Maintenance history

### 5. assets/movements.blade.php
**Purpose**: Asset movement and transfer tracking
**Features**:
- Movement history tracking
- Transfer request management
- Location change monitoring
- Approval workflow for movements
- Movement type categorization (transfer, relocation, assignment, return)
- Real-time status updates
- Movement statistics and analytics

## Routes

### Web Routes (in routes/web.php)
```php
// Asset Management
Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
Route::post('/assets', [AssetController::class, 'store'])->name('assets.store');
Route::get('/assets/{id}', [AssetController::class, 'show'])->name('assets.show');
Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

// Asset Reports & Analytics
Route::get('/assets/reports/analytics', [AssetController::class, 'reports'])->name('assets.reports');
Route::get('/assets/reports/data', [AssetController::class, 'getReportData'])->name('assets.reports.data');
Route::post('/assets/reports/export', [AssetController::class, 'exportReports'])->name('assets.reports.export');

// Maintenance Management
Route::get('/assets/maintenance/schedule', [AssetController::class, 'maintenance'])->name('assets.maintenance');
Route::post('/assets/maintenance/schedule', [AssetController::class, 'scheduleMaintenance'])->name('assets.maintenance.schedule');
Route::patch('/assets/maintenance/{id}/complete', [AssetController::class, 'completeMaintenance'])->name('assets.maintenance.complete');
Route::patch('/assets/maintenance/{id}/reschedule', [AssetController::class, 'rescheduleMaintenance'])->name('assets.maintenance.reschedule');
Route::get('/assets/maintenance/filter', [AssetController::class, 'filterMaintenance'])->name('assets.maintenance.filter');

// Movement & Transfer Management
Route::get('/assets/movements/history', [AssetController::class, 'movements'])->name('assets.movements');
Route::post('/assets/movements/record', [AssetController::class, 'recordMovement'])->name('assets.movements.record');
Route::patch('/assets/movements/{id}/approve', [AssetController::class, 'approveMovement'])->name('assets.movements.approve');
Route::get('/assets/movements/filter', [AssetController::class, 'filterMovements'])->name('assets.movements.filter');

// API Endpoints
Route::get('/api/assets', [AssetController::class, 'getAssetsJson'])->name('api.assets');
```

## Key Features

### 1. Comprehensive Asset Management
- **Full Lifecycle Tracking**: From acquisition to disposal
- **Detailed Asset Information**: Complete asset specifications and metadata
- **Financial Management**: Value tracking, depreciation, cost analysis
- **Multi-Centre Support**: Assets distributed across multiple centres

### 2. Advanced Analytics & Reporting
- **Utilization Metrics**: Asset utilization rate calculations
- **Financial Analytics**: Value distribution and cost analysis
- **Performance Tracking**: Asset performance and efficiency metrics
- **Custom Reports**: Configurable reports with multiple export formats

### 3. Maintenance Management System
- **Preventive Maintenance**: Scheduled maintenance planning
- **Maintenance History**: Complete maintenance record tracking
- **Priority Management**: Priority-based maintenance scheduling
- **Cost Tracking**: Maintenance cost estimation and tracking
- **Technician Assignment**: Maintenance task assignment system

### 4. Movement & Transfer Tracking
- **Location Monitoring**: Real-time asset location tracking
- **Transfer Management**: Asset transfer between centres/locations
- **Approval Workflow**: Multi-level approval for asset movements
- **History Tracking**: Complete movement history with audit trail

### 5. Status & Condition Management
- **Multi-Status System**: Available, in-use, maintenance, damaged, disposed
- **Condition Tracking**: Excellent, good, fair, poor, damaged conditions
- **Automated Workflows**: Status changes trigger appropriate actions
- **Alert System**: Notifications for maintenance due, damaged assets

## Statistical Calculations

### 1. Asset Analytics
```php
// Basic asset statistics
$stats = [
    'total' => Asset::count(),
    'types' => Asset::distinct('asset_type_id')->count(),
    'centres' => Asset::distinct('centre_id')->count(),
    'total_value' => Asset::sum('asset_value')
];

// Utilization calculations
$utilizationRate = Asset::where('asset_status', 'in_use')->count() / 
                   Asset::count() * 100;
```

### 2. Maintenance Metrics
```php
// Maintenance statistics
$maintenanceStats = [
    'overdue' => Asset::where('next_maintenance_date', '<', now())->count(),
    'due_soon' => Asset::whereBetween('next_maintenance_date', [
        now(), now()->addDays(7)
    ])->count(),
    'scheduled' => Asset::where('next_maintenance_date', '>', now()->addDays(7))->count()
];
```

### 3. Financial Analytics
```php
// Value analytics
$valueAnalytics = [
    'total_value' => Asset::sum('asset_value'),
    'avg_value' => Asset::avg('asset_value'),
    'centre_values' => Asset::selectRaw('centre_id, SUM(asset_value) as total')
        ->groupBy('centre_id')->get(),
    'type_values' => Asset::selectRaw('asset_type_id, SUM(asset_value) as total')
        ->groupBy('asset_type_id')->get()
];
```

## Mock Data Generation

### 1. Maintenance Schedule Generation
The system includes comprehensive mock data generation for:
- **Maintenance Tasks**: Various types with different priorities and statuses
- **Due Dates**: Realistic scheduling based on maintenance type
- **Status Distribution**: Balanced distribution of maintenance statuses
- **Asset Assignment**: Logical assignment to different asset types

### 2. Movement History Generation
- **Movement Types**: Transfer, relocation, assignment, return
- **Location Mapping**: Realistic centre and room assignments
- **Timeline Generation**: Chronological movement history
- **User Attribution**: Assignment to appropriate staff members

### 3. Asset Type Categorization
```php
$assetTypes = [
    'Computer Equipment', 'Furniture', 'Medical Equipment',
    'Sports Equipment', 'Office Supplies', 'Vehicles',
    'Tools', 'Electronics', 'Kitchen Equipment', 'Safety Equipment'
];
```

## Security Features

### 1. Administrative Control
- **Admin-Only Operations**: Asset creation, editing, deletion restricted to admins
- **Role-based Maintenance**: Maintenance scheduling requires admin/supervisor roles
- **Movement Approval**: Asset movements require appropriate authorization
- **Audit Logging**: All asset operations logged with user attribution

### 2. Data Validation
- **Input Sanitization**: Comprehensive validation for all asset fields
- **Financial Validation**: Proper validation for monetary values
- **Date Validation**: Logical date validation for maintenance scheduling
- **Status Consistency**: Validates status transitions for logical consistency

### 3. Access Control
- **Centre-based Access**: Users typically see assets from their assigned centre
- **Permission Validation**: Each operation validates user permissions
- **Resource Protection**: Asset resources protected from unauthorized access
- **API Security**: API endpoints include proper authentication and authorization

## Integration Points

### 1. Centre Management Integration
- **Centre Assignment**: Assets assigned to specific centres
- **Location Tracking**: Detailed location within centres
- **Resource Allocation**: Centre-based resource planning
- **Capacity Planning**: Asset availability for centre operations

### 2. User Management Integration
- **Asset Assignment**: Assets assigned to specific users
- **Responsibility Tracking**: User responsibility for assigned assets
- **Permission-based Access**: Asset access based on user roles
- **Activity Logging**: User actions tracked for audit purposes

### 3. Maintenance System Integration
- **Scheduled Maintenance**: Integration with maintenance scheduling
- **Work Order Management**: Maintenance work order generation
- **Technician Assignment**: Assignment to maintenance staff
- **Cost Tracking**: Maintenance cost integration with financial systems

### 4. Activity Management Integration
- **Resource Requirements**: Assets required for specific activities
- **Availability Checking**: Asset availability for activity scheduling
- **Equipment Reservation**: Asset booking for activities
- **Usage Tracking**: Asset usage in rehabilitation activities

## Performance Optimization

### 1. Database Optimization
```php
// Optimized queries with proper indexing
$assets = Asset::with(['centre'])
    ->where('asset_status', 'available')
    ->orderBy('asset_name')
    ->paginate(20);

// Efficient statistical queries
$stats = Asset::selectRaw('
    COUNT(*) as total,
    COUNT(DISTINCT asset_type_id) as types,
    SUM(asset_value) as total_value
')->first();
```

### 2. Caching Strategy
- **Statistics Caching**: Asset statistics cached for improved performance
- **Report Caching**: Generated reports cached for repeated access
- **Search Results**: Search results cached for common queries
- **Image Caching**: Asset images cached for faster loading

### 3. Frontend Optimization
- **Lazy Loading**: Asset images and detailed data loaded on demand
- **AJAX Updates**: Real-time updates without page refresh
- **Pagination**: Efficient pagination for large asset lists
- **Search Debouncing**: Debounced search to reduce server load

## Recent Updates

### 1. Enhanced Asset Model
- **Simplified Structure**: Streamlined asset model to match actual database schema
- **Improved Relationships**: Better defined relationships with centres and users
- **Custom Attributes**: JSON-based custom attribute system
- **Financial Tracking**: Enhanced financial value tracking

### 2. Maintenance System
- **Mock Data Generation**: Comprehensive mock maintenance data
- **Priority Management**: Priority-based maintenance scheduling
- **Status Tracking**: Complete maintenance status lifecycle
- **Cost Estimation**: Maintenance cost tracking and estimation

### 3. Reporting Enhancements
- **Interactive Charts**: Enhanced chart visualizations
- **Export Functionality**: Multiple export formats (CSV, PDF)
- **Custom Filters**: Advanced filtering options for reports
- **Real-time Analytics**: Live updating of asset analytics

## Future Enhancements

### 1. Advanced Features
- **RFID Integration**: RFID-based asset tracking
- **Barcode Scanning**: Mobile barcode scanning for asset management
- **IoT Integration**: Internet of Things device integration for real-time monitoring
- **Depreciation Calculation**: Automated asset depreciation calculations

### 2. Mobile Applications
- **Mobile Asset Scanning**: Mobile app for asset scanning and tracking
- **Maintenance Mobile App**: Mobile maintenance management
- **Offline Capability**: Offline asset tracking with synchronization
- **Push Notifications**: Mobile notifications for maintenance and alerts

### 3. Integration Expansion
- **ERP Integration**: Integration with enterprise resource planning systems
- **Financial System Integration**: Connection with accounting systems
- **Procurement Integration**: Integration with procurement and purchasing systems
- **External APIs**: RESTful API for third-party integrations

### 4. Analytics & AI
- **Predictive Maintenance**: AI-powered predictive maintenance scheduling
- **Usage Analytics**: Advanced asset usage pattern analysis
- **Cost Optimization**: AI-driven cost optimization recommendations
- **Lifecycle Management**: Intelligent asset lifecycle management

This module provides comprehensive asset management capabilities essential for rehabilitation centre operations, ensuring efficient resource utilization, proper maintenance scheduling, and detailed financial tracking of all physical assets.