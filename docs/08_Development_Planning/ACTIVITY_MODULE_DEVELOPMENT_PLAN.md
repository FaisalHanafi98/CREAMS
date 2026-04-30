# CREAMS Activity Module - Complete Development Plan

## Current Status: **Foundation Built, Core Features Missing**

### What We Have ✅
- **Solid Database Architecture**: Activities, Sessions, Enrollments, Attendance
- **Conflict Detection**: Teacher, venue, time overlap prevention
- **Basic Session Management**: Create, update, cancel sessions
- **Attendance Tracking**: Session-level attendance with progress calculation
- **Role-Based Access**: Admin, supervisor, teacher permissions
- **Centre Integration**: Activities scoped to rehabilitation centres

### What's Missing ❌
- **Academic Progression System**: Course levels, prerequisites, competency tracking
- **Schedule Templates**: Reusable patterns (3x/week for 2 months, etc.)
- **Learning Outcome Mapping**: Goals and achievement tracking
- **Bulk Operations**: Mass session management, batch enrollments
- **Advanced Reporting**: Progress reports, academic outcomes
- **Resource Management**: Equipment booking, material tracking

---

## PHASE 1: FOUNDATION IMPROVEMENTS (Week 1-2)

### 1.1 Database Schema Cleanup
**Priority: CRITICAL** - Fix data inconsistencies

```sql
-- Fix mixed date fields
ALTER TABLE activity_sessions 
DROP COLUMN session_date,
RENAME COLUMN scheduled_date TO session_date;

-- Add missing room_number field
ALTER TABLE activity_sessions 
ADD COLUMN room_number VARCHAR(50) AFTER venue;

-- Standardize enrollment table
-- (Keep ActivityEnrollment, deprecate SessionEnrollment)
```

### 1.2 Model Enhancements
**Files to Update:**
- `app/Models/Activity.php` - Add academic progression methods
- `app/Models/ActivitySession.php` - Add room_number support
- `app/Models/ActivityEnrollment.php` - Add progress tracking fields

**Key Additions:**
```php
// Activity.php
public function generateSessionSchedule($template) {
    // Auto-create sessions based on template
}

public function getCompletionRate() {
    // Calculate overall activity completion
}

// ActivitySession.php  
public function getRoomDetails() {
    return $this->venue . ($this->room_number ? " - Room {$this->room_number}" : '');
}
```

### 1.3 Controller Fixes
**File:** `app/Http/Controllers/Activity/ActivityController.php`

**Immediate Fixes:**
- Fix data queries to use correct field names
- Add bulk operations endpoints
- Improve error handling for missing data

---

## PHASE 2: SCHEDULE TEMPLATE SYSTEM (Week 3-4)

### 2.1 New Database Tables

```sql
-- Activity Schedule Templates
CREATE TABLE activity_schedule_templates (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(100) NOT NULL,
    description TEXT,
    sessions_per_week INT NOT NULL,
    duration_weeks INT NOT NULL,
    session_length_minutes INT DEFAULT 60,
    days_of_week JSON, -- ["Monday", "Wednesday", "Friday"]
    created_by BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Template Applications (Track which activities use which templates)
CREATE TABLE activity_template_applications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    activity_id BIGINT,
    template_id BIGINT,
    start_date DATE,
    end_date DATE,
    customizations JSON, -- Any template overrides
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    FOREIGN KEY (template_id) REFERENCES activity_schedule_templates(id)
);
```

### 2.2 Template Management Features

**New Controller:** `app/Http/Controllers/Activity/ScheduleTemplateController.php`

**Key Methods:**
```php
public function create() {
    // Create template form (3x/week for 8 weeks, etc.)
}

public function generateSessions($activityId, $templateId, $startDate) {
    // Auto-generate all sessions for activity using template
}

public function applyTemplate($activityId, $templateId) {
    // Apply template to existing activity
}
```

### 2.3 User Interface Enhancements

**New Views:**
- `resources/views/activity/templates/index.blade.php` - Template library
- `resources/views/activity/templates/create.blade.php` - Template builder
- Enhanced activity creation with template selection

---

## PHASE 3: ACADEMIC PROGRESSION SYSTEM (Week 5-6)

### 3.1 Learning Outcomes & Competencies

```sql
-- Learning Outcomes
CREATE TABLE learning_outcomes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    activity_id BIGINT,
    outcome_title VARCHAR(200) NOT NULL,
    outcome_description TEXT,
    competency_level ENUM('Beginner', 'Intermediate', 'Advanced'),
    assessment_criteria JSON,
    FOREIGN KEY (activity_id) REFERENCES activities(id)
);

-- Trainee Competency Progress
CREATE TABLE trainee_competency_progress (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    trainee_id BIGINT,
    learning_outcome_id BIGINT,
    current_level ENUM('Not Started', 'In Progress', 'Achieved', 'Mastered'),
    progress_percentage DECIMAL(5,2),
    last_assessed_at TIMESTAMP,
    assessed_by BIGINT,
    notes TEXT,
    FOREIGN KEY (trainee_id) REFERENCES trainees(id),
    FOREIGN KEY (learning_outcome_id) REFERENCES learning_outcomes(id),
    FOREIGN KEY (assessed_by) REFERENCES users(id)
);
```

### 3.2 Prerequisite System

```sql
-- Activity Prerequisites
CREATE TABLE activity_prerequisites (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    activity_id BIGINT,
    prerequisite_activity_id BIGINT,
    minimum_completion_percentage DECIMAL(5,2) DEFAULT 80.00,
    required_competency_level ENUM('Beginner', 'Intermediate', 'Advanced'),
    FOREIGN KEY (activity_id) REFERENCES activities(id),
    FOREIGN KEY (prerequisite_activity_id) REFERENCES activities(id)
);
```

