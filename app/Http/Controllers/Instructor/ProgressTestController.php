<?php

namespace App\Http\Controllers\Instructor;

use Imagick;
use App\Models\Course;
use App\Models\ProgressTest;
use Illuminate\Http\Request;
use Laravel\Prompts\Progress;
use App\Models\CourseTypeSkill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use App\Models\ProgressTestStudent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

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
    

    public function prindt(ProgressTest $progressTest, Request $request)
    {
        $progressTest->load([
            'progressTestStudents.student',
            'progressTestStudents.grades.courseTypeSkill.skill',
            'course.courseType.skills'
        ]);
    
        return view('instructor.courses.progress_test_print', compact('progressTest'));
    }

    

    public function print(int $id)
    {
        $progressTest = ProgressTest::findOrFail($id);
        $progressTest->load([
            'progressTestStudents.student',
            'progressTestStudents.grades.courseTypeSkill.skill',
            'course.courseType.skills'
        ]);
        
        $bgB64  = base64_encode(file_get_contents(public_path('images/exam.png')));
        $html   = view('instructor.courses.progress_test_print', compact('exam','bgB64'))->render();
    
        $sidePt = 768; // لازم 768 pt عشان بالضبط 1024px بعد التحويل
    
        $pdfBin = Pdf::loadHTML($html)
                    ->setPaper([0, 0, $sidePt, $sidePt])
                    ->setOptions([
                        'dpi' => 96,
                        'isRemoteEnabled' => true,
                        'isHtml5ParserEnabled' => true,
                        'isFontSubsettingEnabled' => true,
                        'defaultFont' => 'cairo',
                    ])->output();
    
        $tmpPdf = storage_path("app/tmp_progress_test_$id.pdf");
        file_put_contents($tmpPdf, $pdfBin);
    
        $im = new Imagick();
        $im->setResolution(96, 96);
        $im->readImage($tmpPdf.'[0]');
        $im->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(90);
        $im->resizeImage(1024, 1024, \Imagick::FILTER_LANCZOS, 1);
    
        $ts     = now()->format('Ymd_His');
        $nameLg = "prints/progress_test_{$id}_{$ts}_lg.jpg";
    
        Storage::disk('public')->put($nameLg, $im);
    
        unlink($tmpPdf);
    
        return response()->download(
            storage_path('app/public/' . $nameLg),
            "progress_test_{$id}.jpg"
        );
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
