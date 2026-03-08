# CREAMS Playwright Browser Test Infrastructure Design

## Overview

This document defines the browser-based testing infrastructure for CREAMS using Playwright.
The goal is to catch issues where "unit tests pass but browser behavior fails."

## Test Credentials (from TestingGuideDataSeeder.php)

| Role | Email | Password | Dashboard |
|------|-------|----------|-----------|
| Admin | lakshmi.krishnan@iium.edu.my | Admin@2024! | /admin/dashboard |
| Supervisor | supervisor.gombak@iium.edu.my | Supervise@2024 | /supervisor/dashboard |
| Teacher | ahmad.hassan@iium.edu.my | Teacher@2024 | /teacher/dashboard |
| AJK | fatimah.abdullah@iium.edu.my | AJK@2024 | /ajk/dashboard |

## Architecture

### Directory Structure

```
tests/
├── Browser/
│   ├── PLAYWRIGHT_TEST_DESIGN.md     # This design doc
│   ├── playwright.config.ts          # Playwright configuration
│   ├── package.json                  # Node dependencies
│   │
│   ├── fixtures/                     # Test fixtures and setup
│   │   ├── auth.fixture.ts           # Authentication helpers
│   │   └── test-data.ts              # Test credentials and data
│   │
│   ├── pages/                        # Page Object Models (POM)
│   │   ├── BasePage.ts               # Common page methods
│   │   ├── LoginPage.ts              # Login page interactions
│   │   ├── DashboardPage.ts          # Dashboard common methods
│   │   ├── admin/
│   │   │   └── AdminDashboard.ts
│   │   ├── teacher/
│   │   │   └── TeacherDashboard.ts
│   │   ├── supervisor/
│   │   │   └── SupervisorDashboard.ts
│   │   └── ajk/
│   │       └── AJKDashboard.ts
│   │
│   ├── tests/                        # Test files
│   │   ├── auth/                     # Priority 1: Authentication tests
│   │   │   ├── login.spec.ts
│   │   │   ├── logout.spec.ts
│   │   │   └── session.spec.ts
│   │   ├── rbac/                     # Priority 2: RBAC tests
│   │   │   ├── admin-access.spec.ts
│   │   │   ├── teacher-access.spec.ts
│   │   │   ├── supervisor-access.spec.ts
│   │   │   └── ajk-access.spec.ts
│   │   ├── dashboard/                # Priority 3: Dashboard tests
│   │   │   └── dashboard.spec.ts
│   │   ├── trainee/                  # Priority 4: Trainee management
│   │   │   ├── trainee-list.spec.ts
│   │   │   └── trainee-create.spec.ts
│   │   └── activity/                 # Priority 5: Activity management
│   │       ├── activity-list.spec.ts
│   │       └── activity-schedule.spec.ts
│   │
│   └── utils/                        # Utility functions
│       ├── helpers.ts
│       └── selectors.ts
```

## Key Technical Details

### Authentication System (CRITICAL)

CREAMS uses **custom session-based authentication**, NOT Laravel Auth:

```php
// Session variables used:
session('id')        // User ID
session('role')      // admin|supervisor|teacher|ajk|trainee|parent
session('name')      // Display name
session('centre_id') // Multi-tenant data isolation
session('logged_in') // Boolean flag
```

### Login Form Selectors

```typescript
const LOGIN_SELECTORS = {
  identifierField: '#identifier',      // Email or IIUM ID
  passwordField: '#password',
  submitButton: '.login-btn',
  rememberCheckbox: '#remember',
  forgotPasswordLink: 'a[href*="forgot-password"]'
};
```

### Logout Method

```typescript
// Logout is a form submission, not a link
await page.evaluate(() => {
  document.querySelector('.logout-btn').click();
});
```

## Page Object Model Design

### BasePage.ts

