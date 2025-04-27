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

        // Data for filters
        $courseTypes  = CourseType::all();
        $groupTypes   = GroupType::all();
        $examiners    = User::role('Examiner')->get();
        $instructors  = User::role('Instructor')->get();
        $examStatuses = ['new', 'assigned', 'completed'];

        // 1) Filter by Course Type
        if ($request->filled('course_type_id')) {
            $query->whereHas('course.courseType', function($subQuery) use ($request) {
                $subQuery->where('id', $request->course_type_id);
            });
        }

        // 2) Filter by Group Type
        if ($request->filled('group_type_id')) {
            $query->whereHas('course.groupType', function($subQuery) use ($request) {
                $subQuery->where('id', $request->group_type_id);
            });
        }

        // 3) Filter by Instructor
        if ($request->filled('instructor_id')) {
            $instructorId = $request->instructor_id;
            $query->whereHas('course.instructor', function($subQuery) use ($instructorId) {
                $subQuery->where('id', $instructorId);
            });
        }

        // 4) Filter by Examiner
        if ($request->filled('examiner_id')) {
            $query->where('examiner_id', $request->examiner_id);
        }

        // 5) Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereNotIn('status', ['completed','canceled','paused']);
        }
        
        // 6) Custom filter for exam_date (daily, weekly, afterTwoDays)
        if ($request->filled('exam_date_filter')) {
            $dateFilter = $request->exam_date_filter;
            $today      = Carbon::today();
            if ($dateFilter === 'daily') {
                $query->whereDate('exam_date', $today);
            } elseif ($dateFilter === 'weekly') {
                $startOfWeek = $today->copy()->startOfWeek(); // Monday
                $endOfWeek   = $today->copy()->endOfWeek();   // Sunday
                $query->whereBetween('exam_date', [$startOfWeek, $endOfWeek]);
            } elseif ($dateFilter === 'afterTwoDays') {
                $targetDate = $today->copy()->addDays(2);
                $query->whereDate('exam_date', $targetDate);
            }
        }



        // if i'm examiner, show only my exams check permission Exam Manager
       
        if (Auth::user()->hasRole('Examiner') && auth()->user()->permissions->where('name','Exam Manager')->count() == 0) {
            $query->where('examiner_id', Auth::id());
        }


        $exams = $query->orderByDesc('exam_date')->get();



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
            'exam_date'      => 'required|date',
            'current_status' => 'required|in:new,assigned,completed',
        ]);
    
        $exam = Exam::findOrFail($data['exam_id']);
        $course = $exam->course;
        $newDate = Carbon::parse($data['exam_date'])->startOfDay();
    
        if ($exam->exam_type === 'pre') {
            $start = Carbon::parse($course->start_date)->startOfDay();
            if ($newDate->gt($start)) {
                return back()->withErrors(['exam_date' => 'Pre-test date cannot be after course start date.']);
            }
        }
    
        if ($exam->exam_type === 'mid') {
            $halfIndex = intdiv($course->courseType->duration, 2) - 1;
            $sortedSchedules = $course->schedules()->orderBy('date')->get();
            if (isset($sortedSchedules[$halfIndex])) {
                $expectedMid = Carbon::parse($sortedSchedules[$halfIndex]->date)->addDay();
                if ($newDate->lt($expectedMid)) {
                    return back()->withErrors(['exam_date' => 'Mid exam date cannot be earlier than suggested midpoint.']);
                }
            }
        }
    
        if ($exam->exam_type === 'final') {
            $lastClassDate = $course->schedules()->orderByDesc('date')->first();
            if ($lastClassDate && $newDate->lte(Carbon::parse($lastClassDate->date))) {
                return back()->withErrors(['exam_date' => 'Final exam must be after the last class date.']);
            }
        }
    
        $origExaminer = $exam->examiner_id;
        $origTime     = $exam->time;
        $origDate     = $exam->exam_date;
        $origStatus   = $exam->status;
    
        $exam->examiner_id = $data['examiner_id'];
        $exam->time        = $data['time'];
        $exam->exam_date   = $newDate;
    
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
            if ($old !== $new) {
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
        if ($exam->examiner_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'You are not authorized to record grades for this exam.');
        }
        $students = $exam->students;  // This relies on the defined relationship in the Exam model
        return view('exam_officer.exams.grads_record', compact('exam', 'students'));
    }


 
    public function storeGrades(Request $request, $examId)
    {
        // 0) Prevent entering grades before the exam start
        $exam = Exam::findOrFail($examId);
        [$startTime] = explode(' - ', $exam->time);
        $examStartsAt = $exam->exam_date
            ->copy()
            ->setTimeFromTimeString($startTime);
    
        if (Carbon::now()->lt($examStartsAt)) {
            return redirect()
                ->back()
                ->with('error', 'You cannot submit grades before the exam start time.');
        }
    
        // 1) Validate input
        $validatedData = $request->validate([
           'grades' => 'required|array',
        ]);
    
        $courseType = $exam->course->courseType;
        $skills     = $courseType->skills;
    
        // 2) Determine max grades per skill based on exam_type
        $max_grades = [];
        foreach ($skills as $skill) {
            if ($exam->exam_type == 'pre') {
                $max_grades[$skill->id] = $skill->pivot->pre_max;
            } elseif ($exam->exam_type == 'mid') {
                $max_grades[$skill->id] = $skill->pivot->mid_max;
            } else { // final
                $max_grades[$skill->id] = $skill->pivot->final_max;
            }
        }
    
        // 3) Save each student's grades
        foreach ($validatedData['grades'] as $studentId => $gradeData) {
            $examStudent = \App\Models\ExamStudent::firstOrCreate([
                'exam_id'    => $exam->id,
                'student_id' => $studentId,
            ]);
    
            foreach ($gradeData as $skillId => $gradeValue) {
                if (isset($max_grades[$skillId]) && $gradeValue > $max_grades[$skillId]) {
                    $gradeValue = $max_grades[$skillId];
                }
    
                \App\Models\ExamStudentGrade::updateOrCreate(
                    [
                        'exam_student_id'      => $examStudent->id,
                        'course_type_skill_id' => $skillId,
                    ],
                    ['grade' => $gradeValue]
                );
            }
        }
    
        // 4) Mark exam as completed
        $exam->status = 'completed';
        $exam->save();
    
        // 5) If final exam, mark course completed
        if ($exam->exam_type == 'final') {
            $course = $exam->course;
            $course->status = 'completed';
            $course->save();
        }
    
        return redirect()
            ->route('exam_officer.exams.index')
            ->with('success', 'Grades recorded successfully!');
    }
    
    


    public function show($id)
    {
        $exam = Exam::with(['examStudents.grades', 'course.courseType.skills'])->findOrFail($id);
        return view('exam_officer.exams.show', compact('exam'));
    }


    public function print(int $id)
    {
        /* 1. البيانات والخلفية */
        $exam   = Exam::with(['course.courseType.skills'])->findOrFail($id);
        $bgB64  = base64_encode(file_get_contents(public_path('images/exam.png')));
        $html   = view('exam_officer.exams.print', compact('exam','bgB64'))->render();
    
        /* 2. HTML → PDF (90 mm × 90 mm) */
        $sidePt = 255.1;                                           // 90 mm بالـ point
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper([0,0,$sidePt,$sidePt])
                     ->setOptions([
                         'dpi'                  => 96,
                         'isRemoteEnabled'      => true,
                         'isHtml5ParserEnabled' => true,
                         'isFontSubsettingEnabled' => true,
                         'defaultFont'          => 'cairo',
                     ])->output();
    
        $tmpPdf = storage_path("app/tmp_exam_$id.pdf");
        file_put_contents($tmpPdf,$pdfBin);
    
        $im = new \Imagick();
        $im->setResolution(600,600);
        $im->readImage($tmpPdf.'[0]');
        $im->setImageUnits(\Imagick::RESOLUTION_PIXELSPERINCH);
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(93);
        
        $im->cropThumbnailImage(1020,1020);   // جودة عالية للهواتف
        
        /* 4. صورتان */
        $lg  = clone $im; $lg->cropThumbnailImage(1020,1020);   // كبيرة
        $sm  = clone $im; $sm->cropThumbnailImage(340,340);     // بطاقة
    
        $ts        = now()->format('Ymd_His');
        $nameLg    = "prints/exam_{$id}_{$ts}_lg.jpg";
        $nameSm    = "prints/exam_{$id}_{$ts}.jpg";
    
        Storage::disk('public')->put($nameLg,$lg);
        Storage::disk('public')->put($nameSm,$sm);
    
        unlink($tmpPdf);
    
        /* 5. حمّل الصورة الصغيرة مباشرة */
        return response()->download(
            storage_path('app/public/'.$nameLg),
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
        if ($exam->exam_type === 'pre') {
            $start = Carbon::parse($course->start_date)->startOfDay();
            if ($newDate->gt($start)) {
                return back()->withErrors(['exam_date' => 'Pre-test date cannot be after course start date.']);
            }
        }
    
        if ($exam->exam_type === 'mid') {
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
    
        if ($exam->exam_type === 'final') {
            // final لا يمكن تقديمه قبل آخر محاضرة
            $lastClassDate = $course->schedules()->orderByDesc('date')->first();
            if ($lastClassDate && $newDate->lte(Carbon::parse($lastClassDate->date))) {
                return back()->withErrors(['exam_date' => 'Final exam must be after the last class date.']);
            }
        }
    
        // تحديث التاريخ والحالة
        $exam->exam_date = $newDate;
        $exam->status = $newDate->lt(Carbon::today()->subDays(2)) ? 'overdue' : 'assigned';
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
        $exam->status = $exam->status === 'new' ? 'assigned' : $exam->status;
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
    
}
