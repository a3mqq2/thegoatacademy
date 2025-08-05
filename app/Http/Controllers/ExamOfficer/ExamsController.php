<?php

namespace App\Http\Controllers\ExamOfficer;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\GroupType;
use App\Models\CourseType;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamsController extends Controller
{
   
    public function index(Request $request)
    {
        $query = Exam::with([
            'examiner',
            'course' => function($q) {
                $q->with(['courseType', 'groupType', 'instructor', 'students']);
            }
        ]);

        $courseTypes  = CourseType::all();
        $groupTypes   = GroupType::all();
        $examiners    = User::role('Examiner')->get();
        $instructors  = User::role('Instructor')->get();
        $examStatuses = ['new', 'assigned', 'completed'];

        if ($request->filled('course_type_id')) {
            $query->whereHas('course.courseType', function($subQuery) use ($request) {
                $subQuery->where('id', $request->course_type_id);
            });
        }

        if ($request->filled('group_type_id')) {
            $query->whereHas('course.groupType', function($subQuery) use ($request) {
                $subQuery->where('id', $request->group_type_id);
            });
        }

        if ($request->filled('instructor_id')) {
            $instructorId = $request->instructor_id;
            $query->whereHas('course.instructor', function($subQuery) use ($instructorId) {
                $subQuery->where('id', $instructorId);
            });
        }

        if ($request->filled('examiner_id')) {
            $query->where('examiner_id', $request->examiner_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereNotIn('status', ['completed', 'canceled', 'paused']);
        }

        if ($request->filled('exam_date_filter')) {
            $dateFilter = $request->exam_date_filter;
            $today      = Carbon::today();
            if ($dateFilter == 'daily') {
                $query->whereDate('exam_date', $today);
            } elseif ($dateFilter == 'weekly') {
                $startOfWeek = $today->copy()->startOfWeek();
                $endOfWeek   = $today->copy()->endOfWeek();
                $query->whereBetween('exam_date', [$startOfWeek, $endOfWeek]);
            } elseif ($dateFilter == 'afterTwoDays') {
                $targetDate = $today->copy()->addDays(2);
                $query->whereDate('exam_date', $targetDate);
            }
        }

        if (Auth::user()->hasRole('Examiner') && auth()->user()->permissions->where('name', 'Exam Manager')->count() == 0) {
            $query->where('examiner_id', Auth::id());
        }


        // where has course status is not paused or cancelled
        $query->whereHas('course', function($subQuery) {
            $subQuery->whereNotIn('status', ['paused', 'cancelled']);
        });

        $exams = $query
            ->orderBy('exam_date', 'asc')
            ->orderBy('time', 'asc') // لو حابب كمان ترتيب بالوقت داخل نفس اليوم
            ->get();

        return view('exam_officer.exams.index', compact(
            'exams',
            'courseTypes',
            'groupTypes',
            'examiners',
            'instructors',
            'examStatuses'
        ));
    }

    
    public function prepareExam(Request $request)
    {
        $data = $request->validate([
            'exam_id'        => 'required|exists:exams,id',
            'examiner_id'    => 'required|exists:users,id',
            'time'           => 'required',
            'current_status' => 'required|in:new,assigned,completed',
        ]);
    
        $exam = Exam::findOrFail($data['exam_id']);
        $course = $exam->course;
     
    
        $origExaminer = $exam->examiner_id;
        $origTime     = $exam->time;
        $origDate     = $exam->exam_date;
        $origStatus   = $exam->status;
    
        $exam->examiner_id = $data['examiner_id'];
        $exam->time        = $data['time'];
    
        if ($exam->exam_date->lt(Carbon::today()->subDays(2))) {
            $exam->status = 'overdue';
        } else {
            $exam->status = 'assigned';
        }
    



        $exam->save();
    
        $changes = [];
    
        if ($origExaminer != $exam->examiner_id) {
            $old = $origExaminer
                ? optional(User::find($origExaminer))->name
                : 'NULL';
            $new = $exam->examiner
                ? $exam->examiner->name
                : 'NULL';
            if ($old != $new) {
                $changes[] = "Examiner changed from [{$old}] to [{$new}]";
            }
        }
    
        if ($origTime != $exam->time) {
            $changes[] = "Time changed from [{$origTime}] to [{$exam->time}]";
        }
    
        if ($origDate != $exam->exam_date) {
            $old = $origDate ? $origDate->format('Y-m-d') : 'NULL';
            $new = $exam->exam_date->format('Y-m-d');
            $changes[] = "Exam Date changed from [{$old}] to [{$new}]";
        }
    
        if ($origStatus != $exam->status) {
            $changes[] = "Status changed from [{$origStatus}] to [{$exam->status}]";
        }
    
        if (count($changes)) {
            AuditLog::create([
                'user_id'     => auth()->id(),
                'description' => "Updated exam #{$exam->id}: " . implode(' | ', $changes),
                'type'        => 'exams',
                'entity_id'   => $exam->id,
                'entity_type' => Exam::class,
            ]);
        }
    
        if ($course) {
            switch ($exam->exam_type) {
                case 'pre':
                    $course->pre_test_date = $exam->exam_date->toDateString();
                    break;
                case 'mid':
                    $course->mid_exam_date = $exam->exam_date->toDateString();
                    break;
                case 'final':
                    $course->final_exam_date = $exam->exam_date->toDateString();
                    break;
            }
    
            $course->save();
            $course->generateSchedule();
    
            $course->exams()
                ->where('id', '!=', $exam->id)
                ->get()
                ->each(function(Exam $other) use ($course) {
                    switch ($other->exam_type) {
                        case 'pre':
                            $other->exam_date = $course->pre_test_date;
                            break;
                        case 'mid':
                            $other->exam_date = $course->mid_exam_date;
                            break;
                        case 'final':
                            $other->exam_date = $course->final_exam_date;
                            break;
                    }
                    $other->save();
                });
        }
    
        return redirect()
            ->route('exam_officer.exams.index')
            ->with('success', 'Exam preparation updated successfully!');
    }
    
    
    
    public function showRecordForm($examId)
    {
        $exam = Exam::with(['course', 'course.students'])->findOrFail($examId);
        if ($exam->examiner_id != \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'You are not authorized to record grades for this exam.');
        }
        $students = $exam->students;  // This relies on the defined relationship in the Exam model
        return view('exam_officer.exams.grads_record', compact('exam', 'students'));
    }

// إضافة هذه الدوال في ExamsController

/**
 * الحصول على المهارات والدرجات القصوى للامتحان
 */
private function getSkillsAndMaxGrades(Exam $exam)
{
    $skills = $exam->course->courseType->getSkillsForExamType($exam->exam_type);
    
    $maxGrades = [];
    foreach ($skills as $skill) {
        $pivotId = $skill->pivot->id;
        $maxGrades[$pivotId] = $skill->pivot->getMaxGradeForExamType($exam->exam_type);
    }
    
    return [$skills, $maxGrades];
}

/**
 * التحقق من صحة الدرجات المدخلة
 */
private function validateGradesForExam(array $grades, array $maxGrades): array
{
    $validatedGrades = [];
    
    foreach ($grades as $studentId => $gradeData) {
        foreach ($gradeData as $pivotId => $gradeValue) {
            $maxGrade = $maxGrades[$pivotId] ?? 0;
            
            // التأكد من أن الدرجة لا تتجاوز الحد الأقصى
            $validatedGrades[$studentId][$pivotId] = min($gradeValue, $maxGrade);
            
            // التأكد من أن الدرجة لا تقل عن الصفر
            $validatedGrades[$studentId][$pivotId] = max($validatedGrades[$studentId][$pivotId], 0);
        }
    }
    
    return $validatedGrades;
}


    public function storeGrades(Request $request, $examId)
    {
        $exam = Exam::findOrFail($examId);
        [$startTime] = explode(' - ', $exam->time);
        $examStartsAt = $exam->exam_date->copy()->setTimeFromTimeString($startTime);

        if (Carbon::now()->lt($examStartsAt)) {
            return back()->with('error', 'You cannot submit grades before the exam start time.');
        }

        $validatedData = $request->validate([
            'grades' => 'required|array',
        ]);

        // للـ Mid و Final فقط - نحصل على مهارات الامتحان فقط
        $skills = $exam->course->courseType->examSkills;

        $maxGrades = [];
        foreach ($skills as $skill) {
            $pivotId = $skill->pivot->id;
            $maxGrades[$pivotId] = match ($exam->exam_type) {
                'mid'   => $skill->pivot->mid_max,
                'final' => $skill->pivot->final_max,
                default => $skill->pivot->final_max, // fallback
            };
        }

        foreach ($validatedData['grades'] as $studentId => $gradeData) {
            $examStudent = \App\Models\ExamStudent::firstOrCreate([
                'exam_id'    => $exam->id,
                'student_id' => $studentId,
            ]);

            foreach ($gradeData as $pivotId => $gradeValue) {
                if (isset($maxGrades[$pivotId]) && $gradeValue > $maxGrades[$pivotId]) {
                    $gradeValue = $maxGrades[$pivotId];
                }

                \App\Models\ExamStudentGrade::updateOrCreate(
                    [
                        'exam_student_id'      => $examStudent->id,
                        'course_type_skill_id' => $pivotId,
                    ],
                    ['grade' => $gradeValue]
                );
            }
        }

        $exam->update(['status' => 'completed']);

        if ($exam->exam_type == 'final') {
            $exam->course->update(['status' => 'completed']);
        }

        return redirect()->route('exam_officer.exams.index')
                        ->with('success', 'Grades recorded successfully!');
    }
    public function show($id)
    {
        $exam = Exam::with(['examStudents.grades', 'course.courseType.skills'])->findOrFail($id);
        return view('exam_officer.exams.show', compact('exam'));
    }


    public function print(int $id)
    {
        $exam   = Exam::with(['course.courseType.skills'])->findOrFail($id);
        $bgB64  = base64_encode(file_get_contents(public_path('images/exam.png')));
        $html   = view('exam_officer.exams.print', compact('exam','bgB64'))->render();
    
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
    
        $tmpPdf = storage_path("app/tmp_exam_$id.pdf");
        file_put_contents($tmpPdf, $pdfBin);
    
        $im = new \Imagick();
        $im->setResolution(96, 96);
        $im->readImage($tmpPdf.'[0]');
        $im->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(90);
        $im->resizeImage(1024, 1024, \Imagick::FILTER_LANCZOS, 1);
    
        $ts     = now()->format('Ymd_His');
        $nameLg = "prints/exam_{$id}_{$ts}_lg.jpg";
    
        Storage::disk('public')->put($nameLg, $im);
    
        unlink($tmpPdf);
    
        return response()->download(
            storage_path('app/public/' . $nameLg),
            "exam_{$id}.jpg"
        );
    }
    
    

    

    

    public function updateDate(Request $request)
    {
        $data = $request->validate([
            'exam_id'    => 'required|exists:exams,id',
            'exam_date'  => 'required|date',
        ]);
    
        $exam = Exam::findOrFail($data['exam_id']);
        $course = $exam->course;
        $newDate = Carbon::parse($data['exam_date'])->startOfDay();
        $origDate = $exam->exam_date;
        $origStatus = $exam->status;
    
        // قواعد التواريخ بناءً على نوع الامتحان
        if ($exam->exam_type == 'pre') {
            $start = Carbon::parse($course->start_date)->startOfDay();
            if ($newDate->gt($start)) {
                return back()->withErrors(['exam_date' => 'Pre-test date cannot be after course start date.']);
            }
        }
    
        if ($exam->exam_type == 'mid') {
            // نقوم بحساب الموعد المقترح (منتصف مدة الدورة)
            $halfIndex = intdiv($course->courseType->duration, 2) - 1;
            $sortedSchedules = $course->schedules()->orderBy('date')->get();
            if (isset($sortedSchedules[$halfIndex])) {
                $expectedMid = Carbon::parse($sortedSchedules[$halfIndex]->date)->addDay();
                if ($newDate->lt($expectedMid)) {
                    return back()->withErrors(['exam_date' => 'Mid exam date cannot be earlier than suggested midpoint.']);
                }
            }
        }
    
        if ($exam->exam_type == 'final') {
            // final لا يمكن تقديمه قبل آخر محاضرة
            $lastClassDate = $course->schedules()->orderByDesc('date')->first();
            if ($lastClassDate && $newDate->lte(Carbon::parse($lastClassDate->date))) {
                return back()->withErrors(['exam_date' => 'Final exam must be after the last class date.']);
            }
        }
    
        // تحديث التاريخ والحالة
        $exam->exam_date = $newDate;
        $exam->status = $newDate->lt(Carbon::today()->subDays(2)) ? 'overdue' : $exam->status;
        $exam->save();
    
        // Audit Log
        $changes = [];
        if ($origDate != $exam->exam_date) {
            $changes[] = "Exam Date changed from [" . $origDate?->format('Y-m-d') . "] to [" . $exam->exam_date->format('Y-m-d') . "]";
        }
        if ($origStatus != $exam->status) {
            $changes[] = "Status changed from [$origStatus] to [$exam->status]";
        }
        if ($changes) {
            AuditLog::create([
                'user_id'     => auth()->id(),
                'description' => "Updated exam #{$exam->id}: " . implode(' | ', $changes),
                'type'        => 'exams',
                'entity_id'   => $exam->id,
                'entity_type' => Exam::class,
            ]);
        }
    
        // تحديث تاريخ الامتحان في الكورس
        if ($course) {
            switch ($exam->exam_type) {
                case 'pre':
                    $course->pre_test_date = $exam->exam_date->toDateString();
                    break;
                case 'mid':
                    $course->mid_exam_date = $exam->exam_date->toDateString();
                    break;
                case 'final':
                    $course->final_exam_date = $exam->exam_date->toDateString();
                    break;
            }
    
            $course->save();
            $course->generateSchedule();
    
            // تحديث تواريخ باقي الامتحانات لنفس الكورس
            $course->exams()
                ->where('id', '!=', $exam->id)
                ->get()
                ->each(function(Exam $other) use ($course) {
                    switch ($other->exam_type) {
                        case 'pre':
                            $other->exam_date = $course->pre_test_date;
                            break;
                        case 'mid':
                            $other->exam_date = $course->mid_exam_date;
                            break;
                        case 'final':
                            $other->exam_date = $course->final_exam_date;
                            break;
                    }
                    $other->save();
                });
        }
    
        return back()->with('success', 'Exam date updated and schedule regenerated.');
    }
    
    

    public function assignExaminer(Request $request)
    {
        $data = $request->validate([
            'exam_id'     => 'required|exists:exams,id',
            'examiner_id' => 'required|exists:users,id',
        ]);
    
        $exam = Exam::findOrFail($data['exam_id']);
        $exam->examiner_id = $data['examiner_id'];
        $exam->status = $exam->status == 'new' ? 'assigned' : $exam->status;
        $exam->save();
    
        return back()->with('success', 'Examiner assigned successfully');
    }

    public function markAsComplete()
    {
        $exam = Exam::findOrFail(request('exam_id'));
        $exam->status = 'completed';
        $exam->save();


        // if type is final update course status to completed

        if ($exam->exam_type == 'final') {
            $course = $exam->course;
            $course->status = 'completed';
            $course->save();
        }

        return back()->with('success', 'Exam marked as complete');
    }


    public function updateTime(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'time'    => 'required|date_format:H:i',
        ]);
    
        $exam = Exam::findOrFail($request->exam_id);
        $exam->time = $request->time;
        $exam->save();
    
        return redirect()->back()->with('success', 'Exam time updated successfully.');
    }
    



