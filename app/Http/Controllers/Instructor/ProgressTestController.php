<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Models\ProgressTest;
use Illuminate\Http\Request;
use App\Models\CourseTypeSkill;
use Illuminate\Support\Facades\DB;
use App\Models\ProgressTestStudent;
use App\Http\Controllers\Controller;
use Laravel\Prompts\Progress;

class ProgressTestController extends Controller
{
    public function show(ProgressTest $progressTest, Request $request)
    {
        if ($request->wantsJson()) {
            $progressTest->load([
                'progressTestStudents.student',
                'progressTestStudents.grades.courseTypeSkill.skill',
                'course.courseType.skills'
            ]);
    
            return response()->json([
                'progressTest' => $progressTest
            ]);
        }
    
        return view('instructor.courses.progress_test', compact('progressTest'));
    }
    

    public function print(ProgressTest $progressTest, Request $request)
    {
        $progressTest->load([
            'progressTestStudents.student',
            'progressTestStudents.grades.courseTypeSkill.skill',
            'course.courseType.skills'
        ]);
    
        return view('instructor.courses.progress_test_print', compact('progressTest'));
    }


    public function store(Request $request, ProgressTest $progressTest)
    {
        $data = $request->validate([
            'date'               => 'required|date',
            'students'           => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.scores'     => 'required|array',
            'students.*.scores.*'   => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function() use ($data, $progressTest) {
            // update test date
            $progressTest->update(['date' => $data['date'], 'done_at' => now(), 'done_by' => auth()->user()->id]);

            foreach ($data['students'] as $stu) {
                // ensure a student record exists
                $pts = ProgressTestStudent::firstOrCreate(
                    [
                        'progress_test_id' => $progressTest->id,
                        'student_id'       => $stu['student_id'],
                    ],
                    [
                        'course_id' => $progressTest->course_id,
                    ]
                );

                // update aggregate score
                $total = array_sum($stu['scores']);
                $pts->update(['score' => $total]);

                // update or create individual skill grades
                foreach ($stu['scores'] as $pivotId => $score) {
                    $pivot = CourseTypeSkill::findOrFail($pivotId);

                    $pts->grades()->updateOrCreate(
                        ['course_type_skill_id' => $pivotId],
                        [
                            'progress_test_grade' => $score,
                            'max_grade'           => $pivot->progress_test_max,
                        ]
                    );
                }
            }
        });

        return response()->json([
            'message' => 'Progress-test scores saved successfully'
        ], 200);
    }
}
