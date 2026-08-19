# Result Processing Extension - Production Safe Implementation

**IMPORTANT: This app is in production. Follow this guide for safe implementation of result processing features.**

## Safe for Live Production (Can be done anytime)

### 1. Create New Tables (No Impact on Existing Data)

These tables are completely new and don't affect existing functionality:

```php
// Course versioning tables
Schema::create('course_versions', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(Course::class);
    $table->foreignIdFor(Department::class);
    $table->string('course_code');
    $table->string('course_title');
    $table->integer('credit_units');
    $table->string('semester')->default('first');
    $table->integer('level');
    $table->boolean('is_compulsory')->default(true);
    $table->boolean('is_prerequisite')->default(false);
    $table->string('academic_session');
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

Schema::create('course_change_history', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(Course::class);
    $table->foreignId('previous_version_id')->nullable();
    $table->foreignId('new_version_id')->nullable();
    $table->string('change_type');
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('academic_session');
    $table->foreignId('changed_by');
    $table->text('reason');
    $table->timestamp('change_date');
    $table->timestamps();
});

Schema::create('course_mappings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('old_course_version_id');
    $table->foreignId('new_course_version_id');
    $table->string('mapping_type');
    $table->string('academic_session_from');
    $table->string('academic_session_to')->nullable();
    $table->boolean('is_active')->default(true);
    $table->text('notes')->nullable();
    $table->foreignId('created_by');
    $table->timestamps();
    $table->unique(['old_course_version_id', 'new_course_version_id']);
});

// Academic records tables
Schema::create('academic_sessions', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('semesters', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(AcademicSession::class);
    $table->string('name');
    $table->string('type');
    $table->date('start_date');
    $table->date('end_date');
    $table->boolean('is_current')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Result processing tables
Schema::create('results', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(RegisteredCourse::class);
    $table->foreignIdFor(DepartmentCourse::class);
    $table->foreignIdFor(AcademicDetail::class);
    $table->string('semester');
    $table->string('academic_session');
    $table->decimal('ca_score', 5, 2)->nullable();
    $table->decimal('exam_score', 5, 2)->nullable();
    $table->decimal('total_score', 5, 2)->nullable();
    $table->string('grade')->nullable();
    $table->integer('grade_point')->nullable();
    $table->integer('credit_units');
    $table->integer('grade_point_total')->nullable();
    $table->string('status')->default('pending');
    $table->foreignId('lecturer_id')->nullable();
    $table->foreignId('hod_approved_by')->nullable();
    $table->timestamp('hod_approved_at')->nullable();
    $table->foreignId('exam_officer_approved_by')->nullable();
    $table->timestamp('exam_officer_approved_at')->nullable();
    $table->text('remarks')->nullable();
    $table->boolean('is_repeated')->default(false);
    $table->string('original_result_id')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->unique(['user_id', 'registered_course_id', 'academic_session', 'semester']);
});

Schema::create('result_gpa_records', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(AcademicDetail::class);
    $table->string('semester');
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

Schema::create('result_approvals', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->string('academic_session');
    $table->string('semester');
    $table->string('approval_level');
    $table->foreignId('approved_by');
    $table->timestamp('approved_at');
    $table->string('status')->default('pending');
    $table->text('comments')->nullable();
    $table->timestamps();
});

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
    $table->boolean('auto_registered')->default(false);
    $table->timestamp('auto_registered_at')->nullable();
    $table->timestamps();
});

// Graduation tables
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

Schema::create('certificates', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(GraduationList::class);
    $table->string('certificate_number')->unique();
    $table->string('certificate_type');
    $table->string('class_of_degree');
    $table->date('issue_date');
    $table->string('file_path')->nullable();
    $table->boolean('is_printed')->default(false);
    $table->boolean('is_collected')->default(false);
    $table->timestamp('collected_at')->nullable();
    $table->foreignId('collected_by')->nullable();
    $table->timestamps();
});

Schema::create('transcripts', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->string('request_number')->unique();
    $table->string('destination')->nullable();
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

// Lecturer and assessment tables
Schema::create('lecturers', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id');
    $table->foreignIdFor(Department::class);
    $table->string('staff_id')->unique();
    $table->string('rank')->nullable();
    $table->string('specialization')->nullable();
    $table->boolean('is_course_advisor')->default(false);
    $table->boolean('is_exam_officer')->default(false);
    $table->timestamps();
});

Schema::create('assessment_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(DepartmentCourse::class);
    $table->foreignIdFor(AcademicSession::class);
    $table->foreignIdFor(Semester::class);
    $table->decimal('ca_percentage', 5, 2)->default(40);
    $table->decimal('exam_percentage', 5, 2)->default(60);
    $table->integer('number_of_ca_tests')->default(2);
    $table->boolean('attendance_mandatory')->default(true);
    $table->decimal('attendance_percentage', 5, 2)->default(10);
    $table->timestamps();
});

Schema::create('course_allocations', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(DepartmentCourse::class);
    $table->foreignIdFor(AcademicSession::class);
    $table->foreignIdFor(Semester::class);
    $table->foreignId('lecturer_id');
    $table->foreignId('department_id');
    $table->integer('assigned_units');
    $table->string('venue')->nullable();
    $table->string('day_of_week')->nullable();
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->timestamps();
});
```

