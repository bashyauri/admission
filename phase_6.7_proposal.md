#### Task 6.7: Student Academic Status, Withdrawal & Reinstatement Management (⏳ UP NEXT)
* **Goal:** Implement Senate-compliant student status management with auditable withdrawal/reinstatement workflows, academic progression history tracking, and proper separation of academic performance from institutional decisions.
* **Risk Profile:** Low (additive tables and service layer with proper governance workflows).
* **Status:** **Ready for Execution (Structured into 10 Daily Tasks)**

### Design Principles
The system must follow these governance rules:
- Academic progression is not the same as withdrawal
- A recommendation is not an official withdrawal
- Senate-approved status changes must be auditable
- Historical academic records must never disappear because of withdrawal
- Withdrawal must be session/semester aware
- Reinstatement must create a new historical event, not erase withdrawal history
- PG workflows remain untouched
- Disciplinary suspension/expulsion remains separate from ordinary academic withdrawal
- Institutional regulations determine withdrawal thresholds
- No withdrawal rule should be inferred merely from a CGPA unless the approved regulation says so

### Daily Tasks Breakdown:

#### Task 6.7.1: Academic Status Foundation (Database & Models)
* **Scope:** Migrations & Eloquent Models only.
* **Tasks:**
  - [ ] Create `academic_progression_records` table migration & model (`AcademicProgressionRecord.php`)
    - Fields: `user_id`, `academic_detail_id`, `academic_session`, `semester`, `level`, `cgpa`, `standing`, `withdrawal_recommended`
    - Purpose: Track academic progression history independent of institutional decisions
  - [ ] Create `student_status_records` table migration & model (`StudentStatusRecord.php`)
    - Fields: `user_id`, `academic_detail_id`, `status`, `status_type`, `reason_code`, `reason`, `academic_session`, `semester`, `effective_date`, `end_date`, `senate_reference`, `senate_decision_date`, `senate_decision`, `reinstatement_eligible`, `processed_by`, `notes`
    - Purpose: Track actual institutional decisions (withdrawal, reinstatement, etc.)
  - [ ] Create PHP enums: `StudentStatus`, `StudentStatusType`, `AcademicActivity`
    - `StudentStatus`: ACTIVE, VOLUNTARY_WITHDRAWAL, ACADEMIC_WITHDRAWAL, MEDICAL_WITHDRAWAL, SUSPENDED, EXPELLED, REINSTATED
    - `StudentStatusType`: ACADEMIC, VOLUNTARY, MEDICAL, DISCIPLINARY
    - `AcademicActivity`: COURSE_REGISTRATION, SCHOOL_FEES, EXAM_REGISTRATION, RESULT_PROCESSING, GRADUATION
  - [ ] Establish relationships on `User` and `AcademicDetail`
  - [ ] Create historical status query scopes
  - [ ] Automated unit test: `tests/Unit/Phase67StatusFoundationTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.1: Create the academic progression records and student status records tables with proper enums, relationships, and unit tests."*

#### Task 6.7.2: Withdrawal Eligibility Engine
* **Scope:** Business logic & calculation service layer (strictly backend TDD).
* **Tasks:**
  - [ ] Extend `AcademicProgressionService` with `evaluateWithdrawalEligibility()` method
    - Rule-driven evaluation (no hard-coded CGPA < 0.50 until institutionally confirmed)
    - Consecutive probation tracking
    - Maximum residency checks using existing programme duration logic
    - Registration/non-registration pattern analysis
    - Configurable institutional rules via configuration file
  - [ ] Returns structured assessment: `['eligible' => bool, 'reason_code' => string, 'reason' => string, 'standing' => string]`
  - [ ] Integration with existing `determineAcademicStanding()` method
  - [ ] Automated unit test suite: `tests/Unit/WithdrawalEligibilityTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.2: Extend AcademicProgressionService with rule-driven withdrawal eligibility evaluation that supports configurable institutional policies and returns structured assessments."*

