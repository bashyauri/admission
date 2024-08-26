<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StudentLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            ['id' => 1, 'level' => 'PGD 1'],
            ['id' => 2, 'level' => 'PGD 2'],
            ['id' => 3, 'level' => 'Spill Over'],



        ];
        DB::table('student_levels')->insert($levels);
    }
}