**Risk:** None - these are new tables
**Downtime:** None
**Impact:** No effect on existing functionality

## Requires Maintenance Window (Scheduled downtime)

### 2. Modify Existing Tables (Add Snapshot Fields)

These changes modify existing tables and require careful migration:

```php
// Modify registered_courses table
Schema::table('registered_courses', function (Blueprint $table) {
    // Add snapshot fields
    $table->string('course_code_snapshot')->after('department_course_id')->nullable();
    $table->string('course_title_snapshot')->after('course_code_snapshot')->nullable();
    $table->integer('credit_units_snapshot')->after('course_title_snapshot')->nullable();
    $table->string('semester_snapshot')->after('credit_units_snapshot')->nullable();
    $table->integer('level_snapshot')->after('semester_snapshot')->nullable();
    
    // Add course version reference
    $table->foreignId('course_version_id')->nullable()->after('level_snapshot');
    
    // Add indexes
    $table->index(['course_code_snapshot', 'academic_session']);
});

// Backfill existing data with current course details
DB::statement('
    UPDATE registered_courses rc
    JOIN department_courses dc ON rc.department_course_id = dc.id
    JOIN student_courses sc ON dc.student_course_id = sc.id
    SET 
        rc.course_code_snapshot = sc.code,
        rc.course_title_snapshot = sc.name,
        rc.credit_units_snapshot = dc.units
');

// Modify results table (when created, add snapshot fields)
// This is in the new table creation above, so no migration needed
```

**Risk:** Medium - data backfill required
**Downtime:** 15-30 minutes
**Backup Required:** Yes
**Testing Required:** Verify backfill accuracy

### 3. Extend department_max_units Table

```php
// Add min_units field
Schema::table('department_max_units', function (Blueprint $table) {
    $table->integer('min_units')->after('max_units')->default(15);
    $table->unique(['department_id', 'student_level_id']);
});

// Rename table (optional, for clarity)
Schema::rename('department_max_units', 'department_level_units');
```

**Risk:** Low - additive change only
**Downtime:** 5-10 minutes
**Backup Required:** Yes

### 4. Extend users.role Enum Column

Modify the enum values in the `users` table to add `'lecturer'` and `'exam_officer'` (plus any missing cases like `'cit', 'coordinator', 'idcard_officer'`):

```php
DB::statement("ALTER TABLE users MODIFY COLUMN role 
    ENUM('applicant', 'student', 'graduate', 'hod', 'admin', 
         'cit', 'coordinator', 'idcard_officer', 'lecturer', 'exam_officer') 
    DEFAULT 'applicant'");
```

**Risk:** Low - adding values to an enum doesn't require rebuilding rows in MySQL 8.0+
**Downtime:** 1-2 minutes
**Backup Required:** Yes

## Implementation Strategy for Production

