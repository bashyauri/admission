# Course Versioning and Data Integrity System

## Problem Statement

The current course system lacks versioning, creating critical data integrity issues:

1. **Course Name Changes**: If "MAT101 - Elementary Mathematics" becomes "MAT101 - Introduction to Mathematics", transcripts for past students show the new name instead of what they actually took.

2. **Credit Unit Changes**: If a 3-unit course becomes 4-unit, GPA calculations for past students become incorrect if the system uses current course data.

3. **Course Code Changes**: If "CSC201" becomes "CSC301", historical records lose accuracy.

4. **Course Deletion/Replacement**: If a course is removed and replaced, students who took the old course have no valid reference.

## Solution: Course Versioning System

### Core Principle: **Snapshot at Registration**

When a student registers for a course, the system must capture a **snapshot** of all course details at that point in time. This ensures historical records remain accurate regardless of future changes.

## Database Schema Extensions

### 1. Course Versions Table

```php
Schema::create('course_versions', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(Course::class);
    $table->foreignIdFor(Department::class);
    $table->string('course_code');
    $table->string('course_title');
    $table->integer('credit_units');
    $table->string('semester')->default('first'); // 'first' or 'second'
    $table->integer('level'); // 100, 200, 300, 400
    $table->boolean('is_compulsory')->default(true);
    $table->boolean('is_prerequisite')->default(false);
    $table->string('academic_session'); // '2024-2025'
    $table->boolean('is_active')->default(true);
    $table->timestamp('effective_date');
    $table->timestamp('expiry_date')->nullable();
    $table->foreignId('created_by')->nullable();
    $table->text('change_reason')->nullable();
    $table->timestamps();
    $table->softDeletes();
    
    $table->unique(['course_code', 'academic_session']);
    $table->index(['course_code', 'is_active']);
});
```

### 2. Course Change History Table

```php
Schema::create('course_change_history', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(Course::class);
    $table->foreignId('previous_version_id')->nullable();
    $table->foreignId('new_version_id')->nullable();
    $table->string('change_type'); // 'name_change', 'unit_change', 'code_change', 'deletion', 'replacement'
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('academic_session');
    $table->foreignId('changed_by');
    $table->text('reason');
    $table->timestamp('change_date');
    $table->timestamps();
});
```

### 3. Updated Registered Course Table

```php
// Modify existing registered_courses table to include snapshot
Schema::table('registered_courses', function (Blueprint $table) {
    // Snapshot fields - these capture course details at registration time
    $table->string('course_code_snapshot')->after('department_course_id');
    $table->string('course_title_snapshot')->after('course_code_snapshot');
    $table->integer('credit_units_snapshot')->after('course_title_snapshot');
    $table->string('semester_snapshot')->after('credit_units_snapshot');
    $table->integer('level_snapshot')->after('semester_snapshot');
    
    // Link to course version for reference
    $table->foreignId('course_version_id')->nullable()->after('level_snapshot');
    
    // Indexes for historical queries
    $table->index(['course_code_snapshot', 'academic_session']);
});
```

### 4. Updated Results Table

```php
// Modify results table to include course snapshot
Schema::table('results', function (Blueprint $table) {
    // Snapshot fields for historical accuracy
    $table->string('course_code_snapshot')->after('department_course_id');
    $table->string('course_title_snapshot')->after('course_code_snapshot');
    $table->integer('credit_units_snapshot')->after('course_title_snapshot');
    $table->string('semester_snapshot')->after('credit_units_snapshot');
    $table->integer('level_snapshot')->after('semester_snapshot');
    $table->foreignId('course_version_id')->nullable()->after('level_snapshot');
});
```

### 5. Course Mapping Table (for course replacements)

```php
Schema::create('course_mappings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('old_course_version_id');
    $table->foreignId('new_course_version_id');
    $table->string('mapping_type'); // 'equivalent', 'replacement', 'upgrade'
    $table->string('academic_session_from');
    $table->string('academic_session_to')->nullable();
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable();
    $table->foreignId('created_by');
    $table->timestamps();
    
    $table->unique(['old_course_version_id', 'new_course_version_id']);
});
```

## Course Versioning Service

