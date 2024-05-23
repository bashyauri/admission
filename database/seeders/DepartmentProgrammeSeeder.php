<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['program_id' => 1, 'department_id' => 1],
            ['program_id' => 1, 'department_id' => 2],
            ['program_id' => 1, 'department_id' => 3],
            ['program_id' => 1, 'department_id' => 5],
            ['program_id' => 1, 'department_id' => 6],
            ['program_id' => 1, 'department_id' => 7],
            ['program_id' => 1, 'department_id' => 8],
            ['program_id' => 1, 'department_id' => 9],
            ['program_id' => 1, 'department_id' => 10],
            ['program_id' => 1, 'department_id' => 11],
            ['program_id' => 1, 'department_id' => 12],
            ['program_id' => 1, 'department_id' => 13],
            ['program_id' => 1, 'department_id' => 14],
            ['program_id' => 1, 'department_id' => 15],
            ['program_id' => 1, 'department_id' => 16],
            ['program_id' => 1, 'department_id' => 17],
            ['program_id' => 1, 'department_id' => 20],
            ['program_id' => 1, 'department_id' => 21],
            ['program_id' => 1, 'department_id' => 22],
            ['program_id' => 2, 'department_id' => 1],
            ['program_id' => 2, 'department_id' => 2],
            ['program_id' => 2, 'department_id' => 3],
            ['program_id' => 2, 'department_id' => 4],
            ['program_id' => 2, 'department_id' => 5],
            ['program_id' => 2, 'department_id' => 6],
            ['program_id' => 2, 'department_id' => 7],
            ['program_id' => 2, 'department_id' => 8],
            ['program_id' => 2, 'department_id' => 9],
            ['program_id' => 2, 'department_id' => 10],
            ['program_id' => 2, 'department_id' => 11],
            ['program_id' => 2, 'department_id' => 12],
            ['program_id' => 2, 'department_id' => 13],
            ['program_id' => 2, 'department_id' => 14],
            ['program_id' => 2, 'department_id' => 15],
            ['program_id' => 2, 'department_id' => 16],
            ['program_id' => 2, 'department_id' => 17],
            ['program_id' => 2, 'department_id' => 20],
            ['program_id' => 2, 'department_id' => 21],
            ['program_id' => 2, 'department_id' => 22],
            ['program_id' => 2, 'department_id' => 23],
            ['program_id' => 2, 'department_id' => 27],
            ['program_id' => 3, 'department_id' => 1],
            ['program_id' => 3, 'department_id' => 2],
            ['program_id' => 3, 'department_id' => 3],
            ['program_id' => 3, 'department_id' => 4],
            ['program_id' => 3, 'department_id' => 5],
            ['program_id' => 3, 'department_id' => 6],
            ['program_id' => 3, 'department_id' => 7],
            ['program_id' => 3, 'department_id' => 8],
            ['program_id' => 3, 'department_id' => 9],
            ['program_id' => 3, 'department_id' => 10],
            ['program_id' => 3, 'department_id' => 11],
            ['program_id' => 3, 'department_id' => 12],
            ['program_id' => 3, 'department_id' => 13],
            ['program_id' => 3, 'department_id' => 14],
            ['program_id' => 3, 'department_id' => 15],
            ['program_id' => 3, 'department_id' => 16],
            ['program_id' => 3, 'department_id' => 17],
            ['program_id' => 3, 'department_id' => 20],
            ['program_id' => 3, 'department_id' => 21],
            ['program_id' => 3, 'department_id' => 23],
            ['program_id' => 3, 'department_id' => 27],
            ['program_id' => 4, 'department_id' => 18],
            ['program_id' => 4, 'department_id' => 19],
            ['program_id' => 4, 'department_id' => 25],
            ['program_id' => 5, 'department_id' => 1],
            ['program_id' => 5, 'department_id' => 2],
            ['program_id' => 5, 'department_id' => 3],
            ['program_id' => 5, 'department_id' => 5],
            ['program_id' => 5, 'department_id' => 6],
            ['program_id' => 5, 'department_id' => 7],
            ['program_id' => 5, 'department_id' => 8],
            ['program_id' => 5, 'department_id' => 9],
            ['program_id' => 5, 'department_id' => 10],
            ['program_id' => 5, 'department_id' => 11],
            ['program_id' => 5, 'department_id' => 13],
            ['program_id' => 5, 'department_id' => 14],
            ['program_id' => 5, 'department_id' => 15],
            ['program_id' => 5, 'department_id' => 16],
            ['program_id' => 5, 'department_id' => 17],
            ['program_id' => 5, 'department_id' => 21],
            ['program_id' => 5, 'department_id' => 23],
            ['program_id' => 5, 'department_id' => 24],
            ['program_id' => 5, 'department_id' => 26],
            ['program_id' => 5, 'department_id' => 27],


        ];
        DB::table('department_programmes')->insert($data);
    }
}