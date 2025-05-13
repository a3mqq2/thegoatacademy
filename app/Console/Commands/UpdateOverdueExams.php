<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Exam;
use App\Services\WaapiService;
use Illuminate\Console\Command;

class UpdateOverdueExams extends Command
{
    protected $signature = 'exams:update-overdue';
    protected $description = 'Update exams to overdue or revert back to assigned based on their exam dates.';

    public function handle()
    {
        $overdueExams = Exam::where('status', 'assigned')
            ->whereDate('exam_date', '<=', Carbon::now()->subDays(2))
            ->get();

        foreach($overdueExams as $exam)
        {
            $whatsapp_service = new WaapiService();
            $msg = 
            "🔔 *تنبيه إداري *\n"
            . "لديك امتحان مرت عليه 48 ساعة ولم تدخل النتائج.\n\n"
            . "🆔 *رقم الكورس:* {$exam->course->id}\n"
            . "👤 *الممتحِن:* " . ($exam->examiner->name ?? 'غير محدد') . "\n"
            . "📚 *نوع الامتحان:* {$exam->exam_type}\n"
            . "🔗 *رابط القروب:* {$exam->course->whatsapp_group_link}\n\n"
            . "يرجى الالتزام بإدخال النتائج في توقيتها  😊 ";

            $whatsapp_service->sendText(formatLibyanPhone($exam->examiner->phone), $msg);
        }

            $overdueExams->update(['status' => 'overdue']);

        $revertedAssigned = Exam::where('status', 'overdue')
            ->whereDate('exam_date', '>', Carbon::now())
            ->update(['status' => 'assigned']);

        $this->info("Exams reverted to assigned: {$revertedAssigned}");


        return 0;
    }
}
