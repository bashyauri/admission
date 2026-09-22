# AI Coding Agent Instructions

> **MANDATORY PROJECT-WIDE INSTRUCTIONS**
>
> This file defines the rules that every AI coding agent must follow when working on this repository.
>
> These instructions apply to ChatGPT, Claude, Kimi, Gemini, GitHub Copilot, Codex, Devin, and any other AI coding agent.

---

# 1. 🚨 MANDATORY AGENT STARTUP

Before modifying code, the agent **MUST** complete the following sequence:

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

## 1.1 Required startup actions

Before implementation:

1. Read `AGENTS.md`.
2. Read `AGENT_CONTEXT.md`.
3. Identify the affected module, feature, service, component, migration, workflow, or test.
4. Inspect the existing implementation.
5. Read the relevant specification, roadmap, proposal, or phase document.
6. Search the repository for existing implementations.
7. Identify reusable services, components, queries, policies, validations, and UI patterns.
8. Understand the current workflow before changing it.
9. Determine whether the requested change affects UG, PG, or shared functionality.
10. Determine the appropriate Alpine.js / Livewire / Laravel responsibility.
11. Make the smallest safe change required.
12. Run relevant tests and validation.
13. Report exactly what changed and what was tested.

## 1.2 Required project documents

```text
AGENTS.md
    ↓
Global AI coding rules

AGENT_CONTEXT.md
    ↓
Project architecture, modules, roles, conventions

result_processing_agent_phases.md
    ↓
Result-processing roadmap and implementation sequence

Relevant specification / proposal
    ↓
Feature-specific requirements

Existing source code
    ↓
Actual implementation
```

Do not implement a feature based only on the user's description when the repository already contains relevant documentation or implementation.

---

# 2. 🎯 CORE DEVELOPMENT PRINCIPLES

## 2.1 Inspect before implementing

Before creating or modifying any:

* service
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

Determine whether existing functionality can be:

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
Understand existing behavior
        ↓
Small focused change
        ↓
Test
        ↓
Review
        ↓
Next change
```

Avoid:

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
* unrelated dependencies

unless the change is genuinely required.

If an unrelated problem is discovered, report it separately.

---

# 3. 🏗️ PROJECT ARCHITECTURE

Respect the existing application architecture.

Before introducing a new architectural pattern, determine how the existing module solves the same problem.

Do not introduce a parallel architecture without a clear technical reason and appropriate authorization.

---

## 3.1 Service responsibility boundaries

The following responsibilities must remain separated.

### `AcademicProgressionService`

Responsible for:

* academic standing
* progression
* promotion
* probation
* repeat
* spillover
* academic progression calculations

It must **not** become a general-purpose institutional student-status service.

---

### `StudentStatusService`

Responsible for institutional student status such as:

* active status
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
* report data
* report preparation
* report presentation
* reporting-specific aggregation

It must not duplicate:

* academic progression logic
* student-status business rules
* result approval logic

---

## 3.2 Business logic belongs in the appropriate backend layer

Use the appropriate architectural layer:

```text
Blade
    ↓
Presentation

Alpine.js
    ↓
Client-side UI state

Livewire
    ↓
Server interaction / component state

Laravel Service / Domain Logic
    ↓
Business rules

Models / Repositories / Queries
    ↓
Data access

Database
    ↓
