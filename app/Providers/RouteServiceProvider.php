<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard/analytics';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
            $this->mapAdminRoutes();
            $this->mapHodRoutes();
            $this->mapStudentRoutes();
            $this->mapCitRoutes();
            $this->mapCoordinatorRoutes();
        });
    }

    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::middleware(['web', 'auth', 'verified', 'role:admin'])
            ->prefix('admin')
            ->as('admin.')
            ->group(base_path('routes/admin.php'));
    }

    protected function mapHodRoutes()
    {
        Route::middleware(['web', 'auth', 'verified', 'role:hod'])
            ->prefix('hod')
            ->as('hod.')
            ->group(base_path('routes/hod.php'));
    }

    protected function mapStudentRoutes()
    {
        Route::middleware(['web', 'auth', 'verified', 'role:student'])
            ->prefix('student')
            ->as('student.')
            ->group(base_path('routes/student.php'));
    }
    protected function mapCitRoutes()
    {
        Route::middleware(['web', 'auth', 'verified', 'role:cit'])
            ->prefix('cit')
            ->as('cit.')
            ->group(base_path('routes/cit.php'));
    }
    protected function mapCoordinatorRoutes()
    {
        Route::middleware(['web', 'auth', 'verified', 'role:coordinator'])
            ->prefix('coordinator')
            ->as('coordinator.')
            ->group(base_path('routes/coordinator.php'));
    }


    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
