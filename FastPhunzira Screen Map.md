# FastPhunzira

## Screen Specification & Frontend Development Plan

**Product:** FastPhunzira
**Platform:** Web Application
**Purpose:** Online learning, quizzes, examinations, results, digital certificates, and public certificate verification.

---

# 1. Product Workflow

FastPhunzira follows the core learning journey:

**Learn → Practice → Examine → Pass → Certify → Verify**

The MVP focuses on delivering this complete workflow without unnecessary complexity.

---

# 2. User Roles

## 2.1 Student

Students can:

* Register and log in
* Browse courses
* Enroll in courses
* Study lessons
* Track learning progress
* Take quizzes
* Take final examinations
* View results
* View examination history
* Download certificates
* View their profile
* Update account settings

## 2.2 Administrator

Administrators can:

* Manage students
* Create and manage courses
* Create modules and lessons
* Manage quizzes
* Manage the question bank
* Create and manage examinations
* Review examination attempts
* Manage results
* Issue and manage certificates
* View reports
* View audit logs
* Configure system settings

---

# 3. Public Screens

## P01. Landing Page

**Purpose:** Introduce FastPhunzira and direct visitors into the platform.

### Sections

* Header/navigation
* Hero section
* Platform introduction
* Featured courses
* How FastPhunzira works
* Learning benefits
* Certificate/verification section
* Call-to-action
* Footer

### Primary Actions

* Browse Courses
* Register
* Login
* Verify Certificate

---

## P02. About

### Content

* FastPhunzira introduction
* Mission
* How the platform works
* Learning process
* Certification process

---

## P03. Course Catalogue

### Features

* Search courses
* Filter courses
* Course cards
* Course thumbnail
* Course title
* Short description
* Course status
* Certificate availability
* View Course button

### Future Filters

* Category
* Level
* Duration
* Certificate availability

---

## P04. Course Details

### Display

* Course title
* Description
* Thumbnail
* Course instructor/creator
* Modules
* Lessons
* Course requirements
* Course outcomes
* Exam information
* Certificate information

### Actions

* Enroll
* Login to Enroll

---

## P05. Certificate Verification

### Fields

* Certificate Number
* Verification Code

### Action

**Verify Certificate**

### Example

```text
Certificate Number
FP-2026-000001

Verification Code
XXXXXXXX

[ VERIFY CERTIFICATE ]
```

---

## P06. Verification Result

### Valid Certificate

Display:

* Verification status
* Certificate number
* Student name
* Course name
* Score
* Issue date
* Certificate type
* Verification date

### Invalid Certificate

Display:

```text
Certificate Not Found

The certificate number or verification code
could not be verified.
```

Do not expose internal database IDs or private student information.

---

## P07. Contact

### Content

* Contact information
* Email
* Phone
* Location
* Contact form

---

## P08. Login

### Fields

* Email
* Password
* Remember me

### Actions

* Login
* Forgot Password
* Create Account

---

## P09. Register

### Fields

* Full Name
* Email
* Password
* Confirm Password

### Actions

* Create Account
* Login

---

## P10. Forgot Password

### Flow

```text
Enter Email
      ↓
Send Reset Link
      ↓
Open Reset Link
      ↓
Create New Password
      ↓
Login
```

---

# 4. Student Screens

## S01. Student Dashboard

### Dashboard Cards

* Enrolled Courses
* Courses Completed
* Exams Taken
* Certificates Earned

### Sections

* Continue Learning
* Recent Results
* Recent Certificates
* Course Progress

---

## S02. My Courses

Display:

* Enrolled courses
* Course progress
* Completion percentage
* Last lesson
* Course status

### Actions

* Continue Learning
* View Course

---

## S03. Browse Courses

Similar to the public course catalogue but optimized for authenticated students.

### Actions

* View Course
* Enroll

---

## S04. Course Details

Authenticated version of the course details page.

### Additional Information

* Enrollment status
* Progress
* Completed lessons
* Quiz status
* Final examination status

### Actions

* Start Course
* Continue Learning

---

# 5. Learning Screens

## S05. Course Learning

This is the main learning workspace.

### Layout

```text
-------------------------------------------------
FastPhunzira
-------------------------------------------------

Course Title                    Progress: 45%

-------------------------------------------------
| Course Content |              | Main Content |
|                |              |              |
| Module 1       |              | Lesson Title |
| ✓ Lesson 1     |              |              |
| ✓ Lesson 2     |              | Lesson       |
| → Lesson 3     |              | Content      |
| ○ Lesson 4     |              |              |
|                |              |              |
| Module 2       |              |              |
-------------------------------------------------

[ Previous ]                    [ Next Lesson ]
```

