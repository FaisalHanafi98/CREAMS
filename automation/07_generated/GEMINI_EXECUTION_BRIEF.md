# CREAMS — Gemini Execution Brief

> **Purpose**: Briefing document to accompany every future Gemini planning session. Allows a brand-new Gemini session to immediately understand where the project stands without reading the full inventory.
> **Generated**: 2026-05-31
> **Source**: `automation/00_inventory/*` + `automation/04_audits/CRITICAL_FINDINGS_REGISTER.md`

---

## 1. Current Repository State

### What CREAMS Is
A Laravel 12.x (PHP 8.2+, MySQL 8.0+) community-based rehabilitation management system for Malaysian PPDK centres. Custom session auth (NOT Breeze/Sanctum). 4 active roles (Admin, Supervisor, Teacher, AJK). 25 CentreScoped models for multi-centre PDPA isolation. 629 routes. 359 PHPUnit tests. 181/210 Playwright tests.

### Where We Are
**Phase**: Re-baselining / containment. No deployment. No feature work. The project was nearly UAT-ready 5 months before the Apr-May 2026 sprint, lost momentum, and is now being re-stabilized.

### What's Active
- Local development environment (working)
- Fixers branch (working branch, 14 uncommitted files as of May 8)
- Test suite: 359 passing (floor), 0 failures
- CentreScope isolation (25 models scoped, tests passing)
- Rate limiting (9 limiters active)
- Security headers (7 headers deployed)
- 8 user manuals v2.0 (May 2026)

### What's Blocked
- **Deployment**: ON HOLD (gated on code-reality audit + Portfolio co-tenancy)
- **Stakeholder demo**: NOT SAFE (2 persistent UAT blockers)
- **Pre-deploy gate**: NOT CLEARED (2 RED items)
- **Live UAT**: FAIL across all May 15-18 runs (logout + trainee creation)

---

## 2. Current Project Objective

### Immediate (This Sprint)
1. Fix 2 live UAT blockers (logout session termination, trainee creation 500 error)
2. Clear pre-deploy security gate (generate APP_KEY, set LOG_LEVEL=warning)
3. Enable stakeholder demo

### Short-Term (Next 2-4 Weeks)
1. Complete documentation consolidation (Validate + numbered folders)
2. Address 4 CRITICAL PDPA findings (real_data_backup.json, screenshots, hardcoded passwords, worktrees)
3. Stabilize Playwright tests (86.2% → 98% target)
4. Run 4-role code-reality audit per CREAMS_SESSION_CURRENT.md
5. Close Portfolio co-tenancy traffic/cost plan

### Medium-Term (4-8 Weeks)
1. Deploy to Lightsail ($5/mo instance)
2. Phase 2: Test infrastructure (13.4% → 60% coverage target)
3. Phase 3: Performance optimization (26s → <5s trainee creation, 19.5s → <3s schedule page)
4. External security audit
5. Pilot rollout — one centre, real users

---

## 3. What Has Already Been Completed

### Phase 0 — Audit (Feb 2026)
- Module functionality inventory (163 features, 16 modules)
- Database schema documentation (37 tables)
- API endpoint security inventory (231 routes)
- Performance baseline methodology
- Security baseline scan methodology

### Phase 1 — Security Hardening (Feb-Apr 2026)
- Removed debug routes exposing session data
- Implemented rate limiting on all auth endpoints
- Removed IC numbers from API responses
- Deployed 7 security headers
- Fixed session fixation (flush + regenerate on login/logout)
- Added RBAC to 4 sensitive route groups
- Verified centre isolation (CentreScope applied to 25 models)
- Implemented password policy (12+ chars, complexity)
- Fixed XSS vulnerabilities (escapeHtml() function)
- Added CSRF verification tests
- Auth test suite fixed (AuthenticationTest.php rewritten)
- Message + Centre exceptions documented for CentreScope

