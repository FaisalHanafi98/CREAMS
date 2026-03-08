import { Page, expect } from '@playwright/test';
import { BasePage } from './BasePage';
import { SELECTORS, ROUTES, TEST_USERS, UserRole } from '../fixtures/test-data';

/**
 * Login Page Object for CREAMS
 *
 * Handles all login-related interactions:
 * - Form filling
 * - Login submission
 * - Validation assertions
 * - Password toggle
 */
export class LoginPage extends BasePage {
  private selectors = SELECTORS.login;

  /**
   * Navigate to login page
   */
  async goto(): Promise<void> {
    await this.navigate(ROUTES.login);
  }

  /**
   * Fill identifier field (email or IIUM ID)
   */
  async fillIdentifier(value: string): Promise<void> {
    await this.page.fill(this.selectors.identifier, value);
  }

  /**
   * Fill password field
   */
  async fillPassword(value: string): Promise<void> {
    await this.page.fill(this.selectors.password, value);
  }

  /**
   * Check/uncheck remember me checkbox
   */
  async setRememberMe(checked: boolean): Promise<void> {
    if (checked) {
      await this.page.check(this.selectors.rememberCheckbox);
    } else {
      await this.page.uncheck(this.selectors.rememberCheckbox);
    }
  }

  /**
   * Click the login button
   */
  async clickLogin(): Promise<void> {
    await this.page.click(this.selectors.submitButton);
    // Give page time to start redirect
    await this.page.waitForTimeout(500);
  }

  /**
   * Complete login flow with credentials
   */
  async login(email: string, password: string, remember = false): Promise<void> {
    await this.fillIdentifier(email);
    await this.fillPassword(password);

    if (remember) {
      await this.setRememberMe(true);
    }

    await this.clickLogin();
  }

  /**
   * Login as a specific role
   */
  async loginAs(role: UserRole): Promise<void> {
    const user = TEST_USERS[role];
    await this.login(user.email, user.password);
  }

  /**
   * Assert login was successful and redirected to correct dashboard
   */
  async expectLoginSuccess(role: UserRole): Promise<void> {
    const user = TEST_USERS[role];
    // Wait for navigation to dashboard — use domcontentloaded (not full load)
    // to avoid timeout caused by slow external resources (weather widget, etc.)
    await this.page.waitForURL(`**${user.dashboard}`, { timeout: 85000, waitUntil: 'domcontentloaded' });

    const url = await this.getCurrentUrl();
    expect(url).toContain(user.dashboard);
  }

  /**
   * Assert login failed with error message
   */
  async expectLoginFailure(): Promise<void> {
    const hasError = await this.hasAlertMessage('danger');
    expect(hasError).toBe(true);

    // Should still be on login page
    const url = await this.getCurrentUrl();
    expect(url).toContain('/login');
  }

  /**
   * Assert we're on the login page
   */
  async expectOnLoginPage(): Promise<void> {
    await expect(this.page).toHaveURL(/\/login/);
    await expect(this.page.locator(this.selectors.identifier)).toBeVisible();
    await expect(this.page.locator(this.selectors.password)).toBeVisible();
    await expect(this.page.locator(this.selectors.submitButton)).toBeVisible();
  }

  /**
   * Toggle password visibility
   */
  async togglePasswordVisibility(): Promise<void> {
    await this.page.click('#passwordToggle');
  }

  /**
   * Check if password is visible (text type) or hidden (password type)
   */
  async isPasswordVisible(): Promise<boolean> {
    const type = await this.page.getAttribute(this.selectors.password, 'type');
    return type === 'text';
  }

  /**
   * Click forgot password link
   */
  async clickForgotPassword(): Promise<void> {
    await this.page.click(this.selectors.forgotPasswordLink);
  }

  /**
   * Get identifier field value
   */
  async getIdentifierValue(): Promise<string> {
    return await this.page.inputValue(this.selectors.identifier);
  }

  /**
   * Check if identifier field has validation error
   */
  async hasIdentifierError(): Promise<boolean> {
    const field = this.page.locator(this.selectors.identifier);
    return await field.evaluate(el => el.classList.contains('is-invalid'));
  }

  /**
   * Check if password field has validation error
   */
  async hasPasswordError(): Promise<boolean> {
    const field = this.page.locator(this.selectors.password);
    return await field.evaluate(el => el.classList.contains('is-invalid'));
  }
}
