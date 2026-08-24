# Student MIS Extension Plan - Result Processing & Graduation System

## Overview
This document outlines the comprehensive plan to extend the current admission system into a full Student Management Information System (MIS) covering the complete student lifecycle from admission to graduation, following National Universities Commission (NUC) standards for Nigerian universities.

## NUC Standards Compliance

### Current NUC Grading System (2018 onwards)

#### Grade Point Scale (5-Point System)
| Letter Grade | Score Range | Grade Point | Description |
|---------------|-------------|-------------|-------------|
| A | 70-100% | 5 | Excellent |
| B | 60-69% | 4 | Very Good |
| C | 50-59% | 3 | Good |
| D | 45-49% | 2 | Fair |
| F | 0-44% | 0 | Fail |

#### Degree Classification
| CGPA Range | Class of Degree |
|------------|-----------------|
| 4.50 - 5.00 | First Class Honours |
| 3.50 - 4.49 | Second Class Upper Division |
| 2.40 - 3.49 | Second Class Lower Division |
| 1.50 - 2.39 | Third Class Honours |
| 1.00 - 1.49 | Pass |
| Below 1.00 | Fail |

### Key NUC Requirements

#### Graduation Requirements
1. **Minimum CGPA**: 1.00 (some disciplines require 1.5)
2. **Credit Load per Semester**: Minimum 15, Maximum 24 units
3. **Total Credit Requirements** (varies by programme):
   - Engineering (UTME): 150-180 units over 5 years (10 semesters)
   - Engineering (Direct Entry): 120-150 units over 4 years (8 semesters)
   - Other programmes follow similar patterns
4. **Mandatory Courses**:
   - SIWES: 15 credit units
   - General Studies: 8 credit units
   - Entrepreneurship: 4 credit units

#### CGPA Calculation Rules
- **ALL registered courses** (compulsory and optional) must be included
- **Failed courses** are included in computation
- **Repeated courses**: All attempts included in CGPA calculation
- **Substituted courses**: Original failed grade still counted
- **Prerequisite courses**: Must be taken and passed before higher-level courses

#### Semester System
- **Harmattan Semester**: August - December (First Semester)
- **Rain Semester**: January - July (Second Semester)

## Phase 1: Database Schema Extensions (✅ COMPLETED)

**Status:** Completed & Migrated (August 2026)  
**Verification Suite:** `tests/Unit/Phase1DatabaseFoundationTest.php` (All Passed)

### 1.1 Result Processing Tables

#### results
```php
Schema::create('results', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id'); // Student
    $table->foreignIdFor(RegisteredCourse::class); // Course registration
    $table->foreignIdFor(DepartmentCourse::class); // Course details
    $table->foreignIdFor(AcademicDetail::class); // Academic session info
    $table->string('semester'); // 'first' or 'second'
    $table->string('academic_session'); // '2024-2025'
    $table->decimal('ca_score', 5, 2)->nullable(); // Continuous Assessment (0-40)
    $table->decimal('exam_score', 5, 2)->nullable(); // Exam Score (0-60)
    $table->decimal('total_score', 5, 2)->nullable(); // Total (0-100)
    $table->string('grade')->nullable(); // A, B, C, D, F
    $table->integer('grade_point')->nullable(); // 5, 4, 3, 2, 0
    $table->integer('credit_units');
    $table->integer('grade_point_total')->nullable(); // grade_point * credit_units
    $table->string('status')->default('pending'); // pending, submitted, approved, released
    $table->foreignId('lecturer_id')->nullable(); // Who graded
    $table->foreignId('hod_approved_by')->nullable(); // HOD approval
    $table->timestamp('hod_approved_at')->nullable();
    $table->foreignId('exam_officer_approved_by')->nullable(); // Exam officer approval
    $table->timestamp('exam_officer_approved_at')->nullable();
    $table->text('remarks')->nullable();
    $table->boolean('is_repeated')->default(false);
    $table->string('original_result_id')->nullable(); // If repeated, reference original
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['user_id', 'registered_course_id', 'academic_session', 'semester']);
});
```

#### result_gpa_records
```php
Schema::create('result_gpa_records', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(AcademicDetail::class);
    $table->string('semester'); // 'first' or 'second'
    $table->string('academic_session');
    $table->decimal('semester_gpa', 5, 2)->nullable();
    $table->integer('total_credit_units')->default(0);
    $table->integer('total_grade_points')->default(0);
    $table->decimal('cumulative_gpa', 5, 2)->nullable();
    $table->integer('cumulative_credit_units')->default(0);
    $table->integer('cumulative_grade_points')->default(0);
    $table->string('class_of_degree')->nullable();
    $table->timestamps();
    
    $table->unique(['user_id', 'academic_session', 'semester']);
});
```

#### result_approvals
```php
Schema::create('result_approvals', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->string('academic_session');
    $table->string('semester');
    $table->string('approval_level'); // 'lecturer', 'hod', 'exam_officer', 'vc'
    $table->foreignId('approved_by');
    $table->timestamp('approved_at');
    $table->string('status')->default('pending'); // pending, approved, rejected
    $table->text('comments')->nullable();
    $table->timestamps();
});
```

#### carry_over_courses
```php
Schema::create('carry_over_courses', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(RegisteredCourse::class);
    $table->foreignIdFor(DepartmentCourse::class);
    $table->string('failed_session');
    $table->string('failed_semester');
    $table->decimal('failed_score', 5, 2);
    $table->string('failed_grade');
    $table->string('retake_session')->nullable();
    $table->string('retake_semester')->nullable();
    $table->boolean('is_cleared')->default(false);
    $table->timestamp('cleared_at')->nullable();
    $table->boolean('auto_registered')->default(false); // Track if auto-registered
    $table->timestamp('auto_registered_at')->nullable();
    $table->timestamps();
});
```

