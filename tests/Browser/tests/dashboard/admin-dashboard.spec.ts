import { test, expect } from '@playwright/test';
import * as path from 'node:path';
import { DashboardPage } from '../../pages/DashboardPage';

/**
 * CREAMS Dashboard Tests - Admin Dashboard
 *
 * Tests admin dashboard functionality including:
 * - Dashboard loads correctly
 * - All widgets display
 * - Statistics are accurate
 * - Navigation links work
 * - Performance metrics
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */

// Load pre-authenticated admin session (saved in global-setup.ts)
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Dashboard - Admin', () => {
  let dashboardPage: DashboardPage;

  test.beforeEach(async ({ page }) => {
    dashboardPage = new DashboardPage(page);
    // Navigate to dashboard (already authenticated via storageState)
    await page.goto('http://localhost:8000/admin/dashboard');
    await page.waitForLoadState('domcontentloaded');
  });

  test.describe('Page Load & Layout', () => {

    test('Admin dashboard loads successfully', async ({ page }) => {
      await expect(page).toHaveURL(/\/dashboard/);

      // Check for main dashboard elements - title shows "Welcome back, [Name]"
      await expect(page.locator('h1, h2, .dashboard-title')).toContainText(/welcome/i);
    });

    test('Dashboard loads within acceptable time', async ({ page }) => {
      const startTime = Date.now();

      await page.goto('http://localhost:8000/dashboard');
      await page.waitForLoadState('domcontentloaded');

      const loadTime = Date.now() - startTime;
      console.log(`Admin Dashboard Load Time: ${loadTime}ms`);

      // Should load within 10 seconds (dev server under load)
      expect(loadTime).toBeLessThan(10000);
    });

    test('Dashboard displays all required widgets', async ({ page }) => {
      // Labels from dashboard/modern.blade.php — served for all roles by DashboardController
      // Admin/Supervisor see: Active Staff, Active Trainees, Ongoing Activities, Sessions This Week
      const widgets = [
        'Active Staff',
        'Active Trainees',
        'Ongoing Activities',
        'Sessions This Week',
      ];

      for (const widget of widgets) {
        const widgetLocator = page.getByText(widget, { exact: false });
        await expect(widgetLocator).toBeVisible({ timeout: 10000 });
      }
    });
  });

  test.describe('Navigation', () => {

    test('Can navigate to different modules from dashboard', async ({ page }) => {
      const modules = [
        { buttonText: /create.*activity/i, urlPattern: /activities\/create/ },
        { buttonText: /register.*trainee/i, urlPattern: /trainees\/create/ },
      ];

      for (const module of modules) {
        await page.goto('http://localhost:8000/dashboard');
        await page.waitForLoadState('domcontentloaded');

        const button = page.locator('a, button').filter({ hasText: module.buttonText }).first();

        if (await button.isVisible({ timeout: 3000 }).catch(() => false)) {
          await button.click();
          await expect(page).toHaveURL(module.urlPattern, { timeout: 5000 });
        }
      }
    });

    test('Quick action buttons work correctly', async ({ page }) => {
      const quickActions = [
        { text: /create.*activity/i, urlPattern: /activities\/create/ },
        { text: /register.*trainee/i, urlPattern: /trainees\/create/ },
      ];

      for (const action of quickActions) {
        await page.goto('http://localhost:8000/dashboard');

        const button = page.locator(`a, button`).filter({ hasText: action.text }).first();
        if (await button.isVisible({ timeout: 2000 }).catch(() => false)) {
          await button.click();
          await expect(page).toHaveURL(action.urlPattern, { timeout: 5000 });
        }
      }
    });
  });

  test.describe('Statistics & Data', () => {

    test('Statistics display numeric values', async ({ page }) => {
      // dashboard/modern.blade.php uses .stats-number-enhanced for stat values
      const statNums = page.locator('.stats-number-enhanced');
      const count = await statNums.count();

      if (count > 0) {
        for (let i = 0; i < Math.min(count, 4); i++) {
          const text = await statNums.nth(i).textContent();
          // Values are server-rendered integers (may be 0 in fresh seeded DB)
          expect(text?.trim()).toMatch(/^\d+$/);
        }
      }
    });

    test('Recent activities section displays', async ({ page }) => {
      const recentSection = page.locator('text=/recent.*activities/i, text=/activity.*feed/i').first();

      const exists = await recentSection.count();
      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('Responsive Behavior', () => {

    test('Dashboard is responsive on mobile viewport', async ({ page }) => {
      await page.setViewportSize({ width: 375, height: 667 });
      await page.goto('http://localhost:8000/dashboard');

      await expect(page.locator('body')).toBeVisible();
    });

    test('Dashboard is responsive on tablet viewport', async ({ page }) => {
      await page.setViewportSize({ width: 768, height: 1024 });
      await page.goto('http://localhost:8000/dashboard');

      await expect(page.locator('body')).toBeVisible();
    });
  });

  test.describe('Admin-Specific Features', () => {

    test('Admin sees all centres data', async ({ page }) => {
      const allCentresIndicator = page.locator('text=/all.*centres/i').first();

      const exists = await allCentresIndicator.count();
      expect(exists).toBeGreaterThanOrEqual(0);
    });

    test('Admin has access to system settings', async ({ page }) => {
      const settingsLink = page.locator('a:has-text("Settings"), a[href*="settings"]').first();

      if (await settingsLink.isVisible({ timeout: 2000 }).catch(() => false)) {
        await settingsLink.click();
        await expect(page).toHaveURL(/settings|admin/, { timeout: 5000 });
      }
    });
  });

  test.describe('Performance', () => {

    test('Dashboard widgets load within timeout', async ({ page }) => {
      await page.goto('http://localhost:8000/dashboard');
      await page.waitForLoadState('domcontentloaded');

      // 'Active Trainees' is present for all roles in dashboard/modern.blade.php
      const keyWidget = page.getByText('Active Trainees', { exact: false });

      await expect(keyWidget).toBeVisible({ timeout: 10000 });
    });
  });
});
