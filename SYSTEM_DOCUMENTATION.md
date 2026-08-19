# Admission Management System - Complete Documentation

## Overview
This is a comprehensive Laravel-based admission management system built for handling student applications, admissions, course registrations, and fee payments for an educational institution. The system supports multiple programmes (Undergraduate, Postgraduate, HND, ND, NCE, etc.) and provides role-based access control for different stakeholders.

## Tech Stack
- **Framework**: Laravel 12.x (PHP 8.3+)
- **Frontend**: Livewire 3.4 + TailwindCSS
- **Database**: MySQL
- **Key Packages**:
  - Laravel Sanctum (API authentication)
  - Livewire (reactive UI components)
  - Maatwebsite Excel (Excel import/export)
  - Barryvdh Laravel DomPDF (PDF generation)
  - SimpleSoftwareIO Simple QRCode (QR code generation)
  - Laravel Pulse (application monitoring)
  - Jantinnerezo Livewire Alert (alert notifications)

## User Roles and Privileges

### 1. Admin
- **Full system access** with highest privileges
- Can create, edit, delete users
- Can manage all roles and permissions
- Can view all applicants across all programmes
- Can manage system settings
- Can upload and manage applicant data
- Can generate reports (FUBK reports, payment reports)
- Can manage course drops
- Can assign matric numbers
- **Routes**: `/admin/*`

### 2. HOD (Head of Department)
- Department-level access control
- Can view applicants in their department
- Can recommend applicants for admission
- Can shortlist applicants
- Can edit applicant details
- Can view department-specific reports
- **Routes**: `/hod/*`

### 3. Applicant
- Can register and create profile
- Can fill application forms (personal info, O-level results, school attended)
- Can upload certificates
- Can propose courses for admission
- Can pay application fees
- Can check admission status
- Can print application forms
- **Routes**: `/applications/*`, `/transactions/*`

### 4. Student
- Can access student dashboard
- Can pay school fees
- Can register for courses
- Can view course history
- Can print exam cards (after fee payment)
- Can print course forms
- **Routes**: `/student/*`

### 5. CIT (Central IT)
- Similar privileges to Admin
- Can manage system-wide operations
- Can add matric numbers to students
- Can manage first school fees
- **Routes**: `/cit/*`

### 6. Coordinator
- Programme-level coordination
- Assigned to specific departments
- Can coordinate academic activities
- **Routes**: `/coordinator/*`

### 7. ID Card Officer
- Manages student ID card processing
- Can view and process ID card requests
- **Routes**: `/idcard_officer/*`

### 8. Lecturer
- Academic instruction and result processing
- Can view assigned courses
- Can enter and edit scores (CA and Exam) for assigned courses
- Can submit results for approval
- **Routes**: `/lecturer/*` (via primary role or capabilities check)

### 9. Exam Officer
- Faculty/department-wide result review and auditing
- Can view all results in faculty
- Can approve results at faculty level
- Can generate statistical performance reports
- **Routes**: `/exam-officer/*` (via primary role or capabilities check)

## Database Schema

### Core Tables

#### users
- **Primary Key**: UUID
- **Fields**: 
  - `programme_id` (FK to programmes)
  - `jamb_no` (unique, nullable)
  - `picture` (profile image)
  - `surname`, `firstname`, `m_name`
  - `email` (unique)
  - `phone` (unique, nullable)
  - `gender`, `marital_status`, `birthday`
  - `state_id`, `lga_id` (FK to states, lgas)
  - `home_town`, `nationality`
  - `home_address`, `cor_address`
  - `kin_name`, `kin_address`, `kin_phone`
  - `role` (enum: applicant, student, graduate, hod, admin)
  - `password`, `vpassword`
  - `email_verified_at`
- **Relationships**: HasOne(AcademicDetail), HasMany(Transaction), HasOne(ProposedCourse)

#### programmes
- **Fields**: `id`, `name`, `abv`
- **Programme Types** (via enum):
  - HND (1), ND (2), NDS (3), NCE (4), PD (5), PG (6), Undergraduate (7)

#### departments
- **Fields**: `id`, `name`
- **Relationships**: BelongsToMany(Programme) via department_programmes

