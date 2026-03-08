import * as fs from 'fs';
import * as path from 'path';

/**
 * Performance test result interface
 */
export interface PerformanceTestResult {
  testName: string;
  module: string;
  operation: string;
  duration: number;
  success: boolean;
  timestamp: Date;
  details?: string;
}

/**
 * Performance benchmark thresholds
 */
export const PERFORMANCE_THRESHOLDS = {
  pageLoad: {
    excellent: 1000,      // < 1s
    good: 2000,           // < 2s
    acceptable: 3000,     // < 3s
    slow: 5000,           // < 5s
  },
  formSubmission: {
    excellent: 1500,      // < 1.5s
    good: 3000,           // < 3s
    acceptable: 5000,     // < 5s
    slow: 10000,          // < 10s
  },
  crudOperation: {
    create: 5000,
    read: 2000,
    update: 5000,
    delete: 3000,
  },
  fullCrudCycle: 30000,   // < 30s for full CRUD
};

/**
 * Performance Reporter - Collects and reports performance metrics
 */
export class PerformanceReporter {
  private results: PerformanceTestResult[] = [];
  private outputPath: string;

  constructor(outputPath?: string) {
    this.outputPath = outputPath || './test-results/performance';
  }

  /**
   * Add a test result
   */
  addResult(result: PerformanceTestResult): void {
    this.results.push(result);
  }

  /**
   * Get performance rating based on thresholds
   */
  getPerformanceRating(duration: number, type: 'pageLoad' | 'formSubmission'): string {
    const thresholds = PERFORMANCE_THRESHOLDS[type];

    if (duration < thresholds.excellent) return '🟢 Excellent';
    if (duration < thresholds.good) return '🟡 Good';
    if (duration < thresholds.acceptable) return '🟠 Acceptable';
    if (duration < thresholds.slow) return '🔴 Slow';
    return '⛔ Critical';
  }

  /**
   * Generate summary statistics
   */
  generateSummary(): {
    total: number;
    passed: number;
    failed: number;
    averageDuration: number;
    maxDuration: number;
    minDuration: number;
    byModule: Record<string, { count: number; avgDuration: number }>;
  } {
    if (this.results.length === 0) {
      return {
        total: 0,
        passed: 0,
        failed: 0,
        averageDuration: 0,
        maxDuration: 0,
        minDuration: 0,
        byModule: {},
      };
    }

    const passed = this.results.filter(r => r.success).length;
    const failed = this.results.filter(r => !r.success).length;
    const durations = this.results.map(r => r.duration);
    const avgDuration = durations.reduce((a, b) => a + b, 0) / durations.length;

    // Group by module
    const byModule: Record<string, { count: number; totalDuration: number }> = {};
    for (const result of this.results) {
      if (!byModule[result.module]) {
        byModule[result.module] = { count: 0, totalDuration: 0 };
      }
      byModule[result.module].count++;
      byModule[result.module].totalDuration += result.duration;
    }

    const moduleStats: Record<string, { count: number; avgDuration: number }> = {};
    for (const [module, stats] of Object.entries(byModule)) {
      moduleStats[module] = {
        count: stats.count,
        avgDuration: stats.totalDuration / stats.count,
      };
    }

    return {
      total: this.results.length,
      passed,
      failed,
      averageDuration: avgDuration,
      maxDuration: Math.max(...durations),
      minDuration: Math.min(...durations),
      byModule: moduleStats,
    };
  }

  /**
   * Generate text report
   */
  generateTextReport(): string {
    const summary = this.generateSummary();
    const lines: string[] = [];

    lines.push('═══════════════════════════════════════════════════════════════');
    lines.push('           CREAMS FUNCTIONAL TEST PERFORMANCE REPORT           ');
    lines.push('═══════════════════════════════════════════════════════════════');
    lines.push(`Generated: ${new Date().toISOString()}`);
    lines.push('');

    lines.push('┌─────────────────────────────────────────────────────────────┐');
    lines.push('│                     OVERALL SUMMARY                         │');
    lines.push('├─────────────────────────────────────────────────────────────┤');
    lines.push(`│ Total Tests:        ${summary.total.toString().padStart(10)} │`);
    lines.push(`│ Passed:             ${summary.passed.toString().padStart(10)} │`);
    lines.push(`│ Failed:             ${summary.failed.toString().padStart(10)} │`);
    lines.push(`│ Pass Rate:          ${((summary.passed / summary.total) * 100).toFixed(1).padStart(9)}% │`);
    lines.push(`│ Average Duration:   ${summary.averageDuration.toFixed(0).padStart(8)}ms │`);
    lines.push(`│ Max Duration:       ${summary.maxDuration.toString().padStart(8)}ms │`);
    lines.push(`│ Min Duration:       ${summary.minDuration.toString().padStart(8)}ms │`);
    lines.push('└─────────────────────────────────────────────────────────────┘');
    lines.push('');

    lines.push('┌─────────────────────────────────────────────────────────────┐');
    lines.push('│                    RESULTS BY MODULE                        │');
    lines.push('├─────────────────────────────────────────────────────────────┤');

    for (const [module, stats] of Object.entries(summary.byModule)) {
      const rating = this.getPerformanceRating(stats.avgDuration, 'formSubmission');
      lines.push(`│ ${module.padEnd(20)} ${stats.count.toString().padStart(5)} tests │ ${stats.avgDuration.toFixed(0).padStart(6)}ms avg │ ${rating} │`);
    }

    lines.push('└─────────────────────────────────────────────────────────────┘');
    lines.push('');

    lines.push('┌─────────────────────────────────────────────────────────────┐');
    lines.push('│                   DETAILED RESULTS                          │');
    lines.push('├─────────────────────────────────────────────────────────────┤');

    for (const result of this.results) {
      const status = result.success ? '✓' : '✗';
      const rating = this.getPerformanceRating(result.duration, 'formSubmission');
      lines.push(`│ ${status} ${result.testName.substring(0, 35).padEnd(35)} │ ${result.duration.toString().padStart(6)}ms │ ${rating.substring(0, 12).padEnd(12)} │`);
    }

    lines.push('└─────────────────────────────────────────────────────────────┘');
    lines.push('');

    lines.push('═══════════════════════════════════════════════════════════════');
    lines.push('                     PERFORMANCE THRESHOLDS                    ');
    lines.push('═══════════════════════════════════════════════════════════════');
    lines.push('🟢 Excellent: < 1.5s | 🟡 Good: < 3s | 🟠 Acceptable: < 5s | 🔴 Slow: < 10s');
    lines.push('');

    return lines.join('\n');
  }

