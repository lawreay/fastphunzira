# Database Design

## 1. Goal

The database design supports the core FastPhunzira workflows: user access, course delivery, assessment, progress tracking, certification, and verification.

## 2. Database Type

* Relational database: MySQL 8+
* Storage engine: InnoDB
* Transactional consistency for critical flows
* Indexed relationships for course, exam, and certificate queries

## 3. Core Domain Model

```text
users
 ├── enrollments
 │    └── courses
 ├── quiz_attempts
 │    └── quiz_questions
 ├── exam_attempts
 │    └── exams
 ├── certificates
 └── audit_logs
```

## 4. Main Tables

### users
Stores authentication and profile information.

Key fields:
* id
* full_name
* email
* password_hash
* status
* role_id or role mapping
* created_at
* updated_at
* deleted_at

### roles
Defines platform access roles.

Key fields:
* id
* name
* description
* created_at

### permissions
Defines granular permissions.

Key fields:
* id
* permission_key
* description

### user_roles
Maps users to roles.

Key fields:
* user_id
* role_id
* created_at

### courses
Stores course definitions.

Key fields:
* id
* title
* slug
* description
* status
* created_by
* created_at
* updated_at

### course_modules
Organizes course content into modules.

Key fields:
* id
* course_id
* title
* description
* sort_order

### lessons
Represents instruction content by module.

Key fields:
* id
* module_id
* title
* summary
* content
* video_url
* file_path
* sort_order

### enrollments
Tracks student course registration.

Key fields:
* id
* user_id
* course_id
* status
* enrolled_at
* completed_at

### quizzes
Stores quiz metadata and configuration.

Key fields:
* id
* lesson_id
* title
* total_marks
* time_limit
* passing_score
* status

### questions
Stores the shared question bank used by quizzes and exams.

Key fields:
* id
* question_text
* question_type
* points
* status
* created_at
* updated_at

### quiz_questions
Maps questions to a specific quiz and preserves ordering.

Key fields:
* id
* quiz_id
* question_id
* sort_order
* created_at

### exam_questions
Maps questions to a specific exam and preserves scoring or ordering details.

Key fields:
* id
* exam_id
* question_id
* marks
* sort_order
* created_at

### question_options
Stores answer choices for multiple-choice questions.

Key fields:
* id
* question_id
* option_text
* is_correct
* sort_order

### quiz_attempts
Stores quiz attempts.

Key fields:
* id
* user_id
* quiz_id
* started_at
* submitted_at
* score
* passed
* status

### exams
Stores final exam metadata.

Key fields:
* id
* course_id
* title
* time_limit
* passing_score
* total_questions
* status

### exam_attempts
Stores exam attempt records.

Key fields:
* id
* user_id
* exam_id
* started_at
* expires_at
* submitted_at
* score
* percentage
* passed
* status

### exam_answers
Stores student responses for each attempt.

Key fields:
* id
* exam_attempt_id
* question_id
* selected_option_id
* answer_text
* is_correct

### certificates
Stores issued certificates and verification data.

Key fields:
* id
* user_id
* course_id
* exam_id
* certificate_number
* verification_code
* issued_at
* status
* file_path

### audit_logs
Tracks important administrative and security events.

Key fields:
* id
* user_id
* action
* entity_type
* entity_id
* ip_address
* user_agent
* details
* created_at

## 5. Relationships

* One user may have many enrollments
* One course may have many modules
* One course may have many lessons and exams
* One question may belong to many quizzes and many exams through join tables
* One quiz may have many questions via `quiz_questions`
* One exam may have many questions via `exam_questions`
* One exam may have many attempts
* One user may have many certificate records
* One exam attempt may have many answer records
* One user may have many audit log records

### Question Bank Model

This project should use a reusable question bank rather than attaching a question directly to both quiz and exam records. The shared question record keeps the database normalized and makes both quiz and exam authoring easier to manage.

## 6. Indexing Strategy

Recommended indexes:
* users.email
* users.status
* enrollments.user_id + course_id
* exams.course_id
* exam_attempts.user_id + exam_id
* certificates.certificate_number
* certificates.verification_code
* audit_logs.created_at

## 7. Constraints and Rules

* Email addresses should be unique
* Course enrollment should be unique per user and course
* Attempt records should prevent duplicate active attempts where necessary
* Exam score and percentage should be calculated server-side
* Certificate numbers should be unique and immutable once issued
* Soft deletes should be used for user and content records where appropriate

## 8. Data Integrity Principles

* Never trust client-side data for scoring or eligibility
* Validate foreign keys at the application level and database level
* Use transactions for final exam submission and certificate issuance
* Store verification data in a way that supports public lookup without exposing private user data

## 9. Example ERD Concept

```text
users ──< enrollments >── courses
users ──< exam_attempts >── exams
users ──< certificates
courses ──< exams
exams ──< exam_attempts
exam_attempts ──< exam_answers
questions ──< question_options
```

## 10. Notes for Implementation

The database should be defined through migration files and should be supported by seed data for roles, permissions, and basic default admin content. The schema should be reviewed alongside the API and security requirements before implementation begins.