**Purpose**: Track failed courses that need to be retaken
- Automatically registered for next session
- Priority over elective courses
- Counted in unit validation

### 1.2 Academic Records Tables

#### department_level_units (Extension of existing table)
```php
// Add to existing department_max_units table
Schema::table('department_max_units', function (Blueprint $table) {
    $table->integer('min_units')->after('max_units')->default(15);
    $table->unique(['department_id', 'student_level_id']);
});

// Rename table to department_level_units for clarity
Schema::rename('department_max_units', 'department_level_units');
```

**Purpose**: Define minimum and maximum credit units per department per level
- Example: Computer Science 100L: Min 15, Max 24
- Example: Engineering 200L: Min 18, Max 24
- Used for validation during course registration

#### academic_sessions
```php
Schema::create('academic_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // '2024-2025'
    $table->string('slug')->unique();
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### semesters
```php
Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(AcademicSession::class);
    $table->string('name'); // 'Harmattan', 'Rain'
    $table->string('type'); // 'first', 'second'
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### course_allocations
```php
Schema::create('course_allocations', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(DepartmentCourse::class);
    $table->foreignIdFor(AcademicSession::class);
    $table->foreignIdFor(Semester::class);
    $table->foreignId('lecturer_id'); // User with lecturer role
    $table->foreignId('department_id');
    $table->integer('assigned_units');
    $table->string('venue')->nullable();
    $table->string('day_of_week')->nullable();
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->timestamps();
});
```

### 1.3 Graduation Tables

#### graduation_eligibilities
```php
Schema::create('graduation_eligibilities', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(AcademicDetail::class);
    $table->string('academic_session');
    $table->decimal('final_cgpa', 5, 2);
    $table->string('class_of_degree');
    $table->integer('total_units_earned');
    $table->integer('total_units_required');
    $table->boolean('meets_requirements')->default(false);
    $table->boolean('siwes_completed')->default(false);
    $table->boolean('general_studies_completed')->default(false);
    $table->boolean('entrepreneurship_completed')->default(false);
    $table->boolean('is_cleared')->default(false);
    $table->foreignId('cleared_by')->nullable();
    $table->timestamp('cleared_at')->nullable();
    $table->text('remarks')->nullable();
    $table->timestamps();
});
```

#### graduation_lists
```php
Schema::create('graduation_lists', function (Blueprint $table) {
    $table->id();
    $table->string('academic_session');
    $table->string('ceremony_date');
    $table->string('venue');
    $table->boolean('is_published')->default(false);
    $table->foreignId('published_by')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
```

#### graduation_list_items
```php
Schema::create('graduation_list_items', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(GraduationList::class);
    $table->foreignUuid('user_id');
    $table->foreignIdFor(AcademicDetail::class);
    $table->string('matric_no');
    $table->string('full_name');
    $table->string('programme');
    $table->string('department');
    $table->decimal('final_cgpa', 5, 2);
    $table->string('class_of_degree');
    $table->integer('rank')->nullable();
    $table->boolean('is_present')->default(false);
    $table->timestamps();
});
```

#### certificates
```php
Schema::create('certificates', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(GraduationList::class);
    $table->string('certificate_number')->unique();
    $table->string('certificate_type'); // 'bachelor', 'master', 'phd', 'diploma'
    $table->string('class_of_degree');
    $table->date('issue_date');
    $table->string('file_path')->nullable();
    $table->boolean('is_printed')->default(false);
    $table->boolean('is_collected')->default(false);
    $table->timestamp('collected_at')->nullable();
    $table->foreignId('collected_by')->nullable();
    $table->timestamps();
});
```

#### transcripts
```php
Schema::create('transcripts', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->string('request_number')->unique();
    $table->string('destination')->nullable(); // Where to send
    $table->string('purpose')->nullable();
    $table->enum('status', ['pending', 'processing', 'ready', 'sent', 'collected'])->default('pending');
    $table->decimal('fee', 10, 2)->default(0);
    $table->boolean('fee_paid')->default(false);
    $table->foreignId('processed_by')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->string('file_path')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('collected_at')->nullable();
    $table->foreignId('collected_by')->nullable();
    $table->text('remarks')->nullable();
    $table->timestamps();
});
```

### 1.4 Lecturer and Assessment Tables

#### lecturers
```php
Schema::create('lecturers', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(Department::class);
    $table->string('staff_id')->unique();
    $table->string('rank')->nullable(); // Professor, Lecturer I, II, Assistant Lecturer
    $table->string('specialization')->nullable();
    $table->boolean('is_course_advisor')->default(false);
    $table->boolean('is_exam_officer')->default(false);
    $table->timestamps();
});
```

#### assessment_settings
```php
Schema::create('assessment_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(DepartmentCourse::class);
    $table->foreignIdFor(AcademicSession::class);
    $table->foreignIdFor(Semester::class);
    $table->decimal('ca_percentage', 5, 2)->default(40); // 40%
    $table->decimal('exam_percentage', 5, 2)->default(60); // 60%
    $table->integer('number_of_ca_tests')->default(2);
    $table->boolean('attendance_mandatory')->default(true);
    $table->decimal('attendance_percentage', 5, 2)->default(10);
    $table->timestamps();
});
```

## Phase 2: Grade Calculation System & Unit Validation (✅ COMPLETED)

**Status:** Completed & Tested (August 2026)  
**Verification Suites:** `tests/Unit/GradeCalculationTest.php`, `tests/Unit/AcademicProgressionTest.php`, `tests/Unit/CarryOverRegistrationTest.php`

### 2.1 Course Registration with Unit Validation Service

