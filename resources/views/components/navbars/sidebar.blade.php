@php
    $user = auth()->user();
    if (!$user) {
        return;
    }

    $currentRoute = Route::currentRouteName() ?? '';
    $path = request()->path();

    // 1. Determine active sidebar by current route / URL path prefix
    if ((str_starts_with($currentRoute, 'exam-officer.') || str_starts_with($path, 'exam-officer')) && $user->canActAsExamOfficer()) {
        $activeSidebar = 'exam-officer';
    } elseif ((str_starts_with($currentRoute, 'coordinator.') || str_starts_with($path, 'coordinator')) && $user->canActAsCoordinator()) {
        $activeSidebar = 'coordinator';
    } elseif ((str_starts_with($currentRoute, 'lecturer.') || str_starts_with($path, 'lecturer')) && $user->canActAsLecturer()) {
        $activeSidebar = 'lecturer';
    } elseif ((str_starts_with($currentRoute, 'hod.') || str_starts_with($path, 'hod')) && $user->canActAsHod()) {
        $activeSidebar = 'hod';
    } elseif ((str_starts_with($currentRoute, 'cit.') || str_starts_with($path, 'cit')) && $user->canActAsCit()) {
        $activeSidebar = 'cit';
    } elseif ((str_starts_with($currentRoute, 'admin.') || str_starts_with($path, 'admin')) && $user->canActAsAdmin()) {
        $activeSidebar = 'admin';
    } elseif (str_starts_with($currentRoute, 'student.') || str_starts_with($path, 'student') || $user->isStudent()) {
        $activeSidebar = 'student';
    } elseif (str_starts_with($currentRoute, 'applicant.') || str_starts_with($path, 'applicant') || $user->isApplicant()) {
        $activeSidebar = 'applicant';
    } else {
        // 2. Check session active role
        $sessionRole = session('active_role');
        if ($sessionRole === 'exam_officer' && $user->canActAsExamOfficer()) {
            $activeSidebar = 'exam-officer';
        } elseif ($sessionRole === 'coordinator' && $user->canActAsCoordinator()) {
            $activeSidebar = 'coordinator';
        } elseif ($sessionRole === 'lecturer' && $user->canActAsLecturer()) {
            $activeSidebar = 'lecturer';
        } elseif ($sessionRole === 'hod' && $user->canActAsHod()) {
            $activeSidebar = 'hod';
        } elseif ($sessionRole === 'cit' && $user->canActAsCit()) {
            $activeSidebar = 'cit';
        } elseif ($sessionRole === 'admin' && $user->canActAsAdmin()) {
            $activeSidebar = 'admin';
        } elseif ($user->isExamOfficer() || $user->canActAsExamOfficer()) {
            $activeSidebar = 'exam-officer';
        } elseif ($user->isCoordinator() || $user->canActAsCoordinator()) {
            $activeSidebar = 'coordinator';
        } elseif ($user->isLecturer() || $user->canActAsLecturer()) {
            $activeSidebar = 'lecturer';
        } elseif ($user->isHod() || $user->canActAsHod()) {
            $activeSidebar = 'hod';
        } elseif ($user->isCit() || $user->canActAsCit()) {
            $activeSidebar = 'cit';
        } elseif ($user->isAdmin() || $user->canActAsAdmin()) {
            $activeSidebar = 'admin';
        } elseif ($user->isStudent()) {
            $activeSidebar = 'student';
        } elseif ($user->isApplicant()) {
            $activeSidebar = 'applicant';
        } else {
            $activeSidebar = 'admin';
        }
    }
@endphp

@switch($activeSidebar)
    @case('exam-officer')
        @include('components.navbars.exam-officer-sidebar')
        @break
    @case('coordinator')
        @include('components.navbars.coordinator-sidebar')
        @break
    @case('lecturer')
        @include('components.navbars.lecturer-sidebar')
        @break
    @case('hod')
        @include('components.navbars.hod-sidebar')
        @break
    @case('cit')
        @include('components.navbars.cit-sidebar')
        @break
    @case('admin')
        @include('components.navbars.admin-sidebar')
        @break
    @case('student')
        @include('components.navbars.student-sidebar')
        @break
    @case('applicant')
        @include('components.navbars.applicant-sidebar')
        @break
@endswitch