<?php

namespace App\Console\Commands;

use App\Models\CourseSchedule;
use App\Models\Setting;
use App\Services\WaapiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyLateAttendanceCommand extends Command
{
    /** اسم الأمر الذى سيستدعى فى الـ cron */
    protected $signature   = 'attendance:whatsapp-remind';

    protected $description = 'تنبيه المدرّسين عبر واتساب عندما تمضى نصف فترة السماح ولم يُسجَّل الحضور';

    public function handle(WaapiService $waapi): int
    {
        /* عدد الساعات المسموح بها بعد نهاية المحاضرة لتسجيل الحضور */
        $limitHrs = (int) Setting::where('key','Updating the students’ Attendance after the class.')
                                 ->value('value');

        if ($limitHrs === 0) {
            $this->warn('لم يتم ضبط قيمة (Updating the students’ Attendance after the class.) أو أنها = 0');
            return self::SUCCESS;
        }

        $halfWindow = $limitHrs / 2;
        $now        = Carbon::now();

        // فقط المحاضرات بتاريخ 2025-05-17 وما بعده
        $cutoffDate = Carbon::create(2025, 5, 17);

        /* كل المحاضرات التى لم يُسجَّل لها حضور بعد */
        $schedules  = CourseSchedule::with(['course.courseType','course.instructor'])
                      ->whereNull('attendance_taken_at')
                      ->get()
                      ->filter(function ($s) use ($now, $halfWindow, $limitHrs, $cutoffDate) {
                            // إذا كانت المحاضرة قبل 2025-05-17 نتجاهلها
                            $lectureDate = Carbon::parse($s->date);
                            if ($lectureDate->lt($cutoffDate)) {
                                return false;
                            }

                            /* نهاية المحاضرة */
                            $end = Carbon::parse("{$s->date} {$s->to_time}");

                            /* إن كان وقت النهاية أصغر من البداية ➜ عبور منتصف الليل */
                            if (Carbon::parse($s->to_time)
                                      ->lessThanOrEqualTo(Carbon::parse($s->from_time))
                            ) {
                                $end->addDay();
                            }

                            /* أرسل تنبيه إذا: الآن بين (النصف) و (النهاية + كامل الفترة) */
                            return $now->between(
                                        $end->copy()->addHours($halfWindow),
                                        $end->copy()->addHours($limitHrs)
                                   );
                      });

        Log::info('Late-attendance schedules', ['count' => $schedules->count()]);

        foreach ($schedules as $sch) {
            $course = $sch->course;
            $inst   = $course->instructor;

            if (! $inst || ! $inst->phone) {
                continue;
            }

            $msg = "🔔 *تنبيه تسجيل حضور*\n"
                 . "لم يتم تسجيل حضور المحاضرة التالية وقد تجاوزت نصف فترة السماح:\n\n"
                 . "🆔 *الكورس:* {$course->id}\n"
                 . "📚 *المادة:* {$course->courseType->name}\n"
                 . "📆 *التاريخ:* {$sch->date}\n"
                 . "⏰ *الوقت:* {$sch->from_time} – {$sch->to_time}\n\n"
                 . "يرجى تسجيل الحضور قبل انتهاء المهلة المحددة. ✅";

            $waapi->sendText(
                formatLibyanPhone($inst->phone),
                $msg
            );
        }

        return self::SUCCESS;
    }
}
