import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { ROUTES } from '../../fixtures/test-data';

/**
 * CREAMS RBAC Tests - AJK Role
 *
 * AJK (Ahli Jawatankuasa/Committee Member) role focuses on
 * facility management and support tasks.
 *
 * AJK SHOULD have access to:
 * - Own dashboard
 * - Trainee management
 * - Activity management
 * - Centre assets
 * - Profile
 *
 * AJK should NOT have access to:
 * - Admin-level management
 * - Full staff management
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */

// Load pre-authenticated AJK session (saved in global-setup.ts)
test.use({ storageState: path.join(__dirname, '../../.auth/ajk.json') });

test.describe('RBAC - AJK Access', () => {

  test.describe('Allowed Access', () => {

    test('AJK can access own dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.ajkDashboard}`);
      await expect(page).toHaveURL(/ajk\/dashboard/);
      await expect(page.locator('text=Ajk Dashboard').first()).toBeVisible();
    });

    test('AJK can access trainee list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access trainee registration', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access activity list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access activity categories', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCategories}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access activity schedule', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesSchedule}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access centre management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.ajkCentres}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access centre assets', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.assetParents}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('AJK can access own profile', async ({ page }) => {
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

      // Access is blocked if: 403 page shown OR redirected away OR login page
      expect(has403Page || hasUnauthorized || isRedirected).toBe(true);
    };

    test('AJK CANNOT access admin dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/dashboard');
    });

    test('AJK CANNOT access admin user management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminUsers}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/users');
    });

    test('AJK CANNOT access supervisor dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/supervisor/dashboard');
    });

    test('AJK CANNOT access teacher dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.teacherDashboard}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/teacher/dashboard');
    });

    test('AJK CANNOT access admin centres', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminCentres}`);
      await page.waitForLoadState('domcontentloaded');
      await expectAccessDenied(page, '/admin/centres');
    });
  });
});