Persistent data
```

Do not put substantial business rules inside:

* Blade
* Alpine.js
* JavaScript
* UI components

unless the rule is genuinely presentation-only.

---

# 4. 🎓 ACADEMIC RESULT WORKFLOW

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

This workflow **MUST remain intact**.

## Important

The **Coordinator** is the intermediate academic review/approval stage.

Do not replace the Coordinator with the HOD unless explicitly required by an approved specification or existing implementation.

Do not introduce an additional approval stage simply because it appears convenient.

Do not bypass the Coordinator.

Do not allow Exam Officer processing to silently approve unreviewed lecturer results.

---

# 5. 🎓 UG / PG ISOLATION

Undergraduate and postgraduate workflows must remain properly separated.

When implementing an UG feature:

* do not modify PG workflows unnecessarily
* do not change PG business rules
* do not apply UG assumptions to PG
* do not change PG grading rules
* do not change PG progression rules
* do not change PG approval workflows
* do not change PG pass requirements

If shared logic is genuinely required:

1. Identify the shared behavior.
2. Inspect both UG and PG implementations.
3. Verify the effect on both.
4. Add appropriate tests.
5. Make the smallest safe shared change.

A feature intended for UG must not accidentally become a PG feature.

---

# 6. 🎨 GLOBAL UI/UX DESIGN SYSTEM

The application uses **Soft UI Dashboard** as its established visual design system.

This is a **system-wide rule**.

It applies to:

* dashboards
* forms
* tables
* reports
* filters
* modals
* CRUD interfaces
* student portals
* admin interfaces
* lecturer interfaces
* coordinator interfaces
* exam officer interfaces
* mobile-responsive interfaces
* all future features

New interfaces must look like they were designed as part of the existing application.

---

## 6.1 Soft UI is mandatory

Before creating UI, inspect existing screens and components.

Reuse existing patterns whenever possible.

Match the existing:

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

If an existing component is close but incomplete, extend it rather than creating another competing version.

---

## 6.2 No competing design system

Do **NOT** introduce:

* Bootstrap
* Material UI
* shadcn/ui
* arbitrary UI libraries
* another dashboard template
* another typography system
* another spacing system
* another visual language

unless explicitly authorized.

Do not introduce a second design system because an AI-generated component is easier to implement.

---

## 6.3 Component-first rule

Before creating a UI component:

```text
Search existing component
        ↓
Reuse existing component
        ↓
Extend existing component
        ↓
Create new component only if necessary
```

Avoid multiple versions of the same:

* button
* card
* modal
* table
* badge
* form field
* alert

when an established pattern can be reused.

---

# 7. 🎨 UI CONSISTENCY RULES

All interfaces must maintain consistency in the following areas.

## Layout

Follow existing:

* page widths
* containers
* grid structures
* sidebar behavior
* navigation
* responsive breakpoints

## Typography

Follow the established:

* font family
* font sizes
* font weights
* headings
* labels
* helper text

## Spacing

Reuse established:

* padding
* margins
* gaps
* section spacing
* table spacing
* card spacing

## Visual hierarchy

Maintain consistent treatment of:

* primary actions
* secondary actions
* destructive actions
* informational elements
* warnings
* success states
* errors

## Responsive behavior

Every new interface should work properly on:

* desktop
* tablet
* mobile

Do not create desktop-only interfaces unless the feature genuinely requires it.

---

# 8. ⚡ TALL STACK ARCHITECTURE & PERFORMANCE

This application uses the TALL stack:

* Tailwind CSS
* Alpine.js
* Laravel
* Livewire

Agents must choose the correct technology based on **where state and computation actually belong**.

The goal is:

* fast browser interactions
* minimal unnecessary server requests
* efficient database usage
* responsive Livewire components
* maintainable code
* secure server-side business logic

---

# 9. 🟢 ALPINE.JS RULES

Use Alpine.js when the interaction can be completed entirely in the browser without requiring server-side state or database access.

Typical examples:

* opening/closing modals
* dropdowns
* menus
* tabs
* accordions
* show/hide sections
* toggles
* password visibility
* temporary notifications
* visual state
* simple transitions
* expanding/collapsing content
* client-side presentation state
* switching between already-loaded data
* simple filtering of already-loaded data

Example:

```blade
<div x-data="{ open: false }">
    <button type="button" x-on:click="open = true">
        Open
    </button>

    <div x-show="open">
        Modal content
    </div>
</div>
```

Do not create a Livewire request for an interaction that can safely be handled entirely by Alpine.js.

---

# 10. 🔵 LIVEWIRE RULES

Use Livewire when the interaction requires server-side processing.

Examples:

* database queries
* database-backed search
* server-side filtering
* pagination
* CRUD operations
* server-side validation
* authorization
* business rules
* server-side calculations
* persistent state
* multi-step server workflows
* real-time server-backed information

Example:

```text
Search input
    ↓
Livewire
    ↓
Laravel query
    ↓
Database
    ↓
