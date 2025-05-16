<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Models\ProgressTest;
use Illuminate\Http\Request;
use App\Models\CourseTypeSkill;
use Illuminate\Support\Facades\DB;
use App\Models\ProgressTestStudent;
use App\Http\Controllers\Controller;

class ProgressTestController extends Controller
{
    public function create(Course $course)
    {
        return view('instructor.courses.progress_test', compact('course'));
    }

    public function store(Request $request, $courseId)
    {
        $data = $request->validate([
            'date'              => 'required|date',
            'students'          => 'required|array',
            'students.*.student_id'    => 'required|exists:students,id',
            'students.*.scores'        => 'required|array',
            'students.*.scores.*'      => 'nullable|numeric|min:0',
        ]);

        $course = Course::with('courseType.skills')->findOrFail($courseId);

        DB::transaction(function() use ($data, $course) {
            // determine week number (e.g. next sequential)
            $week = $course->progressTests()->count() + 1;

            // create the progress test
            $progressTest = ProgressTest::create([
                'date'      => $data['date'],
                'course_id' => $course->id,
                'week'      => $week,
            ]);

            foreach ($data['students'] as $stu) {
                // overall score can be sum of all skill scores (optional)
                $totalScore = array_sum($stu['scores']);

                // create student record
                $pts = ProgressTestStudent::create([
                    'progress_test_id' => $progressTest->id,
                    'student_id'       => $stu['student_id'],
                    'course_id'        => $course->id,
                    'score'            => $totalScore,
                ]);

                // store each skill grade
                foreach ($stu['scores'] as $skillId => $score) {
                    // find the pivot row to get max grade
                    $ctSkill = CourseTypeSkill::where([
                        'course_type_id' => $course->course_type_id,
                        'skill_id'       => $skillId,
                    ])->firstOrFail();

                    $pts->grades()->create([
                        'course_type_skill_id' => $ctSkill->id,
                        'progress_test_grade'  => $score,
                        'max_grade'            => $ctSkill->progress_test_max,
                    ]);
                }
            }
        });

        return response()->json([
            'message' => 'Scores saved successfully'
        ], 201);
    }
}
