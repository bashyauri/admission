# Database Optimization - Production Safe Changes

**IMPORTANT: This app is in production. Only implement changes marked as "Safe for Live Production". Changes marked as "Requires Maintenance Window" need scheduled downtime.**

## Safe for Live Production (Can be done anytime)

### 1. Add Missing Indexes on Foreign Keys

These can be added without breaking existing data or requiring downtime:

```php
// Migration: add_missing_indexes.php
Schema::table('olevel_subject_grades', function (Blueprint $table) {
    $table->index('user_id');
});

Schema::table('proposed_courses', function (Blueprint $table) {
    $table->index('user_id');
});

Schema::table('certificate_uploads', function (Blueprint $table) {
    $table->index('user_id');
});

Schema::table('transactions', function (Blueprint $table) {
    $table->index('user_id');
});
```

**Impact:** Improves query performance for user-related lookups
**Risk:** None - indexes are additive only
**Downtime:** None (may cause slight slowdown during index creation)

### 2. Add Composite Indexes for Common Query Patterns

```php
// Migration: add_composite_indexes.php
Schema::table('registered_courses', function (Blueprint $table) {
    $table->index(['academic_detail_id', 'academic_session']);
});

Schema::table('transactions', function (Blueprint $table) {
    $table->index(['user_id', 'status']);
});

Schema::table('proposed_courses', function (Blueprint $table) {
    $table->index(['user_id', 'status']);
});
```

**Impact:** Speeds up common filter queries
**Risk:** None
**Downtime:** None

### 3. Add Status Indexes

```php
Schema::table('proposed_courses', function (Blueprint $table) {
    $table->index('status');
});
```

**Impact:** Faster filtering by admission status
**Risk:** None
**Downtime:** None

## Requires Maintenance Window (Schedule downtime)

### 4. Fix registered_courses Unique Constraint

**Current Issue:** 
```php
$table->foreignIdFor(DepartmentCourse::class)->constrained()->unique();
```
This prevents students from retaking courses (carry-overs).

**Safe Approach for Production:**

```php
// Step 1: Create new table with correct structure
Schema::create('registered_courses_v2', function (Blueprint $table) {
    $table->id();
    $table->foreignIdFor(AcademicDetail::class)->constrained();
    $table->foreignIdFor(DepartmentCourse::class)->constrained();
    $table->foreignIdFor(StudentLevel::class)->constrained();
    $table->string('units');
    $table->string('academic_session');
    $table->unique(['academic_detail_id', 'department_course_id', 'academic_session']);
    $table->timestamps();
});

// Step 2: Migrate data
DB::statement('INSERT INTO registered_courses_v2 SELECT * FROM registered_courses');

// Step 3: Verify data integrity
// Step 4: Backup old table
Schema::rename('registered_courses', 'registered_courses_backup');
Schema::rename('registered_courses_v2', 'registered_courses');

// Step 5: Update model to use new table
// Step 6: Test thoroughly
// Step 7: Delete backup after verification
```

**Risk:** Medium - data migration required
**Downtime:** 30-60 minutes
**Backup Required:** Yes

### 5. Fix Data Types (transactions table)

**Current Issue:**
- `amount` is string instead of decimal
- `date` is string instead of date

**Safe Approach:**

```php
// Step 1: Add new columns with correct types
Schema::table('transactions', function (Blueprint $table) {
    $table->decimal('amount_new', 10, 2)->nullable();
    $table->date('date_new')->nullable();
});

// Step 2: Migrate data with validation
DB::statement('UPDATE transactions SET amount_new = CAST(amount AS DECIMAL(10,2)) WHERE amount REGEXP "^[0-9.]+$"');
DB::statement('UPDATE transactions SET date_new = STR_TO_DATE(date, "%Y-%m-%d") WHERE date REGEXP "^[0-9-]+$"');

// Step 3: Validate migration (check for NULLs in new columns)
// Step 4: If validation passes, drop old columns and rename new ones
Schema::table('transactions', function (Blueprint $table) {
    $table->dropColumn('amount');
    $table->dropColumn('date');
});

Schema::table('transactions', function (Blueprint $table) {
    $table->decimal('amount', 10, 2);
    $table->date('date');
});

// Step 5: Migrate data again
DB::statement('UPDATE transactions SET amount = amount_new, date = date_new');
Schema::table('transactions', function (Blueprint $table) {
    $table->dropColumn('amount_new');
    $table->dropColumn('date_new');
});
```

**Risk:** High - data type conversion can fail if data is malformed
**Downtime:** 1-2 hours
**Backup Required:** Yes
**Testing Required:** Extensive

### 6. Fix registered_courses units Data Type

**Current Issue:** `units` is string instead of integer

**Safe Approach:**

```php
// Step 1: Add new column
Schema::table('registered_courses', function (Blueprint $table) {
    $table->integer('units_new')->nullable();
});

// Step 2: Migrate data
DB::statement('UPDATE registered_courses SET units_new = CAST(units AS UNSIGNED) WHERE units REGEXP "^[0-9]+$"');

// Step 3: Validate (check for NULLs)
// Step 4: If validation passes, replace
Schema::table('registered_courses', function (Blueprint $table) {
    $table->dropColumn('units');
});

Schema::table('registered_courses', function (Blueprint $table) {
    $table->integer('units');
});

DB::statement('UPDATE registered_courses SET units = units_new');
Schema::table('registered_courses', function (Blueprint $table) {
    $table->dropColumn('units_new');
});
```

**Risk:** Medium - data validation required
**Downtime:** 30-45 minutes
**Backup Required:** Yes

## NOT Recommended for Production

### 7. UUID to Integer Conversion

**Issue:** UUID foreign keys are slower than integers

**Recommendation:** DO NOT attempt this in production
- Requires complete rewrite of foreign key relationships
- Massive data migration
- High risk of data corruption
- Application code changes throughout system

**Alternative:** Accept UUID performance penalty (minimal impact in practice)

## Implementation Priority

### Phase 1 - Immediate (Next maintenance window, low risk):
1. Add missing indexes on foreign keys (Safe)
2. Add composite indexes (Safe)
3. Add status indexes (Safe)

### Phase 2 - Next scheduled maintenance (medium risk):
4. Fix registered_courses unique constraint
5. Fix registered_courses units data type

### Phase 3 - Major maintenance (high risk):
6. Fix transactions data types

### Phase 4 - Never:
7. UUID to integer conversion

## Pre-Production Checklist

Before any maintenance window change:

- [ ] Full database backup completed
- [ ] Backup verified (can restore)
- [ ] Staging environment tested with same changes
- [ ] Rollback plan documented
- [ ] Maintenance window communicated to users
- [ ] Application in maintenance mode
- [ ] Change executed
- [ ] Data integrity verified
- [ ] Application tested
- [ ] Application taken out of maintenance mode
- [ ] Monitor for 24 hours
- [ ] Delete backups after successful verification

## Monitoring After Changes

Monitor these metrics for 1 week after changes:
- Query response times
- Database CPU usage
- Error rates
- User-reported issues

## Rollback Plan

For each change, document exact rollback steps:
```sql
-- Example rollback for index addition
ALTER TABLE olevel_subject_grades DROP INDEX user_id;
```

---

**Last Updated:** August 2026  
**Status:** Production Safe Changes Only  
**Risk Level:** Documented per change
