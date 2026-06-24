---
description: Resume a CREAMS project session by reading governance, context, git state, DB state, and test baselines.
---

Run the CREAMS resume protocol from `.opencode/skills/creams-resume/SKILL.md`.

Read, in order:
1. `CLAUDE.md`
2. `docs/ai-context/README.md`
3. `docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md`
4. `docs/ai-context/01_CURRENT_STATUS.md`
5. `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md`
6. `docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md`
7. `docs/ai-context/03_BUG_HISTORY/failed_attempts.md`
8. Latest file under `docs/ai-context/02_SESSION_HISTORY/`
9. Latest file under `.memsearch/memory/`

Then run:
- `git status`
- `git log --oneline -15`
- `git branch --show-current`
- `php artisan migrate:status`
- `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`

Output the Resume Report in the exact format from the skill, end with "Context reconstructed. Ready to continue — confirm to proceed.", and wait for the user.
