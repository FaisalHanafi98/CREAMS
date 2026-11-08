const assert = require('node:assert/strict');
const fs = require('node:fs');
const { chromium } = require('playwright');

const origin = 'http://127.0.0.1:18010';
const secret = process.env.CREAMS_SMOKE_SECRET;
const fixturePath = process.argv[2];
const evidencePath = process.argv[3];
if (process.env.APP_ENV !== 'testing' || process.env.DB_PORT !== '13308'
    || process.env.APP_URL !== origin || !secret || !fixturePath || !evidencePath) {
    throw new Error('The owned recovery fixture is required.');
}
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
const cases = [];
const outcomes = [];

function check(id, description, run, viewport = { width: 1280, height: 800 }) {
    cases.push({ id, description, run, viewport });
}

function safeError(error) {
    return String(error?.message ?? error).replaceAll(secret, '[fixture secret]').split('\n').slice(0, 6).join('\n');
}

async function open(page, pathname, expected = 200) {
    const response = await page.goto(origin + pathname, { waitUntil: 'domcontentloaded' });
    assert.equal(response?.status(), expected, `${pathname} response status`);
    return response;
}

async function textVisible(page, text) {
    await page.getByText(text, { exact: true }).first().waitFor({ state: 'visible' });
}

async function login(page, role, account = role) {
    await open(page, '/login');
    await page.locator('input[name="identifier"]').fill(`http-${account}@example.test`);
    await page.locator('input[name="password"]').fill(secret);
    await Promise.all([
        page.waitForURL((url) => url.pathname === `/${role}/dashboard`),
        page.locator('#loginButton').click(),
    ]);
    await page.locator('body').waitFor();
    const response = await page.request.get(`${origin}/api/dashboard-data`);
    assert.equal(response.status(), 200, `${role} session can load its dashboard API`);
    assert.equal((await response.json()).data.user.role, role);
}

async function submitMark(page, status) {
    return page.evaluate(async ({ session, trainee, status }) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!token) throw new Error('Missing CSRF token on authenticated page.');
        const response = await fetch(`/centre/attendance/session/${session}/store`, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ attendance: [{ trainee_id: trainee, status }] }),
        });
        return response.status;
    }, { session: fixture.session, trainee: fixture.trainee, status });
}

for (const role of ['admin', 'supervisor', 'teacher', 'ajk']) {
    check(`C01-login-${role}`, `Sign in as ${role}, sign out, and reopen a protected page`, async (page) => {
        await login(page, role);
        await Promise.all([
            page.waitForURL((url) => ['/login', '/auth/login', '/'].includes(url.pathname)),
            page.locator('button.logout-btn').click(),
        ]);
        await open(page, `/${role}/dashboard`);
        assert.ok(['/login', '/auth/login'].includes(new URL(page.url()).pathname), 'Protected page must redirect to login after logout.');
    });
}

check('C04-centre-read', 'Read recent activities in the assigned centre and deny another centre', async (page) => {
    await login(page, 'teacher');
    await open(page, '/centres/01');
    await textVisible(page, 'Synthetic HTTP Activity');
    assert.equal(await page.getByText('Synthetic Foreign Browser Activity', { exact: true }).count(), 0);
    await open(page, '/centres/02');
    assert.notEqual(new URL(page.url()).pathname, '/centres/02');
    assert.equal(await page.getByText('Synthetic Foreign Browser Centre', { exact: true }).count(), 0);
});

check('C05-category-navigation', 'Navigate a populated category, an empty category and an invalid slug', async (page) => {
    await login(page, 'teacher');
    await open(page, '/activities/categories');
    const category = page.locator('.category-card').filter({ hasText: 'Learning Support' });
    await category.getByRole('link', { name: /View Activity/ }).click();
    await page.waitForURL('**/activities/categories/learning-support');
    await textVisible(page, 'Synthetic HTTP Activity');
    await textVisible(page, 'Synthetic browser programme description');
    assert.equal(await page.getByText('Synthetic Foreign Browser Activity', { exact: true }).count(), 0);
    await open(page, '/activities/categories/speech-therapy');
    await textVisible(page, 'There are currently no activities in the Speech Therapy category.');
    await open(page, '/activities/categories/not-a-category', 404);
});

