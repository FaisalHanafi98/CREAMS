import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { ROUTES } from '../../fixtures/test-data';

/**
 * CREAMS RBAC Tests - Teacher Role
 *
 * Tests that Teacher has appropriate access restrictions.
 * Teacher should NOT have access to:
 * - Admin user management
 * - Centre management (full)
 * - Staff registration
 *
 * Teacher SHOULD have access to:
 * - Own dashboard
 * - Activities
 * - Trainees (view/limited)
 * - Profile
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */

// Load pre-authenticated teacher session (saved in global-setup.ts)
test.use({ storageState: path.join(__dirname, '../../.auth/teacher.json') });

test.describe('RBAC - Teacher Access', () => {

  test.describe('Allowed Access', () => {

    test('Teacher can access own dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.teacherDashboard}`);
      await expect(page).toHaveURL(/teacher\/dashboard/);
      await expect(page.locator('text=Teacher Dashboard').first()).toBeVisible();
    });

    test('Teacher can access activity list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access activity categories', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCategories}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access activity schedule', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesSchedule}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can create activities', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access trainee list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access trainee registration', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access own profile', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.profile}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Teacher can access centre assets', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.assetParents}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });

  test.describe('Restricted Access', () => {

    // Helper: CREAMS shows 403 page instead of redirecting
    const expectAccessDenied = async (page: any, restrictedPath: string) => {
      const url = page.url();
      const has403Page = await page.locator('text=403').isVisible().catch(() => false);
      const hasUnauthorized = await page.locator('text=Unauthorized').isVisible().catch(() => false);
      const isRedirected = !url.includes(restrictedPath) || url.includes('/login');

      // Access is blocked if: 403 page shown OR redirected away OR login page
      expect(has403Page || hasUnauthorized || isRedirected).toBe(true);
    };

    test('Teacher CANNOT access admin user management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminUsers}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/users');
    });

    test('Teacher CANNOT access admin dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/dashboard');
    });

    test('Teacher CANNOT access admin centres', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminCentres}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/centres');
    });

    test('Teacher CANNOT access supervisor dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/supervisor/dashboard');
    });

    test('Teacher CANNOT access centre attendance management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.centreAttendance}`);
      await page.waitForLoadState('domcontentloaded');
      // Teacher may have limited or no access - check for 403 or redirect
      await expectAccessDenied(page, '/centre/attendance');
    });
  });
});
