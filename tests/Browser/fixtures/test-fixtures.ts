/**
 * Custom Playwright Test Fixtures
 *
 * Extends the base Playwright test with E2E testing mode flag
 * This allows client-side JS to detect and bypass validation for testing
 */

import { test as base, Page } from '@playwright/test';

/**
 * Extended test fixture with E2E mode initialization
 */
export const test = base.extend<{ e2ePage: Page }>({
  e2ePage: async ({ page }, use) => {
    // Inject E2E testing flag before every navigation
    await page.addInitScript(() => {
      (window as any).__E2E_TESTING__ = true;
    });

    await use(page);
  },
});

/**
 * Helper to add E2E flag to any page
 * Use this in beforeEach if not using the e2ePage fixture
 */
export async function enableE2EMode(page: Page): Promise<void> {
  await page.addInitScript(() => {
    (window as any).__E2E_TESTING__ = true;
  });
}

/**
 * Re-export expect from base
 */
export { expect } from '@playwright/test';
