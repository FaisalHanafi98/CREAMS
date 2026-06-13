import { Page, expect } from '@playwright/test';
import { BASE_URL, SELECTORS } from '../fixtures/test-data';

/**
 * Base Page Object for CREAMS
 * Contains common methods shared across all pages
 */
export class BasePage {
  constructor(protected page: Page) {}

  /**
   * Navigate to a path relative to base URL
   */
  async navigate(path: string): Promise<void> {
    await this.page.goto(`${BASE_URL}${path}`);
    await this.waitForPageLoad();
  }

  /**
   * Wait for page to fully load
   */
  async waitForPageLoad(): Promise<void> {
    await this.page.waitForLoadState('domcontentloaded');
  }

  /**
   * Get current page URL
   */
  async getCurrentUrl(): Promise<string> {
    return this.page.url();
  }

  /**
   * Get page title
   */
  async getPageTitle(): Promise<string> {
    return this.page.title();
  }

  /**
   * Check if user is logged in by looking for logout button
   */
  async isLoggedIn(): Promise<boolean> {
    try {
      return await this.page.locator(SELECTORS.navigation.logoutButton).isVisible({ timeout: 2000 });
    } catch {
      return false;
    }
  }

  /**
   * Logout from the application
   * Note: CREAMS uses a form button, not a link
   *
   * If the logout button is absent (e.g. the session was already invalidated
   * by a previous test that shared the same storageState file), this method
   * returns immediately — a missing logout button means the user is already
   * effectively logged out, so no further action is needed.
   */
  async logout(): Promise<void> {
    // locator.count() is immediate (no waiting), which is intentional:
    // by the time logout() is called, the page has already waited for
    // domcontentloaded, so all server-rendered elements are in the DOM.
    const logoutBtnCount = await this.page.locator(SELECTORS.navigation.logoutButton).count();
    if (logoutBtnCount === 0) {
      return; // Already logged out (session invalid or expired)
    }

    await this.page.evaluate(() => {
      const logoutBtn = document.querySelector('.logout-btn') as HTMLElement;
      if (logoutBtn) logoutBtn.click();
    });
    // MainController::logout redirects to the login page, not home
    await this.page.waitForURL('**/auth/login');
  }

  /**
   * Check if current URL contains a specific path
   */
  async urlContains(path: string): Promise<boolean> {
    const url = await this.getCurrentUrl();
    return url.includes(path);
  }

  /**
   * Wait for URL to contain specific path
   */
  async waitForUrlContains(path: string, timeout = 10000): Promise<void> {
    await this.page.waitForURL(`**${path}**`, { timeout });
  }

  /**
   * Check if a toast notification is visible
   * CREAMS uses toast notifications for all status messages
   * Toast classes: .toast-notification.toast-success, .toast-notification.toast-error
   */
  async hasAlertMessage(type: 'success' | 'danger' | 'warning' | 'info'): Promise<boolean> {
    // Map alert types to toast notification classes
    const toastType = type === 'danger' ? 'error' : type;
    const selector = `.toast-notification.toast-${toastType}`;

    try {
      // Wait for toast to appear (max 3 seconds)
      await this.page.waitForSelector(selector, { timeout: 3000 });
      return await this.page.locator(selector).isVisible();
    } catch {
      return false;
    }
  }

  /**
   * Get toast notification message text
   * CREAMS uses toast notifications for all status messages
   */
  async getAlertText(type: 'success' | 'danger' | 'warning' | 'info'): Promise<string> {
    const toastType = type === 'danger' ? 'error' : type;
    const toast = this.page.locator(`.toast-notification.toast-${toastType} .toast-message`);

    if (await toast.isVisible()) {
      return await toast.textContent() || '';
    }
    return '';
  }

  /**
   * Take a screenshot with a descriptive name
   */
  async takeScreenshot(name: string): Promise<void> {
    await this.page.screenshot({ path: `./test-results/screenshots/${name}.png` });
  }
}
