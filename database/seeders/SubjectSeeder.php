<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [
            ['name' => 'Agricultural Science'],
            ['name' => 'Animal Husbandry'],
            ['name' => 'Automobile Parts Merchandising'],
            ['name' => 'Biology'],
            ['name' => 'Book Keeping'],
            ['name' => 'Catering Craft Practice'],
            ['name' => 'Chemistry'],
            ['name' => 'Christian Studies'],
            ['name' => 'Civic Education'],
            ['name' => 'Clothing & Textile'],
            ['name' => 'Commerce'],
            ['name' => 'Computer & IT'],
            ['name' => 'Cosmetology'],
            ['name' => 'Dyeing & Bleaching'],
            ['name' => 'Economics'],
            ['name' => 'English Language'],
            ['name' => 'Financial Accounting'],
            ['name' => 'Fisheries'],
            ['name' => 'Food & Nutrition'],
            ['name' => 'French'],
            ['name' => 'Further Mathematics'],
            ['name' => 'Garment Making'],
            ['name' => 'General Mathematics'],
            ['name' => 'Geography'],
            ['name' => 'Government'],
            ['name' => 'Hausa'],
            ['name' => 'Health Education'],
            ['name' => 'History'],
            ['name' => 'Home Management'],
            ['name' => 'Igbo'],
            ['name' => 'Insurance'],
            ['name' => 'Islamic Studies'],
            ['name' => 'Literature in English'],
            ['name' => 'Marketing'],
            ['name' => 'Music'],
            ['name' => 'Office Practice'],
            ['name' => 'Painting & Decorating'],
            ['name' => 'Photography'],
            ['name' => 'Physical Education'],
            ['name' => 'Physics'],
            ['name' => 'Salesmanship'],
            ['name' => 'Stenography'],
            ['name' => 'Store Keeping'],
            ['name' => 'Store Management'],
            ['name' => 'Tourism'],
            ['name' => 'Type Writing'],
            ['name' => 'Visual Art'],
            ['name' => 'Yoruba'],
        ];

        // Insert data into the subjects table
        DB::table('subjects')->insert($subjects);
    }
}