# CREAMS User Stories & Workflows

## User Roles Overview

### 1. Admin
**Role**: System Administrator
**Access**: Full system access across all centers
**Responsibilities**: System configuration, user management, center oversight

### 2. Supervisor  
**Role**: Center Supervisor
**Access**: Full access within assigned center
**Responsibilities**: Center operations, staff management, activity oversight

### 3. Teacher
**Role**: Activity Teacher/Instructor
**Access**: Activity and trainee management within assigned center
**Responsibilities**: Activity delivery, trainee progress tracking, session management

### 4. AJK (Committee Member)
**Role**: Administrative Support
**Access**: Limited access to basic functions
**Responsibilities**: Administrative support, basic data entry

## Core User Journeys

### Admin User Stories

#### US-A001: System Overview
**As an** Admin  
**I want to** see a comprehensive dashboard with system-wide statistics  
**So that** I can monitor the overall health and performance of all centers

**Acceptance Criteria:**
- View total users, trainees, activities across all centers
- See recent system activities and alerts
- Access quick links to all major modules
- View system health indicators

**Current Status**: ⚠️ Partially working (dashboard stats broken due to DB schema issues)

#### US-A002: Center Management
**As an** Admin  
**I want to** manage all rehabilitation centers in the system  
**So that** I can maintain accurate center information and oversight

**Acceptance Criteria:**
- Create new centers with complete details
- Edit existing center information
- Activate/deactivate centers
- View center-specific statistics
- Assign staff to centers

**Current Status**: ❌ Broken (redirects to dashboard)

#### US-A003: User Management
**As an** Admin  
**I want to** manage user accounts across all centers  
**So that** I can control system access and user permissions

**Acceptance Criteria:**
- Create new user accounts with appropriate roles
- Edit user profiles and permissions
- Reset user passwords
- Assign users to centers
- View user activity logs

**Current Status**: ❌ Password reset broken (table name mismatch)

#### US-A004: Letter Template Management
**As an** Admin  
**I want to** manage official letter templates  
**So that** all centers use consistent, professional correspondence

**Acceptance Criteria:**
- Create and edit letter templates
- Upload letterhead images
- Set active templates
- Preview template designs
- Version control for templates

**Current Status**: ✅ Working

### Supervisor User Stories

#### US-S001: Center Dashboard
**As a** Supervisor  
**I want to** see my center's dashboard with key metrics  
**So that** I can monitor daily operations effectively

**Acceptance Criteria:**
- View trainee enrollment numbers
- See today's scheduled activities
- Monitor staff attendance
- View recent alerts and notifications
- Access quick actions for common tasks

**Current Status**: ⚠️ Partially working (some stats broken)

#### US-S002: Trainee Management
**As a** Supervisor  
**I want to** oversee all trainees in my center  
**So that** I can ensure quality care and progress tracking

**Acceptance Criteria:**
- View all trainees in my center
- Access detailed trainee profiles
- Monitor enrollment in activities
- Track overall progress
- Generate trainee reports

**Current Status**: ❌ Broken (profile viewing fails, enrollment errors)

#### US-S003: Activity Oversight
**As a** Supervisor  
**I want to** manage all activities in my center  
**So that** I can ensure programs meet quality standards

**Acceptance Criteria:**
- View all center activities
- Create new activity programs
- Assign teachers to activities
- Monitor session attendance
- Review activity outcomes

**Current Status**: ❌ Broken (activities page redirects)

#### US-S004: Staff Coordination
**As a** Supervisor  
**I want to** coordinate staff assignments and schedules  
**So that** activities are properly staffed and managed

**Acceptance Criteria:**
- View staff schedules
- Assign teachers to activities
- Monitor staff workloads
- Handle staff requests
- Generate staff reports

**Current Status**: ⚠️ Partially working

### Teacher User Stories

#### US-T001: Activity Management
**As a** Teacher  
**I want to** manage my assigned activities  
**So that** I can deliver effective rehabilitation programs

**Acceptance Criteria:**
- View my activity schedule
- Access activity details and objectives
- See enrolled trainees for each activity
- Update activity notes and progress
- Manage session materials

**Current Status**: ❌ Broken (activities module non-functional)

#### US-T002: Session Delivery
**As a** Teacher  
**I want to** conduct activity sessions with trainees  
**So that** I can provide quality rehabilitation services

**Acceptance Criteria:**
- Mark session attendance
- Record session notes
- Update trainee progress
- Document incidents or concerns
- Upload session photos/materials

**Current Status**: ❌ Broken (session management issues)

#### US-T003: Trainee Progress Tracking
**As a** Teacher  
**I want to** track individual trainee progress  
**So that** I can provide personalized care and report outcomes

**Acceptance Criteria:**
- View trainee profiles and history
- Record progress notes
- Set individual goals
- Track skill development
- Generate progress reports

**Current Status**: ❌ Broken (trainee profile access fails)

#### US-T004: Communication
**As a** Teacher  
**I want to** communicate with supervisors and parents  
**So that** I can maintain transparency and collaboration

**Acceptance Criteria:**
- Send updates to supervisors
- Generate progress letters
- Record parent communications
- Submit incident reports
- Request resources or support

