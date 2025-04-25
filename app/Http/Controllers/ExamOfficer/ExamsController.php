<?php

namespace App\Http\Controllers\ExamOfficer;

use Imagick;
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
use Illuminate\Support\Facades\Http;
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
        $examStatuses = ['new', 'pending', 'completed'];

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


        $exams = $query->orderByDesc('id')->get();



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
        // 1) Validate request fields
        $data = $request->validate([
            'exam_id'        => 'required|exists:exams,id',
            'examiner_id'    => 'required|exists:users,id',
            'time'           => 'required',
            'exam_date'      => 'required|date',
            'current_status' => 'required|in:new,pending,completed',
        ]);
    
        // 2) Load the exam
        $exam = Exam::findOrFail($data['exam_id']);
    
        // 3) Preserve originals for audit
        $origExaminer = $exam->examiner_id;
        $origTime     = $exam->time;
        $origDate     = $exam->exam_date;
        $origStatus   = $exam->status;
    
        // 4) Apply incoming updates
        $exam->examiner_id = $data['examiner_id'];
        $exam->time        = $data['time'];
        $exam->exam_date   = Carbon::parse($data['exam_date']);
    
        // 5) Recompute status
        if ($exam->exam_date->lt(Carbon::today()->subDays(2))) {
            $exam->status = 'overdue';
        } else {
            $exam->status = 'pending';
        }
    
        $exam->save();
    
        // 6) Collect audit changes
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
    
        // 7) If this exam belongs to a course, sync course dates & regenerate its schedule
        if ($course = $exam->course) {
            // update the matching course date field
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
    
            // regenerate the course schedule (skip Fridays, week starts Saturday)
            $course->generateSchedule();
    
            // 8) Propagate those three dates to all OTHER exams of that course
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
        // 1. البيانات والخلفية
        $exam   = Exam::with(['course.courseType.skills'])->findOrFail($id);
        $bgB64  = base64_encode(file_get_contents(public_path('images/exam.png')));
        $html   = view('exam_officer.exams.card', ['exam'=>$exam,'bgData'=>$bgB64])->render();
    
        // 2. HTML → PDF 90mm²
        $sidePt = 255.1;
        $pdfBin = Pdf::loadHTML($html)
                     ->setPaper([0,0,$sidePt,$sidePt])
                     ->setOptions([
                         'dpi'                     => 96,
                         'isRemoteEnabled'         => true,
                         'isHtml5ParserEnabled'    => true,
                         'isFontSubsettingEnabled' => true,
                         'defaultFont'             => 'cairo',
                     ])->output();
    
        $tmpPdf = storage_path("app/tmp_exam_$id.pdf");
        file_put_contents($tmpPdf,$pdfBin);
    
        // 3. Imagick
        $im = new \Imagick();
        $im->setResolution(300,300);
        $im->readImage($tmpPdf.'[0]');
        $im->setImageBackgroundColor('white');
        $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        $im->setImageFormat('jpg');
        $im->setImageCompressionQuality(95);
        $im->unsharpMaskImage(0,0.5,1,0);
    
        // 4. صورتان: كبيرة وصغيرة
        $large = clone $im;   $large->cropThumbnailImage(1020,1020);          // ≈ 3×90mm
        $small = clone $im;   $small->cropThumbnailImage(340,340);            // 90mm
    
        $nameLarge = 'prints/exam_'.$id.'_'.now()->format('Ymd_His').'_lg.jpg';
        $nameSmall = 'prints/exam_'.$id.'_'.now()->format('Ymd_His').'.jpg';
    
        Storage::disk('public')->put($nameLarge,$large);
        Storage::disk('public')->put($nameSmall,$small);
    
        unlink($tmpPdf);
    
        return response()->json([
            'success'      => true,
            'image_large'  => asset('storage/'.$nameLarge),
            'image_small'  => asset('storage/'.$nameSmall),
        ]);
    }
    
    
}
