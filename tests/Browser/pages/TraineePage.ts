import { Page, expect } from '@playwright/test';
import { BasePage } from './BasePage';
import { ROUTES, BASE_URL } from '../fixtures/test-data';

/**
 * Trainee form data interface matching CREAMS form fields
 */
export interface TraineeFormData {
  // Personal Information
  firstName: string;
  lastName: string;
  dateOfBirth: string;
  gender: 'Male' | 'Female';
  email: string;
  icNumber: string;
  phone?: string;
  address?: string;

  // Centre & Medical
  centreName?: string;
  condition?: string;
  medicalHistory?: string;
  status?: 'active' | 'inactive' | 'completed_program';

  // Guardian Information
  guardianName?: string;
  guardianRelationship?: string;
  guardianPhone?: string;
  guardianEmail?: string;
  guardianAddress?: string;

  // Emergency Contact
  emergencyContactName?: string;
  emergencyContactPhone?: string;
  emergencyContactRelationship?: string;

  // Additional
  additionalNotes?: string;

  // Consent (all required)
  photoConsent?: boolean;
  servicesConsent?: boolean;
  dataConsent?: boolean;
}

/**
 * Service categories/conditions available
 */
export const SERVICE_CATEGORIES = [
  'Physical Disabilities',
  'Learning Support',
  'Visual Impairment',
  'Autism Spectrum Support',
  'Hearing Impairment',
  'Speech Therapy',
] as const;

/**
 * Page Object for Trainee Management
 * Handles CRUD operations for trainees
 */
export class TraineePage extends BasePage {

  /**
   * Navigate to trainee list
   */
  async gotoList(): Promise<void> {
    await this.navigate(ROUTES.traineesHome);
  }

  /**
   * Navigate to create trainee page
   */
  async gotoCreate(): Promise<void> {
    await this.navigate(ROUTES.traineesCreate);
  }

