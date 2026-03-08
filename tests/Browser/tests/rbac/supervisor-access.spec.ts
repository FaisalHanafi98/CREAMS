import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { ROUTES } from '../../fixtures/test-data';

/**
 * CREAMS RBAC Tests - Supervisor Role
 *
 * Tests that Supervisor has appropriate access.
 * Supervisor SHOULD have access to:
 * - Own dashboard
 * - Staff management (within centre)
 * - Trainee management
 * - Activity management
 * - Centre management (within scope)
 * - Attendance management
 *
 * Supervisor should NOT have access to:
 * - Admin-level system settings
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */

// Load pre-authenticated supervisor session (saved in global-setup.ts)
test.use({ storageState: path.join(__dirname, '../../.auth/supervisor.json') });

test.describe('RBAC - Supervisor Access', () => {

  test.describe('Allowed Access', () => {

    test('Supervisor can access own dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorDashboard}`);
      await expect(page).toHaveURL(/supervisor\/dashboard/);
      await expect(page.locator('text=Supervisor Dashboard').first()).toBeVisible();
    });

    test('Supervisor can access staff list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorUsers}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access staff registration', async ({ page }) => {
      await page.goto('http://localhost:8000/staffs/register');
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access volunteer applications', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminVolunteers}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access trainee list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access trainee registration', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access activity list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access activity categories', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCategories}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access activity schedule', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesSchedule}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access centre management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorCentres}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access centre attendance', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.centreAttendance}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access centre assets', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.assetParents}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Supervisor can access own profile', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.profile}`);
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

      expect(has403Page || hasUnauthorized || isRedirected).toBe(true);
    };

    test('Supervisor CANNOT access admin dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/dashboard');
    });

    test('Supervisor CANNOT access admin user management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminUsers}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/users');
    });

    test('Supervisor CANNOT access admin centres (full admin view)', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminCentres}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/centres');
    });
  });
});
