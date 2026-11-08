# Isolated recovery browser checks

These checks use the installed Playwright/Chromium with real forms, cookies and
browser requests against the owned synthetic CREAMS fixture. They do not load
the legacy `global-setup.ts`, saved auth states or the usual `localhost:8000`
server. No existing uploads are served and non-loopback requests are blocked.

From the Development workspace, use the
[isolated runner](../../../../../workspace/implementation-2026-11-04-creams-recovery/scripts/verify-creams.ps1):

```powershell
& .\workspace\implementation-2026-11-04-creams-recovery\scripts\verify-creams.ps1 -ReuseOwned -BrowserSmoke -EvidenceLabel browser-review-01
```

Use `-ReuseOwned` only for the recovery runner's own data directory. A fresh
owned schema is required after HTTP/browser fixtures. `-BrowserCaseFilter`
selects a case ID prefix; `-TestFilter` separately narrows PHP checks. A filtered
result does not establish the rest of the suite. The harness records owned data
and storage identities before starting, and stops only its own processes.

`smoke.cjs` runs the browser checks, records scoped response paths and redacts the
synthetic login secret. It treats uncaught browser errors as partial results even
when the direct assertions pass. `surface.cjs` takes the runtime `routes.json`,
`browser-smoke.json` and an output path to produce the per-route coverage ledger.
Only explicitly checked and observed operations count. Incidental requests, an
HTTP 200, a passing PHP suite and demo aliases do not establish full browser
acceptance. A case can prove one outcome while leaving other inputs/roles open.

The first full recovery run on 8 November 2026 passed 11 browser cases. It
covered sign-in/sign-out for four roles, centre reads and denial, category
navigation and session attendance save/reload with denied/invalid submissions.
The recorded ledger accounts for 601 route registrations, with 13 direct entries
asserted, 288 direct entries still unrun, 294 demo registrations and six
future-role registrations. Extend the named browser cases and their explicit
route mappings as each release capability is repaired. Do not close a module
until its relevant positive, negative, persistence and state cases pass.

Release scope and gaps live in the [acceptance matrix](../../../docs/MODULE_FUNCTIONALITY_INVENTORY.md)
and [decision register](../../../docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md).
Full traces stay in the local recovery evidence directory. This is local
synthetic evidence, with production, original stakeholder UAT, service-worker
behaviour and comprehensive accessibility still separate.
