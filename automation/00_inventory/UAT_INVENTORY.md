# CREAMS — UAT Inventory

> **Generated**: 2026-05-31
> **Category**: UAT (User Acceptance Testing)
> **Purpose**: Inventory of all UAT-related files including test cases, execution reports, gap analysis, demo scripts, and live production verification.

---

## Primary UAT Files (docs/UAT FILES/)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/UAT FILES/FINAL_COMPREHENSIVE_UAT_STATUS.md` | Report | 716 lines — most comprehensive Oct 2025 UAT: methodology, per-module analysis, known issues, final verdict "Production Ready" | High | Critical |
| `docs/UAT FILES/FINAL_UAT_REPORT_ALL_TESTS_PASSED.md` | Report | 40/40 automated DB tests passed (100% pass rate) after fixes applied. Claims Production Ready. | High | Critical |
| `docs/UAT FILES/FINAL_STATUS_SUMMARY.md` | Report | Distinguishes DB-level tests (100% pass) from route-level tests (78.1% pass). Explains false-positive route failures. | High | High |
| `docs/UAT FILES/COMPREHENSIVE_UAT_REPORT_2025-10-13_FINAL.md` | Report | 43 planned / 40 executed tests. 14 pass / 26 skipped. 23-min execution. v1.0 FINAL. | High | High |
| `docs/UAT FILES/COMPREHENSIVE_UAT_REPORT_2025-10-13.md` | Report | Earlier automation report: 39 tests, 84.62% pass. Contact form, IC column, volunteer issues documented. | High | High |
| `docs/UAT FILES/UAT_REALITY_CHECK_REPORT.md` | Analysis | Reconciles automated UAT "failures" with manual verification — concludes most automated failures were false positives due to naming conventions. | High | Critical |
| `docs/UAT FILES/UAT_FINAL_EXECUTIVE_SUMMARY.md` | Summary | Declares "Production Ready for UAT" with 100% of 39 analyzed tests fully usable. | High | Critical |
| `docs/UAT FILES/GAP_ANALYSIS_REPORT.md` | Analysis | 64 UAT test cases vs implementation: 76.6% fully implemented, 21.9% not implemented. Module-by-module breakdown. | High | High |
| `docs/UAT FILES/CHRONOLOGICAL_UAT_TEST_ORDER.md` | Test Cases | Master 56+ test cases in 7 chronological phases: Public → Auth → Dashboards → Common → Core Business → Admin → System. | High | High |
| `docs/UAT FILES/UAT_REORGANIZATION_SUMMARY.md` | Organization | Documents reorganization from alphabetical to chronological user-journey order (13 phases). Date: 2025-10-13. | High | Medium |
| `docs/UAT FILES/DETAILED_VERIFICATION_REPORT_2025-10-13_21-52-21.md` | Report | Raw per-test verification: 53 tests, 10 pass, 43 issues — detailed route/controller/view checking output. | High | High |
| `docs/UAT FILES/CREAMS_UAT_DETAILED_TEST_CASES.csv` | Test Cases | 70+ line CSV inventory with test IDs, priorities, detailed steps, expected results, module assignments. | High | High |
| `docs/UAT FILES/CREAMS_UAT_CHRONOLOGICAL_ORDER.csv` | Test Cases | Chronological execution order CSV derived from the master test case list. | High | Medium |
| `docs/UAT FILES/DEMO_SCRIPT_2026-05-03.md` | Script | 45-min live demo for PPDK staff + IIUM committee: pre-demo checklist, 13 beats, backup plans, anticipated Q&A. | High | Critical |
| `docs/UAT FILES/TESTING_CREDENTIALS.md` | Reference | Test credentials for all 4 roles + 3 seeding methods (migrate:fresh --seed, TestingGuideDataSeeder, standalone). | High | High |
| `docs/UAT FILES/UAT_EXECUTION_GUIDE.md` | Guide | 75 test cases across 11 modules. CSV import instructions. Comprehensive procedures. | Medium | Medium |
| `docs/UAT FILES/ENTERPRISE_SPECIALIZED_UAT_MASTER_GUIDE.md` | Guide | Enterprise-level suites: auth/security (NIST, OWASP, ISO 27001), dashboard analytics, RBAC, performance testing. | Medium | Medium |
| `docs/UAT FILES/DETAILED_UAT_EXECUTION_TEMPLATE.md` | Template | Enhanced CSV template with detailed test steps, validation points, browser requirements, edge cases. | Medium | Low |
| `docs/UAT FILES/NEXT_STEPS_ACTION_PLAN.md` | Plan | Post-verification: 3 options (Deploy Now, Polish First, Comprehensive Testing) with pros/cons. Date: 2025-10-13. | Medium | Medium |
| `docs/UAT FILES/FRESH_SEED_SUMMARY.md` | Summary | Post migrate:fresh --seed: 5 centres, 43 users, 113 trainees, test credentials documented. | Medium | Medium |
| `docs/UAT FILES/README_UAT_ORGANIZATION.md` | Index | Directory-purpose doc listing all UAT files and their roles (primary, supporting, logs). | Medium | Low |