  /**
   * Generate JSON report
   */
  generateJsonReport(): string {
    return JSON.stringify({
      generatedAt: new Date().toISOString(),
      summary: this.generateSummary(),
      thresholds: PERFORMANCE_THRESHOLDS,
      results: this.results,
    }, null, 2);
  }

  /**
   * Generate HTML report
   */
  generateHtmlReport(): string {
    const summary = this.generateSummary();

    return `
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CREAMS Performance Report</title>
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; }
    h1 { color: #333; text-align: center; }
    .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0; }
    .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
    .card h3 { margin: 0 0 10px 0; color: #666; font-size: 14px; }
    .card .value { font-size: 32px; font-weight: bold; color: #333; }
    .card.success .value { color: #28a745; }
    .card.fail .value { color: #dc3545; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; background: white; border-radius: 8px; overflow: hidden; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #333; color: white; }
    tr:hover { background: #f9f9f9; }
    .excellent { color: #28a745; }
    .good { color: #17a2b8; }
    .acceptable { color: #ffc107; }
    .slow { color: #dc3545; }
    .status-pass { color: #28a745; }
    .status-fail { color: #dc3545; }
    .timestamp { color: #666; text-align: center; margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="container">
    <h1>CREAMS Functional Test Performance Report</h1>
    <p class="timestamp">Generated: ${new Date().toISOString()}</p>

    <div class="summary-cards">
      <div class="card">
        <h3>Total Tests</h3>
        <div class="value">${summary.total}</div>
      </div>
      <div class="card success">
        <h3>Passed</h3>
        <div class="value">${summary.passed}</div>
      </div>
      <div class="card fail">
        <h3>Failed</h3>
        <div class="value">${summary.failed}</div>
      </div>
      <div class="card">
        <h3>Pass Rate</h3>
        <div class="value">${((summary.passed / Math.max(summary.total, 1)) * 100).toFixed(1)}%</div>
      </div>
      <div class="card">
        <h3>Avg Duration</h3>
        <div class="value">${summary.averageDuration.toFixed(0)}ms</div>
      </div>
      <div class="card">
        <h3>Max Duration</h3>
        <div class="value">${summary.maxDuration}ms</div>
      </div>
    </div>

    <h2>Results by Module</h2>
    <table>
      <thead>
        <tr>
          <th>Module</th>
          <th>Tests</th>
          <th>Avg Duration</th>
          <th>Rating</th>
        </tr>
      </thead>
      <tbody>
        ${Object.entries(summary.byModule).map(([module, stats]) => `
          <tr>
            <td>${module}</td>
            <td>${stats.count}</td>
            <td>${stats.avgDuration.toFixed(0)}ms</td>
            <td>${this.getPerformanceRating(stats.avgDuration, 'formSubmission')}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>

    <h2>Detailed Results</h2>
    <table>
      <thead>
        <tr>
          <th>Test Name</th>
          <th>Module</th>
          <th>Operation</th>
          <th>Duration</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        ${this.results.map(r => `
          <tr>
            <td>${r.testName}</td>
            <td>${r.module}</td>
            <td>${r.operation}</td>
            <td>${r.duration}ms</td>
            <td class="${r.success ? 'status-pass' : 'status-fail'}">${r.success ? '✓ Pass' : '✗ Fail'}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  </div>
</body>
</html>
    `;
  }

  /**
   * Save reports to files
   */
  async saveReports(): Promise<void> {
    // Ensure output directory exists
    if (!fs.existsSync(this.outputPath)) {
      fs.mkdirSync(this.outputPath, { recursive: true });
    }

    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');

    // Save text report
    fs.writeFileSync(
      path.join(this.outputPath, `performance-report-${timestamp}.txt`),
      this.generateTextReport()
    );

    // Save JSON report
    fs.writeFileSync(
      path.join(this.outputPath, `performance-report-${timestamp}.json`),
      this.generateJsonReport()
    );

    // Save HTML report
    fs.writeFileSync(
      path.join(this.outputPath, `performance-report-${timestamp}.html`),
      this.generateHtmlReport()
    );

    console.log(`Reports saved to ${this.outputPath}`);
  }

  /**
   * Clear all results
   */
  clear(): void {
    this.results = [];
  }
}

// Singleton instance for global use
export const globalPerformanceReporter = new PerformanceReporter();