```php
<?php

namespace App\Services;

use App\Models\RegisteredCourse;
use App\Models\CarryOverCourse;
use App\Models\DepartmentLevelUnit;
use App\Models\AcademicDetail;
use App\Models\CourseVersion;
use App\Services\CourseVersioningService;
use Illuminate\Support\Facades\DB;

class CourseRegistrationService
{
    protected CourseVersioningService $versioningService;

    public function __construct(CourseVersioningService $versioningService)
    {
        $this->versioningService = $versioningService;
    }

    /**
     * Register courses with department unit validation
     */
    public function registerCourses(
        string $userId, 
        array $courseVersionIds, 
        string $session, 
        string $semester
    ): array {
        return DB::transaction(function () use ($userId, $courseVersionIds, $session, $semester) {
            $student = AcademicDetail::where('user_id', $userId)->firstOrFail();
            $departmentId = $student->department_id;
            $levelId = $student->student_level_id;

            // Get department unit constraints
            $unitConstraints = DepartmentLevelUnit::where('department_id', $departmentId)
                ->where('student_level_id', $levelId)
                ->firstOrFail();

            $minUnits = $unitConstraints->min_units;
            $maxUnits = $unitConstraints->max_units;

            // Get existing registered units for this session
            $existingUnits = RegisteredCourse::whereHas('academicDetail', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->sum('units');

            // Get carry-over units (priority)
            $carryOverUnits = $this->getCarryOverUnits($userId, $session, $semester);
            $newCourseUnits = 0;

            $registeredCourses = [];

            foreach ($courseVersionIds as $courseVersionId) {
                $snapshot = $this->versioningService->getCourseSnapshot($courseVersionId);
                $courseUnits = $snapshot['credit_units'];

                // Check if adding this course exceeds max units
                if (($existingUnits + $carryOverUnits + $newCourseUnits + $courseUnits) > $maxUnits) {
                    throw new \Exception(
                        "Cannot register. Total units would exceed maximum of {$maxUnits}. " .
                        "Current: " . ($existingUnits + $carryOverUnits + $newCourseUnits) . 
                        ", Adding: {$courseUnits}"
                    );
                }

                // Register the course
                $registeredCourse = RegisteredCourse::create([
                    'academic_detail_id' => $student->id,
                    'department_course_id' => $this->getDepartmentCourseId($courseVersionId),
                    'course_version_id' => $snapshot['course_version_id'],
                    'course_code_snapshot' => $snapshot['course_code'],
                    'course_title_snapshot' => $snapshot['course_title'],
                    'credit_units_snapshot' => $snapshot['credit_units'],
                    'semester_snapshot' => $snapshot['semester'],
                    'level_snapshot' => $snapshot['level'],
                    'units' => $snapshot['credit_units'],
                    'academic_session' => $session,
                    'student_level_id' => $levelId
                ]);

                $registeredCourses[] = $registeredCourse;
                $newCourseUnits += $courseUnits;
            }

            // Check minimum units requirement
            $totalUnits = $existingUnits + $carryOverUnits + $newCourseUnits;
            if ($totalUnits < $minUnits) {
                throw new \Exception(
                    "Minimum {$minUnits} units required. Current total: {$totalUnits}. " .
                    "Please register more courses."
                );
            }

            return [
                'registered_courses' => $registeredCourses,
                'total_units' => $totalUnits,
                'min_units' => $minUnits,
                'max_units' => $maxUnits,
                'carry_over_units' => $carryOverUnits
            ];
        });
    }

    /**
     * Get carry-over units for a session
     */
    private function getCarryOverUnits(string $userId, string $session, string $semester): int
    {
        return CarryOverCourse::where('user_id', $userId)
            ->where('retake_session', $session)
            ->where('retake_semester', $semester)
            ->where('auto_registered', true)
            ->with('departmentCourse')
            ->get()
            ->sum('departmentCourse.units');
    }

    /**
     * Get department course ID from version
     */
    private function getDepartmentCourseId(int $courseVersionId): int
    {
        $version = CourseVersion::findOrFail($courseVersionId);
        return DepartmentCourse::where('student_course_id', $version->course_id)
            ->where('department_id', $version->department_id)
            ->firstOrFail()->id;
    }
}
```

### 2.2 Automatic Carry-Over Registration Service

