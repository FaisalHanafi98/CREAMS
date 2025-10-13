# CREAMS Complete Setup Guide
# Community-based REhAbilitation Management System
# Platform: Windows & macOS Compatible
# Last Updated: 2025-07-08

===============================================================================
## 🚀 QUICK START OVERVIEW
===============================================================================

**CREAMS** is a comprehensive rehabilitation center management system built with Laravel 10.x and PHP 8.1+. This guide will walk you through the complete setup process from scratch, including all dependencies, database setup, seeding, and user credentials.

**What You'll Have After Setup:**
- Fully functional CREAMS system running locally
- Pre-seeded demo data (users, trainees, activities, centres)
- Complete access to all modules and features
- Ready-to-use login credentials for all roles

**Estimated Setup Time:** 30-45 minutes

===============================================================================
## 📋 SYSTEM REQUIREMENTS
===============================================================================

### Minimum Requirements
- **PHP**: 8.1 or higher (8.2+ recommended)
- **Node.js**: 18.x or higher (20.x recommended)
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Memory**: 4GB RAM minimum (8GB recommended)
- **Storage**: 2GB free space

### Required PHP Extensions
```bash
php-curl, php-dom, php-gd, php-json, php-mbstring, php-mysql, 
php-openssl, php-tokenizer, php-xml, php-zip, php-bcmath, php-fileinfo
```

### Development Tools Needed
- **Composer** (PHP dependency manager)
- **Git** (version control)
- **Code Editor** (VS Code, PHPStorm, etc.)

===============================================================================
## 🔧 STEP 1: INSTALL CORE DEPENDENCIES
===============================================================================

### For Windows Users

#### Install PHP 8.1+
1. **Option A: XAMPP (Recommended for Beginners)**
   ```
   • Download XAMPP 8.1+ from https://www.apachefriends.org/
   • Install and start Apache and MySQL services
   • PHP will be available at C:\xampp\php\php.exe
   ```

2. **Option A: Laragon (Recommended for Laravel)**
   ```
   • Download Laragon from https://laragon.org/
   • Install and start services
   • Includes PHP, MySQL, Node.js automatically
   ```

3. **Option C: Manual Installation**
   ```
   • Download PHP from https://windows.php.net/download/
   • Add PHP to system PATH
   • Install MySQL separately
   ```

#### Install Composer
```powershell
# Download and run Composer installer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# Add to PATH or use globally
move composer.phar C:\Windows\System32\composer.bat
```

#### Install Node.js
```
• Download Node.js LTS from https://nodejs.org/
• Run installer with default settings
• Verify: node --version && npm --version
```

#### Install Git
```
• Download Git from https://git-scm.com/download/win
• Install with default settings
• Verify: git --version
```

### For macOS Users

#### Install Homebrew (if not already installed)
```bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
```

#### Install PHP 8.1+
```bash
# Install PHP via Homebrew
brew install php@8.2
brew link php@8.2 --force

# Verify installation
php --version
```

#### Install MySQL
```bash
# Install MySQL
brew install mysql

# Start MySQL service
brew services start mysql

# Secure installation (optional but recommended)
mysql_secure_installation
```

#### Install Composer
```bash
# Download and install Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify installation
composer --version
```

#### Install Node.js
```bash
# Install Node.js via Homebrew
brew install node

# Verify installation
node --version
npm --version
```

#### Install Git (usually pre-installed)
```bash
# Check if Git is installed
git --version

# If not installed
brew install git
```

===============================================================================
## 📁 STEP 2: CLONE AND SETUP CREAMS
===============================================================================

### Download CREAMS Project
```bash
# Navigate to your development directory
cd ~/Documents  # macOS
cd C:\xampp\htdocs  # Windows with XAMPP
cd C:\laragon\www   # Windows with Laragon

# Clone the repository
git clone https://github.com/YOUR_USERNAME/CREAMS.git
cd CREAMS

# Verify you're in the correct directory
ls -la  # macOS/Linux
dir     # Windows
```

### Install PHP Dependencies
```bash
# Install Laravel dependencies
composer install

# This will install:
# - Laravel Framework 10.x
# - All required PHP packages
# - Development tools (PHPUnit, Pint, etc.)
```