#### courses
- **Fields**: `id`, `name`, `department_id`, `programme_id`, `semesters` (default 4)
- **Relationships**: BelongsTo(Department), BelongsTo(Programme)

#### proposed_courses
- **Fields**: `id`, `user_id` (UUID FK), `department_id`, `course_id`, `status`, `acad_session`
- **Status**: `shortlisted`, `recommended`, or null (pending)
- **Relationships**: BelongsTo(User), BelongsTo(Department), BelongsTo(Course)

#### transactions
- **Fields**: `id`, `transaction_id`, `user_id` (UUID FK), `amount`, `date`, `status`, `use_status`, `resource`, `RRR` (unique), `code`, `acad_session`
- **Status**: `00` (approved), `01` (activated), or null (pending)
- **Purpose**: Application fees, school fees, acceptance fees, etc.

#### academic_details
- **Fields**: `id`, `user_id` (FK), `matric_no` (unique), `course_id`, `programme_id`, `department_id`, `student_level_id`, `acad_session`
- **Purpose**: Stores enrolled student information with matric number
- **Relationships**: BelongsTo(User), BelongsTo(Course), BelongsTo(Programme), BelongsTo(Department), BelongsTo(StudentLevel)

#### student_levels
- **Fields**: `id`, `level`
- **Levels**: 100, 200, 300, 400, SPILL OVER

### Supporting Tables

#### olevel_exams
- **Fields**: `id`, `user_id` (UUID FK), `exam_name`, `exam_number` (unique), `exam_year`
- **Purpose**: Stores O-level examination details (WAEC, NECO, etc.)

#### olevel_subject_grades
- **Fields**: `id`, `user_id` (UUID FK), `subject_name`, `grade`, `exam_name`
- **Purpose**: Stores individual subject grades

#### certificate_uploads
- **Fields**: `id`, `user_id` (UUID FK), `name`, `path`
- **Purpose**: Stores uploaded certificate file paths

#### post_utme_uploads
- **Fields**: `id` (UUID), `jamb_no` (unique), `name`, `course`, `jamb_score`, `acad_session`
- **Purpose**: Bulk upload of UTME applicant data

#### hod_users
- **Fields**: `id`, `user_id` (FK), `department_id` (FK)
- **Purpose**: Links HOD users to their departments

#### coordinators
- **Fields**: `id`, `user_id` (FK), `department_id` (FK)
- **Purpose**: Links coordinators to departments

#### department_courses
- **Fields**: `id`, `department_id`, `student_course_id`, `units`
- **Purpose**: Defines courses offered by a department with credit units

#### registered_courses
- **Fields**: `id`, `academic_detail_id`, `department_course_id` (unique), `student_level_id`, `units`, `academic_session`
- **Purpose**: Tracks courses registered by students per session

#### student_transactions
- **Fields**: `id`, `user_id` (UUID FK), `amount`, `date`, `status`, `resource`, `RRR`, `acad_session`
- **Purpose**: Student-specific fee transactions

#### school_fees_payments
- **Fields**: `id`, `user_id` (UUID FK), `amount`, `date`, `status`, `acad_session`
- **Purpose**: Tracks school fee payments

#### fee_structures
- **Fields**: `id`, `programme_id`, `department_id`, `level`, `amount`, `acad_session`
- **Purpose**: Defines fee structure per programme/department/level

#### id_card_processings
- **Fields**: `id`, `user_id` (UUID FK), `status`, `processed_at`
- **Purpose**: Tracks ID card processing status

#### course_drop_audits
- **Fields**: `id`, `user_id` (UUID FK), `course_id`, `reason`, `dropped_at`, `approved_by`
- **Purpose**: Audit trail for course drops

#### settings
- **Fields**: `id`, `key`, `value`
- **Purpose**: System-wide configuration settings

#### user_capabilities
- **Fields**: `id`, `user_id` (UUID FK), `capability`, `department_id` (FK), `is_active`, `granted_at`, `revoked_at`, `granted_by` (FK), `reason`
- **Purpose**: Supports overlapping/multi-role capabilities (e.g. HOD who also acts as a Lecturer) safely without breaking production single-role structures.

### Reference Tables

#### states, lgas
- Geographic reference data for user addresses

#### subjects
- Reference table for O-level subjects

#### grades
- Reference table for grade values

