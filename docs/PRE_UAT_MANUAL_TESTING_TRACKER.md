# PRE-UAT MANUAL TESTING TRACKER
## Comprehensive One-by-One Manual Verification Session

**Date Started:** October 22, 2025
**Tester:** Manual Testing Session
**Environment:** Local Development (Laragon)
**Database:** Fresh seed completed
**Base URL:** http://localhost/CREAMS

---

## 📋 TEST SESSION INFORMATION

### Fresh Database Stats:
- 👥 Users: 46 (real Gombak staff + demo users)
- 👶 Trainees: 126
- 🎯 Activities: 400
- 📅 Sessions: 9,186
- ✅ Attendance Records: 25,980
- 🏭 Assets: 130
- 📧 Messages: 275
- 📄 Letters: 273

### Test Credentials (Real Users with Most Activity):
```
Admin:      asbourne1998@gmail.com / Mifune1998@
            ruzita.abd.rahim@iium.edu.my / password123
Supervisor: nor.aisyah.muhamad.asri@iium.edu.my / password123
Teacher:    nabilah.ahmad@iium.edu.my / password123
AJK:        ahmad.zaki.mohamed@iium.edu.my / password123
```

**Note:** Most passwords are: `password123` (default seeder password)

---

## 🎯 TESTING METHODOLOGY

**For Each Test Case:**
1. ⏱️ **Manual Test** - Perform action in browser
2. ✅ **Record Result** - Pass/Fail/Issues
3. 🐛 **Note Issues** - Document any problems found
4. 🔧 **Fix If Needed** - Apply fixes immediately
5. ✔️ **Re-test** - Verify fix works
6. 📝 **Update Status** - Mark as completed

**Status Legend:**
- 🟢 **PASS** - Works perfectly
- 🟡 **PASS WITH NOTES** - Works but has minor issues
- 🔴 **FAIL** - Does not work, needs fixing
- ⏸️ **PENDING** - Not yet tested
- 🔧 **FIXING** - Currently being fixed
- ✅ **FIXED** - Was broken, now fixed

---

## PHASE 1: PUBLIC ACCESS MODULE (3 Tests)

### HOME001: Welcome/Landing Page Load and Verification
**Status:** 🟢 PASS
**URL:** `http://localhost/CREAMS`
**Expected:** Landing page loads with public information within 3 seconds

**Test Steps:**
1. Navigate to Application Homepage
2. Test Initial Page Load (within 3 seconds, no console errors, images load, CSS applied, responsive)
3. Test Welcome Page Content (title, logo, welcome message, navigation, footer)
4. Test Navigation Elements (About Us, Contact Us, Login button)
5. Test Performance (page load time, loading indicators, smooth scrolling)
6. Test Accessibility (keyboard navigation, screen reader, color contrast, zoom 150%/200%)

**Result:** The page loads correctly
**Issues:** Footer needs newsletter function placeholder (info@creams.org is temporary), social media links not created yet
**Fix:** Updated quick links in footer to follow topbar options, added more IIUM presence/links
**Retest:** ✅ Now working properly

---

### HOME002: Public Information Display
**Status:** 🟢 PASS
**URL:** `http://localhost/CREAMS`
**Expected:** All public info sections visible and accessible

**Test Steps:**
1. Navigate to About Us Section
2. Test Information Display (mission statement, program descriptions, contact info)
3. Test Content Accessibility (no authentication required, no sensitive data exposed)
4. Test Navigation (between sections, breadcrumbs, back to home)

**Result:** All public info sections visible
**Issues:** None noted
**Fix:** N/A
**Retest:** N/A

---

### CONTACT001: Contact Us Form Submission
**Status:** 🟢 PASS
**URL:** `http://localhost/CREAMS/contact`
**Expected:** Contact form submits successfully with proper validation

**Test Steps:**
1. Navigate to contact page and verify form loads
2. Test Form Display (required fields marked, labels clear, instructions provided)
3. Test Form Validation (empty form, invalid email, invalid phone, special characters)
4. Test Valid Form Submission with test data
5. Test Submission Confirmation (success message, confirmation email, form clears)
6. Test Error Handling (network disconnected, error message, data preserved)

**Test Data:**
```
Name: Test User
Email: faisalhanafi.dsa@gmail.com
Phone Number: +60123456789
Reason for contact: Volunteer Inquiry
Subject: UAT Test Contact
Priority Level: Urgent Priority
Message: This is a test message for UAT verification
```

**Result:** Contact form submits successfully
**Issues:** Font in top bar different from landing page; new paginated format not yet implemented (still old status message format)
**Fix:** Need to implement flash message format for all forms in system
**Retest:** Still not fixed but can be corrected later

---

## PHASE 2: AUTHENTICATION MODULE (5 Tests)

### AUTH001: Standard Login Functionality
**Status:** 🟢 PASS
**URL:** `http://localhost/CREAMS/login`
**Expected:** Admin login successful with proper security measures

**Test Steps:**
1. Navigate to Login Page
2. Test Login Form Display (username/email field, password masking, "Show Password" toggle, "Remember Me", "Forgot Password" link)
3. Test Login Validation (empty form, invalid email, whitespace, SQL injection protection, XSS protection)
4. Test Invalid Login Attempts (wrong password, invalid username, account lockout after 5 attempts, generic error messages)
5. Test Valid Login - Administrator
6. Test Valid Login - Teacher
7. Test Valid Login - Supervisor
8. Test Session Management (timeout after 30 minutes, "Remember Me" for 7 days, concurrent login handling)
9. Test Security Features (CSRF token, HTTPS, secure HttpOnly cookies, brute force protection)

**Credentials:**
```
Admin: asbourne1998@gmail.com / Mifune1998@
```

**Result:** Login works correctly
**Issues:** Error message says "No account found with this IIUM ID" - should say "No account found with this email or IIUM ID"; password is password123 not password for seeded users
**Fix:** Updated error message
**Retest:** ✅ Working correctly

---

### AUTH002: Enhanced Login with Multi-Factor Options
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/enhanced-login`
**Expected:** Enhanced login features work if implemented

**Test Steps:**
1. Navigate to Enhanced Login
2. Test Enhanced Features (password strength indicator, show/hide toggle, biometric login, QR code)
3. Test Two-Factor Authentication (2FA prompt, valid OTP, invalid OTP, OTP expiration)
4. Test Remember Device Feature (30 days device trust)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH003: User Registration Process
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/register`
**Expected:** Only admin can register new staff with proper validation