### Sprint — Documentation Re-baselining (Apr-May 2026)
- WIP register (every dirty/untracked git item classified)
- UAT blocker audit (629 routes verified)
- PDPA compliance scan (clean verdict)
- Git history audit (72 IC patterns identified, deferred)
- 8 user manuals rewritten to v2.0
- Manual audit vs code (12 discrepancies per manual fixed)
- Demo dry-run (all endpoints 200, RBAC enforced)
- Playwright verification (8/8 demo spec, 181/210 full suite)
- UATSeeder built (Faker-only, 3 centres)
- IRLSeeder hard-gated (3-layer enforcement)
- STAGING_SEED_POLICY.md created
- HANDOVER_PACKAGE_2026-05-04.md created
- KNOWN_LIMITATIONS_2026-05-04.md created
- AI context archive built (21 files cataloguing 155+ docs)
- Deviation register (8 classified deviations)
- Docs vs code alignment matrix (15 rows verified)

---

## 4. What Should NOT Be Repeated

From `docs/ai-context/03_BUG_HISTORY/failed_attempts.md`:

| # | Failed Approach | Why It Failed |
|---|----------------|---------------|
| 1 | Remi's PHP 8.1 el9 RPM on Amazon Linux 2023 | Kernel mismatch — AL2023 ships PHP 8.2 |
| 2 | Laravel 13 on PHP 8.2 | L13 requires PHP 8.3+, breaks L12-compatible packages |
| 3 | Side-by-side PHP 8.1 + 8.3 on same server | Shared extension directory conflicts |
| 4 | git-submodule for openswoole | Maintenance overhead too high for single developer |
| 5 | composer global require for deployer | Permission escalation issues on shared hosting |
| 6 | Dockerfile with `php artisan serve` | Single-process anti-pattern — use PHP-FPM + nginx |
| 7 | certbot --apache on nginx server | Conflicts with nginx — use certbot --nginx |
| 8 | mysql_native_password plugin | Removed in MySQL 8.4 — use caching_sha2_password |

From `docs/Validate/CREAMS_SESSION_CURRENT.md` (DO-NOTs):
- Do NOT deploy to Lightsail or anywhere else
- Do NOT follow archived prompts under `docs/archive/prompts/`
- Do NOT re-run the delta re-evaluation
- Do NOT re-apply CentreScope to already-scoped models
- Do NOT re-rewrite AuthenticationTest.php
- Do NOT use "329 tests", "306 tests", "13% coverage" as assumptions
- Do NOT assume roles are Admin/Manager/Staff/Caretaker
- Do NOT assume auth stack is Breeze + Sanctum

---

## 5. Known Audit Findings

### Top 5 Critical (from CF Register)
| # | Finding | Location |
|---|---------|----------|
| CF-01 | Real production data backup on disk | `database/real_data_backup.json` |
| CF-02 | Live UAT screenshots expose real email | `docs/audit/screenshots/` |
| CF-03 | Hardcoded DB passwords in bootstrap script | `scripts/server-init.sh` |
| CF-04 | Two full repo copies in worktrees | `.claude/worktrees/` |
| CF-12 | 2 persistent UAT blockers | Live UAT (May 15-18) |

### Top 5 Missing Artifacts
| # | Artifact | Priority |
|---|----------|----------|
| M16 | PDPA Data Handling SOP | CRITICAL |
| M05 | Disaster Recovery Plan | CRITICAL |
| M11 | Database Backup Script | CRITICAL |
| M02 | Security Standard | HIGH |
| M04 | Deployment Runbook | HIGH |

### Documentation Debt
- 6 stale docs flagged in Validate set
- 8 deviations between docs and actual code
- 15 doc-vs-code alignment issues
- Numbered folders 01, 02, 06, 07, 08 mostly stale (archiving planned)
- Consolidation plan approved but not yet executed

---

## 6. Current Priorities

**Priority 0 (Safety)**:
- Address CF-01 (real_data_backup.json — verify git tracking, add to .gitignore)
- Address CF-03 (hardcoded passwords — rotate if deployed)

**Priority 1 (Demo Readiness)**:
- Fix logout session termination
- Fix trainee creation 500 error
- Re-run full browser UAT

**Priority 2 (Deploy Readiness)**:
- Generate real APP_KEY for production
- Set LOG_LEVEL=warning in .env.production (or strip PII from Log::debug)
- Verify all pre-deploy checklist items green
- Fix 3 deployment blockers (seeder, nginx, certbot)

**Priority 3 (Documentation)**:
- Execute approved consolidation plan (archive ~40 stale files, merge module docs)
- Backfill empty memory checkpoint stubs for May 2026 idle days