Paginated result
```

Livewire is appropriate when the server needs to perform the operation.

---

# 11. 🚫 DO NOT USE LIVEWIRE UNNECESSARILY

Do not use Livewire simply because a page already contains a Livewire component.

Avoid:

```blade
wire:click="openModal"
```

when opening the modal does not require server-side processing.

Prefer:

```blade
x-on:click="open = true"
```

Similarly, do not create Livewire actions merely for:

* opening dropdowns
* closing modals
* switching tabs
* expanding sections
* showing/hiding elements
* toggling visual state
* animations

when no server-side operation is required.

---

# 12. ⚡ LIVEWIRE REQUEST DISCIPLINE

Every Livewire request has a cost.

Before adding a Livewire action or event, ask:

1. Does this require the server?
2. Does this require database access?
3. Does this require authorization?
4. Does this modify persistent data?
5. Does this require server-side business logic?
6. Does this require server-side state?

If all answers are **NO**, prefer Alpine.js.

Do not create unnecessary:

* `wire:click`
* `wire:change`
* `wire:input`
* `wire:poll`
* event dispatches
* `$refresh`
* broad component refreshes

---

# 13. 🔄 LIVEWIRE RE-RENDER RULES

Livewire components should not unnecessarily re-render large sections of a page.

Agents must consider component boundaries and rendering cost.

## Avoid unnecessary full-component refreshes

Do not use:

```php
$this->dispatch('$refresh');
```

or equivalent broad refresh patterns as a default solution.

Before refreshing a component, determine:

* what state actually changed
* which UI actually needs updating
* whether Alpine can handle the change
* whether a smaller Livewire component can be refreshed
* whether the affected data can be updated directly

Prefer targeted updates over broad re-rendering.

---

## 13.1 Component boundaries

Do not turn one Livewire component into a giant controller for an entire page.

Prefer logical boundaries such as:

```text
Page
 ├── Filters
 ├── Summary cards
 ├── Results table
 ├── Modal
 └── Activity/history
```

Where appropriate, independent sections can be separate Livewire components.

Do not split components unnecessarily either.

The objective is **logical boundaries**, not maximum component count.

---

## 13.2 Isolate expensive sections

If a page contains an expensive section that does not need to update whenever another section changes:

* isolate it
* lazy-load it where appropriate
* defer loading where appropriate
* avoid refreshing it unnecessarily

Do not make an expensive database query rerun merely because a visual UI property changed.

---

## 13.3 Use Alpine for local state

If state exists only to control presentation:

```text
Modal open/closed
Dropdown open/closed
Active tab
Expanded row
Temporary notification
Password visibility
```

use Alpine.js.

Do not store presentation-only state in Livewire unless server-side state is genuinely required.

---

# 14. 🔄 LIVEWIRE + ALPINE TOGETHER

Alpine.js and Livewire are complementary.

A well-designed interface may look like:

```text
                         PAGE
                          │
              ┌───────────┴───────────┐
              │                       │
          Alpine.js               Livewire
              │                       │
       UI interaction          Server interaction
              │                       │
              │                 Laravel Services
              │                       │
              │                    Database
              │
       Instant response
```

Example:

```text
Open modal
    ↓
Alpine.js

Load student data
    ↓
Livewire

Validate/update student
    ↓
Laravel + Livewire

Close modal
    ↓
Alpine.js
```

Do not send a server request for every UI interaction.

---

# 15. 📡 POLLING RULES

Do not add Livewire polling unless the feature genuinely requires periodic server updates.

Avoid:

```blade
wire:poll
```

unless there is a clear business requirement.

If polling is required:

* use the least frequent interval that satisfies the requirement
* avoid polling expensive queries
* avoid polling large components
* return only necessary data
* consider event-driven alternatives
* verify the actual performance impact

---

# 16. 📊 LIVEWIRE DATA-LOADING RULES

For Livewire components:

* query only required columns where appropriate
* eager-load required relationships
* prevent N+1 queries
* paginate large datasets
* avoid huge component state
* avoid repeated queries
* avoid expensive calculations in Blade
* use loading states
* debounce search where appropriate
* use indexed queries
* avoid loading entire tables into memory

Example:

```blade
wire:model.live.debounce.300ms="search"
```

Use an appropriate debounce interval for the feature.

Do not blindly use the same debounce interval everywhere.

---

# 17. 🔎 SEARCH & FILTERING STRATEGY

For small datasets already loaded into the browser:

```text
Existing data
    ↓
Alpine.js
    ↓
Client-side filtering
```

For large or database-backed datasets:

```text
Search input
    ↓
Livewire
    ↓
Laravel query
    ↓
Database
    ↓
