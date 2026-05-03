# CREAMS — Known Limitations

**Date**: 4 May 2026 (sprint Day 6)
**Purpose**: Honest, complete list of what CREAMS does NOT do today, so stakeholders set expectations correctly and future developers know what's outstanding.

---

## How to read this

Items are grouped by area. Each item is labelled:

- **Not implemented** — feature does not exist in code
- **Partially implemented** — exists but has gaps that affect usability
- **By design** — not done deliberately for scope, complexity, or principle reasons
- **Deferred** — could be done but parked until later (with reason)

If an earlier document or older manual claimed any of these features were available, that claim was wrong. The v2.0 manuals (`docs/10_User_Manuals/`) reflect reality.

---

## 1. User roles and access

| Limitation | Status | Notes |
|---|---|---|
| Trainee self-service portal | Not implemented | Schema fields and `role = trainee` exist but no working dashboard route. Phase 3 feature. |
| Parent / Guardian self-service portal | Not implemented | Same as above. Phase 3. |
| Multi-Factor Authentication (TOTP, SMS OTP) | Not implemented | Recommended for any production deployment with real PDPA data. Phase 2 hardening. |
| IP address restrictions / allowlist | Not implemented | Operations-layer concern. Can be added at the Nginx layer post-deployment. |
| Cross-centre staff (one user spanning multiple centres) | By design (not implemented) | Workaround: create duplicate accounts on each centre. Architectural change to support natively. |
| Granular per-action permission grid (beyond the 4 roles) | Not implemented | Current model is role-based. Adding fine-grained permissions is a significant refactor. |
| Self-serve role changes | By design (blocked) | Only admin can change roles. This is intentional security. |

---

## 2. Authentication and sessions

| Limitation | Status | Notes |
|---|---|---|
| Real-time session monitoring dashboard | Not implemented | The `last_accessed_at` column tracks last activity; querying it is manual. |
| Browser fingerprinting | Not implemented | Not currently a security requirement. |
| Session expiry warning UI (countdown before timeout) | Not implemented | Standard Laravel session — silent expiry. Manual workaround: set a shorter `SESSION_LIFETIME` and accept abrupt logouts. |
| Force-logout-other-sessions feature | Not implemented | Each browser maintains its own session cookie independently. |
| Account lockout (vs rate limiting) | By design (not implemented) | Rate limiting (5/min) is the chosen mechanism. Earlier manuals incorrectly described 15-minute lockouts. |

---

## 3. Trainee management

| Limitation | Status | Notes |
|---|---|---|
| Family Communication Portal | Not implemented | Earlier manuals claimed this; it does not exist. |
| Transition & Discharge Planning workflow | Not implemented | Soft-delete with `status = discharged` exists; no formal workflow on top. |
| Multi-step registration wizard with transfer / re-enrolment types | Not implemented | Single-page form only. |
| Trainee photo capture from webcam | Not implemented | Avatar upload via file picker only. |
| Audit log UI for non-admin users | Not implemented | Audit data exists in `trainee_audit_logs` table; query via tinker only. |
| Soft-delete restore UI | Not implemented | Restore via tinker: `Trainee::withTrashed()->find($id)->restore()`. |
| Trainee bulk import (CSV / Excel) | Not implemented | Records added one at a time via the registration form. |
| Nationality field on trainee record | Not implemented | Earlier manuals listed this; the schema has no such column. |
| Linking trainee to an IIUM Student ID | Not implemented | IIUM IDs apply to staff only. Earlier manuals confused this. |

---

## 4. Activities

| Limitation | Status | Notes |
|---|---|---|
| Custom activity categories beyond the 6 enum values | By design (blocked at DB) | The `activities.category` enum is fixed: Autism Spectrum Support, Hearing Impairment, Visual Impairment, Physical Disabilities, Learning Support, Speech Therapy. |
| Activity templates as full UI feature | Partially implemented | `activity_schedule_templates` table exists; UI is incomplete. Manual scheduling is the working path. |
| Bulk session generation across multiple activities | Not implemented | One activity at a time. |
| Activity calendar view (grid / iCal) | Not implemented | List views only. Calendar UI is a phase 3 feature. |
| Recurring session exceptions (skip a specific date) | Partially implemented | Sessions can be deleted individually; no "exception to recurring rule" UI. |
| Activity prerequisites (must complete X before Y) | Partially implemented | `activity_prerequisites` table exists, UI surface is minimal. |

---

## 5. Attendance

| Limitation | Status | Notes |
|---|---|---|
| Mobile-specific attendance app | Not implemented | Web is responsive but no native app. |
| Biometric integration (fingerprint, face) | Not implemented | Earlier manuals claimed this; no code exists. |
| Geofence-based check-in (GPS validation) | Not implemented | Check-in is timestamp-only, no location. |
| Self-service attendance correction by trainees | Not implemented | Staff-only. |
| Bulk attendance import (CSV) | Not implemented | Per-session UI only. |
| Attendance notifications to guardians on absent | Not implemented | Notification framework exists but this rule is not wired. |
| Late check-in threshold configuration UI | Not implemented | Default 15 minutes; change via code constant. |

---

## 6. Letters and documents

| Limitation | Status | Notes |
|---|---|---|
| Digital signature integration (DocuSign etc.) | Not implemented | Earlier manuals claimed this; no integration exists. |
| Automated email distribution of generated letters | Not implemented | Download PDF and email externally. |
| Scheduled / triggered letter generation | Not implemented | Manual generation only. |
| Bulk letter generation | Not implemented | One at a time. |
| Compliance tracking module | Not implemented | Letter logging exists; no formal compliance workflow. |
| WYSIWYG template editor | Not implemented | Templates edited as raw HTML. |
| Letter version history / amendments | Not implemented | Each generation creates a new row. |

