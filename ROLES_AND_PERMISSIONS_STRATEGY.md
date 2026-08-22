# Roles & Permissions Strategy — Production-Safe Multi-Role Support

**Status:** Implemented (Completed)  
**Last Updated:** August 2026  
**Risk Level:** Low (additive changes only)  
**Related Documents:**  
- [SYSTEM_DOCUMENTATION.md](file:///c:/laragon/www/admission/SYSTEM_DOCUMENTATION.md)  
- [RESULT_PROCESSING_EXTENSION_PLAN.md](file:///c:/laragon/www/admission/RESULT_PROCESSING_EXTENSION_PLAN.md)  
- [RESULT_PROCESSING_PRODUCTION_SAFE.md](file:///c:/laragon/www/admission/RESULT_PROCESSING_PRODUCTION_SAFE.md)  

---

## Problem Statement

The current system stores a **single role** per user in the `users.role` enum column:

```
enum('applicant', 'student', 'graduate', 'hod', 'admin')
```

This creates two blockers for the Result Processing extension:

1. **No Lecturer or Exam Officer role exists** — there's nowhere to assign a pure lecturer.
2. **A HOD who also teaches cannot enter results** — they're locked to `role = 'hod'` and can only access `/hod/*` routes.

### Real-World Overlapping Roles

| Person | Primary Role | Also Needs |
|---|---|---|
| Dr. Aminu (HOD, teaches CSC201) | HOD | Lecturer access |
| Dr. Bala (just teaches MAT101) | Lecturer | — |
| Prof. Chioma (HOD, Exam Officer) | HOD | Exam Officer access |
| Mrs. Dayo (Exam Officer, teaches GNS101) | Exam Officer | Lecturer access |

---

## Do We Need a Package (e.g., Spatie Permission)?

**No.** Here's why:

| Factor | Spatie Permission | Custom Capabilities |
|---|---|---|
| **Complexity** | Adds `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` tables (5 tables). | Adds 1 table (`user_capabilities`). |
| **Migration Risk** | Requires migrating ALL existing role logic to Spatie's system — **high risk of breaking production**. | Layers on top of existing role system — **zero risk**. |
| **Learning Curve** | New API (`$user->hasRole()`, `$user->givePermissionTo()`, `@can` changes). | Simple `$user->hasCapability('lecturer')` — follows your existing patterns. |
| **Overkill?** | Yes — designed for apps with dozens of granular permissions. | Our case is simple: ~3-4 extra capabilities. |
| **Performance** | Extra joins on every request via middleware. | Single fast query on `user_capabilities`. |

**Bottom line:** Spatie Permission is excellent for complex RBAC, but your system has a small, well-defined set of roles. A single `user_capabilities` table is simpler, safer, and follows your existing code patterns without risking database corruption or migration failure on a live production app.

---

## Solution Architecture

### Two-Layer Approach

```
Layer 1: users.role (existing)
├── Determines PRIMARY dashboard and route group
├── Controls login redirect
├── Untouched for existing roles (applicant, student, hod, admin, etc.)
└── ADD 'lecturer' and 'exam_officer' to the enum

Layer 2: user_capabilities (new table)
├── Grants ADDITIONAL access beyond primary role
├── A HOD with capability 'lecturer' can access /lecturer/* routes
├── A Lecturer with capability 'exam_officer' can access /exam-officer/* routes
└── Checked by new CapabilityMiddleware (does NOT replace RoleMiddleware)
```

### Who Sees What Dashboard?

| User | `users.role` | `user_capabilities` | Primary Dashboard | Can Also Access |
|---|---|---|---|---|
| **Pure Lecturer** | `lecturer` | — | `/lecturer/dashboard` | — |
| **HOD who teaches** | `hod` | `lecturer` | `/hod/dashboard` | `/lecturer/*` (via sidebar/switcher link) |
| **Pure Exam Officer** | `exam_officer` | — | `/exam-officer/dashboard` | — |
| **Lecturer + Exam Officer** | `lecturer` | `exam_officer` | `/lecturer/dashboard` | `/exam-officer/*` (via sidebar/switcher link) |
| **HOD + Exam Officer** | `hod` | `exam_officer`, `lecturer` | `/hod/dashboard` | `/lecturer/*`, `/exam-officer/*` |
| **Admin** | `admin` | — | `/admin/dashboard` | Everything (admin bypass) |

**Rule:** The `users.role` column determines your **home base** dashboard. Capabilities represent **additional panels/routes** you are allowed to access.

---

## Database Changes

### Step 1: Extend the `users.role` Enum (Maintenance Window Required)

This is the **only change to an existing table**. It's an `ALTER TABLE` that adds values to the enum — existing rows are untouched.

```php
// Migration: extend_users_role_enum.php
public function up(): void
{
    DB::statement("ALTER TABLE users MODIFY COLUMN role 
        ENUM('applicant', 'student', 'graduate', 'hod', 'admin', 
             'cit', 'coordinator', 'idcard_officer', 'lecturer', 'exam_officer') 
        DEFAULT 'applicant'");
}

public function down(): void
{
    // Only safe if no users have the new roles
    DB::statement("ALTER TABLE users MODIFY COLUMN role 
        ENUM('applicant', 'student', 'graduate', 'hod', 'admin') 
        DEFAULT 'applicant'");
}
```

**Risk:** Low — `ALTER TABLE ... MODIFY COLUMN` on an enum that only ADDS values does not rewrite existing rows.  
**Downtime:** 1-2 minutes.  
**Backup Required:** Yes.

### Step 2: Create `user_capabilities` Table (Safe, No Downtime)

```php
// Migration: create_user_capabilities_table.php
Schema::create('user_capabilities', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
    $table->string('capability');       // 'lecturer', 'exam_officer', 'course_advisor'
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamp('granted_at')->useCurrent();
    $table->timestamp('revoked_at')->nullable();
    $table->foreignUuid('granted_by')->nullable();
    $table->text('reason')->nullable();
    $table->timestamps();
    
    $table->unique(['user_id', 'capability', 'department_id']);
    $table->index(['user_id', 'is_active']);
    $table->index('capability');
});
```

**Risk:** None — brand new table.  
**Downtime:** None.

---

## Code Changes

### 1. Update PHP Role Enum

Update [`app/Enums/Role.php`](file:///c:/laragon/www/admission/app/Enums/Role.php) to support the new options:

```php
// app/Enums/Role.php
enum Role: string
{
    case HOD = 'hod';
    case APPLICANT = 'applicant';
    case ADMIN = 'admin';
    case STUDENT = 'student';
    case CIT = 'cit';
    case COORDINATOR = 'coordinator';
    case IDCARD_OFFICER = 'idcard_officer';
    case LECTURER = 'lecturer';           // NEW
    case EXAM_OFFICER = 'exam_officer';   // NEW

    public function toString(): string
    {
        return match ($this) {
            self::HOD => 'Head of Department',
            self::APPLICANT => 'Applicant',
            self::ADMIN => 'Admin',
            self::STUDENT => 'Student',
            self::CIT => 'CIT',
            self::COORDINATOR => 'Coordinator',
            self::IDCARD_OFFICER => 'ID Card Officer',
            self::LECTURER => 'Lecturer',
            self::EXAM_OFFICER => 'Exam Officer',
        };
    }

    public static function getRoles(): array
    {
        return array_map(function (Role $role) {
            return [
                'name' => $role->toString(),
                'value' => $role->value,
            ];
        }, self::cases());
    }
}
```

### 2. Create UserCapability Model (New File)

Create [`app/Models/UserCapability.php`](file:///c:/laragon/www/admission/app/Models/UserCapability.php):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCapability extends Model
{
    protected $fillable = [
        'user_id',
        'capability',
        'department_id',
        'is_active',
        'granted_at',
        'revoked_at',
        'granted_by',
        'reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
```

### 3. Add Methods to User Model (Additive — No Existing Code Touched)

Add the following to [`app/Models/User.php`](file:///c:/laragon/www/admission/app/Models/User.php):

```php
// RELATIONSHIPS
public function capabilities(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(UserCapability::class)->where('is_active', true);
}

// CAPABILITY CHECKS
public function hasCapability(string $capability): bool
{
    return $this->capabilities()->where('capability', $capability)->exists();
}

public function canActAsLecturer(): bool
{
    return $this->role === Role::LECTURER->value || $this->hasCapability('lecturer');
}

public function canActAsExamOfficer(): bool
{
    return $this->role === Role::EXAM_OFFICER->value || $this->hasCapability('exam_officer');
}

public function isLecturer(): bool
{
    if ($this->impersonateRole) {
        return $this->impersonateRole === Role::LECTURER->value;
    }
    return $this->role === Role::LECTURER->value;
}

public function isExamOfficer(): bool
{
    if ($this->impersonateRole) {
        return $this->impersonateRole === Role::EXAM_OFFICER->value;
    }
    return $this->role === Role::EXAM_OFFICER->value;
}

public function capabilityDepartments(string $capability): array
{
    return $this->capabilities()
        ->where('capability', $capability)
        ->pluck('department_id')
        ->filter()
        ->toArray();
}

public function activeCapabilities(): array
{
    return $this->capabilities()->pluck('capability')->toArray();
}
```

### 4. Create CapabilityMiddleware (New File)

Create [`app/Http/Middleware/CapabilityMiddleware.php`](file:///c:/laragon/www/admission/app/Http/Middleware/CapabilityMiddleware.php):

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CapabilityMiddleware
{
    public function handle(Request $request, Closure $next, string $capabilities): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin bypasses all checks
        if ($user->isAdmin()) {
            return $next($request);
        }

        $required = collect(explode(',', $capabilities))
            ->map(fn($c) => trim($c))
            ->filter()
            ->all();

        foreach ($required as $capability) {
            // Check primary role match (e.g. a pure lecturer passes a 'lecturer' check)
            if ($user->role === $capability) {
                return $next($request);
            }

            // Check capability mappings
            if ($user->hasCapability($capability)) {
                return $next($request);
            }
        }

        // Direct user back to their primary role's dashboard if not authorized
        $redirectRoute = match ($user->role) {
            'hod' => 'hod.dashboard',
            'admin' => 'admin.dashboard',
            'student' => 'student.dashboard',
            'cit' => 'cit.dashboard',
            'coordinator' => 'coordinator.dashboard',
            'idcard_officer' => 'idcard.processing',
            'lecturer' => 'lecturer.dashboard',
            'exam_officer' => 'exam-officer.dashboard',
            default => 'analytics',
        };

        return redirect()->route($redirectRoute);
    }
}
```

### 5. Update RoleMiddleware

Update [`app/Http/Middleware/RoleMiddleware.php`](file:///c:/laragon/www/admission/app/Http/Middleware/RoleMiddleware.php):

```php
$redirectRoute = match ($userRole) {
    'hod' => 'hod.dashboard',
    'admin' => 'admin.dashboard',
    'student' => 'student.dashboard',
    'cit' => 'cit.dashboard',
    'coordinator' => 'coordinator.dashboard',
    'idcard_officer' => 'idcard.processing',
    'lecturer' => 'lecturer.dashboard',           // ADD THIS
    'exam_officer' => 'exam-officer.dashboard',   // ADD THIS
    default => 'analytics',
};
```

### 6. Update RouteServiceProvider

Update [`app/Providers/RouteServiceProvider.php`](file:///c:/laragon/www/admission/app/Providers/RouteServiceProvider.php) to bind these route files:

```php
// In the boot() method routes callback:
$this->mapLecturerRoutes();
$this->mapExamOfficerRoutes();

// Define these mapping methods:
protected function mapLecturerRoutes()
{
    Route::middleware(['web', 'auth', 'verified', 'capability:lecturer'])
        ->prefix('lecturer')
        ->as('lecturer.')
        ->group(base_path('routes/lecturer.php'));
}

protected function mapExamOfficerRoutes()
{
    Route::middleware(['web', 'auth', 'verified', 'capability:exam_officer'])
        ->prefix('exam-officer')
        ->as('exam-officer.')
        ->group(base_path('routes/exam_officer.php'));
}
```

### 7. Update RedirectIfAuthenticated Middleware

Update [`app/Http/Middleware/RedirectIfAuthenticated.php`](file:///c:/laragon/www/admission/app/Http/Middleware/RedirectIfAuthenticated.php):

```php
$redirectRoute = match ($user->role) {
    'admin' => '/admin/dashboard',
    'hod' => '/hod/dashboard',
    'student' => '/student/dashboard',
    'cit' => '/cit/dashboard',
    'coordinator' => '/coordinator/dashboard',
    'idcard_officer' => '/idcard/processing',
    'lecturer' => '/lecturer/dashboard',           // ADD THIS
    'exam_officer' => '/exam-officer/dashboard',   // ADD THIS
    default => '/dashboard/analytics',
};
```

---

## Navigation Switcher UI

When a user has overlapping privileges (e.g. HOD who teaches), display a role switcher or panel navigation links in the dashboard sidebar:

```blade
{{-- HOD Sidebar --}}
@if(auth()->user()->canActAsLecturer())
    <li class="nav-item">
        <a href="{{ route('lecturer.dashboard') }}">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Switch to Lecturer view</span>
        </a>
    </li>
@endif

{{-- Lecturer Sidebar --}}
@if(auth()->user()->isHod())
    <li class="nav-item">
        <a href="{{ route('hod.dashboard') }}">
            <i class="fas fa-user-tie"></i>
            <span>Return to HOD view</span>
        </a>
    </li>
@endif
```

---

## Implementation Checklist

### Phase 1: Database Setup
- [ ] Update migration to expand `users.role` enum to include `'lecturer', 'exam_officer'`.
- [ ] Create `user_capabilities` table migration.
- [ ] Run migrations on local/staging first.

### Phase 2: Core Files
- [ ] Add capability checking methods on the `User` model.
- [ ] Update `app/Enums/Role.php`.
- [ ] Create `CapabilityMiddleware` and register it in `app/Http/Kernel.php` or `bootstrap/app.php`.
- [ ] Add mapping rules to `RouteServiceProvider`.
- [ ] Define basic dashboard routes in `routes/lecturer.php` and `routes/exam_officer.php`.
