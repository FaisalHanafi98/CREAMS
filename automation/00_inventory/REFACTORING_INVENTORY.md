# CREAMS — Refactoring Inventory

> **Generated**: 2026-05-31
> **Category**: Refactoring
> **Purpose**: Inventory of technical debt, restructuring plans, cleanup efforts, modernization plans, and deferred improvements.

---

## Known Limitations & Debt Register

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/KNOWN_LIMITATIONS_2026-05-04.md` | Register | 14 categories of honest limitations: roles (trainee/parent portal not implemented, MFA missing), auth (no session monitoring, no force-logout), trainees (no bulk import, no soft-delete UI), activities (fixed category enum, no calendar view), attendance (no mobile app, no biometric), letters (no digital signature, no WYSIWYG editor), admin (no web-based config, no backup UI), reporting (no custom builder), notifications (no push/email/SMS), performance (no load testing), operations (no CI/CD pipeline, no automated backups). | High | Critical |
| `docs/Validate/MASTER_PROGRESS_LOG.md` | Phased Plan | Phase 2 (Test Infrastructure): 0% - increase coverage to 70%, add 85+ tests, fix Playwright from 81% to 98%+. Phase 3 (Performance): 0% - reduce trainee creation 26s→<5s, schedule page 19.5s→<3s, eliminate 221 N+1 queries. Phase 4 (Deployment): 0% - CI/CD, monitoring, alerts. | Medium | Medium |
| `docs/Validate/DELTA_REEVAL_REPORT_2026-03-22.md` | Retrofit Plan | Wave 0-4 execution roadmap. Critical fixes: XSS in /letters-archive, session logging PII leak, SoftDeletes trait missing, CentreScope not yet implemented. High: debug routes, password min, GombakDataExtractor, API error sanitizer, hardcoded ICs. Effort estimates per task. | High | Critical |
| `docs/Validate/TEST_STABILIZATION_REPORT.md` | Analysis | Feb 2026: 26 failing Playwright tests classified into 3 categories. Post-submit redirect timeout (14), activity wizard incomplete (12), performance threshold (1). Fix strategies documented. 81%→98% target. | Medium | High |
| `docs/Validate/PERFORMANCE_BASELINE_METHODOLOGY.md` | Methodology | 976 lines. Known issues: trainee creation 26s (email + N+1 enrollments), schedule page 19.5s (221 N+1 queries), dashboard load unknown. Data collection templates included. | Medium | Medium |
| `docs/Validate/PRODUCTION_READINESS_ROADMAP.md` | Roadmap | 2,790 lines. Phases 0-4. Audit → Security → Tests → Performance → Deploy. Phase structure now superseded by re-baselining. | Medium | Low |

## Failed Approaches (Do Not Repeat)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/ai-context/03_BUG_HISTORY/failed_attempts.md` | Register | 8 archived failures: Remi el9 on AL2023 (kernel mismatch), Laravel 13 on PHP 8.2 (breaks L12 ecosystem), side-by-side PHP 8.1+8.3 (shared extension conflicts), git-submodule for openswoole (maintainer overhead), composer global require for deployer (permission escalation), Dockerfile with artisan serve (single-process anti-pattern), certbot --apache (conflicts with nginx), mysql_native_password plugin (MySQL 8.4 removed it). | High | High |

## Documentation Drift (Superseded Files)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/ai-context/08_DOC_ALIGNMENT/stale_or_conflicting_docs.md` | Audit | 6 stale docs: old CLAUDE.md (L10, Breeze, wrong roles), CONSOLIDATED_DOCUMENTATION_INDEX (Jan 2025), CREAMS_CODEBASE_DOCUMENTATION (Dec 2025), PRODUCTION_READINESS_ROADMAP (Feb 2026), 06_Status_Reports/FINAL files, SECURITY_AUDIT_REPORT (falsified claims). 5 doc conflicts. | High | High |
| `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md` | Register | 8 deviations: stale CLAUDE.md claims L10 (actual L12), Breeze+Sanctum (actual custom session), Tailwind (actual Bootstrap5), 6 roles (actual 4 active), "329 tests" (actual 359), "13% coverage" (banned stale metric). | High | Critical |
| `docs/ai-context/08_DOC_ALIGNMENT/docs_vs_code_alignment.md` | Matrix | 15 doc claims vs actual code. Verdicts: ✓VERIFIED, ✗FALSE, ⚠STALE, ✓PARTIAL. Recommended actions per row. | High | Critical |

