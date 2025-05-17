<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ProgressTest;
use App\Services\WaapiService;

class NotifyProgressTestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'progress-tests:whatsapp-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp alerts at the configured will_alert_at for progress tests';

    /**
     * Execute the console command.
     */
    public function handle(WaapiService $waapi): int
    {
        $now    = Carbon::now();
        $date   = $now->toDateString();
        $hour   = $now->format('H');
        $minute = $now->format('i');

        Log::info('Checking progress test notifications', [
            'date' => $date,
            'time' => "$hour:$minute",
        ]);

        $tests = ProgressTest::with(['course.courseType', 'course.instructor'])
            ->whereDate('will_alert_at', $date)
            ->whereRaw('HOUR(will_alert_at) = ?', [$hour])
            ->whereRaw('MINUTE(will_alert_at) = ?', [$minute])
            ->get();

        Log::info('Discovered progress tests', [
            'count' => $tests->count(),
            'ids'   => $tests->pluck('id')->toArray(),
        ]);

        foreach ($tests as $test) {
            $course     = $test->course;
            $instructor = $course->instructor;

            $msg  = "🔔 *Reminder: Progress Test Entry Window Opened*\n"
                  . "🆔 *Course ID:* {$course->id}\n"
                  . "📅 *Test Date:* {$test->date} (Week {$test->week})\n"
                  . "🎯 *Level:* {$course->courseType->name}\n"
                  . "🔗 *WhatsApp Group:* {$course->whatsapp_group_link}\n\n"
                  . "Please enter student scores now before the window closes. ✅";

            // if ($instructor && $instructor->phone) {
            //     $waapi->sendText(
            //         formatLibyanPhone($instructor->phone) . '@c.us',
            //         $msg
            //     );
            // }
        }

        return self::SUCCESS;
    }
}
