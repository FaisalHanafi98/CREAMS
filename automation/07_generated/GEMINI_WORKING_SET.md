# CREAMS — Gemini Working Set

> **Purpose**: Recommend which files to load into Gemini first, respecting a 10-file limit with 3 slots reserved for future URLs/articles/repositories.
> **Generated**: 2026-05-31
> **Source**: `automation/00_inventory/*` + `automation/04_audits/CRITICAL_FINDINGS_REGISTER.md`
> **Rule**: Never exceed 10 files per prompt. Context degrades when overloaded.

---

## Priority 1 — Core Context (Load First, Always)

These 4 files give Gemini everything needed to understand the project at a foundational level:

| # | File | Reason | Value | Expected Use |
|---|------|--------|-------|--------------|
| 1 | `GEMINI_MASTER_CONTEXT.md` | Executive summary of entire project | System overview, architecture, security posture, blockers, debt, missing artifacts | Foundation for all planning sessions |
| 2 | `REPOSITORY_KNOWLEDGE_MAP.md` | Navigation map showing all knowledge hubs and their relationships | Visual hierarchy of where everything lives | Quick lookup — "where is X?" during any session |
| 3 | `CRITICAL_FINDINGS_REGISTER.md` | 20 security/PDPA/deployment findings with risk levels | Prioritized actionable issues | Basis for security + PDPA remediation planning |
| 4 | `MISSING_ARTIFACTS.md` | 16 gaps vs mature Laravel project baseline | What doesn't exist yet — prioritized by criticality | Basis for standards creation + infrastructure planning |

**Reserve**: 6 remaining slots for domain-specific files + future URLs.

---

## Priority 2 — Domain-Specific (Load Per Use Case)

Load these files depending on the session objective. **Never load more than 7 files total in any prompt** (keeping 3 for URLs).

| # | File | Reason | Value | Expected Use |
|---|------|--------|-------|--------------|
| 5 | `PROJECT_STATE_INVENTORY.md` | Current project condition — what phase, what's blocked, what's done | Prevents wasted effort on completed or frozen work | All sessions — understand where we are before deciding what to do |
| 6 | `SECURITY_INVENTORY.md` | Rate limiters, security headers, CentreScope, auth config, pre-deploy gate status | Complete security posture with evidence paths | Security planning, pre-deploy checklist verification |
| 7 | `ARCHITECTURE_INVENTORY.md` | 54 models, 22 middleware, 9 services, 34 migrations, 3 ADRs, 629 routes | Source-of-truth for system design decisions | Architecture review, CentreScope audit, refactoring planning |
| 8 | `UAT_INVENTORY.md` | 38 UAT files across Oct 2025 and May 2026. Live UAT status: FAIL (2 blockers) | Complete UAT landscape with failure patterns | UAT stabilisation, demo readiness planning |
| 9 | `SMOKE_TEST_INVENTORY.md` | 359 PHPUnit floor. 181/210 Playwright. Live smoke: FAIL. Test infrastructure configs. | Test coverage landscape with gaps | Test infrastructure planning, Playwright stabilisation |
| 10 | `REFACTORING_INVENTORY.md` | Known limitations (14 categories), technical debt, failed approaches, doc drift, historical fix logs | What's broken, what's deferred, what failed before | Refactoring prioritisation, technical debt triage |
| 11 | `DEPLOYMENT_INVENTORY.md` | deploy.sh, Dockerfile, server-init.sh, nginx config, Lightsail footprint, rollback procedure | Complete deployment landscape — scripts, configs, blockers | VPS migration planning, deployment readiness |
| 12 | `CODE_QUALITY_INVENTORY.md` | PHPStan level 5, SonarScanner config, CI pipeline, bug registers, doc alignment matrix | Quality tooling landscape + known issues | Code quality baseline, lint/static analysis planning |
| 13 | `REFERENCES_INVENTORY.md` | AI agent prompts, ADRs, commit SOP, test credentials, route inventory | Templates and standards for development | Onboarding new agents, commit standards enforcement |

---

## Priority 3 — Deep Reference (Load on Demand)

