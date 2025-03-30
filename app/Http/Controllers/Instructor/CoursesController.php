<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            "status" => "required|in:upcoming,ongoing",
        ]);

        $courses = auth()->user()->courses()->where('status', $request->status)->paginate(20);
        return view('instructor.courses.index', compact('courses'));
    }


    public function show($course)
    {
        $course = auth()->user()->courses()->findOrFail($course);
        $logs = $course->logs()->get();
        $schedules = $course->schedules()->get();
        return view('instructor.courses.show', compact('course','logs'));
    }

    public function take_attendance($course, $schedule)
    {
        $course = auth()->user()->courses()->findOrFail($course);
        return view('instructor.courses.take_attendance', compact('course','schedule'));
    }
}
