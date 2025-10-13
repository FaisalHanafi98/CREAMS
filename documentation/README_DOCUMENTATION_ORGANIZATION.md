# CREAMS Documentation Organization

This directory has been reorganized for better navigation and maintenance. Below is the new structure and purpose of each folder.

## 📁 Folder Structure

### 01_System_Overview
**Purpose**: High-level system documentation, project overview, and organizational files
- System architecture documents
- Project overview files
- CLAUDE.md (main development guide)
- Naming conventions and structure guides
- Project handoff documentation

### 02_Module_Documentation
**Purpose**: Detailed documentation for each system module
- MODULE_SUMMARY_ACTIVITIES.md
- MODULE_SUMMARY_ASSETS.md
- MODULE_SUMMARY_ATTENDANCE.md
- MODULE_SUMMARY_AUTHENTICATION.txt
- MODULE_SUMMARY_CENTRES.md
- MODULE_SUMMARY_DASHBOARD.md
- MODULE_SUMMARY_LETTERS.md
- MODULE_SUMMARY_MESSAGES_NOTIFICATIONS.md
- MODULE_SUMMARY_TRAINEES.md
- MODULE_SUMMARY_USERS_PROFILE.md

### 03_Technical_Guides
**Purpose**: Technical implementation guides and reference materials
- API documentation
- Database architecture and schemas
- Technical implementation guides
- Setup and configuration guides
- Quality control guides (SonarScanner, etc.)
- Error handling documentation

### 04_Deployment_Guides
**Purpose**: Deployment and migration documentation
- AWS deployment guides
- Vercel deployment guides
- Migration procedures and proposals
- Database migration guides
- Environment setup documentation

### 05_Testing_Documentation
**Purpose**: Testing procedures and reports
- Comprehensive testing reports
- Form testing guides
- Testing credentials and procedures
- Test script documentation

### 06_Status_Reports
**Purpose**: Project status updates and completion reports
- Final implementation reports
- System completion documentation
- Demo day reports
- Project state summaries
- Health reports and logs

### 07_Fixes_and_Audits
**Purpose**: Bug fixes, audits, and issue resolution documentation
- Fix logs and summaries
- Audit reports
- Issue analysis documentation
- Performance verification reports
- Module fix summaries

### 08_Development_Planning
**Purpose**: Development planning and requirements documentation
- Activity module development plans
- User stories and requirements
- Asset controller references
- Data generation summaries
- Development roadmaps

## 🗃️ Special Files

### my_test_case_manager.xlsx
**Location**: Root of documentation folder
**Purpose**: UAT test case management for all system modules

### Summary Files Folder
**Purpose**: Contains legacy summary files that may need archival

## 📋 For UAT Test Case Management

Based on the system modules identified, your UAT test cases should cover:

1. **Authentication Module** - Login, registration, password reset
2. **Dashboard Module** - Statistics, widgets, navigation
3. **Activities Module** - Activity creation, enrollment, session management
4. **Attendance Module** - Recording attendance, reports
5. **Trainees Module** - Registration, profile management, progress tracking
6. **Users/Staff Module** - Staff management, roles, permissions
7. **Assets Module** - Equipment and resource management
8. **Centres Module** - Centre configuration and management
9. **Letters Module** - Document generation and templates
10. **Messages/Notifications Module** - Communication features

## 🧹 File Cleanup Summary

The following cleanup actions were performed:
- Removed 25+ redundant test files from development/tests/
- Combined similar test files into comprehensive test suites
- Organized 80+ documentation files into logical categories
- Created organized folder structure for better navigation
- Maintained all important documentation while removing duplicates

## 📚 How to Use This Organization

1. **For System Overview**: Start with `01_System_Overview/CLAUDE.md`
2. **For Module Details**: Check `02_Module_Documentation/`
3. **For Implementation**: Use guides in `03_Technical_Guides/`
4. **For Deployment**: Reference `04_Deployment_Guides/`
5. **For Testing**: Use procedures in `05_Testing_Documentation/`
6. **For Status Updates**: Check `06_Status_Reports/`
7. **For Issue Resolution**: Review `07_Fixes_and_Audits/`
8. **For Planning**: Use documents in `08_Development_Planning/`

Last Updated: September 30, 2025