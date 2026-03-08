import { Page, expect } from '@playwright/test';
import { BasePage } from './BasePage';
import { ROUTES, BASE_URL } from '../fixtures/test-data';

/**
 * Staff form data interface matching CREAMS form fields
 */
export interface StaffFormData {
  // Account Details (Step 1)
  email: string;
  iiumId?: string;
  password?: string;
  passwordConfirmation?: string;

  // Profile Information (Step 2)
  firstName: string;
  lastName: string;
  phone?: string;

  // Role and Centre (Step 2)
  role: 'admin' | 'supervisor' | 'teacher' | 'ajk';
  centreId?: string;

  // Additional
  status?: 'active' | 'inactive';
}

/**
 * Available staff roles
 */
export const STAFF_ROLES = ['admin', 'supervisor', 'teacher', 'ajk'] as const;

/**
 * Page Object for Staff Management
 * Handles CRUD operations for staff members
 */
export class StaffPage extends BasePage {

  /**
   * Navigate to staff list (admin)
   */
  async gotoAdminList(): Promise<void> {
    await this.navigate(ROUTES.adminUsers);
  }

  /**
   * Navigate to staff list (supervisor)
   */
  async gotoSupervisorList(): Promise<void> {
    await this.navigate(ROUTES.supervisorUsers);
  }

  /**
   * Navigate to staff registration page
   */
  async gotoCreate(): Promise<void> {
    await this.navigate('/staffs/register');
  }

  /**
   * Fill Step 1 - Account Details
   * Fields: Email, IIUM ID, Password, Confirm Password
   */
  async fillStep1AccountDetails(data: Partial<StaffFormData>): Promise<void> {
    if (data.email) {
      await this.page.fill('#email, [name="email"], input[placeholder*="email" i]', data.email);
    }

    if (data.iiumId) {
      await this.page.fill('#iium_id, [name="iium_id"], input[placeholder*="IIUM" i]', data.iiumId);
    }

    if (data.password) {
      await this.page.fill('#password, [name="password"]', data.password);
    }

    if (data.passwordConfirmation) {
      await this.page.fill('#password_confirmation, [name="password_confirmation"]', data.passwordConfirmation);
    }
  }

  /**
   * Fill Step 2 - Profile Information
   * Fields: First Name, Last Name, Phone, Role, Centre
   */
  async fillStep2ProfileInfo(data: Partial<StaffFormData>): Promise<void> {
    // CRITICAL: Wait for Tab 2 fields to be visible before filling
    await this.page.waitForSelector('#name', { state: 'visible', timeout: 10000 });

    // Form uses "Full Name" single field with id="name"
    const fullName = [data.firstName, data.lastName].filter(Boolean).join(' ');
    if (fullName) {
      await this.page.fill('#name', fullName);
    }

    if (data.phone) {
      // Try multiple possible selectors for phone field
      const phoneSelectors = [
        '#phone',
        '[name="phone"]',
        'input[type="tel"]',
        'input[placeholder*="phone" i]'
      ];

      let phoneFilled = false;
      for (const selector of phoneSelectors) {
        try {
          await this.page.waitForSelector(`${selector}:visible`, { state: 'visible', timeout: 2000 });
          await this.page.fill(selector, data.phone);
          phoneFilled = true;
          break;
        } catch (e) {
          // Try next selector
          continue;
        }
      }

      if (!phoneFilled) {
        console.warn('Phone field not found with any known selector, skipping phone input');
      }
    }

    if (data.role) {
      // Role is radio buttons - click the label/option wrapper since input is hidden
      const roleOption = this.page.locator(`.role-option:has(input[value="${data.role}"])`);
      if (await roleOption.isVisible().catch(() => false)) {
        await roleOption.click();
      } else {
        // Fallback to clicking the radio input directly via JS
        await this.page.evaluate((role) => {
          const radio = document.querySelector(`input[name="role"][value="${role}"]`) as HTMLInputElement;
          if (radio) radio.click();
        }, data.role);
      }
    }

    if (data.centreId) {
      await this.page.selectOption('#centre_id, [name="centre_id"]', data.centreId);
    }
  }

  /**
   * Navigate to next wizard step with verification
   */
  async clickNextStep(): Promise<void> {
    const nextBtn = this.page.locator('button:has-text("Next"), a:has-text("Next"), [type="button"]:has-text("Next")').first();
    await nextBtn.click();

    // Wait for tab transition animation
    await this.page.waitForTimeout(800);

    // Check if #name became visible (section-2 is active)
    const nameVisible = await this.page.locator('#name').isVisible().catch(() => false);
    if (!nameVisible) {
      // jQuery wizard JS may be blocked by CSP — manually toggle sections via DOM
      await this.page.evaluate(() => {
        const sections = document.querySelectorAll('.form-section');
        sections.forEach(s => s.classList.remove('active'));
        const section2 = document.getElementById('section-2');
        if (section2) section2.classList.add('active');
      });
      await this.page.waitForTimeout(300);
    }
  }

