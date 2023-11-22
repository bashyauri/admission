<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'surname' => 'Umar',
            'firstname' => 'Bashar',
            'm_name' => '',
            'programme_id' => 1,
            'email' => 'basharu83@gmail.com',
            'password' => Hash::make('secret'),
            'vpassword' => 'secret',
            'phone' => '08038272560'
        ]);

        User::factory()->create([
            'surname' => 'Umar',
            'firstname' => 'Bashar',
            'm_name' => '',
            'programme_id' => 2,
            'email' => 'basharu84@gmail.com',
            'password' => Hash::make('secret'),
            'vpassword' => 'secret',
            'phone' => '08038272561'
        ]);

        User::factory()->create([
            'surname' => 'Umar',
            'firstname' => 'Bashar',
            'm_name' => '',
            'programme_id' => 3,
            'email' => 'basharu85@gmail.com',
            'password' => Hash::make('secret'),
            'vpassword' => 'secret',
            'phone' => '08038272562'
        ]);
    }
}