```php
<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseVersion;
use App\Models\CourseChangeHistory;
use App\Models\CourseMapping;
use Illuminate\Support\Facades\DB;

class CourseVersioningService
{
    /**
     * Create a new course version
     */
    public function createVersion(array $data, string $reason, int $userId): CourseVersion
    {
        return DB::transaction(function () use ($data, $reason, $userId) {
            // Check if this is a new course or version
            $existingVersion = CourseVersion::where('course_code', $data['course_code'])
                ->where('academic_session', $data['academic_session'])
                ->first();

            if ($existingVersion) {
                // This is a change to existing course
                return $this->updateExistingVersion($existingVersion, $data, $reason, $userId);
            }

            // Create new course version
            $version = CourseVersion::create([
                'course_id' => $data['course_id'] ?? null,
                'department_id' => $data['department_id'],
                'course_code' => $data['course_code'],
                'course_title' => $data['course_title'],
                'credit_units' => $data['credit_units'],
                'semester' => $data['semester'] ?? 'first',
                'level' => $data['level'],
                'is_compulsory' => $data['is_compulsory'] ?? true,
                'is_prerequisite' => $data['is_prerequisite'] ?? false,
                'academic_session' => $data['academic_session'],
                'is_active' => true,
                'effective_date' => now(),
                'created_by' => $userId,
                'change_reason' => $reason
            ]);

            return $version;
        });
    }

    /**
     * Update existing course version (creates new version)
     */
    private function updateExistingVersion(
        CourseVersion $existingVersion, 
        array $newData, 
        string $reason, 
        int $userId
    ): CourseVersion {
        // Deactivate old version
        $existingVersion->update([
            'is_active' => false,
            'expiry_date' => now()
        ]);

        // Record change history
        $this->recordChangeHistory($existingVersion, $newData, $reason, $userId);

        // Create new version
        $newVersion = CourseVersion::create([
            'course_id' => $existingVersion->course_id,
            'department_id' => $newData['department_id'] ?? $existingVersion->department_id,
            'course_code' => $newData['course_code'] ?? $existingVersion->course_code,
            'course_title' => $newData['course_title'] ?? $existingVersion->course_title,
            'credit_units' => $newData['credit_units'] ?? $existingVersion->credit_units,
            'semester' => $newData['semester'] ?? $existingVersion->semester,
            'level' => $newData['level'] ?? $existingVersion->level,
            'is_compulsory' => $newData['is_compulsory'] ?? $existingVersion->is_compulsory,
            'is_prerequisite' => $newData['is_prerequisite'] ?? $existingVersion->is_prerequisite,
            'academic_session' => $newData['academic_session'] ?? $existingVersion->academic_session,
            'is_active' => true,
            'effective_date' => now(),
            'created_by' => $userId,
            'change_reason' => $reason
        ]);

        return $newVersion;
    }

    /**
     * Record course change history
     */
    private function recordChangeHistory(
        CourseVersion $oldVersion, 
        array $newData, 
        string $reason, 
        int $userId
    ): void {
        $changes = $this->detectChanges($oldVersion, $newData);
        
        if (empty($changes)) {
            return; // No actual changes
        }

        CourseChangeHistory::create([
            'course_id' => $oldVersion->course_id,
            'previous_version_id' => $oldVersion->id,
            'new_version_id' => null, // Will be updated after new version is created
            'change_type' => $this->determineChangeType($changes),
            'old_values' => json_encode($this->getChangedFields($oldVersion, $changes)),
            'new_values' => json_encode($this->getNewValues($newData, $changes)),
            'academic_session' => $newData['academic_session'] ?? $oldVersion->academic_session,
            'changed_by' => $userId,
            'reason' => $reason,
            'change_date' => now()
        ]);
    }

    /**
     * Detect what fields changed
     */
    private function detectChanges(CourseVersion $oldVersion, array $newData): array
    {
        $changes = [];
        
        if (isset($newData['course_title']) && $newData['course_title'] !== $oldVersion->course_title) {
            $changes[] = 'course_title';
        }
        
        if (isset($newData['credit_units']) && $newData['credit_units'] !== $oldVersion->credit_units) {
            $changes[] = 'credit_units';
        }
        
        if (isset($newData['course_code']) && $newData['course_code'] !== $oldVersion->course_code) {
            $changes[] = 'course_code';
        }
        
        if (isset($newData['semester']) && $newData['semester'] !== $oldVersion->semester) {
            $changes[] = 'semester';
        }
        
        if (isset($newData['level']) && $newData['level'] !== $oldVersion->level) {
            $changes[] = 'level';
        }
        
        return $changes;
    }

    /**
     * Determine the type of change
     */
    private function determineChangeType(array $changes): string
    {
        if (in_array('course_code', $changes)) {
            return 'code_change';
        }
        
        if (in_array('credit_units', $changes)) {
            return 'unit_change';
        }
        
        if (in_array('course_title', $changes)) {
            return 'name_change';
        }
        
        return 'other_change';
    }

    /**
     * Get changed field values from old version
     */
    private function getChangedFields(CourseVersion $version, array $changes): array
    {
        $values = [];
        
        foreach ($changes as $field) {
            $values[$field] = $version->$field;
        }
        
        return $values;
    }

    /**
     * Get new values from data
     */
    private function getNewValues(array $data, array $changes): array
    {
        $values = [];
        
        foreach ($changes as $field) {
            $values[$field] = $data[$field] ?? null;
        }
        
        return $values;
    }

    /**
     * Get active course version for a session
     */
    public function getActiveVersion(string $courseCode, string $session): ?CourseVersion
    {
        return CourseVersion::where('course_code', $courseCode)
            ->where('academic_session', $session)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get course version at a specific date
     */
    public function getVersionAtDate(string $courseCode, string $date): ?CourseVersion
    {
        return CourseVersion::where('course_code', $courseCode)
            ->where('effective_date', '<=', $date)
            ->where(function($query) use ($date) {
                $query->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', $date);
            })
            ->where('is_active', true)
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Map old course to new course (for equivalencies)
     */
    public function mapCourses(
        int $oldVersionId, 
        int $newVersionId, 
        string $mappingType, 
        string $sessionFrom,
        string $notes = null,
        int $userId
    ): CourseMapping {
        return CourseMapping::create([
            'old_course_version_id' => $oldVersionId,
            'new_course_version_id' => $newVersionId,
            'mapping_type' => $mappingType,
            'academic_session_from' => $sessionFrom,
            'is_active' => true,
            'notes' => $notes,
            'created_by' => $userId
        ]);
    }

    /**
     * Get course snapshot for registration
     */
    public function getCourseSnapshot(int $courseVersionId): array
    {
        $version = CourseVersion::findOrFail($courseVersionId);
        
        return [
            'course_version_id' => $version->id,
            'course_code' => $version->course_code,
            'course_title' => $version->course_title,
            'credit_units' => $version->credit_units,
            'semester' => $version->semester,
            'level' => $version->level
        ];
    }
}
```

