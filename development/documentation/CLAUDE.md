# CREAMS - System Documentation & Development Guidelines

## 🚨 **CRITICAL - DO NOT MODIFY THESE PAGES** 🚨

### ✅ **FULLY FUNCTIONAL PAGES - PRODUCTION READY**

These pages are **COMPLETELY FUNCTIONAL** and **PRODUCTION READY** for demo day. **DO NOT MODIFY, EDIT, OR CHANGE ANYTHING** related to these pages unless explicitly requested:

#### 1. **Welcome/Home Page** (`/` or `/home`)

-   **Status**: ✅ **FULLY FUNCTIONAL - DO NOT TOUCH**
-   **File**: `resources/views/home.blade.php`
-   **Features Working**:
    -   Hero section with video background
    -   Vision & Mission sections
    -   Client Charter cards
    -   Journey Timeline
    -   Services showcase
    -   **Organization Structure with leadership images** (from `/images/leadership/` folder)
    -   Impact metrics section
    -   Responsive design & animations
    -   Contact information & Google Maps integration
-   **⚠️ IMPORTANT**: Leadership images are correctly mapped and displaying

#### 2. **Contact Page** (`/contact`)

-   **Status**: ✅ **FULLY FUNCTIONAL - DO NOT TOUCH**
-   **File**: `resources/views/contactus.blade.php`
-   **Controller**: `app/Http/Controllers/ContactController.php`
-   **JavaScript**: `public/js/contact.js` (✅ **FIXED** - form submission working)
-   **Features Working**:
    -   Multi-step contact form with real-time validation
    -   Auto-save functionality & form progress tracking
    -   **Success/failure messages display properly** (RECENTLY FIXED)
    -   Email notifications to both admin and user
    -   Phone number auto-formatting
    -   Subject auto-generation based on reason
    -   Character counters & priority levels
    -   Google Maps integration
    -   Responsive design

#### 3. **Volunteer Page** (`/volunteer`)

-   **Status**: ✅ **FULLY FUNCTIONAL - DO NOT TOUCH**
-   **File**: `resources/views/volunteers/home.blade.php`
-   **Features Working**:
    -   Hero section with video background
    -   Multi-step volunteer application form
    -   Form validation & progress tracking
    -   Volunteer opportunities showcase
    -   Impact statistics display
    -   Email confirmation system
    -   Application status tracking
    -   Auto-save functionality
    -   Responsive design

---

## 📋 **PROJECT OVERVIEW**

**CREAMS** (Community-based Rehabilitation Systems) is a comprehensive management system for IIUM PD-CARE, designed to manage rehabilitation services, staff, trainees, and community engagement.

### **Tech Stack**

-   **Backend**: Laravel 10.x (PHP 8.1+)
-   **Frontend**: Blade templates, Bootstrap 5, jQuery
-   **Database**: MySQL 8.0+
-   **Server**: Laravel development server / Apache

### **Key Features**

-   Role-based access control (Admin, Teacher, Supervisor, AJK, Trainee)
-   Activity and schedule management
-   Attendance tracking system
-   User and trainee management
-   Communication and notification system
-   Letter generation and PDF export
-   Centre and asset management

---

## 🔧 **ESSENTIAL SETUP FOR NEW DEBUGGING SESSIONS**

**CRITICAL: Start every debugging session with these steps for complete system understanding:**

### Phase 1: Core Documentation Review (Required)

1. **Read this CLAUDE.md** - Complete system overview and protected pages list
2. **Read CREAMS_GENERAL_OVERVIEW.txt** - System overview, user roles, and business logic
3. **Read detailed_implementation_report.txt** - Technical implementation details and database schema
4. **Read all other .md and .txt files** in development/documentation directory for complete context

### Phase 2: System State Analysis (Required)

1. **Check git status and recent commits** - Understand current branch state and recent changes
2. **Review database schema** - Check actual column names vs. assumptions (common error source)
3. **Analyze user roles and permissions** - Admin, Teacher, Supervisor, AJK, Trainee roles have different data access

