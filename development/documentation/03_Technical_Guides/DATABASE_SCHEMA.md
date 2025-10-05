# CREAMS Database Schema Documentation

## Database Overview
- **Database Name**: creams
- **Engine**: MySQL 8.0+
- **Character Set**: utf8mb4_unicode_ci
- **Foreign Keys**: Enabled with cascading rules

## Core Tables

### 1. users
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    role ENUM('admin','supervisor','teacher','ajk') NOT NULL,
    position VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    centre_id BIGINT UNSIGNED NULL,
    profile_picture VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE SET NULL
);
```

### 2. trainees
```sql
CREATE TABLE trainees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainee_id VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male','female') NOT NULL,
    ic_number VARCHAR(20) UNIQUE NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    emergency_contact_name VARCHAR(255) NULL,
    emergency_contact_phone VARCHAR(20) NULL,
    disability_type VARCHAR(255) NULL,
    disability_description TEXT NULL,
    medical_notes TEXT NULL,
    centre_id BIGINT UNSIGNED NOT NULL,
    registration_date DATE NOT NULL,
    status ENUM('active','inactive','graduated','withdrawn') DEFAULT 'active',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### 3. activities
```sql
CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_name VARCHAR(255) NOT NULL,
    activity_description TEXT NULL,
    category_id BIGINT UNSIGNED NULL,
    centre_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NULL,
    capacity INTEGER DEFAULT 10,
    duration_minutes INTEGER DEFAULT 60,
    location VARCHAR(255) NULL,
    equipment_needed TEXT NULL,
    objectives TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);
```

### 4. activity_sessions
```sql
CREATE TABLE activity_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    session_name VARCHAR(255) NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255) NULL,
    max_participants INTEGER NULL,
    session_notes TEXT NULL,
    status ENUM('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE
);
```

### 5. activity_enrollments
```sql
CREATE TABLE activity_enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trainee_id BIGINT UNSIGNED NOT NULL,
    activity_id BIGINT UNSIGNED NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('enrolled','active','completed','withdrawn') DEFAULT 'enrolled',
    progress_notes TEXT NULL,
    completion_date DATE NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (trainee_id) REFERENCES trainees(id) ON DELETE CASCADE,
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (trainee_id, activity_id)
);
```

### 6. centres
```sql
CREATE TABLE centres (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    centre_name VARCHAR(255) NOT NULL,
    centre_code VARCHAR(50) UNIQUE NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    capacity INTEGER DEFAULT 50,
    established_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 7. categories
```sql
CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL,
    category_description TEXT NULL,
    category_code VARCHAR(50) UNIQUE NULL,
    icon VARCHAR(255) NULL,
    color VARCHAR(7) DEFAULT '#007bff',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 8. assets
```sql
CREATE TABLE assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_name VARCHAR(255) NOT NULL,
    asset_code VARCHAR(100) UNIQUE NOT NULL,
    asset_type VARCHAR(100) NOT NULL,
    brand VARCHAR(100) NULL,
    model VARCHAR(100) NULL,
    serial_number VARCHAR(255) NULL,
    purchase_date DATE NULL,
    purchase_price DECIMAL(10,2) NULL,
    warranty_expiry DATE NULL,
    centre_id BIGINT UNSIGNED NOT NULL,
    location VARCHAR(255) NULL,
    condition_status ENUM('excellent','good','fair','poor','damaged') DEFAULT 'good',
    maintenance_schedule ENUM('weekly','monthly','quarterly','yearly') NULL,
    last_maintenance DATE NULL,
    next_maintenance DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE CASCADE
);
```

## Authentication Tables

### 9. password_reset_tokens (Laravel 10+)
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

**Note**: Code currently references `password_resets` table - migration needed!

## Communication Tables

### 10. letters
```sql
CREATE TABLE letters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    letter_reference VARCHAR(255) UNIQUE NOT NULL,
    letter_subject VARCHAR(500) NOT NULL,
    letter_content TEXT NOT NULL,
    letter_date DATE NOT NULL,
    letter_data JSON NULL,
    letter_file_path VARCHAR(500) NULL,
    template_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (template_id) REFERENCES letter_templates(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### 11. letter_templates
```sql
CREATE TABLE letter_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(255) NOT NULL,
    template_content TEXT NULL,
    header_content TEXT NULL,
    footer_content TEXT NULL,
    header_image_path VARCHAR(500) NULL,
    footer_image_path VARCHAR(500) NULL,
    is_active BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### 12. notifications
```sql
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_type VARCHAR(50) NOT NULL,  -- Should be 'role'
    notification_title VARCHAR(255) NOT NULL,  -- Should be 'title'
    notification_message TEXT NOT NULL,  -- Should be 'content'
    is_read BOOLEAN DEFAULT FALSE,  -- Should be 'read'
    centre_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (centre_id) REFERENCES centres(id) ON DELETE CASCADE
);
```

