# Implementation Plan - Phase 4: Multi-Level Result Approval Workflows

This plan outlines the implementation of Phase 4 of the Student MIS extension: building the multi-level result review and approval workflow for Heads of Department (HOD) and Exam Officers.

## Background Context
In Phase 3, lecturers were empowered to enter CA (0–40) and Exam (0–60) scores and submit them (`status = 'submitted'`). 
In Phase 4, we need:
1. **HOD Result Review Panel**: HODs review submitted course results for their department and approve them (`status = 'hod_approved'`) or reject/return them to pending with feedback (`status = 'pending'`).
2. **Exam Officer Result Review Panel**: Exam Officers review HOD-approved department results across the institution, provide faculty-level auditing, and release results (`status = 'released'`).
3. **State Machine Transitions**:
   - `pending` (Lecturer draft) $\rightarrow$
   - `submitted` (Submitted to HOD) $\rightarrow$
   - `hod_approved` (Approved by HOD, forwarded to Exam Officer) $\rightarrow$
   - `released` (Approved by Exam Officer, visible to students and GPA calculated)

---

## User Review Required

> [!IMPORTANT]
> - When an Exam Officer releases results (`status = 'released'`), we will automatically trigger `GradeCalculationService::processAndSaveGpaRecord()` for all affected students and clear any cleared carry-overs via `CarryOverRegistrationService::processResultClearance()`.
> - Rejections: If an HOD or Exam Officer rejects results, status will revert to `pending` and unlock scores for the lecturer to revise.

---

## Proposed Changes

### 1. HOD Result Review Module

#### [NEW] [HodResultReview.php](file:///C:/laragon/www/admission/app/Http/Livewire/Hod/HodResultReview.php)
- Session and semester filter dropdowns (auto-detected via `AcademicSessionService`).
- Course list table showing courses in the HOD's department with status indicators (Pending, Submitted, HOD Approved, Released).
- Drill-down view into a specific course to inspect student scores (Matric No, Name, CA, Exam, Total, Grade, Grade Point).
- Actions:
  - `approveCourseResults($departmentCourseId)`: Transitions results from `submitted` to `hod_approved`, records `hod_approved_by` (Auth::id()) and `hod_approved_at` (now()).
  - `rejectCourseResults($departmentCourseId, $reason)`: Transitions results back to `pending` with remarks so the lecturer can adjust scores.

#### [NEW] [hod-result-review.blade.php](file:///C:/laragon/www/admission/resources/views/livewire/hod/hod-result-review.blade.php)
- Responsive UI matching the Soft UI / Tailwind design system.
- Summary metrics: Total Courses, Submitted for Review, Approved, Released.
- Modal / confirmation dialog for approvals and rejections.

#### [MODIFY] [routes/hod.php](file:///C:/laragon/www/admission/routes/hod.php)
- Register route `/hod/results-review` pointing to `HodResultReview::class`.

#### [MODIFY] [hod-sidebar.blade.php](file:///C:/laragon/www/admission/resources/views/layouts/navbars/auth/sidebar.blade.php) (or HOD navigation)
- Add "Result Approvals" link under academic section.

---

### 2. Exam Officer Result Review Module

#### [NEW] [ExamOfficerResultReview.php](file:///C:/laragon/www/admission/app/Http/Livewire/ExamOfficer/ExamOfficerResultReview.php)
- Filter by Academic Session, Semester, and Department.
- Summary cards showing institutional grading statistics (Pass rate, Fail rate, GPA distributions).
- Detailed grade sheet inspection per department course.
- Actions:
  - `releaseCourseResults($departmentCourseId)`: Transitions from `hod_approved` to `released`, sets `exam_officer_approved_by` and `exam_officer_approved_at`, updates GPA records, and triggers carry-over clearance.
  - `rejectToHod($departmentCourseId, $reason)`: Returns results to `submitted` or `pending`.

#### [NEW] [exam-officer-result-review.blade.php](file:///C:/laragon/www/admission/resources/views/livewire/exam-officer/exam-officer-result-review.blade.php)
- Exam officer auditing view with grade breakdown charts/summaries.

#### [MODIFY] [routes/exam_officer.php](file:///C:/laragon/www/admission/routes/exam_officer.php)
- Register route `/exam-officer/results-review` pointing to `ExamOfficerResultReview::class`.

---

### 3. Automated Feature Testing

#### [NEW] [ResultApprovalWorkflowTest.php](file:///C:/laragon/www/admission/tests/Feature/ResultApprovalWorkflowTest.php)
- Test Lecturer submitting results $\rightarrow$ status becomes `submitted`.
- Test HOD approving results $\rightarrow$ status becomes `hod_approved` with audit timestamps.
- Test HOD rejecting results $\rightarrow$ status reverts to `pending`.
- Test Exam Officer releasing results $\rightarrow$ status becomes `released`, GPA records generated in `result_gpa_records`, and carry-over cleared in `carry_over_courses`.

---

## Verification Plan

### Automated Tests
- Run `php artisan test tests/Feature/ResultApprovalWorkflowTest.php`
- Run `php artisan test tests/Unit` to ensure all existing calculations and services remain 100% green.

### Manual Verification
- Log in as HOD, view submitted courses, approve course results.
- Log in as Exam Officer, review HOD-approved grades, and click Release Results.