**Test Steps:**
1. Navigate to Registration Page (verify only admin can access)
2. Test Registration Form Display (required fields marked, field groupings logical, helper text, password requirements)
3. Test Form Validation (IIUM ID format, email uniqueness, password strength requirements, password confirmation)
4. Test Role Selection (Administrator, Teacher, Supervisor options)
5. Test Centre Assignment (dropdown populates, multiple centres available)
6. Test Valid Registration - Administrator with test data
7. Test Registration Success (success message, redirect to user list, user appears, welcome email)
8. Test Duplicate Prevention (existing IIUM ID, duplicate email, form preserves data)
9. Test Role-Based Registration (Teacher with limited permissions, Supervisor capabilities)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH004: Password Reset Flow
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/forgot-password`
**Expected:** Password reset process works securely

**Test Steps:**
1. Navigate to Forgot Password Page
2. Test Password Reset Request (enter email, verify success message, check email sent, token generated)
3. Test Reset Email (email received, reset link present, 24-hour expiration notice, correct formatting)
4. Test Reset Link Access (click link, redirect to reset page, token valid)
5. Test New Password Setting (enter new password, confirm password, password strength indicator)
6. Test Reset Success (confirmation message, redirect to login, new password works, old password fails)
7. Test Security Validations (expired token, used token, invalid token, rate limiting)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH005: Logout Functionality & Session Management
**Status:** 🟢 PASS
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** User logout successful and session properly destroyed

**Test Steps:**
1. Test Standard Logout (logout button, confirmation prompt, session destroyed, redirect)
2. Test Post-Logout Security (protected pages redirect to login, browser back doesn't restore session, cookie cleared)
3. Test Session Timeout Logout (30 minutes inactivity, automatic logout, timeout warning)
4. Test Normal Activity (session maintains for 10 minutes of use)
5. Test Idle Timeout (inactive for 30 minutes, redirect with timeout message)
6. Test Session Hijacking Protection (cookie security, prevent unauthorized access)
7. Test Concurrent Sessions (multiple browser login policy)
8. Test Remember Me feature (extended session duration)

**Result:** Works correctly - shows "Please log in to access this page" error when accessing protected pages after logout
**Issues:** None
**Fix:** N/A
**Retest:** N/A

---

## PHASE 3: DASHBOARD MODULE (4 Tests)

### DASH001: Admin Dashboard Display
**Status:** 🟡 PASS WITH NOTES
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Admin sees full statistics with accurate data

**Login:** asbourne1998@gmail.com / Mifune1998@

**Test Steps:**
1. Login as Administrator
2. Navigate to Dashboard
3. Test Dashboard Widgets (total users, trainees, activities, attendance, recent registrations, notifications)
4. Test Statistics Display (charts render correctly, data accuracy, chart interactions, date range filters)
5. Test Quick Actions Panel (Add Trainee, Create Activity, Generate Report, System Settings)
6. Test Recent Activity Feed (last 10 activities, timestamps, clickable links, real-time updates)
7. Test Navigation Sidebar (all menu items accessible, active highlighting, collapse/expand, submenus)
8. Test Role-Based Content (admin-only sections visible, sensitive data appropriate, all management options available)

**Expected Stats:**
```
Trainees: 126
Activities: 400
Sessions: 9,186
Attendance: 25,980
```

**Result:** Dashboard works but has several issues
**Issues:**
- Left navbar interactive (collapsible) ✅
- Search function not working - should search Trainee (name + disability + centre), User/Staff (name + role + centre), Activities (name + instructor + centre)
- Search should allow pressing Enter or clicking search icon to go to results page
- Notification bell shows placeholder message ✅
- Hero section confusing: Remove "Online" status, remove "18 Today 23 Centre Activities 98% Completion", move Date/Time to right, add weather+temperature API, specify "Gombak centre has 18 activity sessions scheduled today" + personal session count
- Recent activities showing 0 - needs activity log implementation with 1-minute batch updates + refresh button
- "Live Data" text should be before statistics, not after
- Mark attendance button issue: One user clicking marks attendance for ALL users instead of individual
- Quick actions same for all roles - should be role-specific
- AJK level should be same as supervisor, not lowest
- AJK missing staff module access in left navbar

**Fix:** Need to implement activity logging table that tracks all CRUD operations across system
**Retest:** _[After fixes applied]_

---

### DASH002: Supervisor Dashboard Display
**Status:** 🟡 PASS WITH NOTES
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Supervisor sees centre-level data

**Login:** nor.aisyah.muhamad.asri@iium.edu.my / password123

**Test Steps:**
1. Login as Supervisor
2. Verify centre statistics (centre-level data, staff overview, activity summary)
3. Check staff overview
4. Verify activity summary
5. Check centre-specific filters work

**Result:** Dashboard displays correctly for supervisor role
**Issues:** Mark attendance button changed to "already marked today" when user didn't mark it - system incorrectly marks for all users when one user marks attendance (should be per individual user)
**Fix:** Fix attendance marking to be per-user, not global
**Retest:** _[After fix]_

---

### DASH003: Teacher Dashboard Display
**Status:** 🟡 PASS WITH NOTES
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Teacher sees activity-focused view

**Login:** nabilah.ahmad@iium.edu.my / password123

**Test Steps:**
1. Login as Teacher
2. Verify "My Activities" widget (assigned activities only)
3. Check "Today's Schedule" (current day sessions)
4. Verify "My Trainees" count
5. Check "Pending Attendance" alerts
6. Test Limited Access (system admin hidden, only own classes, no financial data, no user management)
7. Test Class Management Shortcuts (Mark Attendance, View Schedule, Message Trainees)

**Result:** Dashboard displays
**Issues:** Quick actions same for all user roles - many users won't be able to create activities, need role-based quick action buttons. Only admin should have full access to every function.
**Fix:** Implement role-based quick actions
**Retest:** _[After fix]_

---

### DASH004: AJK Dashboard Display & Responsiveness
**Status:** 🔴 FAIL
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** AJK sees asset-focused view with proper access level

**Login:** ahmad.zaki.mohamed@iium.edu.my / password123

**Test Steps:**
1. Login as AJK
2. Verify asset statistics
3. Check maintenance alerts
4. Verify asset quick actions
5. Check centre data visible
6. Test on Desktop (1920x1080) - full layout, all widgets, sidebar proportions
7. Test on Tablet (768x1024) - responsive layout, collapsed sidebar, widget stacking
8. Test on Mobile (375x667) - mobile-optimized layout, touch controls, no horizontal scrolling

**Result:** Dashboard displays but with major issues
**Issues:**
- AJK access level is set to lowest when it should be same as supervisor
- AJK quick actions only show "View Schedule" and "Profile Settings" - need more options
- AJK left navbar missing staff module access - all staff should have view access to staff module (may not edit but can view)
**Fix:** Elevate AJK permissions to supervisor level, add staff module view access, expand quick actions
**Retest:** _[After fix]_

---

## PHASE 4: PROFILE MODULE (4 Tests)

### PROF001: View User Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/profile`
**Expected:** User can view their complete profile

**Test Steps:**
1. Navigate to Profile Page (click user avatar/name, select "My Profile")
2. Test Profile Display (IIUM ID read-only, full name, email, role, centre, profile image/avatar)
3. Test Profile Information Layout (sections clearly labeled, data formatted properly, responsive on different screens)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### PROF002: Edit Profile Information
**Status:** 🔧 FIXED - READY FOR RETEST
**URL:** `http://localhost/CREAMS/profile`
**Expected:** User can update editable profile fields

**Test Steps:**
1. Navigate to Profile Page and access edit mode
2. Test Edit Mode Activation (fields editable, IIUM ID disabled, edit/save buttons appear)
3. Test Profile Updates (update name, modify email, save changes)
4. Test Update Validation (invalid email format, required fields, special characters in name)
5. Test Update Success (success message, profile refreshes, edit mode deactivates, changes persist after logout/login)
6. Test Update Cancellation (click Cancel, changes discarded, original data restored)

**Result:** It works but no message of the operation status
**Issues:** Missing success/error flash message after profile update
**Fix:** ✅ Added toast notification component to display flash messages globally
**Retest:** _[Please retest to verify fix]_

---

### PROF003: Change Password
**Status:** 🔧 FIXED - READY FOR RETEST
**URL:** `http://localhost/CREAMS/profile`
**Expected:** Password change successful with proper validation

**Test Steps:**
1. Navigate to Profile Page password section
2. Test Password Change Form (current password field, new password field, confirm password field, strength indicator)
3. Test Password Validation (wrong current password, weak new password, mismatched passwords, requirements enforcement)
4. Test Successful Password Change (enter correct current, new password, confirm password, click Update)
5. Test Change Confirmation (success message, email notification, immediate logout, login with new password works, old password fails)

**Test Data:**
```
Current: password123
New: NewStrongPass789!
Confirm: NewStrongPass789!
```

**Result:** ✅ Password change works with real-time validation, but shows duplicate success messages
**Issues:**
- ✅ FIXED: No real-time password match validation (checking happens only after clicking submit button)
- ✅ FIXED: Password confirmation should check match on-the-spot as user types
- ✅ FIXED: Missing visual feedback for password match/mismatch before submission
- 🔴 STILL ISSUE: Success message appears TWICE - one in old format at top of content section, one in new toast format at top-right
- 🔴 Both duplicate messages show same text at same time
**Fix Applied:**
- ✅ Added real-time JavaScript validation with visual indicators (green checkmark when match, red X when mismatch)
- ✅ Removed duplicate showErrorAlert function from profile page
- ✅ Removed old session alert HTML from role-access-denied component
**Retest:** ⏸️ PENDING - Need to retest after changing workstation to verify duplicate message fix

