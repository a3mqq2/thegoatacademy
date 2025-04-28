<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use Carbon\Carbon;

class UpdateOverdueExams extends Command
{
    protected $signature = 'exams:update-overdue';
    protected $description = 'Update exams to overdue or revert back to assigned based on their exam dates.';

    public function handle()
    {
        // ✅ 1. اجعل أي امتحان assigned وتأخر يومين أو أكثر → overdue
        $overdueUpdated = Exam::where('status', 'assigned')
            ->whereDate('exam_date', '<=', Carbon::now()->subDays(2))
            ->update(['status' => 'overdue']);

        // ✅ 2. ولو أي امتحان overdue وتاريخه قادم → رجعه assigned
        $revertedAssigned = Exam::where('status', 'overdue')
            ->whereDate('exam_date', '>', Carbon::now())
            ->update(['status' => 'assigned']);

        $this->info("Overdue exams updated: {$overdueUpdated}");
        $this->info("Exams reverted to assigned: {$revertedAssigned}");

        return 0;
    }
}
