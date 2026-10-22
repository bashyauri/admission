<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
   protected function schedule(Schedule $schedule)
{
    // 1. Remita payment status checker (runs every hour)
    $schedule->command('remita:check-payments --chunk=50 --max=100')
        ->hourly()
        ->withoutOverlapping(60);

        // 2. Dedicated reports queue worker for heavy PDF jobs
        $schedule->command('queue:work --queue=reports --stop-when-empty --tries=1 --timeout=600 --memory=512 --sleep=3 --max-jobs=20')
            ->everyMinute()
            ->withoutOverlapping();

        // 3. Dedicated worker for result workflow notification emails
        $schedule->command('queue:work database --queue=notifications --stop-when-empty --tries=3 --timeout=120 --memory=256 --sleep=2 --max-jobs=50')
            ->everyMinute()
            ->withoutOverlapping();

        // 4. Default/remita queue worker
        $schedule->command('queue:work --queue=default,remita --stop-when-empty --tries=3 --timeout=120 --memory=512 --sleep=5 --max-jobs=50')
            ->everyMinute()
            ->withoutOverlapping();

    // 5. Daily queue restart (helps after deployments or code changes)
    $schedule->command('queue:restart')
        ->dailyAt('03:00');

    // Always keep an encrypted local database archive; sync it and uploads when rclone is configured.
    if (filled(config('backup.backup.password'))) {
        $schedule->command('backup:sync-google')
            ->dailyAt('01:00')
            ->withoutOverlapping(1440);

        $schedule->command('backup:clean')
            ->dailyAt('02:00')
            ->withoutOverlapping(60);

        $schedule->command('backup:monitor')
            ->dailyAt('02:30')
            ->withoutOverlapping(60);
    }

    // Your existing demo logic (unchanged)
    $hour = config('app.hour');
    $min = config('app.min');
    $scheduledInterval = $hour !== ''
        ? (($min !== '' && $min != 0) ? $min . ' */' . $hour . ' * * *' : '0 */' . $hour . ' * * *')
        : '*/' . $min . ' * * * *';

    if (app()->environment('local') && config('app.is_demo')) {
        $schedule->command('migrate:fresh --seed')->cron($scheduledInterval);
    }
}

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
