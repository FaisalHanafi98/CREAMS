import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { ROUTES } from '../../fixtures/test-data';

/**
 * CREAMS RBAC Tests - Admin Role
 *
 * Tests that Admin has full access to all system features.
 * Admin should be able to access all routes without restriction.
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */

// Load pre-authenticated admin session (saved in global-setup.ts)
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('RBAC - Admin Access', () => {

  test.describe('Dashboard Access', () => {

    test('Admin can access own dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminDashboard}`);
      await expect(page).toHaveURL(/admin\/dashboard/);
      await expect(page.locator('text=Admin Dashboard').first()).toBeVisible();
    });
  });

  test.describe('User Management', () => {

    test('Admin can access user management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminUsers}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access staff registration', async ({ page }) => {
      await page.goto('http://localhost:8000/staffs/register');
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access volunteer management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminVolunteers}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });

  test.describe('Centre Management', () => {

    test('Admin can access centre list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminCentres}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
      await expect(page.locator('text=Centre Management').first()).toBeVisible();
    });

    test('Admin can access centre assets', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.assetParents}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access centre attendance', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.centreAttendance}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });

  test.describe('Trainee Management', () => {

    test('Admin can access trainee list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access trainee registration', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });

  test.describe('Activity Management', () => {

    test('Admin can access activity list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesHome}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access activity categories', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCategories}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access activity schedule', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesSchedule}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });

    test('Admin can access create activity', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesCreate}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });

  test.describe('Profile Access', () => {

    test('Admin can access own profile', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.profile}`);
      await page.waitForLoadState('domcontentloaded');

      await expect(page).not.toHaveURL(/login/);
    });
  });
});
