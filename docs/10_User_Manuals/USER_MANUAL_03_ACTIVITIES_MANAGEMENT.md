# CREAMS User Manual: Activities Management Module

## 📖 Table of Contents
1. [Activities Overview](#activities-overview)
2. [Viewing Activities](#viewing-activities)
3. [Creating Activities (Admin Only)](#creating-activities-admin-only)
4. [Managing Activity Sessions](#managing-activity-sessions)
5. [Trainee Enrollment](#trainee-enrollment)
6. [Scheduling and Calendar](#scheduling-and-calendar)
7. [Activity Categories](#activity-categories)
8. [Reporting and Analytics](#reporting-and-analytics)
9. [Troubleshooting](#troubleshooting)

---

## 🎯 Activities Overview

### What are Activities in CREAMS?
Activities are the core programs and services offered by your rehabilitation centre. They include therapeutic sessions, educational programs, recreational activities, and life skills training designed to support trainee development and achievement of rehabilitation goals.

### Activity Types
CREAMS supports four main activity categories:
- **🏥 Therapy**: Physiotherapy, Occupational Therapy, Speech Therapy
- **📚 Education**: Academic support, Vocational training, Literacy programs
- **🎮 Recreation**: Sports, Arts & Crafts, Music, Social activities
- **🛠️ Life Skills**: Daily living skills, Job training, Independent living

*[MEDIA SPACE: Infographic showing the four activity categories with icons]*

### Role-Based Access
Activity management permissions vary by user role:
- **👑 Admin**: Full access - create, edit, delete activities and sessions
- **👨‍🏫 Teacher**: Manage assigned activities, enroll trainees, mark attendance
- **👥 Supervisor**: View all centre activities, generate reports
- **🏢 AJK**: View activities for facility planning
- **🎓 Trainee**: View enrolled activities and schedules

*[MEDIA SPACE: Role permission matrix table]*

---

## 👀 Viewing Activities

### Accessing the Activities Module
1. **Login to CREAMS** with your credentials
2. **Navigate to Activities** from the main menu
3. **Activities List** will display all activities you have access to view

*[MEDIA SPACE: Screenshot of main navigation showing Activities menu item]*

### Activities List Interface

#### Main Activities View
The activities list displays:
- **Activity Name**: Full name of the activity
- **Category**: Therapy, Education, Recreation, or Life Skills
- **Instructor**: Assigned teacher/therapist
- **Capacity**: Current enrollment / Maximum capacity
- **Status**: Active, Inactive, or Scheduled
- **Next Session**: Date and time of upcoming session

*[MEDIA SPACE: Screenshot of Activities List with labeled components]*

#### Filtering Activities
Use filters to find specific activities:

**Filter by Category**
1. **Click Category dropdown** at top of activities list
2. **Select desired category** (Therapy, Education, Recreation, Life Skills)
3. **Activities list updates** to show only selected category

*[MEDIA SPACE: Screenshot of category filter dropdown]*

**Filter by Status**
1. **Click Status dropdown**
2. **Choose status filter**:
   - **Active**: Currently running activities
   - **Inactive**: Temporarily suspended activities
   - **Scheduled**: Future activities not yet started
   - **All**: Display all activities

*[MEDIA SPACE: Screenshot of status filter options]*

**Filter by Instructor**
1. **Click Instructor dropdown**
2. **Select specific instructor** to view their activities
3. **Select "All Instructors"** to clear filter

*[MEDIA SPACE: Screenshot of instructor filter dropdown]*

#### Search Activities
**Quick Search Function**:
1. **Enter search terms** in the search box
2. **Search includes**:
   - Activity names
   - Instructor names
   - Activity descriptions
   - Keywords and tags
3. **Results update automatically** as you type

*[MEDIA SPACE: Screenshot of search box with search suggestions]*

### Activity Details View

#### Viewing Individual Activity Information
1. **Click on activity name** in the activities list
2. **Activity details page opens** showing comprehensive information

*[MEDIA SPACE: Screenshot of Activity Details page layout]*

#### Activity Information Sections

**Basic Information**
- **Activity Name**: Full official name
- **Description**: Detailed activity description and objectives
- **Category**: Primary category classification
- **Duration**: Standard session length
- **Prerequisites**: Requirements for participation

*[MEDIA SPACE: Screenshot of Basic Information section]*

**Scheduling Information**
- **Schedule Pattern**: Weekly/Daily recurring schedule
- **Session Times**: Start and end times
- **Location**: Room or facility location
- **Equipment Required**: Necessary materials and equipment

*[MEDIA SPACE: Screenshot of Scheduling Information section]*

**Enrollment Information**
- **Current Enrollment**: Number of enrolled trainees
- **Maximum Capacity**: Maximum number of participants
- **Waitlist**: Number of trainees waiting for enrollment
- **Enrollment Status**: Open, Full, or Closed

*[MEDIA SPACE: Screenshot of Enrollment Information section]*

**Instructor Information**
- **Primary Instructor**: Main teacher/therapist
- **Assistant Instructors**: Additional staff assigned
- **Contact Information**: Instructor contact details
- **Qualifications**: Relevant certifications and expertise

*[MEDIA SPACE: Screenshot of Instructor Information section]*

---

## ➕ Creating Activities (Admin Only)

### Prerequisites for Activity Creation
**Admin Access Required**: Only users with Admin role can create new activities.

### Step-by-Step Activity Creation

#### Step 1: Access Activity Creation
1. **Login as Admin** user
2. **Navigate to Activities** module
3. **Click "Create New Activity"** button
4. **Activity creation form opens**

*[MEDIA SPACE: Screenshot of Create New Activity button and form opening]*

#### Step 2: Enter Basic Information

**Activity Name**
- **Enter descriptive name** for the activity
- **Use clear, professional terminology**
- **Avoid abbreviations** that may be unclear
- **Example**: "Advanced Physiotherapy Sessions" not "Adv PT"

*[MEDIA SPACE: Screenshot of Activity Name field with example]*

**Activity Description**
- **Provide comprehensive description** of the activity
- **Include objectives and goals**
- **Describe target participants**
- **Mention expected outcomes**
- **Character limit**: 500 characters

*[MEDIA SPACE: Screenshot of Description field with character counter]*

**Activity Category**
1. **Select primary category** from dropdown:
   - **Therapy**: Medical and therapeutic interventions
   - **Education**: Learning and academic support
   - **Recreation**: Social and recreational activities
   - **Life Skills**: Practical daily living skills
2. **Category determines** available subcategories and settings

*[MEDIA SPACE: Screenshot of Category selection dropdown]*

#### Step 3: Configure Session Details

**Session Duration**
- **Set standard session length** (15-minute increments)
- **Consider setup and cleanup time**
- **Common durations**: 30min, 45min, 60min, 90min
- **Format**: HH:MM (e.g., 01:30 for 90 minutes)

*[MEDIA SPACE: Screenshot of Duration time picker]*

**Maximum Participants**
- **Set maximum capacity** for safety and effectiveness
- **Consider**:
  - Room size and layout
  - Equipment availability
  - Instructor-to-participant ratio
  - Safety requirements

*[MEDIA SPACE: Screenshot of Capacity field with guidelines]*

**Age Group**
Select appropriate age range:
- **Children (5-12)**
- **Adolescents (13-17)**
- **Adults (18-64)**
- **Seniors (65+)**
- **All Ages**: Multi-generational activities

*[MEDIA SPACE: Screenshot of Age Group selection]*

#### Step 4: Assignment and Logistics

**Instructor Assignment**
1. **Select Primary Instructor** from dropdown list
2. **Choose qualified staff** based on activity requirements
3. **Add Assistant Instructors** if needed
4. **Verify instructor availability** for planned schedule

*[MEDIA SPACE: Screenshot of Instructor selection with availability indicator]*

**Centre Assignment**
1. **Select operating centre** for the activity
2. **Multi-centre activities** require special configuration
3. **Centre determines**:
   - Available instructors
   - Facility resources
   - Local scheduling constraints

*[MEDIA SPACE: Screenshot of Centre selection dropdown]*

**Equipment and Resources**
- **List required equipment** and materials
- **Specify room requirements** (size, accessibility, special features)
- **Note any safety equipment** needed
- **Include setup requirements**

*[MEDIA SPACE: Screenshot of Equipment requirements field]*

#### Step 5: Prerequisites and Requirements

**Participant Prerequisites**
- **Medical clearances** required
- **Previous activity completion** requirements
- **Assessment scores** or skill levels
- **Age or physical requirements**

*[MEDIA SPACE: Screenshot of Prerequisites section]*

**Special Considerations**
- **Accessibility accommodations**
- **Medical considerations** and contraindications
- **Safety protocols** and emergency procedures
- **Family involvement** requirements

*[MEDIA SPACE: Screenshot of Special Considerations section]*

#### Step 6: Review and Create

**Final Review**
1. **Review all entered information** for accuracy
2. **Check spelling and grammar**
3. **Verify instructor assignments**
4. **Confirm capacity and duration**

**Submit Activity**
1. **Click "Create Activity"** button
2. **Confirmation message** appears
3. **Activity appears** in activities list
4. **Ready for session scheduling**

*[MEDIA SPACE: Screenshot of final review screen and create button]*

### Activity Creation Best Practices

#### Naming Conventions
- **Use descriptive, professional names**
- **Include level indicators** (Beginner, Intermediate, Advanced)
- **Specify age groups** if relevant
- **Be consistent** with existing activity names

#### Description Guidelines
- **Start with main objective**
- **List key benefits for participants**
- **Describe typical session structure**
- **Include progression expectations**
- **Mention family involvement** if applicable

#### Capacity Planning
- **Consider optimal learning environment**
- **Account for individual attention needs**
- **Plan for equipment sharing**
- **Allow for different ability levels**
- **Include safety margins**

*[MEDIA SPACE: Infographic with best practices tips]*

---

## 📅 Managing Activity Sessions

### Session Scheduling Overview
Once an activity is created, you need to schedule specific sessions when the activity will take place.

### Creating Activity Sessions

#### Access Session Management
1. **Go to Activities List**
2. **Click on activity name** to view details
3. **Click "Manage Sessions"** tab or button
4. **Session management interface opens**

*[MEDIA SPACE: Screenshot of Activity Details with Manage Sessions button]*

#### Single Session Creation

**Step 1: Basic Session Information**
- **Session Date**: Select specific date for the session
- **Start Time**: Session start time
- **End Time**: Automatically calculated based on activity duration
- **Session Title**: Optional custom title (defaults to activity name + date)

*[MEDIA SPACE: Screenshot of single session creation form]*

**Step 2: Session Configuration**
- **Instructor**: Confirm or change instructor for this session
- **Room/Location**: Specify exact location
- **Maximum Participants**: Override activity default if needed
- **Special Notes**: Any session-specific information

*[MEDIA SPACE: Screenshot of session configuration options]*

**Step 3: Save Session**
1. **Review session details**
2. **Click "Create Session"**
3. **Session appears in schedule**
4. **Available for trainee enrollment**

#### Recurring Session Creation

**Step 1: Access Recurring Scheduler**
1. **Click "Create Recurring Sessions"** button
2. **Recurring session wizard opens**
3. **Choose recurrence pattern**

*[MEDIA SPACE: Screenshot of recurring session creation button]*

**Step 2: Set Recurrence Pattern**

**Daily Recurrence**
- **Every weekday** (Monday-Friday)
- **Every day** including weekends
- **Specific days** of the week
- **Custom day intervals** (every 2 days, etc.)

*[MEDIA SPACE: Screenshot of daily recurrence options]*

**Weekly Recurrence**
- **Same day each week** (Every Monday)
- **Multiple days per week** (Monday, Wednesday, Friday)
- **Bi-weekly patterns** (Every other Tuesday)
- **Custom week intervals**

*[MEDIA SPACE: Screenshot of weekly recurrence settings]*

**Monthly Recurrence**
- **Same date each month** (15th of every month)
- **Same weekday** (First Monday of every month)
- **Multiple dates** per month
- **Custom month intervals**

*[MEDIA SPACE: Screenshot of monthly recurrence options]*

**Step 3: Set Duration and Limits**
- **Start Date**: First session date
- **End Date**: Last session date (optional)
- **Number of Sessions**: Create specific number of sessions
- **Skip Holidays**: Automatically exclude public holidays

*[MEDIA SPACE: Screenshot of duration and limits settings]*

**Step 4: Review and Create**
1. **Preview generated sessions** in calendar view
2. **Modify individual sessions** if needed
3. **Confirm creation** of all sessions
4. **Sessions added to schedule**

*[MEDIA SPACE: Screenshot of session preview and confirmation]*

### Managing Existing Sessions

#### Session List View
View all sessions for an activity:
- **Chronological listing** of all scheduled sessions
- **Session status** indicators (Scheduled, In Progress, Completed, Cancelled)
- **Enrollment counts** for each session
- **Quick action buttons** for common tasks

*[MEDIA SPACE: Screenshot of session list view with status indicators]*

#### Editing Sessions

**Edit Single Session**
1. **Click edit icon** next to session
2. **Modify session details**:
   - Date and time
   - Instructor
   - Location
   - Capacity
   - Special notes
3. **Save changes**

*[MEDIA SPACE: Screenshot of session edit interface]*

**Bulk Session Editing**
1. **Select multiple sessions** using checkboxes
2. **Choose bulk action**:
   - Change instructor
   - Update location
   - Modify capacity
   - Add notes
3. **Apply changes** to selected sessions

*[MEDIA SPACE: Screenshot of bulk editing interface]*

#### Cancelling Sessions

**Cancel Single Session**
1. **Click cancel button** for specific session
2. **Select cancellation reason**:
   - Instructor unavailable
   - Equipment maintenance
   - Holiday/closure
   - Low enrollment
   - Other (specify)
3. **Confirm cancellation**
4. **Enrolled trainees notified automatically**

*[MEDIA SPACE: Screenshot of session cancellation dialog]*

**Cancel Multiple Sessions**
1. **Select sessions** to cancel
2. **Click "Bulk Cancel"**
3. **Choose reason** for cancellations
4. **Confirm bulk cancellation**
5. **All affected trainees notified**

*[MEDIA SPACE: Screenshot of bulk cancellation interface]*

#### Rescheduling Sessions

**Reschedule Process**
1. **Click reschedule icon** for session
2. **Select new date and time**
3. **Check for conflicts** with:
   - Instructor availability
   - Room availability
   - Trainee schedules
4. **Confirm reschedule**
5. **Notifications sent** to all participants

*[MEDIA SPACE: Screenshot of reschedule interface with conflict checking]*

---

## 👥 Trainee Enrollment

### Enrollment Overview
Trainee enrollment is the process of registering trainees for specific activities and sessions.

### Viewing Current Enrollments

#### Activity Enrollment List
1. **Go to Activity Details** page
2. **Click "Enrollments" tab**
3. **View all enrolled trainees** for the activity

*[MEDIA SPACE: Screenshot of Activity Enrollments tab]*

#### Enrollment Information Display
For each enrolled trainee:
- **Trainee Name** and ID
- **Enrollment Date**
- **Attendance Rate** for this activity
- **Last Attended Session**
- **Status** (Active, On Hold, Dropped)

*[MEDIA SPACE: Screenshot of enrollment list with trainee information]*

### Enrolling Trainees

#### Individual Enrollment

**Step 1: Access Enrollment**
1. **Go to Activity Details**
2. **Click "Enroll Trainee"** button
3. **Enrollment form opens**

*[MEDIA SPACE: Screenshot of Enroll Trainee button]*

**Step 2: Select Trainee**
1. **Search for trainee** by name or ID
2. **Select trainee** from search results
3. **Verify trainee eligibility**:
   - Age requirements
   - Prerequisites met
   - No schedule conflicts

*[MEDIA SPACE: Screenshot of trainee search and selection]*

**Step 3: Configure Enrollment**
- **Enrollment Date**: Date enrollment begins
- **Priority Level**: High, Medium, Low (for waitlist)
- **Special Accommodations**: Any special needs
- **Parent/Guardian Consent**: Verification of consent

*[MEDIA SPACE: Screenshot of enrollment configuration form]*

**Step 4: Confirm Enrollment**
1. **Review enrollment details**
2. **Click "Enroll Trainee"**
3. **Enrollment confirmation** appears
4. **Trainee added** to activity roster

*[MEDIA SPACE: Screenshot of enrollment confirmation]*

#### Bulk Enrollment

**Step 1: Access Bulk Enrollment**
1. **Click "Bulk Enroll"** button
2. **Bulk enrollment interface opens**

*[MEDIA SPACE: Screenshot of Bulk Enroll button]*

**Step 2: Select Multiple Trainees**
1. **Use trainee selection interface**:
   - **Search and filter** trainees
   - **Check boxes** for multiple selection
   - **Select all** in filtered results
2. **Review selected trainees** list

*[MEDIA SPACE: Screenshot of multiple trainee selection interface]*

**Step 3: Batch Enrollment Configuration**
- **Common enrollment date** for all trainees
- **Batch priority setting**
- **Group accommodations**
- **Bulk consent verification**

*[MEDIA SPACE: Screenshot of batch enrollment settings]*

**Step 4: Process Bulk Enrollment**
1. **Review all selections**
2. **Click "Enroll All Selected"**
3. **Processing progress** shown
4. **Enrollment summary** displayed

*[MEDIA SPACE: Screenshot of bulk enrollment progress and summary]*

### Managing Enrollments

#### Enrollment Status Management

**Active Enrollment**
- **Trainee regularly attends** sessions
- **Full access** to activity benefits
- **Included in planning** and capacity calculations

**On Hold Enrollment**
- **Temporary suspension** of participation
- **Maintains enrollment slot** for return
- **Common reasons**: Medical leave, family circumstances

**Dropped Enrollment**
- **Permanent withdrawal** from activity
- **Frees up enrollment slot** for waitlisted trainees
- **Requires documentation** of reason

*[MEDIA SPACE: Diagram showing enrollment status flow]*

#### Changing Enrollment Status
1. **Go to trainee's enrollment record**
2. **Click "Change Status"**
3. **Select new status**:
   - Active → On Hold
   - On Hold → Active
   - Active/On Hold → Dropped
4. **Enter reason** for status change
5. **Confirm change**

*[MEDIA SPACE: Screenshot of status change interface]*

#### Waitlist Management

**Adding to Waitlist**
When activity is at capacity:
1. **Attempt enrollment** shows "Activity Full"
2. **Option to add** to waitlist appears
3. **Select "Add to Waitlist"**
4. **Trainee added** with priority ranking

*[MEDIA SPACE: Screenshot of waitlist addition dialog]*

**Waitlist Prioritization**
Waitlist position determined by:
- **Enrollment request date** (first come, first served)
- **Priority level** (High, Medium, Low)
- **Special circumstances** (admin discretion)
- **Referral source** (medical referral priority)

**Automatic Enrollment from Waitlist**
When space becomes available:
1. **System automatically notifies** next trainee on waitlist
2. **48-hour response window** for acceptance
3. **Automatic enrollment** upon acceptance
4. **Next trainee notified** if declined

*[MEDIA SPACE: Screenshot of automatic waitlist notification]*

---

## 📊 Scheduling and Calendar

### Calendar Views

#### Monthly Calendar View
**Access Monthly Calendar**:
1. **Navigate to Activities** module
2. **Click "Calendar"** tab
3. **Monthly view** displays all scheduled sessions

*[MEDIA SPACE: Screenshot of monthly calendar with activity sessions]*

**Calendar Features**:
- **Color coding** by activity category
- **Session details** on hover
- **Click to view** session details
- **Navigation arrows** for different months

#### Weekly Calendar View
**Switch to Weekly View**:
1. **Click "Week"** button in calendar
2. **Detailed weekly schedule** with time slots
3. **Better view** for daily planning

*[MEDIA SPACE: Screenshot of weekly calendar view]*

**Weekly View Features**:
- **Time slot grid** (8 AM - 6 PM)
- **Multiple activities** visible per time slot
- **Instructor assignments** shown
- **Room/location** information

#### Daily Calendar View
**Access Daily View**:
1. **Click "Day"** button in calendar
2. **Hour-by-hour** session schedule
3. **Detailed session** information

*[MEDIA SPACE: Screenshot of daily calendar view]*

### Schedule Conflict Detection

#### Automatic Conflict Checking
The system automatically checks for conflicts when scheduling:

**Instructor Conflicts**
- **Double-booking prevention**: Same instructor, same time
- **Travel time consideration**: Time between sessions at different locations
- **Availability verification**: Instructor work schedule limits

**Room/Facility Conflicts**
- **Room capacity**: Ensure room can accommodate activity
- **Equipment conflicts**: Shared equipment scheduling
- **Maintenance windows**: Scheduled maintenance periods

**Trainee Schedule Conflicts**
- **Multiple enrollment**: Same trainee in different activities
- **Session overlap**: Conflicting session times
- **Transportation time**: Travel between locations

*[MEDIA SPACE: Diagram showing conflict detection system]*

#### Conflict Resolution

**Conflict Notification**
When conflicts detected:
1. **Warning message** appears during scheduling
2. **Conflict details** clearly described
3. **Suggested alternatives** provided
4. **Override options** for authorized users

*[MEDIA SPACE: Screenshot of conflict warning dialog]*

**Resolution Options**:
- **Change session time** to avoid conflict
- **Assign different instructor**
- **Use alternative room/location**
- **Split session** into smaller groups
- **Admin override** for emergency situations

### Resource Planning

#### Instructor Workload Management
**View Instructor Schedules**:
1. **Go to Calendar view**
2. **Filter by instructor**
3. **Review workload distribution**

*[MEDIA SPACE: Screenshot of instructor schedule filter]*

**Workload Indicators**:
- **Daily hour limits**: Maximum hours per day
- **Weekly hour limits**: Maximum hours per week
- **Consecutive session limits**: Break requirements
- **Overtime alerts**: Excessive workload warnings

#### Room Utilization Tracking
**Facility Usage Reports**:
- **Room occupancy rates**
- **Peak usage times**
- **Underutilized spaces**
- **Maintenance scheduling** opportunities

*[MEDIA SPACE: Screenshot of room utilization dashboard]*

#### Equipment Scheduling
**Shared Equipment Management**:
- **Equipment calendar** showing usage
- **Booking conflicts** prevention
- **Maintenance schedules** integration
- **Setup/cleanup time** allocation

*[MEDIA SPACE: Screenshot of equipment scheduling interface]*

---

## 📂 Activity Categories

### Understanding Categories

#### Therapy Activities
**Therapeutic intervention activities** designed for rehabilitation:

**Physical Therapy**
- **Mobility improvement**: Walking, balance, coordination
- **Strength building**: Resistance training, exercise therapy
- **Pain management**: Therapeutic exercises, modalities
- **Adaptive equipment**: Training with assistive devices

*[MEDIA SPACE: Image collage of physical therapy activities]*

**Occupational Therapy**
- **Daily living skills**: Cooking, cleaning, personal care
- **Fine motor skills**: Hand therapy, dexterity training
- **Cognitive rehabilitation**: Memory, attention, problem-solving
- **Workplace preparation**: Job skills, ergonomics

**Speech Therapy**
- **Communication skills**: Speech, language development
- **Swallowing therapy**: Dysphagia treatment
- **Cognitive-communication**: After brain injury
- **Assistive technology**: Communication devices

*[MEDIA SPACE: Video thumbnail of speech therapy session]*

#### Education Activities
**Learning and academic support programs**:

**Academic Support**
- **Literacy programs**: Reading, writing skills
- **Numeracy training**: Math skills, financial literacy
- **Computer skills**: Digital literacy, adaptive technology
- **Study skills**: Organization, time management

**Vocational Training**
- **Job preparation**: Resume writing, interview skills
- **Specific trade skills**: Carpentry, food service, clerical
- **Workplace behavior**: Professional communication
- **Career exploration**: Interest assessment, job shadowing

*[MEDIA SPACE: Screenshot of vocational training resources]*

#### Recreation Activities
**Social and recreational programs** for engagement and enjoyment:

**Physical Recreation**
- **Adaptive sports**: Wheelchair basketball, swimming
- **Fitness programs**: Exercise classes, strength training
- **Outdoor activities**: Gardening, nature walks
- **Team sports**: Modified team games

**Creative Arts**
- **Art therapy**: Painting, drawing, crafts
- **Music therapy**: Singing, instrument playing
- **Drama therapy**: Role-playing, storytelling
- **Dance/movement**: Adaptive dance, movement therapy

*[MEDIA SPACE: Gallery of creative arts activities]*

**Social Activities**
- **Group games**: Board games, card games
- **Community outings**: Shopping, restaurants, events
- **Social skills training**: Communication, friendship
- **Holiday celebrations**: Cultural and seasonal events

#### Life Skills Activities
**Practical skills** for independent living:

**Daily Living Skills**
- **Personal care**: Hygiene, grooming, dressing
- **Household management**: Cleaning, organizing, maintenance
- **Cooking skills**: Meal planning, food preparation, nutrition
- **Money management**: Banking, budgeting, shopping

**Independent Living**
- **Transportation training**: Public transit, ride services
- **Community navigation**: Finding resources, asking for help
- **Safety skills**: Emergency procedures, personal safety
- **Technology use**: Smartphones, computers, apps

*[MEDIA SPACE: Step-by-step cooking skills demonstration]*

### Category-Specific Features

#### Category Templates
Each category has **pre-configured templates** for quick activity creation:
- **Standard duration** recommendations
- **Typical capacity** limits
- **Required qualifications** for instructors
- **Common equipment** lists
- **Safety protocols** specific to category

*[MEDIA SPACE: Screenshot of category template selection]*

#### Category Reporting
**Category-specific analytics** and reports:
- **Participation rates** by category
- **Outcome measurements** relevant to category
- **Cost-effectiveness** analysis
- **Benchmarking** against standards

*[MEDIA SPACE: Chart showing category participation rates]*

#### Category Certification Requirements
Different categories may require **specific instructor certifications**:
- **Therapy activities**: Licensed therapists
- **Medical activities**: Healthcare professionals
- **Education activities**: Teaching qualifications
- **Recreation activities**: Activity leadership certification

---

## 📈 Reporting and Analytics

### Activity Performance Reports

#### Enrollment Reports
**Track enrollment trends** and patterns:

**Enrollment Summary Report**
- **Total enrollments** by time period
- **Category breakdown** of enrollments
- **Enrollment vs. capacity** utilization
- **Waitlist statistics** and trends

*[MEDIA SPACE: Screenshot of enrollment summary report]*

**Individual Activity Reports**
- **Enrollment history** for specific activities
- **Demographic breakdown** of participants
- **Retention rates** and drop-out analysis
- **Seasonal patterns** in enrollment

*[MEDIA SPACE: Chart showing enrollment trends over time]*

#### Attendance Analytics
**Monitor attendance patterns** and identify issues:

**Overall Attendance Rates**
- **System-wide attendance** percentages
- **Category comparison** of attendance
- **Instructor comparison** of rates
- **Time-based analysis** (daily, weekly, monthly)

*[MEDIA SPACE: Dashboard showing attendance analytics]*

**Individual Trainee Attendance**
- **Personal attendance** tracking
- **Pattern identification**: Days/times with poor attendance
- **Intervention indicators**: When to provide support
- **Progress correlation**: Attendance vs. outcomes

#### Instructor Performance Reports
**Evaluate teaching effectiveness** and workload:

**Instructor Workload Analysis**
- **Total hours** taught per instructor
- **Activity distribution** across instructors
- **Capacity utilization** by instructor
- **Student-to-instructor** ratios

*[MEDIA SPACE: Instructor workload comparison chart]*

**Teaching Effectiveness Metrics**
- **Student attendance** in instructor's sessions
- **Student progress** rates
- **Student satisfaction** scores
- **Peer evaluation** results

### Resource Utilization Reports

#### Facility Usage Analysis
**Optimize space and resource allocation**:

**Room Utilization Reports**
- **Occupancy rates** by room
- **Peak usage times** identification
- **Underutilized spaces** highlighting
- **Maintenance impact** on usage

*[MEDIA SPACE: Heatmap showing room utilization patterns]*

**Equipment Usage Tracking**
- **Equipment utilization** rates
- **Maintenance scheduling** optimization
- **Replacement planning** data
- **Cost-per-use** analysis

#### Cost-Effectiveness Analysis
**Financial performance** of activities:

**Cost per Participant**
- **Direct costs**: Instructor salaries, materials
- **Indirect costs**: Facility, utilities, administration
- **Revenue analysis**: Funding, fees, grants
- **Cost trends** over time

*[MEDIA SPACE: Cost analysis dashboard]*

**Return on Investment (ROI)**
- **Outcome achievement** vs. cost
- **Long-term benefits** measurement
- **Comparison** with external benchmarks
- **Funding justification** data

### Custom Reports

#### Report Builder
**Create customized reports** for specific needs:

**Report Configuration**
1. **Select data sources**: Activities, enrollments, attendance
2. **Choose time periods**: Custom date ranges
3. **Set filters**: Categories, instructors, centres
4. **Select metrics**: Counts, percentages, averages

*[MEDIA SPACE: Screenshot of report builder interface]*

**Output Options**
- **Dashboard view**: Interactive online display
- **PDF export**: Formatted document
- **Excel export**: Data for further analysis
- **Email delivery**: Scheduled report distribution

#### Scheduled Reporting
**Automate regular report generation**:

**Report Scheduling**
- **Daily reports**: Attendance summaries
- **Weekly reports**: Enrollment updates
- **Monthly reports**: Performance analytics
- **Quarterly reports**: Comprehensive analysis

*[MEDIA SPACE: Screenshot of report scheduling interface]*

**Distribution Lists**
- **Role-based distribution**: Reports to appropriate users
- **Supervisor notifications**: Team performance updates
- **Admin summaries**: System-wide statistics
- **External reporting**: Regulatory requirements

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Cannot Create New Activity
**Problem**: "Create Activity" button is not visible or clickable

**Solutions**:
1. **Check user role**: Only Admin users can create activities
2. **Verify permissions**: Contact admin if you should have access
3. **Browser issues**: Try refreshing page or different browser
4. **System maintenance**: Check if system is under maintenance

*[MEDIA SPACE: Screenshot showing admin vs. non-admin view differences]*

#### Activity Sessions Not Displaying
**Problem**: Scheduled sessions are not showing in calendar

**Solutions**:
1. **Check date range**: Ensure calendar is showing correct time period
2. **Verify filters**: Clear any active filters that might hide sessions
3. **Refresh page**: Force page reload to get latest data
4. **Check activity status**: Inactive activities may not show sessions

#### Enrollment Errors
**Problem**: Cannot enroll trainee in activity

**Common Causes and Solutions**:

**Activity at Capacity**
- **Solution**: Add trainee to waitlist or increase activity capacity

**Schedule Conflicts**
- **Solution**: Check trainee's schedule for conflicting activities

**Prerequisites Not Met**
- **Solution**: Verify trainee meets all activity requirements

**System Error**
- **Solution**: Try again later or contact technical support

*[MEDIA SPACE: Error message examples with solutions]*

#### Calendar Synchronization Issues
**Problem**: Changes not appearing immediately in calendar

**Solutions**:
1. **Manual refresh**: Click refresh button in calendar view
2. **Clear browser cache**: Reset stored data
3. **Check network connection**: Ensure stable internet
4. **Wait for sync**: Some changes may take a few minutes

#### Instructor Assignment Problems
**Problem**: Cannot assign instructor to activity

**Possible Causes**:
- **Instructor not qualified** for activity type
- **Schedule conflicts** with other assignments
- **Maximum workload** reached for instructor
- **Centre assignment** mismatch

**Solutions**:
- **Verify qualifications** match activity requirements
- **Check instructor schedule** for conflicts
- **Review workload limits** and adjust as needed
- **Ensure instructor assigned** to correct centre

*[MEDIA SPACE: Instructor assignment troubleshooting flowchart]*

### Performance Issues

#### Slow Loading Activity Lists
**Causes**:
- **Large number of activities** in system
- **Complex filtering** operations
- **Network connectivity** issues
- **Browser performance** problems

**Solutions**:
1. **Use filters**: Narrow down displayed activities
2. **Clear browser cache**: Remove stored data
3. **Close unnecessary tabs**: Free up browser memory
4. **Contact IT support**: For persistent performance issues

#### Mobile Access Problems
**Solutions for mobile users**:
1. **Use mobile-optimized browser**
2. **Enable JavaScript** on mobile device
3. **Check mobile data/WiFi** connection
4. **Try landscape orientation** for better view

*[MEDIA SPACE: Mobile interface examples]*

### Getting Help

#### Built-in Help Resources
- **Tooltips**: Hover over ? icons for quick help
- **Help documentation**: Comprehensive guides
- **Video tutorials**: Step-by-step instructions
- **FAQ section**: Common questions and answers

#### Contact Support
- **Technical Support**: For system issues and errors
- **Training Support**: For help using features
- **Administrative Support**: For permissions and access
- **User Community**: Peer support and tips

*[MEDIA SPACE: Support contact information display]*

---

## 📚 Additional Resources

### Training Materials
- **Video Library**: Complete tutorial series
- **Quick Start Guides**: Essential tasks overview
- **Best Practices**: Proven strategies for success
- **Advanced Features**: Power user techniques

### Documentation
- **User Manuals**: Detailed operation guides
- **Technical Documentation**: System specifications
- **Policy Documents**: Organizational guidelines
- **Release Notes**: System updates and changes

### Community Resources
- **User Forums**: Discussion and support
- **Success Stories**: Implementation examples
- **Tips and Tricks**: User-contributed advice
- **Feature Requests**: Suggest improvements

---

*Last Updated: [Date]
Version: 1.0
Document Type: User Manual - Activities Management Module*

**Note**: This manual includes placeholder spaces marked as *[MEDIA SPACE: Description]* where screenshots, diagrams, videos, and other visual aids should be inserted to provide comprehensive visual guidance for users at all skill levels.