### Install Frontend Dependencies
```bash
# Install Node.js dependencies
npm install

# This will install:
# - Bootstrap 5.3.3
# - jQuery 3.7.1
# - Vite build tools
# - Alpine.js
```

===============================================================================
## 🗄️ STEP 3: DATABASE SETUP
===============================================================================

### Create Database

#### Using MySQL Command Line
```bash
# Connect to MySQL
mysql -u root -p

# Create database
CREATE DATABASE creams CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Create dedicated user (recommended)
CREATE USER 'creams_user'@'localhost' IDENTIFIED BY 'secure_password_123';
GRANT ALL PRIVILEGES ON creams.* TO 'creams_user'@'localhost';
FLUSH PRIVILEGES;

# Exit MySQL
EXIT;
```

#### Using phpMyAdmin (XAMPP Users)
```
1. Open http://localhost/phpmyadmin
2. Click "New" to create database
3. Name: "creams"
4. Collation: utf8mb4_unicode_ci
5. Click "Create"
```

#### Using Laragon Database Manager
```
1. Open Laragon > Menu > MySQL > phpMyAdmin
2. Follow same steps as phpMyAdmin above
```

### Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Edit .env Configuration
Open `.env` file in your text editor and configure:

```env
# Application Settings
APP_NAME="CREAMS"
APP_ENV=local
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=creams
DB_USERNAME=creams_user
DB_PASSWORD=secure_password_123

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache Configuration
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Run Database Migrations
```bash
# Run all database migrations
php artisan migrate

# This will create all tables:
# - users, centres, trainees, activities
# - activity_sessions, enrollments, assets
# - letters, notifications, and more
```

===============================================================================
## 🌱 STEP 4: SEED DATABASE WITH DEMO DATA
===============================================================================

### Run Database Seeders
```bash
# Seed database with comprehensive demo data
php artisan db:seed

# This will create:
# - Multiple rehabilitation centres across Malaysia
# - Users with all roles (admin, supervisor, teacher, ajk)
# - 200+ realistic trainee profiles
# - 100+ activities across all categories
# - Session schedules and enrollment data
# - Asset inventory and maintenance records
```

### Alternative: Fresh Migration + Seeding
```bash
# Reset database and reseed (if needed)
php artisan migrate:fresh --seed
```

### Verify Seeding Success
```bash
# Check seeded data
php artisan tinker

# In Tinker console:
App\Models\Users::count();        // Should show ~50-100 users
App\Models\Trainee::count();      // Should show ~200-500 trainees  
App\Models\Activity::count();     // Should show ~100-200 activities
App\Models\Centres::count();      // Should show ~10-20 centres
exit;
```

===============================================================================
## 🔐 STEP 5: DEFAULT LOGIN CREDENTIALS
===============================================================================

### Pre-Seeded User Accounts

After running the seeders, you'll have the following accounts available:

#### Administrator Accounts
```
Email: admin@creams.my
Password: password123
Role: admin
Access: Full system access, all centres

Email: admin2@creams.my  
Password: password123
Role: admin
Access: Full system access, all centres
```

#### Supervisor Accounts
```
Email: supervisor@creams.my
Password: password123
Role: supervisor
Centre: Kuala Lumpur Centre
Access: Centre management, staff oversight

Email: supervisor.penang@creams.my
Password: password123
Role: supervisor  
Centre: Penang Centre
Access: Centre management, staff oversight
```

#### Teacher Accounts
```
Email: teacher@creams.my
Password: password123
Role: teacher
Centre: Kuala Lumpur Centre
Access: Activity delivery, attendance marking

Email: teacher.johor@creams.my
Password: password123
Role: teacher
Centre: Johor Bahru Centre
Access: Activity delivery, attendance marking
```

#### AJK (Committee) Accounts
```
Email: ajk@creams.my
Password: password123
Role: ajk
Centre: Kuala Lumpur Centre
Access: View-only access, basic operations
```

### Custom Test Accounts

You can also create additional test accounts:

```bash
php artisan tinker

# Create custom admin
App\Models\Users::create([
    'name' => 'Test Admin',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin',
    'centre_id' => 'KL001',
    'status' => 'active'
]);

exit;
```

===============================================================================
## 🚀 STEP 6: START THE APPLICATION
===============================================================================

### Compile Frontend Assets
```bash
# Development mode (with file watching)
npm run dev