Pagination
```

Do not load thousands of database records into the browser merely to avoid Livewire.

Conversely, do not make repeated Livewire requests when a small dataset already loaded in the browser can be filtered instantly with Alpine.js.

---

# 18. 📝 FORM RULES

Use Alpine.js for presentation-level form behavior:

* show/hide fields
* conditional presentation
* password visibility
* client-side display state
* multi-step presentation

Use Livewire/backend processing for:

* database writes
* server-side validation
* authorization
* business rules
* persistent state
* server-side calculations

Client-side validation may improve UX but **must never replace server-side validation**.

---

# 19. 🪟 MODAL RULES

Modal visibility should normally be handled by Alpine.js.

Example:

```blade
<div x-data="{ open: false }">

    <button
        type="button"
        x-on:click="open = true"
    >
        Edit Student
    </button>

    <div x-show="open">
        ...
    </div>

</div>
```

If the modal requires server-side data or operations:

```text
Alpine
    ↓
Open modal

Livewire
    ↓
Load/process server data

Alpine
    ↓
Close modal
```

Do not create a server request merely to open or close a modal.

---

# 20. 📋 TABLES & LARGE DATASETS

For large datasets:

```text
Database
    ↓
Laravel query
    ↓
Livewire
    ↓
Pagination
```

Use:

* pagination
* appropriate filtering
* eager loading
* selective columns
* indexed queries
* efficient sorting
* server-side searching

Do not load the entire dataset into the browser.

For small static datasets already available on the page, Alpine.js may handle simple presentation filtering.

---

# 21. ⚡ PERFORMANCE RULES

Agents must avoid introducing obvious performance regressions.

Pay attention to:

* N+1 queries
* repeated queries
* queries inside loops
* large unpaginated datasets
* unnecessary Livewire requests
* unnecessary API calls
* expensive Blade calculations
* unnecessary polling
* unnecessary re-rendering
* large Livewire payloads
* excessive component state
* missing indexes where appropriate
* unnecessary eager loading
* excessive eager loading
* browser-side processing of huge datasets

Use existing project conventions for:

* eager loading
* pagination
* caching
* query optimization
* indexes

Do not prematurely optimize without evidence.

However, do not introduce obviously inefficient code.

---

# 22. 🔐 SECURITY RULES

Security must be enforced server-side.

Never rely solely on:

* hidden buttons
* disabled buttons
* frontend conditions
* Blade conditions
* JavaScript
* Alpine.js
* Livewire UI restrictions

for authorization.

Always enforce authorization in the appropriate backend layer.

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

Never bypass authorization simply to make a feature work.

---

# 23. 🗄️ DATABASE SAFETY

Database operations must respect the environment.

---

## 23.1 Development and testing databases

For confirmed disposable development/test databases, it may be appropriate to use:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:wipe
php artisan migrate:refresh
php artisan migrate:reset
```

These operations are acceptable when:

* the database is confirmed disposable
* the command is being executed against the intended environment
* the operation is appropriate for the task

---

## 23.2 Production databases

Production databases require significantly greater caution.

Agents **MUST NOT** execute destructive production database operations without explicit authorization.

