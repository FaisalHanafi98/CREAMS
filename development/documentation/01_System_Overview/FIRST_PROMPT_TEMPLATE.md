# 🚀 CREAMS - Optimal First Prompt for New Sessions

**Copy and paste this prompt at the start of EVERY new Claude Code session for best comprehension.**

---

## 📋 THE FIRST PROMPT (Copy Everything Below This Line)

---

Hello! I'm working on the **CREAMS** (Community-based REhAbilitation Management System) project - a Laravel 10.x application for Malaysian rehabilitation centers serving children with special needs.

**Please start by reading these files in this exact order:**

1. **`development/documentation/01_System_Overview/CREAMS_MASTER_DOCUMENTATION.md`** (CRITICAL - read completely)
2. **`development/documentation/01_System_Overview/CREAMS_FORM_TESTING_GUIDE.txt`** (for form patterns and validation)

**After reading, please confirm you understand these CRITICAL points:**

### 🔐 **Authentication & Data Isolation**
- [ ] Custom session-based auth (NOT Laravel default Auth)
- [ ] Session structure: `session('id')`, `session('role')`, `session('centre_id')`
- [ ] Centre-based multi-tenancy: ALWAYS filter by `centre_id` for non-admin users
- [ ] 4 user roles: admin, supervisor, teacher, ajk

### 🗄️ **Database Critical Gotchas**
- [ ] Database name: `creams` (NOT `creams_db`)
- [ ] Users table: `status` field (NOT `is_active`)
- [ ] Activity enrollments: `enrollment_status` (NOT `status`)
- [ ] Activity enrollments: `progress_percentage` (NOT `attendance_rate`)
- [ ] Trainees: `guardian_name` field (NOT `parent_id`)
- [ ] Centre ID: STRING type ("01", "02", etc.)

### 🚨 **Protected Pages (DO NOT MODIFY)**
- [ ] `resources/views/home.blade.php`
- [ ] `resources/views/contactus.blade.php`
- [ ] `resources/views/volunteers/home.blade.php`
- [ ] `app/Http/Controllers/ContactController.php`
- [ ] `public/js/contact.js`
- [ ] `public/images/leadership/` folder

### 📊 **Current System Status**
- [ ] Status: **IN DEVELOPMENT** - Bug fixing in progress
- [ ] Production Ready: **NO** - Several bugs remain
- [ ] Branch: `Fixers` (most advanced)
- [ ] Laravel: 10.x with PHP 8.1+
- [ ] Server: Laragon on Windows

### 🎯 **Development Standards**
- [ ] Always verify column names before queries
- [ ] Apply centre filtering for non-admin users
- [ ] Use Eloquent ORM (avoid raw queries)
- [ ] Follow Laravel conventions
- [ ] Test with different user roles

**Current Working Directory:** `C:\laragon\www\CREAMS`

**My Current Task:**
[DESCRIBE YOUR SPECIFIC TASK HERE - e.g., "Fix the password reset functionality" or "Debug activity enrollment errors"]