---

### PROF004: Update Profile Image
**Status:** 🔧 FIXED - READY FOR RETEST
**URL:** `http://localhost/CREAMS/profile`
**Expected:** Profile photo upload successful

**Test Steps:**
1. Navigate to Profile Page image section
2. Test Image Upload (browse button, select JPG/PNG, preview displays, upload)
3. Test Image Validation (file size limit 2MB, unsupported formats rejected, oversized image handling, extension validation)
4. Test Upload Success (success message, new image appears, displays across all pages, properly resized/cropped)
5. Test Image Removal (Remove Photo option, confirmation prompt, default avatar displays)

**Result:** It works but page refresh required to see changes
**Issues:** Profile image upload successful but requires manual page refresh to display new image - should auto-refresh or update dynamically
**Fix:** ✅ Added automatic page reload after 1.5 seconds (shows success message first, then refreshes)
**Retest:** _[Please retest to verify fix]_

---

## PHASE 5: STAFF MODULE (6 Tests)

### STAFF001: Staff Listing and Search
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs`
**Expected:** View all staff with comprehensive search/filter

**Test Steps:**
1. Navigate to Staff Page
2. Test Initial Staff Display (loads within 2 seconds, table/grid format, pagination if >20, loading indicators)
3. Test Staff Information Display (IIUM ID, Name, Role, Email, Centre, status indicators, action buttons, profile images)
4. Test Search Functionality (by name partial matches, by IIUM ID, by email, special characters, real-time updates)
5. Test Filtering Options (by Role, by Centre, by Status, combined filters)
6. Test Sorting Options (by Name A-Z/Z-A, by IIUM ID, by Role, by Date Added)
7. Test Role-Based Access (Admin sees all with full management, Supervisor sees assigned teachers, Teacher no access)
8. Test Pagination (Next/Previous buttons, page numbers, items per page dropdown 10/25/50/100, total count accuracy)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### STAFF002: Add New Staff Member
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/register`
**Expected:** New staff created successfully

**Test Steps:**
1. Navigate to Add Staff Page
2. Test Form Completion (follow AUTH003 registration process)
3. Test Post-Creation Actions (new staff appears in list, welcome email sent, immediate login capability)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### STAFF003: View Staff Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/{id}`
**Expected:** Complete staff profile displays

**Test Steps:**
1. From Staff List, Click "View" on a Staff Member
2. Test Profile Display (all staff information, employment details, assigned activities for teachers, supervision hierarchy for supervisors, performance metrics)
3. Test Related Information (assigned classes/trainees, attendance records, activity history, documents)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### STAFF004: Edit Staff Information
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/{id}/edit`
**Expected:** Staff information updated successfully

**Test Steps:**
1. Navigate to Staff Edit Page
2. Test Editable Fields (update name, modify email, change role admin-only, reassign centre, update status Active/Inactive)
3. Test Edit Restrictions (IIUM ID cannot change, supervisors can only edit assigned teachers, self-edit limitations)
4. Test Edit Validation (required fields, email format, duplicate email detection, role change permissions)
5. Test Update Success (save changes, success message, updates reflect in system, audit trail logged)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### STAFF005: Delete/Deactivate Staff
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/{id}`
**Expected:** Staff deletion/deactivation handled properly

**Test Steps:**
1. Navigate to Staff Profile
2. Test Deletion Warning (confirmation dialog, warning about dependent data, cancellation option)
3. Test Soft Delete (deactivation - status changes to Inactive, removed from active lists, data preserved)
4. Test Hard Delete (permanent deletion, staff removed, dependent data handling: activities reassignment, attendance preserved, historical data maintained)
5. Test Deletion Restrictions (cannot delete self, admin approval required, protection against accidental deletion)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### STAFF006: Teacher Assignment Management
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/staffs/{id}/assignments`
**Expected:** Teacher activity assignments managed properly

**Test Steps:**
1. Navigate to Teacher Profile
2. Test Activity Assignment (assign activity button, select from dropdown, set start/end dates, save)
3. Test Assignment Display (assigned activities list, schedule conflicts highlighted, assignment history)
4. Test Assignment Removal (remove assignment button, confirmation prompt, impact on sessions, trainee notifications)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 6: TRAINEES MODULE (6 Tests)

### TRAIN001: Trainee Listing and Comprehensive Search
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees`
**Expected:** View 126 trainees with comprehensive search

**Test Steps:**
1. Navigate to Trainees Page
2. Test Initial Page Load (loads within 3 seconds, grid/list view, pagination if >20, loading indicators)
3. Test Trainee Display Information (ID, Name, Age, Status, Guardian Contact, disability info if authorized, enrollment status, profile images)
4. Test Search Functionality (by trainee name full/partial, by unique identifier, by guardian name, by phone, special characters, highlight matches)
5. Test Advanced Filtering (by Status, by Age Range 0-5/6-12/13-18/18+, by Centre, by Disability Type, by Enrollment Status, combined filters)
6. Test Sorting Options (by Name A-Z/Z-A, by Age youngest/oldest, by Registration Date, by Last Activity Date)
7. Test Role-Based Viewing (Admin sees all with management, Supervisor sees assigned centres, Teacher sees enrolled trainees, unauthorized restrictions)
8. Test Performance with Large Dataset (100+ records, pagination performance, search performance, filter performance, export CSV/PDF)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN002: Register New Trainee
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/register`
**Expected:** New trainee registered successfully

**Test Steps:**
1. Navigate to Trainee Registration Page
2. Test Registration Form Sections (Personal Info, Guardian/Parent Info, Emergency Contact, Medical/Disability Info, Centre Assignment)
3. Test Personal Information Entry (First/Last Name, DOB, Gender, unique identifier)
4. Test Guardian Information (Name, Relationship, Phone, Email, Address)
5. Test Emergency Contact (Name, Phone different from guardian, at least one contact required)
6. Test Medical Information Entry (Disability/Condition Type, Detailed Description, Medications, Allergies, Special Requirements, medical documents upload)
7. Test Centre Assignment (centre dropdown, only accessible centres shown, required field validation)
8. Test Form Validation (empty form errors, invalid phone format, future DOB error, age calculation, email format)
9. Test Successful Registration (complete all fields, upload photo optional, click Register, success message, unique identifier generated, appears in system, welcome packet)
10. Test Post-Registration Actions (guardian notification, profile accessible, immediately enroll in activities)

**Test Data:**
```
First Name: UAT Test
Last Name: Trainee
IC: 100101-01-5678
DOB: 2010-01-01
Gender: Male
Centre: Gombak
Guardian: UAT Guardian
Phone: 0123456789
Email: uat.trainee@test.com
Status: Active
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN003: Edit Trainee Information
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/{id}/edit`
**Expected:** Trainee information updated successfully

**Test Steps:**
1. Navigate to Trainee Edit Page
2. Test Editable Information (update personal details, modify guardian info, update emergency contacts, update medical info, change centre assignment)
3. Test Update Validation (required fields cannot be cleared, email/phone format, date validation, duplicate identifier detection)
4. Test Update Restrictions (unique identifier cannot change, authorization for medical info updates, audit trail for sensitive changes)
5. Test Update Success (save changes, success message, updates reflect immediately, affected parties notified, change history logged)

**Test Data:**
```
New Guardian Phone: 0111111111
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN004: View Trainee Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}`
**Expected:** Complete trainee profile displays

**Test Steps:**
1. Click "View" on trainee
2. Verify sections display: Personal Info, Guardian Info, Activities, Attendance
3. Check data accuracy

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN005: Manage Trainee Status
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/{id}`
**Expected:** Status updates successfully

