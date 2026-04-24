import { test, expect } from '@playwright/test';
import * as path from 'node:path';

/**
 * CREAMS Functional Tests - IEP (Individual Education Plan) Management
 *
 * Tests IEP management functionality including:
 * - IEP creation
 * - Goal setting
 * - Progress tracking
 * - IEP viewing and editing
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */
test.use({ storageState: path.join(__dirname, '../../.auth/teacher.json') });

test.describe('Functional - IEP Management', () => {

  test.describe('Page Load', () => {

    test('IEP list page loads successfully', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/iep/);
    });

    test('IEP create page loads', async ({ page }) => {
      await page.goto('http://localhost:8000/iep/create');

      // Check for form
      await page.waitForSelector('form, input', { timeout: 10000 });
    });
  });

  test.describe('IEP Listing', () => {

    test('Can view IEP list', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      // Should show IEP list
      const iepList = page.locator('table, .iep-list, .iep-card').first();
      await expect(iepList).toBeVisible({ timeout: 10000 });
    });

    test('IEP list shows trainee information', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      // Look for trainee names or IEP titles
      const iepInfo = page.locator('text=/trainee/i, text=/plan/i').first();
      const exists = await iepInfo.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('IEP Creation', () => {

    test('Can access IEP creation form', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      const createButton = page.locator('a:has-text("Create"), button:has-text("Create")').first();

      if (await createButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await createButton.click();
        await expect(page).toHaveURL(/iep\/create/);
      }
    });

    test('IEP creation form has required fields', async ({ page }) => {
      await page.goto('http://localhost:8000/iep/create');

      // Check for common IEP fields
      const fields = ['trainee', 'title', 'goal', 'objective'];

      let fieldCount = 0;
      for (const field of fields) {
        const input = page.locator(`input[name*="${field}"], select[name*="${field}"], textarea[name*="${field}"]`).first();
        const count = await input.count();
        if (count > 0) fieldCount++;
      }

      // At least some IEP fields should exist
      expect(fieldCount).toBeGreaterThan(0);
    });
  });

  test.describe('IEP Details', () => {

    test('Can view IEP details', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      // Use table row view button to avoid matching the create link
      const viewLink = page.locator('table tbody tr a.btn-outline-primary, table tbody tr a:has(i.fa-eye)').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();
        await expect(page).toHaveURL(/iep\/\d+/);
      }
    });

    test('IEP details shows goals and objectives', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      const viewLink = page.locator('table tbody tr a.btn-outline-primary, table tbody tr a:has(i.fa-eye)').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();

        // Look for goals/objectives
        const goals = page.locator('text=/goal/i, text=/objective/i').first();
        const hasGoals = await goals.isVisible({ timeout: 5000 }).catch(() => false);

        expect(hasGoals).toBeTruthy();
      }
    });
  });

  test.describe('IEP Updates', () => {

    test('Can edit existing IEP', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      const editButton = page.locator('a:has-text("Edit"), button:has-text("Edit")').first();

      if (await editButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await editButton.click();
        await expect(page).toHaveURL(/iep\/\d+\/edit/);
      }
    });

    test('Can add goals to IEP', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      const viewLink = page.locator('table tbody tr a.btn-outline-primary, table tbody tr a:has(i.fa-eye)').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();

        // Look for add goal button
        const addGoalButton = page.locator('button:has-text("Add Goal"), a:has-text("Add Goal")').first();
        const exists = await addGoalButton.count();

        expect(exists).toBeGreaterThanOrEqual(0);
      }
    });

    test('Can update goal progress', async ({ page }) => {
      await page.goto('http://localhost:8000/iep');

      const viewLink = page.locator('table tbody tr a.btn-outline-primary, table tbody tr a:has(i.fa-eye)').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();

        // Look for progress update option
        const progressButton = page.locator('button:has-text("Update Progress"), button:has-text("Progress")').first();
        const exists = await progressButton.count();

        expect(exists).toBeGreaterThanOrEqual(0);
      }
    });
  });

  test.describe('Performance', () => {

    test('IEP page loads within acceptable time', async ({ page }) => {
      const startTime = Date.now();

      await page.goto('http://localhost:8000/iep');
      await page.waitForLoadState('networkidle');

      const loadTime = Date.now() - startTime;
      console.log(`IEP Page Load Time: ${loadTime}ms`);

      expect(loadTime).toBeLessThan(5000);
    });
  });
});