### Features

* Module navigation
* Lesson navigation
* Completion status
* Course progress
* Previous lesson
* Next lesson
* Mark lesson complete

---

## S06. Lesson View

### Display

* Lesson title
* Lesson content
* Video if available
* Downloadable materials
* Lesson navigation

### Actions

* Mark Complete
* Previous
* Next

---

# 6. Quiz Screens

## S07. Quiz

### Display

* Quiz title
* Question number
* Question
* Answer options
* Progress
* Remaining questions

Example:

```text
Quiz: Introduction to ICT

Question 3 of 10

What does CPU stand for?

○ A. Central Processing Unit
○ B. Computer Processing Utility
○ C. Central Program Unit
○ D. Computer Personal Unit

[ Previous ]                    [ Next ]
```

### Rules

The frontend displays the quiz.

The backend controls:

* Attempt limits
* Correct answers
* Scoring
* Time limits
* Attempt status

---

## S08. Quiz Result

### Display

* Quiz title
* Score
* Percentage
* Pass/fail status
* Questions answered
* Correct answers
* Incorrect answers

### Actions

* Retry Quiz
* Continue Course

---

# 7. Examination Screens

## S09. Exam Instructions

Before an examination starts, display:

* Exam title
* Number of questions
* Duration
* Pass mark
* Maximum attempts
* Examination rules
* Important warnings

Example:

```text
FINAL EXAMINATION

Questions: 50
Duration: 60 Minutes
Pass Mark: 50%
Attempts Allowed: 2

IMPORTANT:
• Do not refresh the page unnecessarily.
• Submit your examination before time expires.
• Once submitted, the attempt cannot be changed.

[ START EXAM ]
```

---

## S10. Exam Interface

This is one of the most important screens in the system.

### Layout

```text
-------------------------------------------------
FINAL EXAMINATION

Time Remaining: 42:18
-------------------------------------------------

Question 12 of 50

What is an operating system?

○ A. ...
○ B. ...
○ C. ...
○ D. ...

-------------------------------------------------

Question Navigation

[1] [2] [3] [4] [5] ... [12] ... [50]

[ Previous ]                  [ Next ]

                         [ Submit Exam ]
-------------------------------------------------
```

### Features

* Countdown timer
* Question navigation
* Answer selection
* Progress indicator
* Previous/next navigation
* Submit examination

### Security

The frontend timer is only a display.

The backend must determine whether the examination is still valid.

The backend must also calculate the final score.

---

## S11. Exam Submission

Before final submission:

```text
Submit Examination?

You have answered 47 of 50 questions.

Once submitted, your answers cannot be changed.

[ CANCEL ]     [ SUBMIT EXAM ]
```

If the examination expires, the backend automatically finalizes the attempt according to the configured rules.

---

## S12. Exam Result

### Display

```text
EXAMINATION RESULT

Score
42 / 50

Percentage
84%

Status
PASSED

Time Used
47 Minutes

Certificate
Available
```

### Actions

* View Result
* View Certificate
* Return to Dashboard

If unsuccessful:

* View Result
* Retry if another attempt is available

---

## S13. Exam History

Display:

| Exam             | Date        | Score | Percentage | Status |
| ---------------- | ----------- | ----: | ---------: | ------ |
| ICT Fundamentals | 20 Aug 2026 | 42/50 |        84% | Passed |
| Computer Basics  | 15 Aug 2026 | 31/50 |        62% | Passed |

### Actions

* View Result
* View Certificate where applicable

---

# 8. Certificate Screens

## S14. Certificates

Display all certificates belonging to the student.

### Certificate Card

* Course
* Certificate number
* Issue date
* Status
* Score

### Actions

* View
* Download
* Verify

---

## S15. Certificate Preview

Display a digital certificate preview.

### Certificate Information

* Student name
* Course name
* Certificate number
* Score
* Issue date
* Verification QR code
* Verification URL
* Organization/platform branding
* Authorized signature

### Actions

* Download PDF
* Print
* Verify Certificate

---

# 9. Student Account Screens

## S16. Profile

### Fields

* Full Name
* Email
* Profile photo
* Account status
* Registration date

---

## S17. Account Settings

### Sections

#### Password

* Current password
* New password
* Confirm password

#### Account

* Email preferences
* Security settings
* Logout

---

# 10. Admin Screens

## A01. Admin Dashboard

### Statistics

* Total Students
* Active Students
* Total Courses
* Published Courses
* Total Exams
* Exams Completed
* Certificates Issued

### Recent Activity

* New registrations
* Course enrollments
* Exam submissions
* Certificates issued

