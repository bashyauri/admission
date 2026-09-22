# AI Coding Agent Instructions

> **This file contains mandatory project-wide instructions for AI coding agents working on this repository.**
>
> These instructions apply to the entire repository and are intended to be followed by any AI coding agent, including ChatGPT, Claude, Kimi, Gemini, GitHub Copilot, Codex, Devin, and other coding agents.

---

# 🚨 1. MANDATORY AGENT STARTUP

Before modifying any code, the AI coding agent MUST:

1. Read `AGENTS.md`.
2. Read `AGENT_CONTEXT.md`.
3. Identify the relevant module, feature, service, component, migration, test, or workflow.
4. Inspect the existing implementation before creating new code.
5. Read the relevant specification or phase document when one exists.
6. Search the repository for existing implementations that can be reused.
7. Understand the current architecture and workflow before making changes.

### Required project documents

```text
AGENTS.md
    ↓
Global project rules

AGENT_CONTEXT.md
    ↓
Project architecture, modules, roles and conventions

result_processing_agent_phases.md
    ↓
Result-processing implementation roadmap

Relevant source code
    ↓
Actual implementation
```

Do not begin implementation based only on the user's description when the repository already contains relevant implementation or documentation.

---

# 🎯 2. CORE DEVELOPMENT PRINCIPLES

All AI coding agents MUST follow these principles.

## 2.1 Inspect before implementing

Before creating a new:

* service
* component
* controller
* model
* trait
* helper
* migration
* Livewire component
* Blade view
* API endpoint
* validation rule
* policy
* test
* UI component

search the repository first.

Determine whether an existing implementation can be:

* reused
* extended
* refactored safely
* extracted
* shared

Do not create duplicate functionality unnecessarily.

---

## 2.2 Make the smallest safe change

Prefer:

```text
Small change
    ↓
Test
    ↓
Review
    ↓
Next change
```

over:

```text
Rewrite entire module
    ↓
Hope everything still works
```

Do not rewrite working code merely because another implementation appears cleaner.

---

## 2.3 Do not modify unrelated functionality

An agent working on Feature A must not casually modify:

* Feature B
* unrelated migrations
* unrelated views
* unrelated services
* unrelated database tables
* unrelated workflows
* unrelated UI

unless the change is genuinely required.

If an unrelated problem is discovered, report it separately rather than silently changing it.

---

# 🏗️ 3. PROJECT ARCHITECTURE

The existing application architecture must be respected.

Before introducing a new architectural pattern, determine how the existing module solves the same problem.

Do not introduce a parallel architecture without a clear reason and explicit authorization.

---

## 3.1 Service responsibilities

The following responsibilities MUST remain separated.

### `AcademicProgressionService`

Responsible for:

* academic standing
* progression
* promotion
* probation
* repeat
* spillover
* academic progression calculations

It must not become the general-purpose student institutional-status service.

---

### `StudentStatusService`

Responsible for institutional student status such as:

* voluntary withdrawal
* academic withdrawal
* medical withdrawal
* suspension
* expulsion
* reinstatement

Academic standing and institutional status are separate concepts.

---

### `ResultReportingService`

Responsible for:

* result reports
* reporting data
* report preparation
* report presentation

It must not duplicate academic progression or institutional-status business logic.

---

# 🎓 4. ACADEMIC RESULT WORKFLOW

The established undergraduate result workflow is:

```text
Lecturer
    ↓
Coordinator
    ↓
Exam Officer
    ↓
Released
```

This workflow MUST be preserved.

### Important

The **Coordinator** is the intermediate academic review/approval stage.

Do not replace the Coordinator with the HOD unless explicitly required by an approved specification or existing implementation.

Do not introduce a new approval stage simply because it appears convenient.

---

# 🎓 5. UG / PG ISOLATION

Undergraduate and postgraduate workflows must remain properly separated.

When implementing an UG feature:

