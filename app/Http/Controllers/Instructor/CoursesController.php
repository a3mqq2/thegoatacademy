<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            "status" => "required|in:completed,ongoing",
        ]);

        $courses = auth()->user()->courses()->where('status', $request->status)->paginate(20);
        return view('instructor.courses.index', compact('courses'));
    }


    public function show($course)
    {
        $course = Course::with([
            'schedules',
            'progressTests.progressTestStudents.student',
            'progressTests.progressTestStudents.grades.courseTypeSkill'
        ])->findOrFail($course);
        
        $logs = $course->logs()->get();
        $schedules = $course->schedules()->get();
        return view('instructor.courses.show', compact('course','logs'));
    }


    public function print($course)
    {
        $course = Course::with([
            'schedules',
            'progressTests.progressTestStudents.student',
            'progressTests.progressTestStudents.grades.courseTypeSkill'
        ])->findOrFail($course);
        
        $logs = $course->logs()->get();
        $schedules = $course->schedules()->get();
        return view('instructor.courses.print', compact('course','logs'));
    }



    public function take_attendance($course, $schedule)
    {
        $course = auth()->user()->courses()->findOrFail($course);
        return view('instructor.courses.take_attendance', compact('course','schedule'));
    }


/**
 * Show a student’s performance timeline for a given course.
 */
public function studentStats(\App\Models\Course $course, \App\Models\Student $student)
{
    // 1) تأكد أن الطالب مسجّل في هذا الكورس
    abort_unless(
        $course->students->contains($student),
        403,
        'Student is not enrolled in this course.'
    );

    // 2) جلب كل البيانات دفعة واحدة
    $course->load([
        'courseType.skills',
        'exams.examStudents.grades',
        'progressTests.progressTestStudents.grades',
    ]);

    $ct       = $course->courseType;
    $timeline = collect();

    // دالة مساعدة لحساب النسبة المئوية لكل امتحان (pre, mid, final)
    $computeExam = function(string $examType) use ($course, $student, $ct) {
        $exam = $course->exams->first(fn($e) => $e->exam_type === $examType);
        if (! $exam) return null;

        // سجّل الطالب في هذا الامتحان
        $rec = $exam->examStudents->firstWhere('student_id', $student->id);
        if (! $rec || $rec->grades->isEmpty()) {
            return null;
        }

        // مجموع الدرجات المحققة
        $earned = $rec->grades->sum('grade');
        // مجموع الدرجات القصوى المحددة في pivot
        $pivotField = match($examType) {
            'mid'   => 'mid_max',
            default => 'final_max',  // pre & final
        };
        // في حالة الـ pre نستخدم pivot.progress_test_max
        if ($examType === 'pre') {
            $pivotField = 'progress_test_max';
        }

        $maxSum = $ct->skills->sum(fn($s) => $s->pivot->{$pivotField});
        if ($maxSum == 0) return null;

        return round($earned / $maxSum * 100, 1);
    };

    // 3) Pre-Test
    if ($course->exams->contains(fn($e) => $e->exam_type === 'pre')) {
        $timeline->push([
            'label' => 'Pre-Test',
            'score' => $computeExam('pre'),
        ]);
    }

    // 4) Progress Tests (weekly)
    foreach ($course->progressTests->sortBy('week') as $pt) {
        $rec    = $pt->progressTestStudents
                     ->firstWhere('student_id', $student->id);
        $earned = $rec?->grades->sum('progress_test_grade') ?? 0;
        $max    = $rec?->grades->sum('max_grade') ?? 0;
        $pct    = $max ? round($earned / $max * 100, 1) : null;

        $timeline->push([
            'label' => "Progress W{$pt->week}",
            'score' => $pct,
        ]);
    }

    // 5) Mid-Exam
    if ($course->exams->contains(fn($e) => $e->exam_type === 'mid')) {
        $timeline->push([
            'label' => 'Mid-Exam',
            'score' => $computeExam('mid'),
        ]);
    }

    // 6) Final-Exam
    if ($course->exams->contains(fn($e) => $e->exam_type === 'final')) {
        $timeline->push([
            'label' => 'Final-Exam',
            'score' => $computeExam('final'),
        ]);
    }


    if(request('print'))
    {
        return view('instructor.courses.student_stats_print', [
            'course'   => $course,
            'student'  => $student,
            'timeline' => $timeline->toArray(),
        ]);
    }

    // 7) إرجاع الـ view مع البيانات
    return view('instructor.courses.student_stats', [
        'course'   => $course,
        'student'  => $student,
        'timeline' => $timeline->toArray(),
    ]);
}


}