**Test Steps:**
1. Navigate to Trainee Profile status management
2. Test Status Options (Active - currently enrolled, Inactive - temporarily not attending, Graduated - completed program, Withdrawn - left program)
3. Test Status Change Process (select new status, enter reason required, effective date, add notes optional)
4. Test Status Change Validation (confirmation required, impact warning for enrollments, cannot set future-dated status)
5. Test Status Change Effects (enrollment status updates, activity participation adjusted, notification to guardians, reports reflect change, reactivation process)

**Test Steps:**
1. Select trainee
2. Change Active → Inactive
3. Save and verify
4. Change back to Active

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN006: Trainee Document Management
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}/documents`
**Expected:** Document upload and management works

**Test Steps:**
1. Navigate to Trainee Documents Section
2. Test Document Upload (click Upload, select type: Medical/Legal/Academic/Other, choose file PDF/DOC/JPG/PNG, enter title, add description, upload)
3. Test Upload Validation (file size limit 10MB, file type restrictions, virus scanning if implemented, upload progress indicator)
4. Test Document Access (view document list, download document, preview document, access logging)
5. Test Document Management (edit metadata, replace with newer version, delete document, version history if applicable)
6. Test Authorization (only authorized users access sensitive docs, guardian access to non-medical documents, audit trail for document access)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 7: ACTIVITIES MODULE (10 Tests)

### ACT001: Comprehensive Activity Listing and Filtering
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities`
**Expected:** View all activities with comprehensive filtering

**Test Steps:**
1. Navigate to Activities Page
2. Test Initial Page Load (loads within 3 seconds, grid/list view, pagination if >20, loading indicators)
3. Test Activity Display Information (Name, Description, Category, Status, Instructor, images, enrollment counts, session schedules, View Details links)
4. Test Filtering Functionality (by Category: Therapy/Education/Recreation/Life Skills/Sports/Arts, by Status: Active/Inactive/Scheduled/Completed/Cancelled, by Centre, by Instructor, by Day of Week, by Time Slot: Morning/Afternoon/Evening, combined filters)
5. Test Search Functionality (by activity name partial/full, by description keywords, by instructor name, by category, special characters, highlight matches)
6. Test Sorting Options (by Name A-Z/Z-A, by Date Created newest/oldest, by Enrollment Count high/low, by Category alphabetically, by Next Session Date)
7. Test Role-Based Viewing (Admin sees all with management, Supervisor sees assigned centres, Teacher sees assigned activities with limited options, Trainee/Guardian sees available for enrollment, unauthorized restrictions)
8. Test Performance with Large Dataset (100+ activities, pagination performance, search performance, filter combinations, smooth scrolling)
9. Test Activity Cards/List Items (thumbnail images, enrollment capacity "5/10", quick action buttons, status badges clear and visible)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT002: Create New Activity
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities/create`
**Expected:** New activity created successfully

**Test Steps:**
1. Navigate to Create Activity Page
2. Test Activity Basic Information (enter Name, select Category, enter Description, upload Image, select Centre)
3. Test Activity Details (enter Duration 60 minutes, set Capacity 10 trainees, enter Room/Location, select Required Equipment/Assets, set Age Range 6-12 years)
4. Test Instructor Assignment (select Primary Instructor, assign Co-instructor optional, verify only qualified staff shown)
5. Test Schedule Creation - Recurring (select Days of Week, set Start/End Time, enter Start/End Date) OR One-time (select specific date, set time)
6. Test Form Validation (required fields empty, capacity 0, end date before start date, schedule conflict detection, instructor availability check)
7. Test Activity Creation Success (complete all fields, click Create, success message, appears in activities list, instructor notified, available for enrollment)
8. Test Activity Preview (before creating, click Preview, verify displays as trainees will see it, check formatting)

**Test Data:**
```
Activity Name: Art Therapy Session
Category: Therapy
Description: Detailed activity description
Duration: 60 minutes
Capacity: 10 trainees
Room/Location: Room A
Age Range: 6-12 years
Days: Monday, Wednesday
Time: 10:00 AM - 11:00 AM
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT003: Edit Activity Details
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities/{id}/edit`
**Expected:** Activity details updated successfully

**Test Steps:**
1. Navigate to Activity Edit Page
2. Test Editable Fields (update name, modify description, change category, update capacity, modify duration, change location)
3. Test Instructor Reassignment (change primary instructor, verify conflict checking, check enrolled trainee notifications)
4. Test Schedule Modification (update session times, modify recurring pattern, change dates)
5. Test Edit with Enrollments (warning for changes affecting enrollments, capacity reduction validation, notification to affected trainees, instructor change notifications)
6. Test Edit Restrictions (cannot reduce capacity below current enrollments, cannot delete past sessions, authorization requirements for certain changes)
7. Test Update Success (save all changes, success message, updates reflect across system, change history logged, notifications sent)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT004: Delete Activity
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities/{id}`
**Expected:** Activity deletion handled properly

**Test Steps:**
1. Navigate to Activity Details Page
2. Test Deletion Warning (comprehensive warning, list of enrolled trainees shown, scheduled sessions count, attendance records warning, cancellation option)
3. Test Deletion with No Enrollments (confirm deletion, activity removed, all related data cleaned up)
4. Test Deletion with Active Enrollments (prevented or requires additional confirmation, option to Archive instead, notification to enrolled trainees, refund process if applicable)
5. Test Archive Option (Archive instead of delete, activity marked as archived, removed from active lists, historical data preserved, reports still accessible)
6. Test Cascade Delete Protection (attendance records preserved, trainee enrollment history maintained, progress reports remain accessible)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT005: Activity Categories View
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities`
**Expected:** Category filtering works properly

**Test Steps:**
1. Navigate to Activities Page
2. Test Category Filtering (click category, verify only that category displays, count matches results)
3. Test Multiple Categories (select different category, verify filtering updates, test category switching speed)
4. Test Category Display (all categories listed: Therapy/Education/Recreation/Life Skills/Sports/Arts & Crafts/Music/Physical Therapy/Occupational Therapy, activity count per category, "All Categories" option)
5. Test Category Management Admin (add new category, edit category name, delete unused category, test category icon/color customization)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT006: Create Activity Session
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities/{id}/sessions/create`
**Expected:** New session scheduled successfully

**Test Steps:**
1. Navigate to Activity Details Page, click "Schedule New Session"
2. Test Session Scheduling (select Date, set Start Time 2:00 PM, set End Time 3:00 PM, select Instructor from activity instructors, set Room/Location, enter Session Notes)
3. Test Session Validation (date not in past, instructor availability, room availability, schedule conflict detection, within activity parameters)
4. Test Session Creation (click Create Session, success message, session appears in schedule, enrolled trainees notified, instructor receives notification)
5. Test Recurring Session (create multiple sessions at once, select date range, set recurring pattern daily/weekly, verify all sessions created correctly)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT007: Enroll Trainee in Activity
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/activities/{id}/enroll`
**Expected:** Trainee enrollment successful

**Test Steps:**
1. Navigate to Activity Details Page, click "Enroll Trainee"
2. Test Enrollment Form (search and select trainee, verify eligibility: age range match/no schedule conflicts/available capacity, enter enrollment date, add enrollment notes)
3. Test Capacity Validation (attempt enrollment when full, verify waitlist option offered, capacity warning displays)
4. Test Schedule Conflict Check (enroll trainee already in conflicting activity, verify warning displays, test override option admin only)
5. Test Enrollment Success (complete enrollment, success message, trainee added to participant list, guardian notification sent, enrollment confirmation generated)
6. Test Waitlist Enrollment (when activity full add to waitlist, verify waitlist position displayed, test automatic promotion when spot opens)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT008: View Activity Schedule
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/schedule`
**Expected:** Activity schedule displays correctly

