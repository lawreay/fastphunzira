# FastPhunzira Security Hardening

## Release
- Pull request: #4
- Branch: `feature/security-hardening`
- Base: `main`
- Date: 2026-09-25

## Scope

This hardening pass strengthens certificate integrity, authorization, session security, auditability, and regression coverage without introducing new product features.

## Security changes

### Certificate integrity
- Certificate issuance now requires a finalized `submitted` exam attempt.
- Certificate numbers use random generation with repository uniqueness checks.
- Database uniqueness remains the final collision safeguard.
- Certificate issuance is restricted to the authenticated student owning the result.
- Certificate status changes require the authenticated actor to have the required permission and matching user ID.

### Audit trail
- Certificate issuance creates a `certificate_issued` audit event.
- Certificate status changes create a `certificate_status_changed` audit event.
- Status-change events preserve old and new status values.

### Session security
- Added server-side idle-session timeout enforcement.
- Production defaults to HTTPS-only session cookies unless explicitly configured otherwise.
- HttpOnly and SameSite cookie protections remain enabled.

### Authentication response hygiene
- Registration responses no longer expose password hashes.

## Verification

Local verification completed on 2026-09-25:

- PHPUnit 9.6.36
- **59 tests**
- **209 assertions**
- **100% passing**
- Composer lock file verified successfully
- `composer check` passed
- PHP syntax checks passed for `public/index.php` and `bootstrap/app.php`

## Deliberately deferred

The following are separate follow-up security work and are not part of this release:

- Login brute-force/rate limiting
- Failed-login security audit events
- Public certificate verification rate limiting
- Audit-log pagination and filtering
- GitHub Actions CI
- Broader authorization sweep

These are intentionally separated to keep this hardening change reviewable and avoid mixing incomplete security features into one release.

## Release status

PR #4 is the security-hardening release candidate.