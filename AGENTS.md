# AGENTS.md

## Project overview
FastPhunzira is a PHP/MySQL web application for learning, assessment, and certification. The product goal is to support the full learner journey: register → enroll → learn → complete assessments → receive certificates → verify credentials.

## Source of truth
Use the documentation in `docs/` as the authoritative specification.

Priority order:
1. `docs/00-AUTHORITATIVE-SPECIFICATION.md`
2. `docs/01-PROJECT-REQUIREMENTS.md`
3. `docs/02-PRODUCT-ROADMAP.md`
4. `docs/03-SYSTEM-ARCHITECTURE.md`
5. Remaining supporting docs in `docs/`

## Working rules
- Keep scope aligned to the MVP and staged roadmap.
- Do not add large feature work outside the documented phase unless the user explicitly requests it.
- Follow the existing layered structure: controller → service → repository → database.
- Keep SQL in repositories, not controllers or views.
- Validate server-side inputs and enforce auth/authorization checks.
- Protect state-changing actions with CSRF tokens.
- Escape output in views to prevent XSS.
- Prefer prepared statements and secure configuration.
- Keep implementations maintainable and easy to test.

## Delivery expectations
- Prefer small, verifiable changes.
- Validate the relevant behavior after changes with the smallest practical command.
- If a project-level task is broad, break it into concrete milestones and report progress clearly.

## Current focus
The active focus is the MVP: learner onboarding, courses, enrollment, lesson flow, assessment, result tracking, certificates, and basic admin operations.

## Developer notes
- This repo is a custom PHP MVC app, not a framework-based app.
- Use the existing App namespace conventions and route bootstrap patterns.
- Avoid introducing hidden dependencies or framework migrations without explicit approval.
