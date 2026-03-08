import { test, expect } from '@playwright/test';
import { LoginPage } from '../../pages/LoginPage';
import { TEST_USERS } from '../../fixtures/test-data';

/**
 * CREAMS Authentication Tests - Login
 *
 * Priority 1: Core authentication functionality
 * Tests login for all roles and various edge cases.
 */
test.describe('Authentication - Login', () => {

  test.describe('Valid Credentials', () => {

    // Teacher runs first — server is fresh, reducing risk of timeout
    test('Teacher can login with valid credentials', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.loginAs('teacher');
      await loginPage.expectLoginSuccess('teacher');

      // Verify teacher dashboard content (use first() to handle multiple matches)
      await expect(page.locator(`text=${TEST_USERS.teacher.name}`).first()).toBeVisible();
    });

    test('Admin can login with valid credentials', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.loginAs('admin');
      await loginPage.expectLoginSuccess('admin');

      // Verify admin dashboard content (use first() to handle multiple matches)
      await expect(page.locator(`text=${TEST_USERS.admin.name}`).first()).toBeVisible();
    });

    test('Supervisor can login with valid credentials', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.loginAs('supervisor');
      await loginPage.expectLoginSuccess('supervisor');

      // Verify supervisor dashboard content (use first() to handle multiple matches)
      await expect(page.locator(`text=${TEST_USERS.supervisor.name}`).first()).toBeVisible();
    });

    test('AJK can login with valid credentials', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.loginAs('ajk');
      await loginPage.expectLoginSuccess('ajk');

      // Verify AJK dashboard content (use first() to handle multiple matches)
      await expect(page.locator(`text=${TEST_USERS.ajk.name}`).first()).toBeVisible();
    });
  });

  test.describe('Invalid Credentials', () => {

    test('Login fails with invalid email', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login('nonexistent@email.com', 'SomePassword123!');
      await loginPage.expectLoginFailure();
    });

    test('Login fails with wrong password', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login(TEST_USERS.admin.email, 'WrongPassword123!');
      await loginPage.expectLoginFailure();
    });

    test('Login fails with both invalid credentials', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();
      await loginPage.login('fake@email.com', 'FakePassword!');
      await loginPage.expectLoginFailure();
    });
  });

  test.describe('Form Validation', () => {

    test('Cannot submit with empty identifier', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      // Fill only password
      await loginPage.fillPassword('SomePassword');
      await loginPage.clickLogin();

      // Should still be on login page (HTML5 validation)
      await loginPage.expectOnLoginPage();
    });

    test('Cannot submit with empty password', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      // Fill only identifier
      await loginPage.fillIdentifier(TEST_USERS.admin.email);
      await loginPage.clickLogin();

      // Should still be on login page (HTML5 validation)
      await loginPage.expectOnLoginPage();
    });

    test('Cannot submit with both fields empty', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      await loginPage.clickLogin();

      // Should still be on login page (HTML5 validation)
      await loginPage.expectOnLoginPage();
    });
  });

  test.describe('UI Functionality', () => {

    test('Password toggle shows/hides password', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      await loginPage.fillPassword('TestPassword123');

      // Initially password should be hidden
      expect(await loginPage.isPasswordVisible()).toBe(false);

      // Toggle to show
      await loginPage.togglePasswordVisibility();
      expect(await loginPage.isPasswordVisible()).toBe(true);

      // Toggle to hide again
      await loginPage.togglePasswordVisibility();
      expect(await loginPage.isPasswordVisible()).toBe(false);
    });

    test('Forgot password link navigates correctly', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      await loginPage.clickForgotPassword();
      await page.waitForLoadState('networkidle');

      await expect(page).toHaveURL(/forgot-password/);
    });

    test('Remember me checkbox can be toggled', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      // Check the checkbox
      await loginPage.setRememberMe(true);
      await expect(page.locator('#remember')).toBeChecked();

      // Uncheck the checkbox
      await loginPage.setRememberMe(false);
      await expect(page.locator('#remember')).not.toBeChecked();
    });
  });

  test.describe('Login Page Access', () => {

    test('Login page loads correctly', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      await loginPage.expectOnLoginPage();

      // Check page title
      await expect(page).toHaveTitle(/Login.*CREAMS/);
    });

    test('Login page has CREAMS branding', async ({ page }) => {
      const loginPage = new LoginPage(page);
      await loginPage.goto();

      await expect(page.locator('text=CREAMS')).toBeVisible();
      await expect(page.locator('text=Welcome Back')).toBeVisible();
    });
  });
});
