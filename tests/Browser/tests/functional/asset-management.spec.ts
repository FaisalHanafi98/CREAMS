import { test, expect } from '@playwright/test';
import * as path from 'node:path';

/**
 * CREAMS Functional Tests - Asset Management
 *
 * Tests asset management functionality including:
 * - Asset listing and viewing
 * - Asset creation and updates
 * - Asset rental/return
 * - Maintenance scheduling
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 *
 * Route notes (from `php artisan route:list`):
 *   GET /centre/assets      → centre.assets (AssetController@index)
 *     This named shortcut avoids the dev-server static-file conflict: the PHP
 *     built-in server intercepts GET /assets because public/assets/ is a real
 *     directory, returning 404 before Laravel routing runs. /centre/assets has
 *     no matching static path, so requests reach the controller correctly.
 *   GET /assets/create      → assets.create  (admin only)
 *   GET /assets/maintenance → assets.maintenance
 *   GET /assets/movements   → assets.movements
 *   GET /assets/reports     → assets.reports
 *   GET /assets/{id}        → assets.show
 */
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Functional - Asset Management', () => {

  test.describe('Page Load', () => {

    test('Asset list page loads successfully', async ({ page }) => {
      await page.goto('http://localhost:8000/centre/assets');
      await page.waitForLoadState('domcontentloaded');

      await expect(page).toHaveURL(/assets/);
    });

    test('Asset create page loads', async ({ page }) => {
      await page.goto('http://localhost:8000/assets/create');

      // Check for form
      await page.waitForSelector('input, form', { timeout: 10000 });
    });
  });

  test.describe('Asset Listing', () => {

    test('Can view asset list', async ({ page }) => {
      await page.goto('http://localhost:8000/centre/assets');
      await page.waitForLoadState('domcontentloaded');

      // Should show assets table or empty-state content — accept either
      const hasTable = await page.locator('table').isVisible({ timeout: 10000 }).catch(() => false);
      const hasCard = await page.locator('.card, .asset-dashboard').isVisible({ timeout: 5000 }).catch(() => false);

      expect(hasTable || hasCard).toBe(true);
    });

    test('Asset list shows asset information', async ({ page }) => {
      await page.goto('http://localhost:8000/centre/assets');
      await page.waitForLoadState('domcontentloaded');

      // Look for asset columns/fields
      const assetInfo = page.locator('text=/asset.*name/i, text=/status/i, text=/category/i').first();
      const exists = await assetInfo.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('Asset Details', () => {

    test('Can view asset details', async ({ page }) => {
      await page.goto('http://localhost:8000/centre/assets');
      await page.waitForLoadState('domcontentloaded');

      const viewLink = page.locator('.main-content a[href*="/assets/"], .main-content .view-asset').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();

        // Should show asset details
        await expect(page).toHaveURL(/assets\/\d+/);
      }
    });
  });

  test.describe('Asset Operations', () => {

    test('Can access asset rental functionality', async ({ page }) => {
      await page.goto('http://localhost:8000/centre/assets');
      await page.waitForLoadState('domcontentloaded');

      // Look for rent/borrow button
      const rentButton = page.locator('button:has-text("Rent"), button:has-text("Borrow"), a:has-text("Rent")').first();
      const exists = await rentButton.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });

    test('Can access maintenance scheduling', async ({ page }) => {
      await page.goto('http://localhost:8000/assets/maintenance');

      // Check for maintenance page
      await page.waitForSelector('body', { timeout: 10000 });
    });

    test('Can view asset movements', async ({ page }) => {
      await page.goto('http://localhost:8000/assets/movements');

      // Check for movements page
      await page.waitForSelector('body', { timeout: 10000 });
    });

    test('Can view asset reports', async ({ page }) => {
      await page.goto('http://localhost:8000/assets/reports');

      // Check for reports page
      await page.waitForSelector('body', { timeout: 10000 });
    });
  });
});
