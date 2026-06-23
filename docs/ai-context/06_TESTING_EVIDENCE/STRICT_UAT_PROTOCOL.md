# Strict UAT / Verification Protocol — No-Inference Mode

> **Status**: Controlling protocol for all UAT, QA, security, and readiness reporting in CREAMS.
> **Origin**: Owner correction, 2026-06-21, after a UAT report stated system-level conclusions
> ("no broken authorization found", "exhaustive", "80/100 ready") that rested on partial evidence.
> **Applies to**: this session, every future session, and every subagent that runs verification work.

## The one rule

**No global claim is permitted unless every relevant unit is either explicitly tested or explicitly
marked untested.** Partial testing yields a partial statement — never a system-wide conclusion.

The failure mode this prevents: *local checks (some routes, some roles, HTTP 200) → extrapolated
system-wide confidence → "management-ready" language the data does not support.*

## Evidence classes (label every claim with exactly one)

| Class | Meaning | Strength |
|---|---|---|
| **EXECUTED** | A real request/action was sent and the response or DB/UI state was observed. | Strongest |
| **CODE-VERIFIED** | The enforcement mechanism (middleware, controller check, scope) was read in source and judged correct, but not run at runtime. | Real but weaker — proves the gate *exists*, not that it *fires* |
| **NOT TESTED** | Neither executed nor inspected. Status is **UNKNOWN**, never "pass". | None |

`UNKNOWN` / `NOT COVERED` / `NOT EXECUTED` / `INSUFFICIENT DATA` are **first-class outputs**. Report them
loudly; do not hide a gap inside a summary.

## Forbidden language (unless 100% coverage is proven and shown)

Do not write: "fully working", "no issues found", "system is secure", "all endpoints verified",
"exhaustive" (when describing *testing* rather than *inventory*), "ready", "stable", "no critical issues".

If tempted to write one, replace it with the coverage denominator instead:
`"<N> of <M> EXECUTED & passed; <K> CODE-VERIFIED; <J> NOT TESTED (UNKNOWN)."`

## Coverage denominators are mandatory

Every conclusion that spans a set (endpoints, screens, roles, error paths) must state `tested / total`.
"RBAC verified across 105 endpoints" is banned; "RBAC: 6/105 EXECUTED, 105/105 CODE-VERIFIED,
99/105 runtime UNKNOWN" is required.

## Per-module reporting format (no exceptions)

```
MODULE: <name>
  Tested routes (EXECUTED):     <list + observed status/result>
  Code-verified routes:         <list + the check that was read>
  Untested routes (UNKNOWN):    <list>
  Roles covered:                <which of Admin/Supervisor/Teacher/AJK/Parent/Trainee/Guest, EXECUTED vs not>
  Error paths covered:          <invalid id / missing session / forbidden / not-found — EXECUTED vs not>
  Failures:                     <route + error + stack/log line>
  Unknowns:                     <explicit list>
```

A module is **VERIFIED** only if ALL routes EXECUTED, ALL applicable roles EXECUTED, and ALL error paths
EXECUTED. Anything less is **PARTIALLY VERIFIED** with the gaps listed.

## Readiness reporting

- A single 0–100 score is **not** a headline — it compresses tested + code-verified + untested into one
  number. If a score is given at all, it must sit *below* the coverage ledger and be labelled
  "DERIVED JUDGEMENT, not a measurement".
- Global readiness defaults to **UNKNOWN** until module coverage is proven. State what *is* demo-able
  (the EXECUTED surface) separately from what is unproven.

## Required closing sections of any report

1. **VERIFIED FACTS ONLY** — EXECUTED observations, each with its evidence pointer (route, status, role,
   file:line, DB id, screenshot, or log line).
2. **CODE-VERIFIED (not run)** — gates read in source but not exercised.
3. **UNKNOWN / NOT COVERED** — explicit list of everything not tested.
4. **FAILURES** — each with reproduction + error.

No interpretation in those four sections — facts only. Judgement, if any, goes in a clearly separated block.
