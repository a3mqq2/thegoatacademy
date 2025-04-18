<?php

namespace App\Http\Controllers\ExamOfficer;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\CourseType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CourseController extends Controller
{
   
    public function index(Request $request)
    {
        $user = auth()->user();
    
        $coursesQuery = Course::query();
    
        // 1. Exam Status Filter (unchanged)…
        if ($request->filled('status') && in_array($request->status, ['new','pending','completed','overdue'])) {
            $coursesQuery->whereHas('exams', fn($q) => $q->where('status', $request->status));
        } else {
            $coursesQuery->whereHas('exams', fn($q) => $q->whereIn('status', ['new','pending']));
        }
    
        // 2. Schedule Filter (unchanged)…
        $schedule = $request->schedule;
        $today = Carbon::today();
        if ($schedule === 'daily') {
            $coursesQuery->where(fn($q) => $q
                ->whereDate('pre_test_date', $today)
                ->orWhereDate('mid_exam_date', $today)
                ->orWhereDate('final_exam_date', $today)
            );
        } elseif ($schedule === 'weekly') {
            $startOfWeek = $today->copy()->startOfWeek(Carbon::SATURDAY);
            $endOfWeek   = $today->copy()->endOfWeek(Carbon::FRIDAY);
            $coursesQuery->where(fn($q) => $q
                ->whereBetween('pre_test_date', [$startOfWeek, $endOfWeek])
                ->orWhereBetween('mid_exam_date', [$startOfWeek, $endOfWeek])
                ->orWhereBetween('final_exam_date', [$startOfWeek, $endOfWeek])
            );
        } elseif ($schedule === 'afterTwoDays') {
            $afterTwo = $today->copy()->addDays(2);
            $coursesQuery->where(fn($q) => $q
                ->whereDate('pre_test_date', $afterTwo)
                ->orWhereDate('mid_exam_date', $afterTwo)
                ->orWhereDate('final_exam_date', $afterTwo)
            );
        }
    
        // 3. Advanced Filters (unchanged)…
        if ($request->filled('instructor_id')) {
            $coursesQuery->where('instructor_id', $request->instructor_id);
        }
        if ($request->filled('examiner_id')) {
            $coursesQuery->whereHas('exams', fn($q) => $q->where('examiner_id', $request->examiner_id));
        }
        if ($request->filled('exam_date')) {
            $examDate = Carbon::parse($request->exam_date);
            $coursesQuery->where(fn($q) => $q
                ->whereDate('pre_test_date', $examDate)
                ->orWhereDate('mid_exam_date', $examDate)
                ->orWhereDate('final_exam_date', $examDate)
            );
        }
        if ($request->filled('course_type_id')) {
            $coursesQuery->where('course_type_id', $request->course_type_id);
        }
    
        // 4. Restrict by examiner if not Exam Manager (unchanged)…
        if (! $user->permissions->contains('name','Exam Manager')) {
            $coursesQuery->whereHas('exams', fn($q) => $q->where('examiner_id',$user->id));
        }
    
        $courses = $coursesQuery
            ->with([
              'exams' => fn($q) => $request->filled('status')
                ? $q->where('status',$request->status)
                : $q,
              'courseType'
            ])
            ->orderByDesc('id')
            ->paginate(15);
    
        $instructors = User::role('Instructor')->get();
        $examiners   = User::role('Examiner')->get();
        $courseTypes = CourseType::all();
    
        // compute extra dates for “after a day” and pass everything to the view
        $afterOne = $today->copy()->addDay();
    
        if(request('print'))
        {
            // redirect to print view
            return view('exam_officer.courses.print', compact('courses', 'instructors', 'examiners', 'courseTypes'));
        }
        
        return view('exam_officer.courses.index', compact(
          'courses','instructors','examiners','courseTypes',
          'schedule','today','afterOne'
        ));
    }
    


    public function show(Course $course)
    {
        $course->load(['exams', 'courseType']);
        $examiners = User::role('Examiner')->get();
        return view('exam_officer.courses.show', compact('course','examiners'));
    }
}
