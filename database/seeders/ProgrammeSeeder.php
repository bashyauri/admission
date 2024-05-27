<?php

namespace Database\Seeders;

use App\Models\Programme;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $programmes = [
            ['id' => 1, 'name' => 'Higher National Diploma', 'abv' => 'HND'],
            ['id' => 2, 'name' => 'National Diploma', 'abv' => 'ND'],
            ['id' => 3, 'name' => 'National Diploma Special', 'abv' => 'NDS'],
            ['id' => 4, 'name' => 'National Certificate in Education', 'abv' => 'NCE'],
            ['id' => 5, 'name' => 'Polytechnic Diploma', 'abv' => 'PD'],
            ['id' => 6, 'name' => 'Postgraduate Diploma', 'abv' => 'PG'],
        ];

        foreach ($programmes as $programme) {
            Programme::create($programme);
        }
    }
}