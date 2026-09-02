# Phase 4 Reporting Implementation Plan

This plan breaks down the generation of Academic Board / Senate Broadsheets and Course Score Sheets into small, manageable daily tasks. This modular approach ensures that each task can be completed by the AI in a single prompt without exhausting tokens or losing context.

## User Review Required

> [!IMPORTANT]
> Please review this phased approach. You can instruct the AI to execute these phases one by one (e.g., "Execute Task 1 of the implementation plan").
> 
> **Architecture Decision**: For PDF generation, we have two options:
> 1. Use the browser's native `window.print()` with `@media print` CSS rules (faster, less server load, easier to build).
> 2. Use a backend PDF generator like `barryvdh/laravel-dompdf` (heavier, but produces guaranteed identical PDFs on every device).
> 
> **Recommendation**: We will start with standard HTML/Blade views combined with `@media print` CSS rules. Broadsheets are massive horizontal tables (matrix), and HTML prints handle horizontal scrolling/scaling much better than server-side PDF generators.

## Proposed Changes

We will split the implementation into 3 distinct daily tasks.

---

### Task 1: Data Aggregation Service (Backend Foundation)
Before we can build the UI for the broadsheets, we need a service that can quickly query and pivot the result data.

#### [NEW] `app/Services/ResultReportingService.php`
- Create a service responsible for aggregating results for a given cohort (`course_id`, `student_level_id`, `admission_session`).
- Methods to generate:
  - `getCourseScoreSheet($courseId, $session, $semester)`: Fetches a single course's CA, Exam, and Total scores for all students.
  - `getDepartmentalBroadsheet($cohortData)`: Fetches a matrix of all students in a cohort, pivoted against all their registered courses for that semester, including their Semester GPA and Academic Standing.
  - `getSenateSummaryStats($cohortData)`: Fetches grade distributions (e.g., number of A's, B's, etc.) and pass/fail rates.

#### [NEW] `tests/Unit/ResultReportingServiceTest.php`
- Write unit tests to verify that the `ResultReportingService` correctly pivots the data and calculates the summary statistics without errors.

---

### Task 2: Coordinator Course-Level Score Sheets (UI)
The Course Coordinator needs to be able to print the official Score Sheet for their course before sending it to the Exam Officer.

#### [MODIFY] `app/Livewire/Coordinator/CoordinatorResultReview.php`
- Add a method to trigger the score sheet view/export using the `ResultReportingService`.

#### [MODIFY] `resources/views/livewire/coordinator/coordinator-result-review.blade.php`
- Add a "View Score Sheet" button to the existing review table.

#### [NEW] `resources/views/reports/course-score-sheet.blade.php`
- Build the printable Blade template for the official score sheet.
- Include signature lines for the Lecturer, Coordinator, and Exam Officer at the bottom.
- Implement `@media print` CSS for a clean physical printout.

---

### Task 3: Exam Officer Senate Broadsheets (UI)
The Exam Officer needs the massive matrix view (Broadsheet) to present to the Academic Board / Senate.

#### [MODIFY] `app/Livewire/ExamOfficer/ExamOfficerResultReview.php`
- Add a method to load the full cohort broadsheet data via `ResultReportingService`.

#### [MODIFY] `resources/views/livewire/exam-officer/exam-officer-result-review.blade.php`
- Add "Generate Broadsheet" and "View Senate Summary" buttons.

#### [NEW] `resources/views/reports/senate-broadsheet.blade.php`
- Build the complex matrix layout:
  - Rows: Students (Matric Number, Name).
  - Columns: Every course taken that semester (dynamic).
  - Trailing Columns: Total Units, Total Points, Semester GPA, Cumulative GPA, Academic Standing.
- Implement landscape `@media print` CSS.

#### [NEW] `resources/views/reports/senate-summary.blade.php`
- Build the statistical summary view (Pass rates, fail rates, etc.).

## Verification Plan

### Automated Tests
- Run `php artisan test --filter ResultReportingServiceTest` to ensure the data pivoting and math are 100% accurate.

### Manual Verification
- After each task, you can manually log in as a Coordinator and Exam Officer to view and print the generated reports directly in the browser to ensure the layout matches NUC expectations.
