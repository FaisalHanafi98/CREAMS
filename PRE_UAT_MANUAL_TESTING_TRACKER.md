# PRE-UAT MANUAL TESTING TRACKER
## One-by-One Manual Verification Session

**Date Started:** October 13, 2025 22:15
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
Admin:      ruzita.abd..rahim@iium.edu.my / password
Supervisor: nor.aisyah.muhamad.asri@iium.edu.my / password
Teacher:    nabilah.ahmad@iium.edu.my / password
AJK:        ahmad.zaki.mohamed@iium.edu.my / password
```

**Note:** All passwords are: `password` (default seeder password)

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

## PHASE 1: PUBLIC ACCESS (6 Tests)

### HOME001: Welcome/Landing Page Load
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS`
**Expected:** Landing page loads with public information

**Test Steps:**
1. Navigate to base URL
2. Verify page loads without errors
3. Check that public content displays
4. Verify navigation menu visible

**Result:** The pagge loads correctly 
**Issues:** Regarding the footer since we palnned to make a newsletter function in the future the current status is ok, for now we had not made the official email hence info@creams.org is a ggood placeholder, the same goes to the social media pages we did not create them yet gor now
**Fix:** In the quick links option in the footer section it supposes to follow the topbar option, i also think we need to include iium presence more, and maybe create a link where the users can go to check for iium details 
**Retest:** Yeap now it goods
---

### HOME002: Public Information Display
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS`
**Expected:** All public info sections visible

**Test Steps:**
1. Check "About Us" section
2. Verify "Services" section
3. Check "Contact Info" section
4. Verify images load correctly

**Result:** All public info sections visible
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### CONTACT001: Contact Us Form Submission
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/contact`
**Expected:** Contact form submits successfully

**Test Steps:**
1. Navigate to contact page
2. Fill form: Name, Email, Subject, Message
3. Submit form
4. Verify success message
5. Check database for new record

**Test Data:**
```
Name: Test User
Email: faisalhanafi.dsa@gmail.com my own email as i would like to see how is the email sent to me looks like
Phone Number: +60123456789
Reason for contact: Volunteer Inquiry
Subject: UAT Test Contact
Pririty Level: Urgent Pririty
Message: This is a test message for UAT verification
```

**Result:** Contact form submits successfully
**Issues:** _[Any problems]_
**Fix:** Although the contact form is working, i noticed that the font of the top bar is different this page if compared to the landing page or am i just imagining things?
**Retest:** Still not fixed but can be corrected later

---

### CONTACT002: Contact Form Validation
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/contact`
**Expected:** Form validation prevents invalid submissions

**Test Steps:**
1. Try submitting empty form
2. Try invalid email format
3. Try missing required fields
4. Verify error messages display

**Test Data (Invalid):**
```
Name: (empty)
Email: invalid-email
Subject: (empty)
```

**Result:**  Form validation prevents invalid submissions
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### VOL001: Volunteer Registration Form
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/volunteer`
**Expected:** Volunteer can register successfully with 3-step form

**Test Steps:**
1. Navigate to volunteer registration page
2. **Step 1 - Personal Information:**
   - Fill in all personal details
   - Click Next
3. **Step 2 - Volunteer Preferences:**
   - Select area of interest
   - Choose availability
   - Select time commitment
   - Click Next
4. **Step 3 - Additional Information:**
   - Fill motivation
   - Check consent checkbox
   - Submit form
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
Motivation: I want to help children with special needs develop their full potential and contribute to the community
Previous Experience: Worked as assistant teacher for 1 year
How did you hear: IIUM PD-CARE Website
[✓] Consent to process personal information
```

**Expected Database Fields:**
- volunteer_id: Auto-generated (VOL0001, VOL0002, etc.)
- name: "Ahmad Volunteer"
- email: volunteer.uat@test.com
- phone: +60123456789
- address: 123 Jalan Test, Taman UAT
- date_of_birth: 1995-05-15
- gender: Male
- skills: Experience in teaching children, patient and caring
- availability: "weekday, weekend"
- status: applied
- motivation: [full text]

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
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

## PHASE 2: AUTHENTICATION (5 Tests)

### AUTH001: Admin Login
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/login`
**Expected:** Admin login successful

**Credentials:**
```
Email: ruzita.abd..rahim@iium.edu.my
Password: password
```

**Test Steps:**
1. Navigate to login page
2. Enter credentials
3. Submit
4. Verify redirect to dashboard
5. Check role-based menu

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH002: Supervisor Login
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/login`
**Expected:** Supervisor login successful

**Credentials:**
```
Email: nor.aisyah.muhamad.asri@iium.edu.my
Password: password
```

**Test Steps:**
1. Logout previous user
2. Enter supervisor credentials
3. Verify dashboard access
4. Check supervisor-specific features

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH003: Teacher Login
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/login`
**Expected:** Teacher login successful

**Credentials:**
```
Email: nabilah.ahmad@iium.edu.my
Password: password
```