### Phase 3: Common Issue Areas (Critical Knowledge)

**Database Schema Gotchas:**

-   Users table uses `status = 'active'` NOT `is_active` column
-   Trainees table has `guardian_name` NOT `parent_id` for relationships
-   Activity sessions and attendance tables have complex foreign key relationships
-   Always verify column existence before writing queries
-   Database name is `creams` not `creams_db`

**Dashboard Data Flow:**

-   DashboardController.php handles all statistics and calendar data
-   Statistics methods differ between admin (flat array) and supervisor (card array) formats
-   Calendar events require proper Carbon date parsing: `date + month + year` format
-   Never use hardcoded data - always query database even if results are zero

**Common Debugging Patterns:**

-   Statistics showing zeros usually means wrong column names or inactive record filtering
-   "Still the same" user feedback indicates deeper schema or logic issues requiring investigation
-   Calendar/schedule issues often stem from date parsing or data format problems
-   CSS not loading means missing asset includes in app.blade.php layout

### Phase 4: File Structure Knowledge (Essential)

**Key Controllers:**

-   `app/Http/Controllers/Dashboard/DashboardController.php` - All dashboard logic and statistics
-   `app/Http/Controllers/Activity/ActivityController.php` - Activity management and session handling
-   Database models in `app/Models/` - Check these for actual column names and relationships

**Key Views:**

-   `resources/views/dashboard/modern.blade.php` - Main dashboard template
-   `resources/views/layouts/app.blade.php` - Base layout with CSS/JS includes
-   `resources/views/activities/` - Activity management views

**Key Assets:**

-   `public/css/dashboard-widgets.css` - Dashboard styling (must be included in layout)
-   Public assets need proper asset() helper for URL generation

---

## 🎯 **DEVELOPMENT GUIDELINES**

### **When Starting New Sessions**

1. **ALWAYS READ THIS FILE FIRST** before making any changes
2. **DO NOT MODIFY** the 3 functional pages listed above
3. **ASK FOR CONFIRMATION** if any request involves these pages
4. **REFER TO DEMO_CHECKLIST.md** for current project status

### **Code Standards**

-   Follow Laravel conventions and best practices
-   Use meaningful variable and function names
-   Add proper error handling and logging
-   Maintain responsive design principles
-   Test all form submissions and database operations

### **Database Information**

-   **Host**: localhost
-   **Database**: creams (NOT creams_db)
-   **Port**: 3306
-   **Environment**: .env file configured

---

## 📁 **PROJECT STRUCTURE**

### **Key Directories**

```
CREAMS/
├── app/Http/Controllers/          # Application controllers
├── resources/views/               # Blade templates
│   ├── home.blade.php            # ✅ FUNCTIONAL - DO NOT TOUCH
│   ├── contactus.blade.php       # ✅ FUNCTIONAL - DO NOT TOUCH
│   ├── volunteers/home.blade.php # ✅ FUNCTIONAL - DO NOT TOUCH
│   ├── dashboard/                # Dashboard views
│   ├── activities/               # Activity management
│   ├── staff/                    # Staff management
│   └── trainees/                 # Trainee management
├── public/
│   ├── css/                      # Stylesheets
│   ├── js/                       # JavaScript files
│   └── images/
│       └── leadership/           # ✅ Leadership org chart images
├── database/migrations/          # Database migrations
└── routes/web.php               # Application routes
```

### **Important Files**

-   **Routes**: `routes/web.php` - All application routes
-   **Dashboard Controller**: `app/Http/Controllers/Dashboard/DashboardController.php`
-   **Contact Controller**: `app/Http/Controllers/ContactController.php` ✅
-   **Main Layout**: `resources/views/layouts/app.blade.php`
-   **Demo Checklist**: `DEMO_CHECKLIST.md` - Complete testing checklist

---

## 🗄️ **DATABASE SCHEMA**

### **Key Tables**

