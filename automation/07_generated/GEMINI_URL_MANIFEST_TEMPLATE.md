# CREAMS — Gemini URL Manifest Template

> **Purpose**: Template for future external references (URLs, articles, repositories, standards) to be loaded into Gemini alongside project context files.
> **Generated**: 2026-05-31
> **Status**: Template only — no actual URLs populated. Fill during remediation planning.
> **Rule**: Each session reserves 3 of its 10-file slots for URLs from this manifest.

---

## AI Agent References

Resources for AI-assisted development practices, prompt engineering, and agent configuration.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: AI Agent References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Continuous Session References

Resources for maintaining long-running AI development sessions, context management, and checkpointing.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Continuous Session References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Refactoring References

Resources for Laravel refactoring patterns, technical debt management, and code modernization.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Refactoring References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: Fat controllers (400-900 lines), 49 inline role checks, services underutilized, no Policy classes, CentreScope rollout verification needed.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Refactoring References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Security References

Resources for OWASP compliance, Laravel security best practices, PDPA/data protection, and vulnerability remediation.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Security References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 20 critical findings registered. OWASP ~66-78%. 2 RED pre-deploy blockers. 4 CRITICAL PDPA findings. 72 IC patterns in git history.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Security References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Security References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Laravel References

Resources for Laravel upgrades, package recommendations, Eloquent patterns, and framework best practices.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Laravel References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: Currently Laravel 12.x, PHP 8.2+. Custom session auth (not Breeze/Sanctum). 54 Eloquent models, 23 CentreScoped. 34 migrations. 9 rate limiters.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Laravel References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## SonarQube References

Resources for static analysis configuration, quality gate setup, and code quality enforcement.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: SonarQube References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: SonarScanner configured (sonar-project.properties). PHPStan level 5 active. No quality gate thresholds documented. No php-cs-fixer/pint config file.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: SonarQube References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## DevOps References

Resources for CI/CD pipelines, Docker optimization, GitHub Actions, and deployment automation.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: DevOps References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: GitHub Actions CI (PHP 8.1 + MySQL 8.0). Deploy is manual SSH. Dockerfile + docker-compose exist. Lightsail $5/mo target. No automated backups.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: DevOps References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Testing References

Resources for PHPUnit patterns, Playwright E2E best practices, UAT methodology, and test infrastructure.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Testing References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 359 PHPUnit tests (floor). 181/210 Playwright (86.2%). 2 UAT blockers (logout, trainee create). 29 Playwright tests need redirect/wizard fixes. Coverage ~15-20%.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Testing References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Deployment References

Resources for Lightsail, VPS configuration, nginx optimization, SSL, DNS, and production hardening.

```markdown
### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Deployment References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: Lightsail $5/mo (512MB RAM, 20GB SSD). Co-tenancy with Portfolio. 3 deployment blockers (seeder, nginx, certbot). server-init.sh has hardcoded passwords (CF-03). No deployment runbook, DR plan, or backup script.

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Deployment References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 

### [Entry Title]
- **Title**: 
- **URL**: 
- **Category**: Deployment References
- **Reason Included**: 
- **Key Lessons**: 
- **Relevance To CREAMS**: 
```

---

## Pending URL Slots (3 Reserved Per Session)

Each Gemini session has 3 reserved slots in its 10-file prompt. Fill these slots from the categories above before starting a planning session. Select URLs relevant to the session objective.

### Security Session URL Slots
```
1. [URL: OWASP Top 10 — current guidance]
2. [URL: Laravel security best practices — encryption, CSRF, rate limiting]
3. [URL: PDPA Malaysia — data protection requirements for healthcare/rehabilitation data]
```

### UAT Session URL Slots
```
1. [URL: Playwright best practices — waitForURL, networkidle, semantic waits]
2. [URL: UAT methodology for web applications — test case design, traceability]
3. [URL: Browser testing — handling redirects, session persistence after logout]
```

### Refactoring Session URL Slots
```
1. [URL: Laravel refactoring patterns — fat controllers, service extraction, Policy classes]
2. [URL: PHPStan level progression — moving from level 5 to level 8 safely]
3. [URL: Technical debt management — triage, prioritisation, register maintenance]
```

### Deployment Session URL Slots
```
1. [URL: Amazon Lightsail — PHP/nginx/MySQL stack setup, SSL, backups]
2. [URL: Docker → bare-metal migration patterns for Laravel]
3. [URL: Database backup automation — mysqldump, cron, S3 off-site]
```

---

## How to Use This Template

1. Before each Gemini session, select 3 URLs from relevant categories.
2. Verify each URL is still accessible and current.
3. Fill the actual title, URL, reason, key lessons, and relevance fields.
4. Combine the 3 URLs with 7 inventory/context files per `GEMINI_WORKING_SET.md`.
5. Do NOT exceed 10 files total per prompt.
6. After the session, add any newly discovered URLs to this manifest for future use.

---

*Template only. No actual URLs populated. Fill during remediation planning sessions.*
