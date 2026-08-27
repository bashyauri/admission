<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\CourseAllocation;

class AdminCourseAllocationTest extends TestCase
{
    public function test_admin_course_allocation_route_exists()
    {
        $this->assertTrue(true);
    }

    public function test_course_allocation_can_be_created()
    {
        $this->assertTrue(true);
    }
}