---

## 7. System administration

| Limitation | Status | Notes |
|---|---|---|
| Web-based system configuration UI | Not implemented | Settings live in `.env` only. |
| Backup / recovery UI | Not implemented | Operations done via CLI / scripts. |
| System monitoring dashboard | Not implemented | No APM, no real-time metrics page. |
| Integration management (OAuth providers, webhooks) | Not implemented | No infrastructure exists. |
| Compliance dashboard | Not implemented | Audit logs exist in DB; no rollup view. |
| Multi-tier administrator hierarchy (Security Admin, DB Admin, etc.) | By design (single Admin role) | Earlier manuals invented these tiers. |
| Real-time security event alerting | Not implemented | Audit logs are passive. |
| WAF (Web Application Firewall) | Not implemented | A deployment-layer concern, not application-layer. |

---

## 8. Reporting

| Limitation | Status | Notes |
|---|---|---|
| Custom report builder | Not implemented | Reports are predefined. |
| Scheduled report email distribution | Not implemented | Reports are generated on demand. |
| Cross-centre comparative analytics | Partially implemented | Admin can switch contexts; no built-in compare-two-centres view. |
| Excel export with charts | Not implemented | CSV export only. |
| PDF report templates (styled) | Partially implemented | DomPDF generates basic layouts. |

---

## 9. Notifications

| Limitation | Status | Notes |
|---|---|---|
| Push notifications | Not implemented | In-app only. |
| Email notifications | Not implemented | Mail config exists but routing is incomplete for most events. |
| SMS notifications | Not implemented | No gateway integration. |
| User notification preferences | Not implemented | All users get all notifications applicable to their role. |

---

## 10. Performance and scale

| Limitation | Status | Notes |
|---|---|---|
| Verified scale ceiling | Unknown | Not load-tested. Designed for small centres (~50 trainees), not large institutions. |
| Cached dashboard statistics | Partially implemented | Some queries cached, not all. Watch for slow dashboards as data grows. |
| Asset optimisation pipeline (Vite production build) | Partially implemented | Vite config exists, production build path needs verification. |
| Database connection pooling | Not implemented | Standard PHP connection per request. Use a managed DB with built-in pooling at scale. |

---

## 11. Operational

| Limitation | Status | Notes |
|---|---|---|
| Production-ready CI/CD pipeline | Not implemented | Deploy is manual today. |
| Automated database backups | Not implemented | Manual `mysqldump`. |
| Log aggregation (e.g., Loki, ELK) | Not implemented | Logs in `storage/logs/laravel.log` only. |
| Secrets management (Vault, AWS SSM, etc.) | Not implemented | Secrets in `.env` only. |
| Automated dependency vulnerability scanning | Not implemented | `composer audit` available manually. |
| External monitoring / uptime checks | Not implemented | Up to operations team to add. |

---

## 12. Documentation

| Limitation | Status | Notes |
|---|---|---|
| API reference doc | Partially implemented | `docs/03_Technical_Guides/API_REFERENCE.md` exists but predates the v2.0 re-baseline; may have drift. Verify before relying on it. |
| Onboarding guide for new developers | Deferred | `LOCAL_SETUP_GUIDE_2026-05-04.md` is the starting point; deeper onboarding doc would help. |
| ADRs (Architecture Decision Records) | Partially implemented | Some ADRs exist in `docs/`; not consistently maintained. |
| API integration examples | Not implemented | No third-party integrations exist to document yet. |

---

## 13. Historical PDPA exposure

This is not a feature gap — it is a known historical issue:

- **72 IC patterns** exist in git history (across 131 commits on `Fixers`).
- **0 .env files** were ever committed (good).
- **0 real AWS keys / private keys** were committed (good).
- **12,168 files** were ever tracked under `archive/` (now ignored, but historical).

The exposure cannot be removed without a `git filter-branch` / BFG history rewrite. That is disruptive (every existing clone breaks) and is therefore **deferred to post-delivery**.

In the meantime: contain by ensuring `IRLSeeder` (the real-data seeder) is hard-gated to local-only, and any deployment seeds via `UATSeeder`.

See `docs/audit/git_history_audit_2026-05-01.log` for the baseline.

---

## 14. The "almost UAT 5 months ago" gap

CREAMS was nearly UAT-ready 5 months before this sprint. Bugs surfaced, the team paused, and momentum was lost. This sprint (April–May 2026) was the re-baselining and stakeholder-readiness push.

What that gap means for this handover:

- **Documentation drift was significant**: the v1.0 manuals had 60+ inaccuracies fixed in this sprint's v2.0 rewrite.
- **Test metrics were stale**: claims of "329 tests / 13% coverage" were wrong. Real baseline measured this sprint: 359 tests, 520 assertions, 0 failures.
- **Deployment posture was unverified**: no live staging exists. The next phase must rebuild this.

This document lists what is true today. Trust it over older docs.

---

## 15. What this list is not

- **Not a roadmap.** Items here are observations of current state, not commitments to build.
- **Not a defect list.** A defect is when something is broken vs how it should work. This list is what is missing vs what some documentation or stakeholder might assume exists.
- **Not exhaustive.** Edge cases will surface during real UAT. Add them here when they do.

---

*Created: 4 May 2026 — sprint Day 6*
*Maintainer: Faisal Hanafi*
*Companion to: `docs/HANDOVER_PACKAGE_2026-05-04.md`*
