# CREAMS UAT TEST USABILITY REPORT

**Generated:** October 13, 2025 at 06:21:27
**Total Tests Analyzed:** 39

---

## 📊 EXECUTIVE SUMMARY

| Status | Count | Percentage |
|--------|-------|------------|
| ✅ Fully Usable | 39 | 100% |
| ⚠️ Partially Usable | 0 | 0% |
| ❌ Not Usable | 0 | 0% |

## ✅ FULLY USABLE TESTS (39 tests)

*These tests can be executed immediately*

### HOME001: Welcome/Landing Page
**Priority:** High | **Auth:** Public

**Route:** `GET /`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ⚠️ Controller not checked

---

### HOME002: About Us Page
**Priority:** Medium | **Auth:** Public

**Route:** `GET /aboutus`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ⚠️ Controller not checked

---

### CONTACT001: Contact Us Form
**Priority:** High | **Auth:** Public

**Route:** `GET /contact`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### CONTACT002: Contact Form Submission
**Priority:** High | **Auth:** Public

**Route:** `POST /contact/submit`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### VOL001: Volunteer Registration Page
**Priority:** High | **Auth:** Public

**Route:** `GET /volunteer`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### VOL002: Volunteer Form Submission
**Priority:** High | **Auth:** Public

**Route:** `POST /volunteer/submit`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### AUTH001: Login Page
**Priority:** Critical | **Auth:** Public

**Route:** `GET /login`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH002: Login Submit (Email/IIUM ID)
**Priority:** Critical | **Auth:** Public

**Route:** `POST /auth/check`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### AUTH003: Invalid Login Handling
**Priority:** Critical | **Auth:** Public

**Route:** `POST /auth/check`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH004: Forgot Password Page
**Priority:** High | **Auth:** Public

**Route:** `GET /forgot-password`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH005: Forgot Password Submit
**Priority:** High | **Auth:** Public

**Route:** `POST /forgot-password`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### AUTH006: Reset Password Page
**Priority:** High | **Auth:** Public

**Route:** `GET /reset-password/{token}`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH007: Reset Password Submit
**Priority:** High | **Auth:** Public

**Route:** `POST /reset-password`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH008: Session Management
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /auth/check-status`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### AUTH009: Logout
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /logout`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### DASH001: Admin Dashboard
**Priority:** Critical | **Auth:** Required (admin)

**Route:** `GET /admin/dashboard`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### DASH002: Supervisor Dashboard
**Priority:** Critical | **Auth:** Required (supervisor)

**Route:** `GET /admin/dashboard`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### DASH003: Teacher Dashboard
**Priority:** Critical | **Auth:** Required (teacher)

**Route:** `GET /teachershome`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ⚠️ Controller not checked

---

### DASH004: AJK Dashboard
**Priority:** High | **Auth:** Required (ajk)

**Route:** `GET /admin/dashboard`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### PROF001: View Profile
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /profile`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### PROF002: Edit Profile
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /profile`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### PROF003: Change Password
**Priority:** High | **Auth:** Required (any)

**Route:** `POST /profile/change-password`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### USER001: Staff List
**Priority:** Critical | **Auth:** Required (admin)

**Route:** `GET /staffs/home`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### TRAIN001: Trainee List
**Priority:** Critical | **Auth:** Required (any)

**Route:** `GET /trainees/home`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### ACT001: Activities List
**Priority:** Critical | **Auth:** Required (any)

**Route:** `GET /activities`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### ACT002: Create Activity
**Priority:** Critical | **Auth:** Required (admin)

**Route:** `GET /activities/create`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ⚠️ Table not checked
- ✅ Controller exists

---

### ACT003: Edit Activity
**Priority:** Critical | **Auth:** Required (admin)

**Route:** `GET /activities/{id}/edit`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### ACT006: Create Session
**Priority:** Critical | **Auth:** Required (any)

**Route:** `POST /activities/{id}/sessions`

**Verification:**
- ✅ Route exists
- ⚠️ View not checked
- ✅ Table exists
- ✅ Controller exists

---

### ACT007: Enroll Trainee
**Priority:** Critical | **Auth:** Required (any)

**Route:** `GET /activities/{id}/enroll`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### ACT008: Activity Schedule
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /activities/schedule`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### ACT009: Weekly Schedule
**Priority:** Medium | **Auth:** Required (any)

**Route:** `GET /activities/schedule/weekly`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### ACT010: Teacher Personal Schedule
**Priority:** High | **Auth:** Required (teacher)

**Route:** `GET /activities/schedule/personal`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### ATT001: Mark Attendance
**Priority:** Critical | **Auth:** Required (teacher)

**Route:** `GET /activity-attendance`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### ATT002: View Attendance Records
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /activity-attendance`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### CENT001: Centre List
**Priority:** High | **Auth:** Required (admin)

**Route:** `GET /admin/centres`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### ASSET001: Asset List
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /admin/assets`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### ASSET002: Asset Inventory
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /admin/assets`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ⚠️ Table not checked
- ✅ Controller exists

---

### LETT001: Letters Home
**Priority:** High | **Auth:** Required (any)

**Route:** `GET /letters/index`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

### LETT002: Letter Templates
**Priority:** Medium | **Auth:** Required (admin)

**Route:** `GET /admin/admin/letter-templates`

**Verification:**
- ✅ Route exists
- ✅ View exists
- ✅ Table exists
- ✅ Controller exists

---

## ⚠️ PARTIALLY USABLE TESTS (0 tests)

*These tests have some components missing but can be adapted*

## ❌ NOT USABLE TESTS (0 tests)

*These tests cannot be executed without significant development*

## 📋 PRIORITY ANALYSIS

| Priority | Usable | Partial | Not Usable |
|----------|--------|---------|------------|
| High | 22 | 0 | 0 |
| Medium | 3 | 0 | 0 |
| Critical | 14 | 0 | 0 |

---

*Report generated by CREAMS UAT Verification Script*