## Historical Fix Logs (Jul-Aug 2025)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/07_Fixes_and_Audits/STAFF_FIXES_SUMMARY.md` | Historical | Staff pages fixes: Aug 19, 2025. | Medium | Low |
| `docs/07_Fixes_and_Audits/STAFF_EXTENDED_FIXES_SUMMARY.md` | Historical | Extended staff fixes. Aug 19, 2025. | Medium | Low |
| `docs/07_Fixes_and_Audits/DASHBOARD_FIXES_SUMMARY.md` | Historical | Dashboard fixes summary. Aug 19, 2025. | Medium | Low |
| `docs/07_Fixes_and_Audits/LETTER_FIX_SUMMARY.md` | Historical | Letter generator fix summary. | Medium | Low |
| `docs/07_Fixes_and_Audits/LETTER_MODULE_FIX_SUMMARY.md` | Historical | Letter module fix summary. | Medium | Low |
| `docs/07_Fixes_and_Audits/VOLUNTEER_MODULE_FIX_SUMMARY.md` | Historical | Volunteer module fix summary. | Medium | Low |
| `docs/07_Fixes_and_Audits/CONTACT_MODULE_FIX_SUMMARY.md` | Historical | Contact module fix summary. | Medium | Low |
| `docs/07_Fixes_and_Audits/SYSTEM_BUG_FIXES_DOCUMENTATION.md` | Historical | System bug fixes and module repair. Jul 9, 2025. | Medium | Low |
| `docs/07_Fixes_and_Audits/SYNTAX_ERROR_FIXED.md` | Historical | Syntax error fixed — CREAMS topbar. | Medium | Low |
| `docs/07_Fixes_and_Audits/FIXES_SUMMARY_REPORT.md` | Historical | Activity and dashboard fixes summary. | Medium | Low |
| `docs/07_Fixes_and_Audits/SESSION_ENROLLMENT_SUMMARY.md` | Historical | Session enrollment and attendance system implementation. | Medium | Low |
| `docs/07_Fixes_and_Audits/PDF_ISSUE_ANALYSIS.md` | Historical | PDF generation issue analysis and solution. | Medium | Low |
| `docs/07_Fixes_and_Audits/VERIFIED_FIXES_REPORT.md` | Historical | Verified fixes — all issues resolved report. | Medium | Low |
| `docs/07_Fixes_and_Audits/DASHBOARD_PERFORMANCE_VERIFICATION.md` | Historical | Dashboard performance optimization verification report. | Medium | Low |
| `docs/07_Fixes_and_Audits/CENTRE_ACTIVITY_MODULES_AUDIT_REPORT.md` | Historical | Centre and activity modules audit. Jul 9, 2025. | Medium | Low |
| `docs/07_Fixes_and_Audits/fix_log_20250717.md` | Historical | System debugging and repair log. Jul 17, 2025. | Medium | Low |

## Future Planning (Reference Only)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/08_Development_Planning/USER_STORIES.md` | Reference | User stories for future Phase 3 Trainee/Parent portal work. Not authoritative on current implementation. | Low | Medium |
| `docs/08_Development_Planning/ACTIVITY_MODULE_DEVELOPMENT_PLAN.md` | Historical | Activity module development plan from mid-2025. | Low | Low |
| `docs/08_Development_Planning/DATA_GENERATION_SUMMARY.md` | Historical | Activity module data generation patterns. | Low | Low |
| `docs/01_System_Overview/UPDATED_CREAMS_NAMING_STRUCTURE.md` | Proposal | Proposed 7-module migration/seeder consolidation plan. Administrative, not implemented. | Low | Low |
| `docs/archive/PLAN.md` | Historical | 6-phase test infrastructure plan from Feb 2026. Phases 1-4 completed. | Medium | Medium |
| `docs/archive/PHASE2_PROGRESS.md` | Historical | Phase 2 test infrastructure build log. Feb 2026. | Low | Low |

---

## Technical Debt Summary (Synthesized from Evidence)

- **Known limitations**: 14 categories documented in KNOWN_LIMITATIONS_2026-05-04.md. Most "not implemented" or "partially implemented" items are Phase 2-3 deferred.
- **Performance debt**: Trainee creation 26s (target <5s). Schedule page 19.5s (target <3s). 221 N+1 queries. Phase 3 deferred.
- **Test debt**: Playwright 181/210 (86.2%). 29 tests need fixes (redirect handling, wizard fields). Phase 2 deferred.
- **Doc debt**: 6 stale docs. 8 deviations. 15 alignment issues. v2.0 rewrite done for user manuals. Validate consolidation in progress.
- **Failed approaches**: 8 archived with explanations. "Do not repeat" log maintained.
- **Architecture debt**: Fat controllers (400-900 lines). Services underutilized. No Policy classes. 49 inline role checks across 12 controllers. No field-level PII encryption. SoftDeletes migration exists but models possibly lack trait.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
