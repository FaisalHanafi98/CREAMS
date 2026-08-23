# ADR-001: Server-Rendered Blade over React SPA

**Status:** Accepted
**Date:** 2025-01-01 (retroactive documentation)
**Decision Makers:** FYP Development Team

## Context

CREAMS serves Malaysian PPDK rehabilitation centres. These centres operate across 4 locations (Gombak, Kuantan, Pagoh, Gambang) with varying network quality. The system needed a web frontend that works reliably in these conditions.

Two approaches were considered:

1. React SPA with Laravel API backend
2. Laravel Blade server-rendered templates

## Decision

Use Laravel Blade with server-side rendering for all UI.

## Reasons

1. **Network reliability.** Malaysian PPDK centres have inconsistent internet. Server-rendered pages work on slower connections because the server does the heavy lifting. SPAs require downloading a JS bundle before anything renders.

2. **Simpler deployment.** One codebase, one server, one deployment process. No separate frontend build pipeline, no CORS configuration, no API versioning.

3. **University handover.** CREAMS originated as an IIUM FYP. The next maintainer is likely a student or small team. Blade is standard Laravel — any Laravel developer can maintain it. React adds a second framework to learn.

4. **No API needed.** CREAMS is an internal tool. There are no mobile apps or third-party consumers. Building a REST API just to serve a SPA adds complexity without delivering value.

5. **Reduced bundle size.** No React, React DOM, or state management library to download. Pages load with standard HTML + minimal JS enhancements.

## Trade-offs

- Less interactive UI than a React SPA (full page reloads for navigation)
- Adding real-time features requires polling or Livewire instead of native React state
- If mobile apps are needed later, an API layer must be built from scratch

## Consequences

- All UI is in `resources/views/` as `.blade.php` files (252+ templates)
- JavaScript is used sparingly for interactivity (search, modals, charts)
- No frontend build step required for development
- Tailwind CSS compiled via Laravel Mix/Vite
