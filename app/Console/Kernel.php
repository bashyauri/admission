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
        $schedule->command(
        'queue:work --queue=reports
         --stop-when-empty
         --tries=1
         --timeout=600
         --memory=512
         --sleep=3
         --max-jobs=20'
)
        ->everyMinute()
        ->withoutOverlapping();

        // 3. Default/remita queue worker
  $schedule->command(
    'queue:work --queue=default,remita 
     --stop-when-empty 
     --tries=3 
     --timeout=120 
     --memory=192 
     --sleep=5 
     --max-jobs=80'
)
    ->everyMinute()
    ->withoutOverlapping();

    // 4. Daily queue restart (helps after deployments or code changes)
    $schedule->command('queue:restart')
        ->dailyAt('03:00');

    // Your existing demo logic (unchanged)
    $hour = config('app.hour');
    $min = config('app.min');
    $scheduledInterval = $hour !== ''
        ? (($min !== '' && $min != 0) ? $min . ' */' . $hour . ' * * *' : '0 */' . $hour . ' * * *')
        : '*/' . $min . ' * * * *';

    if (env('IS_DEMO')) {
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