**Test Steps:**
1. Navigate to Schedule Page
2. Test Calendar Display (current month/week displays, all scheduled sessions appear, color-coding by category, session details on hover)
3. Test Date Range Selection (select specific range, week view, month view, day view, all sessions in range display)
4. Test Schedule Filtering (filter by instructor, by activity type, by centre, by trainee shows trainee's schedule)
5. Test Session Details (click on session, popup with full details, enrolled trainees list, attendance marking option, session notes display)
6. Test Schedule Navigation (navigate to previous/next week/month, "Today" button, date picker functionality)
7. Test Schedule Export (export to PDF, export to Calendar iCal, verify accuracy)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT009: Weekly Schedule View
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/schedule/week`
**Expected:** Weekly schedule layout works properly

**Test Steps:**
1. Navigate to Weekly View
2. Test Week Layout (Monday-Sunday columns, time slots 8 AM - 6 PM, session blocks positioned correctly, overlapping sessions handled)
3. Test Week Navigation (move to previous week, move to next week, jump to specific week, current week highlighting)
4. Test Session Display (session titles visible, time displayed, instructor name shown, enrolled count displays)
5. Test Interactive Features (click session for details, drag and drop to reschedule if supported, quick actions: mark attendance/cancel)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ACT010: Teacher Personal Schedule
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/schedule/my-schedule`
**Expected:** Teacher sees only assigned sessions

**Login:** Teacher credentials

**Test Steps:**
1. Login as Teacher, navigate to "My Schedule"
2. Test Personal Schedule Display (only assigned sessions displayed, current day highlighted, upcoming sessions listed, past sessions accessible)
3. Test Session Preparation (view session details, check enrolled trainee list, access trainee profiles, view lesson plans if applicable, test material checklist)
4. Test Quick Actions (mark attendance from schedule, add session notes, report issues, request schedule changes)
5. Test Schedule Notifications (upcoming session reminders, schedule change alerts, cancellation notifications)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 8: ATTENDANCE MODULE (4 Tests)

### ATT001: Mark Attendance for Session
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/attendance/mark`
**Expected:** Attendance marking works properly

**Test Steps:**
1. Navigate to Attendance Page, select activity session, click "Mark Attendance"
2. Test Attendance Interface (enrolled trainee list displays, default status "Not Marked", quick mark all options)
3. Test Attendance Marking (mark as Present, Absent, Late, Excused, add notes for absent/late trainees)
4. Test Bulk Actions ("Mark All Present" button, "Mark All Absent" button, undo functionality)
5. Test Attendance Validation (cannot mark future sessions, warning for marking very old sessions, cannot mark twice without override)
6. Test Attendance Submission (click Submit Attendance, success confirmation, attendance reflected in reports, notification to guardians for absences)
7. Test Late Attendance (mark attendance after session ended, verify warning displays, test requires explanation note)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ATT002: View Attendance Reports
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/reports/attendance`
**Expected:** Attendance reports accurate and comprehensive

**Test Steps:**
1. Navigate to Attendance Reports
2. Test Individual Trainee Report (select trainee, choose date range, generate report, verify summary: total sessions/present/absent/late counts/attendance percentage)
3. Test Activity Attendance Report (select activity, choose date range, generate report, session-by-session breakdown, average attendance rate)
4. Test Centre-Wide Report (select centre, generate overall report, aggregated statistics, comparison across activities)
5. Test Report Visualization (view charts: line/bar/pie, check trend analysis, test date range filtering, verify data accuracy)
6. Test Report Export (export to PDF, export to Excel/CSV, verify formatting preserved, check all data included)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ATT003: Attendance History and Trends
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}/attendance`
**Expected:** Attendance trends and patterns visible

**Test Steps:**
1. Navigate to Trainee Profile, access attendance history tab
2. Test History Display (chronological listing, filter by activity, date range selection, view patterns)
3. Test Trend Analysis (attendance rate over time, monthly comparisons, visualization graphs, identify attendance issues)
4. Test Alerts (low attendance warning below 75%, consecutive absence alerts, pattern recognition, generate intervention recommendations)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ATT004: Excuse Management
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/attendance/{id}/excuse`
**Expected:** Excused absences handled properly

**Test Steps:**
1. Navigate to Attendance Record, select absent record, click "Add Excuse"
2. Test Excuse Entry (enter reason: Illness/Family/Appointment, upload supporting document medical cert, set excuse date, submit excuse)
3. Test Excuse Approval (admin/supervisor notification, approval workflow, excuse status updates, rejection with reason)
4. Test Excused Absence Handling (excused absences marked differently, attendance percentage calculation, reporting of excused vs unexcused)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 9: CENTRES MODULE (4 Tests)

### CENT001: Centre Listing
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/centres`
**Expected:** All centres listed with accurate information

**Test Steps:**
1. Navigate to Centres Page
2. Test Centre Display (all centres listed, details: Name/Location/Capacity/Status, statistics: total staff/trainees/active activities, contact information)
3. Test Centre Filtering (filter by Status: Active/Inactive, filter by Region/District, search by name)
4. Test Centre Actions (view details, edit centre, view centre dashboard, access centre reports)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CENT002: Create New Centre
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/centres/create`
**Expected:** New centre created successfully

**Test Steps:**
1. Navigate to Create Centre Page
2. Test Centre Information Entry (enter Name, Registration Number, Address, City/District, State, Postal Code, Contact Phone, Contact Email)
3. Test Facility Information (enter Total Capacity, Number of Rooms, set Operating Hours, list Available Facilities)
4. Test Form Validation (required field validation, email format check, phone number validation, unique centre name check)
5. Test Centre Creation (submit form, success message, centre appears in list, immediate accessibility)

**Test Data:**
```
Centre Name: Test Rehabilitation Centre
Registration Number: RC2025001
Address: 123 Test Street
City: Gombak
State: Selangor
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CENT003: Edit Centre Information
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/centres/{id}/edit`
**Expected:** Centre information updated successfully

**Test Steps:**
1. Navigate to Centre Edit Page
2. Test Information Updates (update contact information, modify capacity, change operating hours, update facilities list)
3. Test Update Validation (cannot reduce capacity below current usage, verify contact info validation, test change notifications)
4. Test Update Success (save changes, verify updates reflected, check audit trail)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CENT004: Centre Dashboard View
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/centres/{id}/dashboard`
**Expected:** Centre-specific dashboard shows accurate data

**Test Steps:**
1. Navigate to Specific Centre Dashboard
2. Test Centre Statistics (total staff assigned, total trainees enrolled, active activities count, capacity utilization, attendance rate)
3. Test Centre Calendar (view centre schedule, check room utilization, test conflict detection)
4. Test Centre Reports (generate centre performance report, view attendance trends, check enrollment statistics, test financial reports if applicable)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 10: ASSETS MODULE (6 Tests)

### ASSET001: Asset Inventory Listing
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets`
**Expected:** All assets listed with proper categorization

**Test Steps:**
1. Navigate to Assets Page
2. Test Asset Display (all assets listed, details: Product Name/Code/Category/Brand/Quantity/Status, asset images display, location information)
3. Test Asset Search (search by asset name, by asset code, by category, barcode search)
4. Test Asset Filtering (filter by Category: Books/Furniture/Equipment/Electronics/Toys/Educational, by Status: Available/In Use/Maintenance/Damaged/Lost, by Location/Centre, combined filters)
5. Test Sorting Options (sort by Name, by Quantity low to high, by Date Added, by Last Used)
6. Test Asset Status Indicators (Available green badge, In Use blue badge, Maintenance yellow badge, Damaged red badge, verify status colors clear)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ASSET002: Add New Asset
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets/create`
**Expected:** New asset added to inventory

**Test Steps:**
1. Navigate to Add Asset Page
2. Test Asset Information Entry (enter Product Name, Asset Code or auto-generate, select Category, enter Brand Name, enter Price, enter Quantity, enter Description/Notes)
3. Test Asset Location (select Centre, enter Room/Area, set Default Location)
4. Test Asset Documentation (upload asset image, attach purchase receipt, add warranty information, enter purchase date)
5. Test Form Validation (required fields check, unique code validation, quantity must be positive, price format validation)
6. Test Asset Creation (submit form, success message, asset appears in inventory, barcode generation if applicable)

**Test Data:**
```
Product Name: Wooden Chair
Asset Code: C1 (auto-generate)
Category: Furniture
Brand Name: Karu
Price: 50
Quantity: 13
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ASSET003: Edit Asset Details
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets/{id}/edit`
**Expected:** Asset information updated successfully

**Test Steps:**
1. Navigate to Asset Edit Page
2. Test Editable Information (update quantity, modify price, change category, update location, modify notes)
3. Test Quantity Adjustments (increase quantity new purchase, decrease quantity damaged/lost, verify quantity history logged)
4. Test Status Change (change Available to Maintenance, update status to Damaged, test status change notifications)
5. Test Update Validation (cannot set negative quantity, verify unique code maintained, test change approval for certain fields)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ASSET004: Delete Asset
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets/{id}`
**Expected:** Asset deletion handled properly

