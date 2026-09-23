# FastPhunzira Authoritative Specification

## 1. Purpose

This document is the single source of truth for the FastPhunzira product specification. It consolidates the requirements, data model, screen flow, API contract, exam behavior, certification rules, and security constraints described across the planning documents in this repository.

The numbered documents in this folder are useful working references, but this file defines the canonical product direction. Any future implementation should align to this specification before diverging into separate design decisions.

## 2. Product Summary

FastPhunzira is a web-based learning, assessment, and certification platform built for students and administrators. The platform supports course discovery, enrollment, lesson consumption, assessment, result tracking, and digital certificate issuance.

The product is designed around a simple learner journey:

```text
Register → Login → Browse Courses → Enroll → Learn → Complete Quiz → Take Exam → Receive Results → Earn Certificate → Verify Certificate
```

## 3. Product Goal

The goal of FastPhunzira is to provide a simple, secure, and maintainable online education workflow that combines learning, assessment, and certification in one system.

## 4. MVP Scope

The MVP includes:

* student registration and login
* student and admin roles
* course catalogue and course detail pages
* enrollment workflow
* lesson access and module navigation
* quiz flow
* final exam flow
* score calculation and results page
* certificate generation for successful learners
* public certificate verification
* basic admin dashboard and management screens

### Explicitly out of scope for MVP

* native mobile app
* enterprise multi-tenancy
* real-time chat or live classroom sessions
* advanced AI features
* complex marketplace or commerce functionality
* large-scale analytics beyond basic administration and reporting

## 5. Target Users

### Student
A student can:

* register an account
* log in and out
* browse courses
* enroll in a course
* access lesson content
* complete quizzes
* take final examinations
* view result summaries
* receive certificates when eligible
* view public or personal certificate information as appropriate

### Administrator
An administrator can:

* manage users
* create and update courses
* publish or archive content
* add lessons and modules
* create and manage quizzes and exams
* review exam attempts and results
* issue and revoke certificates when required
* review audit logs and platform activity

## 6. Functional Requirements

### 6.1 Authentication and User Management

* User registration must validate full name, email, password, and confirmation
* Passwords must be hashed before storage
* Login and logout must be session-based and protected
* Password reset flows must use secure tokens and expiry rules
* Users must be assigned a role and access must be enforced by server-side authorization

### 6.2 Course and Enrollment

* Admins can create, edit, and publish courses
* Courses can include modules and lessons
* Students can browse course listings and view course details
* Students can enroll only after successful authentication
* Enrollment records must be unique per user and course
* Course access must be restricted based on enrollment status and role

### 6.3 Learning Content

* Lessons should be displayed with clear navigation and course context
* Lesson access should be role-aware and course-aware
* Course progress may be tracked, but this does not require advanced analytics in MVP

### 6.4 Assessment

* Quizzes and final exams must support score tracking
* A timer may be configured for exam attempts
* A student must not be able to submit a duplicate or stale attempt
* Server-side validation must determine pass/fail status
* Results must be stored with attempt metadata, score, and percentage

### 6.5 Certificates

* Certificates are generated only if the student meets all eligibility rules
* Certificate numbers must be unique
* Public verification requires a valid certificate number and verification code
* Public verification must not reveal private account data beyond approved certificate fields

### 6.6 Admin Capability

* Admins can manage course content and student activity
* Admins can view result attempts and certificate status
* Admins can review operational logs for audit and compliance

## 7. Non-Functional Requirements

* Keep the system secure against SQL injection, CSRF, XSS, and unauthorized access
* Use prepared statements and server-side validation consistently
* Keep the architecture maintainable with clear separation between controller, service, repository, and database layers
* Provide responsive screens for desktop and mobile viewing
* Preserve documentation and release tracking as a development practice
* Keep code and data model consistent with the specification

## 8. User Flows

### 8.1 Student Registration

```text
Landing page → Register → Validate inputs → Create account → Redirect to login → Login → Dashboard
```

### 8.2 Enrollment and Learning

```text
Browse catalogue → View course → Enroll → Access modules → Read lessons → Complete quiz → Continue course
```

### 8.3 Exam Attempt

```text
Open exam → Start attempt → Timer begins → Answer questions → Submit → Validate attempt → Calculate score → Finalize result
```

### 8.4 Certificate Issuance

```text
Pass required course/exam → Eligibility check → Generate certificate → Save verification data → Make certificate available → Public verification possible
```

### 8.5 Admin Flow

```text
Login as admin → Dashboard → Manage courses/exams/student records → Review attempts/results → Issue or review certificates
```

## 9. Role Model

### Student
* limited to own account, own enrollments, own attempt records, and own certificates
* may view course content only after enrollment or access is granted
* may not modify admin-managed content or system configuration

### Admin
* full access to course management, exam management, result review, and certificate operations
* can access logs and operational dashboards
* can manage roles and sensitive system settings when implemented

## 10. System Architecture

The system follows a layered architecture:

