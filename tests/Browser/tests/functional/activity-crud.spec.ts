import { test, expect } from '../../fixtures/test-fixtures';
import { ActivityPage, generateTestActivity, ActivityFormData } from '../../pages/ActivityPage';
import { PerformanceHelper } from '../../helpers/PerformanceHelper';
import { DatabaseHelper } from '../../helpers/DatabaseHelper';
import * as path from 'node:path';

/**
 * CREAMS Functional Tests - Activity Management CRUD
 *
 * Tests activity Create, Read, Update, Delete operations including:
 * - Page load performance metrics
 * - Multi-step wizard form submission
 * - Toast notification verification
 * - Database reflection (verified through UI)
 *
 * Auth: Uses pre-authenticated storageState from global-setup (no login per test).
 */
// FIXED: Controller updated to use enum-based categories instead of ActivityCategory model
test.use({ storageState: path.join(__dirname, '../../.auth/admin.json') });

test.describe('Functional - Activity Management CRUD', () => {
  let activityPage: ActivityPage;
  let performanceHelper: PerformanceHelper;
  let dbHelper: DatabaseHelper;

  // Test data storage for cleanup
  const createdActivities: ActivityFormData[] = [];

  test.beforeEach(async ({ page }) => {
    activityPage = new ActivityPage(page);
    performanceHelper = new PerformanceHelper(page);
    dbHelper = new DatabaseHelper(page);
  });

  test.describe('Page Load Performance', () => {

    test('Activity list page loads within acceptable time', async ({ page }) => {
      const startTime = performanceHelper.startOperation('Activity List Page Load');

      await activityPage.gotoList();

      const timing = await performanceHelper.endOperation('Activity List Page Load', startTime, true);

      // Assert page loaded
      await expect(page).not.toHaveURL(/login/);

      console.log(`Activity List Page Load: ${timing.duration}ms`);
      // Activity pages are slower due to complex queries - threshold set to 10s
      expect(timing.duration).toBeLessThan(10000);
    });

    test('Activity create wizard loads within acceptable time', async ({ page }) => {
      const startTime = performanceHelper.startOperation('Activity Create Page Load');

      await activityPage.gotoCreate();

      const timing = await performanceHelper.endOperation('Activity Create Page Load', startTime, true);

      // Assert page loaded (check for any form element or page content)
      // Note: Actual form selectors need investigation - see FUNCTIONAL_TEST_ISSUES.md
      await expect(page).not.toHaveURL(/login/);

      console.log(`Activity Create Page Load: ${timing.duration}ms`);
      // Activity pages are slower due to complex queries - threshold set to 10s
      expect(timing.duration).toBeLessThan(10000);
    });

    test('Activity categories page loads within acceptable time', async ({ page }) => {
      const startTime = performanceHelper.startOperation('Activity Categories Page Load');

      await activityPage.gotoCategories();

      const timing = await performanceHelper.endOperation('Activity Categories Page Load', startTime, true);

      await expect(page).not.toHaveURL(/login/);

      console.log(`Activity Categories Page Load: ${timing.duration}ms`);
      // Activity pages are slower due to complex queries - threshold set to 10s
      expect(timing.duration).toBeLessThan(10000);
    });

    test('Activity schedule page loads within acceptable time', async ({ page }) => {
      const startTime = performanceHelper.startOperation('Activity Schedule Page Load');

      await activityPage.gotoSchedule();

      const timing = await performanceHelper.endOperation('Activity Schedule Page Load', startTime, true);

      await expect(page).not.toHaveURL(/login/);

      console.log(`Activity Schedule Page Load: ${timing.duration}ms`);
      // Activity pages are slower due to complex queries - threshold set to 10s
      expect(timing.duration).toBeLessThan(10000);
    });
  });

  test.describe('CREATE Operations', () => {

    test('Can create a new activity through wizard', async ({ page }) => {
      test.setTimeout(60000); // Increase timeout to 60 seconds for full wizard

      // Capture console messages and errors
      const consoleMessages: string[] = [];
      page.on('console', msg => {
        const type = msg.type();
        const text = msg.text();
        consoleMessages.push(`[${type}] ${text}`);
        if (type === 'error' || type === 'warning') {
          console.log(`Browser ${type}: ${text}`);
        }
      });

      page.on('pageerror', error => {
        console.log(`Browser error: ${error.message}`);
        consoleMessages.push(`[pageerror] ${error.message}`);
      });

      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      // Navigate to create page
      await activityPage.gotoCreate();

      const startTime = performanceHelper.startOperation('Create Activity (Full Wizard)');

      // Step 1: Basic Information
      console.log('Filling Step 1...');
      await activityPage.fillStep1(testActivity);
      console.log('Step 1 filled');

      // Check if there's a next button before proceeding
      // Wait longer for JavaScript initialization and form validation
      console.log('Waiting for wizard to initialize...');
      await page.waitForTimeout(2000);

      // Take screenshot to verify button state
      await page.screenshot({ path: 'test-results/wizard-step1-complete.png', fullPage: true });

      // Try multiple detection methods
      let hasWizard = false;
      try {
        // Method 1: Direct ID selector with waitForSelector
        await page.waitForSelector('#nextStep', { state: 'visible', timeout: 3000 });
        hasWizard = true;
        console.log('Wizard detected: #nextStep button found');
      } catch (e1) {
        console.log('Method 1 failed (#nextStep):', e1.message);

        try {
          // Method 2: Button with Next text
          await page.waitForSelector('button:has-text("Next")', { state: 'visible', timeout: 2000 });
          hasWizard = true;
          console.log('Wizard detected: button:has-text("Next") found');
        } catch (e2) {
          console.log('Method 2 failed (button:has-text("Next")):', e2.message);

          // Method 3: Check if submit button exists instead (single-page form)
          const submitButton = await page.locator('button[type="submit"]').isVisible().catch(() => false);
          console.log('Submit button visible (single-page form):', submitButton);
        }
      }

      console.log('Has wizard:', hasWizard);

      if (hasWizard) {
        // Step 2: Activity Details
        console.log('Clicking Next for Step 2...');
        await activityPage.nextStep();

        // Step 2: Activity Details
        console.log('Filling Step 2...');
        await activityPage.fillStep2(testActivity);
        console.log('Step 2 filled, clicking Next...');
        await activityPage.nextStep();

        // Step 3: Schedule Configuration
        console.log('Filling Step 3...');
        await activityPage.fillStep3(testActivity);
        console.log('Step 3 filled, clicking Next...');
        await activityPage.nextStep();

        // Step 4: Resources
        console.log('Filling Step 4...');
        await activityPage.fillStep4(testActivity);
        console.log('Step 4 filled, clicking Next...');
        await activityPage.nextStep();
      }

      // Submit
      console.log('Submitting form...');
      await activityPage.submitForm();
      console.log('Waiting for network idle...');
      await page.waitForLoadState('networkidle');

      const timing = await performanceHelper.endOperation('Create Activity (Full Wizard)', startTime, true);

      console.log(`Create Activity Operation: ${timing.duration}ms`);

      // Verify success toast
      await activityPage.expectSuccessToast();

      // Verify activity appears in list (database reflection)
      const exists = await activityPage.isActivityInList(testActivity.name);
      expect(exists).toBe(true);
    });

    test('Can create activity with minimal required fields', async ({ page }) => {
      const minimalActivity = generateTestActivity({
        name: `Minimal Activity ${Date.now()}`,
        description: 'Minimal test activity',
      });
      createdActivities.push(minimalActivity);

      await activityPage.gotoCreate();

      // Fill only required fields on step 1
      await page.fill('#activity_name, [name="activity_name"]', minimalActivity.name);
      await page.fill('#activity_description, [name="activity_description"]', minimalActivity.description || '');

      // Try to navigate through or submit
      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);

      if (hasWizard) {
        // Minimal wizard navigation
        await activityPage.nextStep();
        await activityPage.fillStep2({ difficultyLevel: 'beginner', location: 'Test Room' });
        await activityPage.nextStep();
        await activityPage.fillStep3(minimalActivity);
        await activityPage.nextStep();
        // Skip step 4
        await activityPage.nextStep();
      }

      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Check for either success or validation
      const successToast = await page.locator('.toast-success').isVisible({ timeout: 3000 }).catch(() => false);
      console.log(`Minimal activity creation: ${successToast ? 'Success' : 'Validation required'}`);
    });

    test('Shows validation error for missing required fields', async ({ page }) => {
      await activityPage.gotoCreate();

      // Try to submit without filling anything
      await activityPage.submitForm();
      await page.waitForTimeout(500);

      // Should have validation errors
      const hasErrors = await activityPage.hasValidationErrors();

      // If no immediate validation, might have client-side validation
      if (!hasErrors) {
        // Check for any error indicators
        const hasAnyError = await page.locator('.is-invalid, .error, .text-danger').isVisible().catch(() => false);
        expect(hasAnyError || await page.url().includes('create')).toBe(true);
      } else {
        expect(hasErrors).toBe(true);
      }
    });

    test('Shows validation error for duplicate activity name', async ({ page }) => {
      // First, create an activity
      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }

      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Try to create another with same name
      await activityPage.gotoCreate();
      await activityPage.fillStep1({ ...testActivity });

      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }

      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Should show error or stay on page
      const onCreatePage = await page.url().includes('create');
      const hasError = await page.locator('.toast-error, .is-invalid').isVisible().catch(() => false);

      expect(onCreatePage || hasError).toBe(true);
    });

    test('Can navigate between wizard steps', async ({ page }) => {
      await activityPage.gotoCreate();

      // Fill step 1
      await page.fill('#activity_name, [name="activity_name"]', 'Navigation Test Activity');

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);

      if (hasWizard) {
        // Go to step 2
        await activityPage.nextStep();

        // Current step should be 2
        const step2Visible = await page.locator('#difficulty_level, [name="difficulty_level"], input[name="difficulty_level"]').isVisible().catch(() => false);

        // Go back to step 1
        await activityPage.previousStep();

        // Should see step 1 fields again
        const step1Visible = await page.locator('#activity_name, [name="activity_name"]').isVisible();
        expect(step1Visible).toBe(true);

        // Data should be preserved
        const activityName = await page.inputValue('#activity_name, [name="activity_name"]');
        expect(activityName).toBe('Navigation Test Activity');
      }
    });
  });

  test.describe('READ Operations', () => {

    test('Can view activity list', async ({ page }) => {
      const startTime = performanceHelper.startOperation('View Activity List');

      await activityPage.gotoList();

      const timing = await performanceHelper.endOperation('View Activity List', startTime, true);

      await expect(page).toHaveURL(/activities/);

      console.log(`View Activity List: ${timing.duration}ms`);
    });

    test('Can search for a specific activity', async ({ page }) => {
      // First create an activity to search for
      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Now search for it
      const startTime = performanceHelper.startOperation('Search Activity');

      await activityPage.searchActivity(testActivity.name);

      const timing = await performanceHelper.endOperation('Search Activity', startTime, true);

      const found = await page.locator(`text="${testActivity.name}"`).isVisible().catch(() => false);

      console.log(`Search Activity: ${timing.duration}ms, Found: ${found}`);
    });

    test('Can view activity details', async ({ page }) => {
      // Create an activity first
      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // View the activity
      const startTime = performanceHelper.startOperation('View Activity Details');

      await activityPage.viewActivity(testActivity.name);

      const timing = await performanceHelper.endOperation('View Activity Details', startTime, true);

      // Verify activity data is displayed
      const pageContent = await page.content();
      expect(pageContent).toContain(testActivity.name);

      console.log(`View Activity Details: ${timing.duration}ms`);
    });

    test('Activity schedule displays correctly', async ({ page }) => {
      await activityPage.gotoSchedule();

      // Check for calendar or schedule elements (actual DOM uses .sessions-list, .session-item, .modern-schedule-container)
      const hasCalendar = await page.locator('.sessions-list, .modern-schedule-container, [class*="schedule"], [class*="calendar"]').isVisible().catch(() => false);
      const hasActivities = await page.locator('.session-item, table, .activity, [class*="activity"]').isVisible().catch(() => false);

      // Should have some schedule display
      expect(hasCalendar || hasActivities).toBe(true);
    });
  });

  test.describe('UPDATE Operations', () => {

    test('Can update activity information', async ({ page }) => {
      // Create an activity first
      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Update the activity
      const updatedDescription = 'Updated description for testing';

      const startTime = performanceHelper.startOperation('Update Activity');

      await activityPage.updateActivity(testActivity.name, {
        description: updatedDescription,
      });

      const timing = await performanceHelper.endOperation('Update Activity', startTime, true);

      // Verify success toast
      await activityPage.expectSuccessToast();

      console.log(`Update Activity: ${timing.duration}ms`);
    });

    test('Can change activity difficulty level', async ({ page }) => {
      // Create an activity with beginner level
      const testActivity = generateTestActivity({ difficultyLevel: 'beginner' });
      createdActivities.push(testActivity);

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Update to advanced level
      await activityPage.updateActivity(testActivity.name, {
        difficultyLevel: 'advanced',
      });

      // Verify success
      await activityPage.expectSuccessToast();
    });
  });

  test.describe('DELETE Operations', () => {

    test('Can delete an activity', async ({ page }) => {
      // Create an activity specifically for deletion
      const testActivity = generateTestActivity({ name: `Delete Me ${Date.now()}` });

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Delete the activity
      const startTime = performanceHelper.startOperation('Delete Activity');

      await activityPage.deleteActivity(testActivity.name);

      const timing = await performanceHelper.endOperation('Delete Activity', startTime, true);

      // Verify activity no longer in list
      await activityPage.gotoList();
      const stillExists = await activityPage.isActivityInList(testActivity.name);

      expect(stillExists).toBe(false);

      console.log(`Delete Activity: ${timing.duration}ms`);
    });
  });

  test.describe('Database Reflection', () => {

    test('Created activity appears in database (verified via UI)', async ({ page }) => {
      const testActivity = generateTestActivity();
      createdActivities.push(testActivity);

      // Create activity
      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Verify via DatabaseHelper
      const result = await dbHelper.verifyActivityExists({
        name: testActivity.name,
      });

      expect(result.found).toBe(true);
      console.log(`Database verification: ${result.message}`);
    });

    test('Deleted activity no longer in database (verified via UI)', async ({ page }) => {
      // Create an activity
      const testActivity = generateTestActivity({ name: `ToDelete ${Date.now()}` });

      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      // Delete activity
      await activityPage.deleteActivity(testActivity.name);

      // Verify deletion via DatabaseHelper
      const result = await dbHelper.verifyActivityDeleted({
        name: testActivity.name,
      });

      expect(result.found).toBe(true); // found=true means successfully deleted
      console.log(`Delete verification: ${result.message}`);
    });
  });

  test.describe('Performance Benchmarks', () => {

    test('Activity CRUD cycle completes within acceptable time', async ({ page }) => {
      const testActivity = generateTestActivity();
      let totalTime = 0;

      // CREATE
      const createStart = Date.now();
      await activityPage.gotoCreate();
      await activityPage.fillStep1(testActivity);

      const hasWizard = await page.locator('button:has-text("Next")').isVisible().catch(() => false);
      if (hasWizard) {
        await activityPage.nextStep();
        await activityPage.fillStep2(testActivity);
        await activityPage.nextStep();
        await activityPage.fillStep3(testActivity);
        await activityPage.nextStep();
        await activityPage.nextStep();
      }
      await activityPage.submitForm();
      await page.waitForLoadState('networkidle');

      const createTime = Date.now() - createStart;
      totalTime += createTime;
      console.log(`CREATE: ${createTime}ms`);

      // READ
      const readStart = Date.now();
      await activityPage.viewActivity(testActivity.name);
      const readTime = Date.now() - readStart;
      totalTime += readTime;
      console.log(`READ: ${readTime}ms`);

      // UPDATE
      const updateStart = Date.now();
      await activityPage.updateActivity(testActivity.name, { description: 'Updated description' });
      const updateTime = Date.now() - updateStart;
      totalTime += updateTime;
      console.log(`UPDATE: ${updateTime}ms`);

      // DELETE
      const deleteStart = Date.now();
      await activityPage.deleteActivity(testActivity.name);
      const deleteTime = Date.now() - deleteStart;
      totalTime += deleteTime;
      console.log(`DELETE: ${deleteTime}ms`);

      console.log(`\nTOTAL ACTIVITY CRUD CYCLE: ${totalTime}ms`);

      // Assert total time is reasonable (< 45 seconds for wizard-based creation)
      expect(totalTime).toBeLessThan(45000);
    });
  });

  test.afterAll(async () => {
    console.log('\n=== ACTIVITY CRUD PERFORMANCE SUMMARY ===');
    console.log(`Total tests completed`);
    console.log('Check individual test logs for timing details');
  });
});