#### Task 6.7.3: Senate Withdrawal Workflow Service
* **Scope:** Service layer with Senate governance workflow.
* **Tasks:**
  - [ ] Create `App\Services\StudentStatusService`:
    - `evaluateAcademicWithdrawal()` - Assessment gateway
    - `createWithdrawalRecommendation()` - Create recommendation records
    - `submitForSenate()` - Submit to Senate workflow
    - `approveWithdrawal()` - Senate approval processing
    - `rejectWithdrawal()` - Senate rejection processing
    - `processVoluntaryWithdrawal()` - Handle voluntary requests
    - `processMedicalWithdrawal()` - Handle medical cases
    - `getCurrentStatus()` - Get current authoritative status
    - `getStatusHistory()` - Get complete status timeline
    - `isAcademicallyActive()` - Activity status check
    - `isEligibleForReinstatement()` - Reinstatement eligibility check
  - [ ] Implement Senate workflow states: WITHDRAWAL_RECOMMENDED → PENDING_SENATE → SENATE_APPROVED/REJECTED
  - [ ] Senate reference validation and format checking
  - [ ] Effective date handling with session awareness
  - [ ] Automated unit test suite: `tests/Unit/StudentStatusServiceTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.3: Create StudentStatusService with complete Senate withdrawal workflow including recommendation, approval, rejection, and status history management."*

#### Task 6.7.4: Academic Activity Enforcement Gate
* **Scope:** Central activity authorization gate integration.
* **Tasks:**
  - [ ] Implement `StudentStatusService::canPerformAcademicActivity($student, $activity)` method
  - [ ] Integrate with `CourseRegistrationService` - block registration for withdrawn students
  - [ ] Integrate with Course Registration Livewire - show status-based messaging
  - [ ] Integrate with `PaymentService` - block fee invoice generation for withdrawn students
  - [ ] Integrate with exam registration where applicable
  - [ ] Integrate with graduation eligibility checks in `GraduationService`
  - [ ] **Historical Preservation Rules:**
    - ✅ Allow viewing old results, transcripts, payments, payment reconciliation
    - ❌ Block new registration, fee invoices, course registration, graduation processing
  - [ ] Feature test suite: `tests/Feature/ActivityEnforcementTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.4: Create centralized academic activity enforcement gate that integrates with existing services while preserving historical data access for withdrawn students."*

