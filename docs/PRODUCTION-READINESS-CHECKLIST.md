# FastPhunzira Production Readiness Checklist

## Purpose

This checklist defines the release gate for moving FastPhunzira from development into staging and, later, production.

The authoritative product specification remains `docs/00-AUTHORITATIVE-SPECIFICATION.md`. This document covers operational readiness only.

## 1. Source and CI

- [ ] Release is based on a reviewed commit from `main`
- [ ] Git working tree is clean
- [ ] GitHub Actions CI is green for the release commit
- [ ] `composer.lock` is committed and matches `composer.json`
- [ ] No secrets, credentials, or local `.env` files are committed

## 2. Application Configuration

### Staging

- [ ] `APP_ENV=staging`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` points to staging
- [ ] Staging has its own database
- [ ] Staging has its own environment variables
- [ ] `REQUIRE_HTTPS=true`
- [ ] Session lifetime and idle timeout are configured
- [ ] Mail settings point to the intended staging mail service

### Production

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL` points to production
- [ ] Production has its own database
- [ ] Production secrets are stored outside source control
- [ ] `REQUIRE_HTTPS=true`
- [ ] Secure session settings are enabled
- [ ] Production mail settings are configured

## 3. Database

- [ ] Database exists and uses the supported MySQL/MariaDB version
- [ ] Application database user has only the permissions required by the application
- [ ] All migrations are applied in order
- [ ] Migration 008 for certificate verification rate limiting is applied
- [ ] Required indexes exist
- [ ] Seed data required by the application exists
- [ ] Database backup completed before production release
- [ ] Backup restore procedure has been tested

## 4. Storage and Web Server

- [ ] Web server document root points to `public/`
- [ ] PHP version is supported by the project
- [ ] Required PHP extensions are installed
- [ ] Composer dependencies are installed from the lock file
- [ ] Required application storage/log directories are writable
- [ ] Sensitive files are not publicly downloadable
- [ ] HTTPS certificate is valid
- [ ] HTTP-to-HTTPS behavior is verified
- [ ] PHP error display is disabled in production
- [ ] Application/server logs are available to administrators

## 5. Security Verification

- [ ] Login works with valid credentials
- [ ] Invalid login returns a generic authentication error
- [ ] Repeated failed logins are throttled
- [ ] Session timeout works
- [ ] Logout invalidates the session
- [ ] CSRF protection works on state-changing requests
- [ ] Student cannot access another student's protected records
- [ ] Student cannot modify admin-managed content
- [ ] Exam ownership and submission authorization are enforced
- [ ] Certificate issuance validates course, exam, attempt, enrollment, pass status, and ownership
- [ ] Public certificate verification exposes only approved fields
- [ ] Public certificate verification rate limiting works
- [ ] Audit events are recorded for security-sensitive actions

## 6. Functional Smoke Test

### Student

- [ ] Register
- [ ] Login
- [ ] Browse published courses
- [ ] Enroll
- [ ] Open enrolled lessons
- [ ] Complete quiz
- [ ] Start exam
- [ ] Submit exam
- [ ] View result
- [ ] Receive eligible certificate
- [ ] View certificate
- [ ] Verify certificate publicly
- [ ] Logout

### Administrator

- [ ] Login
- [ ] View admin dashboard
- [ ] Create/update course
- [ ] Manage course content
- [ ] Manage quizzes/exams
- [ ] Review attempts/results
- [ ] Review certificates
- [ ] Review audit logs
- [ ] Logout

## 7. Deployment Gate

A release may move from staging to production only when:

1. CI is green.
2. Staging migrations succeed.
3. Staging smoke tests pass.
4. Security checks pass.
5. A production database backup exists.
6. The release commit is recorded.
7. Rollback steps are available.
8. Production configuration has been reviewed.

## 8. Rollback Gate

Before production deployment, record:

- Release commit SHA
- Database migration state
- Database backup location
- Application backup, where applicable
- Deployment timestamp
- Configuration version/reference

Do not automatically roll back database migrations unless the migration is explicitly designed to be reversible and the recovery procedure has been tested.

## 9. Final Release Record

Record the following for each production release:

- Release/version:
- Commit SHA:
- Deployment date/time:
- Deployed by:
- Database migration state:
- Backup reference:
- Smoke test result:
- Rollback reference:
- Notes:
