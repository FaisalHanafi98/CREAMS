# CREAMS UAT Execution Guide

## 📋 Overview

This guide provides comprehensive UAT (User Acceptance Testing) procedures for the CREAMS system. Use this in conjunction with the provided CSV test cases.

## 🎯 UAT Test Coverage Summary

### **Total Test Cases Created: 75**

| Module | Test Cases | Priority Breakdown |
|--------|------------|-------------------|
| Authentication | 6 | High: 4, Medium: 2 |
| Dashboard | 7 | High: 5, Medium: 1, Low: 1 |
| Activities | 10 | High: 6, Medium: 4 |
| Attendance | 4 | High: 1, Medium: 3, Low: 1 |
| Users/Staff | 5 | High: 2, Medium: 3 |
| Trainees | 6 | High: 2, Medium: 4 |
| Assets | 4 | Medium: 2, Low: 2 |
| Centres | 3 | High: 1, Medium: 2 |
| Letters | 3 | Medium: 2, Low: 1 |
| Messages/Notifications | 3 | Medium: 2, Low: 1 |
| System | 5 | High: 1, Medium: 3, Low: 1 |

## 🔧 How to Import Test Cases to Excel

### Option 1: Import CSV File
1. Open your `my_test_case_manager.xlsx`
2. Go to **Data** > **Get Data** > **From Text/CSV**
3. Select the `CREAMS_UAT_TEST_CASES.csv` file
4. Configure import settings:
   - Delimiter: Comma
   - Text qualifier: Double quote
   - Data type detection: Automatic
5. Import and format as needed

### Option 2: Copy and Paste
1. Open the CSV file in a text editor
2. Copy all content
3. Paste into Excel with "Text to Columns" delimiter set to comma

## 👥 Test Execution Roles

### Required Test User Accounts
Create the following test accounts for comprehensive testing:

| Role | Test Account | Centre | Purpose |
|------|-------------|--------|---------|
| Admin | test.admin@creams.test | 01 | Full system testing |
| Teacher | test.teacher@creams.test | 01 | Teaching functionality |
| Supervisor | test.supervisor@creams.test | 01 | Oversight testing |
| AJK | test.ajk@creams.test | 01 | Facility management |
| Trainee | test.trainee@creams.test | 01 | End-user testing |

### Multi-Centre Testing
- Create additional accounts for Centre 02 to test data isolation
- Verify users only see data from their assigned centre

## 📊 Test Execution Phases

### Phase 1: High Priority Tests (24 tests)
**Focus**: Core functionality that must work
- All Authentication tests
- Dashboard access for all roles
- Activity creation and management (Admin only)
- Basic trainee and staff management
- Centre data isolation

### Phase 2: Medium Priority Tests (35 tests)
**Focus**: Important features for daily operations
- Advanced dashboard features
- Activity scheduling and enrollment
- Attendance tracking
- Asset management
- Letter generation

### Phase 3: Low Priority Tests (16 tests)
**Focus**: Nice-to-have features
- Dashboard customization
- Advanced reporting
- System performance
- Mobile responsiveness

## 🎯 Test Environment Setup

### Prerequisites
1. **Database**: Fresh test data or seeded development database
2. **Test Data**:
   - Multiple activities across different categories
   - Sample trainees with various statuses
   - Staff members with different roles
   - Historical attendance data
3. **Browser Testing**: Chrome, Firefox, Safari, Edge
4. **Mobile Testing**: Various screen sizes and devices

### Test Data Requirements
```
Activities: 10+ activities across different categories
Trainees: 20+ trainees with different statuses
Staff: 5+ staff members with different roles
Sessions: 15+ scheduled sessions with enrollments
Attendance: Historical data for statistics
Assets: Equipment/resources for testing
Templates: Letter templates for document generation
```

## ✅ Test Execution Checklist

### Before Testing
- [ ] Test environment is stable and accessible
- [ ] All test user accounts created and verified
- [ ] Test data is properly seeded
- [ ] Browser developer tools available for debugging
- [ ] Test result tracking spreadsheet ready

### During Testing
- [ ] Document actual results vs expected results
- [ ] Take screenshots for failed tests
- [ ] Note performance issues or slow responses
- [ ] Test edge cases and boundary conditions
- [ ] Verify error messages are user-friendly

### After Testing
- [ ] Categorize defects by severity (Critical/High/Medium/Low)
- [ ] Create defect reports with reproduction steps
- [ ] Verify cross-browser compatibility
- [ ] Test data cleanup completed
- [ ] Final test summary report prepared

## 🐛 Defect Tracking Template

### Defect Severity Levels
- **Critical**: System crashes, data loss, security issues
- **High**: Major functionality broken, blocking workflows
- **Medium**: Minor functionality issues, workarounds exist
- **Low**: Cosmetic issues, enhancement requests

### Defect Report Template
```
Defect ID: DEF001
Test Case ID: AUTH001
Module: Authentication
Severity: High
Title: Login fails with valid credentials

Steps to Reproduce:
1. Navigate to login page
2. Enter valid email: test@example.com
3. Enter correct password
4. Click Login button

Expected Result: User logs in successfully
Actual Result: Error message "Invalid credentials"

Environment: Chrome 118, Windows 11
Screenshots: [Attach relevant screenshots]
Additional Notes: Issue occurs intermittently
```

## 📈 Success Criteria

### UAT Pass Criteria
- **100%** of Critical and High priority tests pass
- **95%** of Medium priority tests pass
- **90%** of Low priority tests pass
- No unresolved Critical or High severity defects
- Performance within acceptable limits (<3 seconds page load)

### Special Focus Areas
1. **Role-Based Access**: Verify each role sees appropriate data only
2. **Data Isolation**: Centre-specific data filtering works correctly
3. **Core Workflows**: Activity management, attendance, and enrollment
4. **User Experience**: Intuitive navigation and clear error messages
5. **Data Integrity**: No data corruption or loss during operations

## 🔄 Test Cycle Management

### Iteration Planning
1. **Week 1**: Phase 1 (High Priority) + Setup
2. **Week 2**: Phase 2 (Medium Priority) + Defect fixes
3. **Week 3**: Phase 3 (Low Priority) + Regression testing
4. **Week 4**: Final validation + Documentation

### Regression Testing
After each defect fix, re-run:
- The specific failed test case
- Related test cases in the same module
- Any dependent functionality

## 📝 Reporting

### Daily Status Reports
- Tests executed vs planned
- Pass/fail rates by module
- Critical issues discovered
- Blockers and dependencies

### Final UAT Report
- Executive summary of testing results
- Detailed test execution metrics
- Defect summary with recommendations
- Sign-off criteria met/not met
- Go-live readiness assessment

---

**Last Updated**: September 30, 2025
**Created By**: Claude Code Assistant
**Version**: 1.0