import { Page, Locator } from '@playwright/test';
import { BasePage } from './BasePage';

export class DashboardPage extends BasePage {
  // Dashboard widgets
  readonly totalTraineesWidget: Locator;
  readonly totalStaffWidget: Locator;
  readonly totalActivitiesWidget: Locator;
  readonly totalCentresWidget: Locator;

  // Navigation links
  readonly activitiesLink: Locator;
  readonly traineesLink: Locator;
  readonly staffLink: Locator;
  readonly centresLink: Locator;

  constructor(page: Page) {
    super(page);

    // Initialize widget locators (flexible selectors)
    this.totalTraineesWidget = page.locator('text=/total.*trainees/i').first();
    this.totalStaffWidget = page.locator('text=/total.*staff/i').first();
    this.totalActivitiesWidget = page.locator('text=/total.*activities/i').first();
    this.totalCentresWidget = page.locator('text=/total.*centres/i').first();

    // Initialize navigation links
    this.activitiesLink = page.locator('a:has-text("Activities")').first();
    this.traineesLink = page.locator('a:has-text("Trainees")').first();
    this.staffLink = page.locator('a:has-text("Staff")').first();
    this.centresLink = page.locator('a:has-text("Centres")').first();
  }

  /**
   * Navigate to dashboard
   */
  async goto(): Promise<void> {
    await this.navigate('/dashboard');
  }

  /**
   * Get value from a stat widget
   */
  async getStatValue(widgetText: string): Promise<number> {
    const widget = this.page.locator(`text=${widgetText}`).first();
    const parent = widget.locator('..'); // Get parent element
    const text = await parent.textContent();

    // Extract number from text
    const match = text?.match(/\d+/);
    return match ? parseInt(match[0]) : 0;
  }

  /**
   * Check if widget is visible
   */
  async isWidgetVisible(widgetText: string): Promise<boolean> {
    const widget = this.page.locator(`text=${widgetText}`).first();
    return widget.isVisible({ timeout: 5000 }).catch(() => false);
  }

  /**
   * Navigate to module from dashboard
   */
  async navigateToModule(moduleName: 'activities' | 'trainees' | 'staff' | 'centres'): Promise<void> {
    const links = {
      activities: this.activitiesLink,
      trainees: this.traineesLink,
      staff: this.staffLink,
      centres: this.centresLink,
    };

    await links[moduleName].click();
    await this.waitForPageLoad();
  }

  /**
   * Get all dashboard statistics
   */
  async getAllStats(): Promise<Record<string, number>> {
    const stats: Record<string, number> = {};

    const widgets = [
      { name: 'trainees', locator: this.totalTraineesWidget },
      { name: 'staff', locator: this.totalStaffWidget },
      { name: 'activities', locator: this.totalActivitiesWidget },
      { name: 'centres', locator: this.totalCentresWidget },
    ];

    for (const widget of widgets) {
      try {
        const parent = widget.locator.locator('..');
        const text = await parent.textContent();
        const match = text?.match(/\d+/);
        stats[widget.name] = match ? parseInt(match[0]) : 0;
      } catch {
        stats[widget.name] = 0;
      }
    }

    return stats;
  }

  /**
   * Check if user has admin privileges on dashboard
   */
  async hasAdminAccess(): Promise<boolean> {
    // Check for admin-specific elements
    const settingsLink = this.page.locator('a:has-text("Settings"), a[href*="settings"]').first();
    const allCentresOption = this.page.locator('text=/all.*centres/i').first();

    const hasSettings = await settingsLink.isVisible({ timeout: 2000 }).catch(() => false);
    const hasAllCentres = await allCentresOption.isVisible({ timeout: 2000 }).catch(() => false);

    return hasSettings || hasAllCentres;
  }

  /**
   * Wait for dashboard widgets to load
   */
  async waitForWidgetsToLoad(): Promise<void> {
    await this.page.waitForSelector('.stat-card, .dashboard-stat, .widget, [class*="card"]', {
      timeout: 10000
    });
  }
}
