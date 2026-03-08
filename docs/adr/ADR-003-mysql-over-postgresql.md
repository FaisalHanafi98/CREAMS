# ADR-003: MySQL over PostgreSQL

**Status:** Accepted
**Date:** 2025-01-01 (retroactive documentation)
**Decision Makers:** FYP Development Team

## Context

CREAMS needed a relational database for structured data: trainees, activities, attendance records, staff profiles, assets. The two primary candidates were MySQL 8.0 and PostgreSQL 15+.

## Decision

Use MySQL 8.0.

## Reasons

1. **University hosting compatibility.** IIUM servers and common Malaysian shared hosting providers support MySQL natively. PostgreSQL requires additional setup or dedicated hosting.

2. **Simpler handover.** The next maintainer is likely a student. MySQL is the most commonly taught RDBMS in Malaysian CS programs. phpMyAdmin is universally available for visual management.

3. **Sufficient for the data model.** CREAMS stores relational data (users, activities, attendance, assets). No need for PostgreSQL-specific features:
   - No vector search (no ML embeddings stored in DB)
   - No complex JSON queries (config stored in `.env`, not JSONB columns)
   - No PostGIS (no geographic mapping features)
   - No full-text search beyond basic LIKE queries

4. **Laravel ecosystem alignment.** Laravel's default database is MySQL. All documentation examples, community packages, and deployment guides assume MySQL first.

## Trade-offs

- No native JSONB operators (would need to use `JSON_EXTRACT()` instead of `->` operator)
- No PostGIS if mapping features are added later
- MySQL's strict mode can cause issues with GROUP BY queries that PostgreSQL handles correctly
- No partial indexes for query optimization

## Consequences

- Database: `cream` (production), `cream_test` (testing)
- 30 migration files defining 50+ tables
- All queries use Eloquent ORM (database-agnostic, makes future migration possible)
- `config/database.php` has `mysql` and `mysql_test` connections configured
