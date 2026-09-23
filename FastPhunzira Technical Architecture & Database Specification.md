# FastPhunzira Technical Architecture & Database Specification

## 1. System Overview

FastPhunzira is a web-based learning, examination, and certification platform.

The system allows users to:

* Register and authenticate
* Browse courses
* Enroll in courses
* Study lessons
* Complete quizzes
* Take online examinations
* Receive examination results
* Earn certificates
* Verify certificates online

The system will initially be developed as a **modular PHP/MySQL web application**.

---

# 2. Recommended Technology Stack

## Frontend

* HTML5
* CSS3
* JavaScript
* Bootstrap or a lightweight custom CSS system
* Fetch API/AJAX where required

## Backend

* PHP 8.2+
* PDO
* Composer
* PHPMailer where email is required
* Dompdf for certificate PDF generation
* QR code library/API for certificate verification

## Database

* MySQL 8+
* InnoDB storage engine
* Foreign keys
* Transactions
* Proper indexing

## Development

* Git
* GitHub
* Local development using WAMP/XAMPP
* `.env` configuration

## Production

* Linux hosting/server
* Apache or Nginx
* PHP
* MySQL
* HTTPS

---

# 3. High-Level Architecture

```text
                    FASTPHUNZIRA
                         │
              ┌──────────┴──────────┐
              │                     │
          Frontend               Backend
              │                     │
      HTML/CSS/JavaScript      PHP Application
              │                     │
              └──────────┬──────────┘
                         │
                    Application Core
                         │
          ┌──────────────┼──────────────┐
          │              │              │
       Services        Models        Security
          │              │              │
          └──────────────┼──────────────┘
                         │
                    MySQL Database
                         │
          ┌──────────────┼──────────────┐
          │              │              │
       Course Data   Exam Data    Certificate Data
```

---

# 4. Application Architecture

FastPhunzira should use a simple layered architecture.

```text
Presentation Layer
        ↓
Controllers
        ↓
Services / Business Logic
        ↓
Repositories / Models
        ↓
Database
```

## Presentation Layer

Responsible for:

* HTML
* CSS
* JavaScript
* Forms
* Dashboards
* User interaction

## Controllers

Responsible for:

* Receiving requests
* Validating request data
* Calling services
* Returning responses

Controllers should not contain large amounts of business logic.

## Services

Responsible for business rules.

Examples:

```text
AuthService
CourseService
EnrollmentService
QuizService
ExamService
CertificateService
VerificationService
```

## Models / Repositories

Responsible for database interaction.

Examples:

```text
UserRepository
CourseRepository
ExamRepository
CertificateRepository
```

## Security Layer

Responsible for:

* Authentication
* Authorization
* CSRF protection
* Input validation
* Password hashing
* Session security
* Access control

---

# 5. Project Structure

Recommended structure:

```text
fastphunzira/
│
├── app/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CourseController.php
│   │   ├── QuizController.php
│   │   ├── ExamController.php
│   │   ├── CertificateController.php
│   │   └── AdminController.php
│   │
│   ├── Models/
│   │
│   ├── Repositories/
│   │
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── CourseService.php
│   │   ├── ExamService.php
│   │   ├── CertificateService.php
│   │   └── VerificationService.php
│   │
│   ├── Middleware/
│   │
│   ├── Validation/
│   │
│   ├── Security/
│   │
│   └── Helpers/
│
├── config/
│
├── database/
│   ├── migrations/
│   └── seeders/
│
├── public/
│   ├── index.php
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/
│
├── resources/
│   └── views/
│       ├── auth/
│       ├── student/
│       ├── courses/
│       ├── exams/
│       ├── certificates/
│       └── admin/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
│   ├── certificates/
│   ├── course-files/
│   ├── logs/
│   └── temp/
│
├── tests/
│
├── vendor/
│
├── .env
├── .env.example
├── composer.json
└── README.md
```

---

# 6. Database Architecture

The database should be divided conceptually into several domains.

```text
USERS
  │
  ├── Roles
  └── Profiles

LEARNING
  │
  ├── Courses
  ├── Modules
  ├── Lessons
  └── Enrollments

ASSESSMENT
  │
  ├── Quizzes
  ├── Questions
  ├── Exams
  ├── Attempts
  └── Answers

CERTIFICATION
  │
  ├── Certificates
  └── Verification

SYSTEM
  │
  ├── Notifications
  ├── Audit Logs
  └── Settings
```

---

# 7. Core Database Tables

## 7.1 users

Stores authentication accounts.

```text
users
-------------------------
id
uuid
name
email
password_hash
role_id
status
email_verified_at
last_login_at
created_at
updated_at
```

Important rules:

* `email` must be unique
* Never store plain-text passwords
* Use password hashing
* `uuid` can be exposed publicly instead of sequential IDs

---

# 7.2 roles

```text
roles
-------------------------
id
name
description
created_at
updated_at
```

Initial roles:

```text
Admin
Student
```

Future roles can include:

```text
Instructor
Content Manager
Exam Manager
Certificate Manager
```

But don't create them until they are actually needed.

---

# 7.3 courses