check('C08-attendance-mark', 'Open attendance from the dashboard, save a late mark and notes, then reload', async (page) => {
    await login(page, 'teacher');
    await open(page, `/centre/attendance?date=${fixture.date}`);
    await textVisible(page, 'Synthetic HTTP Activity');
    const markLink = page.locator(`a[href$="/centre/attendance/session/${fixture.session}/mark"]`);
    assert.equal(await markLink.count(), 1, `Expected a marking link on the session card: ${await page.locator('.session-card').first().innerText()}`);
    const accessibleLink = page.getByRole('link', { name: 'Mark Attendance', exact: true });
    assert.equal(await accessibleLink.count(), 1, `Expected the exact accessible marking label: ${await markLink.ariaSnapshot()}`);
    await accessibleLink.click();
    await page.waitForURL(`**/centre/attendance/session/${fixture.session}/mark`);
    const status = page.getByLabel('Attendance for Synthetic Browser Learner');
    assert.equal(await status.inputValue(), '', 'Fresh attendance is unmarked.');
    await status.selectOption('late');
    await page.locator('#session_notes').fill('Synthetic browser attendance note');
    await Promise.all([
        page.waitForURL((url) => url.pathname === '/centre/attendance'),
        page.locator('#submitBtn').click(),
    ]);
    await open(page, `/centre/attendance/session/${fixture.session}/mark`);
    assert.equal(await page.getByLabel('Attendance for Synthetic Browser Learner').inputValue(), 'late');
    assert.equal(await page.locator('#session_notes').inputValue(), 'Synthetic browser attendance note');
});

check('C08-attendance-unassigned', 'Deny the same-centre teacher who is not assigned', async (page) => {
    await login(page, 'teacher', 'unassigned');
    assert.equal(await submitMark(page, 'present'), 403);
    await open(page, `/centre/attendance/session/${fixture.session}/mark`, 403);
});

check('C08-attendance-foreign', 'Deny a supervisor from another centre', async (page) => {
    await login(page, 'supervisor', 'foreign-supervisor');
    assert.equal(await submitMark(page, 'present'), 404);
    await open(page, `/centre/attendance/session/${fixture.session}/mark`, 404);
});

check('C08-attendance-ajk', 'Deny attendance dashboard and marking for AJK', async (page) => {
    await login(page, 'ajk');
    assert.equal(await submitMark(page, 'present'), 403);
    await open(page, '/centre/attendance', 403);
    await open(page, `/centre/attendance/session/${fixture.session}/mark`, 403);
});

check('C08-attendance-invalid', 'Reject an invalid status without changing the saved mark', async (page) => {
    await login(page, 'teacher');
    const path = `/centre/attendance/session/${fixture.session}/mark`;
    await open(page, path);
    const status = page.getByLabel('Attendance for Synthetic Browser Learner');
    const before = await status.inputValue();
    assert.equal(await submitMark(page, 'invalid'), 422);
    await open(page, path);
    assert.equal(await status.inputValue(), before);
});

async function applyReportDay(page, day = fixture.report_date) {
    await page.getByLabel('Start date', { exact: true }).fill(day);
    await page.getByLabel('End date', { exact: true }).fill(day);
    await Promise.all([
        page.waitForURL((url) => url.searchParams.get('start_date') === day),
        page.getByRole('button', { name: 'Apply filters', exact: true }).click(),
    ]);
    await page.getByRole('heading', { name: 'Recorded attendance', exact: true }).waitFor();
}