## UAT Evidence in Validate Set

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/creams_detailed_uat.md` | Reference | 66KB detail: user journeys, test flows per role, pre-UAT checklist, expected outcomes. | High | High |
| `docs/Validate/PRE_UAT_MANUAL_TESTING_TRACKER.md` | Tracker | 78KB pre-UAT manual test tracker with pass/fail status per feature across all modules. | High | High |

## Live Production UAT (docs/audit/ — May 2026)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/UAT_BLOCKERS_2026-04-30.md` | Blocker Register | Sprint Day 2: 629 routes verified, dual-URL system documented, CentreScope verified. P0-P3 severity classification. | High | Critical |
| `docs/audit/DEMO_DRYRUN_REPORT_2026-05-03.md` | Dry-Run | HTTP-level demo walkthrough: all pages return 200, RBAC enforced, centre isolation verified. Demo ready. | High | High |
| `docs/audit/DEMO_DRYRUN_LOG_2026-05-03.txt` | Log | Raw curl-based dry-run: all role logins succeed, every demo page returns 200 with populated HTML. | High | Medium |
| `docs/audit/live_mutation_smoke_2026-05-15.md` | Production Test | Production mutation test on pdk-creams.org: synthetic records created, all 4 roles authenticated, CRUD operations verified. | High | Critical |
| `docs/audit/live_functional_uat_readiness_2026-05-16.md` | Readiness Test | pdk-creams.org readiness retest: FAIL. Logout doesn't terminate sessions. Trainee create stuck. Asset 500 fixed. | High | Critical |
| `docs/audit/live_invasive_uat_2026-05-16.md` | Invasive Test | Full invasive UAT on pdk-creams.org: all roles, all modules, synthetic data. RBAC + form behavior recorded. | High | Critical |
| `docs/audit/live_invasive_uat_rerun_2026-05-16_0905.md` | Rerun | Rerun after patches: public surface online, but authenticated UAT blocked (all test accounts redirect to /auth/login). P0 access blocker. | High | Critical |
| `docs/audit/live_invasive_uat_admin_credential_rerun_2026-05-16.md` | Rerun | Admin credential retest: all form variants redirect to login. Authenticated UAT blocked. Conclusion: "Site not safe for stakeholder demo." | High | Critical |
| `docs/audit/live_smoke_claim_verification_2026-05-16.md` | Verification | Claim verification: asset 500 fixed (confirmed), RBAC confirmed, template links gone. Logout + trainee-create + weather still fail = FAIL. | High | Critical |
| `docs/audit/live_admin_uat_lakshmi_credential_2026-05-16.md` | Admin Test | Lakshmi credential works: session created but post-login redirect is unexpectedly /auth/login then allows /admin/dashboard. | High | Critical |
| `docs/audit/live_uat_gate_smoke_2026-05-17.md` | Gate Smoke | Latest gate smoke: all 4 roles login OK. Asset detail 200. No 500s. No template links. Logout still fails = FAIL. | High | Critical |
| `docs/audit/full_browser_uat_report_2026-05-18.md` | Browser UAT | Full browser UAT: 4 roles, 6 public pages, 3,558 controls inventoried. FAIL: logout + trainee create broken. | High | Critical |
| `docs/audit/full_browser_uat_retest_2026-05-18_092233Z.md` | Retest | Retest after fixes: same 2 blockers remain (logout, trainee create). 108 screenshots, 109 RBAC probes. FAIL. | High | Critical |
| `docs/audit/full_browser_uat_report_20260518T153020Z.md` | Edge UAT | Edge Chromium run: all credentials fail from clean context ("No account found"). Contact form rejects valid emails. Auth blocked. FAIL. | High | Critical |

## Historical UAT (archive/)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `archive/Delete These Files/uat_execution_output.log` | Log | Oct 2025 UAT automation: 39 tests, 33 pass, 4 fail, 2 skipped. Contacts table missing, volunteer IC column missing. | Medium | Medium |
| `archive/Delete These Files/uat_final_output.log` | Log | Final UAT run output from Oct 2025. | Medium | Medium |
| `archive/prompts/Claude Final Testing.txt` | Notes | Raw manual testing notes: contact, volunteer, auth, dashboards, activities, trainees, assets. Informal QA log. | Low | Medium |
| `archive/current uat progress.txt` | Notes | Raw manual UAT notes: contact, auth (4 roles), dashboards, search, notification. Informal findings. | Low | Medium |

---

## UAT Status Summary (Synthesized from Evidence)

- **Oct 2025 UAT**: 40/40 DB tests passed (100%). 78.1% route-level. "Production Ready for UAT" verdict.
- **Apr 2026 Sprint**: Zero P0/P1 fixes needed. Demo dry-run passed (all endpoints 200). UATSeeder built.
- **May 15-18 2026 Live UAT**: **2 persistent blockers** — logout doesn't terminate sessions, trainee creation broken. All live UAT reports show FAIL. Gate smoke failed. Stakeholder demo blocked.
- **Demo script**: Ready (DEMO_SCRIPT_2026-05-03.md, 13 beats, 45 mins).
- **Test credentials**: 4 role accounts documented (super.admin / supervisor.a1 / teacher.a1 / ajk.a1 @uat.creams.test).

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
