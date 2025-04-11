<?php

namespace App\Http\Controllers\ExamOfficer;

use App\Models\User;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Start with a base query on courses. (You may adjust this base as needed.)
        $coursesQuery = Course::query();

        // 1. Exam Status Filter
        if ($request->filled('status') && in_array($request->status, ['new', 'pending', 'completed', 'overdue'])) {
            $coursesQuery->whereHas('exams', function ($q) use ($request) {
                $q->where('status', $request->status);
            });
        } else {
            // Default to exams with status new or pending.
            $coursesQuery->whereHas('exams', function ($q) {
                $q->whereIn('status', ['new', 'pending']);
            });
        }

        // 2. Schedule Filter
        if ($request->filled('schedule') && in_array($request->schedule, ['daily', 'weekly', 'afterTwoDays'])) {
            $today = Carbon::today();
            if ($request->schedule == 'daily') {
                $coursesQuery->where(function ($q) use ($today) {
                    $q->whereDate('mid_exam_date', '=', $today)
                      ->orWhereDate('final_exam_date', '=', $today)
                      ->orWhereDate('pre_test_date', '=', $today);
                });
            } elseif ($request->schedule == 'weekly') {
                // Using Carbon's startOfWeek (Monday) and endOfWeek (Sunday).
                $monday = $today->copy()->startOfWeek();
                $sunday = $today->copy()->endOfWeek();
                $coursesQuery->where(function ($q) use ($monday, $sunday) {
                    $q->whereBetween('mid_exam_date', [$monday, $sunday])
                      ->orWhereBetween('pre_test_date', [$monday, $sunday])
                      ->orWhereBetween('final_exam_date', [$monday, $sunday]);
                });
            } elseif ($request->schedule == 'afterTwoDays') {
                $target = $today->copy()->addDays(2);
                $coursesQuery->where(function ($q) use ($target) {
                    $q->whereDate('mid_exam_date', '=', $target)
                      ->orWhereDate('pre_test_date', '=', $target)
                      ->orWhereDate('final_exam_date', '=', $target);
                });
            }
        }

        // 3. Advanced Filters
        if ($request->filled('instructor_id')) {
            $coursesQuery->where('instructor_id', $request->instructor_id);
        }
        if ($request->filled('examiner_id')) {
            $coursesQuery->whereHas('exams', function ($q) use ($request) {
                $q->where('examiner_id', $request->examiner_id);
            });
        }
        if ($request->filled('exam_date')) {
            $examDate = Carbon::parse($request->exam_date);
            $coursesQuery->where(function ($q) use ($examDate) {
                $q->whereDate('mid_exam_date', $examDate)
                  ->orWhereDate('pre_test_date', $examDate)
                  ->orWhereDate('final_exam_date', $examDate);
            });
        }
        if ($request->filled('course_type_id')) {
            $coursesQuery->where('course_type_id', $request->course_type_id);
        }

        // 4. If not an Exam Manager, restrict courses to exams where the examiner is the current user.
        if (!$user->permissions->contains('name', 'Exam Manager')) {
            $coursesQuery->whereHas('exams', function ($q) use ($user) {
                $q->where('examiner_id', $user->id);
            });
        }

        // Eager load related exams (filtering by status if given) and the course type.
        $courses = $coursesQuery->with([
            'exams' => function ($q) use ($request) {
                if ($request->filled('status') && in_array($request->status, ['new', 'pending', 'completed', 'overdue'])) {
                    $q->where('status', $request->status);
                }
            },
            'courseType'
        ])->orderByDesc('id')->get();

        $instructors = User::role('Instructor')->get();
        $examiners = User::role('Examiner')->get();
        $courseTypes = \App\Models\CourseType::all();


        if(request('print'))
        {
            // redirect to print view
            return view('exam_officer.courses.print', compact('courses', 'instructors', 'examiners', 'courseTypes'));
        }

        return view('exam_officer.courses.index', compact('courses', 'instructors', 'examiners', 'courseTypes'));
    }


    public function show(Course $course)
    {
        $course->load(['exams', 'courseType']);
        $examiners = User::role('Examiner')->get();
        return view('exam_officer.courses.show', compact('course','examiners'));
    }
}
