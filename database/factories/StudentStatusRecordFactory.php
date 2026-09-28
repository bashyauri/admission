<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Enums\StudentStatusType;
use App\Models\StudentStatusRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentStatusRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentStatusRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'academic_detail_id' => null,
            'status' => fake()->randomElement(StudentStatus::cases()),
            'status_type' => fake()->randomElement(StudentStatusType::cases()),
            'reason_code' => fake()->randomElement(['CONSECUTIVE_PROBATION', 'CONSECUTIVE_REPEAT', 'VOLUNTARY', 'MEDICAL']),
            'reason' => fake()->sentence(),
            'academic_session' => '2024/2025',
            'semester' => fake()->numberBetween(1, 2),
            'effective_date' => fake()->date(),
            'end_date' => null,
            'senate_reference' => null,
            'senate_decision_date' => null,
            'senate_decision' => 'WITHDRAWAL_RECOMMENDED',
            'reinstatement_eligible' => true,
            'processed_by' => null,
            'notes' => fake()->paragraph(),
        ];
    }
}