async function legacyExport(page, centreId) {
    return page.evaluate(async ({ centreId, day }) => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const response = await fetch('/activity-attendance/export', {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify({ centre_id: centreId, date_from: day, date_to: day, format: 'excel' }),
        });
        return { status: response.status, contentType: response.headers.get('content-type'), body: await response.text() };
    }, { centreId, day: fixture.report_date });
}

check('C08-report-supervisor', 'Filter own-centre attendance and download CSV, PDF and the legacy export', async (page, recordDownload) => {
    await login(page, 'supervisor');
    await open(page, '/centre/attendance');
    await page.getByRole('link', { name: 'View attendance report', exact: true }).click();
    await page.waitForURL((url) => url.pathname === '/centre/attendance/analytics');
    await applyReportDay(page);
    const table = page.getByRole('table', { name: 'Recorded attendance marks' });
    await table.getByText('Synthetic Browser Learner', { exact: false }).waitFor({ state: 'visible' });
    assert.equal(await table.getByText('Synthetic Foreign Browser Learner', { exact: false }).count(), 0);

    for (const [format, label] of [['csv', 'Download CSV'], ['pdf', 'Download PDF']]) {
        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.getByRole('link', { name: label, exact: true }).click(),
        ]);
        assert.ok(download.suggestedFilename().endsWith(`.${format}`), `${format} download filename`);
        const contents = fs.readFileSync(await download.path());
        if (format === 'csv') {
            assert.ok(contents.toString('utf8').includes('Synthetic Browser Learner'));
            assert.ok(!contents.toString('utf8').includes('Synthetic Foreign Browser Learner'));
        } else {
            assert.equal(contents.subarray(0, 5).toString('ascii'), '%PDF-');
            assert.ok(contents.length > 512);
        }
        recordDownload(download);
    }

    const legacy = await legacyExport(page, '01');
    assert.equal(legacy.status, 200);
    assert.match(legacy.contentType, /^text\/csv/);
    assert.ok(legacy.body.includes('Synthetic Browser Learner'));
    assert.ok(!legacy.body.includes('Synthetic Foreign Browser Learner'));
    assert.equal((await legacyExport(page, '02')).status, 403);
    await open(page, '/centre/attendance/export?centre_id=02', 403);
});

check('C08-report-admin', 'Select another centre as Admin without mixing its report with the first', async (page) => {
    await login(page, 'admin');
    await open(page, '/centre/attendance/analytics');
    await page.getByLabel('Centre', { exact: true }).selectOption('02');
    await applyReportDay(page);
    const table = page.getByRole('table', { name: 'Recorded attendance marks' });
    await table.getByText('Synthetic Foreign Browser Learner', { exact: false }).waitFor({ state: 'visible' });
    assert.equal(await table.getByText('Synthetic Browser Learner', { exact: false }).count(), 0);
});

for (const role of ['teacher', 'ajk']) {
    check(`C08-report-denied-${role}`, `Deny centre reports and both exports for ${role}`, async (page) => {
        await login(page, role);
        assert.equal((await legacyExport(page, '01')).status, 403);
        await open(page, '/centre/attendance/analytics', 403);
        await open(page, '/centre/attendance/export?centre_id=01', 403);
    });
}

check('C08-report-narrow', 'Filter recorded marks with the keyboard on a narrow viewport', async (page) => {
    await login(page, 'supervisor');
    await open(page, '/centre/attendance/analytics');
    const start = page.getByLabel('Start date', { exact: true });
    await start.focus();
    assert.equal(await start.evaluate((element) => element === document.activeElement), true);
    let focused = 'report-start';
    for (let step = 0; step < 7 && focused === 'report-start'; step++) {
        await page.keyboard.press('Tab');
        focused = await page.evaluate(() => document.activeElement?.id);
    }
    assert.equal(focused, 'report-end');
    await applyReportDay(page);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1), 'Page should not overflow horizontally.');
}, { width: 390, height: 844 });