# Production mode (optimized)
npm run build
```

### Start Laravel Development Server
```bash
# Start the application server
php artisan serve

# Application will be available at:
# http://localhost:8000
```

### Alternative: Using Laragon
```
1. Place CREAMS folder in C:\laragon\www\
2. Create virtual host: CREAMS.local
3. Access via http://CREAMS.local
```

### Alternative: Using XAMPP
```
1. Place CREAMS folder in C:\xampp\htdocs\
2. Create virtual host in Apache config
3. Access via http://localhost/CREAMS/public
```

### Configure Storage Links
```bash
# Create symbolic link for file storage
php artisan storage:link

# This enables avatar uploads and file management
```

===============================================================================
## 🧪 STEP 7: VERIFY INSTALLATION
===============================================================================

### Test Basic Functionality

#### 1. Login Test
```
• Go to http://localhost:8000
• Click "Login"
• Use admin@creams.my / password123
• Should redirect to admin dashboard
```

#### 2. Dashboard Test
```
• Verify dashboard loads with statistics
• Check navigation menu is visible
• Confirm role-based content appears
```

#### 3. Module Access Test
```
• Navigate to "Management > Trainees"
• Verify trainee list loads with seeded data
• Navigate to "Management > Activities" 
• Verify activities list loads with categories
```

#### 4. Data Creation Test
```
• Try creating a new trainee
• Test file upload (avatar)
• Verify form validation works
```

### Performance Verification
```bash
# Clear all caches for optimal performance
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize for production (optional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

===============================================================================
## 📚 STEP 8: UNDERSTANDING THE SYSTEM
===============================================================================

### System Architecture Overview

**CREAMS** uses a centre-centric multi-tenant architecture:
- Each user belongs to a specific rehabilitation centre
- Data is automatically filtered by centre (except for admins)
- Role-based access control determines available features
- Custom session-based authentication (NOT Laravel's default)

### Navigation Structure
```
Dashboard
├── My Profile
├── Management
│   ├── Staffs (Admin/Supervisor only)
│   ├── Trainees
│   ├── Activities
│   └── Centres (Admin/Supervisor)
├── Reports & Settings
└── Logout
```

### Key Modules

#### 1. User Management
- **Purpose**: Manage staff across all roles
- **Access**: Admin (all centres), Supervisor (own centre)
- **Features**: Registration, profile management, role assignment

#### 2. Trainee Management  
- **Purpose**: Manage rehabilitation recipients
- **Access**: All roles (centre-restricted)
- **Features**: Registration, profiles, progress tracking, medical records

#### 3. Activity Management
- **Purpose**: Core rehabilitation program delivery
- **Access**: Admin/Supervisor (create), Teacher (deliver), AJK (view)
- **Features**: Activity creation, session scheduling, enrollment, attendance

#### 4. Asset Management
- **Purpose**: Equipment and resource tracking
- **Access**: Admin/Supervisor primarily
- **Features**: Inventory, maintenance scheduling, location tracking

#### 5. Communication
- **Purpose**: Internal messaging and notifications
- **Access**: All roles
- **Features**: Messages, notifications, announcements

### Activity Categories
```
Rehabilitation:
├── Physical Therapy
├── Occupational Therapy  
├── Speech & Language Therapy
├── Behavioral Therapy
└── Sensory Integration

Academic:
├── Basic Mathematics
├── Language & Literacy
├── Science Exploration
├── Computer Skills
├── Art & Creativity
├── Music Therapy
├── Social Skills
├── Life Skills
└── Vocational Training
```

===============================================================================
## 🎯 STEP 9: COMMON WORKFLOWS
===============================================================================

### Admin Workflow: Setting Up a New Centre

1. **Create Centre Record**
   ```
   • Navigate to Management > Centres
   • Click "Add New Centre"
   • Fill in centre details (name, location, capacity)
   • Save centre record
   ```

2. **Create Centre Staff**
   ```
   • Navigate to Management > Staffs
   • Create Supervisor account for centre
   • Create Teacher accounts as needed
   • Assign users to the new centre
   ```

3. **Setup Activities**
   ```
   • Navigate to Management > Activities
   • Create centre-specific activities
   • Schedule initial sessions
   • Set capacity and requirements
   ```

### Supervisor Workflow: Managing Daily Operations

1. **Review Dashboard**
   ```
   • Check today's scheduled sessions
   • Review attendance rates
   • Monitor staff assignments
   ```

2. **Manage Trainees**
   ```
   • Process new registrations
   • Update progress records
   • Coordinate with teachers on goals
   ```

3. **Oversee Activities**
   ```
   • Monitor session delivery
   • Review attendance patterns
   • Adjust schedules as needed
   ```

### Teacher Workflow: Delivering Sessions

1. **Check Schedule**
   ```
   • Review today's assigned sessions
   • Prepare materials and resources
   • Check trainee enrollment lists
   ```

2. **Conduct Sessions**
   ```
   • Mark attendance for each session
   • Record participation notes
   • Track individual progress
   ```

3. **Update Records**
   ```
   • Complete session reports
   • Update trainee progress notes
   • Report any issues to supervisor
   ```

### Trainee Journey: From Registration to Progress

1. **Registration Process**
   ```
   • Initial assessment and intake
   • Profile creation with medical history
   • Centre assignment and goal setting
   ```

2. **Activity Enrollment**
   ```
   • Assessment for appropriate activities
   • Enrollment in suitable programs
   • Schedule coordination
   ```

3. **Progress Tracking**
   ```
   • Regular attendance monitoring
   • Skill development assessment
   • Goal achievement measurement
   • Report generation
   ```

===============================================================================
## 🔧 STEP 10: DEVELOPMENT AND CUSTOMIZATION
===============================================================================

### Development Commands

#### Daily Development
```bash
# Start development server
php artisan serve

# Watch frontend assets
npm run dev

# Clear caches during development
php artisan optimize:clear
```

#### Testing
```bash
# Run all tests
php artisan test

# Run specific test suites
vendor/bin/phpunit tests/Feature
vendor/bin/phpunit tests/Unit

# Code formatting
vendor/bin/pint
```

#### Database Operations
```bash
# Create new migration
php artisan make:migration create_example_table

# Create new model
php artisan make:model Example

# Create new controller
php artisan make:controller ExampleController

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Customization Guidelines

#### Adding New Activity Categories
1. Update the category ENUM in activity migration
2. Add category metadata in `ActivityController::categories()`
3. Update frontend category displays
4. Add category-specific icons and colors

#### Creating New User Roles
1. Update role ENUM in users migration
2. Add role-specific middleware
3. Update navigation based on role
4. Create role-specific dashboards

#### Centre-Specific Customization
1. All data queries must include centre filtering
2. Use session('centre_id') for current user's centre
3. Admin role bypasses centre restrictions
4. Maintain data isolation in all operations

### File Organization
```
app/
├── Console/Commands/      # Custom Artisan commands
├── Http/Controllers/      # Feature controllers
├── Models/               # Eloquent models
├── Services/             # Business logic
├── Traits/               # Reusable functionality
└── Extensions/           # Custom Laravel extensions

resources/
├── views/
│   ├── activities/       # Activity management views
│   ├── trainees/         # Trainee management views
│   ├── layouts/          # Base templates
│   └── dashboard/        # Dashboard components
├── css/                  # Source stylesheets
└── js/                   # Source JavaScript

public/
├── css/                  # Compiled stylesheets
├── js/                   # Compiled JavaScript
├── images/               # Image assets
└── letters/              # Generated PDF letters
```

===============================================================================
## 🚨 TROUBLESHOOTING GUIDE
===============================================================================

### Common Installation Issues

#### "composer: command not found"
```bash
# Windows: Add Composer to PATH
set PATH=%PATH%;C:\path\to\composer

# macOS: Reinstall Composer globally
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### "php: command not found"
```bash
# Windows: Add PHP to PATH
set PATH=%PATH%;C:\xampp\php

# macOS: Check PHP installation
brew list php@8.2
brew link php@8.2 --force
```

#### Database Connection Issues
```bash
# Check MySQL is running
# Windows (XAMPP): Start MySQL in XAMPP Control Panel
# macOS: brew services start mysql

# Verify database exists
mysql -u root -p
SHOW DATABASES;
```

#### Permission Issues (macOS/Linux)
```bash
# Fix storage permissions
sudo chmod -R 755 storage/
sudo chmod -R 755 bootstrap/cache/

# Fix ownership
sudo chown -R $USER:$USER storage/
sudo chown -R $USER:$USER bootstrap/cache/
```

### Runtime Issues

#### "Class not found" Errors
```bash
# Clear and regenerate autoload
composer dump-autoload
php artisan optimize:clear
```

#### Session/Authentication Issues
```bash
# Clear sessions and cache
php artisan session:table
php artisan migrate
php artisan cache:clear
```

#### Frontend Asset Issues
```bash
# Rebuild assets
rm -rf node_modules/
npm install
npm run build
```

#### Database Migration Issues
```bash
# Reset migrations (WARNING: deletes data)
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

### Performance Issues

#### Slow Page Loading
```bash
# Enable caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev
```

#### Database Query Performance
```bash
# Enable query logging
DB_LOG_QUERIES=true

# Monitor slow queries in storage/logs/laravel.log
```

### Getting Help

#### Log Files
```bash
# Application logs
tail -f storage/logs/laravel.log

# Web server logs (check your server documentation)
```

#### Debug Mode
```bash
# Enable debug mode in .env
APP_DEBUG=true

# Check for detailed error messages
```

#### Community Resources
- Laravel Documentation: https://laravel.com/docs
- Stack Overflow: Tagged with 'laravel'
- Laravel Forums: https://laracasts.com/discuss

===============================================================================
## 📞 SUPPORT AND MAINTENANCE
===============================================================================

### Regular Maintenance Tasks

#### Weekly
```bash
# Update dependencies (check for security updates)
composer update
npm update

# Clear logs (if they get large)
echo "" > storage/logs/laravel.log

# Backup database
mysqldump -u root -p creams > backup_$(date +%Y%m%d).sql
```

#### Monthly
```bash
# Full cache refresh
php artisan optimize:clear
php artisan optimize

# Check for Laravel updates
composer show laravel/framework
```

### Backup Strategy

#### Database Backup
```bash
# Create backup
mysqldump -u creams_user -p creams > creams_backup_$(date +%Y%m%d_%H%M).sql

# Restore backup
mysql -u creams_user -p creams < creams_backup_20250708_1400.sql
```

#### File Backup
```bash
# Backup uploaded files
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/

# Backup entire project
tar -czf creams_full_backup_$(date +%Y%m%d).tar.gz ./ --exclude=node_modules --exclude=vendor
```

### Production Deployment Checklist

#### Environment Configuration
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Use production database credentials
# Configure mail settings
# Set proper APP_URL
```

#### Security Hardening
```bash
# Generate new APP_KEY
php artisan key:generate --force

# Set proper file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Configure HTTPS
# Setup proper backup procedures
# Configure monitoring
```

#### Performance Optimization
```bash
# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Compile assets for production
npm run build
```

===============================================================================
## 🎓 CONCLUSION
===============================================================================

Congratulations! You now have a fully functional CREAMS system running locally. Here's what you've accomplished:

### ✅ What's Now Available
- Complete rehabilitation center management system
- Pre-loaded demo data with realistic Malaysian content
- All major modules functional (Users, Trainees, Activities, Assets)
- Role-based access control working properly
- Professional UI/UX ready for demonstration or development

### 🎯 Next Steps
1. **Explore the System**: Login with different roles to see varying access levels
2. **Test Workflows**: Try creating trainees, scheduling activities, marking attendance
3. **Customize**: Modify categories, add new centres, adjust to your needs
4. **Develop**: Add new features or modify existing functionality
5. **Deploy**: Follow production deployment checklist for live environment

### 📚 Key Resources
- **CLAUDE.md**: Comprehensive development instructions
- **CREAMS GENERAL OVERVIEW.txt**: System architecture and features
- **PROJECT_STATE.txt**: Current project status and roadmap
- **routes/web.php**: Complete feature mapping
- **Laravel Documentation**: https://laravel.com/docs

### 🤝 Support
If you encounter issues:
1. Check the troubleshooting section above
2. Review log files in `storage/logs/`
3. Consult the comprehensive documentation files
4. Use Laravel community resources

**Happy rehabilitation management with CREAMS!** 🌟

===============================================================================
END OF SETUP GUIDE
===============================================================================