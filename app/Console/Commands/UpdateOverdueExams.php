<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Exam;
use Carbon\Carbon;

class UpdateOverdueExams extends Command
{
    protected $signature = 'exams:update-overdue';
    protected $description = 'Update exams to overdue if their date is within the next two days and still assigned.';

    public function handle()
    {
        $updated = Exam::where('status', 'assigned')
            ->whereDate('exam_date', '<=', Carbon::now()->addDays(2))
            ->update(['status' => 'overdue']);

        $this->info("Exam statuses updated successfully. Total: {$updated}");
        
        return 0;
    }
}
