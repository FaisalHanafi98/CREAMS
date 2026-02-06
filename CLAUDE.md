# CREAMS - Community-based Rehabilitation Management System

## Project Overview

| Field | Value |
|-------|-------|
| **Type** | Full-Stack Web Application |
| **Priority** | 🔥 Flagship (Production System) |
| **Domain** | Rehabilitation Center Management (PPDK) |
| **Origin** | Gold Medal FYP - IIUM Computer Science |
| **Status** | Active Development (85% complete) |

## Tech Stack

| Layer | Technology | Version |
|-------|------------|---------|
| Framework | Laravel | 10.x |
| Language | PHP | 8.1+ |
| Database | MySQL | 8.0+ |
| Frontend | Blade Templates | - |
| CSS | Tailwind CSS | 3.x |
| Auth | Laravel Breeze + Sanctum | - |
| Testing | PHPUnit, Laravel Dusk | - |
| PDF | DomPDF | 3.1+ |
| Quality | PHPStan, Laravel Pint | - |

## Project Structure

```
CREAMS/
├── app/
│   ├── Http/Controllers/    # Request handlers
│   ├── Models/              # Eloquent models (50+ tables)
│   ├── Policies/            # Authorization policies
│   ├── Services/            # Business logic
│   └── Providers/           # Service providers
├── database/
│   ├── migrations/          # Schema definitions
│   └── seeders/             # Test data
├── routes/
│   └── web.php              # Route definitions
├── resources/views/         # Blade templates
├── tests/                   # PHPUnit & Dusk tests
└── public/                  # Web assets
```

## Core Features

1. **6-Role RBAC System**
   - Admin, Manager, Staff, Caretaker, Trainee, Parent
   - Policy-based authorization

2. **Session Scheduling**
   - Activity-based scheduling
   - Attendance tracking with alerts

3. **4-Tier Competency Tracking**
   - Government-aligned progression system
   - Assessment workflows

4. **Reporting**
   - PDF/Excel generation
   - Dashboard statistics with caching

5. **Global Search**
   - Query-optimized search
   - Cross-entity filtering

## Development Commands

```bash
# Local development
php artisan serve

# Run migrations
php artisan migrate

# Run seeders (anonymized data)
php artisan db:seed

# Run PHPUnit tests
php artisan test

# Run Dusk browser tests
php artisan dusk

# Code quality
./vendor/bin/phpstan analyse
./vendor/bin/pint

# Clear cache
php artisan optimize:clear
```

## Development Rules

### Data Confidentiality (CRITICAL)

**NEVER:**
- Include real trainee names, ICs, or personal data
- Screenshot or expose real data
- Document real center names in public specs
- Push real data to GitHub

**ALWAYS:**
- Use anonymized/fictional test data
- Reference "Center A", "Center B" in documentation
- Create seed data with Faker
- Mark confidentiality requirements explicitly

### Code Standards

1. **PHP**: Follow PSR-12, use type hints
2. **Laravel**: Use policies for authorization, services for business logic
3. **Testing**: Write tests for new features (target: 60%+ coverage, current: 13%)
4. **Security**: Validate all inputs, use prepared statements

### Commit Message Standards

Write commit messages as a real human developer would. Sound natural, clear, and conversational.

**Core Rules:**
- Use simple developer language
- Write in imperative mood (fix, add, update, refactor, remove)
- Explain WHAT changed and WHY it matters
- Be concise but meaningful
- Sound like you are explaining to a colleague

**Message Structure:**
```
type(scope): short, clear title (≤50 characters)

- What changed
- Why the change was made
- Any important context or side effects
```

**Style Guidelines:**

MUST:
- Use active voice
- Use bullet points
- Address the reader directly (you, your)
- Support claims with data and examples
- Focus on practical, actionable insights
- Be spartan and informative

AVOID:
- Passive voice
- Marketing tone or hype
- AI-sounding phrases (utilize, unlock, revolutionize, dive deep, harness, leverage)
- Metaphors and cliches
- Rhetorical questions
- Generalizations
- Setup language (in conclusion, in closing)
- Unnecessary adjectives and adverbs
- Constructions like "not just this, but also this"
- Em dashes (use commas or periods)
- Semicolons (use periods)
- Common filler words (can, may, just, that, very, really, literally, actually, certainly, probably, basically)

