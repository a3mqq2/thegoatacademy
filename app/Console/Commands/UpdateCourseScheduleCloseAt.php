<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseSchedule;
use App\Models\Setting;
use Carbon\Carbon;

// SELECT course_id, COUNT(*) AS open_schedules_count FROM course_schedules WHERE close_at IS NULL GROUP BY course_id;

class UpdateCourseScheduleCloseAt extends Command
{
    protected $signature = 'course-schedule:update-close-at';
    protected $description = 'Re-calculate close_at for every CourseSchedule row';

    public function handle(): int
    {
        $hoursForClose = (int) Setting::where('key', 'Updating the students’ Attendance after the class.')
                                      ->value('value');

        CourseSchedule::chunkById(100, function ($rows) use ($hoursForClose) {
            foreach ($rows as $row) {
                $row->close_at = Carbon::createFromFormat('Y-m-d H:i', "{$row->date} {$row->to_time}")
                                        ->addHours($hoursForClose);
                $row->save();
            }
        });

        $this->info('All course_schedule.close_at values updated.');
        return Command::SUCCESS;
    }
}