**Test Steps:**
1. Navigate to Asset Details, click "Delete"
2. Test Deletion Warning (warning for assets in use, usage history warning, cancellation option)
3. Test Deletion Restrictions (cannot delete asset currently in use, must return/resolve before deletion, admin approval required)
4. Test Deletion Success (confirm deletion, asset removed, usage history preserved)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ASSET005: Asset Assignment to Activity
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets/{id}/assign`
**Expected:** Assets assigned and tracked properly

**Test Steps:**
1. Navigate to Asset Page, select asset, click "Assign to Activity"
2. Test Assignment (select activity, enter quantity to assign, set assignment date, enter expected return date)
3. Test Assignment Validation (verify sufficient quantity available, check activity exists and is active, test date validation)
4. Test Assignment Tracking (asset status updates to "In Use", available quantity reduced, assignment appears in activity details)
5. Test Asset Return (mark asset as returned, verify quantity updated, check status returns to Available, test damage reporting during return)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### ASSET006: Asset Maintenance Tracking
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/assets/{id}/maintenance`
**Expected:** Maintenance scheduling and tracking works

**Test Steps:**
1. Navigate to Asset Details, access maintenance section
2. Test Maintenance Schedule (set maintenance date, enter type: Routine/Repair/Inspection, add maintenance notes, set next maintenance date)
3. Test Maintenance Recording (record maintenance performed, upload maintenance receipts, enter cost, update asset condition)
4. Test Maintenance Alerts (due maintenance notifications, overdue maintenance warnings, maintenance history display)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 11: LETTERS MODULE (3 Tests)

### LETT001: Letter Template Management
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/letters`
**Expected:** Letter templates managed properly

**Test Steps:**
1. Navigate to Letters Page
2. Test Template Listing (view all templates, categories: Welcome Letter/Enrollment Confirmation/Progress Report/Activity Certificate/Attendance Warning, template preview)
3. Test Template Creation (click Create Template, enter template name, select template type, design letter content, add placeholders {{trainee_name}}/{{activity_name}}/{{date}}, save template)
4. Test Template Editing (select template, modify content, update placeholders, save changes, version history)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### LETT002: Generate Letter from Template
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}/letters`
**Expected:** Letters generated accurately from templates

**Test Steps:**
1. Navigate to Trainee Profile or Activity, click "Generate Letter", select template
2. Test Letter Generation (template loads, placeholders auto-filled: trainee name/guardian name/activity details/dates/centre information, preview generated letter)
3. Test Letter Customization (edit auto-filled content if needed, add custom notes, modify formatting, add signature)
4. Test Letter Actions (print letter, download as PDF, email to guardian, save to trainee documents)
5. Test Bulk Generation (select multiple trainees, generate letters for all, verify accurate personalization, batch download/email)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### LETT003: Letter History and Tracking
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/letters/history`
**Expected:** Letter history tracked and accessible

**Test Steps:**
1. Navigate to Letter History
2. Test Letter Tracking (view delivery status, check opened/read status if email, verify sent date and time, resend functionality)
3. Test Letter Archive (search historical letters, filter by trainee, export letter history, access archived letters)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 12: MESSAGING MODULE (3 Tests)

### MSG001: Send Message to Guardian
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}/message`
**Expected:** Messages sent successfully to guardians

**Test Steps:**
1. Navigate to Trainee Profile, click "Send Message" or "Contact Guardian"
2. Test Message Composition (auto-fill guardian contact, select message type: SMS/Email/Both, enter subject for email, enter message content, attach files for email max 5MB)
3. Test Message Templates (select from pre-defined templates, customize template content, save as new template option)
4. Test Message Validation (required field checks, character limit for SMS 160 chars, email format validation, attachment size/type validation)
5. Test Message Sending (click Send, sending confirmation, delivery status, message logged in history)
6. Test Bulk Messaging (select multiple guardians, compose group message, verify personalization works, delivery to all recipients)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### MSG002: View Message History
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/messages`
**Expected:** Message history accessible and searchable

**Test Steps:**
1. Navigate to Messages Page
2. Test Message Inbox (view received messages if reply enabled, check sent messages, filter by status: Sent/Delivered/Read/Failed, search messages)
3. Test Message Thread (click on conversation, view full history with guardian, chronological order, download attachments)
4. Test Message Actions (reply to message, forward message, delete message, archive conversation)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### MSG003: Notification Settings
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/settings/notifications`
**Expected:** Notification preferences save properly

**Test Steps:**
1. Navigate to Notification Settings
2. Test Notification Types (email notifications, SMS notifications, in-app notifications, push notifications if mobile)
3. Test Notification Preferences (new enrollment, attendance alerts, schedule changes, low attendance warnings, system updates)
4. Test Notification Customization (enable/disable per type, set frequency: Immediate/Daily Digest/Weekly Summary, quiet hours setting, verify preferences save)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 13: SYSTEM MODULE (5 Tests)

### SYS001: User Role Management
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/settings/roles`
**Expected:** Roles and permissions managed properly

**Test Steps:**
1. Navigate to System Settings, access role management
2. Test Role Display (view all system roles: Administrator/Supervisor/Teacher/Trainee read-only, check permissions per role)
3. Test Role Permissions (view admin permissions full access, check supervisor permissions centre-level, view teacher permissions activity-level, test permission inheritance)
4. Test Permission Modification (select role, modify permissions, test permission conflicts, save changes, verify changes reflected)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### SYS002: System Backup and Restore
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/settings/backup`
**Expected:** Backup and restore functionality works

**Test Steps:**
1. Navigate to Backup Settings
2. Test Manual Backup (click "Create Backup Now", select scope: Full/Database Only/Files Only, verify progress indicator, completion message, backup download)
3. Test Automatic Backup (configure backup schedule, set frequency: Daily/Weekly, set retention: 7/30 days, verify schedule saves)
4. Test Backup List (view all backups, check backup size and date, test backup download, verify backup integrity check)
5. Test Restore Process (select backup to restore, verify warning message, confirm restore, test system recovery, verify data integrity post-restore)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### SYS003: Audit Log Review
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/logs`
**Expected:** All system activities logged and searchable

**Test Steps:**
1. Navigate to Audit Logs
2. Test Log Entries (view all system activities, check user actions logged, verify timestamps accurate, log detail view)
3. Test Log Filtering (filter by User, by Action: Create/Update/Delete/Login/Logout, by Module: Staff/Trainees/Activities, by Date Range)
4. Test Log Search (search by user name, by action type, by record ID, keyword search)
5. Test Log Export (export filtered logs, download as CSV, verify all data included, date range export)
6. Test Critical Actions (verify sensitive actions flagged: user deletion/permission changes/data exports/system settings modified)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### SYS004: System Performance Monitoring
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/settings/performance`
**Expected:** Performance metrics accessible