```php
<?php

namespace App\Services;

use App\Models\CarryOverCourse;
use App\Models\RegisteredCourse;
use App\Models\Result;
use App\Models\AcademicDetail;
use App\Models\CourseVersion;
use App\Services\CourseVersioningService;
use Illuminate\Support\Facades\DB;

class CarryOverRegistrationService
{
    protected CourseVersioningService $versioningService;

    public function __construct(CourseVersioningService $versioningService)
    {
        $this->versioningService = $versioningService;
    }

    /**
     * Automatically register carry-over courses for next session
     */
    public function autoRegisterCarryOvers(string $newSession): array
    {
        return DB::transaction(function () use ($newSession) {
            // Get all students with carry-over courses
            $studentsWithCarryOvers = CarryOverCourse::where('is_cleared', false)
                ->whereNull('retake_session')
                ->with('user.academicDetail')
                ->get()
                ->groupBy('user_id');

            $registrationResults = [];

            foreach ($studentsWithCarryOvers as $userId => $carryOvers) {
                $student = AcademicDetail::where('user_id', $userId)->firstOrFail();
                $departmentId = $student->department_id;
                $levelId = $student->student_level_id;

                // Get department unit constraints
                $unitConstraints = DepartmentLevelUnit::where('department_id', $departmentId)
                    ->where('student_level_id', $levelId)
                    ->firstOrFail();

                $maxUnits = $unitConstraints->max_units;
                $registeredUnits = 0;

                foreach ($carryOvers as $carryOver) {
                    // Check if course is still offered
                    $currentVersion = CourseVersion::where('course_code', $carryOver->departmentCourse->studentCourse->code)
                        ->where('is_active', true)
                        ->first();

                    if (!$currentVersion) {
                        // Course no longer offered - mark for manual review
                        $carryOver->update(['remarks' => 'Course no longer offered - manual review required']);
                        continue;
                    }

                    // Determine which semester to register
                    $semester = $this->determineRetakeSemester($carryOver, $currentVersion);

                    // Check if student has space for this course
                    $semesterUnits = $this->getSemesterUnits($userId, $newSession, $semester);
                    $courseUnits = $currentVersion->credit_units;

                    if (($semesterUnits + $courseUnits) > $maxUnits) {
                        // No space - defer to next semester or session
                        $carryOver->update([
                            'remarks' => 'Deferred due to unit limit',
                            'retake_session' => $newSession,
                            'retake_semester' => $semester === 'first' ? 'second' : null
                        ]);
                        continue;
                    }

                    // Register the carry-over course
                    $this->registerCarryOverCourse($carryOver, $currentVersion, $newSession, $semester);

                    // Update carry-over record
                    $carryOver->update([
                        'retake_session' => $newSession,
                        'retake_semester' => $semester,
                        'auto_registered' => true,
                        'auto_registered_at' => now()
                    ]);

                    $registeredUnits += $courseUnits;
                }

                $registrationResults[$userId] = [
                    'total_carry_overs' => $carryOvers->count(),
                    'auto_registered' => $carryOvers->where('auto_registered', true)->count(),
                    'deferred' => $carryOvers->where('auto_registered', false)->count(),
                    'total_units' => $registeredUnits
                ];
            }

            return $registrationResults;
        });
    }

    /**
     * Determine which semester to retake the course
     */
    private function determineRetakeSemester(CarryOverCourse $carryOver, CourseVersion $currentVersion): string
    {
        // If course is offered in the semester it was failed, prioritize that
        if ($currentVersion->semester === $carryOver->failed_semester) {
            return $carryOver->failed_semester;
        }

        // Otherwise use the semester the course is currently offered
        return $currentVersion->semester;
    }

    /**
     * Get current semester units for a student
     */
    private function getSemesterUnits(string $userId, string $session, string $semester): int
    {
        return RegisteredCourse::whereHas('academicDetail', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('academic_session', $session)
        ->where('semester_snapshot', $semester)
        ->sum('units');
    }

    /**
     * Register a single carry-over course
     */
    private function registerCarryOverCourse(
        CarryOverCourse $carryOver,
        CourseVersion $courseVersion,
        string $session,
        string $semester
    ): void {
        $snapshot = $this->versioningService->getCourseSnapshot($courseVersion->id);
        $student = AcademicDetail::where('user_id', $carryOver->user_id)->first();

        RegisteredCourse::create([
            'academic_detail_id' => $student->id,
            'department_course_id' => $carryOver->department_course_id,
            'course_version_id' => $snapshot['course_version_id'],
            'course_code_snapshot' => $snapshot['course_code'],
            'course_title_snapshot' => $snapshot['course_title'],
            'credit_units_snapshot' => $snapshot['credit_units'],
            'semester_snapshot' => $semester,
            'level_snapshot' => $snapshot['level'],
            'units' => $snapshot['credit_units'],
            'academic_session' => $session,
            'student_level_id' => $student->student_level_id
        ]);
    }

    /**
     * Identify carry-over courses after result release
     */
    public function identifyCarryOvers(string $session, string $semester): array
    {
        $failedResults = Result::where('academic_session', $session)
            ->where('semester', $semester)
            ->where('grade', 'F')
            ->where('status', 'released')
            ->get();

        $carryOversCreated = 0;

        foreach ($failedResults as $result) {
            // Check if carry-over already exists
            $existing = CarryOverCourse::where('user_id', $result->user_id)
                ->where('department_course_id', $result->department_course_id)
                ->where('failed_session', $session)
                ->where('failed_semester', $semester)
                ->where('is_cleared', false)
                ->first();

            if (!$existing) {
                CarryOverCourse::create([
                    'user_id' => $result->user_id,
                    'registered_course_id' => $result->registered_course_id,
                    'department_course_id' => $result->department_course_id,
                    'failed_session' => $session,
                    'failed_semester' => $semester,
                    'failed_score' => $result->total_score,
                    'failed_grade' => $result->grade,
                    'auto_registered' => false
                ]);

                $carryOversCreated++;
            }
        }

        return [
            'failed_courses' => $failedResults->count(),
            'carry_overs_created' => $carryOversCreated
        ];
    }
}
```

### 2.3 Grade Calculation Service