| # | File | Reason | Value | Expected Use |
|---|------|--------|-------|--------------|
| 14 | `INVENTORY_COMPLETION_REPORT.md` | Authority tiers, trust hierarchy, unresolved classification questions | Metadata about the inventories themselves | Validating inventory accuracy, resolving open questions |
| 15 | `GEMINI_URL_MANIFEST_TEMPLATE.md` | Template for future external references | Structure for URLs, articles, repositories | Populating the 3 reserved URL slots |
| 16 | `GEMINI_EXECUTION_BRIEF.md` | Session briefing document | What's completed, what not to repeat, constraints | Every new Gemini session |
| 17 | `MASTER_REMEDIATION_PROMPT.md` | Reusable prompt framework | Prevents hallucinations, ensures structured output | Every remediation planning session |

---

## Recommended Upload Combinations

Respecting the 7-file maximum (10 total minus 3 reserved for URLs):

### Combination A: Security Planning
```
1. GEMINI_MASTER_CONTEXT.md
2. REPOSITORY_KNOWLEDGE_MAP.md
3. CRITICAL_FINDINGS_REGISTER.md
4. SECURITY_INVENTORY.md
5. ARCHITECTURE_INVENTORY.md
6. MASTER_REMEDIATION_PROMPT.md
7. [Reserved for URLs: OWASP, Laravel security docs, etc.]
```

### Combination B: UAT Planning
```
1. GEMINI_MASTER_CONTEXT.md
2. REPOSITORY_KNOWLEDGE_MAP.md
3. PROJECT_STATE_INVENTORY.md
4. UAT_INVENTORY.md
5. SMOKE_TEST_INVENTORY.md
6. MASTER_REMEDIATION_PROMPT.md
7. [Reserved for URLs: Playwright docs, testing standards, etc.]
```

### Combination C: Refactoring Planning
```
1. GEMINI_MASTER_CONTEXT.md
2. REPOSITORY_KNOWLEDGE_MAP.md
3. REFACTORING_INVENTORY.md
4. ARCHITECTURE_INVENTORY.md
5. CODE_QUALITY_INVENTORY.md
6. MASTER_REMEDIATION_PROMPT.md
7. [Reserved for URLs: Laravel upgrade guides, PHPStan docs, etc.]
```

### Combination D: Deployment Planning
```
1. GEMINI_MASTER_CONTEXT.md
2. REPOSITORY_KNOWLEDGE_MAP.md
3. DEPLOYMENT_INVENTORY.md
4. CRITICAL_FINDINGS_REGISTER.md
5. MISSING_ARTIFACTS.md
6. MASTER_REMEDIATION_PROMPT.md
7. [Reserved for URLs: Lightsail docs, VPS guides, etc.]
```

---

## File Size Estimates (for Context Budgeting)

| File | Approx Size |
|------|------------|
| GEMINI_MASTER_CONTEXT.md | ~6 pages |
| REPOSITORY_KNOWLEDGE_MAP.md | ~5 pages |
| CRITICAL_FINDINGS_REGISTER.md | ~4 pages |
| PROJECT_STATE_INVENTORY.md | ~3 pages |
| SECURITY_INVENTORY.md | ~4 pages |
| ARCHITECTURE_INVENTORY.md | ~3 pages |
| UAT_INVENTORY.md | ~3 pages |
| SMOKE_TEST_INVENTORY.md | ~3 pages |
| REFACTORING_INVENTORY.md | ~4 pages |
| DEPLOYMENT_INVENTORY.md | ~4 pages |
| CODE_QUALITY_INVENTORY.md | ~3 pages |
| REFERENCES_INVENTORY.md | ~3 pages |
| MISSING_ARTIFACTS.md | ~3 pages |
| MASTER_REMEDIATION_PROMPT.md | ~3 pages |
| GEMINI_EXECUTION_BRIEF.md | ~3 pages |

**7-file combination total**: ~25-30 pages. **Reserved 3 slots**: URLs/articles (variable).

---

## Rules for Future Sessions

1. P1 files (1-4) are always loaded first in every session.
2. P2 files (5-13) are loaded based on session objective, never all at once.
3. At least 3 slots in every prompt are reserved for URLs, articles, or repository references.
4. If a session requires more than 7 inventory files, split into multiple prompts with clear handoffs.
5. Never load `REFERENCES_INVENTORY.md` and `INVENTORY_COMPLETION_REPORT.md` in the same prompt as other Priority 2 files — they are reference-only.
6. `MASTER_REMEDIATION_PROMPT.md` should accompany every planning prompt to prevent hallucinations.

---

*Generated from existing inventory intelligence. No new findings. No speculation.*