#### schools
- **Fields**: `id`, `user_id` (UUID FK), `name`, `from_year`, `to_year`
- **Purpose**: Schools attended by applicants

## Key Models and Relationships

### User Model
- **Key Methods**:
  - `isAdmin()`, `isHod()`, `isApplicant()`, `isStudent()`, `isCit()`, `isCoordinator()`
  - `isUndergraduate()`, `isPostgraduate()` (programme checks)
  - `hasPaid(string $payment)` - checks if user paid for specific resource
  - `isShortlisted()`, `isRecommended()` - admission status checks
  - `getIsDeAttribute` - checks if Direct Entry student (no JAMB score)
- **Relationships**:
  - `programme()` - BelongsTo
  - `proposedCourse()` - HasOne
  - `transactions()` - HasMany
  - `olevelExams()` - HasMany
  - `olevelsubjectGrades()` - HasMany
  - `certificateUploads()` - HasMany
  - `schools()` - HasMany
  - `academicDetail()` - HasOne
  - `hodDetails()` - HasOne
  - `coordinator()` - HasOne

### ProposedCourse Model
- **Relationships**: BelongsTo(User), BelongsTo(Course), BelongsTo(Department)
- **Purpose**: Stores applicant's course choices and admission status

### Transaction Model
- **Purpose**: Handles all payment transactions
- **Integration**: Remita payment gateway via RRR

## Route Structure

### Main Routes (web.php)
- **Public**: `/` (welcome page), `/contact` (contact form)
- **Auth**: `/sign-in`, `/sign-up`, `/degree-signup`, `/degree-signin`, `/forgot-password`, `/reset-password/{id}`
- **Applicant Routes** (middleware: auth, verified, role:applicant):
  - `/dashboard/analytics` - Main dashboard
  - `/applications/*` - Application forms and management
  - `/transactions/*` - Payment processing
  - Email verification routes

### Admin Routes (admin.php)
- `/dashboard` - Admin dashboard
- `/fubk` - FUBK reports (export, PDF generation)
- `/manage-course-drops` - Course drop management
- `/settings` - System settings
- `/all-applicants` - View all applicants
- `/not-recommended-applicants` - View applicants not recommended
- `/shortlisted-applicants` - View shortlisted applicants
- `/upload-applicants` - Bulk upload applicants
- `/imported-applicants` - View imported applicants
- `/edit-utme-applicant/{user}` - Edit UTME applicant
- `/all-utme-applicants` - UTME-specific views
- `/recommended-utme-applicants` - Recommended UTME applicants
- `/shortlisted-utme-applicants` - Shortlisted UTME applicants
- `/manage-degree-applicants` - Degree programme management
- `/manage-postgraduate-applicants` - Postgraduate management
- `/add-matric-number/{user}` - Assign matric numbers
- Payment status routes

### Student Routes (student.php)
- `/dashboard` - Student dashboard
- `/transactions/schoolfees/invoice` - School fees invoice
- `/transactions/ug-school-fees/{user}` - UG school fees
- `/exam-card` - Exam card (middleware: paid.student.school.fees)
- `/course-registration` - Course registration (middleware: paid.student.school.fees)
- `/course-history` - View course history
- `/print-course-form/{user}` - Print course form
- `/print-exam-card/{session}/{semester}` - Print exam card
- `/applications/print-form` - Print application form
- `/applications/print-acceptance` - Print acceptance letter
- Payment processing routes

### HOD Routes (hod.php)
- `/dashboard` - HOD dashboard
- `/all-applicants` - View department applicants
- `/not-recommended-applicants` - View not recommended
- `/shortlisted-applicants` - View shortlisted
- `/edit-applicant/{user}` - Edit applicant details
- `/hod-profile` - HOD profile

### Other Route Files
- `cit.php` - CIT-specific routes
- `coordinator.php` - Coordinator routes
- `idcard_officer.php` - ID card officer routes
- `lecturer.php` - Lecturer-specific routes (accessed by capability or role)
- `exam_officer.php` - Exam Officer routes (accessed by capability or role)
- `api.php` - API endpoints

## Livewire Components