**Test Steps:**
1. Logout previous user
2. Enter teacher credentials
3. Verify activity-focused dashboard
4. Check teacher features available

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH004: AJK Login
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/login`
**Expected:** AJK login successful

**Credentials:**
```
Email: ahmad.zaki.mohamed@iium.edu.my
Password: password
```

**Test Steps:**
1. Logout previous user
2. Enter AJK credentials
3. Verify dashboard access
4. Check AJK-specific features

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### AUTH005: Logout Functionality
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** User logout successful

**Test Steps:**
1. Login as any user
2. Click logout
3. Verify redirect to login
4. Try accessing dashboard (should redirect)
5. Verify session destroyed

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 3: DASHBOARDS (4 Tests)

### DASH001: Admin Dashboard
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Admin sees full statistics

**Login:** ruzita.abd..rahim@iium.edu.my / password

**Test Steps:**
1. Login as admin
2. Navigate to dashboard
3. Verify all stat widgets
4. Check charts load
5. Verify data accurate

**Expected Stats:**
```
Trainees: 126
Activities: 400
Sessions: 9,186
Attendance: 25,980
```

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### DASH002: Supervisor Dashboard
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Supervisor sees centre-level data

**Login:** nor.aisyah.muhamad.asri@iium.edu.my / password

**Test Steps:**
1. Login as supervisor
2. Verify centre statistics
3. Check staff overview
4. Verify activity summary
5. Check centre-specific filters work

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### DASH003: Teacher Dashboard
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** Teacher sees activity-focused view

**Login:** nabilah.ahmad@iium.edu.my / password

**Test Steps:**
1. Login as teacher
2. Verify "My Activities" section
3. Check "Upcoming Sessions"
4. Verify "Recent Attendance"
5. Check quick actions available

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### DASH004: AJK Dashboard
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/dashboard`
**Expected:** AJK sees asset-focused view

**Login:** ahmad.zaki.mohamed@iium.edu.my / password

**Test Steps:**
1. Login as AJK
2. Verify asset statistics
3. Check maintenance alerts
4. Verify asset quick actions
5. Check centre data visible

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 4: PROFILE MODULE (4 Tests)

### PROF001: View Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/profile`
**Expected:** User can view their profile

**Login:** Any user

**Test Steps:**
1. Login
2. Click profile link
3. Verify all fields display
4. Check: Name, Email, Phone, Role, Centre

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### PROF002: Edit Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/profile`
**Expected:** User can update profile

**Test Data:**
```
New Phone: 0199999999
```

**Test Steps:**
1. Navigate to profile
2. Click "Edit Profile"
3. Modify phone number
4. Save changes
5. Verify success message
6. Refresh - verify persisted

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### PROF003: Change Password
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/profile`
**Expected:** Password change successful

**Test Data:**
```
Current: password
New: NewPassword@2024!
Confirm: NewPassword@2024!
```

**Test Steps:**
1. Navigate to profile
2. Click "Change Password"
3. Enter current and new password
4. Submit
5. Verify success
6. Logout and login with new password
7. **IMPORTANT:** Change back to "password" after test

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### PROF004: Upload Profile Photo
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/profile`
**Expected:** Photo upload successful

**Test Steps:**
1. Navigate to profile
2. Click upload photo
3. Select image (JPG/PNG)
4. Upload
5. Verify image displays
6. Refresh - verify persisted

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## PHASE 5: TRAINEE MODULE (5 Tests)

### TRAIN001: List Trainees
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees`
**Expected:** View 126 trainees

**Login:** Admin or Supervisor

**Test Steps:**
1. Navigate to trainees
2. Verify table displays
3. Try search by name
4. Try filter by centre
5. Try filter by status

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN002: Register Trainee
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/register`
**Expected:** New trainee created

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

**Test Steps:**
1. Click "Register New Trainee"
2. Fill all fields
3. Submit
4. Verify success
5. Check appears in list

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN003: Edit Trainee
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/{id}/edit`
**Expected:** Trainee updated successfully

**Test Data:**
```
New Guardian Phone: 0111111111
```

**Test Steps:**
1. From list, click "Edit" on any trainee
2. Modify guardian phone
3. Save
4. Verify success
5. Check persisted

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN004: View Trainee Profile
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/traineeprofile/{id}`
**Expected:** Complete profile displays

**Test Steps:**
1. Click "View" on trainee
2. Verify sections:
   - Personal Info
   - Guardian Info
   - Activities
   - Attendance
3. Check data accuracy

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

### TRAIN005: Change Status
**Status:** ⏸️ PENDING
**URL:** `http://localhost/CREAMS/trainees/{id}`
**Expected:** Status updates successfully

**Test Steps:**
1. Select trainee
2. Change Active → Inactive
3. Save
4. Verify updated
5. Change back to Active

**Result:** _[To be filled]_
**Issues:** _[Any problems]_
**Fix:** _[If needed]_
**Retest:** _[After fix]_

---

## TESTING CONTINUES...
## (Additional 35 test cases to follow same format)

---

## 📊 CURRENT PROGRESS

**Total:** 50 test cases
**Completed:** 0
**Passing:** 0
**Failing:** 0
**Pass Rate:** 0%

---

## 🐛 ISSUES LOG

### Issue #1: [Title]
- **Test Case:**
- **Description:**
- **Severity:** Critical/High/Medium/Low
- **Status:** Open/Fixing/Fixed
- **Fix Details:**

---

## 🔧 FIXES APPLIED

### Fix #1: [Title]
- **Issue:**
- **Solution:**
- **Files Modified:**
- **Verification:**

---

## 📝 NOTES

-
-
-

---

**Last Updated:** October 13, 2025 22:15
**Ready For:** Manual Testing
**Next Action:** Begin testing with Phase 1 (PUBLIC ACCESS)

**Instructions for Tester:**
1. Open browser to http://localhost/CREAMS
2. Start with HOME001
3. Complete each test one by one
4. Document ALL findings (pass or fail)
5. Report issues immediately for fixing
6. Re-test after each fix
7. Move to next test only when current is complete
