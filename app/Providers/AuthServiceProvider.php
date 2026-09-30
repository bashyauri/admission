<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Models\ProposedCourse;
use Illuminate\Support\Facades\Gate;
use App\Policies\ProposedCoursePolicy;
use App\Policies\StudentStatusPolicy;
use App\Policies\DisciplinaryActionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        ProposedCourse::class => ProposedCoursePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $studentStatusPolicy = app(StudentStatusPolicy::class);
        $disciplinaryActionPolicy = app(DisciplinaryActionPolicy::class);
        Gate::define('disciplinary-actions.manage', fn (User $actor, User $student) => $disciplinaryActionPolicy->manage($actor, $student));
        foreach ([
            'student-status.view-any' => 'viewAny',
            'student-status.view' => 'view',
            'student-status.view-audit' => 'viewAudit',
            'student-status.recommend' => 'recommend',
            'student-status.submit-for-senate' => 'submitForSenate',
            'student-status.decide-senate' => 'decideSenate',
            'student-status.process-voluntary' => 'processVoluntary',
            'student-status.process-medical' => 'processMedical',
            'student-status.process-disciplinary' => 'processDisciplinary',
            'student-status.request-reinstatement' => 'requestReinstatement',
            'student-status.review-department-reinstatement' => 'reviewDepartmentReinstatement',
            'student-status.review-faculty-reinstatement' => 'reviewFacultyReinstatement',
        ] as $ability => $method) {
            Gate::define($ability, fn (...$arguments) => $studentStatusPolicy->{$method}(...$arguments));
        }
    }
}