Never run against production:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:wipe
php artisan migrate:refresh
php artisan migrate:reset
```

and never execute destructive SQL such as:

```sql
DELETE
TRUNCATE
DROP
```

as a shortcut.

Production changes should normally use:

* controlled migrations
* deliberate data migrations
* backups where appropriate
* reversible changes where possible
* explicit verification

Never destroy production data merely to make a test pass.

---

## 23.3 Staging databases

Treat staging according to its actual purpose.

If staging contains disposable test data, resets may be appropriate.

If staging contains shared or valuable data, treat it with production-like caution.

When uncertain, verify before performing destructive operations.

---

## 23.4 Environment verification

Before destructive database operations, determine whether the application is connected to:

* local development
* automated test database
* disposable staging
* shared staging
* production

If the environment cannot be determined with reasonable confidence:

> **STOP and request clarification.**

Never guess.

---

# 24. 📚 HISTORICAL ACADEMIC DATA

Historical academic records are authoritative records.

Agents must preserve:

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

Historical data must remain auditable.

This rule applies to production data and application behavior.

It does not prevent refreshing a disposable development/test database.

---

# 25. 🧑‍🎓 STUDENT STATUS VS ACADEMIC STANDING

These are different concepts.

## Academic standing

Examples:

```text
PROMOTED
PROBATION
REPEAT
SPILLOVER
```

## Institutional status

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

Do not collapse these concepts into one field or one business service unless explicitly required by the approved architecture.

---

# 26. 🔄 WITHDRAWAL & REINSTATEMENT RULES

Where withdrawal/reinstatement functionality exists:

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

Never erase the previous withdrawal record because a student has been reinstated.

Historical records must remain available.

---

# 27. 📋 RESULT ENTRY RULES

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
* lecturer entry
* Coordinator review
* Exam Officer review
* approval history
* release status
* historical results

A student status such as withdrawal must not silently delete historical results.

If a student is restricted from new academic activity:

* preserve previous results
* preserve transcript history
* enforce restrictions in the backend
* clearly communicate status in the UI

Do not silently remove the student from historical reports.

---

# 28. 🛑 DO NOT GUESS BUSINESS RULES

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
* grading rules
* pass marks
* eligibility requirements

If a business rule is not documented or clearly implemented:

1. Identify the ambiguity.
2. Inspect the repository.
3. Check the relevant specification.
4. Explain the conflict or missing requirement.
5. Request confirmation if necessary.

Code must implement an approved rule, not an AI-generated assumption.

---

# 29. 🏛️ SENATE / OFFICIAL DECISION RULE

Where Senate approval is required:

```text
Recommendation
      ≠
Official Decision
```

Do not mark an institutional decision as final merely because a recommendation exists.

Official decisions should retain, where applicable:

* reference
* date
* decision
* status
* responsible authority
* audit information

A recommendation must never silently become an official institutional decision.

---

# 30. 🧾 AUDITABILITY

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
* responsible authority

Do not implement irreversible state changes where an auditable record is required.

Prefer append-only history for institutional decisions where appropriate.

---

# 31. 📖 DOCUMENTATION-FIRST RULE

If the repository contains a:

* specification
* roadmap
* proposal
* implementation document
* phase document

read it before implementing the feature.

For result-processing work, consult:

```text
result_processing_agent_phases.md
```

For general project architecture, consult:

```text
AGENT_CONTEXT.md
```

Do not silently replace documented requirements with assumptions.

If documentation conflicts with actual implementation:

1. Identify the conflict.
2. Inspect the relevant code.
3. Explain the conflict.
4. Do not silently choose one interpretation when the difference affects business behavior.

---

# 32. 🔎 REPOSITORY SEARCH RULE

Before creating functionality, search for:

* similar method names
* similar component names
* similar database queries
* similar UI
* similar validation
* similar services
* similar policies
* similar routes
* similar reports
* similar migrations
* similar tests

The goal is to prevent duplicate implementations.

---

# 33. 🧩 REUSE BEFORE CREATE

Prefer:

```text
Existing service
    ↓
Existing component
    ↓
Existing trait
    ↓
Existing helper
    ↓
Existing validation
    ↓
Existing policy
    ↓
Existing UI pattern
    ↓
Existing query
    ↓
Create new implementation only if necessary
```

If existing functionality is close but incomplete, extend it when doing so preserves clean architecture.

Do not create duplicate services simply because an existing service has an inconvenient name.

---

# 34. 🧪 TESTING REQUIREMENTS

Every meaningful code change should be validated.

Use the most relevant tests available.

Examples:

```bash
php artisan test
```

or targeted tests:

```bash
php artisan test --filter=RelevantTest
```

For frontend changes, verify:

* Blade rendering
* Livewire behavior
* Alpine.js behavior
* JavaScript errors
* browser console errors
* responsive layout
* loading states
* validation states
* empty states
* asset/build issues

For database changes, verify:

* migrations
* relationships
* queries
* indexes
* existing records where applicable
* rollback behavior where appropriate

Never claim that tests passed unless they were actually executed.

---

# 35. 🧪 REGRESSION PROTECTION

When changing existing business logic:

1. Identify existing behavior.
2. Identify affected workflows.
3. Search for existing tests.
4. Add/update tests where appropriate.
5. Make the smallest change.
6. Run relevant tests.
7. Verify unrelated workflows remain functional.

Pay special attention to:

* result processing
* academic progression
* student status
* payments
* registration
* transcripts
* reports
* UG/PG separation
* authorization
* historical data

---

# 36. 📦 DEPENDENCY RULE

Do not introduce a new package simply because it makes one task easier.

Before adding a dependency:

1. Check whether Laravel or the existing stack already provides the capability.
2. Search the repository for an existing solution.
3. Consider maintenance implications.
4. Consider security implications.
5. Consider architectural compatibility.
6. Obtain authorization when a significant new dependency is required.

Do not replace existing libraries or frameworks casually.

---

# 37. 🛠️ CODE STYLE

Follow existing project conventions.

Prefer:

* existing naming conventions
* existing namespaces
* existing directory structure
* existing method patterns
* existing validation patterns
* existing Livewire conventions
* existing Laravel conventions
* existing query patterns
* existing component patterns

Do not reformat large unrelated files.

Do not introduce stylistic changes unrelated to the task.

---

# 38. 🔀 GIT & CHANGE MANAGEMENT

Keep changes focused.

Prefer:

```text
Task
 ↓
