<?php
// app/Console/Commands/NotifyExamsCommand.php
namespace App\Console\Commands;

use App\Models\Exam;
use App\Services\WaapiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyExamsCommand extends Command
{
    protected $signature = 'exams:whatsapp-notify';
    protected $description = 'ارسال تنبيه قبل 5 دقائق من وقت أي امتحان';

    public function handle(WaapiService $waapi): int
    {
        $targetTime = Carbon::now()->addMinutes(5);
        $targetHour = $targetTime->format('H');
        $targetMinute = $targetTime->format('i');

        Log::info('Exam Notification', [
            'target_time' => $targetHour . ':' . $targetMinute,
            'date' => $targetTime->toDateString(),
        ]);

        $exams = Exam::with(['course.courseType', 'examiner'])
            ->whereDate('exam_date', $targetTime->toDateString())
            ->whereRaw('HOUR(time) = ?', [$targetHour])
            ->whereRaw('MINUTE(time) = ?', [$targetMinute])
            ->get();

        Log::info('Exams found', [
            'count' => $exams->count(),
            'exam_ids' => $exams->pluck('id')->toArray(),
        ]);

        foreach ($exams as $exam) {
            $msg = "🔔 *تنبيه إداري مهم*\n"
                . "لديك  امتحان سيبدأ خلال *5 دقائق*.\n\n"
                . "🆔 *رقم الكورس:* {$exam->course->id}\n"
                . "👤 *الممتحِن:* " . ($exam->examiner->name ?? 'غير محدد') . "\n"
                . "🎯 *المستوى الدراسي:* {$exam->course->courseType->name}\n"
                . "📚 *نوع الامتحان:* {$exam->exam_type}\n"
                . "🔗 *رابط القروب:* {$exam->course->whatsapp_group_link}\n\n"
                . "يرجى متابعة الانطلاق والتأكد من الحضور في الوقت المحدد. ✅";

                $waapi->sendText("218934868599@c.us", $msg);
            $waapi->sendText(formatLibyanPhone($exam->examiner->phone), $msg);
        }

        return self::SUCCESS;
    }
}
