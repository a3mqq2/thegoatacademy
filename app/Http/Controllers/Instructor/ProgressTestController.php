<?php

namespace App\Http\Controllers\Instructor;

use Imagick;
use App\Models\Course;
use App\Models\AuditLog;
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
                'course.courseType' => function($query) {
                    // تحميل المهارات مع تحديد النوع
                    $query->with(['skills' => function($skillQuery) {
                        $skillQuery->withPivot(['id', 'skill_type', 'progress_test_max', 'mid_max', 'final_max']);
                    }]);
                }
            ]);
    
            // فلترة المهارات لإرجاع Progress Skills فقط
            $progressSkills = $progressTest->course->courseType->skills->filter(function($skill) {
                return $skill->pivot->skill_type == 'progress' || 
                       ($skill->pivot->skill_type == 'legacy' && $skill->pivot->progress_test_max > 0);
            });
    
            // إضافة Progress Skills إلى البيانات
            $progressTestData = $progressTest->toArray();
            $progressTestData['course']['course_type']['progress_skills'] = $progressSkills->values();
    
            return response()->json([
                'progressTest' => $progressTestData
            ]);
        }
    
        return view('instructor.courses.progress_test', compact('progressTest'));
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
            $progressTest->update([
                'date' => $data['date'], 
                'done_at' => now(), 
                'done_by' => auth()->user()->id
            ]);
    
            // تحميل المهارات المتاحة للتحقق
            $availableSkills = $progressTest->course->courseType->skills()
                ->where(function($query) {
                    $query->where('skill_type', 'progress')
                          ->orWhere(function($subQuery) {
                              $subQuery->where('skill_type', 'legacy')
                                       ->whereNotNull('progress_test_max');
                          });
                })
                ->pluck('course_type_skill.id')
                ->toArray();
    
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
    
                // حساب المجموع فقط للمهارات المتاحة
                $validScores = array_filter($stu['scores'], function($score, $pivotId) use ($availableSkills) {
                    return in_array($pivotId, $availableSkills) && $score !== null;
                }, ARRAY_FILTER_USE_BOTH);
    
                $total = array_sum($validScores);
                $pts->update(['score' => $total]);
    
                // update or create individual skill grades
                foreach ($stu['scores'] as $pivotId => $score) {
                    // التحقق من أن المهارة متاحة للـ Progress Test
                    if (!in_array($pivotId, $availableSkills)) {
                        continue;
                    }
    
                    $pivot = CourseTypeSkill::findOrFail($pivotId);
    
                    // التحقق من أن الدرجة لا تتجاوز الحد الأقصى
                    $maxGrade = $pivot->progress_test_max;
                    if ($score > $maxGrade) {
                        $score = $maxGrade;
                    }
    
                    $pts->grades()->updateOrCreate(
                        ['course_type_skill_id' => $pivotId],
                        [
                            'progress_test_grade' => $score,
                            'max_grade'           => $maxGrade,
                        ]
                    );
                }
            }
    
            // تسجيل العملية في سجل المراجعة
            AuditLog::create([
                'user_id'     => auth()->id(),
                'description' => "Progress test scores updated for test #{$progressTest->id}",
                'type'        => 'progress_test',
                'entity_id'   => $progressTest->id,
                'entity_type' => ProgressTest::class,
            ]);
        });
    
        return response()->json([
            'message' => 'Progress-test scores saved successfully'
        ], 200);
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
        $html   = view('instructor.courses.progress_test_print', compact('progressTest','bgB64'))->render();
    
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

}