#### Task 6.7.5: Reinstatement Workflow
* **Scope:** Senate-approved reinstatement process.
* **Tasks:**
  - [ ] Extend `StudentStatusService` with reinstatement methods:
    - `requestReinstatement()` - Handle reinstatement requests
    - `processReinstatement()` - Senate approval processing
    - `determineReinstatementLevel()` - Calculate correct academic level/session
  - [ ] Implement reinstatement workflow: WITHDRAWN → Reinstatement Request → Department/Faculty Review → Senate Decision → APPROVED/REJECTED → REINSTATED
  - [ ] Reinstatement must be append-only (create new status record, don't erase withdrawal)
  - [ ] Use existing academic progression logic to determine correct level
  - [ ] Senate reference validation for reinstatement decisions
  - [ ] Feature test: `tests/Feature/ReinstatementWorkflowTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.5: Create Senate-approved reinstatement workflow that preserves complete withdrawal history and uses academic progression logic to determine correct reinstatement level."*

#### Task 6.7.6: Senate Withdrawal Reporting
* **Scope:** Senate-compliant withdrawal ledger and reports.
* **Tasks:**
  - [ ] Extend `ResultReportingService` with withdrawal reporting methods:
    - `getWithdrawalLedger()` - Complete withdrawal history with filters
    - `getSenateWithdrawalReport()` - Senate compliance report
    - `getDepartmentalWithdrawalReport()` - Department-level summary
    - `getReinstatementReport()` - Reinstatement tracking report
  - [ ] Create withdrawal ledger view: `resources/views/reports/withdrawal-ledger.blade.php`
    - Columns: Matric No, Student, Programme, Type, Session, Effective Date, Senate Ref, Reinstatement
    - Filters: Academic Session, Department, Programme, Withdrawal Type, Status, Date Range, Reinstatement Eligibility, Senate Reference
  - [ ] Statistics by session/type/department
  - [ ] Print, PDF, and CSV export capabilities
  - [ ] Feature test: `tests/Feature/WithdrawalReportingTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.6: Create Senate-compliant withdrawal reporting including withdrawal ledger, Senate reports, and statistical analysis with export capabilities."*

#### Task 6.7.7: Transcript & Result Integration
* **Scope:** Official transcript annotation and result reporting cleanup.
* **Tasks:**
  - [ ] Extend `TranscriptService` to display withdrawal annotation:
    - STATUS: WITHDRAWN FROM PROGRAMME/UNIVERSITY
    - SESSION: Academic session of withdrawal
    - SENATE REF: Senate reference number
    - CGPA at withdrawal point
  - [ ] **Remove** duplicated withdrawal calculation from `ResultReportingService`:
    - Delete CGPA-based withdrawal calculation (lines 457-463)
    - Replace with authoritative status from `StudentStatusService`
  - [ ] Update broadsheet generation to use database status instead of CGPA calculation
  - [ ] **Historical Result Preservation:**
    - Ensure withdrawn students' historical results remain visible
    - Ensure transcripts show complete academic history up to withdrawal point
  - [ ] Feature test: `tests/Feature/TranscriptWithdrawalIntegrationTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.7: Integrate withdrawal status into transcripts, remove duplicated CGPA-based withdrawal calculations from ResultReportingService, and ensure historical result preservation."*

#### Task 6.7.8: Security & Audit Infrastructure
* **Scope:** Authorization policies and immutable audit trail.
* **Tasks:**
  - [ ] Create authorization policies for status changes:
    - `StudentStatusPolicy` - control who can recommend/approve/reject withdrawals
    - Role restrictions: Admin and other explicitly authorized institutional capabilities; do not assume HOD authority unless separately configured
  - [ ] Implement immutable audit trail around status decisions:
    - Record: student, action, old status, new status, reason, user, timestamp, IP/device, Senate reference
    - Apply to: Withdrawal, Approval, Rejection, Reinstatement, Suspension, Expulsion
  - [ ] Senate reference validation and format enforcement
  - [ ] Prevention of unauthorized status changes
  - [ ] Integration with existing audit systems if available
  - [ ] Security test suite: `tests/Feature/StatusSecurityTest.php`
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.8: Create authorization policies and immutable audit trail for Senate status decisions with proper role restrictions and Senate reference validation."*

#### Task 6.7.9: Full Integration Tests
* **Scope:** Comprehensive end-to-end testing scenarios.
* **Tasks:**
  - [ ] Active student baseline test
  - [ ] Academic withdrawal recommendation test
  - [ ] Senate rejection workflow test
  - [ ] Senate approval workflow test
  - [ ] Voluntary withdrawal test
  - [ ] Medical withdrawal test
  - [ ] Reinstatement workflow test
  - [ ] Withdrawal after results processing test
  - [ ] Withdrawal before registration test
  - [ ] Pending payment after withdrawal test
  - [ ] Historical transcript after withdrawal test
  - [ ] Attempted new registration after withdrawal test
  - [ ] Attempted new invoice after withdrawal test
  - [ ] PG student isolation test (ensure PG workflows unaffected)
  - [ ] Senate reference validation test
  - [ ] Activity enforcement gate test
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.9: Create comprehensive integration test suite covering all withdrawal, reinstatement, and Senate workflow scenarios including edge cases and PG isolation."*

#### Task 6.7.10: Withdrawal, Senate & Status Management UI
* **Scope:** Web UI/UX integration only. Backend services, policies, and status rules remain authoritative.
* **Tasks:**
  - [ ] Create an authorized Admin/Officer Withdrawal Management interface:
    - Search by matric number/name
    - Display current authoritative status
    - Display relevant academic progression summary
    - Select withdrawal/recommendation type
    - Capture reason and reason code
    - Select academic session and semester
    - Set effective date
    - Capture Senate reference/decision information where applicable
    - Set reinstatement eligibility
    - Add notes/supporting information
    - Require explicit confirmation before consequential actions
  - [ ] Create Withdrawal Recommendation / Submission interface:
    - Show recommendation details before submission
    - Show current workflow state
    - Prevent unauthorized users from submitting or approving
    - Display validation and audit information
  - [ ] Create Senate Withdrawal Decision dashboard:
    - List pending Senate decisions
    - Filter by session, department, programme, withdrawal type, and date
    - View complete student/status history before decision
    - Approve or reject with required decision metadata
    - Require Senate reference where institutional policy requires it
    - Record decision date and authorized officer
    - Do not allow UI actions to bypass server-side authorization
  - [ ] Create Reinstatement Management interface:
    - List reinstatement requests
    - Display original withdrawal record and history
    - Show relevant progression/registration history
    - Process Department/Faculty review where applicable
    - Submit to Senate
    - Approve/reject with required reference and decision metadata
    - Create a new REINSTATED status event without modifying historical withdrawal records
  - [ ] Create Student Status Overview / History UI:
    - Current authoritative status
    - Chronological status timeline
    - Academic session/semester
    - Effective dates
    - Senate references
    - Decision information according to permissions
    - Reinstatement eligibility
    - Audit information according to permissions
  - [ ] Integrate status indicators into Result Entry and Result Review:
    - Keep historically relevant students visible
    - Clearly mark officially withdrawn students
    - Prevent new result entry when backend rules prohibit it
    - Preserve previously entered/approved results
    - CSV/Excel imports must skip ineligible withdrawn students with an informational warning rather than crash or silently discard the record
  - [ ] Integrate withdrawal status into Broadsheets and official reports:
    - Show `WITHDRAWN FROM PROGRAMME` or `WITHDRAWN FROM UNIVERSITY` where appropriate
    - Show withdrawal session/effective date where appropriate
    - Show Senate reference where appropriate
    - Preserve the student's historical representation for auditability
  - [ ] Integrate Student Portal status UI:
    - Show a clear professional withdrawal/status banner
    - Explain effective session/date
    - Keep historical results, transcript, and payment history accessible
    - Disable unavailable new academic actions
    - Restore appropriate active UI after reinstatement
  - [ ] Add UI states:
    - Loading states
    - Empty states
    - Validation messages
    - Confirmation dialogs
    - Success/error notifications
    - Permission-aware action visibility
    - Clear disabled states
    - No raw exception/database messages to end users
  - [ ] Add UI/feature tests covering authorized and unauthorized actions.
*Agent Prompt:*
  > *"Please implement Phase 6, Task 6.7.10: Integrate the student status, withdrawal, Senate decision, reinstatement, result-entry, broadsheet, and student-status workflows into the existing web UI using the application's current Livewire/Blade/Filament conventions. Provide authorized withdrawal management, Senate approval/rejection, reinstatement management, status history, result-entry/review indicators, broadsheet status presentation, and student-facing status messaging. All UI restrictions must be backed by server-side authorization and the existing StudentStatusService. Do not change the Lecturer → Coordinator → Exam Officer result approval workflow."*

### Phase 6.7 UI Acceptance Criteria
- [ ] Authorized officers can view a student's authoritative current status before taking action.
- [ ] Withdrawal recommendations and Senate decisions are clearly separated.
- [ ] Senate approval/rejection requires the appropriate authorization and decision metadata.
- [ ] Reinstatement creates a new historical event and never erases the original withdrawal.
- [ ] Result Entry and Result Review clearly distinguish withdrawn students without removing historical records.
- [ ] CSV/Excel result imports handle withdrawn/ineligible students safely with informational warnings.
- [ ] Official broadsheets preserve required withdrawn-student information.
- [ ] Students retain access to historical academic records after withdrawal.
- [ ] Existing active-student workflows remain stable.
- [ ] PG workflows remain unaffected.
- [ ] UI restrictions are backed by server-side authorization and service-level validation.
- [ ] The existing Lecturer → Coordinator → Exam Officer → Released result workflow remains unchanged.

### Architecture Summary
The authoritative result-processing approval workflow remains:

**Lecturer → Coordinator → Exam Officer → Released**

Withdrawal/status management is a separate institutional-status workflow. It may restrict future academic activity and annotate/report historical records, but it must not replace or alter the result approval chain.

This implementation follows the principle: **Build withdrawal as an auditable, Senate-authorized, session-aware student-status history layered on top of the existing academic progression engine—not as a single `academic_details.status` field or an automatic CGPA-triggered deletion/blocking mechanism.**

### Integration with Phase 7
Phase 6.7 establishes the reusable student status infrastructure that Phase 7 (Disciplinary Enforcement) will consume for suspension, expulsion, and disciplinary sanctions, preventing duplicate architecture.