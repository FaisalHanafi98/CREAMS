# CREAMS User Manual: Authentication & Login Module

## 📖 Table of Contents
1. [Getting Started](#getting-started)
2. [Login Process](#login-process)
3. [User Registration](#user-registration)
4. [Password Management](#password-management)
5. [Session Management](#session-management)
6. [Security Features](#security-features)
7. [Troubleshooting](#troubleshooting)
8. [Frequently Asked Questions](#frequently-asked-questions)

---

## 🚀 Getting Started

### System Access
The CREAMS system can be accessed through your web browser at:
**URL**: `http://your-domain.com/CREAMS` or `http://localhost/CREAMS`

### Supported Browsers
- ✅ Google Chrome (Recommended)
- ✅ Mozilla Firefox
- ✅ Microsoft Edge
- ✅ Safari (Mac/iOS)

### User Roles in CREAMS
The system supports five different user roles:
- **Admin**: Full system access and management
- **Teacher**: Activity instruction and trainee management
- **Supervisor**: Team oversight and reporting
- **AJK**: Facility and administrative management
- **Trainee**: Limited access to personal information

---

## 🔐 Login Process

### Accessing the Login Page

**Step 1: Navigate to CREAMS**
1. Open your web browser
2. Enter the CREAMS website URL
3. The login page will automatically appear

*[MEDIA SPACE: Screenshot of login page showing the main interface]*

### Login Options

CREAMS supports **two types of login identifiers**:

#### Option 1: Email Address Login
**Step 1**: Enter your registered email address
- Format: `yourname@domain.com`
- Example: `john.doe@creams.edu.my`

*[MEDIA SPACE: Screenshot showing email login field]*

#### Option 2: IIUM ID Login
**Step 1**: Enter your IIUM identification number
- Format: 10-digit number
- Example: `1234567890`

*[MEDIA SPACE: Screenshot showing IIUM ID login field]*

### Complete Login Process

**Step 2: Enter Your Password**
1. Click on the password field
2. Type your password (characters will be hidden for security)
3. Ensure Caps Lock is off if experiencing issues

*[MEDIA SPACE: Screenshot of password field with masked characters]*

**Step 3: Remember Me (Optional)**
- Check the "Remember Me" box to stay logged in longer
- Recommended for personal devices only
- Do NOT use on shared computers

*[MEDIA SPACE: Screenshot showing Remember Me checkbox]*

**Step 4: Click Login**
1. Click the "Login" button
2. Wait for the system to verify your credentials
3. You will be redirected to your role-specific dashboard

*[MEDIA SPACE: Screenshot of login button and loading indicator]*

### Successful Login Indicators

After successful login, you will see:
- Your name displayed in the top navigation bar
- Role-appropriate menu options
- Welcome message with your role
- Dashboard relevant to your user type

*[MEDIA SPACE: Screenshot of successful login showing user name and navigation]*

---

## 👤 User Registration

### Admin-Only Registration
**Important**: Only Admin users can create new accounts in CREAMS.

### Registration Process for Admins

**Step 1: Access Registration Page**
1. Login as Admin
2. Navigate to "User Management" → "Add New User"
3. The registration form will open

*[MEDIA SPACE: Screenshot of navigation to user registration]*

**Step 2: Fill User Information**

#### Personal Information
- **Full Name**: Enter complete name as it should appear in the system
- **Email Address**: Must be unique and valid
- **IIUM ID**: 10-digit identification number (must be unique)
- **Phone Number**: Contact number for the user

*[MEDIA SPACE: Screenshot of personal information form fields]*

#### Account Configuration
- **Role Assignment**: Select appropriate role (Admin, Teacher, Supervisor, AJK, Trainee)
- **Centre Assignment**: Choose the user's primary centre
- **Status**: Set to "Active" for immediate access

*[MEDIA SPACE: Screenshot of role and centre selection dropdowns]*

#### Password Setup
- **Temporary Password**: Create a secure password for the user
- **Force Password Change**: Check to require password change on first login
- Password must meet security requirements (see Password Policy below)

*[MEDIA SPACE: Screenshot of password configuration section]*

**Step 3: Submit Registration**
1. Review all information for accuracy
2. Click "Create User" button
3. Confirmation message will appear
4. New user will receive welcome email (if configured)

*[MEDIA SPACE: Screenshot of completed registration and success message]*

---

## 🔑 Password Management

### Password Policy Requirements

All passwords must meet the following criteria:
- **Minimum 8 characters** (12+ recommended)
- **At least 1 uppercase letter** (A-Z)
- **At least 1 lowercase letter** (a-z)
- **At least 1 number** (0-9)
- **At least 1 special character** (!@#$%^&*)

*[MEDIA SPACE: Screenshot of password requirements display]*

### Password Strength Indicator

The system provides real-time password strength feedback:
- 🔴 **Weak**: Does not meet minimum requirements
- 🟡 **Medium**: Meets basic requirements
- 🟢 **Strong**: Exceeds minimum requirements with good complexity

*[MEDIA SPACE: Screenshot showing password strength meter]*

### Changing Your Password

**Step 1: Access Profile Settings**
1. Click on your name in the top navigation
2. Select "Profile" or "Account Settings"
3. Navigate to "Change Password" section

*[MEDIA SPACE: Screenshot of profile menu access]*

**Step 2: Enter Password Information**
1. **Current Password**: Enter your existing password
2. **New Password**: Enter your new password
3. **Confirm New Password**: Re-enter the new password
4. Ensure both new password fields match

*[MEDIA SPACE: Screenshot of password change form]*

**Step 3: Save Changes**
1. Click "Update Password" button
2. Success message will confirm the change
3. You may need to log in again with the new password

*[MEDIA SPACE: Screenshot of successful password change]*

### Forgot Password (Admin Reset Required)

**Important**: CREAMS uses admin-managed password resets for security.

**Step 1: Contact Your Administrator**
- Email or call your system administrator
- Provide your email address or IIUM ID
- Verify your identity as requested

**Step 2: Admin Password Reset Process**
1. Admin accesses User Management
2. Locates your user account
3. Sets a temporary password
4. Provides you with new temporary credentials

**Step 3: First Login with New Password**
1. Use temporary password provided by admin
2. System will prompt for password change (if configured)
3. Set your new permanent password

*[MEDIA SPACE: Workflow diagram showing password reset process]*

---

## ⏱️ Session Management

### Session Duration
- **Standard Session**: 30 minutes of inactivity
- **Remember Me**: Extended session (configurable by admin)
- **Security Timeout**: Automatic logout for security

### Session Indicators

**Active Session Signs:**
- Your name appears in navigation bar
- Menu options are accessible
- No login prompts when navigating

*[MEDIA SPACE: Screenshot showing active session indicators]*

**Session Expiry Warning:**
- Warning message appears 5 minutes before timeout
- Option to extend session or logout
- Countdown timer shows remaining time

*[MEDIA SPACE: Screenshot of session timeout warning]*

### Manual Logout

**Step 1: Locate Logout Option**
- Click on your name in top navigation
- Select "Logout" from dropdown menu
- Alternative: Direct logout button (if visible)

*[MEDIA SPACE: Screenshot of logout menu option]*

**Step 2: Confirm Logout**
1. Click "Logout" to confirm
2. You will be redirected to login page
3. Success message confirms logout

*[MEDIA SPACE: Screenshot of logout confirmation]*

### Security Best Practices

**Do's:**
- ✅ Always logout when using shared computers
- ✅ Use "Remember Me" only on personal devices
- ✅ Keep your browser updated
- ✅ Use strong, unique passwords

**Don'ts:**
- ❌ Share your login credentials
- ❌ Leave your session open on public computers
- ❌ Use the same password for multiple accounts
- ❌ Write passwords down where others can see

---

## 🔒 Security Features

### Login Security

**Failed Login Protection:**
- Account temporarily locked after 5 failed attempts
- 15-minute lockout period for security
- Admin notification for repeated failures

*[MEDIA SPACE: Screenshot of account lockout message]*

**Login Audit Trail:**
- All login attempts are logged
- IP address and timestamp recorded
- Admin can review login history

### Session Security

**Session Protection Features:**
- Session ID changes after login
- Encrypted session data
- Automatic timeout for inactive sessions
- Protection against session hijacking

**Device Security:**
- Browser fingerprinting for additional security
- Unusual device alerts (if configured)
- Session management across multiple devices

### Data Security

**Information Protection:**
- All passwords are encrypted and hashed
- Sensitive data transmission is secured
- Role-based access prevents unauthorized data viewing
- Centre-based data isolation for multi-centre operations

*[MEDIA SPACE: Diagram showing security layers]*

---

## 🔧 Troubleshooting

### Common Login Issues

#### "Invalid Credentials" Error

**Possible Causes:**
- Incorrect email/IIUM ID
- Wrong password
- Account not yet created
- Account temporarily locked

**Solutions:**
1. Verify email address or IIUM ID spelling
2. Check password (remember it's case-sensitive)
3. Contact admin to verify account exists
4. Wait 15 minutes if account is locked

*[MEDIA SPACE: Screenshot of invalid credentials error]*

#### "Account Locked" Message

**Cause:** Too many failed login attempts

**Solution:**
1. Wait 15 minutes for automatic unlock
2. Contact admin for immediate unlock
3. Verify correct credentials before retry

#### Page Won't Load

**Possible Causes:**
- Internet connection issues
- Server maintenance
- Browser compatibility

**Solutions:**
1. Check internet connection
2. Try refreshing the page (Ctrl+F5)
3. Try a different browser
4. Contact IT support if persistent

#### "Session Expired" Message

**Cause:** Inactivity timeout reached

**Solution:**
1. Click "Login Again" to return to login page
2. Re-enter your credentials
3. Continue with your work

*[MEDIA SPACE: Screenshot of session expired message]*

### Browser-Specific Issues

#### Chrome Issues
- Clear browser cache and cookies
- Disable browser extensions temporarily
- Check for browser updates

#### Firefox Issues
- Verify JavaScript is enabled
- Clear stored data for the site
- Try private browsing mode

#### Safari Issues
- Enable cookies and JavaScript
- Clear website data
- Check security settings

### Mobile Access Issues

**Mobile Browser Recommendations:**
- Use latest version of mobile browsers
- Ensure good internet connection
- Consider landscape orientation for better view

*[MEDIA SPACE: Screenshot of mobile login interface]*

---

## ❓ Frequently Asked Questions

### Q1: Can I use CREAMS on my mobile phone?
**A:** Yes, CREAMS is mobile-friendly and works on smartphones and tablets. Use your mobile browser to access the system.

### Q2: How often should I change my password?
**A:** It's recommended to change your password every 90 days or immediately if you suspect it may be compromised.

### Q3: Can I have multiple sessions open?
**A:** The system allows multiple sessions, but for security, it's recommended to logout when switching devices.

### Q4: What happens if I forget my password?
**A:** Contact your system administrator for a password reset. For security reasons, users cannot reset passwords themselves.

### Q5: Why can't I see certain menu options?
**A:** Menu visibility depends on your user role. Each role has different permissions and access levels.

### Q6: Can I change my own role or centre assignment?
**A:** No, only Admin users can modify roles and centre assignments for security and organizational control.

### Q7: Is my data safe in CREAMS?
**A:** Yes, CREAMS implements multiple security layers including encryption, access controls, and audit trails to protect your data.

### Q8: Can I access CREAMS from home?
**A:** This depends on your organization's policy. Contact your admin to confirm if remote access is permitted.

---

## 📞 Support and Contact

### Technical Support
- **IT Help Desk**: [Contact Information]
- **System Administrator**: [Contact Information]
- **Emergency Support**: [Contact Information]

### Additional Resources
- **Training Videos**: Available in the system help section
- **User Community**: [Forum or discussion platform]
- **Documentation**: Updated user guides and tutorials

---

*Last Updated: [Date]
Version: 1.0
Document Type: User Manual - Authentication & Login*

**Note**: This manual includes placeholder spaces marked as *[MEDIA SPACE: Description]* where screenshots, diagrams, and other visual aids should be inserted to enhance user understanding and provide visual guidance for each step described.