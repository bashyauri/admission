<?php

namespace Database\Seeders;

use App\Models\HodUser;
use App\Models\User;
use App\Models\UserCapability;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserCapabilitiesSeeder extends Seeder
{
    /**
     * Seed test capabilities for verification.
     *
     * This seeder assigns 'lecturer' and 'exam_officer' capabilities to the first
     * HOD user found, enabling the role-switcher sidebar to appear for testing.
     * It also creates a standalone Lecturer and Exam Officer account if they do
     * not already exist.
     */
    public function run(): void
    {
        // --- 1. Grant capabilities to the first HOD ---
        $hod = User::where('role', 'hod')->first();

        if ($hod) {
            $departmentId = HodUser::where('user_id', $hod->id)->value('department_id');

            UserCapability::firstOrCreate(
                [
                    'user_id'    => $hod->id,
                    'capability' => 'lecturer',
                ],
                [
                    'department_id' => $departmentId,
                    'is_active'     => true,
                    'granted_at'    => now(),
                    'reason'        => 'Seeder: HOD also acts as Lecturer for their department',
                ]
            );

            UserCapability::firstOrCreate(
                [
                    'user_id'    => $hod->id,
                    'capability' => 'exam_officer',
                ],
                [
                    'department_id' => $departmentId,
                    'is_active'     => true,
                    'granted_at'    => now(),
                    'reason'        => 'Seeder: HOD also acts as Exam Officer for their department',
                ]
            );

            $this->command->info("✓ Granted 'lecturer' and 'exam_officer' capabilities to HOD: {$hod->email}");
        } else {
            $this->command->warn('⚠ No HOD user found. Skipping HOD capability seeding.');
        }

        // --- 2. Create a standalone Lecturer test account ---
        $lecturer = User::firstOrCreate(
            ['email' => 'test.lecturer@institution.edu.ng'],
            [
                'surname'      => 'Test',
                'firstname'    => 'Lecturer',
                'm_name'       => '',
                'role'         => 'lecturer',
                'programme_id' => 1,
                'password'     => Hash::make('password'),
                'vpassword'    => 'password',
                'phone'        => '08000000001',
            ]
        );
        $this->command->info("✓ Lecturer test account ready: {$lecturer->email} (password: password)");

        // --- 3. Create a standalone Exam Officer test account ---
        $examOfficer = User::firstOrCreate(
            ['email' => 'test.examofficer@institution.edu.ng'],
            [
                'surname'      => 'Test',
                'firstname'    => 'ExamOfficer',
                'm_name'       => '',
                'role'         => 'exam_officer',
                'programme_id' => 1,
                'password'     => Hash::make('password'),
                'vpassword'    => 'password',
                'phone'        => '08000000002',
            ]
        );
        $this->command->info("✓ Exam Officer test account ready: {$examOfficer->email} (password: password)");
    }
}
