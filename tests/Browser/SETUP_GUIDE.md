# CREAMS Playwright Tests - Setup & Troubleshooting Guide

## Prerequisites Checklist

Before running tests, verify these steps:

### 1. Install Node Dependencies

```bash
cd tests/Browser
npm install
```

Expected output: Should install @playwright/test and dependencies

### 2. Install Playwright Browsers

```bash
npx playwright install chromium
```

Expected output: Downloads Chromium browser (~150MB)

### 3. Start MySQL Server

**CRITICAL**: Tests will fail if MySQL is not running!

Check if MySQL is running:
```bash
# Check MySQL service status
net start | findstr MySQL
```

If MySQL is not running, start it:
```bash
# Start MySQL service (use your actual MySQL service name)
net start MySQL80  # or MySQL57, MySQL, etc.
```

Expected output:
```
The MySQL80 service is starting.
The MySQL80 service was started successfully.
```

### 4. Start Laravel Server

**CRITICAL**: Tests will fail if Laravel is not running!

```bash
# In CREAMS root directory (not Browser folder)
cd c:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS
php artisan serve
```

Expected output:
```
   INFO  Server running on [http://127.0.0.1:8000].

  Press Ctrl+C to stop the server
```

**Keep this terminal open** - do not close it while running tests.

### 5. Verify Database

Ensure MySQL is running and the CREAMS database is set up:

```bash
# Check .env file has correct database
type .env | findstr DB_

# Should show:
# DB_CONNECTION=mysql
# DB_DATABASE=cream
# DB_USERNAME=root
# DB_PASSWORD=
```

Test database connection:
```bash
php artisan db:show
```

## Running Tests

### Step-by-Step Process

1. **Start MySQL Server**:
   ```bash
   net start MySQL80
   ```

2. **Terminal 1** - Start Laravel:
   ```bash
   cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS
   php artisan serve
   ```

3. **Terminal 2** - Run tests:
   ```bash
   cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS\tests\Browser

   # Run diagnostic test first
   npx playwright test 00-diagnostic.spec.ts --headed
   ```

### If Diagnostic Test Passes

```bash
# Run authentication tests
npx playwright test tests/auth/login.spec.ts --headed

# Run all tests
npm test
```

## Troubleshooting

### Error: "An error occurred during login"

**Problem**: MySQL server not running or database connection failed

**Solution**:
```bash
# Check if MySQL is running
net start | findstr MySQL

# If not running, start MySQL
net start MySQL80

# Verify database connection
cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS
php artisan db:show
```

### Error: "net::ERR_CONNECTION_REFUSED"

**Problem**: Laravel server not running

**Solution**:
```bash
# Start Laravel in separate terminal
cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS
php artisan serve
```

### Error: "Timeout 30000ms exceeded"

**Problem**: Page not loading fast enough or server not responding

**Solutions**:
1. Verify Laravel server is running
2. Check database is connected
3. Try clearing Laravel cache:
   ```bash
   php artisan optimize:clear
   ```

### Error: "locator.fill: Target closed"

**Problem**: Form submission redirecting too fast

**Solution**: Already handled in code with `waitForLoadState('networkidle')`

### Error: "Cannot find module '@playwright/test'"

**Problem**: Dependencies not installed

**Solution**:
```bash
cd tests/Browser
npm install
```

### Error: "browserType.launch: Executable doesn't exist"

**Problem**: Playwright browsers not installed

**Solution**:
```bash
npx playwright install chromium
```

## Verifying Setup

Run this checklist before filing issues:

```bash
# 1. Check Laravel is running
curl http://localhost:8000

# 2. Check login page loads
curl http://localhost:8000/login

# 3. Check Node modules installed
ls node_modules/@playwright

# 4. Check Playwright browser
npx playwright --version
```

## Common Issues

### Tests Pass in Playwright MCP but Fail in Playwright Tests

This suggests:
- Timing differences (MCP is slower, gives more time to load)
- Missing `waitForLoadState('networkidle')` calls
- CSRF token issues

**Solution**: Run diagnostic test to see exact error

### All Tests Fail with Same Timeout

This indicates:
- **Laravel server definitely not running**
- Wrong base URL in config
- Firewall blocking localhost:8000

## Next Steps After Diagnostic

1. Run diagnostic test:
   ```bash
   npx playwright test 00-diagnostic.spec.ts --headed
   ```

2. Check console output for specific errors

3. Check screenshots in `test-results/` folder:
   - `diagnostic-login-page.png` - Shows what login page looks like
   - `diagnostic-after-login.png` - Shows what happened after login

4. Share error messages if diagnostic fails

## Quick Reference

```bash
# Full test workflow
cd C:\Users\asbou\OneDrive\Desktop\Work\Development\CREAMS

# Terminal 1
php artisan serve

# Terminal 2
cd tests\Browser
npm install
npx playwright install chromium
npx playwright test 00-diagnostic.spec.ts --headed
```