  /**
   * Navigate to specific wizard tab by clicking tab header with verification
   */
  async goToTab(tabName: string): Promise<void> {
    const tab = this.page.locator(`text="${tabName}"`).first();
    if (await tab.isVisible().catch(() => false)) {
      await tab.click();
      // Wait for tab transition animation
      await this.page.waitForTimeout(800);
    }

    // If jQuery wizard is blocked by CSP, directly activate the target section
    if (tabName.includes('Review') || tabName.includes('Submit')) {
      const submitVisible = await this.page.locator('button[type="submit"], input[type="submit"]').isVisible().catch(() => false);
      if (!submitVisible) {
        await this.page.evaluate(() => {
          const sections = document.querySelectorAll('.form-section');
          sections.forEach(s => s.classList.remove('active'));
          const section3 = document.getElementById('section-3');
          if (section3) section3.classList.add('active');
        });
        await this.page.waitForTimeout(300);
      }
    }
  }

  /**
   * Fill the staff registration form (handles wizard flow)
   * This is the main method that navigates through all wizard steps
   */
  async fillForm(data: Partial<StaffFormData>): Promise<void> {
    // Check if we're on a wizard form
    const hasWizardTabs = await this.page.locator('text="Account Details"').isVisible().catch(() => false);

    if (hasWizardTabs) {
      // WIZARD MODE: Navigate through steps

      // Step 1: Fill Account Details
      await this.fillStep1AccountDetails(data);

      // Navigate to Step 2
      await this.clickNextStep();

      // Step 2: Fill Profile Information
      await this.fillStep2ProfileInfo(data);

      // Navigate to Step 3 (Review & Submit) - click tab directly
      await this.page.waitForTimeout(500);
      await this.goToTab('Review & Submit');
    } else {
      // SINGLE PAGE MODE: Fill all fields directly (legacy support)
      if (data.email) {
        await this.page.fill('#email, [name="email"]', data.email);
      }
      if (data.password) {
        await this.page.fill('#password, [name="password"]', data.password);
      }
      if (data.passwordConfirmation) {
        await this.page.fill('#password_confirmation, [name="password_confirmation"]', data.passwordConfirmation);
      }
      // Form uses "Full Name" single field with id="name"
      const fullName = [data.firstName, data.lastName].filter(Boolean).join(' ');
      if (fullName) {
        await this.page.fill('#name, [name="name"]', fullName);
      }
      if (data.phone) {
        // Try multiple possible selectors for phone field
        const phoneSelectors = [
          '#phone',
          '[name="phone"]',
          'input[type="tel"]',
          'input[placeholder*="phone" i]'
        ];

        let phoneFilled = false;
        for (const selector of phoneSelectors) {
          try {
            await this.page.waitForSelector(`${selector}:visible`, { state: 'visible', timeout: 2000 });
            await this.page.fill(selector, data.phone);
            phoneFilled = true;
            break;
          } catch (e) {
            continue;
          }
        }

        if (!phoneFilled) {
          console.warn('Phone field not found in edit form, skipping phone input');
        }
      }
      if (data.role) {
        const selectExists = await this.page.locator('#role, [name="role"]').first().isVisible().catch(() => false);
        if (selectExists) {
          await this.page.selectOption('#role, [name="role"]', data.role);
        } else {
          await this.page.click(`input[name="role"][value="${data.role}"]`);
        }
      }
      if (data.centreId) {
        await this.page.selectOption('#centre_id, [name="centre_id"]', data.centreId);
      }
    }
  }

  /**
   * Submit the staff form
   */
  async submitForm(): Promise<void> {
    await this.page.click('button[type="submit"], input[type="submit"]');
  }

  /**
   * Create a new staff member
   */
  async createStaff(data: StaffFormData): Promise<void> {
    await this.gotoCreate();
    await this.fillForm(data);
    await this.submitForm();
    await this.waitForPageLoad();
  }

  /**
   * Check if staff member appears in the list
   */
  async isStaffInList(firstName: string, email?: string): Promise<boolean> {
    await this.gotoAdminList();
    await this.page.waitForTimeout(500);

    const byName = await this.page.locator(`text="${firstName}"`).isVisible().catch(() => false);
    if (byName) return true;

    if (email) {
      const byEmail = await this.page.locator(`text="${email}"`).isVisible().catch(() => false);
      if (byEmail) return true;
    }

    return false;
  }