**Test Steps:**
1. Navigate to System Dashboard
2. Test Performance Metrics (view server uptime, check database size, monitor active users, response time graphs)
3. Test Resource Monitoring (CPU usage, memory usage, storage capacity, network bandwidth)
4. Test Performance Alerts (slow query alerts, high resource usage warnings, downtime notifications)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### SYS005: System Configuration
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/settings`
**Expected:** System settings configurable and save properly

**Test Steps:**
1. Navigate to System Settings
2. Test General Settings (organization name, system logo upload, contact information, operating hours, fiscal year settings)
3. Test Email Configuration (SMTP settings, from email address, test email functionality, email templates)
4. Test Security Settings (session timeout duration, password policy: minimum length/complexity/expiration/history, login attempt limits, IP whitelist if applicable)
5. Test Integration Settings (SMS gateway configuration, payment gateway if applicable, calendar integration, external APIs)
6. Test Settings Validation (required field checks, format validation, test connectivity, verify settings save)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 14: VOLUNTEER MODULE (2 Tests)

### VOL001: Volunteer Registration Form
**Status:** 🟡 PASS WITH NOTES
**URL:** `http://localhost/CREAMS/volunteer`
**Expected:** Volunteer can register successfully with 3-step form

**Test Steps:**
1. Navigate to volunteer registration page
2. Step 1 - Personal Information: Fill in all personal details, click Next
3. Step 2 - Volunteer Preferences: Select area of interest, choose availability, select time commitment, click Next
4. Step 3 - Additional Information: Fill motivation, check consent checkbox, submit form
5. Verify success message appears
6. Check confirmation email (log file)
7. Verify database entry created

**Test Data:**
```
=== STEP 1: Personal Information ===
First Name: Ahmad
Last Name: Volunteer
Email: faisalhanafi.dsa@gmail.com
Phone: +60123456789
Date of Birth: 1995-05-15
Gender: Male
Address: 123 Jalan Test, Taman UAT
City: Gombak
Postal Code: 53100
Emergency Contact Name: Fatimah Ahmad
Emergency Contact Phone: +60198765432

=== STEP 2: Volunteer Preferences ===
Area of Interest: Direct Support
Skills & Experience: Experience in teaching children, patient and caring
Availability: [✓] Weekdays (9am-5pm), [✓] Weekends
Time Commitment: 4-6 hours per week

=== STEP 3: Additional Information ===
Motivation: I want to help children with special needs develop their full potential
Previous Experience: Worked as assistant teacher for 1 year
How did you hear: IIUM PD-CARE Website
[✓] Consent to process personal information
```

**Result:** Forms work, page works
**Issues:** Success message disappears too fast - can't read it before it goes away; already changed to new flash message format (unlike contact form which still uses old status message format)
**Fix:** Increase success message display duration
**Retest:** _[After fix]_

---

### VOL002: Volunteer Form Validation
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/volunteer`
**Expected:** Validation prevents invalid submissions

**Test Steps:**
1. Submit with invalid IC format
2. Try invalid phone number
3. Try invalid email format
4. Verify validation messages

**Test Data (Invalid):**
```
IC: 12345
Phone: 123
Email: not-an-email
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 15: CROSS-FUNCTIONAL TESTS (5 Tests)

### CROSS001: Complete User Journey - New Trainee Enrollment
**Status:** ⏸️ PENDING
**Expected:** End-to-end trainee enrollment flow works

