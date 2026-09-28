<?php

namespace Database\Factories;

use App\Models\AcademicDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicDetailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'matric_no' => fake()->unique()->numerify('####/###/###'),
            'programme_id' => 1,
            'department_id' => 1,
            'student_level_id' => fake()->numberBetween(1, 5),
            'acad_session' => '2024/2025',
            'admission_session' => '2024/2025',
            'coordinator_id' => null,
        ];
    }
}