* do not modify PG workflows unnecessarily
* do not change PG business rules
* do not reuse UG-specific assumptions in PG modules
* do not introduce shared logic that changes PG behavior without testing and explicit authorization

If a shared component/service is genuinely required, verify its effect on both UG and PG before modifying it.

---

# 🚨 6. GLOBAL UI/UX RULE

This application uses **Soft UI Dashboard** as its established visual design system.

This is a **SYSTEM-WIDE RULE**.

It applies to:

* existing modules
* new modules
* dashboards
* forms
* tables
* reports
* modals
* filters
* CRUD interfaces
* student portals
* admin interfaces
* lecturer interfaces
* coordinator interfaces
* exam officer interfaces
* mobile-responsive interfaces
* every future feature

---

## 6.1 Soft UI is mandatory

Any AI coding agent MUST:

1. Inspect existing UI before creating a new interface.
2. Reuse existing Soft UI patterns.
3. Reuse existing components whenever possible.
4. Match existing:

   * cards
   * buttons
   * tables
   * forms
   * modals
   * badges
   * alerts
   * navigation
   * typography
   * spacing
   * shadows
   * borders
   * responsive behavior
   * hover states
   * focus states
   * disabled states
   * loading states
   * validation states
   * empty states
5. Extend an existing pattern when functionality is missing.
6. Ensure new screens visually belong to the existing application.

A new screen must look like it was designed as part of the existing application.

---

## 6.2 No competing design system

DO NOT introduce:

* Bootstrap
* Material UI
* shadcn/ui
* arbitrary component libraries
* unrelated Tailwind component patterns
* another dashboard template
* another typography system
* another spacing system
* another visual language

unless explicitly authorized.

Do not introduce a second design system simply because an AI-generated component is easier to implement.

---

## 6.3 Component-first rule

Before creating a new UI component:

```text
Search existing components
        ↓
Reuse existing component
        ↓
Extend existing component
        ↓
Create new component only if necessary
```

Do not create multiple slightly different versions of the same:

* button
* card
* modal
* table
* badge
* form field
* alert

when one established pattern can be reused.

---

# 🎨 7. UI CONSISTENCY RULES

All new interfaces must maintain consistency in:

## Layout

Follow existing:

* page widths
* container sizes
* grid structures
* sidebar behavior
* navigation
* responsive breakpoints

## Typography

Follow the application's established:

* font family
* font sizes
* font weights
* headings
* labels
* helper text

## Spacing

Reuse established:

* padding
* margin
* gaps
* section spacing
* table spacing
* card spacing

## Visual hierarchy

Maintain consistent:

* primary actions
* secondary actions
* destructive actions
* informational elements
* warnings
* success states
* errors

## Responsive design

Every new interface must work properly on:

* desktop
* tablet
* mobile

Do not create desktop-only interfaces unless the feature genuinely requires it.

---

# 🔐 8. SECURITY RULES

Security must be enforced server-side.

Never rely solely on:

* hidden buttons
* disabled buttons
* frontend conditions
* Blade conditions
* JavaScript
* Livewire UI restrictions

for authorization.

Always verify permissions and authorization in the appropriate backend layer.

Agents must respect:

* Laravel authorization
* policies
* gates
* middleware
* roles
* permissions
* validation
* tenant boundaries
* existing access-control mechanisms

Never bypass existing authorization simply to make a feature work.

---

# 🗄️ 9. DATABASE SAFETY

Database operations must respect the environment.

This project may use completely disposable databases during local development and automated testing.

---

## 9.1 Development and testing databases

In local development and automated testing, it is acceptable to:

* refresh the database
* reset the database
* recreate the database
* run migrations from scratch
* run seeders
* run `php artisan migrate:fresh`
* run `php artisan migrate:fresh --seed`
* reset test databases
* rebuild test databases

when appropriate for the task.

For example:

```bash
php artisan migrate:fresh --seed
```

may be used during local development or testing when a clean database is required.

Agents should still verify that the command is being executed against the intended development or testing database.

A disposable development/test database may be destroyed and recreated as part of normal testing.