**Example Commit (Good):**
```
feat(security): Phase 1 Security Hardening Complete (16/16 tasks)

Implemented comprehensive security improvements addressing all CRITICAL
and HIGH priority vulnerabilities. OWASP compliance increased from 68%
to 85%+.

WEEK 1: Critical Fixes
- Removed 4 debug routes exposing session data
- Configured production environment with APP_DEBUG=false
- Implemented rate limiting (3-5 attempts/min for login)
- Removed IC numbers from API responses
- Added 7 security headers (X-Frame-Options, CSP, HSTS)
- Fixed session fixation vulnerability

WEEK 2: Authorization & Access Control
- Added role-based middleware to 4 sensitive route groups
- Verified centre isolation in all controllers
- Confirmed audit logging operational
- Restricted attendance marking to staff only
- Restricted letter generation to admin/supervisor

WEEK 3: Input Validation & Encryption
- Fixed 5 XSS vulnerabilities with escapeHtml() function
- Implemented strong password policy (12+ chars with complexity)
- Verified CSRF protection operational (93 @csrf directives)

Security Impact:
Before: 68% OWASP compliance, 3 CRITICAL vulnerabilities
After: 85%+ OWASP compliance, 0 CRITICAL vulnerabilities

Defense-in-Depth Layers:
1. Network: Rate limiting on auth endpoints
2. Application: RBAC, centre isolation, security headers
3. Input: Validation, XSS escaping, CSRF protection
4. Data: API PII hiding, audit logging
5. Authentication: Strong passwords, session regeneration
```

### Anti-Hallucination Protocol

1. **Database schema**: Read actual migrations, don't assume tables
2. **Feature behavior**: Test actual application, don't guess
3. **Controller logic**: Read actual code, don't invent endpoints
4. **Business rules**: Document from code and logic
5. **Performance claims**: Only from actual profiling data

Mark unknowns as: `[REQUIRES CODE INSPECTION]`

## Malaysian Context (PPDK)

- **PPDK**: Pusat Pemulihan Dalam Komuniti (Community-Based Rehabilitation Centers)
- Government-funded rehabilitation for persons with disabilities
- Operating under JKM (Jabatan Kebajikan Masyarakat)
- Competency-based progression system aligned with government standards

## Testing Priority

### Current State
- PHPUnit coverage: 13%
- Target: 60%+

### Priority Areas
1. RBAC permission enforcement
2. Attendance recording workflow
3. Competency assessment flow
4. Report generation accuracy
5. Dashboard statistics calculation

### Laravel Dusk Priority Scenarios
- Login flow for each role
- Trainee registration journey
- Activity scheduling workflow
- Report generation and download
- Search functionality

## Skills

CREAMS has a local skills system for repeatable workflows. Invoke with `/skill-name`.

| Skill | Purpose |
|-------|---------|
| `/route-audit` | Audit all routes vs controller methods — finds broken mappings |
| `/fix-verify` | Apply a fix with before/after Playwright screenshot verification |
| `/planning` | Multi-actor planning mode — produces decisions before any code |
| `/dead-code` | Remove unused imports, unreferenced controllers, dead routes |
| `/password-reset` | Reset staff password (workaround for tinker escaping bug) |

Additionally, the **obra/superpowers** plugin provides the full development workflow engine (brainstorming → git worktrees → writing plans → subagent-driven development → TDD → code review → branch finishing). Install via:
```
/plugin marketplace add obra/superpowers-marketplace
/plugin install superpowers@superpowers-marketplace
```

Full registry: [`.claude/skills/SKILLS_REGISTRY.md`](.claude/skills/SKILLS_REGISTRY.md)

## Key Documentation

| Document | Purpose |
|----------|---------|
| `CREAMS_CODEBASE_DOCUMENTATION.md` | System overview |
| `CREAMS_Testing_Infrastructure_PRD.md` | Test strategy |
| `creams_detailed_uat.md` | User acceptance tests |
| `CHANGELOG.md` | Version history |

## Quality Gates

- All tests must pass before commit
- PHPStan level 5+ (no errors)
- Laravel Pint formatting applied
- No real data in commits

## Upgrade Considerations

- Laravel 11 upgrade path (when stable)
- PHP 8.2+ compatibility
- Test coverage improvement before major changes

---
*Last Updated: 2026-02-07*
*Gold Medal FYP - Evolved for Production*