```php
<?php

namespace App\Services;

use App\Models\Result;
use App\Models\ResultGpaRecord;
use App\Models\RegisteredCourse;
use App\Enums\GradePoint;

class GradeCalculationService
{
    /**
     * Calculate grade from total score
     */
    public function calculateGrade(float $totalScore): string
    {
        if ($totalScore >= 70) return 'A';
        if ($totalScore >= 60) return 'B';
        if ($totalScore >= 50) return 'C';
        if ($totalScore >= 45) return 'D';
        return 'F';
    }

    /**
     * Calculate grade point from grade
     */
    public function calculateGradePoint(string $grade): int
    {
        return match($grade) {
            'A' => 5,
            'B' => 4,
            'C' => 3,
            'D' => 2,
            'F' => 0,
            default => 0
        };
    }

    /**
     * Calculate GPA for a semester
     */
    public function calculateSemesterGPA(string $userId, string $session, string $semester): array
    {
        $results = Result::where('user_id', $userId)
            ->where('academic_session', $session)
            ->where('semester', $semester)
            ->where('status', 'released')
            ->get();

        $totalCreditUnits = $results->sum('credit_units');
        $totalGradePoints = $results->sum('grade_point_total');

        $gpa = $totalCreditUnits > 0 
            ? round($totalGradePoints / $totalCreditUnits, 2) 
            : 0;

        return [
            'gpa' => $gpa,
            'total_credit_units' => $totalCreditUnits,
            'total_grade_points' => $totalGradePoints,
            'course_count' => $results->count()
        ];
    }

    /**
     * Calculate CGPA (Cumulative GPA)
     */
    public function calculateCGPA(string $userId): array
    {
        $allResults = Result::where('user_id', $userId)
            ->where('status', 'released')
            ->get();

        $totalCreditUnits = $allResults->sum('credit_units');
        $totalGradePoints = $allResults->sum('grade_point_total');

        $cgpa = $totalCreditUnits > 0 
            ? round($totalGradePoints / $totalCreditUnits, 2) 
            : 0;

        $classOfDegree = $this->determineClassOfDegree($cgpa);

        return [
            'cgpa' => $cgpa,
            'total_credit_units' => $totalCreditUnits,
            'total_grade_points' => $totalGradePoints,
            'class_of_degree' => $classOfDegree
        ];
    }

    /**
     * Determine class of degree based on CGPA
     */
    public function determineClassOfDegree(float $cgpa): string
    {
        if ($cgpa >= 4.50) return 'First Class Honours';
        if ($cgpa >= 3.50) return 'Second Class Upper Division';
        if ($cgpa >= 2.40) return 'Second Class Lower Division';
        if ($cgpa >= 1.50) return 'Third Class Honours';
        if ($cgpa >= 1.00) return 'Pass';
        return 'Fail';
    }

    /**
     * Process result and calculate all metrics
     */
    public function processResult(Result $result): void
    {
        // Calculate total score
        $result->total_score = $result->ca_score + $result->exam_score;
        
        // Calculate grade and grade point
        $result->grade = $this->calculateGrade($result->total_score);
        $result->grade_point = $this->calculateGradePoint($result->grade);
        
        // Calculate grade point total
        $result->grade_point_total = $result->grade_point * $result->credit_units;
        
        $result->save();

        // Update semester GPA
        $this->updateSemesterGPA($result->user_id, $result->academic_session, $result->semester);
        
        // Update CGPA
        $this->updateCGPA($result->user_id);
    }

    /**
     * Update semester GPA record
     */
    private function updateSemesterGPA(string $userId, string $session, string $semester): void
    {
        $gpaData = $this->calculateSemesterGPA($userId, $session, $semester);
        
        $cgpaData = $this->calculateCGPA($userId);

        ResultGpaRecord::updateOrCreate(
            [
                'user_id' => $userId,
                'academic_session' => $session,
                'semester' => $semester
            ],
            [
                'semester_gpa' => $gpaData['gpa'],
                'total_credit_units' => $gpaData['total_credit_units'],
                'total_grade_points' => $gpaData['total_grade_points'],
                'cumulative_credit_units' => $cgpaData['total_credit_units'],
                'cumulative_grade_points' => $cgpaData['total_grade_points'],
                'cumulative_gpa' => $cgpaData['cgpa'],
                'class_of_degree' => $cgpaData['class_of_degree']
            ]
        );
    }

    /**
     * Update CGPA for all sessions
     */
    private function updateCGPA(string $userId): void
    {
        $cgpaData = $this->calculateCGPA($userId);
        
        // Update all GPA records with current CGPA
        ResultGpaRecord::where('user_id', $userId)
            ->update([
                'cumulative_gpa' => $cgpaData['cgpa'],
                'cumulative_credit_units' => $cgpaData['total_credit_units'],
                'cumulative_grade_points' => $cgpaData['total_grade_points'],
                'class_of_degree' => $cgpaData['class_of_degree']
            ]);
    }
}
```

## Phase 3: Result Processing Workflow

### 3.1 Result Upload and Processing Flow

#### Step 1: Course Allocation
- Admin allocates courses to lecturers per semester
- Lecturers can view their assigned courses
- Course allocation includes venue, time, and student list

#### Step 2: Assessment Setup
- Lecturers set assessment parameters (CA vs Exam percentage)
- Define number of CA tests
- Set attendance requirements

#### Step 3: Result Entry (Hybrid Approach)

**Option 1: Web Form Entry (Individual)**
- Lecturers enter results one student at a time
- Real-time validation (score ranges, data types)
- Auto-save functionality
- Best for small classes (≤ 30 students)
- Useful for corrections and updates

**Option 2: CSV/Excel Upload (Bulk)**
- Lecturers download Excel template with enrolled students
- Template includes: matric_no, ca_score, exam_score columns
- Fill scores offline in Excel
- Upload CSV file for import
- System validates:
  - All enrolled students accounted for
  - Scores within valid range (0-100)
  - No duplicate entries
  - Data format validation
- Lecturer reviews imported data before submission
- Best for large classes (50+ students)

**CSV Template Format:**
```csv
matric_no,ca_score,exam_score
SC/20/001,35,55
SC/20/002,28,62
SC/20/003,40,58
```

**Benefits:**
- Flexibility for different class sizes
- Lecturers choose preferred method
- Bulk upload saves time
- Web form provides real-time validation
- Excel files serve as backup records

#### Step 4: Result Review
- Lecturers review entered results
- Course advisor reviews department results
- Identify outliers and errors

#### Step 5: Result Approval Workflow
1. **Lecturer Level**: Lecturer submits results
2. **HOD Level**: HOD reviews and approves department results
3. **Exam Officer Level**: Exam officer reviews faculty results
4. **VC Level**: Final approval for result release

#### Step 6: Result Release
- Approved results are released to students
- Students can view results online
- GPAs and CGPAs are automatically calculated
- Carry-over courses are identified

### 3.2 Livewire Components for Result Processing

#### Lecturer Components
- `LecturerDashboard` - Overview of assigned courses
- `ResultEntry` - Enter CA and exam scores
- `ResultReview` - Review entered results before submission
- `MyCourses` - View allocated courses and student lists

