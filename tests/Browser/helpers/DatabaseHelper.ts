import { Page, expect } from '@playwright/test';
import { BASE_URL } from '../fixtures/test-data';

/**
 * Database verification result interface
 */
export interface VerificationResult {
  found: boolean;
  data?: Record<string, any>;
  message: string;
}

/**
 * Trainee data interface
 */
export interface TraineeData {
  firstName: string;
  lastName: string;
  email: string;
  icNumber: string;
  dateOfBirth: string;
  gender: string;
  phone?: string;
  centre?: string;
  condition?: string;
  guardianName?: string;
  guardianEmail?: string;
  guardianPhone?: string;
}

/**
 * Activity data interface
 */
export interface ActivityData {
  name: string;
  description?: string;
  category?: string;
  centreId?: number;
  difficultyLevel?: string;
  ageGroup?: string;
  sessionDuration?: number;
  maxParticipants?: number;
  location?: string;
}

/**
 * Database Helper for verifying data persistence through UI
 *
 * Since we can't directly query the database in browser tests,
 * we verify data persistence by:
 * 1. Checking list pages for created records
 * 2. Viewing detail pages for specific records
 * 3. Searching for records by identifiable fields
 */
export class DatabaseHelper {
  constructor(private page: Page) {}

  /**
   * Verify trainee exists in the system by searching/viewing
   */
  async verifyTraineeExists(trainee: Partial<TraineeData>): Promise<VerificationResult> {
    try {
      // Navigate to trainee list
      await this.page.goto(`${BASE_URL}/trainees/home`);
      await this.page.waitForLoadState('networkidle');

      // Try to find trainee by name or IC in the list
      const searchTerm = trainee.firstName || trainee.icNumber || trainee.email;

      // Look for search input and search (server-side filter)
      const searchInput = this.page.locator('#search');
      if (await searchInput.isVisible({ timeout: 3000 }).catch(() => false)) {
        await searchInput.fill(searchTerm || '');
        const filterBtn = this.page.locator('button:has-text("Filter")');
        if (await filterBtn.isVisible({ timeout: 2000 }).catch(() => false)) {
          await filterBtn.click();
        } else {
          await searchInput.press('Enter');
        }
        await this.page.waitForLoadState('networkidle');
      }

      // Check if trainee appears in cards or table
      const foundByName = await this.page.locator(`.trainee-card:has-text("${trainee.firstName}")`).first().isVisible().catch(() => false);
      const foundByEmail = trainee.email ? (await this.page.content()).includes(trainee.email) : false;

      if (foundByName || foundByEmail) {
        return {
          found: true,
          message: `Trainee found: ${trainee.firstName} ${trainee.lastName || ''}`,
          data: trainee as Record<string, any>,
        };
      }

      // If using pagination, check if there's a link to the trainee
      const pageLinks = await this.page.locator('a:has-text("View")').count();
      if (pageLinks > 0) {
        // Scan through visible records
        const tableRows = await this.page.locator('table tbody tr').count();
        for (let i = 0; i < Math.min(tableRows, 10); i++) {
          const row = this.page.locator('table tbody tr').nth(i);
          const rowText = await row.textContent() || '';

          if (rowText.includes(trainee.firstName || '') ||
              rowText.includes(trainee.email || '')) {
            return {
              found: true,
              message: `Trainee found in row: ${rowText.substring(0, 50)}...`,
              data: trainee as Record<string, any>,
            };
          }
        }
      }

      return {
        found: false,
        message: `Trainee not found: ${trainee.firstName} ${trainee.lastName || ''}`,
      };
    } catch (error) {
      return {
        found: false,
        message: `Error verifying trainee: ${error}`,
      };
    }
  }

  /**
   * Verify activity exists in the system
   */
  async verifyActivityExists(activity: Partial<ActivityData>): Promise<VerificationResult> {
    try {
      // Navigate to activity list
      await this.page.goto(`${BASE_URL}/activities/home`);
      await this.page.waitForLoadState('networkidle');

      // Use client-side search via #searchInput
      const searchInput = this.page.locator('#searchInput');
      if (await searchInput.isVisible({ timeout: 3000 }).catch(() => false)) {
        await searchInput.fill(activity.name || '');
        await this.page.waitForTimeout(500);
      }

      // Check for activity card with the name
      const foundInCard = await this.page.locator(`.activity-card:has-text("${activity.name}")`).first().isVisible().catch(() => false);

      if (foundInCard) {
        return {
          found: true,
          message: `Activity found: ${activity.name}`,
          data: activity as Record<string, any>,
        };
      }

      return {
        found: false,
        message: `Activity not found: ${activity.name}`,
      };
    } catch (error) {
      return {
        found: false,
        message: `Error verifying activity: ${error}`,
      };
    }
  }