---

# 11. Student Management

## A02. Students

### Features

* Search students
* Filter students
* Pagination
* View student
* Activate/deactivate account

### Columns

* Name
* Email
* Status
* Courses
* Exams
* Certificates
* Registered date

---

## A03. Student Details

### Sections

* Personal information
* Enrolled courses
* Learning progress
* Exam attempts
* Results
* Certificates
* Account activity

---

# 12. Course Management

## A04. Courses

### Features

* Search courses
* Filter courses
* Create course
* Edit course
* Publish/unpublish
* Delete/archive course

### Columns

* Course
* Status
* Students
* Lessons
* Exam
* Certificate
* Created date

---

## A05. Create Course

Course creation should be a guided workflow rather than one giant form from the depths of administrative suffering.

### Step 1: Course Information

* Title
* Description
* Short description
* Thumbnail
* Category
* Status

### Step 2: Modules

* Module title
* Description
* Ordering

### Step 3: Lessons

* Lesson title
* Content
* Video URL
* Files
* Ordering

### Step 4: Quiz

* Quiz title
* Questions
* Pass mark
* Time limit
* Attempts

### Step 5: Final Examination

* Exam title
* Questions
* Duration
* Pass mark
* Attempts
* Randomization

### Step 6: Certificate

* Enable certificate
* Certificate type
* Certificate requirements

### Step 7: Preview

Review the complete course.

### Step 8: Publish

Publish the course.

---

## A06. Edit Course

Allow administrators to modify:

* Course information
* Modules
* Lessons
* Quizzes
* Examination
* Certificate configuration

---

## A07. Modules

### Features

* Create module
* Edit module
* Delete module
* Reorder modules
* View lessons

---

## A08. Lessons

### Features

* Create lesson
* Edit lesson
* Delete lesson
* Reorder lessons
* Upload learning materials
* Publish/unpublish

---

# 13. Quiz Management

## A09. Quizzes

Display:

* Quiz
* Course
* Questions
* Pass mark
* Time limit
* Attempts
* Status

### Actions

* Create
* Edit
* View
* Publish
* Delete

---

## A10. Question Bank

The question bank should be reusable.

### Features

* Create question
* Edit question
* Delete question
* Search question
* Filter by course
* Filter by type
* Assign question to quiz/exam

### Question Types

Initial MVP:

* Multiple Choice

Future:

* True/False
* Multiple Answer
* Short Answer
* Essay

### Question Structure

```text
Question
    ↓
Option A
Option B
Option C
Option D
    ↓
Correct Answer
    ↓
Marks
```

---

# 14. Examination Management

## A11. Exams

Display:

* Exam title
* Course
* Questions
* Duration
* Pass mark
* Attempts
* Status

### Actions

* Create
* Edit
* Preview
* Publish
* View attempts

---

## A12. Create Exam

### Step 1: Exam Details

* Title
* Description
* Instructions
* Course

### Step 2: Questions

Select questions from the question bank.

### Step 3: Rules

Configure:

* Duration
* Pass mark
* Attempt limit
* Question count
* Random questions
* Random options

### Step 4: Preview

Show exactly what students will see.

### Step 5: Publish

Make the examination available.

---

## A13. Exam Attempts

Display:

* Student
* Exam
* Started
* Submitted
* Score
* Percentage
* Status

### Attempt Status

* In Progress
* Submitted
* Expired
* Cancelled

Administrators should be able to inspect an attempt without being able to silently alter the student's submitted answers.

---

# 15. Results Management

## A14. Results

### Features

* Search results
* Filter by course
* Filter by exam
* Filter by status
* View result

### Display

* Student
* Exam
* Score
* Percentage
* Pass/fail
* Date

---

# 16. Certificate Management

## A15. Certificates

Display:

* Certificate number
* Student
* Course
* Issue date
* Status

### Actions

* View
* Download
* Verify
* Revoke

---

## A16. Certificate Details

Display:

* Certificate number
* Student
* Course
* Exam
* Score
* Issue date
* Verification token
* Status
* PDF
* Audit information

### Certificate Status

* Valid
* Revoked
* Expired

---

# 17. Reports

## A17. Reports

Initial reports:

### Student Reports

* Total students
* Active students
* Registrations

### Course Reports

* Course enrollments
* Course completion

### Examination Reports

* Exams taken
* Pass rate
* Failure rate
* Average score

### Certificate Reports

* Certificates issued
* Certificates revoked

Reports should be exportable later.

---

# 18. Audit Logs

## A18. Audit Logs

Record important administrative and security actions.

### Example

