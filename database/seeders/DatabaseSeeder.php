<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('programmes')->truncate();
        DB::table('users')->truncate();
        DB::table('departments')->truncate();
        DB::table('department_programmes')->truncate();
        DB::table('courses')->truncate();

        // DB::table('categories')->truncate();
        // DB::table('tags')->truncate();
        // DB::table('items')->truncate();

        $this->call([
            ProgrammeSeeder::class,
            UserSeeder::class,
            DepartmentSeeder::class,
            DepartmentProgrammeSeeder::class,
            CourseSeeder::class,
            SubjectSeeder::class,
            GradeSeeder::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}