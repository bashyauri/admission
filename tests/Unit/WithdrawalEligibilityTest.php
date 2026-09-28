<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\ProgrammesEnum;
use App\Models\AcademicDetail;
use App\Models\AcademicProgressionRecord;
use App\Models\Course;
use App\Models\Department;
use App\Models\Programme;
use App\Models\ResultGpaRecord;
use App\Models\StudentLevel;
use App\Models\User;
use App\Services\AcademicProgressionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Task 6.7.2 — Withdrawal Eligibility Engine Tests
 *
 * Test coverage:
 *  1. PG students are always ineligible.
 *  2. UG student in good standing returns eligible = false.
 *  3. Single-session probation (below threshold) → ineligible.
 *  4. Consecutive probation at threshold → eligible.
 *  5. Consecutive repeat standing at threshold → eligible.
 *  6. CGPA below minimum when rule is disabled → ineligible.
 *  7. CGPA below minimum when rule is enabled → eligible.
 *  8. Max residency exceeded → eligible.
 *  9. Multiple rules trigger simultaneously — all included in triggered_rules.
 * 10. Structured return type assertion.
 * 11. Consecutive count resets after one good session (no false positives).
 * 12. AcademicProgressionRecord takes priority over GPA record fallback.
 */
class WithdrawalEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected AcademicProgressionService $service;
    protected Department $department;
    protected Programme $ugProgramme;
    protected Programme $pgProgramme;
    protected Course $ugCourse;
    protected Course $pgCourse;
    protected StudentLevel $level300;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AcademicProgressionService();

        $this->department = Department::create(['name' => 'Computer Science']);

        $this->ugProgramme = Programme::find(ProgrammesEnum::Undergraduate->value)
            ?? Programme::forceCreate(['name' => 'B.Sc Computer Science', 'abv' => 'UG', 'id' => ProgrammesEnum::Undergraduate->value]);

        $this->pgProgramme = Programme::find(ProgrammesEnum::PG->value)
            ?? Programme::forceCreate(['name' => 'M.Sc Computer Science', 'abv' => 'PG', 'id' => ProgrammesEnum::PG->value]);

        $this->ugCourse = Course::create([
            'name'          => 'Computer Science',
            'department_id' => $this->department->id,
            'programme_id'  => $this->ugProgramme->id,
            'semesters'     => 8, // 4-year programme
        ]);

        $this->pgCourse = Course::create([
            'name'          => 'Computer Science (PG)',
            'department_id' => $this->department->id,
            'programme_id'  => $this->pgProgramme->id,
        ]);

        $this->level300 = StudentLevel::where('level', '300')->first()
            ?? StudentLevel::create(['level' => '300']);

        // Default: disable all rules so each test can opt-in explicitly
        $this->setWithdrawalConfig();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Replace the entire academic_withdrawal config. Only the rules explicitly
     * listed in $enabled will be switched on; everything else stays disabled.
     *
     * @param array<string, array<string, mixed>> $enabled  Keys are rule names, values are overrides.
     */
    private function setWithdrawalConfig(array $enabled = []): void
    {
        $defaults = [
            'consecutive_probation'  => ['enabled' => false, 'threshold' => 2,    'unit' => 'session', 'reason_code' => 'CONSECUTIVE_PROBATION',  'reason' => '{threshold} consecutive sessions of probation.'],
            'consecutive_repeat'     => ['enabled' => false, 'threshold' => 2,    'unit' => 'session', 'reason_code' => 'CONSECUTIVE_REPEAT',      'reason' => '{threshold} consecutive repeat sessions.'],
            'minimum_cgpa'           => ['enabled' => false, 'threshold' => 0.50,                      'reason_code' => 'CGPA_BELOW_MINIMUM',      'reason' => 'CGPA ({cgpa}) below {threshold}.'],
            'max_residency_exceeded' => ['enabled' => false, 'multiplier' => 1.5,                      'reason_code' => 'MAX_RESIDENCY_EXCEEDED',  'reason' => 'Max {max_sessions} sessions exceeded.'],
            'non_registration'       => ['enabled' => false, 'threshold' => 2,                         'reason_code' => 'NON_REGISTRATION_PATTERN','reason' => '{threshold} consecutive non-registration sessions.'],
        ];

        foreach ($enabled as $rule => $overrides) {
            if (isset($defaults[$rule])) {
                $defaults[$rule] = array_merge($defaults[$rule], $overrides, ['enabled' => true]);
            }
        }

        config(['academic_withdrawal' => $defaults]);
    }

    private function enableRule(string $rule, array $overrides = []): void
    {
        $current = config("academic_withdrawal.{$rule}", []);
        $merged = array_merge($current, $overrides, ['enabled' => true]);
        config(["academic_withdrawal.{$rule}" => $merged]);
    }

    private function makeUgStudent(?string $email = null): User
    {
        return User::create([
            'id'           => (string) Str::uuid(),
            'programme_id' => $this->ugProgramme->id,
            'email'        => $email ?? 'ug_' . rand(1000, 9999) . '@test.com',
            'password'     => bcrypt('password'),
            'vpassword'    => 'password',
            'role'         => 'student',
            'firstname'    => 'Test',
            'surname'      => 'Student',
        ]);
    }

    private function makePgStudent(): User
    {
        return User::create([
            'id'           => (string) Str::uuid(),
            'programme_id' => $this->pgProgramme->id,
            'email'        => 'pg_' . rand(1000, 9999) . '@test.com',
            'password'     => bcrypt('password'),
            'vpassword'    => 'password',
            'role'         => 'student',
            'firstname'    => 'PG',
            'surname'      => 'Student',
        ]);
    }

    private function attachAcademicDetail(User $user, Course $course, StudentLevel $level, string $admissionSession = '2021/2022'): AcademicDetail
    {
        return AcademicDetail::create([
            'user_id'           => $user->id,
            'matric_no'         => 'MAT/' . rand(10000, 99999),
            'course_id'         => $course->id,
            'programme_id'      => $user->programme_id,
            'department_id'     => $this->department->id,
            'student_level_id'  => $level->id,
            'acad_session'      => '2024/2025',
            'admission_session' => $admissionSession,
        ]);
    }

    private function addGpaRecord(User $user, AcademicDetail $detail, string $session, float $cgpa, int $semester = 2): ResultGpaRecord
    {
        return ResultGpaRecord::create([
            'user_id'                  => $user->id,
            'academic_detail_id'       => $detail->id,
            'academic_session'         => $session,
            'semester'                 => $semester === 1 ? 'first' : 'second',
            'semester_gpa'             => $cgpa,
            'cumulative_gpa'           => $cgpa,
            'total_credit_units'       => 18,
            'total_grade_points'       => (int) ($cgpa * 18),
            'cumulative_credit_units'  => 36,
            'cumulative_grade_points'  => (int) ($cgpa * 36),
            'class_of_degree'          => $cgpa >= 1.50 ? 'Second Class Lower Division' : 'Fail',
        ]);
    }

    private function addProgressionRecord(User $user, AcademicDetail $detail, string $session, string $standing, int $semester = 2, float $cgpa = 1.20): AcademicProgressionRecord
    {
        return AcademicProgressionRecord::create([
            'user_id'                => $user->id,
            'academic_detail_id'     => $detail->id,
            'academic_session'       => $session,
            'semester'               => $semester,
            'level'                  => '300',
            'cgpa'                   => $cgpa,
            'standing'               => $standing,
            'withdrawal_recommended' => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    public function test_postgraduate_student_is_always_ineligible(): void
    {
        $pg = $this->makePgStudent();

        $result = $this->service->evaluateWithdrawalEligibility($pg);

        $this->assertFalse($result['eligible']);
        $this->assertNull($result['reason_code']);
        $this->assertEmpty($result['triggered_rules']);
        $this->assertStringContainsString('Postgraduate', $result['reason']);
    }

    public function test_good_standing_student_is_not_eligible(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        $this->addGpaRecord($user, $detail, '2022/2023', 2.50);
        $this->addGpaRecord($user, $detail, '2023/2024', 2.80);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertFalse($result['eligible']);
        $this->assertEmpty($result['triggered_rules']);
        $this->assertEquals(AcademicProgressionService::STANDING_PROMOTED, $result['standing']);
    }

    public function test_single_probation_session_does_not_trigger_eligibility(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        $this->addGpaRecord($user, $detail, '2023/2024', 1.20); // PROBATION (one session)

        $this->enableRule('consecutive_probation', ['threshold' => 2, 'unit' => 'session']);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertFalse($result['eligible']);
        $this->assertNotContains('CONSECUTIVE_PROBATION', $result['triggered_rules']);
    }

    public function test_two_consecutive_probation_sessions_triggers_eligibility(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        $this->addGpaRecord($user, $detail, '2022/2023', 1.20); // PROBATION
        $this->addGpaRecord($user, $detail, '2023/2024', 1.10); // PROBATION again

        $this->enableRule('consecutive_probation', [
            'threshold'   => 2,
            'unit'        => 'session',
            'reason_code' => 'CONSECUTIVE_PROBATION',
            'reason'      => '{threshold} consecutive sessions of probation.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);
        $this->assertTrue($result['eligible']);
        $this->assertContains('CONSECUTIVE_PROBATION', $result['triggered_rules']);
        $this->assertEquals('CONSECUTIVE_PROBATION', $result['reason_code']);
        $this->assertEquals(AcademicProgressionService::STANDING_PROBATION, $result['standing']);
    }

    public function test_two_consecutive_repeat_sessions_triggers_eligibility(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        $this->addGpaRecord($user, $detail, '2022/2023', 0.80); // REPEAT
        $this->addGpaRecord($user, $detail, '2023/2024', 0.70); // REPEAT again

        $this->enableRule('consecutive_repeat', [
            'threshold'   => 2,
            'unit'        => 'session',
            'reason_code' => 'CONSECUTIVE_REPEAT',
            'reason'      => '{threshold} consecutive repeat sessions.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertTrue($result['eligible']);
        $this->assertContains('CONSECUTIVE_REPEAT', $result['triggered_rules']);
        $this->assertEquals('CONSECUTIVE_REPEAT', $result['reason_code']);
    }

    public function test_cgpa_below_minimum_is_ineligible_when_rule_disabled(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);
        $this->addGpaRecord($user, $detail, '2023/2024', 0.30); // Very low CGPA

        // All rules remain disabled (setUp calls disableAllRules)
        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertFalse($result['eligible']);
        $this->assertNotContains('CGPA_BELOW_MINIMUM', $result['triggered_rules']);
    }

    public function test_cgpa_below_minimum_triggers_eligibility_when_rule_enabled(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);
        $this->addGpaRecord($user, $detail, '2023/2024', 0.30); // Below 0.50

        $this->enableRule('minimum_cgpa', [
            'threshold'   => 0.50,
            'reason_code' => 'CGPA_BELOW_MINIMUM',
            'reason'      => 'CGPA ({cgpa}) below {threshold}.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertTrue($result['eligible']);
        $this->assertContains('CGPA_BELOW_MINIMUM', $result['triggered_rules']);
        $this->assertEquals('CGPA_BELOW_MINIMUM', $result['reason_code']);
    }

    /**
     * For a 4-year programme (maxLevel=4) with 1.5× multiplier, maxSessions = ceil(4 * 1.5) = 6.
     * 7 distinct sessions studied → exceeds max of 6 → eligible.
     */
    public function test_max_residency_exceeded_triggers_eligibility(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300, '2016/2017');

        foreach (['2016/2017', '2017/2018', '2018/2019', '2019/2020', '2020/2021', '2021/2022', '2022/2023'] as $session) {
            $this->addGpaRecord($user, $detail, $session, 2.00); // 7 sessions
        }

        $this->enableRule('max_residency_exceeded', [
            'multiplier'  => 1.5,
            'reason_code' => 'MAX_RESIDENCY_EXCEEDED',
            'reason'      => 'Max {max_sessions} sessions exceeded.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertTrue($result['eligible']);
        $this->assertContains('MAX_RESIDENCY_EXCEEDED', $result['triggered_rules']);
    }

    public function test_multiple_triggered_rules_are_all_reported(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300, '2016/2017');

        // 7 sessions all at REPEAT standing → both consecutive_repeat AND max_residency fire
        foreach (['2016/2017', '2017/2018', '2018/2019', '2019/2020', '2020/2021', '2021/2022', '2022/2023'] as $session) {
            $this->addGpaRecord($user, $detail, $session, 0.70);
        }

        $this->enableRule('consecutive_repeat', [
            'threshold'   => 2,
            'unit'        => 'session',
            'reason_code' => 'CONSECUTIVE_REPEAT',
            'reason'      => '{threshold} consecutive repeat sessions.',
        ]);
        $this->enableRule('max_residency_exceeded', [
            'multiplier'  => 1.5,
            'reason_code' => 'MAX_RESIDENCY_EXCEEDED',
            'reason'      => 'Max {max_sessions} sessions exceeded.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertTrue($result['eligible']);
        $this->assertContains('CONSECUTIVE_REPEAT', $result['triggered_rules']);
        $this->assertContains('MAX_RESIDENCY_EXCEEDED', $result['triggered_rules']);
        $this->assertCount(2, $result['triggered_rules']);
    }

    public function test_return_type_has_all_required_keys(): void
    {
        $user = $this->makeUgStudent();

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertArrayHasKey('eligible', $result);
        $this->assertArrayHasKey('reason_code', $result);
        $this->assertArrayHasKey('reason', $result);
        $this->assertArrayHasKey('standing', $result);
        $this->assertArrayHasKey('cgpa', $result);
        $this->assertArrayHasKey('triggered_rules', $result);

        $this->assertIsBool($result['eligible']);
        $this->assertIsString($result['reason']);
        $this->assertIsString($result['standing']);
        $this->assertIsFloat($result['cgpa']);
        $this->assertIsArray($result['triggered_rules']);
    }

    public function test_good_session_interrupts_consecutive_count(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        // PROBATION → PROMOTED → PROBATION (not consecutive; only 1 at the end)
        $this->addGpaRecord($user, $detail, '2021/2022', 1.20); // PROBATION
        $this->addGpaRecord($user, $detail, '2022/2023', 2.00); // PROMOTED  ← interrupts
        $this->addGpaRecord($user, $detail, '2023/2024', 1.30); // PROBATION (only 1 consecutive)

        $this->enableRule('consecutive_probation', [
            'threshold'   => 2,
            'unit'        => 'session',
            'reason_code' => 'CONSECUTIVE_PROBATION',
            'reason'      => '{threshold} consecutive sessions of probation.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertFalse($result['eligible']);
        $this->assertNotContains('CONSECUTIVE_PROBATION', $result['triggered_rules']);
    }

    public function test_academic_progression_record_takes_priority_over_gpa_fallback(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300);

        // GPA records say PROMOTED (2.50, 2.80)
        $this->addGpaRecord($user, $detail, '2022/2023', 2.50);
        $this->addGpaRecord($user, $detail, '2023/2024', 2.80);

        // But explicit AcademicProgressionRecord entries override with PROBATION
        $this->addProgressionRecord($user, $detail, '2022/2023', AcademicProgressionService::STANDING_PROBATION, 2, 1.20);
        $this->addProgressionRecord($user, $detail, '2023/2024', AcademicProgressionService::STANDING_PROBATION, 2, 1.30);

        $this->enableRule('consecutive_probation', [
            'threshold'   => 2,
            'unit'        => 'session',
            'reason_code' => 'CONSECUTIVE_PROBATION',
            'reason'      => '{threshold} consecutive sessions of probation.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        // Even though raw GPA data says PROMOTED, the persisted progression record
        // takes priority — revealing the true institutional standing of PROBATION.
        $this->assertTrue($result['eligible']);
        $this->assertContains('CONSECUTIVE_PROBATION', $result['triggered_rules']);
    }

    public function test_non_registration_pattern_triggers_eligibility(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300, '2021/2022');
        $detail->update(['acad_session' => '2024/2025']);

        // Registered for 2021/2022 and 2022/2023; absent for 2023/2024 and 2024/2025 (2 consecutive)
        $this->addGpaRecord($user, $detail, '2021/2022', 2.50);
        $this->addGpaRecord($user, $detail, '2022/2023', 2.50);

        $this->enableRule('non_registration', [
            'threshold'   => 2,
            'reason_code' => 'NON_REGISTRATION_PATTERN',
            'reason'      => '{threshold} consecutive non-registration sessions.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertTrue($result['eligible']);
        $this->assertContains('NON_REGISTRATION_PATTERN', $result['triggered_rules']);
        $this->assertEquals('NON_REGISTRATION_PATTERN', $result['reason_code']);
    }

    public function test_non_registration_below_threshold_is_ineligible(): void
    {
        $user   = $this->makeUgStudent();
        $detail = $this->attachAcademicDetail($user, $this->ugCourse, $this->level300, '2022/2023');
        $detail->update(['acad_session' => '2024/2025']);

        // Registered in 2022/2023 and 2023/2024; missing only 2024/2025 (1 session < threshold 2)
        $this->addGpaRecord($user, $detail, '2022/2023', 2.50);
        $this->addGpaRecord($user, $detail, '2023/2024', 2.50);

        $this->enableRule('non_registration', [
            'threshold'   => 2,
            'reason_code' => 'NON_REGISTRATION_PATTERN',
            'reason'      => '{threshold} consecutive non-registration sessions.',
        ]);

        $result = $this->service->evaluateWithdrawalEligibility($user);

        $this->assertFalse($result['eligible']);
        $this->assertNotContains('NON_REGISTRATION_PATTERN', $result['triggered_rules']);
    }
}
