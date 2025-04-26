<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('exams:whatsapp-notify')->everyMinute();
        $schedule->command('courses:send-daily-image')->dailyAt('11:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
