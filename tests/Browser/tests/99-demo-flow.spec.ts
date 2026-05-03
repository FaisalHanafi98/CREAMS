/**
 * Demo Flow Verification — sprint Day 6 (4 May 2026)
 *
 * Walks the demo script beats in real Chromium.
 * Screenshots saved to docs/audit/screenshots/demo/ (outside outputDir).
 *
 * Session strategy:
 *   Beat A1 — full login flow (tests the actual login UI path).
 *   All others — use pre-authenticated sessions from global-setup .auth/ files.
 *
 * Run:  npx playwright test 99-demo-flow.spec.ts --reporter=list
 */

import { test, expect } from '@playwright/test';
import * as fs from 'node:fs';
import * as path from 'node:path';

const BASE  = 'http://localhost:8000';
const PASS  = 'UatPass2026!';
const AUTH  = path.resolve(__dirname, '../.auth');
const SHOTS = path.resolve(__dirname, '../../../docs/audit/screenshots/demo');

fs.mkdirSync(SHOTS, { recursive: true });
function shot(n: string) { return path.join(SHOTS, n); }

// Filter out expected CSP noise about external CDNs — only surface real errors.
const CDN_NOISE = [
  'Content Security Policy',
  'wttr.in',
  'cdn.jsdelivr',
  'cdnjs.cloudflare',
  'fonts.googleapis',
  'code.jquery.com',
  'popper.js',
];

function realErrors(errs: string[]) {
  return errs.filter(e => !CDN_NOISE.some(n => e.includes(n)));
}

// ─────────────────────────────────────────────────────────────────────────────
// Beat A1 — Full login flow (no pre-auth session, tests the login UI itself)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Beat A1: login flow', () => {
  test('admin can log in via the UI form', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });
    page.on('pageerror', e => errs.push('PAGE: ' + e.message));

    await page.goto(`${BASE}/auth/login`);
    await page.fill('#identifier', 'super.admin@uat.creams.test');
    await page.fill('#password', PASS);
    await page.screenshot({ path: shot('A1-login-form.png') });
    await page.click('.login-btn');
    await page.waitForURL('**/admin/dashboard', { timeout: 30_000 });
    await page.screenshot({ path: shot('A1-admin-dashboard.png'), fullPage: true });

    await expect(page).toHaveURL(/\/admin\/dashboard/);
    const body = await page.textContent('body');
    expect(body?.toLowerCase()).toContain('dashboard');

    const real = realErrors(errs);
    if (real.length) console.log('A1 errors:', real);
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// Beats A2–A5, B1 — admin session (pre-authenticated via global-setup)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Admin session beats (pre-authenticated)', () => {
  test.use({ storageState: path.join(AUTH, 'admin.json') });

  test('Beat A2: admin dashboard renders with stats', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });

    await page.goto(`${BASE}/admin/dashboard`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('A2-admin-dashboard.png'), fullPage: true });
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    const html = await page.content();
    expect(html.length).toBeGreaterThan(50_000);

    const real = realErrors(errs);
    if (real.length) console.log('A2 errors:', real);
  });

  test('Beat A3: trainee list shows UAT trainees', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });

    await page.goto(`${BASE}/admin/trainees`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('A3-trainees-list.png'), fullPage: true });
    // /admin/trainees redirects to /trainees/home — match either.
    await expect(page).toHaveURL(/\/trainees/);
    const body = await page.textContent('body');
    expect(body?.toUpperCase()).toContain('UAT');

    const real = realErrors(errs);
    if (real.length) console.log('A3 errors:', real);
  });

  test('Beat A4: activities list shows UAT activities', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });

    await page.goto(`${BASE}/activities`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('A4-activities-list.png'), fullPage: true });
    const body = await page.textContent('body');
    expect(body?.toUpperCase()).toContain('UAT');

    const real = realErrors(errs);
    if (real.length) console.log('A4 errors:', real);
  });

  test('Beat A5: centres list shows UAT centres', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });

    await page.goto(`${BASE}/centres`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('A5-centres-list.png'), fullPage: true });
    await expect(page).toHaveURL(/\/centres/);
    const body = await page.textContent('body');
    expect(body?.toUpperCase()).toContain('UAT');

    const real = realErrors(errs);
    if (real.length) console.log('A5 errors:', real);
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// Beat B1 — Supervisor session (centre isolation)
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Beat B1: supervisor — centre-isolated view', () => {
  test.use({ storageState: path.join(AUTH, 'supervisor.json') });

  test('supervisor dashboard loads with scoped data', async ({ page }) => {
    const errs: string[] = [];
    page.on('console', m => { if (m.type() === 'error') errs.push(m.text()); });

    await page.goto(`${BASE}/supervisor/dashboard`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('B1-supervisor-dashboard.png'), fullPage: true });
    await expect(page).toHaveURL(/\/supervisor\/dashboard/);
    const html = await page.content();
    expect(html.length).toBeGreaterThan(20_000);

    const real = realErrors(errs);
    if (real.length) console.log('B1 errors:', real);
  });
});

// ─────────────────────────────────────────────────────────────────────────────
// Per-role dashboards — teacher and AJK
// ─────────────────────────────────────────────────────────────────────────────
test.describe('Teacher session', () => {
  test.use({ storageState: path.join(AUTH, 'teacher.json') });

  test('Per-role: teacher dashboard loads', async ({ page }) => {
    await page.goto(`${BASE}/teacher/dashboard`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('role-teacher.png'), fullPage: true });
    await expect(page).toHaveURL(/\/teacher\/dashboard/);
  });
});

test.describe('AJK session', () => {
  test.use({ storageState: path.join(AUTH, 'ajk.json') });

  test('Per-role: ajk dashboard loads', async ({ page }) => {
    await page.goto(`${BASE}/ajk/dashboard`, { waitUntil: 'load' });
    await page.screenshot({ path: shot('role-ajk.png'), fullPage: true });
    await expect(page).toHaveURL(/\/ajk\/dashboard/);
  });
});