**Known Bugs I'm Working On:**
[LIST SPECIFIC BUGS YOU'RE FIXING - e.g.:
- Bug #1: Password reset table name mismatch
- Bug #2: Activity statistics showing zeros for certain date ranges]

Please confirm you've read the documentation and understand the critical points above, then let me know you're ready to help with my task.

---

## 🎯 WHY THIS PROMPT WORKS

### **Complete Context Loading:**
1. ✅ Points to consolidated master documentation
2. ✅ References form testing guide for patterns
3. ✅ Highlights critical gotchas upfront
4. ✅ Sets correct system status expectations
5. ✅ Prevents common mistakes immediately

### **Forces Verification:**
- Checkbox list ensures Claude confirms understanding
- Prevents proceeding without reading documentation
- Reduces back-and-forth clarifications

### **Customizable Section:**
- Space to describe your current task
- Area to list specific bugs being fixed
- Keeps Claude focused on immediate priorities

### **Prevents Common Errors:**
- Explicitly lists database field name gotchas
- Highlights protected files to avoid modifications
- Emphasizes custom authentication system
- Clarifies centre-based filtering requirements

---

## 📝 USAGE INSTRUCTIONS

### **Step 1: Copy the Template**
Copy everything between the two horizontal lines above (starting from "Hello! I'm working on...")

### **Step 2: Customize Your Task**
Fill in these two sections:
```
**My Current Task:**
[Your specific task - be clear and concise]

**Known Bugs I'm Working On:**
[List 2-5 current bugs with brief descriptions]
```

### **Step 3: Paste at Session Start**
- Open new Claude Code session
- Paste the complete customized prompt
- Wait for Claude to confirm reading and understanding
- Proceed with your work

### **Step 4: Update Bug List**
As you fix bugs, update the "Known Bugs" section for the next session

---

## 💡 EXAMPLE FILLED PROMPTS

### Example 1: Bug Fixing Session
```
**My Current Task:**
Fix the password reset functionality - users report they're not receiving reset emails and the reset token validation is failing.

**Known Bugs I'm Working On:**
- Bug #1: Password reset emails not sending (mail config issue)
- Bug #2: Reset token validation failing (table name mismatch)
- Bug #3: Form shows success message even when email fails
```

### Example 2: Feature Development Session
```
**My Current Task:**
Implement bulk trainee enrollment feature for activities - supervisors need to enroll multiple trainees at once instead of one-by-one.

**Known Bugs I'm Working On:**
- None for this feature (new development)
- Background: Need to maintain centre filtering
- Requirement: Check capacity limits before bulk enrollment
```

### Example 3: Testing Session
```
**My Current Task:**
Test all forms in the staff module with different user roles (admin, supervisor, teacher) to verify role-based permissions are working correctly.

**Known Bugs I'm Working On:**
- Potential issue: Some forms may not filter by centre correctly
- Testing: Staff statistics showing zeros for some teachers
- Verification: Encrypted ID handling in profile views
```

---

## 🔄 MAINTENANCE

### **Update This Template When:**
1. ✅ Master documentation structure changes
2. ✅ New critical gotchas are discovered
3. ✅ Protected files list changes
4. ✅ Authentication system is modified
5. ✅ Database schema has major changes

### **Keep This Template Current:**
- Review monthly or after major system changes
- Update file paths if documentation is reorganized
- Add new critical points as they're discovered
- Remove outdated warnings after fixes are deployed

---

## 📊 EXPECTED CLAUDE RESPONSE

**After receiving this prompt, Claude should respond with something like:**

> ✅ I've read the CREAMS_MASTER_DOCUMENTATION.md and CREAMS_FORM_TESTING_GUIDE.txt.
>
> **Confirmed Understanding:**
> - Custom session-based authentication (not Laravel default)
> - Centre-based data isolation with centre_id filtering
> - Critical field names: enrollment_status, progress_percentage, guardian_name
> - Database name: creams (not creams_db)
> - Protected pages: home, contact, volunteer (DO NOT MODIFY)
> - System status: In development, bug fixing in progress
>
> **Your Current Task:**
> [Claude restates your task to confirm understanding]
>
> **Known Bugs:**
> [Claude lists the bugs you mentioned]
>
> I'm ready to help. Let's start with [Claude's suggested first step based on your task].

If Claude doesn't confirm these points, **paste the prompt again** or specifically ask Claude to read the master documentation.

---

## 🎯 SUCCESS METRICS

**This prompt is working well if:**
1. ✅ Claude references correct field names immediately
2. ✅ Claude applies centre filtering without being reminded
3. ✅ Claude avoids suggesting changes to protected files
4. ✅ Claude uses proper authentication patterns
5. ✅ Claude asks clarifying questions based on master doc
6. ✅ Fewer back-and-forth exchanges needed
7. ✅ No "I don't have context about..." responses
8. ✅ Claude catches potential issues before you do

---

## 🔧 TROUBLESHOOTING

### **Problem: Claude doesn't seem to have full context**
**Solution:**
- Explicitly ask: "Did you read CREAMS_MASTER_DOCUMENTATION.md completely?"
- Request: "Please list the 5 critical database gotchas from the documentation"
- Verify: "What authentication system does CREAMS use?"

### **Problem: Claude suggests modifying protected files**
**Solution:**
- Remind: "Check section 2 of CREAMS_MASTER_DOCUMENTATION - that's a protected file"
- Reference: "See the DO NOT MODIFY list in the master documentation"

### **Problem: Claude uses wrong field names**
**Solution:**
- Point to: "Check section 6 (Database Gotchas) in the master documentation"
- Remind: "Verify field names in the Field Mapping Reference section"

### **Problem: Claude forgets centre filtering**
**Solution:**
- Reference: "See section 5 (Data Isolation) for the standard query pattern"
- Remind: "All queries for non-admin users must filter by centre_id"

---

## 📚 RELATED DOCUMENTATION

After Claude has loaded the initial context, you can reference these additional docs as needed:

**For Specific Modules:**
- Staff Module: See section 7.1 in master doc
- Trainee Module: See section 7.2 in master doc
- Activity Module: See section 7.3 in master doc
- Asset Module: See section 7.4 in master doc

**For Technical Details:**
- Authentication: See section 5 in master doc
- Database Schema: See section 6 in master doc
- Debugging: See section 9 in master doc
- Development Workflow: See section 10 in master doc

**For Testing:**
- Form Testing: CREAMS_FORM_TESTING_GUIDE.txt
- Test Credentials: See CREAMS_FORM_TESTING_GUIDE.txt setup section

---

## 💾 SAVE THIS TEMPLATE

**Recommended Location:**
`development/documentation/01_System_Overview/FIRST_PROMPT_TEMPLATE.md`

**Quick Access:**
1. Bookmark this file in your editor
2. Keep a copy in your notes app
3. Create a keyboard shortcut to paste it
4. Add to your project README as "How to start a new session"

---

**Last Updated:** January 2025
**Purpose:** Ensure every new Claude Code session starts with complete context
**Status:** Living Template - Update as system evolves

---

**END OF FIRST PROMPT TEMPLATE**
