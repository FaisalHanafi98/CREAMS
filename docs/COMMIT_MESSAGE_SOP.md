# CREAMS — Commit Message SOP

**Document type**: Governance — commit standards
**Scope**: All CREAMS commits on all branches
**Last updated**: 25 April 2026

---

## Required format

```
Type(Scope): Title.

Project: CREAMS
Date: DD Month YYYY
Task:

[Short human title]

[Plain-language explanation of what changed and why.]

Verified that:

1. [specific check]
2. [specific check]
3. [specific check]

[Short explanation of why the change matters.]
```

---

## Full example

```
Chore(Security): Add CentreScope implementation to repository.

Project: CREAMS
Date: 24 April 2026
Task:

Add missing CentreScope class to git

Added the CentreScope class that existing models already reference
for centre-based data isolation. The file existed on disk but was
never committed, meaning a fresh clone would crash on boot with a
class-not-found error across 23 models.

Verified that:

1. The file contains no secrets or hardcoded data.
2. PHP syntax check passes (php -l).
3. The namespace matches App\Models\Scopes.
4. git ls-files confirms the file is now tracked.

A fresh clone of the Fixers branch would fail without this file.
Centre isolation is the primary PDPA boundary in CREAMS.
```

---

## Conventional title

The first line is always a conventional commit title:

```
Type(Scope): Short description in sentence case.
```

- End with a full stop.
- Keep under 72 characters.
- Sentence case: capitalise the type and the first word of the description only.

Types: `Feat`, `Fix`, `Refactor`, `Docs`, `Test`, `Chore`, `Security`

Scope: feature area or module — `Auth`, `Models`, `Ci`, `Seeder`, `Routes`, `Views`, `Middleware`, `Governance`

---

## Writing rules

**Use UK English throughout.**

Write like a real developer explaining a change to a teammate who will read it in six months.

### Avoid

- **Any attribution or credit to Claude, AI assistants, or AI tooling.
  No "Assisted by AI" footers, no Co-Authored-By AI trailers, no AI
  mentions of any kind. Commits are authored by the project owner.**
- Marketing tone or overpolished AI language
- Vague phrases: "enhanced robustness", "improved reliability" — unless you can state what metric changed
- Emojis in technical commit messages
- Excessive bold formatting
- "not only... but also"
- "from X to Y" unless it refers to a real measurable range (e.g. "from 8 to 12 characters")
- Unnecessary em dashes in prose
- Title-case headings inside the commit body
- Vague claims without evidence

### Prefer

- Direct wording: say what the code does, not how impressive it is
- Clear reason: why did this change happen now?
- Specific test evidence: name the test class or artisan filter used
- Risk reduction language: what could go wrong without this change?
- Honest wording if something is incomplete or deferred

---

## The "Verified that" section

Each numbered item must describe a real check you ran. Examples of good items:

```
1. php artisan test --filter=AuthenticationTest passes (12 tests, 0 failures).
2. git diff --cached confirms no real IC numbers in the diff.
3. php -l app/Models/Scopes/CentreScope.php returns no errors.
4. git diff --cached --diff-filter=R shows renames detected, not add+delete.
```

Examples of bad items (do not use):

```
1. Code is correct.
2. Changes look good.
3. Tests pass.
```

---

## What to include in the body

| Include | Omit |
|---|---|
| What the file/function did before | Line-by-line diff narration |
| What it does now and why | File contents verbatim |
| Which tests cover the change | Editor used |
| What risk this reduces | Praise for the change |
| Any known gaps or deferrals | Future plans unrelated to this commit |

---

## Short commits (docs, typos, renames)

For genuinely trivial commits — a typo fix, a rename-only doc move — you may abbreviate. Omit "Verified that" only when there is nothing to verify beyond "file exists at new path".

```
Docs(Archive): Move PLAN.md into docs/archive/prompts/.

Project: CREAMS
Date: 25 April 2026
Task:

Rename only. No content change. Follows the docs/ consolidation
from commit eadc22e.
```

---

## Git template

A `.gitmessage` template is included at the repository root. To activate it locally:

```bash
git config commit.template .gitmessage
```

This modifies local git config only. Do not commit the config change.

---

## Authority

This SOP is governed by the root `../CLAUDE.md` (Section 9: Commit Protocol). In case of conflict, the root SOP wins. This file refines the format for the CREAMS project specifically.