  /**
   * Verify trainee was deleted (no longer in list)
   */
  async verifyTraineeDeleted(trainee: Partial<TraineeData>): Promise<VerificationResult> {
    const exists = await this.verifyTraineeExists(trainee);
    return {
      found: !exists.found,
      message: exists.found
        ? `Trainee still exists: ${trainee.firstName}`
        : `Trainee successfully deleted: ${trainee.firstName}`,
    };
  }

  /**
   * Verify activity was deleted
   */
  async verifyActivityDeleted(activity: Partial<ActivityData>): Promise<VerificationResult> {
    const exists = await this.verifyActivityExists(activity);
    return {
      found: !exists.found,
      message: exists.found
        ? `Activity still exists: ${activity.name}`
        : `Activity successfully deleted: ${activity.name}`,
    };
  }

  /**
   * Verify data was updated by checking detail page
   */
  async verifyTraineeUpdated(
    trainee: Partial<TraineeData>,
    updatedFields: Partial<TraineeData>
  ): Promise<VerificationResult> {
    try {
      // First find the trainee
      await this.page.goto(`${BASE_URL}/trainees/home`);
      await this.page.waitForLoadState('networkidle');

      // Search for trainee
      const searchInput = this.page.locator('input[type="search"], input[placeholder*="Search"]');
      if (await searchInput.isVisible()) {
        await searchInput.fill(trainee.email || trainee.firstName || '');
        await this.page.waitForTimeout(500);
      }

      // Click on view/edit link
      const viewLink = this.page.locator('a:has-text("View")').first();
      if (await viewLink.isVisible()) {
        await viewLink.click();
        await this.page.waitForLoadState('networkidle');

        // Check for updated values on the page
        const pageContent = await this.page.content();

        let allFieldsMatch = true;
        const mismatches: string[] = [];

        for (const [key, value] of Object.entries(updatedFields)) {
          if (value && !pageContent.includes(String(value))) {
            allFieldsMatch = false;
            mismatches.push(`${key}: expected "${value}" not found`);
          }
        }

        return {
          found: allFieldsMatch,
          message: allFieldsMatch
            ? 'All updated fields verified'
            : `Mismatches: ${mismatches.join(', ')}`,
          data: updatedFields as Record<string, any>,
        };
      }

      return {
        found: false,
        message: 'Could not navigate to trainee detail page',
      };
    } catch (error) {
      return {
        found: false,
        message: `Error verifying update: ${error}`,
      };
    }
  }

  /**
   * Count records on a list page
   */
  async countRecords(pageUrl: string): Promise<number> {
    await this.page.goto(`${BASE_URL}${pageUrl}`);
    await this.page.waitForLoadState('networkidle');

    // Try different methods to count
    const tableRows = await this.page.locator('table tbody tr').count();
    if (tableRows > 0) return tableRows;

    const cards = await this.page.locator('.card, [class*="card"]').count();
    if (cards > 0) return cards;

    // Check for "showing X of Y" text
    const countText = await this.page.locator('text=/showing.*of.*\\d+/i').textContent();
    if (countText) {
      const match = countText.match(/of\s+(\d+)/i);
      if (match) return parseInt(match[1], 10);
    }

    return 0;
  }

  /**
   * Verify toast notification appeared
   */
  async verifyToastNotification(
    type: 'success' | 'error' | 'warning' | 'info',
    expectedMessage?: string
  ): Promise<VerificationResult> {
    const toastClass = type === 'error' ? 'toast-error' : `toast-${type}`;

    try {
      await this.page.waitForSelector(`.${toastClass}`, { timeout: 5000 });

      const toast = this.page.locator(`.${toastClass}`);
      const isVisible = await toast.isVisible();

      if (!isVisible) {
        return {
          found: false,
          message: `Toast notification (${type}) not visible`,
        };
      }

      const messageElement = this.page.locator(`.${toastClass} .toast-message, .${toastClass}`);
      const actualMessage = await messageElement.textContent() || '';

      if (expectedMessage && !actualMessage.toLowerCase().includes(expectedMessage.toLowerCase())) {
        return {
          found: false,
          message: `Toast message mismatch. Expected: "${expectedMessage}", Got: "${actualMessage}"`,
        };
      }

      return {
        found: true,
        message: actualMessage,
        data: { type, message: actualMessage },
      };
    } catch {
      return {
        found: false,
        message: `Toast notification (${type}) did not appear`,
      };
    }
  }

  /**
   * Get form validation errors
   */
  async getValidationErrors(): Promise<string[]> {
    const errors: string[] = [];

    // Check for invalid form fields
    const invalidFields = await this.page.locator('.is-invalid').all();
    for (const field of invalidFields) {
      const name = await field.getAttribute('name') || 'unknown';
      const errorMsg = await this.page.locator(`.invalid-feedback:near(${name})`).textContent().catch(() => '');
      errors.push(`${name}: ${errorMsg || 'Invalid'}`);
    }

    // Check for error messages in general
    const errorAlerts = await this.page.locator('.alert-danger, .text-danger').all();
    for (const alert of errorAlerts) {
      const text = await alert.textContent();
      if (text) errors.push(text.trim());
    }

    return errors;
  }
}
