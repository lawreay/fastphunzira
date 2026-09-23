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
* Welcome summary
* Enrolled courses
* Current progress
* Upcoming tasks
* Recent results

### S02. My Courses
* Course list
* Status indicators
* Continue learning button
* Completion progress

### S03. Lesson View
* Course/lesson navigation
* Lesson content area
* Previous/next lesson controls
* Quiz links

### S04. Quiz Interface
* Question display
* Options selection
* Timer info
* Submit quiz button

### S05. Exam Interface
* Attempt information
* Question list and answers
* Timer and expiry status
* Final submission confirmation

### S06. Results Page
* Total score
* Percentage
* Pass/fail
* Review summary

### S07. Certificates
* Issued certificates
* Download or view option
* Verification code visibility when appropriate

### S08. Profile
* Personal details
* Security settings
* Activity status

## 5. Admin Screens

### A01. Admin Dashboard
* Overview metrics
* Student counts
* Course counts
* Exam activity
* Recent certificate issuance

### A02. Courses Management
* Create/edit courses
* Publish or archive courses
* Manage modules and lessons

### A03. Students Management
* Search students
* View profiles
* Review enrollments
* View results

### A04. Exams Management
* Create exams
* Configure time limits
* Set pass rules
* Manage questions

### A05. Results and Attempts
* Review submissions
* Filter results
* Check scoring and issues

### A06. Certificates Management
* Issue certificates
* Review certificate status
* Revoke or renew when rules allow

### A07. System Settings
* Platform configuration
* Mail settings
* Security preferences
* Audit logs

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