### Authentication Components
- `Login` - User login
- `Register` - Applicant registration
- `DegreeRegister` - Degree programme registration
- `DegreeLogin` - Degree programme login
- `ForgotPassword` - Password recovery
- `ResetPassword` - Password reset
- `Logout` - User logout

### Application Components
- `Profile` - Personal profile management
- `SchoolAttended` - Schools attended form
- `Olevel` - O-level examination details
- `OlevelGrade` - O-level subject grades
- `CertificateUpload` - Certificate uploads
- `ProposedCourse` - Course selection for admission
- `Review` - Application review
- `Wizard` - Application wizard (multi-step form)
- `SearchUtme` - UTME result search

### Transaction Components
- `Payment` - Payment processing
- `AdmissionInvoice` - Admission fee invoice
- `AcceptanceInvoice` - Acceptance fee invoice
- `PostUtmeInvoice` - Post-UTME screening invoice
- `PostutmeScreeningInvoice` - Post-UTME screening invoice
- `SchoolFeesInvoice` - School fees invoice
- `UtmeSchoolFeesInvoice` - UG school fees invoice

### Admin Components
- `AdminIndex` - Admin dashboard
- `Settings` - System settings
- `FubkReport` - FUBK report generation
- `ManageCourseDrops` - Course drop management
- `ManageDegreeApplicants` - Degree applicant management
- `ManagePostgraduateApplicants` - Postgraduate management
- `AllApplicants` - View all applicants
- `NotRecommended` - View not recommended applicants
- `ShortlistedApplicants` - View shortlisted applicants
- `UploadApplicants` - Bulk upload applicants
- `ImportedApplicants` - View imported applicants
- `EditUtmeApplicants` - Edit UTME applicants
- `PaidSchoolFeesList` - Students who paid school fees
- `NotPaidSchoolFeesList` - Students who haven't paid school fees

### HOD Components
- `HodIndex` - HOD dashboard
- `AllApplicants` - View department applicants
- `NotRecommended` - View not recommended
- `ShortlistedApplicants` - View shortlisted
- `ApplicantEdit` - Edit applicant details
- `HodProfile` - HOD profile

### Student Components
- `StudentIndex` - Student dashboard
- `CourseRegistration` - Course registration
- `PrintCourseHistory` - Print course history
- `ExamCard` - Exam card generation

### Form Components (Forms/)
- `ProfileForm` - Profile form
- `SchoolAttendedForm` - School attended form
- `OlevelExamForm` - O-level exam form
- `OlevelGradeForm` - O-level grade form
- `ProposedCourseForm` - Proposed course form
- `AcademicDetailForm` - Academic detail form
- `UgAcademicDetailForm` - UG academic detail form
- `StudentCourseForm` - Student course form
- `CreateUserForm` - Create user form
- `CreateCoordinatorForm` - Create coordinator form
- `UpdateHodNameForm` - Update HOD name
- `UpdateHodPasswordForm` - Update HOD password
- `DepartmentLevelUnitsForm` - Department level units form
- `EditUnitForm` - Edit unit form

## Services

### PaymentService
- Handles payment processing via Remita
- Generates RRR (Remita Retrieval Reference)
- Checks payment status
- Manages transaction lifecycle

### TransactionService
- Transaction management
- Invoice generation
- Payment verification

### StudentTransactionService
- Student-specific payment handling
- School fees processing

### CourseRegistrationService
- Course registration logic
- Unit validation
- Department course mapping

### AcademicSessionService
- Academic session management
- Session-based operations

### UTMEApplicantService
- UTME applicant data management
- Bulk import functionality

### ApplicantCleanupService
- Applicant data cleanup
- Data maintenance operations

### Report Services (Report/)
- Report generation
- PDF export
- Excel export

## Policies (Authorization)

### UserPolicy
- `viewAny` - Admin only
- `create` - Admin only
- `update` - HOD only
- `delete` - Admin only (cannot delete self)
- `manageUsers` - Applicant or HOD
- `manageItems` - Admin or Creator
- `checkPayment` - Payment verification
- `generateAcceptanceInvoice` - Shortlisted applicants only

### RolePolicy
- `viewAny`, `create`, `update`, `delete` - Admin only
- `viewApplicants` - HOD only

### CategoryPolicy, ItemPolicy, TagPolicy
- `viewAny`, `create`, `update`, `delete` - Admin or Creator