```text
Administrator
    ↓
Published Course
    ↓
Course ID: 15
    ↓
2026-09-01 10:42
    ↓
IP Address
```

### Actions to Log

* Login
* Logout
* Course creation
* Course update
* Course publication
* Exam creation
* Exam publication
* Exam configuration changes
* Certificate issuance
* Certificate revocation
* User activation/deactivation
* Important security events

---

# 19. System Settings

## A19. Settings

### General

* Platform name
* Platform description
* Logo
* Contact email
* Contact phone

### Certificate

* Certificate prefix
* Certificate template
* Verification URL
* Signature

### Security

* Session settings
* Password rules
* Login attempt rules

### Email

* SMTP configuration
* Sender name
* Sender email

---

# 20. Frontend Development Order

The frontend team should not attempt all 46 screens simultaneously. Humanity has suffered enough from giant unfinished dashboards.

## Sprint 1: Foundation

Build:

1. Landing Page
2. Login
3. Register
4. Student Dashboard
5. Admin Dashboard

---

## Sprint 2: Courses

Build:

6. Course Catalogue
7. Course Details
8. My Courses
9. Course Learning
10. Lesson View

---

## Sprint 3: Quizzes

Build:

11. Quiz
12. Quiz Result

---

## Sprint 4: Examinations

Build:

13. Exam Instructions
14. Exam Interface
15. Exam Submission
16. Exam Result
17. Exam History

---

## Sprint 5: Certificates

Build:

18. Certificates
19. Certificate Preview
20. Certificate Verification
21. Verification Result

---

## Sprint 6: Administration

Build:

22. Students
23. Student Details
24. Courses
25. Create Course
26. Edit Course
27. Modules
28. Lessons
29. Quizzes
30. Question Bank
31. Exams
32. Create Exam
33. Exam Attempts
34. Results
35. Certificates
36. Certificate Details
37. Reports
38. Audit Logs
39. Settings

The remaining public/account screens can be completed alongside the relevant modules.

---

# 21. Shared Frontend Components

The frontend developer should build reusable components instead of creating every button as though it were a unique species.

## Layout

* Header
* Sidebar
* Footer
* Breadcrumbs
* Page header

## Navigation

* Navbar
* Student sidebar
* Admin sidebar
* Mobile navigation

## Forms

* Text input
* Password input
* Select
* Checkbox
* Radio button
* File upload
* Rich text editor

## Data

* Table
* Pagination
* Search
* Filters
* Empty state
* Loading state

## Feedback

* Alert
* Toast
* Modal
* Confirmation dialog
* Error message
* Success message

## Learning

* Course card
* Progress bar
* Module list
* Lesson item
* Quiz question
* Exam question
* Exam timer
* Question navigator

## Certification

* Certificate card
* Certificate preview
* Verification status
* QR display

---

# 22. Navigation Structure

## Public

```text
Home
├── About
├── Courses
│   └── Course Details
├── Verify Certificate
├── Contact
├── Login
└── Register
```

## Student

```text
Dashboard
├── My Courses
├── Browse Courses
├── Exams
│   ├── Exam History
│   └── Results
├── Certificates
├── Profile
└── Settings
```

## Admin

```text
Dashboard
├── Students
├── Courses
│   ├── All Courses
│   ├── Create Course
│   ├── Modules
│   └── Lessons
├── Quizzes
├── Question Bank
├── Exams
│   ├── All Exams
│   ├── Create Exam
│   └── Attempts
├── Results
├── Certificates
├── Reports
├── Audit Logs
└── Settings
```

---

# 23. Frontend/Backend Contract

Before implementing each screen, both developers must agree on:

```text
Screen
    ↓
Required Data
    ↓
API/Route
    ↓
Request
    ↓
Validation
    ↓
Response
    ↓
UI State
```

Example:

```text
Exam Interface
       ↓
GET /api/exams/{id}
       ↓
ExamService
       ↓
Database
       ↓
Exam Data
       ↓
Frontend
```

For submission:

```text
Student
   ↓
Submit Exam
   ↓
POST /api/exams/{id}/submit
   ↓
ExamService
   ↓
Validate Attempt
   ↓
Validate Time
   ↓
Calculate Score
   ↓
Save Result
   ↓
Determine Pass/Fail
   ↓
Certificate Eligibility
   ↓
Response
```

The browser should never be trusted with the final result.

---

# 24. MVP Security Requirements

The system must implement:

* Password hashing
* Prepared SQL statements
* CSRF protection
* Session security
* Authentication middleware
* Role-based authorization
* Input validation
* Output escaping
* Secure file uploads
* Rate limiting where appropriate
* Audit logging
* Server-side exam timing
* Server-side exam scoring
* Attempt limits
* Database transactions
* Secure certificate verification

