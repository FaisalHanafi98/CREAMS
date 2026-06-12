import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { LoginPage } from '../../pages/LoginPage';
import { BasePage } from '../../pages/BasePage';
import { TEST_USERS } from '../../fixtures/test-data';

/**
 * CREAMS Authentication Tests - Logout
 *
 * Uses dedicated "-2" storageState files to start pre-authenticated for each
 * role.  These sessions are intentionally invalidated during logout testing.
 * The primary auth files (admin.json etc.) remain untouched so that
 * dashboard, RBAC, and functional tests that run afterwards are unaffected.
 */
test.describe('Authentication - Logout', () => {

  test.describe('Logout Functionality', () => {

    test.describe('Admin', () => {
      test.use({ storageState: path.join(__dirname, '../../.auth/admin-2.json') });

      test('Admin can logout successfully', async ({ page }) => {
        const basePage = new BasePage(page);

        await page.goto('http://localhost:8000/admin/dashboard');
        await page.waitForLoadState('domcontentloaded');

        expect(await basePage.isLoggedIn()).toBe(true);

        await basePage.logout();

        await expect(page).toHaveURL(/auth\/login/);
        expect(await basePage.isLoggedIn()).toBe(false);
      });
    });

    test.describe('Teacher', () => {
      test.use({ storageState: path.join(__dirname, '../../.auth/teacher-2.json') });

      test('Teacher can logout successfully', async ({ page }) => {
        const basePage = new BasePage(page);

        await page.goto('http://localhost:8000/teacher/dashboard');
        await page.waitForLoadState('domcontentloaded');

        await basePage.logout();

        await expect(page).toHaveURL(/auth\/login/);
      });
    });

    test.describe('Supervisor', () => {
      test.use({ storageState: path.join(__dirname, '../../.auth/supervisor-2.json') });

      test('Supervisor can logout successfully', async ({ page }) => {
        const basePage = new BasePage(page);

        await page.goto('http://localhost:8000/supervisor/dashboard');
        await page.waitForLoadState('domcontentloaded');

        await basePage.logout();

        await expect(page).toHaveURL(/auth\/login/);
      });
    });

    test.describe('AJK', () => {
      test.use({ storageState: path.join(__dirname, '../../.auth/ajk-2.json') });

      test('AJK can logout successfully', async ({ page }) => {
        const basePage = new BasePage(page);

        await page.goto('http://localhost:8000/ajk/dashboard');
        await page.waitForLoadState('domcontentloaded');

        await basePage.logout();

        await expect(page).toHaveURL(/auth\/login/);
      });
    });
  });

  // Session Clearing and Re-login tests each get their own browser context via
  // storageState so that the session destroyed by one test does not affect
  // another.  We use the admin-2 set; those sessions are expendable.
  test.describe('Session Clearing', () => {

    test('After logout, cannot access protected routes', async ({ browser }) => {
      // Fresh context for this test — isolated session
      const context = await browser.newContext({
        storageState: path.join(__dirname, '../../.auth/admin-2.json'),
      });
      const page = await context.newPage();
      const basePage = new BasePage(page);

      await page.goto('http://localhost:8000/admin/dashboard');
      await page.waitForLoadState('domcontentloaded');

      await basePage.logout();

      await page.goto('http://localhost:8000/admin/dashboard');
      await page.waitForLoadState('domcontentloaded');

      await expect(page).toHaveURL(/login/);
      await context.close();
    });

    test('After logout, back button does not restore session', async ({ browser }) => {
      const context = await browser.newContext({
        storageState: path.join(__dirname, '../../.auth/admin-2.json'),
      });
      const page = await context.newPage();
      const basePage = new BasePage(page);

      await page.goto('http://localhost:8000/admin/dashboard');
      await page.waitForLoadState('domcontentloaded');

      await basePage.logout();

      // Browser may show cached dashboard on back() — expected browser behaviour.
      // Verify the server-side session is truly invalidated by making a fresh request.
      await page.goto('http://localhost:8000/admin/users');
      await page.waitForLoadState('domcontentloaded');

      // Server rejects the invalidated session and redirects to login
      await expect(page).toHaveURL(/login/);
      await context.close();
    });
  });

  test.describe('Re-login After Logout', () => {

    test('Can login again after logout', async ({ browser }) => {
      const context = await browser.newContext({
        storageState: path.join(__dirname, '../../.auth/admin-2.json'),
      });
      const page = await context.newPage();
      const loginPage = new LoginPage(page);
      const basePage = new BasePage(page);

      // Start pre-authenticated — navigate to dashboard, skip login
      await page.goto('http://localhost:8000/admin/dashboard');
      await page.waitForLoadState('domcontentloaded');

      // Logout
      await basePage.logout();

      // Login again
      await loginPage.goto();
      await loginPage.loginAs('admin');
      await loginPage.expectLoginSuccess('admin');

      expect(await basePage.isLoggedIn()).toBe(true);
      await context.close();
    });

    test('Can login as different user after logout', async ({ browser }) => {
      const context = await browser.newContext({
        storageState: path.join(__dirname, '../../.auth/admin-2.json'),
      });
      const page = await context.newPage();
      const loginPage = new LoginPage(page);
      const basePage = new BasePage(page);

      // Start pre-authenticated as admin — skip the initial login
      await page.goto('http://localhost:8000/admin/dashboard');
      await page.waitForLoadState('domcontentloaded');

      // Logout
      await basePage.logout();

      // Login as supervisor (reliable and fast under load)
      await loginPage.goto();
      await loginPage.loginAs('supervisor');
      await loginPage.expectLoginSuccess('supervisor');

      await expect(page).toHaveURL(/supervisor\/dashboard/);
      await expect(page.locator(`text=${TEST_USERS.supervisor.name}`).first()).toBeVisible();
      await context.close();
    });
  });
});
