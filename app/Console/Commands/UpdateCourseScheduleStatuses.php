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

        // 1) إذا المدرس أخذ الحضور (attendance_taken_at != null)
        $schedulesWithAttendance = CourseSchedule::with(['attendances', 'course'])
            ->whereNotNull('attendance_taken_at')
            ->whereHas('course', fn($q) => $q->where('status', 'ongoing'))
            ->get();

        foreach ($schedulesWithAttendance as $schedule) {
            // هل يوجد طالب واحد على الأقل حاضر؟
            $hasPresent = $schedule->attendances()
                ->where('attendance', 'present')
                ->exists();

            $newStatus = $hasPresent ? 'done' : 'absent-S';

            if ($schedule->status !== $newStatus) {
                $schedule->update(['status' => $newStatus]);
            }
        }

        // 2) إذا المدرس لم يأخذ الحضور قبل انتهاء الموعد (attendance_taken_at == null)
        CourseSchedule::whereNull('attendance_taken_at')
            ->whereNotNull('close_at')
            ->where('close_at', '<=', $now)
            ->where('status', '!=', 'absent-T')
            ->whereHas('course', fn($q) => $q->where('status', 'ongoing'))
            ->update(['status' => 'absent-T']);

        // 3) معالجة إيقاف الدورات وتنبيهات الغياب للمُدرِّس
        $courses = Course::where('status', 'ongoing')
            ->with([
                'schedules' => fn($q) => $q->whereIn('status', ['absent-T', 'absent-S']),
                'instructor'
            ])
            ->get();

        foreach ($courses as $course) {
            $allowed = (int) ($course->allowed_abcences_instructor ?? 0);
            $alert   = (int) ($course->alert_abcences_instructor   ?? 0);
            $absents = $course->schedules
                ->whereIn('status', ['absent-T', 'absent-S'])
                ->count();

            // تجاوز الحد → إيقاف الدورة
            if ($allowed > 0 && $absents > $allowed && $course->status !== 'paused') {
                $course->update(['status' => 'paused']);
            }

            // تجاوز عتبة التنبيه دون الوصول للإيقاف → إرسال تنبيه
            if (
                $alert > 0 &&
                $absents >= $alert &&
                $absents < $allowed &&
                $course->instructor &&
                $course->instructor->phone
            ) {
                $phone = formatLibyanPhone($course->instructor->phone);
                $msg   = "⚠️ *Instructor Absence Alert*\n"
                       . "You have been marked absent *{$absents}* times in course #{$course->id}.\n"
                       . "Allowed limit is *{$allowed}* absences.\n"
                       . "Please stay committed to avoid suspension.";
                // $waapi->sendMessage($phone, $msg);
            }
        }

        $this->info('Schedule statuses and instructor alerts updated successfully.');
    }
}