### ProposedCoursePolicy
- Course proposal authorization

### TransactionPolicy
- Transaction authorization

## Application Flow

### Applicant Registration Flow
1. User registers via `/sign-up` or `/degree-signup`
2. Email verification required
3. Complete profile (`/applications/profile`)
4. Add schools attended (`/applications/school-attended`)
5. Add O-level results (`/applications/olevel`, `/applications/olevel-grade`)
6. Upload certificates (`/applications/upload-certificate`)
7. Propose course (`/applications/apply-course`)
8. Review application (`/applications/review`)
9. Print application form (`/applications/print-form`)

### Admission Process Flow
1. Admin uploads UTME results or applicants register
2. HOD reviews applicants in their department
3. HOD recommends qualified applicants
4. Admin shortlists recommended applicants
5. Shortlisted applicants can pay acceptance fee
6. Admin assigns matric numbers
7. Applicants become students

### Student Flow
1. Pay school fees (`/transactions/schoolfees/invoice`)
2. Register courses (`/course-registration`)
3. Print exam card (`/exam-card`)
4. Print course form (`/print-course-form`)

### Payment Flow
1. Generate invoice for specific payment type
2. Get RRR from Remita
3. User pays via Remita
4. System verifies payment status
5. Update transaction status to '00' (approved)
6. Unlock relevant features (course registration, exam card, etc.)

## Key Features

### Multi-Programme Support
- Undergraduate (7)
- Postgraduate (6)
- HND (1), ND (2), NDS (3)
- NCE (4), PD (5)

### Admission Management
- UTME result integration
- Post-UTME screening
- Application shortlisting
- Recommendation system
- Matric number generation

### Financial Management
- Remita payment integration
- Multiple payment types (application, acceptance, school fees)
- Payment status tracking
- Invoice generation
- Fee structure management

### Academic Management
- Course registration
- Department course management
- Credit unit tracking
- Course drop functionality with audit trail
- Academic session management

### Reporting
- FUBK reports
- Payment reports
- Applicant reports
- PDF generation
- Excel export

### Security
- Role-based access control
- Email verification
- UUID-based user identification
- Policy-based authorization
- Sanctum API authentication

## File Structure

```
app/
├── Console/           - Artisan commands
├── Contracts/         - Interfaces
├── Enums/             - PHP enums (Role, ApplicationStatus, ProgrammesEnum, etc.)
├── Exceptions/        - Exception handlers
├── Http/
│   ├── Controllers/   - HTTP controllers
│   ├── Livewire/      - Livewire components
│   ├── Middleware/    - Custom middleware
│   └── Requests/      - Form request validation
├── Imports/           - Import handlers
├── Jobs/              - Queue jobs
├── Livewire/          - Additional Livewire components
├── Models/            - Eloquent models
├── Notifications/     - Notification classes
├── Policies/          - Authorization policies
├── Providers/         - Service providers
├── Repositories/      - Data repositories
├── Rules/             - Custom validation rules
├── Services/          - Business logic services
└── View/              - View components

database/
├── factories/         - Model factories
├── migrations/        - Database migrations
└── seeders/           - Database seeders

resources/
├── views/             - Blade templates
│   └── livewire/      - Livewire component views
└── lang/              - Language files

routes/
├── web.php            - Main web routes
├── admin.php          - Admin routes
├── student.php        - Student routes
├── hod.php            - HOD routes
├── cit.php            - CIT routes
├── coordinator.php    - Coordinator routes
├── idcard_officer.php - ID Card Officer routes
└── api.php            - API routes
```

## Middleware

### Custom Middleware
- `role:applicant` - Applicant role restriction
- `role:admin` - Admin role restriction
- `paid.student.school.fees` - School fee payment verification
- `verified` - Email verification requirement
- `signed` - Signed URL verification

## Enums

### Role Enum
- `HOD` - Head of Department
- `APPLICANT` - Applicant
- `ADMIN` - Administrator
- `STUDENT` - Student
- `CIT` - Central IT
- `COORDINATOR` - Coordinator
- `IDCARD_OFFICER` - ID Card Officer

### ApplicationStatus Enum
- `SHORTLISTED` - Shortlisted for admission
- `RECOMMENDED` - Recommended by HOD
- `PENDING` - No status (null)

