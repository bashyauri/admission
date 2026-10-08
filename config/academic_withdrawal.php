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
|   - Eligibility rules identify students for staff review only.
|   - Thresholds and enabled rules must match FUBK-approved academic regulations.
|   - Senate approval is required before an official withdrawal is recorded.
|   - PG students are excluded from all rules in this file.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Application & Senate Bypass
    |--------------------------------------------------------------------------
    | The progression engine only prepares a staff recommendation when a rule
    | matches. Senate approval is required before an official status is created.
    */
    'auto_apply'    => env('ACADEMIC_WITHDRAWAL_AUTO_APPLY', false),
    'bypass_senate' => env('ACADEMIC_WITHDRAWAL_BYPASS_SENATE', false),

    /*
    |--------------------------------------------------------------------------
    | Rule: Consecutive Academic Probation
    |--------------------------------------------------------------------------
    | A student at CGPA 0.75–0.99 is on probation. Two consecutive probation
    | sessions make the student eligible for a programme-withdrawal recommendation.
    |
    | unit: 'semester' | 'session'
    */
    'consecutive_probation' => [
        'enabled'   => true,
        'threshold' => 2,      // two consecutive probation sessions
        'unit'      => 'session', // count per academic session, not per semester
        'reason_code' => 'CONSECUTIVE_PROBATION',
        'reason'    => 'Student has been on academic probation for {threshold} consecutive sessions; recommend programme withdrawal for Senate review.',
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
    | CGPA below 0.50: university withdrawal recommendation.
    | CGPA 0.50–0.74: programme withdrawal recommendation.
    | CGPA 0.75–0.99: probation; two consecutive probation sessions trigger
    | a programme-withdrawal recommendation.
    | - CGPA < 0.50:          recommend withdrawal from the University
    | - 0.50 ≤ CGPA < 0.75:  recommend withdrawal from the programme
    |
    | These thresholds follow the FUBK rules confirmed for this workflow.
    */
    'minimum_cgpa' => [
        'enabled'   => true,     // Active per confirmed FUBK thresholds
        'university_threshold' => 0.50,  // Below this: recommend university withdrawal to Senate
        'program_threshold' => 0.75,     // Below this (but above university): recommend programme withdrawal
        'reason_code_university' => 'CGPA_BELOW_UNIVERSITY_MINIMUM',
        'reason_code_program' => 'CGPA_BELOW_PROGRAM_MINIMUM',
        'reason_university' => 'Cumulative GPA ({cgpa}) is below the university minimum ({threshold}); recommend the case for Senate review.',
        'reason_program' => 'Cumulative GPA ({cgpa}) is below the programme minimum ({threshold}) but above the university threshold; recommend the case for Senate review.',
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
