<?php
// app/Console/Commands/RecalculateProgressTestWindows.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProgressTest;
use App\Models\Setting;
use Carbon\Carbon;

class RecalculateProgressTestWindows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'progress-tests:recalculate-windows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute close_at and will_alert_at for all existing progress tests based on current settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // جلب القيم من جدول الإعدادات
        $updateWindow = (int) Setting::where('key', 'Allow updating progress tests after class end time (hours)')
                                     ->value('value');
        $alertDelay   = (int) Setting::where('key', 'Notify instructor after update grace period (hours)')
                                     ->value('value');

        $this->info("Using update window of {$updateWindow} hours and alert delay of {$alertDelay} hours");

        $tests = ProgressTest::all();
        $bar   = $this->output->createProgressBar($tests->count());
        $bar->start();

        foreach ($tests as $test) {
            // بناء التاريخ+الوقت الأصلي للامتحان
            $start = Carbon::parse("{$test->date} {$test->time}");

            // حساب close_at و will_alert_at
            $closeAt     = $start->copy()->addHours($updateWindow);
            $willAlertAt = $closeAt->copy()->addHours($alertDelay);

            // تحديث السجل
            $test->update([
                'close_at'      => $closeAt->toDateTimeString(),
                'will_alert_at' => $willAlertAt->toDateTimeString(),
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nAll progress tests have been updated.");

        return self::SUCCESS;
    }
}