/**
     * Mark a student as absent for an exam
     */
    public function markAbsent(Request $request, $examId, $studentId)
    {
        $exam = Exam::findOrFail($examId);
        
 
        try {
            // البحث عن أو إنشاء سجل exam_student
            $examStudent = \App\Models\ExamStudent::firstOrCreate([
                'exam_id'    => $examId,
                'student_id' => $studentId,
            ]);
            
            // تحديث الحالة إلى غائب
            $examStudent->update(['status' => 'absent']);
            
            // حذف جميع الدرجات المرتبطة بهذا الطالب في هذا الامتحان
            $examStudent->grades()->delete();
            
            // تسجيل العملية في الـ Audit Log
            AuditLog::create([
                'user_id'     => auth()->id(),
                'description' => "Marked student #{$studentId} as absent for exam #{$examId}",
                'type'        => 'exams',
                'entity_id'   => $examId,
                'entity_type' => Exam::class,
            ]);
            
            $studentName = \App\Models\Student::find($studentId)->name ?? "Student #{$studentId}";
            
            return back()->with('success', "{$studentName} has been marked as absent and grades have been cleared.");
            
        } catch (\Exception $e) {
            \Log::error('Error marking student as absent: ' . $e->getMessage(), [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'An error occurred while marking the student as absent.');
        }
    }

    /**
     * Mark a student as present for an exam
     */
    public function markPresent(Request $request, $examId, $studentId)
    {
        $exam = Exam::findOrFail($examId);
        
        try {
            // البحث عن سجل exam_student
            $examStudent = \App\Models\ExamStudent::where([
                'exam_id'    => $examId,
                'student_id' => $studentId,
            ])->first();
            
            if ($examStudent) {
                // تحديث الحالة إلى حاضر
                $examStudent->update(['status' => 'present']);
            } else {
                // إنشاء سجل جديد بحالة حاضر
                \App\Models\ExamStudent::create([
                    'exam_id'    => $examId,
                    'student_id' => $studentId,
                    'status'     => 'present'
                ]);
            }
            
            // تسجيل العملية في الـ Audit Log
            AuditLog::create([
                'user_id'     => auth()->id(),
                'description' => "Marked student #{$studentId} as present for exam #{$examId}",
                'type'        => 'exams',
                'entity_id'   => $examId,
                'entity_type' => Exam::class,
            ]);
            
            $studentName = \App\Models\Student::find($studentId)->name ?? "Student #{$studentId}";
            
            return back()->with('success', "{$studentName} has been marked as present.");
            
        } catch (\Exception $e) {
            \Log::error('Error marking student as present: ' . $e->getMessage(), [
                'exam_id' => $examId,
                'student_id' => $studentId,
                'user_id' => auth()->id()
            ]);
            
            return back()->with('error', 'An error occurred while marking the student as present.');
        }
    }

}