### Phase 1 - Immediate (No downtime, low risk):
1. Create all new tables (course_versions, results, graduation tables, user_capabilities, etc.)
2. Add indexes to new tables
3. Create new models and services
4. Build new Livewire components (behind feature flags)

### Phase 2 - Next maintenance window (medium risk):
5. Add snapshot fields to registered_courses
6. Backfill existing data
7. Extend department_max_units table
8. Extend users.role Enum Column
9. Test course registration with new fields and verify role redirects

### Phase 3 - Gradual Rollout:
9. Enable result processing for NEW academic session only
10. Keep existing system running for current session
11. Migrate data gradually between sessions
12. Full cutover after validation

## Feature Flag Strategy

Use feature flags to control rollout:

```php
// config/features.php
return [
    'result_processing' => env('FEATURE_RESULT_PROCESSING', false),
    'course_versioning' => env('FEATURE_COURSE_VERSIONING', false),
    'graduation_system' => env('FEATURE_GRADUATION_SYSTEM', false),
];

// In code
if (config('features.result_processing')) {
    // New result processing logic
} else {
    // Existing logic (if any)
}
```

## Testing Strategy

### Before Production Rollout:

1. **Staging Environment:**
   - Full migration test
   - Data backfill verification
   - Performance testing with production data copy
   - User acceptance testing

2. **Canary Deployment:**
   - Enable for single department first
   - Monitor for 1 week
   - Gradual expansion to other departments

3. **Rollback Plan:**
   - Document exact rollback steps
   - Test rollback in staging
   - Have backup ready

## Monitoring After Rollout

Monitor these metrics for 2 weeks:
- New table query performance
- Registration process speed
- Result entry workflow
- Backfill data accuracy
- User-reported issues

## Data Migration for Existing Students

For students already in the system:

```php
// Create course versions for existing courses
// This should be done in a separate migration
$existingCourses = Course::all();
foreach ($existingCourses as $course) {
    CourseVersion::create([
        'course_id' => $course->id,
        'department_id' => $course->department_id,
        'course_code' => $course->code,
        'course_title' => $course->name,
        'credit_units' => $course->units,
        'academic_session' => '2024-2025', // Current session
        'is_active' => true,
        'effective_date' => now(),
    ]);
}
```

## Backward Compatibility

Ensure existing functionality continues to work:

- Course registration still works without versioning initially
- Existing registered_courses records remain valid
- New fields are nullable initially
- Gradual migration of data

## Pre-Production Checklist

Before any maintenance window:

- [ ] Full database backup completed
- [ ] Backup verified (can restore)
- [ ] Staging environment tested with same changes
- [ ] Feature flags configured
- [ ] Rollback plan documented
- [ ] Maintenance window communicated to users
- [ ] Application in maintenance mode
- [ ] Migration executed
- [ ] Data integrity verified
- [ ] Backfill data validated
- [ ] Application tested
- [ ] Feature flags enabled gradually
- [ ] Monitor for 48 hours
- [ ] Delete backups after successful verification

## Rollback Plan

```sql
-- Rollback snapshot fields
ALTER TABLE registered_courses DROP COLUMN course_code_snapshot;
ALTER TABLE registered_courses DROP COLUMN course_title_snapshot;
ALTER TABLE registered_courses DROP COLUMN credit_units_snapshot;
ALTER TABLE registered_courses DROP COLUMN semester_snapshot;
ALTER TABLE registered_courses DROP COLUMN level_snapshot;
ALTER TABLE registered_courses DROP COLUMN course_version_id;

-- Rollback department_level_units
ALTER TABLE department_level_units DROP COLUMN min_units;
ALTER TABLE department_level_units DROP INDEX department_id_student_level_id_unique;

-- Drop new tables (if needed)
DROP TABLE IF EXISTS course_versions;
DROP TABLE IF EXISTS results;
-- etc.
```

---

**Last Updated:** August 2026  
**Status:** Production Safe Implementation Guide  
**Risk Level:** Documented per change  
**Rollout Strategy:** Phased with feature flags
