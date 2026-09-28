# FastPhunzira Frontend Route Map

## Purpose

This document defines the intended navigation relationships for the public and initial student experience. It is the UI navigation contract for the current frontend polish pass.

## Design source

The active product source of truth is docs/00-AUTHORITATIVE-SPECIFICATION.md, with screen details in docs/06-SCREEN-SPECIFICATION.md.

The requested Share/Frontend Idea folder is not present in the current GitHub repository branch, so this pass does not claim to reproduce that unavailable local reference. The implementation follows the approved screen specification and the existing FastPhunzira architecture until that reference is committed/shared with the project.

## Public navigation

| Current page | User action | Destination | Purpose |
|---|---|---|---|
| Landing / | Start learning | /register | Create a student account |
| Landing / | Explore courses | /courses | Browse published courses |
| Landing / | Go to dashboard | /dashboard | Return to the authenticated learning area |
| Header | Home | / | Return to public landing page |
| Header | Courses | /courses | Browse available learning |
| Header | Login | /login | Authenticate an existing account |
| Header | Register | /register | Create a new account |
| Login /login | Sign in | POST /login | Authenticate the user |
| Login /login | Forgot password | /forgot-password | Enter the recovery flow |
| Login /login | Create one | /register | Start registration |
| Register /register | Create account | POST /register | Create the student account |
| Register /register | Sign in | /login | Return to authentication |
| Forgot password /forgot-password | Back to sign in | /login | Return to login |
| Forgot password /forgot-password | Create account | /register | Start registration |

## Student application navigation

| Current page | User action | Destination | Purpose |
|---|---|---|---|
| Dashboard /dashboard | Browse courses | /courses | Discover learning |
| Dashboard /dashboard | View my courses | /my-courses | Continue enrolled courses |
| Dashboard /dashboard | View certificates | /student/certificates | Review earned certificates |
| My Courses /my-courses | Continue learning | /courses/{id}/learn | Open course lessons |
| My Courses /my-courses | Practice quizzes | /courses/{id}/quizzes | Practice course assessments |
| My Courses /my-courses | Final exams | /courses/{id}/exams | Access published exams |
| Course learning /courses/{id}/learn | Open lesson | /lessons/{id} | Study a lesson |
| Course learning /courses/{id}/learn | Back to course | /courses/{id} | Return to course details |
| Certificates /student/certificates | View verification | /verify/{certificate_number}?code=... | Verify an issued certificate |
| Authenticated header | Logout | POST /logout | End the current session |

## Course discovery flow

```text
Landing
  ↓
Courses
  ↓
Course details
  ↓
Login/Register when authentication is required
  ↓
Enroll
  ↓
My Courses
  ↓
Learn
  ↓
Quiz / Exam
  ↓
Result
  ↓
Certificate
  ↓
Public Verification
```

## Authentication flow

```text
Landing
  ├── Register → account created → Login → Dashboard
  └── Login
        ├── success → Dashboard
        └── Forgot password → Recovery screen
```

### Password recovery status

The forgot-password screen has been added as a frontend state, but the actual reset-token/email delivery workflow is not implemented in the current application. The submit control therefore remains disabled rather than pretending to perform a recovery action that does not exist.

When the backend reset flow is implemented, the intended path is:

```text
Login
  ↓
Forgot password
  ↓
Enter email
  ↓
Secure reset token generated
  ↓
Email reset link
  ↓
Reset password page
  ↓
Login
```

The reset implementation must follow the security requirements in the authoritative specification and requires human review because it changes authentication behavior.

## UI implementation order

1. Landing page
2. Login
3. Register
4. Forgot-password state
5. Student dashboard
6. My Courses
7. Course catalogue/details
8. Lesson view
9. Quiz/exam interfaces
10. Results
11. Certificates
12. Admin screens

The current branch has completed the first five items as a UI polish pass. Existing backend workflows remain the source of truth and have not been replaced by frontend logic.
