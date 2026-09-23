# System Architecture

## 1. Purpose

This document describes the architecture of FastPhunzira, including the frontend, backend, data layer, security model, and deployment assumptions. The goal is to keep the solution simple, modular, and maintainable while supporting the educational workflow.

## 2. High-Level Architecture

```text
Browser
   ↓
Web Frontend (HTML, CSS, JS)
   ↓
PHP Web Application
   ↓
Controllers
   ↓
Services
   ↓
Repositories / Data Access
   ↓
MySQL Database
```

## 3. Architecture Style

FastPhunzira will use a layered architecture with a separation between:

* Presentation
* Application logic
* Business services
* Data access
* Security

This model helps the team keep code readable, testable, and easier to extend.

## 4. Components

### 4.1 Frontend

The frontend is responsible for all browser-facing screens and interactions.

Responsibilities:
* Render pages and forms
* Display course, exam, and certificate data
* Submit form data to backend endpoints
* Handle client-side validation and simple UX patterns

Recommended stack:
* HTML5
* CSS3
* JavaScript
* Bootstrap or lightweight custom styling

### 4.2 Backend

The backend is built with PHP and serves the primary business logic of the system.

Responsibilities:
* User authentication
* Role and permission checks
* Course, quiz, exam, and certificate logic
* Validation and security enforcement
* Database interaction

### 4.3 Business Services

Services coordinate business rules without embedding SQL in the controllers.

Examples:
* AuthService
* CourseService
* EnrollmentService
* QuizService
* ExamService
* CertificateService
* VerificationService

### 4.4 Repositories

Repositories isolate all raw database logic and prepared statements.

Examples:
* UserRepository
* CourseRepository
* EnrollmentRepository
* ExamRepository
* CertificateRepository

### 4.5 Database

MySQL is used as the relational persistence layer for users, course content, assessments, results, and certificates.

## 5. Request Flow

```text
User submits form
   ↓
Controller receives request
   ↓
Controller validates input
   ↓
Service executes business rules
   ↓
Repository reads or writes data
   ↓
Database returns result
   ↓
Controller prepares response
   ↓
View renders HTML or JSON
```

## 6. Authentication and Authorization

The platform will use server-side authentication with session-based access control.

Core principles:
* Passwords are hashed before storage
* User roles determine access rights
* Admin-only actions are restricted by permission checks
* Session expiration is enforced
* CSRF protection is required for all state-changing forms

## 7. File and Media Storage

The system may store:
* Course assets
* Lesson content files
* Certificate PDFs
* Uploaded user files if needed later

Recommended storage structure:
* public/assets
* storage/certificates
* storage/uploads
* storage/logs

## 8. Certificate and Verification Flow

```text
Student completes required course and exam
   ↓
Eligibility is calculated by service logic
   ↓
Certificate is generated
   ↓
Certificate record is saved with verification token
   ↓
Public verification endpoint checks certificate number and token
   ↓
Result is displayed publicly without exposing sensitive data
```

## 9. Security Layers

The architecture includes several security layers:

* Input validation
* Prepared SQL statements
* XSS escaping in views
* CSRF validation on POST actions
* Permission checks before sensitive actions
* Audit logging for key administrative events
* Controlled certificate verification endpoints

## 10. Deployment Architecture

For the initial deployment model:

```text
Browser
   ↓
Apache or Nginx
   ↓
PHP Application
   ↓
MySQL Database
```

Production assumptions:
* Linux server or local hosting plan
* PHP runtime configured with required extensions
* MySQL 8+
* HTTPS enabled
* Environment variables for secrets and config
* log and storage directories writable by the app

## 11. Scalability Considerations

FastPhunzira is designed for controlled scale-up. The current architecture supports:

* modular feature growth
* new repositories and services
* additional roles and permissions
* API expansion if a frontend layer is later separated

The system should remain simple enough for a small team to maintain while still supporting future growth.
