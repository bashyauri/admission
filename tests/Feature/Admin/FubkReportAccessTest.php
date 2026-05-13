<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class FubkReportAccessTest extends TestCase
{
    public function test_admin_can_access_fubk_report_page(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'id' => (string) Str::uuid(),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.fubk'));

        $response->assertOk();
        $response->assertSee('FUBK Reports');
    }

    public function test_non_admin_is_redirected_away_from_fubk_report_page(): void
    {
        /** @var User $student */
        $student = User::factory()->create([
            'id' => (string) Str::uuid(),
            'role' => 'student',
        ]);

        $response = $this->actingAs($student)->get(route('admin.fubk'));

        $response->assertRedirect(route('student.dashboard'));
    }
}