```typescript
import { Page, expect } from '@playwright/test';

export class BasePage {
  constructor(protected page: Page) {}

  async navigate(path: string) {
    await this.page.goto(`http://localhost:8000${path}`);
  }

  async waitForPageLoad() {
    await this.page.waitForLoadState('networkidle');
  }

  async getCurrentUrl(): Promise<string> {
    return this.page.url();
  }

  async getPageTitle(): Promise<string> {
    return this.page.title();
  }

  async logout() {
    await this.page.evaluate(() => {
      const logoutBtn = document.querySelector('.logout-btn') as HTMLElement;
      if (logoutBtn) logoutBtn.click();
    });
    await this.page.waitForURL('**/');
  }

  async isLoggedIn(): Promise<boolean> {
    // Check for logout button presence
    return await this.page.locator('.logout-btn').isVisible();
  }
}
```

### LoginPage.ts

```typescript
import { Page, expect } from '@playwright/test';
import { BasePage } from './BasePage';

export class LoginPage extends BasePage {
  private selectors = {
    identifier: '#identifier',
    password: '#password',
    submitButton: '.login-btn',
    remember: '#remember',
    errorAlert: '.alert-danger',
    successAlert: '.alert-success'
  };

  async goto() {
    await this.navigate('/login');
  }

  async login(email: string, password: string, remember = false) {
    await this.page.fill(this.selectors.identifier, email);
    await this.page.fill(this.selectors.password, password);

    if (remember) {
      await this.page.check(this.selectors.remember);
    }

    await this.page.click(this.selectors.submitButton);
    await this.page.waitForLoadState('networkidle');
  }

  async expectLoginSuccess(expectedRole: string) {
    const url = this.page.url();
    expect(url).toContain(`/${expectedRole}/dashboard`);
  }

  async expectLoginFailure() {
    await expect(this.page.locator(this.selectors.errorAlert)).toBeVisible();
  }
}
```

## Priority Test Cases

### Priority 1: Authentication Tests

```typescript
// tests/auth/login.spec.ts
import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { TEST_USERS } from '../fixtures/test-data';

test.describe('Authentication - Login', () => {

  test('Admin can login with valid credentials', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
    await loginPage.expectLoginSuccess('admin');

    // Verify admin dashboard content
    await expect(page.locator('text=Admin Dashboard')).toBeVisible();
  });

  test('Teacher can login with valid credentials', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.teacher.email, TEST_USERS.teacher.password);
    await loginPage.expectLoginSuccess('teacher');
  });

  test('Supervisor can login with valid credentials', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.supervisor.email, TEST_USERS.supervisor.password);
    await loginPage.expectLoginSuccess('supervisor');
  });

  test('AJK can login with valid credentials', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.ajk.email, TEST_USERS.ajk.password);
    await loginPage.expectLoginSuccess('ajk');
  });

  test('Login fails with invalid credentials', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login('invalid@email.com', 'wrongpassword');
    await loginPage.expectLoginFailure();
  });

  test('Login fails with empty fields', async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await page.click('.login-btn');
    // Form validation should prevent submission
    expect(page.url()).toContain('/login');
  });
});
```

### Priority 2: RBAC Enforcement Tests

```typescript
// tests/rbac/admin-access.spec.ts
import { test, expect } from '@playwright/test';
import { LoginPage } from '../pages/LoginPage';
import { TEST_USERS } from '../fixtures/test-data';

test.describe('RBAC - Admin Access Control', () => {

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.admin.email, TEST_USERS.admin.password);
  });

  test('Admin can access user management', async ({ page }) => {
    await page.goto('http://localhost:8000/admin/users');
    await expect(page).not.toHaveURL(/login/);
    // Verify user list is displayed
  });

  test('Admin can access centre management', async ({ page }) => {
    await page.goto('http://localhost:8000/admin/centres');
    await expect(page).not.toHaveURL(/login/);
  });

  test('Admin can access all trainee records', async ({ page }) => {
    await page.goto('http://localhost:8000/trainees/home');
    await expect(page).not.toHaveURL(/login/);
  });
});

