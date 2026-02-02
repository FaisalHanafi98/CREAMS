---
name: planning
description: Enters CREAMS Planning Mode with multi-actor reasoning framework. Use when making product decisions, triaging unfinished features, planning UX changes, or deciding what to build vs delete. Produces structured output before any code is written.
disable-model-invocation: true
---

# CREAMS Planning Mode

Multi-actor reasoning framework. Every product decision is evaluated through 5 perspectives before code is written.

## Actors

| Actor | Authority | Decides |
|-------|-----------|---------|
| **Product Manager** | PRIMARY | What workflows exist, feature priority, what gets deleted |
| **UX Architect** | UI layer | Interaction design, eliminates fake affordances, defines clarity |
| **Laravel Backend Engineer** | Implementation | Only approved features, no speculative endpoints |
| **Frontend / Blade Engineer** | View layer | Aligns UI to UX decisions, removes unfinished action buttons |
| **QA / Stability Engineer** | Quality gate | No route crashes, flags incomplete features explicitly |

## Non-Negotiable Rules

1. **No fake buttons** — If a feature is not implemented, the UI must hide it, disable it, or label it clearly. A button that triggers a 500 is worse than no button.
2. **Planning before coding** — No controller methods written until workflows are approved by Product Manager.
3. **Pride test** — Every screen must answer: "Would I demo this to a hiring panel without embarrassment?"
4. **No vibe coding** — Every UI element must justify its existence with a user need.

## CREAMS System Facts

- **Framework**: Laravel 10 / PHP 8.5
- **Auth**: Session-based (`session('id')`, `session('role')`) + Laravel `auth` middleware
- **Roles**: Admin, Supervisor, Teacher, AJK
- **Centres**: Gombak, Kuantan, Pagoh, Gambang
- **Core Modules**: Trainee, Activity, Staff, IEP, Letters, Centre, Asset
- **Domain**: Malaysian rehabilitation centres (PPDK) under JKM

## Core Workflows (established)

| # | Workflow | Value |
|---|----------|-------|
| 1 | Trainee Lifecycle | Register → Track → Graduate |
| 2 | Activity & Attendance | Schedule → Record → Report |
| 3 | IEP Management | Plan → Goals → Review |
| 4 | Staff Management | Register → Assign → Manage |
| 5 | Letter Generation | Template → Generate → Download PDF |

## Required Output (in this order, no code)

### 1. Core Workflows
What are the top 5 value-delivering workflows? Re-evaluate if system has changed.

### 2. Feature Triage Table
For every unfinished or questionable feature:

| Feature | Decision | Justification |
|---------|----------|---------------|
| ...     | IMPLEMENT / DEFER / DELETE | Why |

Decisions:
- **IMPLEMENT** — Critical path, user-facing, blocks value delivery
- **DEFER** — Useful but not urgent. Hide UI until implemented.
- **DELETE** — Over-engineered, no demand, adds complexity without value. Remove route + UI.

### 3. UX Principles
Rules that govern all future UI decisions for this module.

### 4. Immediate Cleanup Actions
Concrete list of what changes before any new code. No implementation — just decisions.

## Decision Checkpoint
After producing the output above, STOP. Present to the user for approval. Do not proceed to implementation until the user confirms.