-   `users` - System users (staff, admins)
-   `trainees` - Trainee/student records
-   `activities` - Rehabilitation activities
-   `activity_sessions` - Individual activity sessions
-   `activity_enrollments` - Activity-level enrollments
-   `session_enrollments` - Session-specific enrollments
-   `attendances` - Attendance records
-   `contact_messages` - Contact form submissions ✅
-   `volunteer_applications` - Volunteer applications ✅
-   `centres` - Rehabilitation centres

---

## 🔐 **AUTHENTICATION & ROLES**

### **User Roles**

1. **Admin** - Full system access, can create activities and sessions
2. **Teacher** - Activity instruction and trainee management
3. **Supervisor** - Team oversight and reporting
4. **AJK** - Facility and administrative management
5. **Trainee** - Limited access to personal data

### **Role-based Views**

-   Each role has specific dashboard views in `resources/views/dashboard/`
-   Role-based navigation and permissions implemented
-   Session-based authentication system

### **Permission Structure (NEW)**

-   **Only Admin** can create/edit activities and schedule sessions
-   **All staff** can be assigned as instructors for activities
-   **Proper audit trail** maintained for compliance

---

## 🐛 **KNOWN ISSUES & FIXES**

### **Recently Fixed** ✅

1. **Activity statistics** - Fixed to use real database data instead of placeholder values
2. **Session navigation** - Made schedule sessions clickable with proper routing
3. **Role permissions** - Restricted activity creation to admin-only for better audit control
4. **Database column mapping** - Fixed view references to use correct database column names
5. **Contact form success messages** - JavaScript form submission issue resolved
6. **Leadership images in org chart** - Correct paths to `/images/leadership/` folder
7. **Dashboard tab structure** - General and Personal tabs implemented
8. **Attendance calculation** - Fixed to use actual attendance records instead of session enrollments

### **Tested & Working** ✅

-   Form validations across all functional pages
-   Email notifications and confirmations
-   Image loading and display
-   Responsive design on mobile/tablet/desktop
-   Session management and flash messages
-   Database operations and data persistence
-   Activity management with proper permissions
-   Statistics calculations using real database data

---

## 🚀 **DEPLOYMENT NOTES**

### **Demo Day Preparation**

-   All 3 critical pages are production-ready ✅
-   Database seeded with test data
-   Email system configured and tested
-   All forms tested and working
-   Responsive design verified

### **Server Requirements**

-   PHP 8.1+
-   MySQL 8.0+
-   Laravel 10.x dependencies
-   Proper file permissions for uploads
-   HTTPS recommended for production

---

## ⚠️ **IMPORTANT REMINDERS**

### **🚨 BEFORE MAKING ANY CHANGES:**

1. **READ THIS FILE COMPLETELY**
2. **CHECK IF REQUEST INVOLVES THE 3 FUNCTIONAL PAGES**
3. **IF YES - DECLINE OR SEEK EXPLICIT PERMISSION**
4. **REFER TO DEMO_CHECKLIST.md FOR CURRENT STATUS**

### **✅ Safe to Modify:**

-   Dashboard functionality (non-core features)
-   Activity management features (admin-restricted)
-   User management improvements
-   Attendance system enhancements
-   Letter generation features
-   Backend administrative tools

### **🚨 DO NOT MODIFY WITHOUT EXPLICIT REQUEST:**

-   `resources/views/home.blade.php`
-   `resources/views/contactus.blade.php`
-   `resources/views/volunteers/home.blade.php`
-   `app/Http/Controllers/ContactController.php`
-   `public/js/contact.js`
-   `public/images/leadership/` folder contents
-   Any email templates for contact/volunteer systems

---

## 📞 **EMERGENCY CONTACTS**

For any critical issues during demo day:

-   **Database**: Check `.env` configuration (database name is `creams`)
-   **Email**: Verify SMTP settings
-   **Images**: Ensure `/public/images/leadership/` folder has correct permissions
-   **Forms**: Check JavaScript console for any errors
-   **Statistics**: Verify database column names match code references

---

**Last Updated**: January 15, 2025  
**Status**: Production Ready ✅  
**Critical Pages**: 3/3 Fully Functional ✅
**Activity Management**: Enhanced with Admin-Only Permissions ✅
