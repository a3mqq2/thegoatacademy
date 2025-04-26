<?php
// app/Console/Commands/NotifyExamsCommand.php
namespace App\Console\Commands;

use App\Models\Exam;
use App\Services\WaapiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyExamsCommand extends Command
{
    protected $signature = 'exams:whatsapp-notify';
    protected $description = 'ارسال تنبيه قبل 5 دقائق من وقت أي امتحان';

    public function handle(WaapiService $waapi): int
    {
        // وقت الآن + خمس دقائق بدقّة دقيقة
        $targetTime = Carbon::now()->addMinutes(5);
        \Log::info('Exam Notification', [
            'target_time' => $targetTime->format('H:i:s'),
            'date' => $targetTime->toDateString(),
        ]);
        Exam::with(['course.courseType','examiner'])
            ->whereDate('exam_date', $targetTime->toDateString())
            ->whereTime('time', $targetTime->format('H:i:s'))   // يطابق HH:MM
            ->each(function ($exam) use ($waapi) {

                $msg = "🔔 *تنبيه إداري مهم*\n"
                . "تم جدولة امتحان سيبدأ خلال *5 دقائق*.\n\n"
                . "🆔 *رقم الامتحان:* {$exam->id}\n"
                . "👤 *الممتحِن:* " . ($exam->examiner->name ?? 'غير محدد') . "\n"
                . "🎯 *المستوى الدراسي:* {$exam->course->courseType->name}\n"
                . "📚 *نوع الامتحان:* {$exam->exam_type}\n"
                . "🔗 *رابط القروب:* {$exam->course->group_link}\n\n"
                . "يرجى متابعة الانطلاق والتأكد من الحضور في الوقت المحدد. ✅";
           

                $waapi->sendText(env('EXAM_MANAGER_CHATID'), $msg);
            });

        return self::SUCCESS;
    }
}