**Note**: Column names don't match model expectations - migration needed!

## Support Tables

### 13. volunteers
```sql
CREATE TABLE volunteers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    skills TEXT NULL,
    availability TEXT NULL,
    motivation TEXT NULL,
    experience TEXT NULL,
    preferred_centre VARCHAR(255) NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 14. contacts
```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    phone VARCHAR(20) NULL,
    status ENUM('new','read','responded') DEFAULT 'new',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

## Relationship Diagram

```
users
├── centres (belongs to)
├── trainees (has many, created_by)
├── activities (has many, teacher_id)
├── letters (has many, created_by)
└── letter_templates (has many, created_by)

centres
├── users (has many)
├── trainees (has many)
├── activities (has many)
├── assets (has many)
└── notifications (has many)

activities
├── categories (belongs to)
├── centres (belongs to)
├── users/teacher (belongs to)
├── activity_sessions (has many)
└── trainees (many-to-many via enrollments)

trainees
├── centres (belongs to)
├── users/creator (belongs to)
└── activities (many-to-many via enrollments)

activity_sessions
└── activities (belongs to)

activity_enrollments
├── trainees (belongs to)
└── activities (belongs to)
```

## Indexes

### Performance Indexes
```sql
-- User lookups
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_centre ON users(centre_id);

-- Trainee searches
CREATE INDEX idx_trainees_centre ON trainees(centre_id);
CREATE INDEX idx_trainees_status ON trainees(status);
CREATE INDEX idx_trainees_trainee_id ON trainees(trainee_id);

-- Activity queries
CREATE INDEX idx_activities_centre ON activities(centre_id);
CREATE INDEX idx_activities_teacher ON activities(teacher_id);
CREATE INDEX idx_activities_category ON activities(category_id);
CREATE INDEX idx_activities_active ON activities(is_active);

-- Session queries
CREATE INDEX idx_sessions_activity ON activity_sessions(activity_id);
CREATE INDEX idx_sessions_date ON activity_sessions(session_date);
CREATE INDEX idx_sessions_status ON activity_sessions(status);

-- Enrollment queries
CREATE INDEX idx_enrollments_trainee ON activity_enrollments(trainee_id);
CREATE INDEX idx_enrollments_activity ON activity_enrollments(activity_id);
CREATE INDEX idx_enrollments_status ON activity_enrollments(status);
```

## Known Schema Issues

### Critical Issues
1. **Table Name Mismatch**: `password_reset_tokens` vs `password_resets`
2. **Column Name Mismatches**:
   - `notifications.user_type` should be `role`
   - `notifications.is_read` should be `read`
   - `notifications.notification_title` should be `title`
   - `notifications.notification_message` should be `content`
3. **Missing Columns**:
   - `activity_sessions.deleted_at` for soft deletes
   - `activity_enrollments.status` (if code expects it)
   - `activities.is_active` (if code expects it)

### Migration Fixes Needed
```sql
-- Fix password reset table
RENAME TABLE password_reset_tokens TO password_resets;

-- Fix notifications table
ALTER TABLE notifications 
CHANGE user_type role VARCHAR(50) NOT NULL,
CHANGE is_read read BOOLEAN DEFAULT FALSE,
CHANGE notification_title title VARCHAR(255) NOT NULL,
CHANGE notification_message content TEXT NOT NULL;

-- Add soft deletes to activity_sessions
ALTER TABLE activity_sessions ADD deleted_at TIMESTAMP NULL;

-- Ensure all expected columns exist
ALTER TABLE activities ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE;
```

## Data Validation Rules

### User Validation
- Email must be unique and valid format
- Role must be one of: admin, supervisor, teacher, ajk
- Phone format: Malaysian phone numbers preferred

### Trainee Validation
- IC number must be unique (Malaysian format)
- Age typically 6-12 years for rehabilitation programs
- Status transitions: active → inactive/graduated/withdrawn

### Activity Validation
- Capacity must be positive integer
- Duration in minutes (typical: 30-120 minutes)
- Teacher must be role 'teacher' or 'supervisor'

### Date Constraints
- Registration dates cannot be future dates
- Session dates must be present or future
- Birth dates must result in reasonable ages (0-18 for trainees)

## Sample Data Requirements

### Malaysian Demographics (for realistic seeding)
- **Muslim names (~65%)**: Ahmad, Muhammad, Siti, Fatimah, Abdul, Aminah
- **Chinese names (~25%)**: Lim, Tan, Wong, Lee, Chin, Ng
- **Indian names (~10%)**: Kumar, Priya, Raj, Devi, Singh, Kaur

### Disability Types
- Physical disabilities
- Intellectual disabilities
- Autism spectrum disorders
- Learning disabilities
- Multiple disabilities

This schema supports comprehensive rehabilitation center management with proper relationships and constraints.