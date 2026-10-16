# Programme-Specific Academic Level Mapping

## Purpose

Prevent an academic level ID from displaying as different levels in undergraduate (UG) and postgraduate (PG) workflows. In particular, admin course creation must not present a level label that disagrees with the stored level record.

## Read-only local database audit (2026-10-16)

The application's configured default database is `higher-studies`, but a read-only check found its academic tables empty. The populated local database is `future-academy` (855 academic details); it has the level rows below. Do not point the application at another database until the intended environment is confirmed.

| `student_levels.id` | `student_levels.level` |
| ---: | --- |
| 1 | 100 |
| 2 | 200 |
| 3 | Spill Over |
| 4 | 400 |
| 5 | 500 |
| 6 | 300 |

The application enum `App\\Enums\\StudentLevel` currently maps IDs 1–5 to 100, 200, 300, 400, and Spill Over. The seeder instead maps IDs 1–3 to PGD 1, PGD 2, and Spill Over. These sources disagree.

Observed references in the same database:

| Table / column | Observed rows by level ID |
| --- | --- |
| `academic_details.student_level_id` (`future-academy`) | 1: 449; 2: 401; 3: 5 |
| Programme 6 (Postgraduate Diploma) academic details | 1: 143; 2: 72; 3: 5 |
| Programme 7 (Degree Program) academic details | 1: 306; 2: 329 |
| `student_courses.student_level_id` | 1: 74; 2: 93 |
| `registered_courses.student_level_id` | 1: 11,490; 2: 4,441 |
| `coordinators.student_level_id` | 1: 5; 2: 9; null: 5 |
| `department_max_units.student_level_id` | 1: 7; 2: 7 |
| `fee_structures.student_level_id` | 1: 7; 2: 14 |
| `school_fees_payments.student_level_id` | 1: 163; 2: 12 |
| `student_transactions.student_levels_id` | 1: 955; 2: 339; 3: 2 |

`ug_student_levels` does not exist in this database. The audit did not modify records, run migrations, or run seeders. Counts describe the configured local database only; re-run the preflight against the target database before any production migration.

## Findings

1. `student_level_id` is currently an ordinal-like value reused across programmes: ID 1 is used by UG 100-level students and PGD 1 students; ID 2 is used by UG 200-level students and PGD 2 students.
2. The display label cannot be determined from the level ID alone. The owning programme is required.
3. ID 3 is especially unsafe to infer: the database labels it `Spill Over`, the UG enum calls it 300, and the PG cohort has five academic details at ID 3.
4. `StudentLevelSeeder` is not called by `DatabaseSeeder`; rerunning all seeders is not a safe repair and the application explicitly must not refresh production data.
5. Admin's `student_courses` catalog stores level IDs 1 and 2 in the audited data. The admin course form previously sourced choices from the global UG enum; it now reads actual `student_levels` rows labeled 100–500 and submits their row IDs. The PG course catalog is represented through `courses` and programme associations.

## Recommended design

Make levels programme-specific records instead of assigning one global meaning to each integer. A level record should carry at least:

- `programme_id`
- a stable level key/code (for example `100`, `PGD1`, `PGD2`, `SPILLOVER`)
- a display label
- a progression order

Enforce uniqueness for `(programme_id, level_key)`. Course creation and assignment screens should choose a programme first and then offer only that programme's levels. All user-facing level labels should be resolved using both programme and level identity, never by appending `00` to an ID or reading a global label without programme context.

Do not change the meaning of IDs 1, 2, or 3 in place. Add programme-specific level records with new IDs and migrate references only after each reference can be attributed unambiguously to a programme. Preserve historical result and registration snapshots; do not rewrite those snapshots as part of this change.

## Migration and rollout sequence

1. **Preflight every target environment.** Export IDs, labels, programme counts, and per-level references for every table containing `student_level_id` or `student_levels_id`. Record actual programme IDs from the live database, not assumptions from seed files.
2. **Build an explicit mapping table.** Map each legacy `(programme_id, old_level_id)` to a new programme-specific level ID. Stop if any row cannot be attributed to a programme or if the mapping conflicts with official academic records.
3. **Additive schema change.** Add programme-specific level identity and uniqueness while retaining old columns/relationships during compatibility rollout. Do not truncate or reseed `student_levels`.
4. **Backfill transactionally.** Resolve each consumer through its owning academic detail, course/programme, or other authoritative relationship. Do not guess mappings for rows without a reliable owner; report them for manual review. Rebuild dependent indexes/foreign keys only after verifying reference totals.
5. **Switch writes and displays.** Update UG and PG forms, progression, coordinator filters, registrations, fee screens, and reports to use programme-specific levels. Keep UG/PG workflows isolated and preserve historical snapshots.
6. **Verify before cleanup.** Compare row counts and per-programme cohorts before and after, inspect ambiguous rows, and verify representative UG and PG records in the UI. Remove legacy compatibility only in a later reviewed migration.

## Required implementation safeguards

- Never run `migrate:fresh`, `db:seed`, or a destructive refresh against production.
- The migration must be idempotent where practical and must fail closed when the preflight mapping is incomplete.
- Include rollback guidance and a restorable backup before production deployment.
- Cover UG and PG separately in regression tests, including course creation, course registration, reporting, and level display.
- Update `AGENT_CONTEXT.md` and relevant workflow documentation when implementation begins.

## Current status

The read-only audit confirms the user-added row is ID 6 = 300; ID 3 remains `Spill Over` and is referenced by five PG academic details. `future-academy` has no catalog courses currently assigned to ID 3 or ID 6. The configured default `higher-studies` database is empty, so its intended role needs confirmation before changing `.env` or treating it as the student-facing test database. Application code now resolves UG ordinal and catalog options from level labels rather than assuming IDs are ordinals; unrecognized labels remain at their current ID during progression. Level ID 6 has no academic details, course offerings, department unit-cap rows, fee structures, registrations, or payment records in `future-academy`. The registration page now displays actual level labels and handles the missing unit cap without a divide-by-zero error; registration configuration must be completed before 300-level students can use the form. No database writes, migrations, or seeders were run. The broader programme-specific schema migration remains pending a production audit and explicit mapping, particularly for PG ID 3.