check('C08-report-empty-invalid', 'Distinguish an empty period from an invalid range and preserve the inputs', async (page) => {
    await login(page, 'supervisor');
    await open(page, '/centre/attendance/analytics');
    const previous = new Date(`${fixture.report_date}T00:00:00Z`);
    previous.setUTCDate(previous.getUTCDate() - 1);
    const emptyDay = previous.toISOString().slice(0, 10);
    await applyReportDay(page, emptyDay);
    await textVisible(page, 'No recorded attendance in this period.');

    await page.getByLabel('Start date', { exact: true }).fill(fixture.report_date);
    await Promise.all([
        page.waitForResponse((response) => {
            const url = new URL(response.url());
            return url.pathname === '/centre/attendance/analytics' && url.searchParams.get('start_date') === fixture.report_date
                && url.searchParams.get('end_date') === emptyDay && response.status() === 302;
        }),
        page.getByRole('button', { name: 'Apply filters', exact: true }).click(),
    ]);
    await page.getByRole('alert').getByText(/end date field must be a date after/i).waitFor({ state: 'visible' });
    assert.equal(await page.getByLabel('Start date', { exact: true }).inputValue(), fixture.report_date);
    assert.equal(await page.getByLabel('End date', { exact: true }).inputValue(), emptyDay);
});

async function main() {
    const filter = process.env.CREAMS_BROWSER_CASE_FILTER || '';
    const selected = cases.filter((test) => test.id.startsWith(filter));
    assert.ok(selected.length, 'No browser checks matched the requested prefix.');
    const browser = await chromium.launch({
        headless: true,
        args: ['--disable-background-networking', '--disable-sync', '--disable-component-update'],
    });
    try {
        for (const test of selected) {
            const context = await browser.newContext({ viewport: test.viewport, serviceWorkers: 'block' });
            let externalBlocked = 0;
            await context.route('**/*', (route) => {
                const url = new URL(route.request().url());
                if (url.origin === origin) return route.continue();
                externalBlocked++;
                return route.abort('blockedbyclient');
            });
            const page = await context.newPage();
            page.setDefaultTimeout(12_000);
            page.setDefaultNavigationTimeout(15_000);
            const observations = [];
            const errors = new Set();
            page.on('pageerror', (error) => errors.add(safeError(error)));
            page.on('response', (response) => {
                const url = new URL(response.url());
                const request = response.request();
                if (url.origin !== origin || !['document', 'fetch', 'xhr'].includes(request.resourceType())) return;
                observations.push({ method: request.method(), path: url.pathname, status: response.status() });
                if (response.status() >= 500) errors.add(`${request.method()} ${url.pathname} returned ${response.status()}`);
            });
            const start = Date.now();
            let failure;
            try {
                await test.run(page, (download) => {
                    const url = new URL(download.url());
                    assert.equal(url.origin, origin);
                    observations.push({ method: 'GET', path: url.pathname, status: null, browser_download: true });
                });
            } catch (error) {
                failure = safeError(error);
            } finally {
                await context.close();
            }
            const result = {
                id: test.id, description: test.description, viewport: test.viewport,
                status: failure ? 'FAILED' : errors.size ? 'PARTIAL' : 'PASSED',
                failure, browser_errors: [...errors], observed_requests: observations,
                external_requests_blocked: externalBlocked, duration_ms: Date.now() - start,
            };
            outcomes.push(result);
            process.stdout.write(`${result.status} ${test.id}${failure ? `: ${failure.split('\n')[0]}` : ''}\n`);
        }
        fs.writeFileSync(evidencePath, JSON.stringify({
            browser: `Chromium ${browser.version()}`, fixture_clock: fixture.fixture_clock,
            scope: 'Synthetic browser session checks; non-loopback requests and service workers blocked.',
            filter,
            outcomes,
        }, null, 2));
        process.exitCode = outcomes.every((outcome) => outcome.status === 'PASSED') ? 0 : 1;
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    process.stderr.write(safeError(error) + '\n');
    process.exitCode = 1;
});
