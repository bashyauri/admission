<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Result;
use App\Services\AcademicProgressionService;
use App\Services\GradeCalculationService;
use App\Services\ResultReportingService;
use PHPUnit\Framework\TestCase;

class ResultReportingServiceTest extends TestCase
{
    protected ResultReportingService $reportingService;
    protected GradeCalculationService $gradeCalculator;
    protected AcademicProgressionService $progressionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradeCalculator = new GradeCalculationService();
        $this->progressionService = new AcademicProgressionService();
        $this->reportingService = new ResultReportingService($this->gradeCalculator, $this->progressionService);
    }

    public function test_senate_summary_statistics_calculation(): void
    {
        $rows = collect([
            [
                'matric_no' => 'CSC/20/001',
                'student_name' => 'ADAMU IBRAHIM',
                'remark' => 'PASS',
                'standing' => AcademicProgressionService::STANDING_PROMOTED,
                'class_of_degree' => 'First Class Honours',
            ],
            [
                'matric_no' => 'CSC/20/002',
                'student_name' => 'FATIMA BELLO',
                'remark' => 'PASS',
                'standing' => AcademicProgressionService::STANDING_PROMOTED,
                'class_of_degree' => 'Second Class Upper Division',
            ],
            [
                'matric_no' => 'CSC/20/003',
                'student_name' => 'MUSA YAHAYA',
                'remark' => 'REPEAT: CSC101, CSC102',
                'standing' => AcademicProgressionService::STANDING_PROMOTED,
                'class_of_degree' => 'Second Class Lower Division',
            ],
            [
                'matric_no' => 'CSC/20/004',
                'student_name' => 'ZAINAB USMAN',
                'remark' => 'PROBATION',
                'standing' => AcademicProgressionService::STANDING_PROBATION,
                'class_of_degree' => 'Pass',
            ],
            [
                'matric_no' => 'CSC/20/005',
                'student_name' => 'EMMANUEL OKON',
                'remark' => 'SPILLOVER',
                'standing' => AcademicProgressionService::STANDING_SPILLOVER,
                'class_of_degree' => 'Third Class Honours',
            ],
        ]);

        $summary = $this->reportingService->getSenateSummaryStats($rows);

        $this->assertEquals(5, $summary['total_students']);
        $this->assertEquals(2, $summary['pass_count']);
        $this->assertEquals(1, $summary['repeat_count']);
        $this->assertEquals(1, $summary['probation_count']);
        $this->assertEquals(1, $summary['spillover_count']);
        $this->assertEquals(40.0, $summary['pass_percentage']);
        $this->assertEquals(20.0, $summary['repeat_percentage']);
        $this->assertEquals(20.0, $summary['probation_percentage']);

        $this->assertEquals(1, $summary['class_distribution']['First Class Honours']);
        $this->assertEquals(1, $summary['class_distribution']['Second Class Upper Division']);
        $this->assertEquals(1, $summary['class_distribution']['Second Class Lower Division']);
        $this->assertEquals(1, $summary['class_distribution']['Third Class Honours']);
        $this->assertEquals(1, $summary['class_distribution']['Pass']);
        $this->assertEquals(0, $summary['class_distribution']['Fail']);
    }

    public function test_grade_calculation_service_integration_with_reporting(): void
    {
        // Test NUC 5-point scale used by ResultReportingService
        $this->assertEquals('A', $this->gradeCalculator->calculateGrade(82.5));
        $this->assertEquals(5, $this->gradeCalculator->calculateGradePoint(82.5));
        $this->assertEquals(15, $this->gradeCalculator->calculateQualityPoints(5, 3));

        $this->assertEquals('C', $this->gradeCalculator->calculateGrade(54.0));
        $this->assertEquals(3, $this->gradeCalculator->calculateGradePoint(54.0));
        $this->assertEquals(9, $this->gradeCalculator->calculateQualityPoints(3, 3));

        $this->assertEquals('F', $this->gradeCalculator->calculateGrade(38.0));
        $this->assertEquals(0, $this->gradeCalculator->calculateGradePoint(38.0));
        $this->assertEquals(0, $this->gradeCalculator->calculateQualityPoints(0, 3));

        // GPA calculation
        $r1 = new Result(['credit_units' => 3, 'grade_point' => 5]); // 15
        $r2 = new Result(['credit_units' => 3, 'grade_point' => 4]); // 12
        $r3 = new Result(['credit_units' => 2, 'grade_point' => 3]); // 6
        $calc = $this->gradeCalculator->calculateSemesterGpa(collect([$r1, $r2, $r3]));

        $this->assertEquals(8, $calc['total_units']);
        $this->assertEquals(33, $calc['total_points']);
        $this->assertEquals(4.13, $calc['semester_gpa']);
        $this->assertEquals('Second Class Upper Division', $this->gradeCalculator->getClassOfDegree(4.13));
    }
}
