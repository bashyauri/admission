<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add missing roles and new result processing roles to users.role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role 
            ENUM('applicant', 'student', 'graduate', 'hod', 'admin', 
                 'cit', 'coordinator', 'idcard_officer', 'lecturer', 'exam_officer') 
            DEFAULT 'applicant'");
    }

    public function down(): void
    {
        // Safety rollback check: only rollback if no users are assigned the new roles
        DB::statement("ALTER TABLE users MODIFY COLUMN role 
            ENUM('applicant', 'student', 'graduate', 'hod', 'admin') 
            DEFAULT 'applicant'");
    }
};