### ProgrammesEnum
- `HND` (1), `ND` (2), `NDS` (3), `NCE` (4), `PD` (5), `PG` (6), `Undergraduate` (7)

### StudentLevel Enum
- `YEAR_ONE` (1) - 100 level
- `YEAR_TWO` (2) - 200 level
- `YEAR_THREE` (3) - 300 level
- `YEAR_FOUR` (4) - 400 level
- `SPILL_OVER` (5) - Spill over

### TransactionStatus Enum
- `APPROVED` ('00') - Payment approved
- `ACTIVATED` ('01') - Payment activated
- `PENDING` (null) - Payment pending

## Configuration

### Environment Variables (.env)
- Database configuration
- Mail settings (Mailersend driver)
- Remita payment gateway credentials
- Application URL
- Filesystem settings

### Key Settings
- Academic sessions
- Fee structures
- Payment gateways
- Email templates

## Development Notes

### Impersonation Feature
- Admin/CIT can impersonate other roles for testing
- Uses `impersonateRole()` and `impersonateProgramme()` methods on User model
- Cleared via `clearImpersonation()`

### Direct Entry Detection
- Direct Entry students identified by missing JAMB score (0 or null)
- Checked via `getIsDeAttribute` on User model

### Payment Integration
- Remita payment gateway
- RRR generation and verification
- Status polling for payment confirmation

### PDF Generation
- Uses DomPDF for PDF reports
- Queue-based PDF generation for large reports
- Download tokens for secure PDF access

## Important Notes for AI Coding Agents

1. **Role-Based Access**: Always check user roles before implementing new features
2. **Policy Authorization**: Use Laravel policies for authorization logic
3. **UUID Usage**: User IDs are UUIDs, not auto-increment integers
4. **Programme Types**: Use ProgrammesEnum for programme type consistency
5. **Payment Flow**: Always generate invoice before payment, verify payment after
6. **Academic Sessions**: Most operations are session-based
7. **Livewire Components**: Prefer Livewire for interactive UI components
8. **Service Layer**: Business logic should be in Services, not Controllers
9. **Middleware**: Use middleware for route protection
10. **Database Relations**: Use proper foreign key relationships and constraints

## Common Patterns

### Creating New Features
1. Create migration for database changes
2. Create/update Model with relationships
3. Create Policy for authorization
4. Create Service for business logic
5. Create Livewire component for UI
6. Add routes to appropriate route file
7. Add middleware for protection

### Payment Integration
```php
// Generate invoice
$transaction = TransactionService::generateInvoice($user, $paymentType, $amount);

// Get RRR
$rrr = PaymentService::getRRR($transaction);

// Verify payment
$status = PaymentService::verifyPayment($rrr);
```

### Role Checks
```php
if ($user->isAdmin()) {
    // Admin logic
}
if ($user->isHod()) {
    // HOD logic
}
if ($user->isStudent()) {
    // Student logic
}
```

### Policy Authorization
```php
// In controller
$this->authorize('viewAny', User::class);
$this->authorize('update', $user);

// In views
@can('update', $user)
    // Edit button
@endcan
```

## Testing

### Key Test Scenarios
1. User registration and email verification
2. Application form completion
3. Payment processing and verification
4. Course registration with fee payment check
5. Role-based access control
6. Admission workflow (recommend → shortlist → matric)
7. Report generation

## Deployment Considerations

1. **Queue Workers**: Required for PDF generation and email jobs
2. **Storage Link**: Run `php artisan storage:link` for file access
3. **Environment**: Configure proper .env for production
4. **Database**: Run migrations and seeders
5. **Payment Gateway**: Configure Remita credentials
6. **Email**: Configure Mailersend or other email provider

## Support and Maintenance

### Regular Tasks
- Monitor payment transactions
- Manage academic sessions
- Update fee structures
- Process ID card requests
- Generate periodic reports
- Backup database

### Troubleshooting
- Check Laravel logs in `storage/logs`
- Monitor queue jobs
- Verify payment gateway status
- Check email delivery logs
- Review failed transactions

---

**Last Updated**: August 2026  
**Laravel Version**: 12.x  
**PHP Version**: 8.3+  
**System Version**: 1.0.0
