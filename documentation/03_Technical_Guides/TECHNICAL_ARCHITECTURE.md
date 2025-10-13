# CREAMS Technical Architecture Documentation

## System Overview
Community-based REhAbilitation Management System (CREAMS) is a Laravel 10+ web application designed to manage rehabilitation activities, trainees, and administrative tasks for rehabilitation centers.

## Technology Stack
- **Framework**: Laravel 10.x
- **PHP Version**: 8.1+
- **Database**: MySQL 8.0+
- **Frontend**: Blade Templates, Bootstrap 5, jQuery, Font Awesome
- **PDF Generation**: DomPDF
- **Authentication**: Custom session-based system (not Laravel Auth)

## Database Schema Overview

### Core Tables
1. **users** - System users (admin, supervisor, teacher, ajk)
2. **trainees** - Rehabilitation program participants
3. **activities** - Rehabilitation activities/programs
4. **activity_sessions** - Scheduled activity sessions
5. **activity_enrollments** - Trainee enrollment in activities
6. **centres** - Rehabilitation centers
7. **categories** - Activity categories
8. **assets** - Equipment and resource management
9. **letter_templates** - Letter generation templates
10. **letters** - Generated letters archive

### Authentication Tables
- **password_reset_tokens** (Laravel 10+) or **password_resets** (Legacy)
- **remember_tokens** embedded in users table

### Support Tables
- **notifications** - System notifications
- **volunteers** - Volunteer management
- **contacts** - Contact form submissions

## Authentication System
CREAMS uses a **custom session-based authentication system**, NOT Laravel's built-in Auth.

### Session Variables
```php
session('id')        // Current user ID
session('role')      // User role: admin, supervisor, teacher, ajk
session('name')      // User display name
session('centre_id') // User's assigned center
```

### Role Hierarchy
1. **admin** - Full system access
2. **supervisor** - Center management, user oversight
3. **teacher** - Activity management, trainee interaction
4. **ajk** - Limited access, basic functions

## API Endpoints

### Authentication Routes
- `POST /auth/check` - Login processing
- `GET /logout` - Logout
- `POST /forgot-password` - Password reset request
- `POST /reset-password` - Password reset processing

### Core Module Routes
- `GET /dashboard` - Role-based dashboard
- `GET /profile` - User profile management
- `GET /activities` - Activity management
- `GET /trainees` - Trainee management
- `GET /centres` - Center management
- `GET /assets` - Asset management

### Letter Generation Routes
- `POST /profile/letter-generate` - Generate new letter
- `GET /letters-archive` - Letter archive view
- `GET /profile/letter-download/{id}` - Download letter PDF

## Module Dependencies

### Dashboard Module
- Depends on: Activities, Trainees, Users, Notifications
- Services: AdminDashboardService, UserDashboardService
- Statistics: Active sessions, user counts, recent activities

### Trainee Module
- Depends on: Activities (enrollments), Centres
- Features: Registration, profile management, progress tracking
- Relationships: Many-to-many with activities via enrollments

### Activity Module
- Depends on: Categories, Centres, Users (teachers)
- Features: Scheduling, session management, enrollment
- Relationships: Has many sessions, many trainees via enrollments

### Letter Module
- Depends on: Users, Letter Templates
- Features: PDF generation, template management, archive
- Technology: DomPDF with custom templates

## Database Relationships

### User Relationships
```
users -> trainees (created_by)
users -> activities (assigned teacher)
users -> centres (assigned center)
users -> letters (created_by)
```

### Activity Relationships
```
activities -> categories (belongs to)
activities -> centres (belongs to)
activities -> activity_sessions (has many)
activities -> trainees (many-to-many via enrollments)
```

### Center Relationships
```
centres -> users (has many)
centres -> activities (has many)
centres -> trainees (has many)
centres -> assets (has many)
```

## Security Implementation

### Role-Based Access Control
- Middleware: `role:admin,supervisor,teacher,ajk`
- Center isolation: Users only see data from their assigned center
- Route protection: All routes require authentication

### Data Isolation
```php
// Non-admin users only see their center's data
if (session('role') !== 'admin') {
    $query->where('centre_id', session('centre_id'));
}
```

### Input Validation
- Form requests for all user inputs
- CSRF protection on all forms
- File upload restrictions (images only, 2MB max)

## Performance Considerations

### Database Optimization
- Foreign key constraints properly defined
- Indexes on frequently queried columns
- Eager loading for relationships

### Caching Strategy
- Route caching: `php artisan route:cache`
- View caching: `php artisan view:cache`
- Config caching: `php artisan config:cache`

### PDF Generation Performance
- Image optimization for letterheads
- Template caching for frequently used letters
- Async generation for large documents

## File Structure

### Controllers
- `MainController` - Authentication and basic routes
- `DashboardController` - Role-based dashboards
- `ActivityController` - Activity management
- `TraineeController` - Trainee management
- `LetterTemplateController` - Letter generation

### Models
- Follow Laravel conventions
- Use fillable arrays for mass assignment protection
- Implement proper relationships
- Custom authentication (no User model extends Authenticatable)

### Views
- Blade templates with consistent layout
- Modular components for reusability
- Bootstrap 5 for responsive design
- Font Awesome for icons

## Environment Configuration

### Required Environment Variables
```
APP_NAME=CREAMS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creams
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

## Known Issues and Limitations

### Current Schema Issues
1. Table name mismatch: `password_reset_tokens` vs `password_resets`
2. Column name mismatches in various tables
3. Missing soft deletes in some tables
4. Notification table schema inconsistencies

### Performance Issues
1. PDF generation timeout (2+ minutes)
2. Slow preview generation
3. Large file handling in letter templates

### Missing Features
1. Real-time notifications
2. API authentication tokens
3. Advanced reporting features
4. Mobile responsiveness optimization

## Migration History

### Initial Setup
- Core tables creation (2024-01-01 series)
- Foreign key constraints
- Basic seed data

### Recent Updates
- Letter generation system
- Direct generation fix implementation
- Archive functionality

## Monitoring and Logging

### Laravel Logs
- Location: `storage/logs/laravel.log`
- Rotation: Daily
- Level: Error and above in production

### Database Monitoring
- Query logging available in debug mode
- Performance monitoring needed for production

### Error Tracking
- Laravel's built-in error handling
- Custom error pages for user-friendly messages

This architecture supports a multi-center rehabilitation management system with role-based access control and comprehensive activity tracking.