Relevant files
 ↓
Relevant tests
 ↓
Focused change
 ↓
Verification
```

Avoid combining:

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

If a refactor is genuinely necessary, explicitly report it.

---

# 39. 🚦 ERROR HANDLING

User-facing interfaces must not expose:

* raw exceptions
* stack traces
* internal database errors
* sensitive implementation details

Instead provide:

* meaningful validation messages
* useful error messages
* loading states
* empty states
* success states
* failure states
* appropriate disabled states

Technical details may be logged appropriately.

The UI should communicate what the user needs to know.

---

# 40. 🤖 AI AGENT BEHAVIOR

AI agents are software engineering assistants, not autonomous product decision-makers.

The agent may:

* inspect
* analyze
* search
* implement
* test
* refactor when necessary
* document
* identify risks
* suggest improvements

The agent must not independently invent:

* institutional policies
* academic rules
* Senate decisions
* architectural requirements
* security exceptions
* business rules

When an important ambiguity affects behavior, surface the ambiguity instead of silently guessing.

---

# 41. 📝 CHANGE REPORTING

After implementation, report the work using this structure:

## Changed

List the important files/components changed.

## Behavior

Explain what the change now does.

## Tests

List only the tests/commands actually executed.

## Database

Mention:

* migrations
* schema changes
* data migrations

if applicable.

## Performance

Mention relevant:

* query changes
* N+1 fixes
* Livewire request changes
* pagination
* caching
* indexing

if applicable.

## Risks

Mention remaining risks or manual verification required.

Example:

```text
Changed:
- StudentStatusService
- StudentStatus model
- Exam Officer status view

Behavior:
- Added official withdrawal status handling.
- Preserved historical result records.

Tests:
- php artisan test --filter=StudentStatusTest

Database:
- Added student_status_records migration.

Performance:
- Added pagination to the status history table.

Risks:
- Senate approval workflow requires manual UI verification.
```

Never claim something was tested if it was not actually tested.

---

# 42. 🔒 PRODUCTION SAFETY

Assume the application may contain live institutional data.

Before executing destructive commands:

1. Determine the environment.
2. Determine the database connection.
3. Confirm whether the database is disposable.
4. Confirm that the command is appropriate.
5. Stop if the environment cannot be established.

Safe operations may be used against confirmed disposable development/test databases:

```bash
php artisan migrate:fresh
php artisan migrate:fresh --seed
php artisan db:wipe
php artisan migrate:refresh
php artisan migrate:reset
```

Do not use these commands against production without explicit authorization.

Production changes should normally use:

* controlled migrations
* deliberate data migrations
* backups where appropriate
* reversible changes where possible

Never destroy production data merely to make a test pass.

---

# 43. ⚡ PERFORMANCE DECISION RULE

Before implementing an interaction:

```text
Does it require server-side state?
        │
        ├── NO ──→ Alpine.js
        │
        └── YES
              ↓
     Does it require database/
     business logic/authorization?
              │
              ├── YES ──→ Livewire/backend
              │
              └── NO ──→ Re-evaluate whether Alpine is sufficient
```

Another useful test:

```text
Can the interaction be completed entirely in the browser?
        │
        ├── YES → Prefer Alpine.js
        │
        └── NO  → Use Livewire/backend
