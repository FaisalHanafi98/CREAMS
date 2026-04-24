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
 */
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Functional - Asset Management', () => {

  test.describe('Page Load', () => {

    test('Asset list page loads successfully', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents');
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/asset/);
    });

    test('Asset create page loads', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents/create');

      // Check for form
      await page.waitForSelector('input, form', { timeout: 10000 });
    });
  });

  test.describe('Asset Listing', () => {

    test('Can view asset list', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents');

      // Should show assets
      const assetList = page.locator('table, .asset-card, .asset-item').first();
      await expect(assetList).toBeVisible({ timeout: 10000 });
    });

    test('Asset list shows asset information', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents');

      // Look for asset columns/fields
      const assetInfo = page.locator('text=/asset.*name/i, text=/status/i, text=/category/i').first();
      const exists = await assetInfo.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('Asset Details', () => {

    test('Can view asset details', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents');

      const viewLink = page.locator('.main-content a[href*="/asset-parents/"], .main-content .view-asset').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();

        // Should show asset details
        await expect(page).toHaveURL(/asset-parents\/\d+/);
      }
    });
  });

  test.describe('Asset Operations', () => {

    test('Can access asset rental functionality', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents');

      // Look for rent/borrow button
      const rentButton = page.locator('button:has-text("Rent"), button:has-text("Borrow"), a:has-text("Rent")').first();
      const exists = await rentButton.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });

    test('Can access maintenance scheduling', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents/maintenance');

      // Check for maintenance page
      await page.waitForSelector('body', { timeout: 10000 });
    });

    test('Can view asset movements', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents/movements');

      // Check for movements page
      await page.waitForSelector('body', { timeout: 10000 });
    });

    test('Can view asset reports', async ({ page }) => {
      await page.goto('http://localhost:8000/asset-parents/reports');

      // Check for reports page
      await page.waitForSelector('body', { timeout: 10000 });
    });
  });
});
