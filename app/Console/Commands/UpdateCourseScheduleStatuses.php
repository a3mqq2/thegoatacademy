<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CourseSchedule;
use App\Models\Course;
use Carbon\Carbon;
use App\Services\WaapiService;

class UpdateCourseScheduleStatuses extends Command
{
    protected $signature = 'courseschedules:update-statuses';
    protected $description = 'Update course schedule statuses and pause courses exceeding allowed instructor absences';

    public function handle(WaapiService $waapi)
    {
        $now = Carbon::now();

        // 1. Mark as done if attendance taken and time passed, and course is ongoing
        CourseSchedule::whereNotNull('attendance_taken_at')
            ->where('status', '!=', 'done')
            ->whereHas('course', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->update(['status' => 'done']);

        // 2. Mark as absent if close_at is passed and no attendance, and course is ongoing
        CourseSchedule::whereNull('attendance_taken_at')
            ->whereNotNull('close_at')
            ->where('close_at', '<=', $now)
            ->where('status', '!=', 'absent')
            ->whereHas('course', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->update(['status' => 'absent']);

        // 3. Pause courses and alert instructors (only ongoing)
        $courses = Course::where('status', 'ongoing')
            ->with([
                'schedules' => function ($q) {
                    $q->where('status', 'absent');
                },
                'instructor'
            ])->get();

        foreach ($courses as $course) {
            $allowed = (int) ($course->allowed_abcences_instructor ?? 0);
            $alert   = (int) ($course->alert_abcences_instructor ?? 0);
            $absents = $course->schedules->count();

            // 3.1 Pause course if absents exceed allowed
            if ($allowed > 0 && $absents > $allowed && $course->status !== 'paused') {
                $course->update(['status' => 'paused']);
            }

            // 3.2 Alert instructor if absents exceed alert threshold
            if (
                $alert > 0 &&
                $absents >= $alert &&
                $absents < $allowed &&
                $course->instructor &&
                $course->instructor->phone
            ) {
                $phone = formatLibyanPhone($course->instructor->phone);
                $msg   = "⚠️ *Instructor Absence Alert*\n"
                       . "You have been marked absent *$absents* times in course #{$course->id}.\n"
                       . "The allowed limit is *$allowed* absences.\n"
                       . "Please stay committed to avoid course suspension.";

                // $waapi->sendText($phone, $msg);
            }
        }

        $this->info('Schedule statuses and instructor alerts updated successfully.');
    }
}
