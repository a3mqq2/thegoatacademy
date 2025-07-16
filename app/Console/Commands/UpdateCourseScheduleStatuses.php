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
        $cutoffDate = Carbon::create(2025, 6, 10, 23, 59, 59);

        CourseSchedule::whereNotNull('attendance_taken_at')
            ->where('status', '!=', 'done')
            ->where('date', '>', $cutoffDate)
            ->whereHas('course', function ($q) {
                $q->where('status', 'ongoing');
            })
            ->update(['status' => 'done']);

        $schedules = CourseSchedule::whereNull('attendance_taken_at')
            ->whereNotNull('close_at')
            ->where('close_at', '<=', $now)
            ->where('status', '!=', 'absent')
            ->whereHas('course', function ($q) {
                $q->where('status', 'ongoing');
            })->update(['status' => 'absent']);


        $courses = Course::where('status', 'ongoing')
            ->with([
                'schedules' => function ($q) use ($cutoffDate) {
                    $q->where('status', 'absent')
                      ->where('date', '>', $cutoffDate);
                },
                'instructor'
            ])->get();

        foreach ($courses as $course) {
            $allowed = (int) ($course->allowed_abcences_instructor ?? 0);
            $alert   = (int) ($course->alert_abcences_instructor ?? 0);
            $absents = $course->schedules->count();

            if ($allowed > 0 && $absents > $allowed && $course->status != 'paused') {
                $course->update(['status' => 'paused']);
            }

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
            }
        }

        $this->info('Schedule statuses and instructor alerts updated successfully.');
    }
}