# FastPhunzira Rollback and Recovery

## Purpose

This document defines the recovery approach for failed deployments and database incidents.

## Principle

Application rollback and database rollback are different operations.

```
Application code
    ↓
Can usually be restored to a previous release

Database
    ↓
Must be restored or migrated carefully
```

Do not assume that checking out an older Git commit reverses database changes.

## Before Every Production Deployment

Record:

- current production commit SHA
- target release commit SHA
- current migration state
- target migration state
- database backup reference
- deployment timestamp
- operator
- relevant environment/configuration changes

## Application Rollback

If the new application release is defective but the database remains compatible:

1. Stop or disable new deployment activity.
2. Restore the previous known-good application release.
3. Confirm the production environment variables are unchanged unless the release requires a documented change.
4. Clear/rebuild application caches if the deployment uses them.
5. Run application startup checks.
6. Run critical smoke tests.
7. Record the rollback.

## Database Recovery

If the database has been corrupted or a migration caused an unrecoverable problem:

1. Stop writes where practical.
2. Preserve logs and the failed release information.
3. Identify the latest verified backup.
4. Restore the database into a controlled recovery environment first when possible.
5. Validate schema and critical records.
6. Restore production only after the recovery procedure is understood.
7. Deploy an application version compatible with the recovered database.
8. Run smoke tests.
9. Document the incident.

## Migration Safety

Prefer forward-compatible migrations.

For production:

- back up before schema changes
- avoid destructive changes in the same release as code that depends on the new schema
- avoid dropping columns/tables without a deliberate migration plan
- test migrations against a staging copy first
- record migration state after deployment

## Backup Requirements

At minimum, production should have:

- scheduled database backups
- a secure backup destination separate from the production database server
- retention appropriate to the application
- periodic restore testing

A backup that has never been restored is a very optimistic collection of bytes.

## Recovery Validation

After recovery verify:

- application loads
- authentication works
- students can access their own courses
- exam results remain intact
- certificates remain valid
- public certificate verification works
- admin authorization works
- audit logs are available
- HTTPS remains enforced

## Incident Record

For a production incident record:

- date/time
- affected release
- current commit SHA
- restored commit SHA
- database backup/reference
- migration state
- symptoms
- root cause
- recovery actions
- validation result
- follow-up work
