<?php

namespace App\Console;

use Illuminate\Support\Facades\App;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('report:purge')
                 ->everyMinute()
                 ->runInBackground();

        $schedule->command('trackings:purge')
                 ->everyMinute()
                 ->runInBackground();

        if (App::environment('production')) {
            // Run at 00:00 AM on weekdays (MON->FRI)
            $schedule->command('trackings:scan')
                     ->cron('0 0 * * 1-5')
                     ->runInBackground();
            // Run on 1st & 15th of every month at 00:00 AM
            $schedule->command('universalSSLVerification:check')
                     ->cron('0 0 1,15 * *')
                     ->runInBackground();
            // Run at 18:00 on weekdays (MON->FRI)
            $schedule->command('pingdom:scanForNew')
                     ->cron('0 18 * * 1-5')
                     ->runInBackground();
            // Every 15 minutes
            $schedule->command('cloudflare:scanForIPChangeOnJDCloud')
                     ->everyFifteenMinutes()
                     ->runInBackground();
            // Twice a day 08:00 AM & )5:00 PM
            $schedule->command('cloudflare:scanForChinaNetworkZones')
                     ->twiceDaily(8, 17)
                     ->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     *
     * @return \DateTimeZone|string|null
     */
    protected function scheduleTimezone()
    {
        return config('app.timezone');
    }
}
