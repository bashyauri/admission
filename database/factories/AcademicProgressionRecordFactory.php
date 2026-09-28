<?php

namespace Database\Factories;

use App\Models\AcademicProgressionRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicProgressionRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicProgressionRecord::class;

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
            'academic_session' => '2024/2025',
            'semester' => fake()->numberBetween(1, 2),
            'level' => fake()->numberBetween(1, 5),
            'cgpa' => fake()->randomFloat(2, 0, 5),
            'standing' => fake()->randomElement(['PROMOTED', 'PROBATION', 'REPEAT', 'SPILLOVER']),
            'withdrawal_recommended' => false,
        ];
    }
}