```text
Browser
   ↓
Frontend (HTML, CSS, JavaScript)
   ↓
PHP Application
   ↓
Controllers
   ↓
Services
   ↓
Repositories / Data Access
   ↓
MySQL Database
```

### Responsibilities

* Controllers: request handling and responses
* Services: business logic, validation, workflow orchestration
* Repositories: raw SQL and database access with prepared statements
* Database: persistent storage of users, courses, assessments, results, certificates, and audit data

## 11. Database Domains

The system should use a relational MySQL schema with the following core domains:

### 11.1 Users and access
* users
* roles
* permissions
* user_roles

### 11.2 Learning content
* courses
* course_modules
* lessons
* enrollments

### 11.3 Assessment
* quizzes
* questions
* question_options
* quiz_attempts
* exams
* exam_attempts
* exam_answers

### 11.4 Certification and verification
* certificates

### 11.5 Audit and platform activity
* audit_logs

### Critical database rules

* email must be unique in the users table
* a student cannot have duplicate active enrollment for the same course
* an exam attempt must have a valid user and exam relationship
* certificate numbers must be unique and immutable after issue
* score and result data must be calculated by the backend, not by the browser

## 12. API Surface

The repository defines a logical API layer. The core expected endpoints include:

### Authentication
* POST /api/auth/register
* POST /api/auth/login
* POST /api/auth/logout
* POST /api/auth/forgot-password
* POST /api/auth/reset-password

### Courses and enrollment
* GET /api/courses
* GET /api/courses/{id}
* POST /api/courses
* PUT /api/courses/{id}
* DELETE /api/courses/{id}
* POST /api/courses/{id}/enroll
* GET /api/users/me/enrollments

### Lessons and quizzes
* GET /api/courses/{courseId}/lessons
* GET /api/lessons/{id}
* GET /api/quizzes/{id}
* POST /api/quizzes/{id}/attempts
* POST /api/quizzes/attempts/{id}/submit

### Exams
* GET /api/exams/{id}
* POST /api/exams/{id}/attempts
* PUT /api/exam-attempts/{id}/answers
* POST /api/exam-attempts/{id}/submit
* GET /api/users/me/exam-attempts

### Results and certificates
* GET /api/results/{attemptId}
* GET /api/users/me/results
* GET /api/certificates/verify
* GET /api/users/me/certificates
* POST /api/certificates/generate

### Admin
* GET /api/admin/students
* GET /api/admin/courses
* GET /api/admin/exam-attempts
* GET /api/admin/audit-logs

## 13. Screen Specification Summary

### Public screens
* Landing page
* About page
* Course catalogue
* Course details
* Certificate verification page
* verification result page
* contact page
* login
* register
* forgot password

### Student screens
* dashboard
* my courses
* lesson view
* quiz interface
* exam interface
* results view
* certificates area
* profile settings

### Admin screens
* admin dashboard
* course management
* student management
* exam management
* attempt and result review
* certificate management
* system settings and logs

## 14. Exam Engine Rules

The exam engine is security-critical and must follow strict server-side rules:

* only valid users may start an exam attempt
* one active attempt per user per exam unless retakes are intentionally configured
* time limit is enforced by the server
* responses must be validated against valid question IDs and allowed options
* exam attempts must be finalized only once
* score must be calculated by the backend
* pass/fail result must be stored as part of the official attempt record
* certificate eligibility must use the official result, not the browser state

## 15. Certificate Rules

* Certificates are issued only when fulfillment conditions are met
* Each certificate gets a unique certificate number
* A verification code is generated and securely stored
* Public verification can validate the certificate without exposing private data
* Revocation or reissuance should be managed by admin policy

## 16. Security Requirements

This project must enforce:

* password hashing
* session security and expiry
* CSRF for form and state-changing actions
* XSS prevention in output rendering
* SQL injection prevention with prepared statements
* authorization checks for protected routes
* audit logging for key actions and admin events
* secure handling of certificate verification and exam submission
* server-side validation of all critical actions

## 17. Consistency Review of Current Documents

The current documentation set is rich and mostly coherent, but the repository contains overlapping historical documents. The strongest alignment is as follows:

### Documents that align well
* Requirements and roadmap
* Architecture and database design
* API and screen flow
* Exam engine and certificate rules
* Security and admin model

### Overlap to consolidate later
* FastPhunzira Technical Architecture & Database Specification.md overlaps with 03 and 04
* FastPhunzira Screen Map.md overlaps with 06
* Recommended FastPhunzira Documentation Set.md is a meta-document explaining the documentation model rather than a product specification

## 18. Implementation Priority

The implementation sequence should be:

1. Requirements
2. Architecture
3. Database schema
4. API contract
5. Screens
6. User flows
7. Exam engine
8. Certificate system
9. Security hardening
10. Admin tooling and reporting

This ordering matches the design structure already present in the repository and prevents parallel work from conflicting.

## 19. Final Decision

The product is already well-specified enough to begin implementation, as long as the team treats this document as the source of truth and resolves the legacy overlap documents after implementation starts. The goal is not to add more documentation; the goal is to reduce ambiguity and align implementation with a single authoritative spec.
