<?php

namespace App\Http\Controllers\Instructor;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\CourseSchedule;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $ongoing_courses = auth()->user()->courses()->where('status', 'ongoing')->count();

        // All schedules of ongoing courses where date is today and attendance is not taken
        $schedules = CourseSchedule::whereDate('date', now()->toDateString())
            ->whereHas('course', function($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->whereNull('attendance_taken_at')
            ->with('course')
            ->get();

        // Get the previous week's range from Saturday to (Saturday + 6 days)
        $start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->subWeek();
        $end   = (clone $start)->addDays(6);

        $previousWeekSchedules = CourseSchedule::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNull('attendance_taken_at')
            ->whereHas('course', function($q) {
                $q->where('instructor_id', auth()->id());
            })
            ->with('course')
            ->get();

        // Determine progress test date if today is Thursday or Friday.
        // According to policy, if today is Friday, record as Thursday.
        $today = Carbon::now();
        $progressTestDate = $today;
        // if ($today->isThursday()) {
        //     $progressTestDate = $today->toDateString();
        // } elseif ($today->isFriday()) {
        //     // For Friday, set progress test date as Thursday.
        //     $progressTestDate = $today->subDay()->toDateString();
        // }

        // Fetch all courses that need a progress test.
        // That is, ongoing courses that do NOT have a progress test on the computed Thursday.
        $coursesNeedsProgressTest = collect();
        if ($progressTestDate) {
            $coursesNeedsProgressTest = auth()->user()->courses()
                ->where('status', 'ongoing')
                ->whereDoesntHave('progressTests', function ($q) use ($progressTestDate) {
                    $q->where('date', $progressTestDate);
                })
                ->get();
        }

        return view('instructor.dashboard', compact(
            'ongoing_courses',
            'schedules',
            'previousWeekSchedules',
            'coursesNeedsProgressTest'
        ));
    }
}