---

## 7. Constraints

### Technical
- **Deploy frozen**: No production push until code-reality audit + Portfolio co-tenancy resolved
- **Branch**: Work on Fixers. No force-push. No --no-verify bypass without explicit approval.
- **PHP**: Must stay 8.2+ compatible. No Laravel 13 migration until ecosystem ready.
- **Auth**: Must remain custom session-based. No Breeze/Sanctum migration.

### Compliance
- **PDPA**: No real IC numbers, names, or medical records in seeds, tests, or commits
- **Secrets**: Pre-commit hook active. No bypass. Malaysian NRIC patterns blocked.
- **Seeding**: Staging/UAT = UATSeeder ONLY. IRLSeeder = local ONLY (3-layer gate).

### Process
- **Commit format**: Type(Scope): Sentence case. "Verified that:" checklist. UK English.
- **Test floor**: 359 tests. No regressions below this count.
- **PDPA in docs**: No real data in session checkpoints (.memsearch/memory/).

---

## 8. Context Limitations

### What Gemini Will NOT Have (Unless Explicitly Provided)
- Full source code of any controller, model, or view file
- Actual database contents (schema structure only)
- Playwright test code
- Migration file contents (count and timeline only)
- The 14 uncommitted working-tree files (nature unknown — needs git diff)

### What Gemini WILL Have (From This Package)
- `GEMINI_MASTER_CONTEXT.md` — full executive summary
- `REPOSITORY_KNOWLEDGE_MAP.md` — navigation map of all hubs
- `CRITICAL_FINDINGS_REGISTER.md` — 20 prioritized findings
- `MISSING_ARTIFACTS.md` — 16 prioritized gaps
- Domain-specific inventory files (per session objective)
- Up to 3 external URLs per session

---

## 9. Expected Outputs from Gemini

For each planning session, Gemini should produce:

1. **Gap Analysis**: Identify what is missing or insufficient in the provided evidence
2. **Phased Strategy**: Break work into sequential phases with clear dependencies
3. **Execution Batches**: Group tasks into batches that can be completed in a single session
4. **Risk Assessment**: Identify what could go wrong with each proposed action
5. **Verification Plan**: How to confirm each fix actually resolved the issue
6. **Dependency Map**: What must be done before what, what is parallelizable

### Specific Outputs for Upcoming Sessions

**Security Planning Session**:
- Remediation order for 20 critical findings (immediate → before-deploy → post-delivery)
- Security standard document outline
- Pre-deploy checklist verification strategy

**UAT Stabilisation Session**:
- Root cause analysis for logout session persistence
- Root cause analysis for trainee creation 500 error
- Playwright test fix strategy (29 failing tests → 98% pass rate)
- Re-test plan after fixes

**Refactoring Session**:
- Priority order for technical debt items
- Fat controller decomposition strategy
- CentreScope audit verification plan
- SoftDeletes trait application plan

**Deployment Session**:
- Lightsail bootstrap plan (with credential cleanup)
- Seeder unblocking strategy
- nginx configuration update plan
- SSL certificate issuance strategy
- Backup automation design

---

## 10. Session Checklist (For Every Gemini Session)

Before producing any output, Gemini must verify:

- [ ] Read GEMINI_MASTER_CONTEXT.md (understand the project)
- [ ] Read REPOSITORY_KNOWLEDGE_MAP.md (know where files live)
- [ ] Read CRITICAL_FINDINGS_REGISTER.md (know what's broken)
- [ ] Read this GEMINI_EXECUTION_BRIEF.md (know what's done and what not to repeat)
- [ ] Load domain-specific inventory files for the session objective
- [ ] Reserve 3 slots for URLs (populate from GEMINI_URL_MANIFEST_TEMPLATE.md)
- [ ] Use MASTER_REMEDIATION_PROMPT.md as the output framework
- [ ] Do NOT propose code changes without understanding the codebase constraints
- [ ] Do NOT recommend deploying to production
- [ ] Do NOT recommend changing the auth stack
- [ ] Do NOT recommend changing roles

---

*Generated from 295 catalogued entries. Evidence-based. No speculation. Use with every Gemini planning session.*