### 3.3 Enhanced Models

```php
// Activity.php additions
public function learningOutcomes() {
    return $this->hasMany(LearningOutcome::class);
}

public function prerequisites() {
    return $this->belongsToMany(Activity::class, 'activity_prerequisites', 
                               'activity_id', 'prerequisite_activity_id');
}

public function checkPrerequisites($traineeId) {
    // Verify trainee meets all prerequisites
}
```

---

## PHASE 4: ENHANCED PROGRESS TRACKING (Week 7-8)

### 4.1 Individual Education Plans (IEP) Integration

```sql -- Individual Education Plans
CREATE TABLE trainee_education_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    trainee_id BIGINT,
    plan_name VARCHAR(200),
    start_date DATE,
    end_date DATE,
    overall_goals JSON,
    status ENUM('Active', 'Completed', 'Suspended') DEFAULT 'Active',
    created_by BIGINT,
    FOREIGN KEY (trainee_id) REFERENCES trainees(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- IEP Goal Mapping to Activities
CREATE TABLE iep_activity_goals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    iep_id BIGINT,
    activity_id BIGINT,
    goal_description TEXT,
    target_completion_date DATE,
    progress_tracking_method ENUM('Attendance', 'Competency', 'Assessment'),
    FOREIGN KEY (iep_id) REFERENCES trainee_education_plans(id),
    FOREIGN KEY (activity_id) REFERENCES activities(id)
);
```

### 4.2 Progress Reporting System

**New Controller:** `app/Http/Controllers/ProgressReportController.php`

**Key Features:**
- Individual trainee progress reports
- Parent/guardian accessible reports
- Centre-wide performance dashboards
- Academic outcome analytics

---

## PHASE 5: ADVANCED FEATURES (Week 9-12)

### 5.1 Resource Management Integration

```sql
-- Equipment/Resource Inventory
CREATE TABLE resources (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    resource_name VARCHAR(100),
    resource_type ENUM('Equipment', 'Room', 'Material', 'Tool'),
    centre_id VARCHAR(50),
    availability_status ENUM('Available', 'In Use', 'Maintenance', 'Unavailable'),
    location VARCHAR(100),
    FOREIGN KEY (centre_id) REFERENCES centres(centre_id)
);

-- Session Resource Requirements
CREATE TABLE session_resource_requirements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    session_id BIGINT,
    resource_id BIGINT,
    quantity_required INT DEFAULT 1,
    is_critical BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (session_id) REFERENCES activity_sessions(id),
    FOREIGN KEY (resource_id) REFERENCES resources(id)
);
```

### 5.2 Advanced Scheduling Features

- **Recurring Schedule Patterns**: Automatic session generation
- **Academic Calendar Integration**: Term dates, holidays, breaks
- **Multi-Centre Coordination**: Cross-centre resource sharing
- **Advanced Conflict Resolution**: Automatic rescheduling suggestions

### 5.3 Reporting & Analytics

**New Dashboard Widgets:**
- Activity completion rates by centre
- Trainee progress trends
- Resource utilization analytics
- Teacher workload distribution
- Parent engagement metrics

---

## INTERCONNECTION MAP

### Centre Management Integration
```
Centre → Activities → Sessions → Attendance
   ↓         ↓          ↓          ↓
Resources  Teachers   Rooms    Progress
   ↓         ↓          ↓          ↓
Bookings  Schedules  Conflicts  Reports
```

### Attendance Module Integration
```
Session → Attendance Record → Progress Calculation
   ↓            ↓                    ↓
IEP Goals → Competency → Achievement Tracking
   ↓            ↓                    ↓
Reports   → Analytics → Parent Notifications
```

### User Role Permissions
```
Admin: Full system access, centre management
Supervisor: Centre-specific activities, reporting
Teacher: Assigned sessions, attendance marking  
Parent: Child's progress viewing only
Trainee: Own schedule and progress viewing
```

---

## IMPLEMENTATION PRIORITY

### CRITICAL (Must Fix Now):
1. ✅ **Database field inconsistencies** - Fixed `room_number` and `description` errors
2. **Schedule template system** - Core functionality for class syllabus concept
3. **Session bulk operations** - Essential for managing ongoing programs

### HIGH (Next 2 weeks):
1. **Learning outcome tracking** - Academic progression foundation
2. **Enhanced progress calculation** - Better than simple attendance percentage
3. **Resource conflict detection** - Prevent double-booking equipment/rooms

### MEDIUM (Month 2):
1. **IEP integration** - Individual education planning
2. **Parent reporting system** - Family engagement
3. **Advanced analytics** - Performance insights

### LOW (Future enhancements):
1. **Cross-centre coordination** - Multi-location features
2. **AI-powered scheduling** - Automatic optimization
3. **Mobile app integration** - On-the-go access

---

## SUCCESS METRICS

### Technical Metrics:
- Zero database errors related to missing fields
- <2 second page load times for activity views
- 99.9% uptime for session management
- Conflict detection accuracy >95%

### Functional Metrics:
- 100% of activities have proper descriptions
- 100% of sessions have room assignments
- Average session attendance >80%
- Teacher utilization 70-85% (optimal range)

### User Experience Metrics:
- Staff can create full 3-month activity schedule in <10 minutes
- Parents can view child's progress without assistance
- Zero attendance marking errors
- 95% user satisfaction with scheduling system

---

This comprehensive plan transforms the CREAMS activity module from basic activity management into a full-featured educational program management system that supports your vision of activities as structured class syllabi with proper academic progression and outcome tracking.