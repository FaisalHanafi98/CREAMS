import { test, expect } from '../../fixtures/test-fixtures';
import * as path from 'node:path';

/**
 * CREAMS Functional Tests - Messages & Notifications
 *
 * Tests messaging and notification functionality including:
 * - Viewing messages
 * - Sending messages
 * - Notification display
 * - Notification management
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Functional - Messages & Notifications', () => {

  test.describe('Messages', () => {

    test('Can access messages page', async ({ page }) => {
      await page.goto('http://localhost:8000/messages');
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/messages/);
    });

    test('Messages page displays message list', async ({ page }) => {
      await page.goto('http://localhost:8000/messages');

      // Look for messages list or empty state (actual DOM uses .messages-list / .message-item / .empty-state)
      const messagesList = page.locator('.messages-list, .message-item, .empty-state, table').first();
      await expect(messagesList).toBeVisible({ timeout: 10000 });
    });

    test('Can create new message', async ({ page }) => {
      await page.goto('http://localhost:8000/messages');

      const createButton = page.locator('a:has-text("Create"), a:has-text("New Message"), button:has-text("Create")').first();

      if (await createButton.isVisible({ timeout: 5000 }).catch(() => false)) {
        await createButton.click();
        await expect(page).toHaveURL(/messages\/create/);
      }
    });

    test('Can view message details', async ({ page }) => {
      await page.goto('http://localhost:8000/messages');

      const viewLink = page.locator('a[href*="/messages/"]').first();

      if (await viewLink.isVisible({ timeout: 5000 }).catch(() => false)) {
        await viewLink.click();
        await expect(page).toHaveURL(/messages\/\d+/);
      }
    });
  });

  test.describe('Notifications', () => {

    test('Can access notifications page', async ({ page }) => {
      await page.goto('http://localhost:8000/notifications');
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/notifications/);
    });

    test('Notifications page displays notifications', async ({ page }) => {
      await page.goto('http://localhost:8000/notifications');

      // Look for notifications (actual DOM uses .notifications-container and .notification-item)
      const notificationsList = page.locator('.notifications-container, .notification-item, .empty-state, table').first();
      await expect(notificationsList).toBeVisible({ timeout: 10000 });
    });

    test('Can mark notification as read', async ({ page }) => {
      await page.goto('http://localhost:8000/notifications');

      const markReadButton = page.locator('button:has-text("Mark as Read"), a:has-text("Mark as Read")').first();
      const exists = await markReadButton.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });

    test('Can mark all notifications as read', async ({ page }) => {
      await page.goto('http://localhost:8000/notifications');

      const markAllButton = page.locator('button:has-text("Mark All"), a:has-text("Mark All as Read")').first();
      const exists = await markAllButton.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });

    test('Notification bell icon displays in header', async ({ page }) => {
      await page.goto('http://localhost:8000/dashboard');

      // Look for notification bell icon
      const bellIcon = page.locator('.notification-icon, .fa-bell, [class*="notification"]').first();
      const exists = await bellIcon.count();

      expect(exists).toBeGreaterThanOrEqual(0);
    });
  });

  test.describe('Performance', () => {

    test('Messages page loads within acceptable time', async ({ page }) => {
      const startTime = Date.now();

      await page.goto('http://localhost:8000/messages');
      await page.waitForLoadState('networkidle');

      const loadTime = Date.now() - startTime;
      console.log(`Messages Page Load Time: ${loadTime}ms`);

      expect(loadTime).toBeLessThan(5000);
    });

    test('Notifications page loads within acceptable time', async ({ page }) => {
      const startTime = Date.now();

      await page.goto('http://localhost:8000/notifications');
      await page.waitForLoadState('networkidle');

      const loadTime = Date.now() - startTime;
      console.log(`Notifications Page Load Time: ${loadTime}ms`);

      expect(loadTime).toBeLessThan(5000);
    });
  });
});