---

## 9.2 Production databases

Production databases require significantly greater caution.

Agents MUST NOT execute destructive production database operations without explicit authorization.

This includes:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:wipe
php artisan migrate:refresh
php artisan migrate:reset
```

and destructive SQL such as:

```sql
DELETE
TRUNCATE
DROP
```

Never use a destructive database reset as a solution to a production problem.

Production migrations should preserve existing data unless the task explicitly requires an approved data migration or removal.

---

## 9.3 Staging databases

Treat staging according to its actual purpose.

If staging contains disposable test data, database resets may be appropriate.

If staging contains valuable or shared data, treat it with production-like caution.

When uncertain, verify before performing destructive operations.

---

## 9.4 Environment verification

Before running a destructive database command, the agent should determine whether the application is using:

* local development database
* automated test database
* disposable staging database
* shared staging database
* production database

If the environment cannot be determined with reasonable confidence, the agent must stop and request clarification rather than guessing.

---

# 📚 10. HISTORICAL ACADEMIC DATA

Historical academic records are authoritative records.

Agents MUST preserve:

* previous results
* previous approvals
* previous academic sessions
* previous semesters
* previous course registrations
* transcripts
* payment records
* status history
* approval history
* Senate decisions
* audit information

Do not "clean up" historical records simply because they are no longer active.

Historical data should remain auditable.

This rule applies to application behavior and production data.

It does NOT prevent a disposable development/test database from being completely refreshed.

---

# 🧑‍🎓 11. STUDENT STATUS VS ACADEMIC STANDING

These are different concepts.

### Academic standing

Examples:

```text
PROMOTED
PROBATION
REPEAT
SPILLOVER
```

### Institutional status

Examples:

```text
ACTIVE
VOLUNTARY_WITHDRAWAL
ACADEMIC_WITHDRAWAL
MEDICAL_WITHDRAWAL
SUSPENDED
EXPELLED
REINSTATED
```

Do not collapse these concepts into one field or one piece of business logic unless explicitly required by the approved architecture.

---

# 🔄 12. WITHDRAWAL / REINSTATEMENT RULES

Where student withdrawal and reinstatement functionality is implemented:

```text
Academic / Institutional Trigger
        ↓
Eligibility Evaluation
        ↓
Withdrawal Recommendation
        ↓
Department / Faculty Workflow
        ↓
Pending Senate Decision
        ↓
Approved / Rejected
        ↓
Official Student Status
        ↓
Academic Activity Restrictions
```

Important distinctions:

```text
Recommendation ≠ Official Withdrawal

Academic Standing ≠ Institutional Status

Withdrawal ≠ Deletion of Student Records
```

Reinstatement must be auditable.

Never erase a previous withdrawal simply because a student has been reinstated.

---

# 📋 13. RESULT ENTRY RULES

The result workflow remains:

```text
Lecturer
    ↓
Coordinator
    ↓
Exam Officer
    ↓
