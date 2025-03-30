<?php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Models\ProgressTest;
use Illuminate\Http\Request;
use App\Models\ProgressTestStudent;
use App\Http\Controllers\Controller;

class ProgressTestController extends Controller
{
    public function create(Course $course)
    {
        return view('instructor.courses.progress_test', compact('course'));
    }

    public function store_progress_test(Request $request, Course $course)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.score' => 'required|integer',
        ]);

        $progressTest = ProgressTest::create([
            'date' => $data['date'],
            'course_id' => $course->id,
        ]);

        foreach ($data['students'] as $student) {
            ProgressTestStudent::create([
                'progress_test_id' => $progressTest->id,
                'student_id'       => $student['student_id'],
                'course_id'        => $course->id,
                'score'            => $student['score'],
            ]);
        }

        return response()->json([
            'message'       => 'Progress test scores saved successfully.',
            'progress_test' => $progressTest,
        ]);
    }
}
