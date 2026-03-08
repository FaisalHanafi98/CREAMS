import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  timeout: 90_000,  // Increased for PHP dev server under sustained load
  globalSetup: './global-setup.ts',

  expect: {
    timeout: 5_000,
  },

  fullyParallel: false, // IMPORTANT: debugging > speed for now
  retries: 0,
  workers: 1,

  reporter: [
    ['html', { open: 'never' }],
    ['list'],
    ['json', { outputFile: 'test-results/results.json' }],
  ],

  use: {
    baseURL: 'http://localhost:8000',

    // CORE EVIDENCE SETTINGS
    screenshot: 'on',         // Screenshot EVERY test (pass & fail)
    video: 'on',              // ALWAYS RECORD
    trace: 'retain-on-failure',

    // Stability
    actionTimeout: 15_000,
    navigationTimeout: 90_000,  // Increased for slow PHP dev server

    // Test identity
    viewport: { width: 1280, height: 800 },

    // Reduce flakiness
    ignoreHTTPSErrors: true,
  },

  projects: [
    {
      name: 'chromium',
      use: { browserName: 'chromium' },
    },
  ],

  outputDir: 'test-results/',

  // Auto-start Laravel dev server if not already running
  webServer: {
    command: 'php artisan serve --port=8000',
    url: 'http://localhost:8000',
    reuseExistingServer: true,   // Reuse if already running
    cwd: '../..',                // Run from CREAMS project root
    timeout: 30_000,
  },
});
