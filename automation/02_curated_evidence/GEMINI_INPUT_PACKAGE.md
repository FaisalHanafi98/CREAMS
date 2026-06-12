# CREAMS — Gemini Input Package

> **Source**: `GEMINI_WORKING_SET.md`, `GEMINI_MASTER_CONTEXT.md`, all execution state files
> **Date**: 31 May 2026
> **Rule**: Defines exact 10-file input set for Gemini remediation planning. Must ensure Gemini can operate WITHOUT re-reading full inventories.

---

## Exact 10-File Gemini Input Set

| Slot | File | Why Included | Reading Order |
|------|------|-------------|---------------|
| 1 | `automation/07_generated/GEMINI_MASTER_CONTEXT.md` | Executive-level project summary — system overview, architecture, deployment state, test baseline, UAT status, security posture, blockers, debt, missing artifacts. Gemini's starting point. | 1 |
| 2 | `automation/02_curated_evidence/CURRENT_SYSTEM_STATE.md` | What works, what's broken, what's unknown. Structured bullets. Immediate actionable picture. | 2 |
| 3 | `automation/02_curated_evidence/CURRENT_BLOCKERS.md` | P0-P2 prioritized blockers with file locations and evidence sources. Tells Gemini what to fix first. | 3 |
| 4 | `automation/02_curated_evidence/CURRENT_SECURITY_STATE.md` | Active exploitable risks, PDPA risks, credential exposure, misconfiguration, pre-deploy gate status. Security remediation planning. | 4 |
| 5 | `automation/02_curated_evidence/CURRENT_UAT_STATE.md` | PHPUnit baseline (359), Playwright status (181/210), live production UAT verdict (FAIL), module pass/fail, demo readiness. | 5 |
| 6 | `automation/02_curated_evidence/PRIORITY_FIX_QUEUE.md` | Single source of truth for what to fix, in order. P0 (8 items), P1 (9 items), P2 (13 items). No explanations. | 6 |
| 7 | `automation/02_curated_evidence/GEMINI_INPUT_PACKAGE.md` | This file — tells Gemini what it has, how to use it, what it must NOT do. | 0 (meta) |
| 8 | [URL: OWASP Top 10 — current guidance] | External security reference. Populate from `GEMINI_URL_MANIFEST_TEMPLATE.md`. | — |
| 9 | [URL: Laravel Security Best Practices] | External Laravel reference. Populate from `GEMINI_URL_MANIFEST_TEMPLATE.md`. | — |
| 10 | [URL: PDPA Malaysia Data Protection Requirements] | External compliance reference. Populate from `GEMINI_URL_MANIFEST_TEMPLATE.md`. | — |

---

## What MUST NOT Be Included

These files are excluded because they duplicate content already captured in the 7 curated state files:

- ❌ `00_inventory/*` (12 files) — all evidence already extracted and restructured into curated state files
- ❌ `04_audits/CRITICAL_FINDINGS_REGISTER.md` — all 20 findings already distributed across CURRENT_BLOCKERS.md, CURRENT_SECURITY_STATE.md, and PRIORITY_FIX_QUEUE.md
- ❌ Full source code files — Gemini does not have access to source code. All code references are file paths only.
- ❌ `docs/Validate/` files — content already summarized in GEMINI_MASTER_CONTEXT.md
- ❌ `docs/audit/` files — key evidence already extracted into CURRENT_UAT_STATE.md and CURRENT_SYSTEM_STATE.md
- ❌ `docs/ai-context/` files — session history already captured in GEMINI_MASTER_CONTEXT.md
- ❌ `automation/07_generated/GEMINI_EXECUTION_BRIEF.md` — content overlaps with CURRENT_SYSTEM_STATE.md + GEMINI_MASTER_CONTEXT.md. Redundant for this input set.
- ❌ `automation/07_generated/GEMINI_WORKING_SET.md` — this file supersedes it for this specific planning session

---

## What Gemini Can Do With This Package

With these 7 curated files + 3 URLs, Gemini can:

1. Understand the entire CREAMS project without reading any code
2. Identify every confirmed blocker with file locations
3. Know the security posture, PDPA risks, and pre-deploy gate status
4. Know the UAT state — what passes, what fails, what's unknown
5. Know the exact fix priority (P0→P1→P2)
6. Produce a phased remediation strategy
7. Produce execution batches with verification commands
8. Identify gaps requiring URLs or external standards
9. Flag anything that is UNKNOWN and requires code verification

---

## What Gemini CANNOT Do (and Must Not Attempt)

- Read source code — Gemini does not have access. Must work from file paths and descriptions only.
- Verify whether SoftDeletes trait is applied — marked UNKNOWN in system state. Requires code check.
- Verify whether real_data_backup.json is git-tracked — marked UNKNOWN. Requires `git ls-files`.
- Verify whether hardcoded passwords were ever deployed — marked UNKNOWN. Requires Lightsail check.
- Know the nature of 14 uncommitted files — marked UNKNOWN. Requires `git diff`.
- Change any file — Gemini is a planner, not an executor.
- Propose code changes with line numbers — works from file paths, not file contents.

---

## How Gemini Should Use This Package

1. **Read slots 1-6 in order** (GEMINI_MASTER_CONTEXT → CURRENT_SYSTEM_STATE → CURRENT_BLOCKERS → CURRENT_SECURITY_STATE → CURRENT_UAT_STATE → PRIORITY_FIX_QUEUE)
2. **Load 3 URL slots** from GEMINI_URL_MANIFEST_TEMPLATE.md
3. **Use MASTER_REMEDIATION_PROMPT.md** as the output framework — produce 10-section remediation strategy
4. **Flag all UNKNOWN items** as requiring code verification before any action
5. **Do not propose actions on UNKNOWN items** — only flag them for investigation
6. **Prioritize P0 items first** — they block deployment. P1 items second. P2 items last.
7. **Respect the deployment freeze** — no deployment-related actions without explicit approval

---

## Quick Context (For Gemini)

- **Project**: CREAMS — Laravel 12.x Malaysian rehab centre management system
- **Auth**: Custom session-based (POST /auth/check). NOT Breeze/Sanctum.
- **Roles**: Admin, Supervisor, Teacher, AJK (4 active). Trainee/Parent planned.
- **PDPA Isolation**: 25 CentreScoped models. 2 exceptions.
- **Tests**: 359 PHPUnit (floor), 181/210 Playwright (86.2%)
- **UAT**: FAIL — 2 persistent blockers (logout, trainee creation)
- **Deployment**: FROZEN — on hold per CREAMS_SESSION_CURRENT.md
- **Pre-deploy gate**: NOT CLEARED — 1 RED, 2 YELLOW
- **Critical risks**: 4 CRITICAL PDPA/security findings on disk
- **Goal**: Produce phased remediation strategy. Do NOT execute. Do NOT deploy.