Released
```

Agents must preserve:

* result ownership
* coordinator review
* exam officer review
* approval history
* release status
* historical results

A student status such as withdrawal must not silently delete historical results.

If a student is restricted from new academic activity:

* preserve previous results
* preserve transcript history
* enforce restrictions in the backend
* clearly communicate the status in the UI

---

# 🧪 14. TESTING REQUIREMENTS

Every meaningful code change should be validated.

Agents should run the most relevant tests available.

Examples:

```bash
php artisan test
```

or targeted tests such as:

```bash
php artisan test --filter=RelevantTest
```

For frontend changes, also check:

* Blade rendering
* Livewire behavior
* JavaScript errors
* responsive layout
* browser console errors
* relevant asset/build issues

For database changes, verify:

* migrations
* relationships
* queries
* existing records where applicable
* rollback where appropriate

Do not claim that tests passed if they were not actually run.

---

# 🧪 15. REGRESSION PROTECTION

When changing existing business logic:

1. Identify existing behavior.
2. Identify affected workflows.
3. Add or update tests where appropriate.
4. Make the smallest change.
5. Run relevant tests.
6. Verify that unrelated workflows remain functional.

Special attention must be given to:

* result processing
* academic progression
* student status
* payments
* registration
* transcripts
* reports
* UG/PG separation
* authorization

---

# 📖 16. DOCUMENTATION-FIRST RULE

If the repository contains a specification, roadmap, proposal, or implementation document for a feature, read it before implementing that feature.

For result-processing work, consult:

```text
result_processing_agent_phases.md
```

For general project architecture, consult:

```text
AGENT_CONTEXT.md
```

Do not silently replace documented requirements with assumptions.

If the documentation conflicts with the actual implementation:

1. Identify the conflict.
2. Inspect the relevant code.
3. Explain the conflict.
4. Do not silently choose one interpretation when the difference affects business behavior.

---

# 🔎 17. REPOSITORY SEARCH RULE

Before creating new functionality, search for existing implementations.

Search for:

* similar method names
* similar component names
* similar database queries
* similar UI
* similar validation
* similar services
* similar policies
* similar routes
* similar reports

The goal is to prevent duplicate implementations.

---

# 🧩 18. REUSE BEFORE CREATE

Prefer:

```text
Existing service
Existing component
Existing trait
Existing helper
Existing validation
Existing policy
Existing UI pattern
Existing query
```

before creating something new.

If existing functionality is close but incomplete, extend it where doing so preserves clean architecture.

Do not create duplicate services simply because the existing service name is inconvenient.

---

# 🛑 19. DO NOT GUESS BUSINESS RULES

AI agents must not invent institutional rules.

Do not invent:

* academic thresholds
* Senate rules
* withdrawal criteria
* graduation requirements
* progression requirements
* payment policies
* approval rules
* institutional policies

If a business rule is not documented or clearly implemented, flag it for confirmation.

Code should implement an approved rule, not an AI-generated assumption.

---

# 🏛️ 20. SENATE / OFFICIAL DECISION RULE

Where Senate approval is required:

```text
Recommendation
      ≠
Official Decision
```

Do not mark an institutional decision as final merely because a recommendation exists.

Official decisions must retain appropriate:

* reference
* date
* decision
* status
* responsible authority
* audit information

---

# 🧾 21. AUDITABILITY

Important institutional actions must remain traceable.

Where applicable, preserve:

* who performed the action
* when it happened
* previous state
* new state
* reason
* reference number
* academic session
* semester
* approval status

Do not implement irreversible state changes where an auditable record is required.

---

# 🚦 22. ERROR HANDLING

User-facing interfaces must not expose raw exceptions or stack traces.

Instead provide:

* meaningful validation messages
* useful error messages
* appropriate empty states
* appropriate loading states
* appropriate success states
* appropriate failure states

Logs may contain technical details where appropriate.

The UI should communicate what the user needs to know.

---

# ⚡ 23. PERFORMANCE

Agents should avoid unnecessary performance regressions.

Pay attention to:

* N+1 queries
* unnecessary database queries
* repeated queries inside loops
* large unpaginated datasets
* unnecessary Livewire requests
* unnecessary API calls
* expensive calculations in Blade
* loading entire datasets when pagination is appropriate

Use existing project conventions for:

* eager loading
* pagination
* caching
* query optimization

Do not prematurely optimize without evidence, but do not introduce obviously inefficient queries.

---

# 📦 24. DEPENDENCY RULE

Do not introduce a new package simply because it makes one task easier.

Before adding a dependency:

1. Check whether the existing Laravel/application stack already provides the capability.
2. Search the repository for an existing solution.
3. Consider maintenance and security implications.
4. Consider whether the package conflicts with the existing architecture.
5. Obtain explicit authorization when introducing a significant new dependency.

Do not replace existing libraries or frameworks casually.

---

# 🛠️ 25. CODE STYLE

Follow the existing project's coding conventions.

Prefer:

* existing naming conventions
* existing namespaces
* existing directory structure
* existing method patterns
* existing validation patterns
* existing Livewire conventions
* existing Laravel conventions

Do not reformat large unrelated files.

Do not introduce stylistic changes unrelated to the task.

---

# 🔀 26. GIT / CHANGE MANAGEMENT

Agents should keep changes focused.

A task should ideally produce:

```text
Task
 ↓
