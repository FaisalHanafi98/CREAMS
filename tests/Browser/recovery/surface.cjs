const fs = require('node:fs');
const assert = require('node:assert/strict');

const [routesFile, browserFile, outputFile] = process.argv.slice(2);
assert.ok(routesFile && browserFile && outputFile, 'Supply the runtime routes JSON, browser report and output path.');
const routes = JSON.parse(fs.readFileSync(routesFile, 'utf8').replace(/^\uFEFF/, ''));
const browser = JSON.parse(fs.readFileSync(browserFile, 'utf8').replace(/^\uFEFF/, ''));
assert.ok(Array.isArray(routes) && Array.isArray(browser.outcomes), 'Invalid route or browser evidence.');

const moduleRules = [
    ['C01', /(?:^|\s)(?:auth\/|login\b|logout\b|logout-enhanced\b|forgot-password\b|reset-password\b)|forgotpasswordcontroller|logincontroller|sanctum\/csrf-cookie/],
    ['C18', /volunteer/],
    ['C12', /letter|lettertemplate/],
    ['C11', /messagecontroller|notification|(?:^|[/.])messages(?:[/.]|\s)/],
    ['C07', /staffattendance|staff[-/.]attendance|attendance[-/.]staff/],
    ['C08', /centre[\\/]attendance|activity-attendance|trainee[-/.]attendance|attendancecontroller|(?:^|\s)attendance(?:[/.]|\s)/],
    ['C09', /assetcontroller|assetmaintenance|assetmovement|(?:^|[/.])assets?(?:[/.]|\s)|asset-parents/],
    ['C10', /(?:^|[/.\\])iep|learning-outcome|traineeprogress/],
    ['C15', /userprofilecontroller|(?:^|[/.])profile(?:[/.]|\s)|avatar/],
    ['C02', /staffcontroller|usercontroller|(?:^|[/.])staffs?(?:[/.]|\s)|admin[/.]users/],
    ['C03', /traineecontroller|traineehomecontroller|traineeregistration|traineeprofile|(?:^|[/.])trainees(?:[/.]|\s)/],
    ['C04', /centrecontroller|(?:^|[/.])centres(?:[/.]|\s)/],
    ['C06', /schedule|sessiontemplate|sessioncontroller|activities\/[^ ]*sessions/],
    ['C14', /reports?|exports?/],
    ['C05', /activitycontroller|activitywizardcontroller|enroll|(?:^|[/.])activities(?:[/.]|\s)|rehabilitation/],
    ['C13', /dashboard/],
    ['C16', /search|csrf-token|fallback|settings/],
    ['C17', /(?:^|\s)(?:\/|contact|home|trademark|privacy|about)(?:[/.]|\s)|contactcontroller|homepagecontroller/],
];

function intendedChecks(id) {
    if (id.startsWith('C01-login-')) {
        const role = id.slice('C01-login-'.length);
        return [['GET', 'login', 'render'], ['POST', 'auth/check', 'sign-in'], ['GET', `${role}/dashboard`, 'session and redirect'], ['POST', 'logout', 'sign-out']];
    }
    const mark = 'centre/attendance/session/{sessionId}/mark';
    const store = 'centre/attendance/session/{sessionId}/store';
    return ({
        'C04-centre-read': [['GET', 'centres/{id}', 'assigned and denied centre']],
        'C05-category-navigation': [['GET', 'activities/categories', 'render and navigate'], ['GET', 'activities/categories/{categorySlug}', 'populated, empty and invalid']],
        'C08-attendance-mark': [['GET', 'centre/attendance', 'open marking link'], ['GET', mark, 'read roster and persisted marks'], ['POST', store, 'save and reread']],
        'C08-attendance-unassigned': [['GET', mark, 'denied instructor'], ['POST', store, 'denied instructor']],
        'C08-attendance-foreign': [['GET', mark, 'denied centre'], ['POST', store, 'denied centre']],
        'C08-attendance-ajk': [['GET', 'centre/attendance', 'denied role'], ['GET', mark, 'denied role'], ['POST', store, 'denied role']],
        'C08-attendance-invalid': [['POST', store, 'invalid value preserves saved mark']],
    })[id] ?? [];
}

function pathPattern(uri) {
    if (uri === '/') return /^\/$/;
    return new RegExp('^' + uri.split('/').map((part) => {
        if (/^\{[^}]+\?\}$/.test(part)) return '(?:/[^/]+)?';
        if (/^\{[^}]+\}$/.test(part)) return '/[^/]+';
        return '/' + part.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }).join('') + '/?$');
}

const ledger = routes.map((route) => {
    const middleware = route.middleware ?? [];
    const uri = route.uri;
    const legacy = uri === 'creams/{demo_id}' || uri.startsWith('creams/{demo_id}/');
    const deferred = middleware.some((entry) => /(?:RoleMiddleware|role):(?:parent|trainee)$/i.test(entry));
    const signature = `${uri} ${route.name ?? ''} ${route.action ?? ''} `.toLowerCase();
    const module = moduleRules.find(([, rule]) => rule.test(signature))?.[0] ?? 'UNMAPPED';
    const scope = legacy ? 'LEGACY_DEMO' : deferred ? 'DEFERRED_ROLE' : 'DIRECT';
    const evidence = [];
    if (scope === 'DIRECT') for (const outcome of browser.outcomes) {
        for (const [method, template, check] of intendedChecks(outcome.id)) {
            if (uri !== template || !route.method.split('|').includes(method)) continue;
            const pattern = pathPattern(uri);
            const observed = outcome.observed_requests.some((entry) => entry.method === method && pattern.test(entry.path));
            evidence.push({ case_id: outcome.id, check, result: outcome.status === 'PASSED' && !observed ? 'NOT_OBSERVED' : outcome.status });
        }
    }
    const state = scope !== 'DIRECT' ? scope
        : evidence.some((row) => row.result === 'FAILED') ? 'FAILED_CASE'
        : evidence.some((row) => row.result === 'PARTIAL') ? 'PARTIAL_CASE'
        : evidence.some((row) => row.result === 'PASSED' && !row.check.startsWith('denied')) ? 'ASSERTED_CASES'
        : evidence.some((row) => row.result === 'PASSED') ? 'DENIAL_ONLY' : 'NOT_RUN';
    return { module, scope, state, method: route.method, uri, name: route.name, action: route.action, middleware, evidence };
});

function tally(records, key) {
    return records.reduce((counts, row) => { counts[row[key]] = (counts[row[key]] ?? 0) + 1; return counts; }, {});
}

const summary = { registrations: ledger.length, scopes: tally(ledger, 'scope'), states: tally(ledger, 'state'), browser_cases: tally(browser.outcomes, 'status') };
fs.writeFileSync(outputFile, JSON.stringify({
    summary,
    caveat: 'An asserted case covers only the listed check. Unmapped routes, non-tested variants and unrun functions are not verified. Demo routes are separately accounted for, not silently dropped.',
    routes: ledger,
}, null, 2));
process.stdout.write(JSON.stringify(summary, null, 2) + '\n');
