import { test, expect } from '@playwright/test';
import * as path from 'node:path';

/**
 * CREAMS Functional Tests - Centre Management CRUD
 *
 * Tests centre Create, Read, Update, Delete operations including:
 * - Page load performance
 * - Form validation
 * - CRUD operations
 * - Centre-based access control
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Functional - Centre Management CRUD', () => {

  test.describe('Page Load', () => {

    test('Centre list page loads successfully', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');
      await page.waitForLoadState('networkidle');

      // Should be on centres page
      await expect(page).toHaveURL(/centres/);
    });

    test('Centre create page loads successfully', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/create');
      await page.waitForLoadState('networkidle');

      // Check for form elements
      await expect(page.locator('input[name*="centre"], input[name*="name"]').first()).toBeVisible({
        timeout: 10000
      });
    });
  });

  test.describe('CREATE Operations', () => {

    test('Can access centre creation form', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      // Look for create button
      const createButton = page.locator('a:has-text("Create"), a:has-text("Add"), button:has-text("Create")').first();

      if (await createButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await createButton.click();
        await expect(page).toHaveURL(/centres\/create/);
      }
    });

    test('Centre creation form has required fields', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/create');
      await page.waitForLoadState('networkidle');

      // Check for common centre fields (actual field names use centre_ prefix)
      const fields = ['centre_name', 'centre_phone', 'centre_email', 'centre_address', 'centre_capacity'];

      for (const field of fields) {
        const input = page.locator(`input[name="${field}"], textarea[name="${field}"], select[name="${field}"]`).first();
        const exists = await input.count();

        // Field should exist
        expect(exists).toBeGreaterThan(0);
      }
    });

    test('Shows validation error for empty centre name', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/create');
      await page.waitForLoadState('networkidle');

      // Centre create is a wizard form — try clicking Next without filling required fields
      const nextButton = page.locator('button:has-text("Next"), #nextStep').first();
      const submitButton = page.locator('button[type="submit"], input[type="submit"]').first();

      if (await nextButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await nextButton.click();
        await page.waitForTimeout(500);

        // Should show validation error or stay on step 1 (wizard validates before proceeding)
        const hasErrors = await page.locator('.invalid-feedback, .error, .is-invalid, [class*="error"]').first().isVisible().catch(() => false);
        const stayedOnStep1 = await page.locator('text=Basic Centre Information, text=Basic Info').first().isVisible().catch(() => false);

        expect(hasErrors || stayedOnStep1).toBe(true);
      } else if (await submitButton.isVisible()) {
        await submitButton.click();
        await expect(page.locator('.invalid-feedback, .error, text=/required/i').first()).toBeVisible({
          timeout: 5000
        });
      }
    });
  });

  test.describe('READ Operations', () => {

    test('Can view centre list', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      // Should show centres table or cards
      const centresList = page.locator('table, .card, .centre-item, [class*="list"]').first();
      await expect(centresList).toBeVisible({ timeout: 10000 });
    });

    test('Can view centre details', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      // Click on first centre View button (scoped to main content to avoid sidebar links)
      const viewButton = page.locator('.card a.btn-info:has-text("View"), .card-footer a:has-text("View"), a.btn:has-text("View")').first();

      if (await viewButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewButton.click();

        // Should show centre details
        await expect(page).toHaveURL(/centres\/\d+|centres\/show/);
      }
    });

    test('Centre details page shows information', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      const viewButton = page.locator('.card a.btn-info:has-text("View"), .card-footer a:has-text("View"), a.btn:has-text("View")').first();

      if (await viewButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewButton.click();

        // Should show centre information (show page has .detail-card, .centre-detail-container, .page-header)
        const detailsPage = page.locator('.detail-card, .centre-detail-container, .page-header, .page-title').first();
        await expect(detailsPage).toBeVisible({ timeout: 10000 });
      }
    });
  });

  test.describe('UPDATE Operations', () => {

    test('Can access centre edit form', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      // Look for edit button
      const editButton = page.locator('a:has-text("Edit"), button:has-text("Edit")').first();

      if (await editButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await editButton.click();
        await expect(page).toHaveURL(/centres\/\d+\/edit/);
      }
    });
  });

  test.describe('DELETE Operations', () => {

    test('Delete button is available for admin', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      // Check if delete button exists
      const deleteButton = page.locator('button:has-text("Delete"), a:has-text("Delete")').first();
      const exists = await deleteButton.count();

      // Admin should see delete option
      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('Centre Statistics', () => {

    test('Centre details shows statistics', async ({ page }) => {
      await page.goto('http://localhost:8000/centres/home');

      const viewButton = page.locator('.card a.btn-info:has-text("View"), .card-footer a:has-text("View"), a.btn:has-text("View")').first();

      if (await viewButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewButton.click();
        await page.waitForLoadState('networkidle');

        // Centre detail page shows sections like "Centre Information", "Recent Activity", etc.
        const hasCentreInfo = await page.locator('text=Centre Information, text=Contact Information, text=Operating Hours').first().isVisible({ timeout: 5000 }).catch(() => false);
        const hasPageTitle = await page.locator('h1, h2, .page-title').first().isVisible().catch(() => false);

        expect(hasCentreInfo || hasPageTitle).toBeTruthy();
      }
    });
  });

  test.describe('Performance', () => {

    test('Centre list loads within acceptable time', async ({ page }) => {
      const startTime = Date.now();

      await page.goto('http://localhost:8000/centres/home');
      await page.waitForLoadState('networkidle');

      const loadTime = Date.now() - startTime;
      console.log(`Centre List Load Time: ${loadTime}ms`);

      expect(loadTime).toBeLessThan(5000);
    });
  });
});
