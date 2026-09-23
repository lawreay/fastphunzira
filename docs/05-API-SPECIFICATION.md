# API Specification

## 1. Purpose

This document defines the backend API contract for FastPhunzira. It gives frontend and backend developers a shared understanding of routes, request formats, response shape, and validation expectations.

## 2. Base URL

```text
/api
```

## 3. Conventions

* All authenticated endpoints require a valid session or token
* JSON is the expected request and response format for API calls
* Validation errors return a consistent error object
* Success responses include a status flag and payload
* Server-side authorization checks must be enforced for every protected route

## 4. Common Response Format

### Success

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"]
  }
}
```

## 5. Authentication Endpoints

### POST /api/auth/register

Registers a new student account.

Request body:
```json
{
  "full_name": "Jane Doe",
  "email": "jane@example.com",
  "password": "StrongPass123!",
  "confirm_password": "StrongPass123!"
}
```

Validation:
* Full name required
* Email valid and unique
* Password length and strength requirements
* Confirm password must match

### POST /api/auth/login

Authenticates a user.

Request body:
```json
{
  "email": "jane@example.com",
  "password": "StrongPass123!"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "full_name": "Jane Doe",
      "email": "jane@example.com",
      "role": "student"
    }
  }
}
```

### POST /api/auth/logout

Logs the current user out.

### POST /api/auth/forgot-password

Triggers a password reset email or reset request.

### POST /api/auth/reset-password

Uses reset token to set a new password.

## 6. Course Endpoints

### GET /api/courses
Returns all active published courses.

### GET /api/courses/{id}
Returns detail for a specific course.

### POST /api/courses
Admin-only. Creates a course.

Request body:
```json
{
  "title": "Introduction to Business Ethics",
  "description": "A course on ethical decision-making",
  "status": "published"
}
```

### PUT /api/courses/{id}
Admin-only. Updates a course.

### DELETE /api/courses/{id}
Admin-only. Soft deletes or archives a course.

## 7. Enrollment Endpoints

### POST /api/courses/{id}/enroll
Student-only. Enrolls the authenticated user in a course.

### GET /api/users/me/enrollments
Returns active and completed enrollments for current user.

## 8. Lesson Endpoints

### GET /api/courses/{courseId}/lessons
Returns all lessons for the course.

### GET /api/lessons/{id}
Returns a specific lesson lesson details.

## 9. Quiz Endpoints

### GET /api/quizzes/{id}
Returns a quiz with visible questions and options.

### POST /api/quizzes/{id}/attempts
Creates a new quiz attempt.

### POST /api/quizzes/attempts/{id}/submit
Submits a quiz attempt and returns score.

## 10. Exam Endpoints

### GET /api/exams/{id}
Returns exam metadata and eligibility information for the current user.

### POST /api/exams/{id}/attempts
Starts a timed exam attempt.

### PUT /api/exam-attempts/{id}/answers
Saves partial answers for a current exam attempt.

### POST /api/exam-attempts/{id}/submit
Finalizes a submitted exam attempt.

### GET /api/users/me/exam-attempts
Returns exam history for the current user.

## 11. Result Endpoints

### GET /api/results/{attemptId}
Returns score and evaluation details for an attempt.

### GET /api/users/me/results
Returns all student results.

## 12. Certificate Endpoints

### Public Verification Page

The human-facing browser route is:

```text
GET /verify/{certificate_number}
```

Example:
```text
/verify/FP-2026-000001
```

This page may load certificate metadata and then call the API endpoint below for verification.

### GET /api/certificates/verify
Public endpoint. Accepts certificate number and verification code.

Query parameters:
* certificate_number
* verification_code

This is the backend verification contract used by the public page or a frontend client.

### GET /api/users/me/certificates
Returns the current user’s certificates.

### POST /api/certificates/generate
Admin or server-side internal process. Issues a certificate when criteria are met.

## 13. Admin Endpoints

### GET /api/admin/students
Returns user list and basic account metadata.

### GET /api/admin/courses
Returns all courses including unpublished entries.

### GET /api/admin/exam-attempts
Returns attempts by exam or user.

### GET /api/admin/audit-logs
Returns audit activity for review and compliance.

## 14. Validation Rules

All endpoints must validate:
* required fields
* email format
* role permissions
* request size
* numeric IDs
* time constraints on exam attempts
* submission duplicates or double-submits

## 15. Security Rules

* All POST, PUT, and DELETE endpoints require CSRF protection when used in session-based forms
* Protected endpoints require authentication and role validation
* Sensitive actions must be logged
* Public verification endpoints must not reveal account information beyond approved certificate data

## 16. Error Standards

Common HTTP statuses:

* 200 OK
* 201 Created
* 400 Bad Request
* 401 Unauthorized
* 403 Forbidden
* 404 Not Found
* 409 Conflict
* 422 Unprocessable Entity
* 500 Internal Server Error

## 17. Implementation Notes

API behavior should be implemented in a consistent pattern across the application: controllers receive requests, services execute rules, repositories persist data, and responses are returned in a standardized format. This keeps the platform easier to test and safer to maintain.
