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
5. **AUTH-MESSAGE-001**: Error message says "No account found with this IIUM ID" should say "email or IIUM ID"

### DASHBOARD ISSUES
6. **DASH-SEARCH-001**: Search function not working (should search Trainees, Users/Staff, Activities)
7. **DASH-SEARCH-002**: Search results format needs standardization
8. **DASH-HERO-001**: Remove confusing statistics from hero section (Online status, redundant counts)
9. **DASH-HERO-002**: Move date/time to right, add weather API integration
10. **DASH-HERO-003**: Make activity schedule messages more specific per centre
11. **DASH-DEBUG-001**: Remove debug messages from general section
12. **DASH-ACTIVITIES-001**: Implement recent activities tracking table
13. **DASH-STATS-001**: Move "Live Data" info before statistics section
14. **DASH-ATTEND-001**: Prevent duplicate attendance marking on same day
15. **DASH-ATTEND-002**: Add attendance symbols to calendar
16. **DASH-ATTEND-003**: Fix attendance marking - should be per user, not global
17. **DASH-QUICKACTIONS-001**: Make quick actions role-specific (not same for all roles)
18. **DASH-AJK-001**: Elevate AJK role level to match Supervisor access
19. **DASH-AJK-002**: Add Staff module view access to AJK left navbar

---

## Completed Fixes

_No fixes completed yet. Awaiting approval to start._

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
