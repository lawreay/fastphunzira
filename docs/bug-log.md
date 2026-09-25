# FastPhunzira Bug Log

## Scope
This document records production-relevant bugs discovered during implementation review and validation for the exam engine and authentication flow. It is intended to preserve root cause, impact, and remediation status for future review and release gates.

---

## 1. WAMP subfolder routing regression

- Status: Fixed
- Date: 2026-09-24
- Area: Front controller / Apache rewrite / route dispatch
- Symptom:
  - `/login`, `/register`, and other dynamic routes returned `404 Not Found` under the local WAMP setup at `/fastphunzira/public/`.
- Root cause:
  - The application front controller did not reliably normalize the base path when running behind a subfolder.
  - Dynamic route matching for parameterized paths was not correctly converting placeholders before matching.
  - The Apache rewrite config was missing or incomplete for the project subfolder.
- Impact:
  - Users could not reach auth pages or routed exam/admin screens.
  - Browser smoke tests failed before the application logic executed.
- Fix:
  - Restored and corrected `.htaccess` rewrite rules.
  - Normalized the router base path from `SCRIPT_NAME`.
  - Fixed dynamic route regex matching for placeholders like `{id}`.
  - Added parameter coercion before invoking handler methods.
- Validation:
  - Browser load of `/fastphunzira/public/login` reached the login form successfully.
  - PHPUnit project suite passed after the fix.

---

## 2. Missing CSRF input helper

- Status: Fixed
- Date: 2026-09-24
- Area: Security / form rendering
- Symptom:
  - Exam and quiz forms crashed at render time with `Call to undefined method App\Support\Csrf::input()`.
- Root cause:
  - The `Csrf::input()` helper was missing from the security helper implementation even though templates depended on it.
- Impact:
  - Forms could not render their hidden `_token` fields.
  - Protected POST requests could not be validated reliably.
- Fix:
  - Added `Csrf::input()` to render the hidden token field in the same format as the rest of the app.
- Validation:
  - Login and exam form pages rendered successfully in the browser.
  - PHPUnit suite remained green.

---

## 3. Exam result ownership gap

- Status: Fixed
- Date: 2026-09-26
- Area: Authorization / exam service layer
- Symptom:
  - `getAttemptResult()` validated the attempt belonged to the supplied student ID, but it did not verify the current authenticated user matched that student.
- Root cause:
  - Service authorization was weaker than route-level authorization.
  - The service trusted method arguments without checking the active session identity.
- Impact:
  - An authenticated administrator or another user could potentially retrieve another student’s result if the service call was reached with mismatched data.
- Fix:
  - Enforced `Auth::check()` in the service path.
  - Enforced `Auth::userId() === $studentId` before returning a result.
  - Added a regression test covering unauthorized cross-user access.
- Validation:
  - Fresh PHPUnit run after the fix passed successfully.

---

## 4. Route closure missing repository injection

- Status: Fixed
- Date: 2026-09-24
- Area: Exam engine HTTP layer
- Symptom:
  - `/exam-attempts/{id}/answers` referenced `$examAttemptRepository` in a route closure without injecting it into the closure scope.
- Root cause:
  - The closure used a variable that was never bound from the application container, causing runtime failures during exam answering flows.
- Impact:
  - Student answer submission could fail at runtime, even when the rest of the exam flow was otherwise valid.
- Fix:
  - Injected the required repository into the route closure and revalidated the browser flow.
- Validation:
  - Browser exam flow completed successfully after the fix.

---

## 5. Undefined view variables during controller dispatch

- Status: Fixed
- Date: 2026-09-24
- Area: Template rendering / route result extraction
- Symptom:
  - Admin screens showed undefined-variable warnings because route results were not being extracted into the template scope.
- Root cause:
  - The front controller returned arrays from handlers and rendered views without exporting the payload fields into the view scope.
- Impact:
  - Page rendering produced warnings and missing data in admin/student templates.
- Fix:
  - Used `extract($result, EXTR_SKIP)` before rendering the view.
- Validation:
  - Admin course list and creation screens rendered correctly.

---

## Security / quality notes

- Authentication and authorization checks must be enforced in the service layer, not only in route guards.
- Browser timers and client-side state must never be trusted for exam finalization, scoring, or deadline enforcement.
- Dynamic route matching and subfolder hosting require explicit base-path normalization and validation.
- CSRF tokens must be generated and rendered consistently for all protected forms.
- Exam result retrieval must verify both attempt ownership and current authenticated user identity.

---

## Current status

- Auth flow: working
- Route dispatch under WAMP subfolder: working
- Exam engine flow: working
- Result ownership enforcement: enforced and regression-tested
- Certificates: intentionally deferred until exam eligibility and result integrity are confirmed.