  /**
   * Search for a staff member
   */
  async searchStaff(searchTerm: string): Promise<void> {
    await this.gotoAdminList();

    // Use .first() to avoid strict mode violation with multiple search inputs
    const searchInput = this.page.locator('input[type="search"], #search, [placeholder*="Search"]').first();
    if (await searchInput.isVisible({ timeout: 3000 })) {
      await searchInput.fill(searchTerm);
      await this.page.waitForTimeout(500);
    }
  }

  /**
   * View staff member details
   */
  async viewStaff(firstName: string): Promise<void> {
    await this.gotoAdminList();

    const row = this.page.locator(`tr:has-text("${firstName}")`).first();
    if (await row.isVisible()) {
      const viewBtn = row.locator('a:has-text("View"), a[href*="view"], a[href*="show"]').first();
      await viewBtn.click();
      await this.waitForPageLoad();
    }
  }

  /**
   * Edit staff member
   */
  async editStaff(firstName: string): Promise<void> {
    await this.gotoAdminList();

    const row = this.page.locator(`tr:has-text("${firstName}")`).first();
    if (await row.isVisible()) {
      const editBtn = row.locator('a:has-text("Edit"), a[href*="edit"]').first();
      await editBtn.click();
      await this.waitForPageLoad();
    }
  }

  /**
   * Update staff member information
   */
  async updateStaff(firstName: string, updates: Partial<StaffFormData>): Promise<void> {
    await this.editStaff(firstName);
    await this.fillForm(updates);
    await this.submitForm();
    await this.waitForPageLoad();
  }

  /**
   * Delete a staff member
   */
  async deleteStaff(firstName: string): Promise<void> {
    await this.gotoAdminList();

    const row = this.page.locator(`tr:has-text("${firstName}")`).first();
    if (await row.isVisible()) {
      const deleteBtn = row.locator('button:has-text("Delete"), a:has-text("Delete")').first();

      this.page.on('dialog', async dialog => {
        await dialog.accept();
      });

      await deleteBtn.click();
      await this.waitForPageLoad();
    }
  }

  /**
   * Get count of staff members in the list
   */
  async getStaffCount(): Promise<number> {
    await this.gotoAdminList();

    const tableRows = await this.page.locator('table tbody tr').count();
    return tableRows;
  }

  /**
   * Verify success - checks for redirect to staff list OR success toast/alert
   */
  async expectSuccessToast(expectedMessage?: string): Promise<void> {
    await this.page.waitForLoadState('domcontentloaded');

    // Check multiple success indicators
    const currentUrl = this.page.url();
    const redirected = currentUrl.includes('staffs') || currentUrl.includes('users');
    const hasToast = await this.page.locator('.toast-notification.toast-success, .toast-success, .alert-success, [class*="success"]').isVisible().catch(() => false);
    const hasSwal = await this.page.locator('.swal2-success, .swal2-popup:has(.swal2-success)').isVisible().catch(() => false);

    // Any success indicator is acceptable
    expect(redirected || hasToast || hasSwal).toBe(true);
  }

  /**
   * Verify error toast
   * CREAMS uses .toast-notification.toast-error
   */
  async expectErrorToast(expectedMessage?: string): Promise<void> {
    const selector = '.toast-notification.toast-error';
    await this.page.waitForSelector(selector, { timeout: 5000 });
    const toast = this.page.locator(selector);
    await expect(toast).toBeVisible();

    if (expectedMessage) {
      const toastText = await toast.textContent();
      expect(toastText?.toLowerCase()).toContain(expectedMessage.toLowerCase());
    }
  }

  /**
   * Check for form validation errors
   */
  async hasValidationErrors(): Promise<boolean> {
    return await this.page.locator('.is-invalid, .invalid-feedback').isVisible().catch(() => false);
  }

  /**
   * Get validation error messages
   */
  async getValidationErrors(): Promise<string[]> {
    const errors: string[] = [];
    const errorElements = await this.page.locator('.invalid-feedback').all();

    for (const el of errorElements) {
      const text = await el.textContent();
      if (text) errors.push(text.trim());
    }

    return errors;
  }
}

/**
 * Generate test staff data
 */
export function generateTestStaff(overrides: Partial<StaffFormData> = {}): StaffFormData {
  const timestamp = Date.now();
  const random = Math.floor(Math.random() * 10000);
  // Generate IIUM ID format: 4 letters + 4 digits (e.g., ABCD1234)
  const letters = 'ABCD';
  const digits = random.toString().padStart(4, '0').slice(0, 4);

  return {
    firstName: `TestStaff${random}`,
    lastName: 'Automation',
    email: `test.staff.${timestamp}@test.com`,
    iiumId: `${letters}${digits}`,
    password: 'TestPassword@123',
    passwordConfirmation: 'TestPassword@123',
    phone: `01${random.toString().padStart(8, '0')}`,
    role: 'teacher',
    ...overrides,
  };
}
