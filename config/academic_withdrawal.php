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
    | Rule: Minimum CGPA Threshold
    |--------------------------------------------------------------------------
    | If a student's CGPA falls below this value, they may be flagged for
    | withdrawal recommendation.
    |
    | IMPORTANT: Only enable this after institutional sign-off. The default
    | threshold below is illustrative only and MUST be confirmed.
    |
    | Set 'enabled' => false until the institution confirms the minimum CGPA
    | in writing.
    */
    'minimum_cgpa' => [
        'enabled'   => false,    // DISABLED until institutionally confirmed
        'threshold' => 0.50,     // Must be confirmed by academic regulations
        'reason_code' => 'CGPA_BELOW_MINIMUM',
        'reason'    => 'Cumulative GPA ({cgpa}) is below the institutional minimum of {threshold}.',
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
