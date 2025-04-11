<?php

namespace App\Http\Controllers\ExamOfficer;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\CourseType;
use App\Models\GroupType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

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
        $request->validate([
            'exam_id'        => 'required|exists:exams,id',
            'examiner_id'    => 'required|exists:users,id',
            'time'           => 'required',
            'exam_date'      => 'required|date',
            'current_status' => 'required|in:new,pending,completed',
        ]);
        
        $exam = Exam::findOrFail($request->exam_id);
        $originalExaminerId = $exam->examiner_id;
        $originalTime       = $exam->time;
        $originalDate       = $exam->exam_date;
        $originalStatus     = $exam->status;
        
        $exam->examiner_id = $request->examiner_id;
        $exam->time        = $request->time;
        $exam->exam_date   = $request->exam_date;
        
        // Check if the exam date is at least two days before today.
        // If so, set the status to 'overdue'.
        if ($exam->exam_date->lt(Carbon::today()->subDays(2))) {
            $exam->status = "overdue";
        } else {
            $exam->status = "pending";
        }
        
        $exam->save();
        $changes = [];
        
        // Examiner changes
        if ($originalExaminerId != $exam->examiner_id) {
            $oldExaminerName = $originalExaminerId
                ? optional(\App\Models\User::find($originalExaminerId))->name
                : 'NULL';
            $newExaminerName = $exam->examiner_id
                ? optional($exam->examiner)->name
                : 'NULL';
            if ($oldExaminerName !== $newExaminerName) {
                $changes[] = "Examiner changed from [{$oldExaminerName}] to [{$newExaminerName}]";
            }
        }
        
        // Time change
        if ($originalTime != $exam->time) {
            $oldTime = $originalTime ?? 'NULL';
            $newTime = $exam->time ?? 'NULL';
            $changes[] = "Time changed from [{$oldTime}] to [{$newTime}]";
        }
        
        // Exam Date change
        if ($originalDate != $exam->exam_date) {
            $oldDateFmt = $originalDate ? $originalDate->format('Y-m-d') : 'NULL';
            $newDateFmt = $exam->exam_date ? $exam->exam_date->format('Y-m-d') : 'NULL';
            $changes[] = "Exam Date changed from [{$oldDateFmt}] to [{$newDateFmt}]";
        }
        
        // Status change
        if ($originalStatus != $exam->status) {
            $changes[] = "Status changed from [{$originalStatus}] to [{$exam->status}]";
        }
        
        if (count($changes) > 0) {
            \App\Models\AuditLog::create([
                'user_id'     => \Illuminate\Support\Facades\Auth::id(),
                'description' => "Updated exam #{$exam->id}: " . implode(' | ', $changes),
                'type'        => 'exams',
                'entity_id'   => $exam->id,
                'entity_type' => \App\Models\Exam::class,
            ]);
        }
        
        return redirect()
            ->route('exam_officer.exams.index')
            ->with('success', 'Exam preparation updated successfully!');
    }
    
    
    public function showRecordForm($examId)
    {
        // Load exam with course and its students (with pivot data)
        $exam = Exam::with(['course', 'course.students'])->findOrFail($examId);
        
        // Ensure only the assigned examiner can record grades
        if ($exam->examiner_id !== \Illuminate\Support\Facades\Auth::id()) {
            abort(403, 'You are not authorized to record grades for this exam.');
        }
        
        // In the simplified design, we use the exam's belongsToMany relation to fetch students along with their pivot (grades)
        $students = $exam->students;  // This relies on the defined relationship in the Exam model
        
        return view('exam_officer.exams.grads_record', compact('exam', 'students'));
    }


 
    public function storeGrades(Request $request, $examId)
    {
        // Validate that 'grades' is provided and is an array.
        $validatedData = $request->validate([
           'grades' => 'required|array',
        ]);
        
        // Retrieve the exam; if not found, fail.
        $exam = Exam::findOrFail($examId);
        $course_type = $exam->course->courseType;
        $skills = $course_type->skills;
        
        // Determine max grade for each skill based on exam type.
        $max_grades = [];
        foreach ($skills as $skill) {
            if ($exam->exam_type == 'pre') {
                $max_grades[$skill->id] = $skill->pivot->final_max;
            } elseif ($exam->exam_type == 'mid') {
                $max_grades[$skill->id] = $skill->pivot->mid_max;
            } else {
                // Fallback; adjust as needed.
                $max_grades[$skill->id] = $skill->pivot->final_max;
            }
        }
        
        // Iterate over each student in the submitted grades.
        // Here, the keys of the 'grades' array represent student IDs.
        foreach ($validatedData['grades'] as $studentId => $gradeData) {
            // Try to find the exam-student record using the ExamStudent model.
            $examStudent = \App\Models\ExamStudent::where('exam_id', $exam->id)
                                ->where('student_id', $studentId)
                                ->first();
            // If not found, create it.
            if (!$examStudent) {
                $examStudent = \App\Models\ExamStudent::create([
                    'exam_id'    => $exam->id,
                    'student_id' => $studentId,
                ]);
            }
            
            // For each submitted grade for this student, keyed by skill_id:
            foreach ($gradeData as $skillId => $gradeValue) {
                // OPTIONAL: Cap the grade to the maximum allowed if necessary.
                if (isset($max_grades[$skillId]) && $gradeValue > $max_grades[$skillId]) {
                    $gradeValue = $max_grades[$skillId];
                }
                
                // Create or update the exam student grade record.
                \App\Models\ExamStudentGrade::updateOrCreate(
                    [
                        'exam_student_id'      => $examStudent->id,
                        'course_type_skill_id' => $skillId,
                    ],
                    [
                        'grade' => $gradeValue,
                    ]
                );
            }
        }
        
        // Update the exam's status to completed.
        $exam->status = 'completed';
        $exam->save();
        
        // If this is a final exam, update the course status to completed.
        if ($exam->exam_type == 'final') {
            $exam->course->status = 'completed';
            $exam->course->save();
        }
        
        return redirect()->route('exam_officer.exams.index')
                         ->with('success', 'Grades recorded successfully!');
    }
    


    public function show($id)
    {
        $exam = Exam::with(['examStudents.grades', 'course.courseType.skills'])->findOrFail($id);
        return view('exam_officer.exams.show', compact('exam'));
    }
    
}
