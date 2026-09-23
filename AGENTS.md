# FastPhunzira AI Agent Governance

## 1. Project Identity

Project: FastPhunzira

Purpose:
A lightweight e-learning, examination, certification, and certificate-verification platform.

Core workflow:

Learn
→ Practice
→ Examine
→ Pass
→ Certify
→ Verify

## 2. Authority Hierarchy

The project specification is the source of truth. AI agents must follow the authority chain below in order:

1. `docs/00-AUTHORITATIVE-SPECIFICATION.md`
2. `docs/01-PROJECT-REQUIREMENTS.md`
3. `docs/03-SYSTEM-ARCHITECTURE.md`
4. `docs/04-DATABASE-DESIGN.md`
5. `docs/05-API-SPECIFICATION.md`
6. `docs/06-SCREEN-SPECIFICATION.md`
7. `docs/08-EXAM-ENGINE-SPECIFICATION.md`
8. `docs/09-CERTIFICATE-SPECIFICATION.md`
9. `docs/10-SECURITY-REQUIREMENTS.md`
10. Implementation code and tests

Agents must never contradict a higher-priority document. If a requested change conflicts with the specification, the agent must stop, explain the conflict, and request human direction.

## 3. Required Workflow Before Code Changes

Before modifying code, the agent must:

1. Read `AGENTS.md`.
2. Identify the relevant specification document.
3. Inspect the relevant existing implementation.
4. Identify dependencies and affected areas.
5. Confirm whether the requested change conflicts with the approved specification.
6. Explain the planned change internally before editing.
7. Make the smallest necessary change.
8. Run relevant tests or verification checks.
9. Review security implications.
10. Review database and API compatibility.
11. Update documentation only when behavior changed.
12. Report exactly what changed and what was validated.

Important rule:
Inspect first, modify second.

## 4. Don’t-Do Rules

Agents must not:

- introduce Laravel or another framework without explicit approval
- replace PHP with another backend framework
- replace MySQL without approval
- rewrite the architecture without approval
- change database relationships arbitrarily
- put business logic into views
- trust frontend timers for exam enforcement
- calculate final exam scores on the client
- expose correct answers through public or frontend APIs
- allow students to modify exam results
- issue certificates without server-side eligibility validation
- expose private student information through public verification
- commit passwords, API keys, or secrets
- disable security controls to make development easier
- delete existing functionality without checking dependencies
- create duplicate implementations of existing services or repositories
- install unnecessary dependencies

## 5. Permission Boundaries

### Frontend Agent
Allowed to modify:
- HTML, CSS, JavaScript, layouts, views
- UI components and screen behavior
- client validations and page interactions

Not allowed to modify:
- schema design
- auth and role logic
- exam scoring
- certificate eligibility logic
- database migrations without review

### Backend Agent
Allowed to modify:
- PHP controllers, services, repositories, and helpers
- business logic and API behavior
- security and validation logic

Not allowed to modify:
- product scope without specification approval
- database design without approval
- public UI styling without design approval where necessary

### Database Agent
Allowed to modify:
- migrations
- schemas
- indexes
- seed data

Not allowed to modify:
- business requirements
- API contract without approval
- exam rules without approval

### Security Agent
Allowed to modify:
- audit logging
- authorization logic
- session handling
- verification and certificate security

Not allowed to modify:
- product behavior or scope arbitrarily

### Testing Agent
Allowed to modify:
- automated tests
- test fixtures
- validation scripts

Not allowed to modify:
- production logic without approval

### Documentation Agent
Allowed to modify:
- documentation and spec updates

Not allowed to modify:
- architecture or implementation without approval

## 6. Human Approval Required

The following changes require explicit human approval before they are merged:

- database schema changes
- authentication changes
- authorization changes
- exam scoring rules
- certificate eligibility rules
- certificate verification rules
- security architecture changes
- API breaking changes
- new external services
- new dependencies
- framework or technology changes
- changes to personal-data handling
- destructive migrations
- production configuration

## 7. Change Classification

### SAFE
Examples:
- CSS adjustment
- typo correction
- documentation improvement
- small UI polish
- test addition

### REVIEW
Examples:
- controller changes
- service changes
- API changes
- new database columns
- auth or role adjustments

### CRITICAL
Examples:
- exam scoring logic
- certificate issuance logic
- permission checks
- user access changes
- database restructuring
- security controls
- production deployment configuration

Critical changes require human review before merge.

## 8. Stop-and-Ask Rule

The agent must stop and ask for guidance when:

- requirements conflict
- the specification is missing or unclear
- database relationships are ambiguous
- the API contract is unclear
- security implications are unclear
- a requested change breaks an existing requirement
- a destructive migration is required
- two documents disagree

The correct behavior is:

STOP
↓
Identify the conflict
↓
Reference the affected specification
↓
Explain the issue
↓
Wait for human decision

The agent must not invent a solution when the specification is unclear.

## 9. Implementation Report Requirement

For meaningful changes, the agent should provide an implementation report that includes:

- feature or fix name
- files changed
- specification document referenced
- summary of changes
- security impact
- tests/verification performed
- database impact
- breaking changes, if any

Example format:

```text
## Change Summary

Feature: Exam submission

Files changed:
- app/Services/ExamService.php
- app/Controllers/ExamController.php
- tests/ExamSubmissionTest.php

Specification:
- docs/08-EXAM-ENGINE-SPECIFICATION.md

Changes:
- Added server-side expiration validation
- Added duplicate submission protection
- Added server-side scoring

Security:
- Frontend timer is not trusted
- Attempt ownership is validated
- Submission is processed under server-side transaction rules

Tests:
- valid submission
- expired submission
- duplicate submission
- unauthorized submission
```

## 10. Git and Review Control

AI work must happen in a feature branch, not directly on `main`.

Branch model:

```text
main
├── feature/authentication
├── feature/course-management
├── feature/exam-engine
├── feature/certificates
├── fix/exam-submission
└── hotfix/security-fix
```

AI changes must be reviewed before merge. The final merge is human-controlled.

## 11. Tests and Validation

The agent must not claim success without validation.

Required validation patterns:

- unit tests for isolated logic
- integration tests for business flows
- security checks for auth, exam, certificate, and verification flows
- manual verification when an automated test does not cover the real behavior

For the exam system, at minimum, validate:

1. start exam
2. create attempt
3. timer begins
4. submit answers
5. server validates
6. server calculates score
7. attempt is finalized
8. certificate eligibility is checked

## 12. Documentation Rules

- Documentation is not optional.
- If behavior changes, update the relevant documentation.
- If the code contradicts the current specification, the code must be corrected or the specification must be updated by human approval.
- Historical or duplicate docs in `docs/archive/` are historical references only and must not be treated as active product specifications.

## 13. Final Rule

The agent is an implementer, not the owner of the product specification.

The approved project specification remains the authority.

FastPhunzira should be built with a clear chain of control:

```text
Human decision
      ↓
Specification
      ↓
AGENTS.md
      ↓
AI coding agent
      ↓
Implementation
      ↓
Tests
      ↓
Human review
      ↓
Git merge
```

This prevents the repository from turning into an AI-generated improvisation with a database nobody can explain.