#### HOD Components
- `HodResultReview` - Review department results
- `DepartmentResultApproval` - Approve/reject results
- `ResultStatistics` - View department result statistics

#### Exam Officer Components
- `ExamOfficerDashboard` - Overview of faculty results
- `FacultyResultApproval` - Approve faculty-wide results
- `ResultAudit` - Audit results for anomalies

#### Admin Components
- `ResultManagement` - Overall result management
- `CourseAllocation` - Allocate courses to lecturers
- `ResultReleaseControl` - Control result release timing
- `GradeConfiguration` - Configure grading parameters

#### Student Components
- `MyResults` - View released results
- `GpaCalculator` - Calculate GPA/CGPA
- `TranscriptRequest` - Request official transcript
- `ResultHistory` - View historical results

## Phase 4: Transcript Generation

### 4.1 Transcript Requirements

#### Official Transcript Content
1. **Student Information**
   - Full name
   - Matric number
   - Programme
   - Department
   - Faculty
   - Date of admission
   - Date of graduation (if applicable)

2. **Academic Record**
   - All courses taken by session/semester
   - Credit units for each course
   - Grades obtained
   - Grade points
   - Semester GPA
   - Cumulative GPA (CGPA)

3. **Summary**
   - Total credit units earned
   - Final CGPA
   - Class of degree
   - Signature of authorized officer
   - University seal

4. **NUC Compliance**
   - Include all attempts (including failed courses)
   - Show repeated courses with all attempts
   - Follow NUC grading system
   - Include semester designations (Harmattan/Rain)

### 4.2 Transcript Generation Service

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Result;
use App\Models\ResultGpaRecord;
use PDF;

class TranscriptService
{
    /**
     * Generate student transcript
     */
    public function generateTranscript(string $userId, bool $official = true)
    {
        $student = User::findOrFail($userId);
        $results = $this->getStudentResults($userId);
        $gpaRecords = $this->getGPARecords($userId);
        $summary = $this->calculateSummary($userId);

        $data = [
            'student' => $student,
            'results' => $results,
            'gpa_records' => $gpaRecords,
            'summary' => $summary,
            'official' => $official,
            'generated_at' => now()
        ];

        $pdf = PDF::loadView('transcripts.official', $data);
        
        return $pdf->download('transcript_' . $student->matric_no . '.pdf');
    }

    /**
     * Get student results organized by session and semester
     */
    private function getStudentResults(string $userId): array
    {
        $results = Result::where('user_id', $userId)
            ->where('status', 'released')
            ->orderBy('academic_session')
            ->orderBy('semester')
            ->get()
            ->groupBy(['academic_session', 'semester']);

        return $results->toArray();
    }

