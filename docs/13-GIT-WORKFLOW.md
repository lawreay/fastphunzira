# Git Workflow

## 1. Purpose

This workflow defines how the FastPhunzira team coordinates development, review, and release work while keeping the project organized and low-risk.

## 2. Branching Model

```text
main
 └── develop
      ├── feature/auth
      ├── feature/courses
      ├── feature/exams
      ├── feature/certificates
      └── hotfix/security-fix
```

## 3. Branch Naming

Use clear, structured branch names:
* feature/authentication
* feature/course-management
* feature/exam-engine
* feature/certificate-verify
* hotfix/session-timeout

## 4. Development Flow

1. Start from `develop`
2. Create a feature branch
3. Implement work and commit changes in small units
4. Open a pull request against `develop`
5. Review code and discuss edge cases
6. Merge after validation and approval

## 5. Commit Standards

Use small, readable commits with clear messages such as:
* Add student registration workflow
* Implement course enrollment validation
* Fix exam timer expiry logic
* Add certificate verification endpoint

## 6. Pull Request Guidelines

Each pull request should include:
* summary of change
* affected files
* testing performed
* risks or follow-up items
* link to issue or task if applicable

## 7. Merge Rules

* Do not merge directly to `main` without review
* Keep `develop` stable before release
* Merge hotfixes carefully and verify critical flows
* Prefer small, incremental changes over broad rewrites

## 8. Release Process

* Merge approved work into `develop`
* Run final validation for key user flows
* Tag the release version
* Merge into `main` when release is ready

## 9. Issue Tracking

Use a simple method for tracking:
* features
* bugs
* security issues
* documentation updates

This helps keep development structured even in a small team.

## 10. Team Collaboration Principles

* Keep branches focused on one job at a time
* Communicate merge conflicts early
* Review logic before merging large database or exam changes
* Treat security and certificate logic as especially high-risk changes
