# Screen Specification

## 1. Product Workflow

FastPhunzira follows the learning lifecycle:

```text
Learn → Practice → Examine → Pass → Certify → Verify
```

## 2. Roles

### Student
* Register and log in
* Browse and enroll in courses
* Access lessons and content
* Complete quizzes and final exams
* See results and progress
* Download or view certificates

### Administrator
* Manage students and content
* Create courses and lessons
* Create exam and quiz structures
* Review submissions and results
* Issue and manage certificates
* View platform reports and logs

## 3. Public Screens

### P01. Landing Page
Sections:
* Header and navigation
* Hero section
* Benefits and platform overview
* Featured courses
* How it works
* Certificate verification CTA
* Footer

### P02. About Page
Contains:
* Mission and purpose
* Education model
* Certification process
* Trust and transparency information

### P03. Course Catalogue
Features:
* Search and filter
* Course cards
* Thumbnail and summary
* Enrollment status
* CTA buttons

### P04. Course Details
Displays:
* Course title and summary
* Syllabus
* Module and lesson breakdown
* Outcomes
* Enrollment CTA

### P05. Certificate Verification
Fields:
* Certificate number
* Verification code

Action:
* Verify certificate

### P06. Verification Result
If valid:
* Student name
* Course name
* Issue date
* Score and result
* Certificate status

If invalid:
* Show a generic not found message

### P07. Contact Page
* Contact information
* Email and phone
* Contact form

### P08. Login
Fields:
* Email
* Password
* Remember me
Actions:
* Login
* Forgot password
* Register

### P09. Register
Fields:
* Full name
* Email
* Password
* Confirm password
Actions:
* Create account
* Go to login

### P10. Forgot Password
Flow:
* Enter email
* Request reset link
* Open reset link
* Set new password
* Log in

## 4. Student Screens

### S01. Student Dashboard
Purpose: Provide a summary of learning progress and current activity.

Components:
* Welcome summary
* Enrolled courses
* Current progress
* Upcoming tasks
* Recent results

States:
* default view
* empty enrollment state
* active course state
* result summary state

### S02. My Courses
Purpose: Show course inventory and access state.

Components:
* course list
* status indicators
* continue learning button
* completion progress

States:
* not enrolled
* enrolled and active
* completed course

### S03. Lesson View
Purpose: Display learning content and navigate between instructional units.

Components:
* course/lesson navigation
* lesson content area
* previous/next lesson controls
* quiz links

States:
* lesson content
* lesson complete
* next lesson unavailable

### S04. Quiz Interface
Purpose: Present assessment questions and collect answers.

Components:
* question display
* option selection
* timer info
* submit quiz button

States:
* quiz instructions
* active question state
* submitted state
* time expired state

### S05. Exam Interface
Purpose: Deliver the final assessment with time enforcement and validation.

Components:
* attempt information
* question list and answers
* timer and expiry status
* final submission confirmation

States:
* exam instructions
* active exam session
* save draft state
* submit confirmation modal
* submitted state
* expired state

### S06. Results Page
Purpose: Present attempt outcomes after the assessment is complete.

Components:
* total score
* percentage
* pass/fail status
* review summary

States:
* pass
* fail
* no result yet

### S07. Certificates
Purpose: Show earned credentials and verification options.

Components:
* issued certificates
* download or view option
* verification code visibility when appropriate

States:
* certificate available
* waiting for issuance
* certificate invalid or revoked

### S08. Profile
Purpose: Allow users to manage their personal account data.

Components:
* personal details
* security settings
* activity status

States:
* view profile
* edit profile
* password update flow

## 5. Admin Screens

### A01. Admin Dashboard
Purpose: Summarize platform operations and key metrics.

Components:
* overview metrics
* student counts
* course counts
* exam activity
* recent certificate issuance

States:
* default dashboard view
* filter by date or role
* empty dataset state

### A02. Courses Management
Purpose: Support course creation, editing, publishing, and lifecycle control.

Components:
* create/edit courses
* publish or archive courses
* manage modules and lessons

States:
* create form
* edit form
* published/unpublished status

### A03. Students Management
Purpose: Review and manage learners and their enrollments.

Components:
* search students
* view profiles
* review enrollments
* view results

States:
* list view
* single student detail
* no records found

### A04. Exams Management
Purpose: Configure and maintain assessment sessions.

Components:
* create exams
* configure time limits
* set pass rules
* manage questions

States:
* exam draft
* published exam
* archived exam

### A05. Results and Attempts
Purpose: Review assessment submissions and scoring.

Components:
* review submissions
* filter results
* check scoring and issues

States:
* all attempts
* flagged attempt
* reviewed result

### A06. Certificates Management
Purpose: Track and maintain issued credentials.

Components:
* issue certificates
* review certificate status
* revoke or renew when rules allow

States:
* pending issuance
* active certificate
* revoked or invalid

### A07. System Settings
Purpose: Manage operational and security configuration.

Components:
* platform configuration
* mail settings
* security preferences
* audit logs

States:
* default settings
* saved settings
* validation error state

## 6. Screen Development Order

1. Landing page
2. Login and register
3. Student dashboard
4. Course catalogue and details
5. Lesson view
6. Quiz flow
7. Exam flow
8. Result display
9. Certificate generation and verification
10. Admin dashboard and management pages

## 7. UI Requirements

* Response design on desktop and mobile
* Clear labels and validation states
* Consistent cards and form layouts
* Accessible color contrast and navigation
* Simple, predictable user interactions
