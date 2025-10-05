# CREAMS Testing Guide Credentials & Data Setup

This document provides all the credentials and setup instructions needed to run the comprehensive CREAMS Form Testing Guide.

## 🚀 Quick Setup

### Method 1: Run Full Database Seeding
```bash
php artisan migrate:fresh --seed
```

### Method 2: Seed Only Testing Data (Faster)
```bash
php artisan db:seed --class=TestingGuideDataSeeder
```

### Method 3: Use Standalone Script
```bash
php seed-testing-data.php
```

## 🔐 Test User Credentials

All passwords are case-sensitive and must be entered exactly as shown.

### Admin Account
- **Email**: `lakshmi.krishnan@iium.edu.my`
- **Password**: `Admin@2024!`
- **Role**: admin
- **Centre**: Gombak (01)
- **Name**: Dr. Lakshmi a/p Krishnan
- **Position**: Director of Special Needs Services

### Teacher Account (with IIUM ID)
- **Email**: `ahmad.hassan@iium.edu.my`
- **IIUM ID**: `1928471` (can login with this instead of email)
- **Password**: `Teacher@2024`
- **Role**: teacher
- **Centre**: Gombak (01)
- **Name**: Ustaz Ahmad bin Hassan
- **Position**: Special Education Teacher

### Supervisor Account
- **Email**: `supervisor.gombak@iium.edu.my`
- **Password**: `Supervise@2024`
- **Role**: supervisor
- **Centre**: Gombak (01)
- **Name**: Dr. Aminah binti Mohd Said
- **Position**: Program Supervisor

### AJK Account
- **Email**: `fatimah.abdullah@iium.edu.my`
- **IIUM ID**: `1543298`
- **Password**: `AJK@2024`
- **Role**: ajk
- **Centre**: Gombak (01)
- **Name**: Siti Fatimah binti Abdullah
- **Position**: Community Liaison Officer

## 🏢 Test Centre Data

### Gombak Centre (Primary Test Centre)
- **Centre ID**: `01`
- **Name**: Gombak
- **Address**: Jalan Gombak, 53100 Gombak, Selangor
- **Phone**: +603-6196-4000
- **Email**: gombak@iium.edu.my
- **Status**: active
- **Manager**: Prof. Dr. Mohd Roslan bin Mohd Nor

## 👶 Pre-existing Test Data

These records exist for testing edit/update forms and duplicate validation:

### Existing Trainee
- **Trainee ID**: `AU0001`
- **Name**: Muhammad Irfan bin Zainal
- **Email**: `existing.trainee@gmail.com`
- **IC Number**: `100815-03-1234`
- **Status**: active
- **Centre**: Gombak (01)
- **Condition**: Autism Spectrum Disorder

### Existing Volunteer
- **Volunteer ID**: `VL0001`
- **Name**: Existing Volunteer
- **Email**: `existing.volunteer@gmail.com`
- **Phone**: +60167891234
- **Status**: active
- **Centre**: Gombak (01)

### Previous Contact Message
- **Email**: `previous.inquirer@gmail.com`
- **Subject**: Previous Inquiry
- **Status**: replied
- **Type**: general

## 🏭 Asset Management Test Data

### Asset Categories
1. **Medical Equipment** (ID: 1) - Therapy and medical equipment
2. **Furniture** (ID: 2) - Office and therapy furniture  
3. **Electronics** (ID: 3) - Computers and electronic devices

### Asset Types
1. **Therapy Equipment** (ID: 1, Category: Medical Equipment)
2. **Office Furniture** (ID: 2, Category: Furniture)
3. **Computer Equipment** (ID: 3, Category: Electronics)

### Asset Locations
1. **Therapy Room** (ID: 1, Centre: Gombak)
2. **Office Area** (ID: 2, Centre: Gombak)
3. **Computer Lab** (ID: 3, Centre: Gombak)

## 📋 Testing Guide Usage

### Role-Based Testing
- **Admin**: Can access all forms and features
- **Supervisor**: Can manage activities, view reports, moderate content
- **Teacher**: Can mark attendance, create activities, manage trainees
- **AJK**: Limited access, mainly data entry and basic operations

### Form Testing Sequence
1. Start with **login form** using the credentials above
2. Test **profile forms** to update user information
3. Test **trainee registration** with new realistic data
4. Test **trainee edit** using existing trainee (AU0001)
5. Test **activity creation** with realistic therapy programs
6. Test **asset management** using the seeded categories
7. Test **contact form** with realistic Malaysian inquiries
8. Test **volunteer form** with IIUM student data

### Validation Testing
- Use existing emails for duplicate testing
- Use invalid IC formats for validation testing  
- Use invalid phone numbers for Malaysian format testing
- Test with missing required fields
- Test with data that exceeds field limits

## 🔧 Troubleshooting

### If Login Fails
1. Ensure you're using the exact password (case-sensitive)
2. Try clearing browser cache and cookies
3. Verify the database has been seeded properly
4. Check that migrations have been run

### If Data is Missing
1. Run the seeder again: `php artisan db:seed --class=TestingGuideDataSeeder`
2. Or run full seeding: `php artisan migrate:fresh --seed`
3. Check database connection settings in `.env`

### If Phone Number Validation Fails
- Ensure MalaysianPhoneRule is properly implemented
- Test with various formats: +60123456789, 0123456789, 012-345-6789
- Check that phone fields have initial value "+60"

### If Asset Forms Don't Work
- Verify asset categories, types, and locations are seeded
- Check foreign key relationships in database
- Ensure user has proper permissions for asset management

## 📱 Malaysian Data Validation

The system includes comprehensive validation for Malaysian data:

### Phone Numbers
- **Valid formats**: +60123456789, 0123456789, 012-345-6789
- **Auto-normalization**: All formats converted to +60 international format
- **Validation**: Must be valid Malaysian mobile (01X) or landline (03, 04, etc.)

### IC Numbers  
- **Format**: YYMMDD-PB-NNNN (e.g., 050612-03-0234)
- **Validation**: Proper date, place of birth, and check digit validation
- **Uniqueness**: Each IC number can only be registered once

### Email Addresses
- **IIUM domains**: @iium.edu.my, @live.iium.edu.my accepted for staff
- **Public domains**: gmail.com, yahoo.com, hotmail.com accepted for trainees/volunteers
- **Validation**: Proper email format with domain verification

### Names
- **Malaysian convention**: Proper bin/binti usage
- **Islamic names**: Culturally appropriate Islamic names
- **Character validation**: Only letters, spaces, and Islamic name particles

## 🎯 Test Coverage

The seeded data ensures comprehensive testing of:

✅ **Authentication system** - All user roles and login methods  
✅ **Form validation** - All validation rules and error messages
✅ **Database constraints** - Foreign keys, unique constraints, required fields
✅ **Malaysian localization** - Phone numbers, IC numbers, Islamic names
✅ **File uploads** - Asset images, profile pictures (test with your files)
✅ **Role-based access** - Different permissions per user role
✅ **Data relationships** - Centre assignments, user-trainee relationships
✅ **Business logic** - Enrollment limits, scheduling conflicts, etc.

## 📞 Support

If you encounter issues with the testing setup:

1. Check this document for troubleshooting steps
2. Verify your environment setup (PHP, database, Laravel)
3. Review the CREAMS_FORM_TESTING_GUIDE.txt for detailed test scenarios
4. Check the error logs in `storage/logs/`

---

**Last Updated**: 2024-12-05  
**Version**: 1.0  
**Compatible with**: CREAMS Testing Guide v2.0