# User Flows

## 1. Student Registration Flow

```text
Open landing page
   ↓
Click Register
   ↓
Fill form
   ↓
Validate inputs
   ↓
Create user account
   ↓
Redirect to login
   ↓
Login to dashboard
```

## 2. Student Login Flow

```text
Open login page
   ↓
Enter email and password
   ↓
Validate credentials
   ↓
Create session
   ↓
Redirect to student dashboard
```

## 3. Course Enrollment Flow

```text
Browse course catalogue
   ↓
Open course details
   ↓
Click Enroll
   ↓
Check authentication and eligibility
   ↓
Create enrollment record
   ↓
Redirect to course view
```

## 4. Learning Flow

```text
Open enrolled course
   ↓
View module list
   ↓
Open lesson content
   ↓
Read lesson material
   ↓
Complete quiz or task
   ↓
Track progress
```

## 5. Quiz Attempt Flow

```text
Open lesson or course quiz
   ↓
Start quiz attempt
   ↓
Answer questions
   ↓
Submit answers
   ↓
Score is calculated
   ↓
Display result and summary
```

## 6. Final Exam Flow

```text
Open exam section
   ↓
Start exam attempt
   ↓
Timer begins
   ↓
Answer all questions
   ↓
Submit attempt
   ↓
Validate time and answers
   ↓
Store result
   ↓
Display outcome
```

## 7. Certificate Eligibility Flow

```text
Student passes required exam
   ↓
System checks completion rules
   ↓
Course requirements are evaluated
   ↓
Certificate record is generated
   ↓
Verification token is assigned
   ↓
Certificate is available to student
```

## 8. Certificate Verification Flow

```text
Visitor opens verification page
   ↓
Enters certificate number
   ↓
Enters verification code
   ↓
System checks database record
   ↓
If valid, show certificate result
   ↓
If invalid, show not found message
```

## 9. Admin Course Creation Flow

```text
Login as admin
   ↓
Open course management
   ↓
Create course
   ↓
Add modules and lessons
   ↓
Set publishing status
   ↓
Save and publish
```

## 10. Admin Exam Management Flow

```text
Login as admin
   ↓
Open exam management
   ↓
Create exam and questions
   ↓
Set time and pass rules
   ↓
Publish exam
   ↓
Monitor attempts and results
```

## 11. Password Reset Flow

```text
User clicks Forgot Password
   ↓
User enters email
   ↓
System validates account
   ↓
Reset token is created
   ↓
Email message sent
   ↓
User creates new password
   ↓
Account is updated and login is allowed
```

## 12. Notes for Testing

Each user flow should be validated from both the user perspective and the system perspective. This ensures the application behaves correctly for real users and that backend rules remain consistent even when interface actions are bypassed.