```text
courses
-------------------------
id
uuid
title
slug
description
short_description
thumbnail
status
pass_mark
certificate_enabled
created_by
published_at
created_at
updated_at
```

Example:

```text
Computer Fundamentals
ICT Basics
Introduction to Networking
Digital Literacy
```

---

# 7.4 course_modules

```text
course_modules
-------------------------
id
course_id
title
description
sort_order
created_at
updated_at
```

Relationship:

```text
Course
  │
  ├── Module 1
  ├── Module 2
  └── Module 3
```

---

# 7.5 lessons

```text
lessons
-------------------------
id
module_id
title
slug
content
video_url
file_path
sort_order
status
created_at
updated_at
```

A lesson may contain:

* Text
* Images
* PDF
* Video link
* Downloadable material

---

# 7.6 enrollments

```text
enrollments
-------------------------
id
student_id
course_id
status
enrolled_at
completed_at
created_at
updated_at
```

Possible statuses:

```text
active
completed
cancelled
```

Unique constraint:

```text
student_id + course_id
```

A student should not be enrolled in the same course twice.

---

# 7.7 lesson_progress

```text
lesson_progress
-------------------------
id
student_id
lesson_id
completed
completed_at
created_at
updated_at
```

This allows the platform to track course progress.

---

# 8. Quiz Architecture

## quizzes

```text
quizzes
-------------------------
id
course_id
module_id
title
description
pass_mark
time_limit
attempt_limit
status
created_at
updated_at
```

## quiz_questions

```text
quiz_questions
-------------------------
id
quiz_id
question_id
sort_order
created_at
```

## questions

A centralized question bank is preferable.

```text
questions
-------------------------
id
course_id
question_text
question_type
marks
explanation
status
created_at
updated_at
```

Question types:

```text
multiple_choice
true_false
short_answer
```

Start with **multiple choice and true/false**.

Short-answer marking introduces additional complexity and should come later.

---

# 9. Question Options

```text
question_options
-------------------------
id
question_id
option_text
is_correct
sort_order
created_at
```

Example:

```text
Question:
What does CPU stand for?

A. Central Processing Unit
B. Computer Personal Unit
C. Central Program Utility
D. Control Processing User
```

Only the backend determines which option is correct.

---

# 10. Exam Architecture

## exams

```text
exams
-------------------------
id
course_id
title
description
instructions
duration_minutes
pass_mark
attempt_limit
question_count
randomize_questions
randomize_options
status
created_at
updated_at
```

---

# 11. exam_questions

```text
exam_questions
-------------------------
id
exam_id
question_id
marks
sort_order
created_at
```

This allows questions to be reused without duplicating the actual question.

---

# 12. exam_attempts

This is one of the most important tables in FastPhunzira.

```text
exam_attempts
-------------------------
id
uuid
exam_id
student_id
started_at
submitted_at
expires_at
score
percentage
passed
status
created_at
updated_at
```

Possible statuses:

```text
in_progress
submitted
expired
cancelled
```

---

# 13. exam_answers

```text
exam_answers
-------------------------
id
attempt_id
question_id
selected_option_id
answer_text
is_correct
marks_awarded
answered_at
created_at
updated_at
```

The backend calculates:

```text
marks_awarded
is_correct
score
percentage
passed
```

The browser does not.

---

# 14. Certificate Architecture

## certificates

```text
certificates
-------------------------
id
uuid
certificate_number
student_id
course_id
exam_attempt_id
certificate_type
issued_at
status
pdf_path
verification_token
created_at
updated_at
```

Certificate statuses:

```text
valid
revoked
expired
```

---

# 15. Certificate Verification

Public verification should not expose internal database IDs.

Example:

```text
https://fastphunzira.com/verify/FP-2026-000001
```

Verification process:

```text
Certificate Number
       ↓
Verification Service
       ↓
Certificate Database
       ↓
Check Status
       ↓
Return Public Information
```

Public information can include:

```text
Student Name
Course Name
Certificate Number
Issue Date
Status
```

Do not expose:

* Passwords
* Internal IDs
* Exam answers
* Private student information
* Administrative information

---

# 16. Audit Logs

Important actions should be recorded.

## audit_logs

```text
audit_logs
-------------------------
id
user_id
action
entity_type
entity_id
ip_address
user_agent
metadata
created_at
```

Examples:

```text
LOGIN
COURSE_CREATED
COURSE_PUBLISHED
EXAM_STARTED
EXAM_SUBMITTED
CERTIFICATE_ISSUED
CERTIFICATE_REVOKED
USER_SUSPENDED
```

This becomes particularly important for examinations and certificates.

---

# 17. Notifications

Optional for the first MVP, but the architecture should allow it.

## notifications

```text
notifications
-------------------------
id
user_id
type
title
message
read_at
created_at
```

Possible notifications:

```text
Course enrollment
Exam result
Certificate issued
Important announcement
```

Email notifications can be added later.

---

# 18. System Settings

## settings

```text
settings
-------------------------
id
setting_key
setting_value
setting_type
updated_at
```

Possible settings:

```text
site_name
site_logo
certificate_prefix
default_pass_mark
support_email
certificate_verification_url
```

Avoid putting configurable business values directly into PHP code.

---

# 19. Importan
