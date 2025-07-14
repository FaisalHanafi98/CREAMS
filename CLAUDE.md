# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Essential Development Commands

### Local Development
```bash
# Start development server
php artisan serve

# Watch and compile frontend assets
npm run dev

# Run tests
php artisan test

# Run specific test suites
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature

# Clear application caches
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Database operations
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed
```

### Asset Compilation
```bash
# Development (Vite dev server)
npm run dev

# Production build (Vite)
npm run build

# Development with hot reload
vite --host
```

### Code Quality & Linting
```bash
# Laravel Pint for code formatting
vendor/bin/pint

# PHPUnit tests
php artisan test
```

## Architecture Overview

### Authentication System
CREAMS uses a **custom session-based multi-role authentication system** - NOT Laravel's default Auth:

- **Session Management**: Custom session handling via `app/Extensions/MultipleUserGuard.php`
- **Role-Based Access**: Four roles (`admin`, `supervisor`, `teacher`, `ajk`) managed through `app/Traits/AuthenticationTrait.php`
- **Middleware Stack**: `Role.php` and `Authenticate.php` enforce access control
- **User Models**: Unified `Users` model with separate role-specific models for extended functionality

### Database Architecture
**Centre-Centric Multi-Tenant Design:**
- Most data models are segmented by rehabilitation centres
- Foreign key relationships enforce data isolation between centres
- Primary entities: Users, Trainees, Activities, Assets, Courses
- Extensive use of Eloquent relationships and scopes

### Controller Organization
**Role-Based Feature Controllers:**
- Controllers organized by functionality (Dashboard, Activity, Trainee, etc.)
- Each user role has specific dashboard views and permissions
- Extensive logging throughout controllers for debugging and audit trails
- Pattern: Try-catch blocks with user-friendly error handling

### Frontend Structure
**Role-Based UI System:**
- Dynamic navigation based on user roles
- Bootstrap 5.3.3 + Font Awesome + jQuery 3.7.1 + Vite for asset compilation
- Role-specific CSS files in `public/css/` and `public/assets/`
- Blade template inheritance with `layouts/app.blade.php`
- Custom CSS for role-specific styling (admin, teacher, trainee, supervisor)
- Video backgrounds and interactive elements on public pages

## Key Development Patterns

### Model Conventions
```php
// Custom accessors for full names
public function getFullNameAttribute() {
    return "{$this->first_name} {$this->last_name}";
}

// Role checking methods
public function hasRole($roleName) { 
    return $this->role === $roleName; 
}

// Avatar URL handling
public function getAvatarUrlAttribute() {
    // Custom avatar logic
}
```

### Authentication Patterns
```php
// Check if user is authenticated (not Laravel's Auth facade)
if (session()->has('id') && session()->has('role')) {
    // User is authenticated
}

// Role-based access in controllers
if (session('role') !== 'admin') {
    return redirect()->route('dashboard');
}
```

### Logging Patterns
Controllers extensively use logging for debugging:
```php
Log::info('User action performed', [
    'user_id' => session('id'),
    'action' => 'specific_action',
    'data' => $data
]);
```

## File Organization Conventions

### Models (`app/Models/`)
- `Users.php` - Main user model with role field
- `Trainee.php` - Trainee-specific data and relationships
- `Activity.php` - Activity management with sessions and attendance
- Role-specific models: `Teachers.php`, `Supervisors.php`, `Admins.php`, `AJKs.php`

### Controllers (`app/Http/Controllers/`)
- Base controllers for each major feature
- Role-specific controllers for specialized functionality
- Separate folders for related controllers (Activity/, Auth/, etc.)

### Views (`resources/views/`)
- `layouts/` - Base templates (app.blade.php, header.blade.php, footer.blade.php)
- `dashboard/` - Role-specific dashboard views
- Feature-specific folders: `activities/`, `trainees/`, `messages/`

### Assets (`public/`)
- `css/` - Role and feature-specific stylesheets
- `images/` - Organized by context (Staffs/, Trainee/, User/)
- `js/` - Feature-specific JavaScript files

## Database Relationships

### Core Relationships
- **Users** ↔ **Centres** (many-to-many through pivot)
- **Trainees** → **Centres** (belongs to)
- **Activities** → **Centres** (belongs to)
- **ActivitySessions** → **Activities** (belongs to)
- **Attendance** relationships through multiple models

### Migration Patterns
- Migrations follow numbered sequence (01_, 02_, etc.)
- Foreign key constraints with proper cascade rules
- Standardized column naming (snake_case with descriptive prefixes)

## Security Considerations

### Authentication Flow
1. Login validates credentials and creates session
2. Middleware validates session on each request
3. Role middleware enforces access control
4. Controllers log actions for audit trail

### Session Structure
```php
session([
    'id' => $user->id,
    'role' => $user->role,
    'name' => $user->full_name,
    'centre_id' => $user->centre_id,
    // Additional user data
]);
```

## Testing Approach
- PHPUnit configured for Feature and Unit tests
- Test database uses array drivers for speed
- Existing tests focus on authentication and basic functionality
- Follow existing test patterns in `tests/Feature/Auth/`

## Common Development Tasks

### Adding New Features
1. Create migration if database changes needed
2. Update/create models with proper relationships
3. Create controller with appropriate middleware
4. Add routes with role-based access
5. Create views following existing layout patterns
6. Add any required assets (CSS/JS)

### Role-Based Features
1. Check existing role patterns in middleware and traits
2. Use session-based authentication (not Laravel Auth)
3. Follow logging patterns for audit trails
4. Implement proper access control at controller level

### Working with Trainees
- Primary entity in the system
- Related to activities, attendance, and centres
- Has extensive profile management features
- Follow existing patterns for registration and management

## Avatar and File Management

### Avatar Storage Patterns
- User avatars: `public/avatars/` and `storage/app/public/avatars/`
- Trainee avatars: `storage/app/public/trainee_avatars/`
- File naming: `{type}_{id}_{identifier}_{timestamp}.{ext}`
- Use symbolic links: `php artisan storage:link`

### File Upload Conventions
```php
// Avatar URL generation pattern
public function getAvatarUrlAttribute() {
    if ($this->avatar) {
        return asset('storage/avatars/' . $this->avatar);
    }
    return asset('images/default-avatar.png');
}
```

## Environment Setup

### Key Environment Variables
```bash
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creams
DB_USERNAME=root
DB_PASSWORD=

# Application
APP_NAME="CREAMS"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# Storage
FILESYSTEM_DISK=local
```

## Important Notes

- **Do NOT use Laravel's default Auth system** - this application uses custom session-based authentication
- Always check user role via session, not Auth facade
- Maintain centre-based data isolation in queries
- Follow existing logging patterns for consistency
- Use established avatar handling patterns for file uploads
- Respect the role-based access control throughout the application
- **Laravel 10.x** with **PHP 8.1+** minimum requirements
- Uses **Vite** for asset compilation (not Laravel Mix)