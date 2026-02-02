# CREAMS Skills Registry

Master index of all skills available in this project. Two categories: **Local Skills** (project-specific, defined here) and **Plugin Skills** (installed via obra/superpowers marketplace plugin).

---

## Local Skills (CREAMS Project)

Defined in `.claude/skills/`. Invoked with `/creams-*`. Each encodes a workflow pattern proven in production on this codebase.

| Skill | Invoke | What It Does | When to Use |
|-------|--------|--------------|-------------|
| `route-audit` | `/route-audit` | Full audit of web.php routes vs controller methods | After route/controller changes, or when suspecting 500 errors |
| `fix-verify` | `/fix-verify` | Applies a fix with mandatory before/after Playwright screenshots | Any route, controller, or view change that affects page rendering |
| `planning` | `/planning` | Multi-actor planning mode (Product/UX/Backend/Frontend/QA) | Feature decisions, triage, UX changes — produces output before any code |
| `dead-code` | `/dead-code` | Three-pass sweep: unused imports → unreferenced controllers → dead routes | Periodic cleanup, or after removing features |
| `password-reset` | `/password-reset [email] [password]` | Resets staff password via standalone PHP script (avoids tinker escaping bug) | When credentials are unknown or locked out |

### Local Skill Patterns

Each local skill follows one of three design patterns:

- **Audit** (`route-audit`, `dead-code`) — Read-only analysis. Uses `context: fork` + `Explore` agent. Never writes files. Produces a findings table.
- **Gated Action** (`fix-verify`) — Wraps a code change in a verification gate. Screenshot before → edit → screenshot after. Nothing ships without visual proof.
- **Structured Decision** (`planning`) — Multi-actor reasoning framework. Produces structured output and STOPS for user approval before any implementation begins.

---

## Plugin Skills (obra/superpowers)

Installed via Claude Code Plugin Marketplace. These provide the **development workflow engine** — the ordered sequence of steps that govern how features move from idea to shipped code.

### Installation

```
/plugin marketplace add obra/superpowers-marketplace
/plugin install superpowers@superpowers-marketplace
```

Verify with `/help` — should show `/superpowers:*` commands.

### The Mandatory Workflow (execute in order)

Every feature follows this pipeline. These are not optional suggestions — they are the process.

| Step | Skill | Activates When | What It Does |
|------|-------|----------------|--------------|
| 1 | `brainstorming` | Before writing any code | Refines rough ideas through Socratic questions. Explores alternatives. Presents design in sections for validation. Saves design document. |
| 2 | `using-git-worktrees` | After design approval | Creates isolated workspace on a new branch. Runs project setup. Verifies clean test baseline before work begins. |
| 3 | `writing-plans` | With approved design | Breaks work into bite-sized tasks (2–5 minutes each). Every task specifies: exact file paths, complete code, verification steps. |
| 4 | `subagent-driven-development` | With approved plan | Dispatches a fresh subagent per task. Two-stage review after each: (1) spec compliance, (2) code quality. Fast iteration. |
| 5 | `test-driven-development` | During implementation | Enforces RED → GREEN → REFACTOR. Write failing test first, watch it fail, write minimal code, watch it pass, commit. Deletes code written before tests. |
| 6 | `requesting-code-review` | Between tasks | Reviews completed work against the plan. Reports issues by severity. Critical issues block progress. |
| 7 | `finishing-a-development-branch` | When all tasks complete | Verifies tests pass. Presents options: merge / PR / keep / discard. Cleans up worktree. |

### Full Skills Library

#### Testing
| Skill | Purpose |
|-------|---------|
| `test-driven-development` | RED-GREEN-REFACTOR cycle. Includes testing anti-patterns reference to avoid common traps. |

#### Debugging
| Skill | Purpose |
|-------|---------|
| `systematic-debugging` | 4-phase root cause process. Includes root-cause-tracing, defense-in-depth, and condition-based-waiting techniques. |
| `verification-before-completion` | Ensures a fix is actually working before declaring it done. Never assume — verify. |

#### Collaboration
| Skill | Purpose |
|-------|---------|
| `brainstorming` | Socratic design refinement. Questions before answers. |
| `writing-plans` | Detailed implementation plans broken into 2–5 minute tasks. |
| `executing-plans` | Batch execution with human checkpoints between batches. |
| `dispatching-parallel-agents` | Concurrent subagent workflows for independent tasks. |
| `requesting-code-review` | Pre-review checklist. Structured severity-based reporting. |
| `receiving-code-review` | How to respond to and incorporate review feedback. |
| `using-git-worktrees` | Parallel development branches in isolated workspaces. |
| `finishing-a-development-branch` | Merge/PR decision workflow with cleanup. |
| `subagent-driven-development` | Fast iteration with two-stage review (spec compliance → code quality). |

#### Meta
| Skill | Purpose |
|-------|---------|
| `writing-skills` | How to create new skills following best practices. Includes testing methodology for skills. |
| `using-superpowers` | Introduction to the skills system. Start here if unfamiliar. |

### Superpowers Philosophy
| Principle | Meaning |
|-----------|---------|
| Test-Driven Development | Write tests first, always. No exceptions. |
| Systematic over ad-hoc | Process over guessing. Follow the workflow. |
| Complexity reduction | Simplicity as primary goal. If it's complex, it's wrong. |
| Evidence over claims | Verify before declaring success. Screenshots, test results, not assumptions. |

---

## How Local and Plugin Skills Interact

| Scenario | Which Skills | Order |
|----------|--------------|-------|
| New feature in CREAMS | `/planning` → superpowers workflow (brainstorm → plan → implement) | Planning first, then execution |
| Suspected broken routes | `/route-audit` → `/fix-verify` per broken route | Audit first, then fix each one |
| Periodic cleanup | `/dead-code` standalone | Independent sweep |
| Locked out of system | `/password-reset` standalone | Independent utility |
| Code review before merge | `requesting-code-review` (superpowers) | Part of standard workflow |

---

*Registry version: 1.0*
*Last updated: 2026-02-02*
*Local skills: 5 | Plugin skills: 14 (obra/superpowers)*