```

The objective is:

> **Use the server when the server is needed. Use the browser when the browser is enough.**

---

# 44. ⚠️ DO NOT OPTIMIZE BY AVOIDING LIVEWIRE AT ALL COSTS

Livewire is appropriate and should be used when server-side interaction is required.

Do not replace a necessary database-backed Livewire interaction with large client-side datasets merely to avoid a server request.

The objective is not:

```text
"Use as little Livewire as possible."
```

The objective is:

```text
"Use the correct tool for each responsibility."
```

Choose the architecture that provides the best balance of:

* responsiveness
* correctness
* security
* maintainability
* database efficiency
* scalability

---

# 45. 🔍 PERFORMANCE VERIFICATION

For performance-sensitive features, inspect the actual implementation.

Consider:

* number of Livewire requests
* database query count
* N+1 queries
* query complexity
* payload size
* component state size
* component re-rendering
* pagination
* debounce behavior
* eager loading
* database indexes
* browser responsiveness
* loading states
* polling
* unnecessary JavaScript
* unnecessary database queries

Do not claim that a feature is optimized without examining the relevant implementation.

---

# 46. 📌 FINAL PRE-COMMIT CHECKLIST

Before considering a task complete:

### Documentation

* [ ] I read `AGENTS.md`.
* [ ] I read `AGENT_CONTEXT.md`.
* [ ] I read the relevant specification/phase document.
* [ ] I checked for conflicts between documentation and implementation.

### Architecture

* [ ] I inspected the existing implementation.
* [ ] I searched for reusable functionality.
* [ ] I followed the existing architecture.
* [ ] I did not create duplicate functionality unnecessarily.
* [ ] I preserved service responsibility boundaries.

### Academic workflows

* [ ] I preserved `Lecturer → Coordinator → Exam Officer → Released`.
* [ ] I preserved Coordinator review.
* [ ] I preserved UG/PG separation.
* [ ] I did not invent academic/business rules.
* [ ] I preserved historical academic records.
* [ ] I preserved institutional auditability.

### UI/UX

* [ ] I followed the Soft UI design system.
* [ ] I reused existing UI components/patterns.
* [ ] I did not introduce a competing UI framework.
* [ ] I verified responsive behavior.
* [ ] I verified loading/empty/error/success states.

### TALL stack

* [ ] I used Alpine.js for browser-only interactions where appropriate.
* [ ] I used Livewire for server-side interactions where appropriate.
* [ ] I avoided unnecessary Livewire requests.
* [ ] I avoided unnecessary `$refresh`/broad component refreshes.
* [ ] I considered component boundaries.
* [ ] I avoided unnecessary re-rendering.
* [ ] I avoided unnecessary polling.
* [ ] I used pagination for large datasets.
* [ ] I checked for N+1 queries.
* [ ] I used appropriate eager loading.
* [ ] I used appropriate debounce behavior.
* [ ] I did not move business logic into Alpine.js.

### Security

* [ ] Authorization is enforced server-side.
* [ ] Existing policies/gates/middleware/permissions remain respected.
* [ ] No security decision depends only on frontend behavior.

### Database

* [ ] I verified the target environment.
* [ ] I did not execute destructive production operations.
* [ ] I preserved existing production data.
* [ ] Migrations are appropriate and safe.
* [ ] Existing records remain compatible.

### Testing

* [ ] Relevant tests were run.
* [ ] Frontend behavior was checked where applicable.
* [ ] Database behavior was checked where applicable.
* [ ] Regressions were considered.
* [ ] I did not claim tests passed unless they actually ran.

### Scope

* [ ] I did not modify unrelated functionality.
* [ ] I did not introduce unnecessary dependencies.
* [ ] I documented important risks.
* [ ] I reported the actual files changed.

---

# 47. 🏁 GOLDEN RULE

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
VERIFY
  ↓
REPORT
```

And remember:

> **Do not guess.**

> **Do not duplicate.**

> **Do not redesign the application unnecessarily.**

> **Do not break existing workflows.**

> **Do not introduce a competing UI system.**

> **Do not move business logic into the browser.**

> **Do not use Livewire when Alpine is sufficient.**

> **Do not avoid Livewire when the server is actually required.**

> **Do not create unnecessary component re-renders.**

> **Do not modify unrelated functionality.**

> **Do not destroy production data.**

> **Do not invent institutional rules.**

> **Preserve historical records and auditability.**

> **Build new functionality so that it feels like it has always been part of this application.**
