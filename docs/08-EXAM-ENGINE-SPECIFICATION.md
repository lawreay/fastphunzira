# Exam Engine Specification

## 1. Purpose

The exam engine is responsible for creating, running, validating, scoring, and finalizing online assessments in a secure and consistent way. Because examinations drive certification and grading outcomes, this engine must enforce strict server-side rules.

## 2. Core Rules

* Exams must be created and managed by authorized administrators
* A student may have only one active exam attempt at a time for a given exam
* Timers must be enforced server-side
* Submissions must be validated before scoring
* Results must be calculated consistently and stored without client-side manipulation
* Certification eligibility must be derived from server-verified exam results

## 3. Exam Lifecycle

```text
Create exam
   ↓
Publish exam
   ↓
Student starts attempt
   ↓
Set expire time
   ↓
Student answers questions
   ↓
Student submits attempt
   ↓
Server validates attempt
   ↓
Score is calculated
   ↓
Result is finalized
   ↓
Certificate eligibility is checked
```

## 4. Exam Configuration

Each exam may contain:
* Title and description
* Course association
* Time limit
* Pass score
* Difficulty or order settings
* Question set
* Retake rules
* Availability period

## 5. Attempt Rules

### Start Attempt
When a student starts an exam:
* create an exam attempt record
* capture started_at
* calculate expires_at from time limit
* mark attempt as active
* load the authorized question set

### Duplicate Submission Prevention
* prevent the same attempt from being submitted twice
* reject any stale or already-finalized attempt

### Time Expiration
* if the time has expired, the exam should be auto-submitted or rejected as expired
* server must verify deadlines irrespective of client timer state

## 6. Question Handling

* Questions should be loaded from server-side storage
* Options must be rendered from the backend model, not from arbitrary client input
* Correct answers must never be exposed to students after submission is complete
* Question order may be fixed or randomized according to exam configuration

## 7. Answer Submission

The server should accept submitted answers in a structured format. Example:

```json
{
  "answers": [
    { "question_id": 12, "selected_option_id": 5 },
    { "question_id": 13, "answer_text": "The process of evaluating risk" }
  ]
}
```

Validation rules:
* question must belong to the exam
* student must be allowed to answer it
* option must be valid
* answer must be in the allowed format for the question type

## 8. Scoring Model

Scoring should be server-side and based on the actual attempt data.

Possible model:
* each correct answer awards points
* percentage is calculated from total possible points
* passing threshold must be configured by the exam
* result is stored as pass/fail plus score and percentage

Example:

```text
Total Points: 100
Student Score: 78
Percentage: 78%
Pass Threshold: 70%
Result: Passed
```

## 9. Result Finalization

When finalizing an exam attempt:
* verify attempt is still open
* validate submission time and expiration
* calculate final result
* persist score and percentage
* mark attempt as submitted or completed
* trigger certificate eligibility check if relevant

## 10. Retake Rules

The platform may support:
* one attempt only
* multiple attempts with best score retained
* limited retakes within a fixed window

These rules should be explicit and configured by admin settings.

## 11. Security Requirements

The exam engine must protect against:
* answer tampering in the browser
* multiple browser tabs resubmitting the same attempt
* time bypass via local clock manipulation
* direct API calls to submit invalid answer payloads
* unauthorized access to questions or attempts

## 12. Audit and Logging

The system should log:
* exam started
* question viewed
* answers submitted
* exam finalized
* expired attempts
* flagging suspicious behavior where applicable

## 13. Implementation Notes

The exam engine should be treated as a security-critical component. It must validate all calculations on the server, never trust client state, and maintain immutable attempt records for review and dispute resolution.