// tests/rbac/teacher-access.spec.ts
test.describe('RBAC - Teacher Access Restrictions', () => {

  test.beforeEach(async ({ page }) => {
    const loginPage = new LoginPage(page);
    await loginPage.goto();
    await loginPage.login(TEST_USERS.teacher.email, TEST_USERS.teacher.password);
  });

  test('Teacher CANNOT access admin user management', async ({ page }) => {
    await page.goto('http://localhost:8000/admin/users');
    // Should redirect to unauthorized or dashboard
    await expect(page).not.toHaveURL('/admin/users');
  });

  test('Teacher CAN access activities', async ({ page }) => {
    await page.goto('http://localhost:8000/activities/home');
    await expect(page).not.toHaveURL(/login/);
  });
});
```

## Test Data Configuration

```typescript
// fixtures/test-data.ts
export const TEST_USERS = {
  admin: {
    email: 'lakshmi.krishnan@iium.edu.my',
    password: 'Admin@2024!',
    role: 'admin',
    dashboard: '/admin/dashboard',
    name: 'Dr. Lakshmi a/p Krishnan'
  },
  supervisor: {
    email: 'supervisor.gombak@iium.edu.my',
    password: 'Supervise@2024',
    role: 'supervisor',
    dashboard: '/supervisor/dashboard',
    name: 'Dr. Aminah binti Mohd Said'
  },
  teacher: {
    email: 'ahmad.hassan@iium.edu.my',
    password: 'Teacher@2024',
    role: 'teacher',
    dashboard: '/teacher/dashboard',
    name: 'Ustaz Ahmad bin Hassan'
  },
  ajk: {
    email: 'fatimah.abdullah@iium.edu.my',
    password: 'AJK@2024',
    role: 'ajk',
    dashboard: '/ajk/dashboard',
    name: 'Siti Fatimah binti Abdullah'
  }
};

export const BASE_URL = 'http://localhost:8000';
```

## Playwright Configuration

```typescript
// playwright.config.ts
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: false, // Run tests sequentially for CREAMS
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1, // Single worker to avoid session conflicts
  reporter: 'html',

  use: {
    baseURL: 'http://localhost:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // Ensure Laravel server is running
  webServer: {
    command: 'php artisan serve',
    url: 'http://localhost:8000',
    reuseExistingServer: !process.env.CI,
    cwd: '../..',
  },
});
```

## Implementation Phases

### Phase 1: Setup (Current)
- [x] Verify login functionality with Playwright MCP
- [x] Document test credentials and selectors
- [ ] Create directory structure
- [ ] Install Playwright dependencies
- [ ] Create base configuration

### Phase 2: Authentication Tests
- [ ] Implement LoginPage POM
- [ ] Implement login tests for all roles
- [ ] Implement logout tests
- [ ] Implement session persistence tests

### Phase 3: RBAC Tests
- [ ] Test admin access to all routes
- [ ] Test teacher access restrictions
- [ ] Test supervisor access restrictions
- [ ] Test AJK access restrictions
- [ ] Test unauthorized access handling

### Phase 4: Dashboard Tests
- [ ] Verify dashboard stats for each role
- [ ] Test quick action links
- [ ] Test navigation sidebar

### Phase 5: Core Feature Tests
- [ ] Trainee management workflow
- [ ] Activity scheduling workflow
- [ ] Attendance marking workflow

## Running Tests

```bash
# Install dependencies
cd tests/Browser
npm install

# Run all tests
npx playwright test

# Run specific test file
npx playwright test tests/auth/login.spec.ts

# Run with UI mode
npx playwright test --ui

# Run headed (see browser)
npx playwright test --headed

# Generate report
npx playwright show-report
```

## Notes

1. **Session Isolation**: CREAMS uses server-side sessions. Each test should start fresh.
2. **Multi-tenancy**: Tests should verify centre_id data isolation.
3. **No Test Database**: Using production database with seeded test data.
4. **Selectors**: Prefer data-testid attributes for stability (future improvement).

---
*Created: 2026-01-25*
*Based on: CREAMS_Testing_Infrastructure_PRD.md*