**Test Steps:**
1. Start at Homepage, access public website
2. Administrator Login (navigate to login, enter admin credentials, access dashboard)
3. Register New Trainee (navigate to trainee registration, complete all required information, upload documents, submit registration)
4. Enroll in Activity (search for suitable activity, enroll trainee, verify enrollment confirmation)
5. View Schedule (check trainee's schedule, verify activity appears, confirm dates and times)
6. Generate Welcome Letter (create welcome letter, email to guardian, save to trainee documents)
7. Verify Complete Flow (check trainee profile complete, verify all data accurate, test guardian notification received)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CROSS002: Data Consistency Across Modules
**Status:** ⏸️ PENDING
**Expected:** Data synchronizes correctly across all modules

**Test Steps:**
1. Create Test Scenario (add new trainee, enroll in multiple activities, mark attendance, generate reports)
2. Test Data Synchronization (verify trainee count updates in dashboard, check enrollment count in activity details, verify attendance reflected in reports, test real-time updates)
3. Test Data Modification Impact (edit trainee information, verify updates across all modules, check report accuracy, test historical data preserved)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CROSS003: Role-Based Access Control (RBAC)
**Status:** ⏸️ PENDING
**Expected:** Access properly restricted by role

**Test Steps:**
1. Test Administrator Access (login as admin, verify full system access, check all CRUD operations available, test system settings access)
2. Test Teacher Access (login as teacher, verify limited to assigned activities, check cannot access user management, test cannot modify system settings)
3. Test Supervisor Access (login as supervisor, verify centre-level access, check teacher management available, test report access for assigned centre)
4. Test Access Violations (attempt unauthorized direct URL access, verify redirect or error message, test API endpoint protection, check audit log records attempts)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CROSS004: System Integration Testing
**Status:** ⏸️ PENDING
**Expected:** All modules integrate properly

**Test Steps:**
1. Test Module Interactions (create activity affects schedule, mark attendance updates reports, modify trainee status affects enrollments, delete activity impacts trainees)
2. Test Notification Flow (activity created → instructor notified, trainee enrolled → guardian notified, attendance marked → updates sent, schedule changed → all parties notified)
3. Test Report Generation (activity report pulls attendance data, trainee report includes all modules, centre report aggregates all activities, system report includes audit logs)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CROSS005: Performance and Load Testing
**Status:** ⏸️ PENDING
**Expected:** System performs well under load

**Test Steps:**
1. Test with Multiple Concurrent Users (simulate 50 concurrent logins, test simultaneous form submissions, check database query performance, verify no data corruption)
2. Test Large Dataset Performance (load 500+ trainees, create 100+ activities, generate reports with full dataset, test search and filter performance)
3. Test Peak Usage Scenarios (attendance marking during class hours, report generation end of month, bulk enrollment at term start, verify system remains responsive)

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## 📊 CURRENT PROGRESS

**Total Test Cases:** 69
**Completed:** 9
**Passing:** 4
**Pass with Notes:** 5
**Failing:** 0
**Pending:** 60
**Pass Rate:** 100% (of completed tests - all tests working, some need improvements)

**By Phase:**
- Phase 1: Public Access (3/3 completed) - 100%
- Phase 2: Authentication (2/5 completed) - 40%
- Phase 3: Dashboards (4/4 completed) - 100%
- Phase 4: Profile (3/4 completed) - 75% ⬆️ NEW
- Phase 5: Staff (0/6 completed) - 0%
- Phase 6: Trainees (0/6 completed) - 0%
- Phase 7: Activities (0/10 completed) - 0%
- Phase 8: Attendance (0/4 completed) - 0%
- Phase 9: Centres (0/4 completed) - 0%
- Phase 10: Assets (0/6 completed) - 0%
- Phase 11: Letters (0/3 completed) - 0%
- Phase 12: Messaging (0/3 completed) - 0%
- Phase 13: System (0/5 completed) - 0%
- Phase 14: Volunteer (1/2 completed) - 50%
- Phase 15: Cross-Functional (0/5 completed) - 0%

---

## 🐛 ISSUES LOG

### Issue #1: Flash Message Format Inconsistency
- **Test Cases:** CONTACT001, VOL001
- **Description:** Contact form still uses old status message format, volunteer form uses new flash message format
- **Severity:** Medium
- **Status:** Open
- **Fix Details:** Need to implement consistent flash message format for all forms in the system

### Issue #2: Volunteer Success Message Too Fast
- **Test Case:** VOL001
- **Description:** Success message disappears before user can read it
- **Severity:** Low
- **Status:** Open
- **Fix Details:** Increase success message display duration to 5-7 seconds

### Issue #3: Search Function Not Working
- **Test Case:** DASH001
- **Description:** Global search not functional - should search Trainee (name + disability + centre), User/Staff (name + role + centre), Activities (name + instructor + centre)
- **Severity:** High
- **Status:** Open
- **Fix Details:** Implement global search with proper keyword matching and results page

### Issue #4: Hero Section Statistics Confusing
- **Test Case:** DASH001
- **Description:** Hero section shows confusing statistics and "Online" status
- **Severity:** Medium
- **Status:** Open
- **Fix Details:** Remove "Online" status, remove redundant statistics, move Date/Time to right, add weather API, specify centre-specific session counts + personal session counts

### Issue #5: Recent Activities Not Implemented
- **Test Case:** DASH001
- **Description:** Recent activities showing 0 - needs activity log implementation
- **Severity:** High
- **Status:** Open
- **Fix Details:** Create activity_logs table to track all CRUD operations across system, implement 1-minute batch updates with refresh button

### Issue #6: Mark Attendance Global Instead of Per-User
- **Test Case:** DASH001, DASH002
- **Description:** One user clicking "Mark Attendance" marks attendance for ALL users instead of individual user
- **Severity:** Critical
- **Status:** Open
- **Fix Details:** Fix attendance marking to be per-user, not global

### Issue #7: Quick Actions Not Role-Based
- **Test Case:** DASH003
- **Description:** Quick actions same for all user roles - should be role-specific
- **Severity:** Medium
- **Status:** Open
- **Fix Details:** Implement role-based quick action buttons

### Issue #8: AJK Permission Level Too Low
- **Test Case:** DASH004
- **Description:** AJK access level set to lowest when should be same as supervisor; missing staff module access
- **Severity:** High
- **Status:** Open
- **Fix Details:** Elevate AJK permissions to supervisor level, add staff module view access, expand quick actions

### Issue #9: Contact Page Font Inconsistency
- **Test Case:** CONTACT001
- **Description:** Font in top bar different from landing page
- **Severity:** Low
- **Status:** Open
- **Fix Details:** Standardize font across all public pages

### Issue #10: Login Error Message Inaccurate
- **Test Case:** AUTH001
- **Description:** Error says "No account found with this IIUM ID" should say "No account found with this email or IIUM ID"
- **Severity:** Low
- **Status:** Fixed
- **Fix Details:** Updated error message to be more accurate

### Issue #11: Profile Edit Missing Success Message
- **Test Case:** PROF002
- **Description:** Profile edit works but no success/error message shown after update
- **Severity:** Medium
- **Status:** Fixed
- **Fix Details:** Added toast notification component to profile page to display flash messages globally across all tabs

### Issue #12: Password Change No Real-Time Validation
- **Test Case:** PROF003
- **Description:** Password confirmation validation only happens on submit, not real-time as user types
- **Severity:** Medium
- **Status:** Fixed
- **Fix Details:** Added JavaScript real-time validation to check if new password and confirm password match as user types, with visual feedback (green checkmark when match, red X when mismatch)

### Issue #13: Profile Image Upload Requires Manual Refresh
- **Test Case:** PROF004
- **Description:** Profile image uploads successfully but requires manual page refresh to see new image
- **Severity:** Low
- **Status:** Fixed
- **Fix Details:** Added auto-reload after successful image upload with 1.5 second delay to show success message first

---

## 🔧 FIXES APPLIED

### Fix #1: Footer Quick Links Updated
- **Issue:** Footer quick links didn't match topbar options
- **Solution:** Updated footer to include IIUM presence and links
- **Files Modified:** resources/views/layouts/app.blade.php (footer section)
- **Verification:** ✅ Verified - footer now displays correctly

### Fix #2: Login Error Message Corrected
- **Issue:** Error message only mentioned IIUM ID when it accepts email too
- **Solution:** Updated error message to "No account found with this email or IIUM ID"
- **Files Modified:** app/Http/Controllers/Auth/LoginController.php
- **Verification:** ✅ Verified - error message now accurate

### Fix #3: Profile Edit Success Message Added
- **Issue:** Profile edit works but no success/error message displayed (Issue #11)
- **Solution:** Added toast notification component inclusion to profile page
- **Files Modified:** resources/views/profile/home.blade.php (line 704)
- **Changes:** Added `@include('components.toast-notifications')` after avatar form
- **Verification:** ⏳ Awaiting user re-test

### Fix #4: Real-Time Password Validation Implemented
- **Issue:** Password confirmation only validated on submit (Issue #12)
- **Solution:** Added real-time JavaScript validation for password matching with visual indicators
- **Files Modified:** resources/views/profile/home.blade.php (lines 970-975, 1752-1784)
- **Changes:**
  - Added match/mismatch indicators in HTML
  - Implemented `checkPasswordMatch()` function
  - Bound input/keyup events to trigger real-time validation
  - Green checkmark shown when passwords match
  - Red X shown when passwords don't match
- **Verification:** ⏳ Awaiting user re-test

### Fix #5: Profile Image Auto-Refresh Implemented
- **Issue:** Profile image upload required manual refresh (Issue #13)
- **Solution:** Added automatic page reload after successful upload with delay
- **Files Modified:** resources/views/profile/home.blade.php (lines 1430-1442)
- **Changes:**
  - Modified AJAX success handler to reload page after 1.5 seconds
  - Shows success message before reload
  - Clears edit mode before refresh
- **Verification:** ⏳ Awaiting user re-test

---

## 📝 TESTING NOTES

### General Observations:
- **Database Performance:** With 9,186 sessions and 25,980 attendance records, query performance is acceptable
- **User Experience:** Most forms work well, but flash messages need consistency
- **Role-Based Access:** Needs significant work - especially for AJK role and quick actions
- **Activity Logging:** Critical feature missing - no recent activities tracking implemented yet
- **Search Functionality:** Global search completely non-functional - high priority fix needed

### Recommended Priority Order for Fixes:
1. **Critical:** Issue #6 - Mark Attendance Global Bug (affects data integrity)
2. **High:** Issue #5 - Recent Activities Implementation (core feature missing)
3. **High:** Issue #3 - Search Function (core feature non-functional)
4. **High:** Issue #8 - AJK Permission Level (access control issue)
5. **Medium:** Issue #1 - Flash Message Format Consistency
6. **Medium:** Issue #4 - Hero Section Statistics
7. **Medium:** Issue #7 - Role-Based Quick Actions
8. **Medium:** Issue #11 - Profile Edit Missing Success Message (UX issue)
9. **Medium:** Issue #12 - Password Change No Real-Time Validation (UX improvement)
10. **Low:** Issue #2 - Volunteer Message Duration
11. **Low:** Issue #9 - Contact Page Font
12. **Low:** Issue #13 - Profile Image Upload Requires Manual Refresh (UX polish)

### Testing Environment Notes:
- Using Laragon local development environment
- Database: MySQL with real Gombak staff data + demo users
- Browser testing: Primarily Chrome (need to test Firefox, Safari, Edge)
- Responsive testing: Desktop only so far (need tablet and mobile testing)
- All passwords for seeded users: `password123`

---

## 📅 TESTING SCHEDULE

**Estimated Time per Test:** 5-10 minutes average
**Total Estimated Time:** 69 tests × 7.5 minutes = ~520 minutes (~9 hours)

**Recommended Daily Sessions:**
- Session 1 (2 hours): Complete Phases 4-5 (Profile + Staff modules)
- Session 2 (2 hours): Complete Phase 6 (Trainees module)
- Session 3 (2 hours): Complete Phase 7 (Activities module)
- Session 4 (2 hours): Complete Phases 8-9 (Attendance + Centres)
- Session 5 (2 hours): Complete Phases 10-12 (Assets + Letters + Messaging)
- Session 6 (1.5 hours): Complete Phases 13-14 (System + Volunteer completion)
- Session 7 (1.5 hours): Complete Phase 15 (Cross-functional tests)

---

**Last Updated:** October 22, 2025
**Ready For:** Continued Manual Testing
**Next Action:** Begin testing Phase 4 (PROFILE MODULE) - 4 tests

**Instructions for Tester:**
1. Continue from Phase 4 (Profile Module)
2. Complete each test one by one
3. Document ALL findings (pass or fail)
4. Report issues immediately for fixing
5. Re-test after each fix
6. Update progress tracker regularly
7. Move to next test only when current is complete
