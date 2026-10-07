<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Academic Withdrawal Institutional Configuration
|--------------------------------------------------------------------------
|
| This file defines the configurable withdrawal rules for the Withdrawal
| Eligibility Engine (Task 6.7.2).
|
| IMPORTANT GOVERNANCE RULES:
|   - No withdrawal is triggered automatically from CGPA alone.
|   - All thresholds here are defaults that MUST be confirmed by the
|     institution's approved academic regulations before enabling any rule.
|   - Set 'enabled' => false for any rule not yet confirmed in writing by
|     the institution to prevent premature withdrawal recommendations.
|   - These rules only produce a *recommendation*; Senate approval is
|     required before any official withdrawal can be recorded.
|   - PG students are excluded from all rules in this file.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Application & Senate Bypass
    |--------------------------------------------------------------------------
    | When enabled, academic standing issues and withdrawals are applied
    | automatically to student records without requiring intermediate Senate
    | recommendation queues.
    */
    'auto_apply'    => env('ACADEMIC_WITHDRAWAL_AUTO_APPLY', true),
    'bypass_senate' => env('ACADEMIC_WITHDRAWAL_BYPASS_SENATE', true),

    /*
    |--------------------------------------------------------------------------
    | Rule: Consecutive Academic Probation
    |--------------------------------------------------------------------------
    | A student placed on academic probation for `threshold` consecutive
    | semesters (or sessions, per `unit`) may be recommended for academic
    | withdrawal.
    |
    | unit: 'semester' | 'session'
    */
    'consecutive_probation' => [
        'enabled'   => true,
        'threshold' => 2,      // e.g. 2 consecutive semesters of PROBATION
        'unit'      => 'session', // count per academic session, not per semester
        'reason_code' => 'CONSECUTIVE_PROBATION',
        'reason'    => 'Student has been on academic probation for {threshold} consecutive sessions.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule: Consecutive REPEAT Standing
    |--------------------------------------------------------------------------
    | A student who has received REPEAT standing for `threshold` consecutive
    | sessions may be recommended for withdrawal.
    */
    'consecutive_repeat' => [
        'enabled'   => true,
        'threshold' => 2,
        'unit'      => 'session',
        'reason_code' => 'CONSECUTIVE_REPEAT',
        'reason'    => 'Student has been required to repeat for {threshold} consecutive sessions.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule: Minimum CGPA Threshold (Two-Tier)
    |--------------------------------------------------------------------------
    | FUBK Standard: Two-tier CGPA-based withdrawal
    | - CGPA < 0.50:          WITHDRAWN FROM UNIVERSITY
    | - 0.50 ≤ CGPA < 0.75:  WITHDRAWN FROM PROGRAM
    | - 0.75 ≤ CGPA < 1.00:  PROBATION (handled by standing logic, not here)
    |
    | IMPORTANT: Enabled based on FUBK academic regulations from grade report.
    */
    'minimum_cgpa' => [
        'enabled'   => true,     // ENABLED - matches FUBK policy
        'university_threshold' => 0.50,  // Below this: withdrawn from university
        'program_threshold' => 0.75,     // Below this (but above university): withdrawn from program
        'reason_code_university' => 'CGPA_BELOW_UNIVERSITY_MINIMUM',
        'reason_code_program' => 'CGPA_BELOW_PROGRAM_MINIMUM',
        'reason_university' => 'Cumulative GPA ({cgpa}) is below university minimum ({threshold}). Student withdrawn from university.',
        'reason_program' => 'Cumulative GPA ({cgpa}) is below programme minimum ({threshold}) but above university threshold. Student withdrawn from program.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule: Maximum Programme Residency Exceeded
    |--------------------------------------------------------------------------
    | The NUC allows students a maximum residency of 1.5× the standard
    | programme duration (e.g., 6 years for a 4-year programme). Students
    | who have exceeded this cap without graduating may be recommended for
    | withdrawal.
    |
    | multiplier: Maximum allowed sessions as a multiple of programme
    |             duration (NUC default: 1.5).
    */
    'max_residency_exceeded' => [
        'enabled'    => true,
        'multiplier' => 1.5,   // NUC standard: 1.5× programme duration
        'reason_code' => 'MAX_RESIDENCY_EXCEEDED',
        'reason'     => 'Student has exceeded the maximum programme residency period ({max_sessions} sessions).',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rule: Non-Registration Pattern
    |--------------------------------------------------------------------------
    | A student who fails to register for courses in `threshold` consecutive
    | sessions without an approved medical or voluntary withdrawal may be
    | flagged.
    */
    'non_registration' => [
        'enabled'   => true,
        'threshold' => 2,
        'reason_code' => 'NON_REGISTRATION_PATTERN',
        'reason'    => 'Student has not registered for {threshold} consecutive sessions without an approved absence.',
    ],

];
