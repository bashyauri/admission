# Course-Based Coordinator Workflow

## Overview
The system now implements a course-based coordinator assignment system where students from the same course cohort (e.g., Computer Science 2026/2027 freshers) are assigned to the same coordinator and retain them throughout their academic program. This approach is essential for departments with multiple courses/programs.

## Workflow

### 1. Coordinator Assignment Logic
- **Course-Based**: Students are assigned based on **course + admission level + admission session**
- **Example**: Computer Science 2026/2027 freshers → 100L coordinator for Computer Science 2026/2027 session
- **Multi-Course Support**: Departments with multiple courses (e.g., Computer Science + Information Systems) have separate coordinators for each course
- **Persistence**: Once assigned, a student retains the same coordinator until graduation
- **Admin Override**: Admin can remove and replace coordinators if needed

### 2. Result Submission Flow (Updated)
1. **Course Lecturers** submit results to **Students' Respective Course Coordinators**
2. **Course Coordinators** review and approve results for their assigned course cohort
3. **Course Coordinators** submit directly to **Exam Officer** for final approval
4. **Exam Officer** releases results to students (triggers GPA and carry-over calculations)

### 3. Result Approval Status Flow
- `pending` → Lecturer draft (editable)
- `submitted` → Submitted to course coordinator
- `exam_officer_approved` → Coordinator approved, awaiting Exam Officer release
- `released` → Exam Officer released, visible to students

### 4. Database Changes
- Changed `coordinators` table from department-based to **course-based**
- Added `student_level_id` and `academic_session` to `coordinators` table
- Added `admission_session` to `academic_details` table for cohort tracking
- Added coordinator fields to `results` table (`coordinator_id`, `coordinator_approved_by`, `coordinator_approved_at`)
- Added composite unique constraint: `course_cohort_coordinator_unique` (course_id + student_level_id + academic_session)
- Added course cohort index to academic_details for efficient queries
- Updated Coordinator model with course-based logic
- Updated Result model with coordinator relationships and scopes
- Updated PIN generation to assign course-based coordinators

### 5. Coordinator Model Updates
```php
public function course()
{
    return $this->belongsTo(Course::class);
}

public function studentLevel()
{
    return $this->belongsTo(StudentLevel::class);
}

public function scopeForCourseCohort($query, $courseId, $studentLevelId, $academicSession)
{
    return $query->where('course_id', $courseId)
                ->where('student_level_id', $studentLevelId)
                ->where('academic_session', $academicSession);
}
```

### 6. AcademicDetail Model Updates
```php
public function getCourseCohortCoordinatorAttribute(): ?Coordinator
{
    $admissionSession = $this->admission_session ?? null;
    
    if (!$admissionSession) {
        return null;
    }
    
    return Coordinator::forCourseCohort(
        $this->course_id,
        $this->student_level_id,
        $admissionSession
    )->first();
}
```

### 7. Result Model Updates
```php
public function coordinator(): BelongsTo
{
    return $this->belongsTo(Coordinator::class, 'coordinator_id');
}

public function coordinatorApprover(): BelongsTo
{
    return $this->belongsTo(User::class, 'coordinator_approved_by');
}

public function scopeCoordinatorApproved($query)
{
    return $query->where('status', 'exam_officer_approved');
}

public function scopeForCoordinator($query, $coordinatorId)
{
    return $query->where('coordinator_id', $coordinatorId)->where('status', 'submitted');
}
```

### 8. PIN Generation Updates
- Coordinator search now filters by **course + level + admission session**
- Course cohort coordinator assignment persists throughout student's academic career
- Added validation to ensure course cohort coordinator exists before PIN generation
- Uses admission_session from academic_details if available, otherwise falls back to current session

### 9. Result Processing Updates
- **Lecturers** submit results to students' respective course coordinators (not HOD)
- **Coordinators** receive results for their assigned course cohort
- **Coordinators** submit directly to Exam Officer (no HOD in workflow)
- **Exam Officer** receives coordinator-approved results for final review and release
- **HOD** result review component is deprecated but kept for reference