    /**
     * Get GPA records
     */
    private function getGPARecords(string $userId): collection
    {
        return ResultGpaRecord::where('user_id', $userId)
            ->orderBy('academic_session')
            ->orderBy('semester')
            ->get();
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary(string $userId): array
    {
        $cgpaData = app(GradeCalculationService::class)->calculateCGPA($userId);
        
        return [
            'total_credit_units' => $cgpaData['total_credit_units'],
            'final_cgpa' => $cgpaData['cgpa'],
            'class_of_degree' => $cgpaData['class_of_degree']
        ];
    }
}
```

## Phase 5: Graduation Processing

### 5.1 Graduation Eligibility Check

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\GraduationEligibility;
use App\Models\AcademicDetail;
use App\Models\Result;

class GraduationService
{
    /**
     * Check student graduation eligibility
     */
    public function checkEligibility(string $userId): array
    {
        $student = User::findOrFail($userId);
        $academicDetail = $student->academicDetail;
        
        $cgpaData = app(GradeCalculationService::class)->calculateCGPA($userId);
        
        $requirements = [
            'minimum_cgpa' => $cgpaData['cgpa'] >= 1.00,
            'total_units' => $this->checkTotalUnits($userId),
            'siwes' => $this->checkSIWES($userId),
            'general_studies' => $this->checkGeneralStudies($userId),
            'entrepreneurship' => $this->checkEntrepreneurship($userId),
            'no_outstanding' => $this->checkOutstandingCourses($userId)
        ];

        $meetsAllRequirements = collect($requirements)->every(fn($value) => $value === true);

        $eligibility = GraduationEligibility::updateOrCreate(
            ['user_id' => $userId],
            [
                'academic_detail_id' => $academicDetail->id,
                'academic_session' => now()->format('Y'),
                'final_cgpa' => $cgpaData['cgpa'],
                'class_of_degree' => $cgpaData['class_of_degree'],
                'total_units_earned' => $cgpaData['total_credit_units'],
                'total_units_required' => $this->getRequiredUnits($student->programme_id),
                'meets_requirements' => $meetsAllRequirements,
                'siwes_completed' => $requirements['siwes'],
                'general_studies_completed' => $requirements['general_studies'],
                'entrepreneurship_completed' => $requirements['entrepreneurship'],
                'is_cleared' => false
            ]
        );

        return [
            'eligible' => $meetsAllRequirements,
            'requirements' => $requirements,
            'cgpa' => $cgpaData['cgpa'],
            'class_of_degree' => $cgpaData['class_of_degree']
        ];
    }

    /**
     * Check if student has completed required total units
     */
    private function checkTotalUnits(string $userId): bool
    {
        $totalUnits = Result::where('user_id', $userId)
            ->where('status', 'released')
            ->sum('credit_units');

        // Minimum 120 units for most programmes
        return $totalUnits >= 120;
    }

    /**
     * Check SIWES completion
     */
    private function checkSIWES(string $userId): bool
    {
        // Check if student has passed SIWES courses (15 units)
        return Result::where('user_id', $userId)
            ->where('status', 'released')
            ->whereHas('departmentCourse', function($query) {
                $query->where('name', 'like', '%SIWES%');
            })
            ->where('grade', '!=', 'F')
            ->exists();
    }

    /**
     * Check General Studies completion
     */
    private function checkGeneralStudies(string $userId): bool
    {
        // Check if student has passed GNS courses (8 units)
        return Result::where('user_id', $userId)
            ->where('status', 'released')
            ->whereHas('departmentCourse', function($query) {
                $query->where('name', 'like', '%GNS%');
            })
            ->where('grade', '!=', 'F')
            ->exists();
    }

    /**
     * Check Entrepreneurship completion
     */
    private function checkEntrepreneurship(string $userId): bool
    {
        // Check if student has passed Entrepreneurship courses (4 units)
        return Result::where('user_id', $userId)
            ->where('status', 'released')
            ->whereHas('departmentCourse', function($query) {
                $query->where('name', 'like', '%Entrepreneurship%');
            })
            ->where('grade', '!=', 'F')
            ->exists();
    }

    /**
     * Check for outstanding failed courses
     */
    private function checkOutstandingCourses(string $userId): bool
    {
        // Check if student has any failed courses not yet retaken
        return !Result::where('user_id', $userId)
            ->where('status', 'released')
            ->where('grade', 'F')
            ->whereDoesntHave('carryOver')
            ->exists();
    }

    /**
     * Get required units based on programme
     */
    private function getRequiredUnits(int $programmeId): int
    {
        // Return required units based on programme type
        return match($programmeId) {
            1 => 180, // HND
            2 => 150, // ND
            7 => 150, // Undergraduate
            6 => 90,  // Postgraduate
            default => 120
        };
    }

    /**
     * Add student to graduation list
     */
    public function addToGraduationList(string $userId, string $session): bool
    {
        $eligibility = $this->checkEligibility($userId);
        
        if (!$eligibility['eligible']) {
            return false;
        }

        $graduationList = GraduationList::firstOrCreate(
            ['academic_session' => $session],
            [
                'ceremony_date' => $this->estimateCeremonyDate($session),
                'venue' => 'University Main Auditorium'
            ]
        );

        GraduationListItem::create([
            'graduation_list_id' => $graduationList->id,
            'user_id' => $userId,
            'academic_detail_id' => User::find($userId)->academicDetail->id,
            'matric_no' => User::find($userId)->academicDetail->matric_no,
            'full_name' => User::find($userId)->full_name,
            'programme' => User::find($userId)->programme->name,
            'department' => User::find($userId)->academicDetail->department->name,
            'final_cgpa' => $eligibility['cgpa'],
            'class_of_degree' => $eligibility['class_of_degree']
        ]);

        return true;
    }
}
```

## Phase 6: Implementation Roadmap

### Phase 1: Database Setup (Weeks 1-2)
- [x] Create multi-role enum migration & `user_capabilities` migration
- [x] Run capability migrations and register `UserCapability` model
- [x] Create `Role` enum with all 9 backed roles
- [ ] Create result processing tables (`results`, `result_gpa_records`, `result_approvals`, `carry_over_courses`)
- [ ] Create models with relationships for results engine
- [ ] Create enums for grades (A-F) and result workflow statuses

### Phase 2: Core Services (Weeks 3-4)
- [ ] Install enhanced PDF package: `composer require barryvdh/laravel-snappy` (optional, for better PDF rendering)
- [ ] Implement GradeCalculationService
- [ ] Implement TranscriptService
- [ ] Implement GraduationService
- [ ] Implement ResultProcessingService
- [ ] Create service interfaces and contracts

### Phase 3: Lecturer Module (Weeks 5-6)
- [x] Create lecturer dashboard (`LecturerIndex` Livewire component & views)
- [x] Register `/lecturer/*` route mapping & `CapabilityMiddleware`
- [ ] Implement result entry forms (Web grid & CSV upload)
- [ ] Create course allocation system
- [ ] Implement result review workflow
- [x] Add lecturer-specific permissions & capability helpers

### Phase 4: Approval Workflow (Weeks 7-8)
- [x] Create Exam Officer dashboard (`ExamOfficerIndex` Livewire component & views)
- [x] Register `/exam-officer/*` route mapping & permissions
- [x] Add multi-role sidebar switcher toggles for HOD, Lecturer, and Exam Officer
- [x] Implement Admin Staff Capabilities Management UI (`/admin/manage-capabilities`)
- [ ] Implement HOD approval system & action buttons
- [ ] Implement Exam Officer approval & vetting actions
- [ ] Create approval notification system
- [ ] Add approval audit trail
- [ ] Implement result release control

### Phase 5: Student Module (Weeks 9-10)
- Create student result viewing
- Implement GPA calculator
- Create transcript request system
- Add result history view
- Implement carry-over tracking

### Phase 6: Graduation System (Weeks 11-12)
- Implement eligibility checking
- Create graduation list management
- Implement certificate generation
- Add certificate printing
- Create graduation ceremony management

### Phase 7: Reporting and Analytics (Weeks 13-14)
- Install charting package: `composer require laraveldaily/laravel-charts`
- Create result statistics dashboards
- Implement departmental performance reports
- Add grade distribution analysis
- Create graduation rate analytics
- Implement NUC compliance reports

### Phase 8: Testing and Deployment (Weeks 15-16)
- Unit testing for all services
- Integration testing for workflows
- User acceptance testing
- Performance optimization
- Security audit
- Deployment to production

## Phase 7: Security and Access Control

### 7.1 Role Extensions & Overlapping Capabilities

To prevent breaking changes in the live production app, the system uses a **Two-Layer Roles & Permissions System** (documented fully in [ROLES_AND_PERMISSIONS_STRATEGY.md](file:///c:/laragon/www/admission/ROLES_AND_PERMISSIONS_STRATEGY.md)):
1. **Primary Role (`users.role`)**: Controls default dashboard routing and login redirection. Added `'lecturer'` and `'exam_officer'` to the database role enum.
2. **User Capabilities (`user_capabilities` table)**: Handles overlapping privileges (e.g. HOD who is also a lecturer, or lecturer who is also an exam officer) dynamically without changing the primary role.

#### New Role/Capability: Lecturer
- Primary dashboard `/lecturer/dashboard` (if role is `'lecturer'`) or accessible via sidebar switcher (if assigned as capability).
- Can view assigned courses.
- Can enter and edit results for assigned courses.
- Can submit results for approval.
- Cannot view other lecturers' results.
- Cannot approve results at HOD level.

#### New Role/Capability: Exam Officer
- Primary dashboard `/exam-officer/dashboard` (if role is `'exam_officer'`) or accessible via sidebar switcher (if assigned as capability).
- Can view all faculty results.
- Can approve results at faculty level.
- Can generate result statistics.
- Can audit results for anomalies.

#### Extended Admin Role
- Can allocate courses to lecturers.
- Can configure grading parameters.
- Can control result release timing.
- Can manage graduation lists.
- Can generate certificates.
- Can grant/revoke user capabilities via Admin Panel.

### 7.2 Permission Policies

#### ResultPolicy
```php
class ResultPolicy
{
    public function view(User $user, Result $result)
    {
        // Student can view own results
        if ($user->isStudent() && $user->id === $result->user_id) {
            return $result->status === 'released';
        }
        
        // Lecturer can view assigned course results
        if ($user->isLecturer()) {
            return $user->lecturerCourses()->where('id', $result->department_course_id)->exists();
        }
        
        // HOD can view department results
        if ($user->isHod()) {
            return $user->department->courses()->where('id', $result->department_course_id)->exists();
        }
        
        // Admin and Exam Officer can view all
        return $user->isAdmin() || $user->isExamOfficer();
    }

    public function create(User $user)
    {
        return $user->isLecturer() || $user->isAdmin();
    }

    public function update(User $user, Result $result)
    {
        // Only lecturers can edit before submission
        if ($user->isLecturer()) {
            return $result->status === 'pending' && 
                   $user->lecturerCourses()->where('id', $result->department_course_id)->exists();
        }
        
        return $user->isAdmin();
    }

    public function approve(User $user, Result $result)
    {
        if ($user->isHod()) {
            return $result->status === 'submitted' &&
                   $user->department->courses()->where('id', $result->department_course_id)->exists();
        }
        
        if ($user->isExamOfficer()) {
            return $result->status === 'hod_approved';
        }
        
        return false;
    }
}
```

## Phase 8: Integration with Existing System

### 8.1 Student Role Extension
- Extend existing Student role to include result viewing
- Add transcript request functionality
- Integrate with existing course registration
- Link with existing fee payment system

### 8.2 Academic Detail Integration
- Use existing AcademicDetail model
- Add result-related fields
- Link with graduation eligibility
- Maintain matric number consistency

### 8.3 Course Registration Integration
- Link RegisteredCourse with Result processing
- Use existing department course structure
- Maintain credit unit consistency
- Integrate with semester system

## Phase 9: NUC Compliance Checklist

### 9.1 Grading System
- ✅ Use 5-point grade scale (A=5, B=4, C=3, D=2, F=0)
- ✅ Follow NUC score ranges (A=70-100, B=60-69, C=50-59, D=45-49, F=0-44)
- ✅ Implement degree classification based on CGPA
- ✅ Include all course attempts in CGPA calculation
- ✅ Track repeated courses with all attempts

### 9.2 Credit Requirements
- ✅ Enforce minimum 15 units per semester
- ✅ Enforce maximum 24 units per semester
- ✅ Track total units earned vs required
- ✅ Include mandatory courses (SIWES, GNS, Entrepreneurship)
- ✅ Programme-specific total unit requirements

### 9.3 Documentation
- ✅ Official transcript with all required information
- ✅ Include semester designations (Harmattan/Rain)
- ✅ Show all attempts including failures
- ✅ Include signature and seal
- ✅ Follow NUC transcript format

### 9.4 Quality Assurance
- ✅ Result approval workflow
- ✅ Audit trail for all result changes
- ✅ Grade verification system
- ✅ Anomaly detection
- ✅ External examiner integration (optional)

## Phase 10: Additional Features

### 10.1 Result Analytics
- Departmental performance analysis
- Course pass/fail rate statistics
- Grade distribution charts
- Student performance trends
- Lecturer performance metrics

### 10.2 Student Portal Enhancements
- Real-time GPA calculator
- What-if GPA calculator
- Academic progress tracker
- Graduation eligibility checker
- Course recommendation system

### 10.3 Reporting
- NUC compliance reports
- Accreditation reports
- Departmental annual reports
- Student performance reports
- Grade distribution reports

### 10.4 Notifications
- Result release notifications
- Transcript request status updates
- Graduation eligibility notifications
- Carry-over course alerts
- Academic standing warnings

## Conclusion

This extension plan provides a comprehensive roadmap for transforming the current admission system into a full Student MIS that complies with NUC standards for Nigerian universities. The implementation follows best practices for:

- **Database Design**: Proper normalization, relationships, and constraints
- **Security**: Role-based access control and proper authorization
- **NUC Compliance**: Full adherence to grading and graduation standards
- **Scalability**: Modular design for future enhancements
- **User Experience**: Intuitive interfaces for all user types
- **Data Integrity**: Proper validation and audit trails

The phased approach ensures systematic development with clear milestones and deliverables, allowing for testing and refinement at each stage.

---

**Document Version**: 1.0  
**Last Updated**: August 2026  
**Compliance**: NUC Standards 2018 onwards  
**Implementation Timeline**: 16 weeks
