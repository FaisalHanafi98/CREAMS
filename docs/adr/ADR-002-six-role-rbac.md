# ADR-002: 6-Role RBAC Design

**Status:** Accepted
**Date:** 2025-01-01 (retroactive documentation)
**Decision Makers:** FYP Development Team

## Context

CREAMS manages sensitive rehabilitation data for persons with disabilities. The system needed access control that reflects the actual PPDK organizational hierarchy and satisfies PDPA compliance requirements.

## Decision

Implement a 6-role RBAC system: Admin, Supervisor, Teacher, AJK, Trainee, Parent.

Currently active roles: Admin, Supervisor, Teacher, AJK (4 of 6).

## Role Mapping

| Role | Real-World Position | Access Level |
|------|-------------------|-------------|
| Admin | Centre Director / IT | Full system access, user management, configuration |
| Supervisor | Program Coordinator | Staff oversight, reporting, letter generation |
| Teacher | Instructor / Therapist | Activity management, attendance, trainee interaction |
| AJK | Committee Member | Limited read access, meeting records |
| Trainee | (Future) | Self-service portal for own records |
| Parent | (Future) | Read-only access to child's progress |

## Reasons

1. **Maps to actual org chart.** Each role corresponds to a real position in the PPDK structure. No artificial grouping required.

2. **Principle of least privilege.** Teachers cannot manage other staff. AJK members cannot modify trainee records. Each role sees only what it needs.

3. **Audit trail.** Role-tagged session data (`session('role')`) enables per-action audit logging. Every operation is traceable to a specific role and user.

4. **Centre isolation.** Combined with `centre_id` scoping, roles enforce that Gombak staff cannot see Kuantan data.

## Implementation

- Authentication: Custom session-based (`session('id')`, `session('role')`, `session('centre_id')`)
- Middleware: `RoleMiddleware` checks `session('role')` against allowed roles per route group
- Route protection: `middleware(['role:admin,supervisor'])` syntax in `web.php`

## Trade-offs

- More middleware complexity than a 2-3 role system
- Custom auth instead of Laravel's built-in Gate/Policy system (historical decision from FYP)
- Trainee and Parent roles defined but not yet implemented

## Consequences

- 70+ controllers check session role before processing
- Route groups in `web.php` are segmented by role access
- User factory has states for each role: `admin()`, `supervisor()`, `teacher()`, `ajk()`
