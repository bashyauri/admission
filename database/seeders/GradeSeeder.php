<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grades =  [
            ['name' => 'A1'],
            ['name' => 'B2'],
            ['name' => 'B3'],
            ['name' => 'C4'],
            ['name' => 'C5'],
            ['name' => 'C6'],
            ['name' => 'D7'],
            ['name' => 'E8'],
            ['name' => 'F9'],
        ];
        DB::table('grades')->insert($grades);
    }
}