Never store:

* Plaintext passwords
* Exam answers in client-side JavaScript
* Sensitive internal information in public verification responses

---

# 25. Certificate Verification Flow

```text
Student Passes Exam
        ↓
Backend Checks Eligibility
        ↓
Certificate Generated
        ↓
Unique Certificate Number
        ↓
Verification Token
        ↓
QR Code Generated
        ↓
PDF Certificate
        ↓
Student Downloads Certificate
        ↓
Third Party Scans QR
        ↓
Public Verification Page
        ↓
Certificate Status
```

Example verification URL:

```text
/verify/FP-2026-000001
```

---

# 26. V1 Scope

The first production version should contain:

### Authentication

* Registration
* Login
* Logout
* Password reset
* Roles

### Learning

* Courses
* Modules
* Lessons
* Enrollment
* Progress tracking

### Assessment

* Quizzes
* Question bank
* Online exams
* Exam timer
* Attempt limits
* Automatic scoring
* Results

### Certification

* Certificate generation
* PDF download
* QR verification
* Public verification
* Certificate status

### Administration

* Student management
* Course management
* Quiz management
* Question management
* Exam management
* Result management
* Certificate management
* Audit logs
* Basic reports
* Settings

---

# 27. Explicitly Deferred

Do not add these to the first version:

* Payments
* Subscriptions
* Instructor marketplace
* Live classes
* AI tutor
* AI-generated courses
* Messaging
* Forums
* Mobile application
* Gamification
* Advanced analytics
* AI proctoring
* Complex notification infrastructure

These can be evaluated after the core product has been tested with real users.

---

# 28. Definition of Done

A screen is not considered complete merely because it looks attractive in a browser. Humanity has already invented enough screenshots pretending to be software.

A screen is complete when:

* UI is responsive
* Required backend route exists
* Validation works
* Loading state exists
* Empty state exists
* Error state exists
* Success state exists
* Authorization is enforced
* Data is persisted correctly
* Security requirements are satisfied
* Frontend and backend have been tested together
* Code has been reviewed
* Changes are committed to Git
* Documentation is updated where necessary

---

# 29. Development Rule

The team should build **feature by feature**, not frontend versus backend in isolation.

Recommended workflow:

```text
Feature Planning
      ↓
Database Design
      ↓
Backend API/Route
      ↓
Frontend UI
      ↓
Integration
      ↓
Testing
      ↓
Code Review
      ↓
Merge
```

Example:

```text
Course Feature
      ↓
courses table
      ↓
CourseService
      ↓
CourseController
      ↓
Course API
      ↓
Course Catalogue UI
      ↓
Course Details UI
      ↓
Integration Testing
      ↓
Pull Request
      ↓
Merge
```

This approach keeps the two developers synchronized and prevents the classic tragedy where the frontend developer builds a beautiful interface for an API that does not exist.

# 30. Recommended V1 Architecture

```text
                    FASTPHUNZIRA
                         │
          ┌──────────────┴──────────────┐
          │                             │
       FRONTEND                       BACKEND
          │                             │
 HTML/CSS/JS                    PHP 8.2+
          │                             │
          │                    Controllers
          │                             │
          │                       Services
          │                             │
          │                     Repositories
          │                             │
          │                         Models
          │                             │
          └──────────────┬──────────────┘
                         │
                       MySQL
                         │
             ┌───────────┼───────────┐
             │           │           │
          Courses      Exams     Certificates
             │           │           │
          Lessons      Results    Verification
             │           │           │
          Quizzes     Attempts      QR/PDF
```

The architecture should remain a **modular monolith** for V1.

There is no reason to introduce microservices when two developers are still trying to make the login button and exam timer agree on reality.

# 31. V1 Success Condition

FastPhunzira V1 is complete when a new student can:

```text
Register
   ↓
Login
   ↓
Browse Course
   ↓
Enroll
   ↓
Study Lessons
   ↓
Complete Quiz
   ↓
Take Final Exam
   ↓
Receive Result
   ↓
Pass
   ↓
Receive Certificate
   ↓
Download Certificate
   ↓
Someone Else Verifies Certificate
```

And an administrator can:

```text
Login
   ↓
Create Course
   ↓
Create Modules
   ↓
Create Lessons
   ↓
Create Questions
   ↓
Create Quiz
   ↓
Create Exam
   ↓
Publish Course
   ↓
Monitor Students
   ↓
Review Results
   ↓
Manage Certificates
```

That complete loop is the actual product. Everything else is decoration until this works reliably.
