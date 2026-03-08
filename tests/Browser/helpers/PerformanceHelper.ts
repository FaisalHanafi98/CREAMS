import { Page } from '@playwright/test';

/**
 * Performance measurement interface
 */
export interface PerformanceMetrics {
  pageLoadTime: number;
  domContentLoaded: number;
  firstContentfulPaint: number;
  largestContentfulPaint: number;
  timeToInteractive: number;
  totalBlockingTime: number;
}

/**
 * Operation timing interface
 */
export interface OperationTiming {
  operationName: string;
  startTime: number;
  endTime: number;
  duration: number;
  success: boolean;
  toastMessage?: string;
}

/**
 * Performance Helper for measuring page and operation performance
 */
export class PerformanceHelper {
  private operationTimings: OperationTiming[] = [];

  constructor(private page: Page) {}

  /**
   * Measure page load performance metrics
   */
  async measurePageLoad(): Promise<PerformanceMetrics> {
    const metrics = await this.page.evaluate(() => {
      const performance = window.performance;
      const navigation = performance.getEntriesByType('navigation')[0] as PerformanceNavigationTiming;
      const paint = performance.getEntriesByType('paint');

      const fcp = paint.find(p => p.name === 'first-contentful-paint');

      return {
        pageLoadTime: navigation.loadEventEnd - navigation.startTime,
        domContentLoaded: navigation.domContentLoadedEventEnd - navigation.startTime,
        firstContentfulPaint: fcp ? fcp.startTime : 0,
        largestContentfulPaint: 0, // Would need PerformanceObserver
        timeToInteractive: navigation.domInteractive - navigation.startTime,
        totalBlockingTime: 0, // Would need PerformanceObserver
      };
    });

    return metrics;
  }

  /**
   * Start timing an operation
   */
  startOperation(operationName: string): number {
    return Date.now();
  }

  /**
   * End timing an operation and record results
   */
  async endOperation(
    operationName: string,
    startTime: number,
    success: boolean = true
  ): Promise<OperationTiming> {
    const endTime = Date.now();
    const duration = endTime - startTime;

    // Try to get toast message if present
    let toastMessage: string | undefined;
    try {
      const toast = this.page.locator('.toast-message').first();
      if (await toast.isVisible({ timeout: 1000 })) {
        toastMessage = await toast.textContent() || undefined;
      }
    } catch {
      // No toast visible
    }

    const timing: OperationTiming = {
      operationName,
      startTime,
      endTime,
      duration,
      success,
      toastMessage,
    };

    this.operationTimings.push(timing);
    return timing;
  }

  /**
   * Measure form submission time
   */
  async measureFormSubmission(
    submitAction: () => Promise<void>,
    operationName: string
  ): Promise<OperationTiming> {
    const startTime = this.startOperation(operationName);

    let success = true;
    try {
      await submitAction();

      // Wait for response (either toast or page navigation)
      // CREAMS uses .toast-notification.toast-success and .toast-notification.toast-error
      await Promise.race([
        this.page.waitForSelector('.toast-notification.toast-success', { timeout: 10000 }),
        this.page.waitForSelector('.toast-notification.toast-error', { timeout: 10000 }),
        this.page.waitForNavigation({ timeout: 10000 }),
      ]).catch(() => {});

    } catch (error) {
      success = false;
    }

    // Check for error toast
    const hasError = await this.page.locator('.toast-notification.toast-error').isVisible().catch(() => false);
    if (hasError) {
      success = false;
    }

    return await this.endOperation(operationName, startTime, success);
  }

  /**
   * Get all recorded operation timings
   */
  getOperationTimings(): OperationTiming[] {
    return [...this.operationTimings];
  }

  /**
   * Clear recorded timings
   */
  clearTimings(): void {
    this.operationTimings = [];
  }

  /**
   * Generate performance report
   */
  generateReport(): string {
    const report: string[] = [];
    report.push('=== PERFORMANCE REPORT ===\n');

    for (const timing of this.operationTimings) {
      report.push(`Operation: ${timing.operationName}`);
      report.push(`  Duration: ${timing.duration}ms`);
      report.push(`  Success: ${timing.success}`);
      if (timing.toastMessage) {
        report.push(`  Message: ${timing.toastMessage}`);
      }
      report.push('');
    }

    // Calculate averages
    if (this.operationTimings.length > 0) {
      const avgDuration = this.operationTimings.reduce((sum, t) => sum + t.duration, 0) / this.operationTimings.length;
      const successRate = this.operationTimings.filter(t => t.success).length / this.operationTimings.length * 100;

      report.push('--- SUMMARY ---');
      report.push(`Total Operations: ${this.operationTimings.length}`);
      report.push(`Average Duration: ${avgDuration.toFixed(2)}ms`);
      report.push(`Success Rate: ${successRate.toFixed(1)}%`);
    }

    return report.join('\n');
  }

  /**
   * Assert page load time is within threshold
   */
  async assertPageLoadTime(maxMs: number): Promise<void> {
    const metrics = await this.measurePageLoad();
    if (metrics.pageLoadTime > maxMs) {
      throw new Error(
        `Page load time (${metrics.pageLoadTime}ms) exceeded threshold (${maxMs}ms)`
      );
    }
  }

  /**
   * Assert operation completed within threshold
   */
  assertOperationTime(timing: OperationTiming, maxMs: number): void {
    if (timing.duration > maxMs) {
      throw new Error(
        `Operation "${timing.operationName}" (${timing.duration}ms) exceeded threshold (${maxMs}ms)`
      );
    }
  }
}