Relevant files
 ↓
Relevant tests
 ↓
Focused change
```

Avoid mixing:

```text
Feature implementation
+
Unrelated refactor
+
UI redesign
+
Dependency upgrade
+
Database cleanup
```

in one task.

If a refactor is necessary, clearly identify it.

---

# 📝 27. CHANGE REPORTING

After implementation, the agent should report:

### Changed

List the important files/components changed.

### Behavior

Explain what the change now does.

### Tests

List the tests/commands actually run.

### Database

Mention any migrations or schema changes.

### Risks

Mention any remaining risks or areas requiring manual verification.

Example:

```text
Changed:
- StudentStatusService
- StudentStatus model
- Exam Officer status view

Behavior:
- Added official withdrawal status handling.

Tests:
- php artisan test --filter=StudentStatusTest

Database:
- Added student_status_records migration.

Risks:
- Senate approval workflow requires manual UI verification.
```

Do not claim tests passed unless they were actually executed.

---

# 🤖 28. AI AGENT BEHAVIOR

AI agents should behave as software engineering assistants, not autonomous product decision-makers.

The agent may:

* inspect
* analyze
* implement
* test
* refactor when necessary
* document
* identify risks

The agent must not independently invent institutional policy or architectural changes.

When an important ambiguity affects business behavior, surface the ambiguity rather than silently guessing.

---

# 🔒 29. PRODUCTION SAFETY

Assume this application may contain live institutional data.

Before executing destructive commands, verify exactly what environment is being targeted.

### Safe development/test operations

The following may be used when the target database is confirmed to be disposable development or test data:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:wipe
php artisan migrate:refresh
php artisan migrate:reset
```

These operations are valid tools for testing migrations, seeders, database structure, and application behavior.

### Production

Do NOT use the above commands against production without explicit authorization.

Production changes should normally use controlled migrations and deliberate data migrations.

Never destroy production data merely to make a test pass.

---

# 📌 30. FINAL PRE-COMMIT CHECKLIST

Before considering a task complete, verify:

* [ ] I read `AGENTS.md`.
* [ ] I read `AGENT_CONTEXT.md`.
* [ ] I read the relevant specification/phase document.
* [ ] I inspected the existing implementation.
* [ ] I searched for reusable functionality.
* [ ] I followed the existing architecture.
* [ ] I preserved the result workflow.
* [ ] I preserved UG/PG separation.
* [ ] I followed the Soft UI design system.
* [ ] I did not introduce an unnecessary UI framework.
* [ ] I did not introduce unnecessary dependencies.
* [ ] I preserved historical academic records.
* [ ] I preserved authorization/security.
* [ ] I verified the database environment before destructive operations.
* [ ] I used database refresh/reset only where appropriate for development/testing.
* [ ] I protected production data from destructive operations.
* [ ] I added/updated relevant tests where appropriate.
* [ ] I actually ran the relevant tests.
* [ ] I checked for regressions.
* [ ] I did not modify unrelated functionality.
* [ ] I documented important changes and risks.

---

# 🏁 31. GOLDEN RULE

When in doubt:

```text
READ
 ↓
SEARCH
 ↓
UNDERSTAND
 ↓
REUSE
 ↓
IMPLEMENT
 ↓
TEST
 ↓
REPORT
```

**Do not guess.**

**Do not duplicate.**

**Do not redesign the application.**

**Do not break existing workflows.**

**Do not introduce a competing UI system.**

**Do not modify unrelated functionality.**

**Do not destroy production data.**

Build new functionality so that it feels like it has always been part of this application.
