# FastPhunzira Staging Deployment

## Purpose

Staging is a production-like environment used to validate a FastPhunzira release before production deployment.

Staging must never use the production database.

## Environment Model

```
Developer
   ↓
GitHub Pull Request
   ↓
GitHub Actions CI
   ↓
main
   ↓
Staging application
   ↓
Staging database
   ↓
Smoke/security tests
   ↓
Production
```

## Staging Requirements

- PHP 8.2+
- MySQL 8+ or a compatible supported database version
- Apache or Nginx
- HTTPS
- Composer
- A separate staging database
- A separate staging `.env`

The exact hosting mechanism must be verified before deployment automation is introduced.

## Configuration

Create the staging environment from `.env.example`, then set values appropriate to the staging server.

Minimum settings:

```env
APP_NAME="FastPhunzira"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.example.com

DB_HOST=...
DB_PORT=3306
DB_DATABASE=fastphunzira_staging
DB_USERNAME=...
DB_PASSWORD=...

REQUIRE_HTTPS=true
```

Never commit the staging `.env`.

## Deployment Procedure

### 1. Select the release

Deploy a known-good commit from `main`.

Record:

- commit SHA
- deployment date/time
- operator

### 2. Prepare the server

Confirm:

- supported PHP version
- required PHP extensions
- web server configuration
- HTTPS
- document root set to `public/`
- writable application storage/log directories

### 3. Deploy application files

Place the selected release on the staging server.

Do not copy the local development `.env`.

### 4. Install dependencies

Run:

```bash
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
```

If staging is intended to reproduce CI/dev tooling exactly, install dev dependencies instead. Production must not require PHPUnit.

### 5. Configure environment

Create the staging `.env` and verify:

- application URL
- database credentials
- HTTPS requirement
- session settings
- login security limits
- certificate verification rate limits
- mail configuration

### 6. Prepare the database

Create the staging database and application user.

Apply migrations in repository order.

Migration 008 must be applied because it creates the persistent certificate verification rate-limit table.

Never point this process at the production database.

### 7. Validate application startup

Verify:

- landing page loads
- login page loads
- public certificate verification page loads
- admin authentication works
- no PHP errors are displayed

### 8. Run smoke tests

Execute the student, administrator, certificate, authentication, and authorization checks in `docs/PRODUCTION-READINESS-CHECKLIST.md`.

### 9. Record result

Record:

- commit SHA
- migration state
- smoke-test result
- known issues
- approval for production or rejection

## Staging Data

Use synthetic/test data where practical.

Do not copy production student data into staging unless there is a documented operational reason, approved handling, and appropriate protection.

## Important Rule

Staging is not a second production database. It is a controlled validation environment.

Do not test destructive migrations, certificate revocation, account deletion, or other irreversible operations against production while pretending to be in staging. Humans have historically been surprisingly good at that particular mistake.
