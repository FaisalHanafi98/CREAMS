import { test, expect } from '@playwright/test';

/**
 * Diagnostic Test - Run this first to verify setup
 */
test.describe('00 - Setup Diagnostics', () => {

  test('Can connect to Laravel server', async ({ page }) => {
    console.log('🔍 Attempting to connect to http://localhost:8000...');

    const response = await page.goto('http://localhost:8000');

    console.log('✅ Response status:', response?.status());
    console.log('✅ Response URL:', response?.url());

    expect(response?.status()).toBe(200);
  });

  test('Login page loads with correct selectors', async ({ page }) => {
    console.log('🔍 Navigating to login page...');

    await page.goto('http://localhost:8000/login');

    // Take screenshot for debugging
    await page.screenshot({ path: './test-results/diagnostic-login-page.png' });

    console.log('✅ Current URL:', page.url());
    console.log('✅ Page title:', await page.title());

    // Check if login form elements exist
    const identifierExists = await page.locator('#identifier').count() > 0;
    const passwordExists = await page.locator('#password').count() > 0;
    const submitExists = await page.locator('.login-btn').count() > 0;

    console.log('✅ Identifier field exists:', identifierExists);
    console.log('✅ Password field exists:', passwordExists);
    console.log('✅ Submit button exists:', submitExists);

    expect(identifierExists).toBe(true);
    expect(passwordExists).toBe(true);
    expect(submitExists).toBe(true);
  });

  test('Can fill and submit login form', async ({ page }) => {
    console.log('🔍 Testing login form submission...');

    await page.goto('http://localhost:8000/login');

    // Fill form — canonical UAT admin, same account global-setup.ts authenticates with
    await page.fill('#identifier', 'super.admin@uat.creams.test');
    await page.fill('#password', 'UatPass2026!');

    console.log('✅ Form filled');

    // Click submit and wait for redirect
    await page.click('.login-btn');
    await page.waitForURL('**/admin/dashboard', { timeout: 15000 });

    const finalUrl = page.url();
    console.log('✅ Final URL after login:', finalUrl);
    console.log('✅ Page title after login:', await page.title());

    // Take screenshot of result
    await page.screenshot({ path: './test-results/diagnostic-after-login.png' });

    // Check if we're on a dashboard
    const isDashboard = finalUrl.includes('/dashboard');
    console.log('✅ Redirected to dashboard:', isDashboard);

    expect(isDashboard).toBe(true);
  });
});
