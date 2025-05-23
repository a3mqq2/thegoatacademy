<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('exams:whatsapp-notify')->everyMinute();
        $schedule->command('courses:send-daily-image')->dailyAt('11:00');
        $schedule->command('exams:update-overdue')->dailyAt('02:00');
        $schedule->command('attendance:whatsapp-remind')->everyMinute();
        $schedule->command('courses:update-status')->daily();
        $schedule->command('progress-tests:whatsapp-notify')->everyMinute();
        $schedule->command('courseschedules:update-statuses')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
