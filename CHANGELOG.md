# CREAMS System Changelog

## Version Control for UAT Fixes
**Date Started:** October 21, 2025
**Session:** Pre-UAT Manual Testing Fixes

---

## Pending Fixes Queue

### GENERAL ISSUES
1. **CONTACT-FLASH-001**: Implement paginated error display for contact forms (currently only on volunteer)
2. **FLASH-GLOBAL-001**: Standardize flash message format across ALL forms in system
3. **VOLUNTEER-FLASH-001**: Success message disappears too fast on volunteer forms

### AUTHENTICATION ISSUES
4. **AUTH-LOGIN-001**: Login fails for seeded users (password is 'password123' not 'password')

### DASHBOARD ISSUES
5. **DASH-SEARCH-001**: Search function not working (should search Trainees, Users/Staff, Activities)
6. **DASH-SEARCH-002**: Search results format needs standardization
7. **DASH-HERO-001**: Remove confusing statistics from hero section (Online status, redundant counts)
8. **DASH-HERO-002**: Move date/time to right, add weather API integration
9. **DASH-HERO-003**: Make activity schedule messages more specific per centre
10. **DASH-DEBUG-001**: Remove debug messages from general section
11. **DASH-ACTIVITIES-001**: Implement recent activities tracking table
12. **DASH-STATS-001**: Move "Live Data" info before statistics section
13. **DASH-ATTEND-001**: Prevent duplicate attendance marking on same day
14. **DASH-ATTEND-002**: Add attendance symbols to calendar
15. **DASH-ATTEND-003**: Fix attendance marking - should be per user, not global
16. **DASH-QUICKACTIONS-001**: Make quick actions role-specific (not same for all roles)
17. **DASH-AJK-001**: Elevate AJK role level to match Supervisor access
18. **DASH-AJK-002**: Add Staff module view access to AJK left navbar

---

## Completed Fixes

- **AUTH-MESSAGE-001**: Error message updated to "No account found with this email or IIUM ID" in `MainController::check()` (line 393).

---

## Rollback Instructions

To rollback any version:
1. Use git to revert to the specific commit tagged with version number
2. Run `php artisan migrate:rollback` if database changes were made
3. Clear cache: `php artisan cache:clear && php artisan config:clear`

---

## Notes
- Each fix = 1 version = 1 commit
- Test after each fix before moving to next
- Mark issues in tracker after each successful fix