## Updated Course Registration Service

```php
<?php

namespace App\Services;

use App\Models\RegisteredCourse;
use App\Models\CourseVersion;
use App\Services\CourseVersioningService;

class CourseRegistrationService
{
    protected CourseVersioningService $versioningService;

    public function __construct(CourseVersioningService $versioningService)
    {
        $this->versioningService = $versioningService;
    }

    /**
     * Register student for course with snapshot
     */
    public function registerCourse(
        string $userId, 
        int $courseVersionId, 
        string $session, 
        string $semester
    ): RegisteredCourse {
        // Get course snapshot at time of registration
        $snapshot = $this->versioningService->getCourseSnapshot($courseVersionId);
        
        return RegisteredCourse::create([
            'academic_detail_id' => $this->getAcademicDetailId($userId),
            'department_course_id' => $this->getDepartmentCourseId($courseVersionId),
            'course_version_id' => $snapshot['course_version_id'],
            'course_code_snapshot' => $snapshot['course_code'],
            'course_title_snapshot' => $snapshot['course_title'],
            'credit_units_snapshot' => $snapshot['credit_units'],
            'semester_snapshot' => $snapshot['semester'],
            'level_snapshot' => $snapshot['level'],
            'units' => $snapshot['credit_units'], // For backward compatibility
            'academic_session' => $session,
            'student_level_id' => $this->getStudentLevelId($session)
        ]);
    }
}
```

## Assessment Configuration (Separate from Course Versioning)

### Assessment Settings Table

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

### Assessment Change Permissions

**Lecturer Permissions:**
- Can change CA percentage for their assigned courses
- Must provide reason for the change
- Changes within standard range (30-50% CA): Auto-approved
- Changes outside standard range: Requires HOD approval
- Cannot change after results are submitted

**Approval Workflow:**
1. Lecturer proposes change with reason
2. System validates range
3. If within range: Auto-approved, logged, notification to HOD
4. If outside range: HOD must approve
5. After result submission: No changes allowed

## Benefits of Course Versioning

1. **Transcript Accuracy**: Historical transcripts show exactly what students took
2. **Audit Trail**: Complete history of all course changes
3. **NUC Compliance**: Transcripts reflect actual courses taken
4. **Data Integrity**: No lost historical data
5. **Flexibility**: Courses can evolve without breaking records
6. **Course Replacements**: Mapping system handles equivalencies

## Course Upload Workflow

**Not Every Session Required:**
- No changes: System automatically carries forward courses
- Name/unit/code changes: Create new version for that session
- New courses: Create version for that session
- Curriculum overhaul: Bulk import new versions

**Smart Defaults:**
- At session start, copy all active courses from previous session
- Admin reviews and updates only what changed
- Bulk import available for major curriculum changes

## Integration with Result Processing

When generating transcripts:
- Use snapshot data from `registered_courses` and `results` tables
- Never reference current course data
- This ensures historical accuracy regardless of course changes

When calculating GPA:
- Use `credit_units_snapshot` from results table
- This ensures GPA calculations use correct units for that session

---

**Document Version**: 1.0  
**Last Updated**: August 2026  
**Related Documents**: 
- SYSTEM_DOCUMENTATION.md
- RESULT_PROCESSING_EXTENSION_PLAN.md
