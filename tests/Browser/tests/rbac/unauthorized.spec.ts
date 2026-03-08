import { test, expect } from '@playwright/test';
import { ROUTES } from '../../fixtures/test-data';

/**
 * CREAMS RBAC Tests - Unauthorized Access
 *
 * Tests that unauthenticated users cannot access protected routes.
 * All protected routes should redirect to login page.
 */
test.describe('RBAC - Unauthorized Access Protection', () => {

  test.describe('Dashboard Routes', () => {

    test('Unauthenticated user cannot access admin dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminDashboard}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access supervisor dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.supervisorDashboard}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access teacher dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.teacherDashboard}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access AJK dashboard', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.ajkDashboard}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });
  });

  test.describe('Management Routes', () => {

    test('Unauthenticated user cannot access user management', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminUsers}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access trainee list', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesHome}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access trainee registration', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.traineesCreate}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access activities', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesHome}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access activity schedule', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.activitiesSchedule}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access centres', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.adminCentres}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });

    test('Unauthenticated user cannot access profile', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.profile}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
    });
  });

  test.describe('Public Routes', () => {

    test('Login page is accessible without authentication', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.login}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/login/);
      await expect(page.locator('#identifier')).toBeVisible();
    });

    test('Home page is accessible without authentication', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.home}`);
      await page.waitForLoadState('networkidle');

      // Home page should not redirect to login
      await expect(page).not.toHaveURL(/login/);
    });

    test('Forgot password page is accessible without authentication', async ({ page }) => {
      await page.goto(`http://localhost:8000${ROUTES.forgotPassword}`);
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/forgot-password/);
    });
  });
});