**Current Status**: ⚠️ Letter generation works, other communication broken

### AJK User Stories

#### US-J001: Data Entry Support
**As an** AJK member  
**I want to** assist with basic data entry tasks  
**So that** I can support center operations

**Acceptance Criteria:**
- Enter trainee registration data
- Update basic information
- Assist with attendance recording
- Help with administrative tasks
- Access basic reports

**Current Status**: ⚠️ Limited by broken core functions

## Detailed Workflows

### Workflow 1: Trainee Registration Process

**Participants**: Supervisor, AJK, Parent/Guardian  
**Current Status**: ❌ Broken

1. **Initial Contact**: Parent contacts center
2. **Application**: Parent fills application form
3. **Assessment**: Staff conducts needs assessment
4. **Registration**: AJK enters trainee data in system
5. **Enrollment**: Supervisor enrolls trainee in appropriate activities
6. **Orientation**: Teacher conducts orientation session
7. **Progress Tracking**: Ongoing monitoring begins

**Broken Points**:
- Step 4: Registration fails (trainee_id error)
- Step 5: Enrollment fails (missing status column)

### Workflow 2: Activity Session Management

**Participants**: Teacher, Trainees, Supervisor  
**Current Status**: ❌ Broken

1. **Session Preparation**: Teacher reviews session plan
2. **Attendance**: Teacher marks trainee attendance
3. **Activity Delivery**: Conduct rehabilitation activities
4. **Progress Recording**: Document trainee progress
5. **Session Notes**: Record session outcomes
6. **Reporting**: Submit session report to supervisor

**Broken Points**:
- Step 1: Activity access fails
- Step 2: Attendance system broken
- Step 5: Session recording fails

### Workflow 3: Letter Generation Process

**Participants**: Admin, Supervisor  
**Current Status**: ✅ Working (recently fixed)

1. **Template Setup**: Admin creates letter templates
2. **Letter Creation**: Supervisor generates letters
3. **Content Input**: Enter recipient and letter details
4. **Preview**: Review letter before generation
5. **Generation**: Create PDF letter
6. **Distribution**: Download and send letter

**Working Points**:
- ✅ Template management
- ✅ Letter generation (direct method)
- ✅ PDF download
- ⚠️ Preview still slow/broken

### Workflow 4: Center Asset Management

**Participants**: Supervisor, Admin, AJK  
**Current Status**: ❌ Broken

1. **Asset Registration**: Record new equipment/resources
2. **Location Tracking**: Assign assets to locations
3. **Maintenance Scheduling**: Set maintenance schedules
4. **Condition Monitoring**: Track asset condition
5. **Reporting**: Generate asset reports
6. **Disposal**: Handle end-of-life assets

**Broken Points**:
- Step 1: Asset page returns 404
- All subsequent steps unavailable

## Role-Based Feature Access Matrix

| Feature | Admin | Supervisor | Teacher | AJK |
|---------|-------|------------|---------|-----|
| System Dashboard | ✅ | ✅ | ✅ | ✅ |
| Center Management | ✅ | ❌ | ❌ | ❌ |
| User Management | ✅ | ⚠️ | ❌ | ❌ |
| Trainee Registration | ✅ | ✅ | ❌ | ✅ |
| Trainee Profiles | ✅ | ✅ | ✅ | ⚠️ |
| Activity Management | ✅ | ✅ | ✅ | ❌ |
| Session Management | ✅ | ✅ | ✅ | ❌ |
| Asset Management | ✅ | ✅ | ❌ | ❌ |
| Letter Generation | ✅ | ✅ | ❌ | ❌ |
| Reports | ✅ | ✅ | ⚠️ | ⚠️ |
| Notifications | ✅ | ✅ | ✅ | ✅ |

**Legend**:
- ✅ Full Access & Working
- ⚠️ Limited Access or Partially Working
- ❌ No Access or Broken

## Business Logic Requirements

### Data Validation Rules
1. **Trainees**: Ages 6-12, unique IC numbers, required guardian info
2. **Activities**: Capacity limits, qualified teacher assignment, appropriate categories
3. **Sessions**: Future dates only, no time conflicts, max participant limits
4. **Users**: Unique emails, strong passwords, valid role assignments

### Security Requirements
1. **Authentication**: Session-based with "remember me" option
2. **Authorization**: Role-based access control
3. **Data Isolation**: Users see only their center's data (except admin)
4. **Audit Trail**: Log all critical actions and changes

### Integration Requirements
1. **Email**: Password reset and notification emails
2. **PDF Generation**: Letter and report generation
3. **File Upload**: Profile pictures, document attachments
4. **Backup**: Regular data backup and recovery

## Success Metrics

### User Experience Metrics
- Login success rate: >99%
- Page load time: <3 seconds
- Feature availability: >95%
- User satisfaction: >4/5 rating

### Operational Metrics
- Trainee registration completion: <30 minutes
- Activity session recording: <5 minutes
- Letter generation: <2 minutes
- System uptime: >99.5%

### Data Quality Metrics
- Complete trainee profiles: >90%
- Session attendance accuracy: >95%
- Activity completion tracking: >90%
- Report generation success: >98%

This user story documentation provides comprehensive context for understanding how CREAMS should function when all issues are resolved.