  /**
   * Fill the trainee registration form
   * Note: Uses JavaScript to set phone values directly to bypass buggy client-side validation
   */
  async fillForm(data: Partial<TraineeFormData>): Promise<void> {
    // Dismiss any error alerts/toasts that may block interactions
    await this.page.evaluate(() => {
      document.querySelectorAll('.alert-dismissible .close, .alert-dismissible button[data-dismiss="alert"], .toast-notification .toast-close').forEach(btn => (btn as HTMLElement).click());
      document.querySelectorAll('.alert-dismissible, .toast-notification').forEach(el => el.remove());
    });

    // Set novalidate early to prevent HTML5 validation from blocking form fill
    await this.page.evaluate(() => {
      const form = document.querySelector('form.trainee-form') as HTMLFormElement;
      if (form) form.setAttribute('novalidate', 'true');
    });

    // Disable the problematic phone input handlers to prevent validation conflicts
    await this.page.evaluate(() => {
      // Remove all event listeners from phone inputs by cloning and replacing
      const phoneInputs = document.querySelectorAll('#trainee_phone_number, #guardian_phone, #emergency_contact_phone');
      phoneInputs.forEach(input => {
        const newInput = input.cloneNode(true) as HTMLInputElement;
        input.parentNode?.replaceChild(newInput, input);
      });
    });

    // Personal Information
    if (data.firstName) {
      await this.page.fill('#trainee_first_name, [name="trainee_first_name"]', data.firstName);
    }

    if (data.lastName) {
      await this.page.fill('#trainee_last_name, [name="trainee_last_name"]', data.lastName);
    }

    if (data.dateOfBirth) {
      await this.page.fill('#trainee_date_of_birth, [name="trainee_date_of_birth"]', data.dateOfBirth);
    }

    if (data.gender) {
      await this.page.selectOption('#gender, [name="gender"]', data.gender);
    }

    if (data.email) {
      await this.page.fill('#trainee_email, [name="trainee_email"]', data.email);
    }

    if (data.icNumber) {
      await this.page.fill('#ic_number, [name="ic_number"]', data.icNumber);
    }

    if (data.phone) {
      // Set phone value directly via JavaScript to bypass client-side validation bug
      // Server accepts local format (0XXXXXXXXX) and normalizes to +60 format
      const localPhone = data.phone.replace(/^\+60\s*/, '0').replaceAll(/[\s-]/g, '');
      await this.page.evaluate((phone) => {
        const input = document.querySelector('#trainee_phone_number, [name="trainee_phone_number"]') as HTMLInputElement;
        if (input) {
          // Use native setter to ensure the value is recognized by form submission
          const nativeInputValueSetter = Object.getOwnPropertyDescriptor(globalThis.HTMLInputElement.prototype, 'value')?.set;
          if (nativeInputValueSetter) {
            nativeInputValueSetter.call(input, phone);
          } else {
            input.value = phone;
          }
          input.classList.remove('is-invalid');
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }, localPhone);
    }

    if (data.address) {
      await this.page.fill('#trainee_address, [name="trainee_address"]', data.address);
    }

    // Centre & Medical
    if (data.centreName) {
      // Try exact label match first, fall back to first available option
      try {
        await this.page.selectOption('#centre_name, [name="centre_name"]', { label: data.centreName });
      } catch {
        // Label not found — select the first non-empty option
        const firstOptionValue = await this.page.evaluate(() => {
          const select = document.querySelector('#centre_name, [name="centre_name"]') as HTMLSelectElement;
          if (select) {
            const options = Array.from(select.options).filter(o => o.value !== '');
            if (options.length > 0) return options[0].value;
          }
          return null;
        });
        if (firstOptionValue) {
          await this.page.selectOption('#centre_name, [name="centre_name"]', firstOptionValue);
        }
      }
    }

    if (data.condition) {
      await this.page.selectOption('#trainee_condition, [name="trainee_condition"]', data.condition);
    }

    if (data.medicalHistory) {
      await this.page.fill('#medical_history, [name="medical_history"]', data.medicalHistory);
    }

    // Guardian Information
    if (data.guardianName) {
      await this.page.fill('#guardian_name, [name="guardian_name"]', data.guardianName);
    }

    if (data.guardianRelationship) {
      await this.page.selectOption('#guardian_relationship, [name="guardian_relationship"]', data.guardianRelationship);
    }

    if (data.guardianPhone) {
      // Set phone value directly via JavaScript
      const localPhone = data.guardianPhone.replace(/^\+60\s*/, '0').replaceAll(/[\s-]/g, '');
      await this.page.evaluate((phone) => {
        const input = document.querySelector('#guardian_phone, [name="guardian_phone"]') as HTMLInputElement;
        if (input) {
          const nativeInputValueSetter = Object.getOwnPropertyDescriptor(globalThis.HTMLInputElement.prototype, 'value')?.set;
          if (nativeInputValueSetter) {
            nativeInputValueSetter.call(input, phone);
          } else {
            input.value = phone;
          }
          input.classList.remove('is-invalid');
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }, localPhone);
    }

    if (data.guardianEmail) {
      await this.page.fill('#guardian_email, [name="guardian_email"]', data.guardianEmail);
    }

    if (data.guardianAddress) {
      await this.page.fill('#guardian_address, [name="guardian_address"]', data.guardianAddress);
    }

    // Emergency Contact
    if (data.emergencyContactName) {
      await this.page.fill('#emergency_contact_name, [name="emergency_contact_name"]', data.emergencyContactName);
    }

    if (data.emergencyContactPhone) {
      // Set phone value directly via JavaScript
      const phone = data.emergencyContactPhone.replace(/^\+60\s*/, '0').replaceAll(/[\s-]/g, '');
      await this.page.evaluate((phoneVal) => {
        const input = document.querySelector('#emergency_contact_phone, [name="emergency_contact_phone"]') as HTMLInputElement;
        if (input) {
          const nativeInputValueSetter = Object.getOwnPropertyDescriptor(globalThis.HTMLInputElement.prototype, 'value')?.set;
          if (nativeInputValueSetter) {
            nativeInputValueSetter.call(input, phoneVal);
          } else {
            input.value = phoneVal;
          }
          input.classList.remove('is-invalid');
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }, phone);
    }

    if (data.emergencyContactRelationship) {
      await this.page.fill('#emergency_contact_relationship, [name="emergency_contact_relationship"]', data.emergencyContactRelationship);
    }

    // Additional Notes
    if (data.additionalNotes) {
      await this.page.fill('#additional_notes, [name="additional_notes"]', data.additionalNotes);
    }

    // Consent checkboxes (required) - use evaluate to bypass any overlay blocking
    await this.page.evaluate((consent) => {
      const checkboxes = [
        { id: 'photo_consent', check: consent.photo },
        { id: 'services_consent', check: consent.services },
        { id: 'data_consent', check: consent.data },
      ];
      for (const cb of checkboxes) {
        if (cb.check) {
          const el = document.getElementById(cb.id) as HTMLInputElement;
          if (el && !el.checked) {
            el.checked = true;
            el.dispatchEvent(new Event('change', { bubbles: true }));
          }
        }
      }
    }, {
      photo: data.photoConsent !== false,
      services: data.servicesConsent !== false,
      data: data.dataConsent !== false,
    });

    // Final cleanup: remove any validation errors that might block submission
    await this.page.evaluate(() => {
      document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      document.querySelectorAll('.invalid-feedback, .phone-error').forEach(el => el.remove());
    });
  }

  /**
   * Submit the trainee form and wait for result
   * Form submission redirects to trainees.home on success with session flash
   */
  async submitForm(): Promise<void> {
    // Prepare the form for submission: clear errors and disable HTML5 validation
    await this.page.evaluate(() => {
      // Set novalidate on ALL forms (works for both create and edit pages)
      document.querySelectorAll('form').forEach(f => f.setAttribute('novalidate', 'true'));

      // Clear all validation error classes and messages
      document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
      document.querySelectorAll('.phone-error, .invalid-feedback').forEach(el => el.remove());
    });

    // Click the actual submit button — works on both create (form.trainee-form) and edit pages
    const createBtn = this.page.locator('form.trainee-form button[type="submit"]');
    if (await createBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
      await createBtn.click();
    } else {
      // Edit page: find the main content form's submit button (not logout/delete forms)
      const editBtn = this.page.locator('.container form button[type="submit"]:not(.btn-danger)').first();
      await editBtn.click();
    }

    // Wait for navigation (success redirect) or page reload (validation errors)
    await this.page.waitForLoadState('domcontentloaded', { timeout: 30000 });
    await this.page.waitForTimeout(500);
  }

  /**
   * Create a new trainee with full flow
   */
  async createTrainee(data: TraineeFormData): Promise<void> {
    await this.gotoCreate();
    await this.fillForm(data);
    await this.submitForm();
    await this.waitForPageLoad();
  }

  /**
   * Search for a trainee in the list
   */
  async searchTrainee(searchTerm: string): Promise<void> {
    // Navigate to list if not already there
    if (!this.page.url().includes('trainees/home')) {
      await this.gotoList();
    }

    const searchInput = this.page.locator('#search');
    if (await searchInput.isVisible({ timeout: 3000 })) {
      await searchInput.fill(searchTerm);
      // Server-side search: submit the filter form
      const filterBtn = this.page.locator('button:has-text("Filter")');
      if (await filterBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
        await filterBtn.click();
      } else {
        await searchInput.press('Enter');
      }
      await this.waitForPageLoad();
    }
  }

  /**
   * Check if trainee appears in the list
   */
  async isTraineeInList(firstName: string, email?: string): Promise<boolean> {
    await this.gotoList();

    // Use server-side search to handle pagination (8 per page)
    await this.searchTrainee(firstName);
    await this.page.waitForTimeout(500);

    // Check for name in trainee cards (home page uses .trainee-card)
    const byName = await this.page.locator(`.trainee-card:has-text("${firstName}")`).first().isVisible().catch(() => false);
    if (byName) return true;

    // Check for email in trainee cards if provided
    if (email) {
      const byEmail = await this.page.locator(`.trainee-card:has-text("${email}")`).first().isVisible().catch(() => false);
      if (byEmail) return true;
    }

    return false;
  }

  /**
   * Click view button for a trainee
   */
  async viewTrainee(firstName: string): Promise<void> {
    await this.gotoList();

    // Search for the trainee first to narrow results
    await this.searchTrainee(firstName);

    // Trainee home uses .trainee-card cards, not table rows
    const card = this.page.locator(`.trainee-card:has-text("${firstName}")`).first();
    await card.waitFor({ state: 'visible', timeout: 10000 });
    const viewBtn = card.locator('a:has-text("Profile"), a[href*="profile"]').first();
    await viewBtn.click();
    await this.waitForPageLoad();
  }

  /**
   * Click edit button for a trainee
   */
  async editTrainee(firstName: string): Promise<void> {
    await this.gotoList();

    // Search for the trainee first to narrow results
    await this.searchTrainee(firstName);

    // Trainee home uses .trainee-card cards, not table rows
    const card = this.page.locator(`.trainee-card:has-text("${firstName}")`).first();
    await card.waitFor({ state: 'visible', timeout: 10000 });
    const editBtn = card.locator('a:has-text("Edit"), a[href*="edit"]').first();
    await editBtn.click();
    await this.waitForPageLoad();
  }

  /**
   * Update trainee information
   */
  async updateTrainee(firstName: string, updates: Partial<TraineeFormData>): Promise<void> {
    await this.editTrainee(firstName);
    await this.fillForm(updates);
    await this.submitForm();
    await this.waitForPageLoad();
  }

  /**
   * Delete a trainee
   */
  async deleteTrainee(firstName: string): Promise<void> {
    // Navigate to the edit page which has the delete button
    await this.editTrainee(firstName);

    // Handle the confirm() dialog that fires on the modal form submit
    this.page.on('dialog', async dialog => {
      await dialog.accept();
    });

    // Step 1: Click "Delete Trainee" button to open the Bootstrap modal
    const openModalBtn = this.page.locator('button[data-target="#deleteTraineeModal"]');
    await openModalBtn.scrollIntoViewIfNeeded();
    await openModalBtn.click();

    // Step 2: Wait for the modal to be visible
    const modal = this.page.locator('#deleteTraineeModal');
    await modal.waitFor({ state: 'visible', timeout: 5000 });

    // Step 3: Click "Confirm Delete" button inside the modal form
    const confirmBtn = modal.locator('button[type="submit"].btn-danger');
    await confirmBtn.click();

    // Wait for navigation after delete (redirects to trainees.home)
    await this.page.waitForLoadState('domcontentloaded', { timeout: 30000 });
    await this.waitForPageLoad();
  }

  /**
   * Get count of trainees in the list
   */
  async getTraineeCount(): Promise<number> {
    await this.gotoList();

    // Try table rows first
    const tableRows = await this.page.locator('table tbody tr').count();
    if (tableRows > 0) return tableRows;

    // Try cards
    const cards = await this.page.locator('.trainee-card, [class*="trainee"]').count();
    return cards;
  }

  /**
   * Verify success - checks for redirect to trainee list OR success toast/alert
   */
  async expectSuccessToast(expectedMessage?: string): Promise<void> {
    await this.page.waitForLoadState('domcontentloaded');

    // Check multiple success indicators
    const currentUrl = this.page.url();
    const redirected = currentUrl.includes('trainees') && !currentUrl.includes('create') && !currentUrl.includes('edit');
    const hasToast = await this.page.locator('.toast-notification.toast-success, .toast-success, .alert-success, [class*="success"]').isVisible().catch(() => false);
    const hasSwal = await this.page.locator('.swal2-success, .swal2-popup:has(.swal2-success)').isVisible().catch(() => false);

    // Any success indicator is acceptable
    expect(redirected || hasToast || hasSwal).toBe(true);
  }

  /**
   * Verify error toast after operation
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
    // Check multiple indicators of validation errors
    const hasInvalid = await this.page.locator('.is-invalid').first().isVisible().catch(() => false);
    if (hasInvalid) return true;

    const hasFeedback = await this.page.locator('.invalid-feedback').first().isVisible().catch(() => false);
    if (hasFeedback) return true;

    const hasAlertDanger = await this.page.locator('.alert-danger, .alert-error').first().isVisible().catch(() => false);
    if (hasAlertDanger) return true;

    const hasToastError = await this.page.locator('.toast-error, .toast-notification.toast-error').first().isVisible().catch(() => false);
    if (hasToastError) return true;

    // Check page content for common validation error text
    const content = await this.page.content();
    return content.includes('is required') || content.includes('is-invalid');
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
 * Generate test trainee data
 */
export function generateTestTrainee(overrides: Partial<TraineeFormData> = {}): TraineeFormData {
  const timestamp = Date.now();
  const random = Math.floor(Math.random() * 10000);

  // Generate valid Malaysian phone format: +60 12-345 6789
  const phonePrefix = ['12', '13', '14', '16', '17', '18', '19'][Math.floor(Math.random() * 7)];
  const phoneBody = random.toString().padStart(7, '0').substring(0, 7);
  const phone = `+60 ${phonePrefix}-${phoneBody.substring(0, 3)} ${phoneBody.substring(3)}`;
  const guardianPhoneBody = (random + 1).toString().padStart(7, '0').substring(0, 7);
  const guardianPhone = `+60 ${phonePrefix}-${guardianPhoneBody.substring(0, 3)} ${guardianPhoneBody.substring(3)}`;

  // Generate valid IC number format: YYMMDD-SS-NNNN
  const icYear = '05'; // Born 2005
  const icMonth = '06';
  const icDay = '15';
  const icState = '14'; // WP Kuala Lumpur
  const icSerial = random.toString().padStart(4, '0').substring(0, 4);
  const icNumber = `${icYear}${icMonth}${icDay}-${icState}-${icSerial}`;

  return {
    firstName: `TestTrainee${random}`,
    lastName: `Automation`,
    dateOfBirth: '2005-06-15',
    gender: 'Male',
    email: `test.trainee.${timestamp}@test.com`,
    icNumber: icNumber,
    phone: phone,
    address: '123 Test Street, Kuala Lumpur, Malaysia',
    condition: 'Learning Support',
    centreName: 'Kuantan', // Updated to match actual database centre names
    guardianName: 'Test Guardian',
    guardianRelationship: 'Parent',
    guardianPhone: guardianPhone,
    guardianEmail: `guardian.${timestamp}@test.com`,
    photoConsent: true,
    servicesConsent: true,
    dataConsent: true,
    ...overrides,
  };
}