### 10. Department Coordinator Review Workspace
- Department coordinators can hold multiple level assignments. The review workspace derives the available levels from all department-coordinator assignments belonging to the signed-in user.
- The coordinator selects a level, session, semester, and status before selecting a course. The initial page load does not fetch the student result rows.
- Course queues are aggregated in MySQL using grouped conditional counts, scoped by department, selected level, academic period, and the coordinator assignment IDs.
- Selecting a course loads only that course's matching students, with server-side name/matric search and pagination of 25 results per page.
- Legacy result records without course snapshots fall back through the registered course's original `department_courses -> student_courses` relationship. Newer result snapshots remain the preferred historical label source.
- Coordinating approval transitions submitted results to `exam_officer_approved`, and records `coordinator_approved_by` and `coordinator_approved_at`. This is the queue consumed by the Exam Officer release workflow.
- The review action uses an in-app confirmation panel that shows the course, period, submitted batch count, and downstream Exam Officer handoff. Returning a batch requires a reason.

## Implementation Details

### Migrations Required
Run: 
```bash
php artisan migrate --path=database/migrations/2026_08_30_000000_add_student_level_to_coordinators_table.php
php artisan migrate --path=database/migrations/2026_08_30_000001_add_admission_session_to_academic_details.php
php artisan migrate --path=database/migrations/2026_08_30_000002_add_coordinator_fields_to_results_table.php
php artisan migrate --path=database/migrations/2026_09_01_122537_add_coordinator_review_index_to_results_table.php
```

### Coordinator Setup
Admins need to create coordinators with:
- `user_id`: The coordinator's user account
- `course_id`: The **course/program** they coordinate (e.g., Computer Science, Information Systems)
- `student_level_id`: The admission level they coordinate (100L or 200L)
- `academic_session`: The admission session they coordinate (e.g., "2026/2027")

### Student Admission Session
When admitting students, set their `admission_session` to match the coordinator's academic_session to ensure proper course cohort assignment.

### New Components Created
- `CoordinatorResultReview.php` - Coordinator result review and approval component
- `ResultStatus.php` - Enum for result approval statuses

### Updated Components
- `ResultEntry.php` - Now submits to students' respective coordinators
- `HodResultReview.php` - Deprecated, shows message about new workflow
- `Result.php` - Added coordinator relationships and scopes, removed HOD fields
- `ExamOfficerResultReview.php` - Updated to receive coordinator-approved results

### Backward Compatibility
The system supports both course-based (new) and department-based (legacy) coordinators to ensure existing assignments continue to work:

- **Course-Based Coordinators**: New system where coordinators are assigned to specific courses
- **Department-Based Coordinators**: Legacy system where coordinators are assigned to departments
- **Fallback Logic**: When searching for a coordinator, the system first tries course-based, then falls back to department-based
- **Migration**: The `2026_08_31_000000_add_backward_compatibility_for_coordinators` migration adds `department_id` back to the coordinators table and separate unique constraints for each type

### Coordinator Types Supported
- **Course Cohort Coordinator**: `course_id + student_level_id + academic_session`
- **Department Cohort Coordinator**: `department_id + student_level_id + academic_session` (legacy support)

### PIN Generation Compatibility
The PIN generation logic automatically detects and uses the appropriate coordinator type:
1. First attempts to find course-based coordinator for the student's course cohort
2. Falls back to department-based coordinator if no course-based coordinator exists
3. Ensures existing student-coordinator relationships are maintained

### Benefits
- **Course-Specific**: Each course/program has dedicated coordinators
- **Multi-Course Support**: Departments with multiple courses are properly supported
- **Cohort Consistency**: Students from same course cohort maintain same coordinator
- **Better Tracking**: Clear responsibility for monitoring specific course cohorts
- **Academic Progress**: Coordinator can track course cohort progress throughout program
- **Flexible Override**: Admin can replace coordinators when needed
- **Streamlined Workflow**: Simplified result approval chain (Lecturer → Coordinator → Exam Officer)
- **Improved Oversight**: Coordinators provide course-level academic oversight for exam officer review
- **Backward Compatible**: Existing department-based coordinators continue to work
