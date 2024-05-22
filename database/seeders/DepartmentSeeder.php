<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments =  [
            ['name' => 'Business Administration and Management'],
            ['name' => 'Marketing'],
            ['name' => 'Public Administration'],
            ['name' => 'Local Government Studies'],
            ['name' => 'Accountancy'],
            ['name' => 'Banking and Finance'],
            ['name' => 'Mechanical Engineering'],
            ['name' => 'Metallurgical Engineering'],
            ['name' => 'Electrical Engineering'],
            ['name' => 'Agricultural Engineering'],
            ['name' => 'Civil Engineering'],
            ['name' => 'Architectural Technology'],
            ['name' => 'Building Technology'],
            ['name' => 'Urban and Regional Planning'],
            ['name' => 'Quantity Surveying'],
            ['name' => 'Estate Management'],
            ['name' => 'Surveying and Geo-Informatics'],
            ['name' => 'Industrial Education'],
            ['name' => 'Business Education'],
            ['name' => 'Computer Science'],
            ['name' => 'Statistics'],
            ['name' => 'Sciences Laboratory Technology'],
            ['name' => 'Social Development'],
            ['name' => 'Arts and Social Sciences'],
            ['name' => 'Science Education'],
            ['name' => 'ENGLISH'],
            ['name' => 'Library and Information Science'],
        ];
        DB::table('departments')->insert($departments);
    }
}
