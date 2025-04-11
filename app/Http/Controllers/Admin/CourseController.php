<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Student;
use App\Models\AuditLog;
use App\Models\GroupType;
use App\Models\CourseType;
use Illuminate\Http\Request;
use App\Models\CourseStudent;
use App\Models\ExcludeReason;
use App\Models\CourseSchedule;
use App\Models\MeetingPlatform;
use App\Models\WithdrawnReason;
use App\Models\CourseAttendance;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['courseType', 'groupType', 'instructor', 'schedules']);

        if ($request->filled('course_type')) {
            $query->where('course_type_id', $request->course_type);
        }

        if ($request->filled('group_type')) {
            $query->where('group_type_id', $request->group_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->paginate(10);
        $courseTypes = CourseType::all();
        $groupTypes = GroupType::all();
        $instructors = User::role('instructor')->get();
        return view('admin.courses.index', compact('courses', 'courseTypes', 'groupTypes','instructors'));
    }

    public function create()
    {
        $courseTypes = CourseType::all();
        $groupTypes = GroupType::all();
        $instructors = User::role('instructor')->get();
        $students = Student::all();

        return view('admin.courses.create', compact('courseTypes', 'groupTypes', 'instructors', 'students'));
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_type_id'    => 'required|exists:course_types,id',
            'group_type_id'     => 'required|exists:group_types,id',
            'instructor_id'     => 'required|exists:users,id',
            'start_date'        => 'required|date',
            'pre_test_date'     => 'required|date', // التأكد من وجود تاريخ pre test
            'mid_exam_date'     => 'required|date',
            'final_exam_date'   => 'required|date',
            'student_capacity'  => 'required|integer|min:1',
            'schedule'          => 'required|array|min:1',
            'schedule.*.day'    => 'required|string',
            'schedule.*.date'   => 'required|date',
            'schedule.*.fromTime' => 'required',
            'schedule.*.toTime' => 'required',
            'students'          => 'required|array|min:1',
            'students.*'        => 'exists:students,id',
            'whatsapp_group_link' => 'nullable',
            'levels'            => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();
        
        $daysMapping = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];
        
        $selectedDays = collect($request->selected_days)->map(function ($day) use ($daysMapping) {
            return $daysMapping[$day];
        });
        
        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['final_exam_date']);
        $today = Carbon::now();
        
        $status = 'upcoming';
        if ($today->lt($start)) {
            $status = 'upcoming';
        } elseif ($today->gt($end)) {
            $status = 'completed';
        } else {
            $status = 'ongoing';
        }
        
        if (count($data['students']) > $data['student_capacity']) {
            return response()->json([
                'message' => 'The number of students exceeds the student capacity.'
            ], 422);
        }
        
        try {
            // إنشاء الكورس
            $course = Course::create([
                'course_type_id'   => $data['course_type_id'],
                'group_type_id'    => $data['group_type_id'],
                'instructor_id'    => $data['instructor_id'],
                'start_date'       => $data['start_date'],
                'pre_test_date'    => $data['pre_test_date'], // إضافة تاريخ pre test
                'mid_exam_date'    => $data['mid_exam_date'],
                'final_exam_date'  => $data['final_exam_date'],
                'end_date'         => $data['final_exam_date'],
                'student_capacity' => $data['student_capacity'],
                'status'           => $status,
                'days'             => implode('-', $selectedDays->toArray()),
                'time'             => $request->time,
                'student_count'    => count($data['students']),
                'meeting_platform_id' => isset($data['meeting_platform_id']) ? $data['meeting_platform_id'] : null,
                'whatsapp_group_link' => $data['whatsapp_group_link'],
            ]);
        
            // إنشاء سجلات الجدول الزمني
            foreach ($data['schedule'] as $item) {
                $course->schedules()->create([
                    'day'       => $item['day'],
                    'date'      => $item['date'],
                    'from_time' => $item['fromTime'],
                    'to_time'   => $item['toTime'],
                ]);
            }
        
            // إنشاء سجلات الامتحانات تلقائيًا: pre, mid, final
            $course->exams()->create([
                'exam_type' => 'pre',
                'exam_date' => $data['pre_test_date'],
                'status'    => 'new',
            ]);
        
            $course->exams()->create([
                'exam_type' => 'mid',
                'exam_date' => $data['mid_exam_date'],
                'status'    => 'new',
            ]);
        
            $course->exams()->create([
                'exam_type' => 'final',
                'exam_date' => $data['final_exam_date'],
                'status'    => 'new',
            ]);
        
            // ربط الطلاب بالكورس
            $course->students()->sync($data['students']);
        
            if (isset($data['levels'])) {
                $course->levels()->sync($data['levels']);
            }
        
            AuditLog::create([
                'user_id'      => Auth::id(),
                'description'  => 'Created a new course: ' . ($course->courseType->name ?? 'Unnamed'),
                'type'         => 'create',
                'entity_id'    => $course->id,
                'entity_type'  => Course::class,
            ]);
        
            return response()->json([
                'message' => 'Course created successfully',
                'course'  => $course
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating the course',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function edit($id)
    {
        $course = Course::with('schedules', 'students')->findOrFail($id);
        $courseTypes = CourseType::all();
        $groupTypes = GroupType::all();
        $instructors = User::role('instructor')->get();
        $students = Student::all();

        return view('admin.courses.edit', compact('course', 'courseTypes', 'groupTypes', 'instructors', 'students'));
    }

  
    
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
    
        $validator = Validator::make($request->all(), [
            'course_type_id'        => 'required|exists:course_types,id',
            'group_type_id'         => 'required|exists:group_types,id',
            'instructor_id'         => 'required|exists:users,id',
            'start_date'            => 'required|date',
            'pre_test_date'         => 'required|date', // Ensure pre test date is validated
            'mid_exam_date'         => 'required|date',
            'final_exam_date'       => 'required|date',
            'student_capacity'      => 'required|integer|min:1',
            'schedule'              => 'required|array|min:1',
            'schedule.*.day'        => 'required|string',
            'schedule.*.date'       => 'required|date',
            'schedule.*.fromTime'   => 'required',
            'schedule.*.toTime'     => 'required',
            'students'              => 'required|array|min:1',
            'students.*'            => 'exists:students,id',
    
            // Validate selected_days
            'selected_days'         => 'required|array|min:1',
            'selected_days.*'       => 'integer|in:0,1,2,3,4,5,6',
    
            'meeting_platform_id'   => 'nullable|exists:meeting_platforms,id',
            'whatsapp_group_link'   => 'nullable',
            'levels'                => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
    
        $daysMapping = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];
    
        // Convert numeric days to short codes (e.g. "Sun", "Mon", ...)
        $selectedDays = collect($data['selected_days'])->map(
            fn($day) => $daysMapping[$day] ?? null
        )->filter();
    
        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['final_exam_date']);
        $today = Carbon::now();
    
        // Determine status
        $status = 'upcoming';
        if ($today->lt($start)) {
            $status = 'upcoming';
        } elseif ($today->gt($end)) {
            $status = 'completed';
        } else {
            $status = 'ongoing';
        }
    
        // Check capacity
        if (count($data['students']) > $data['student_capacity']) {
            return response()->json([
                'message' => 'The number of students exceeds the student capacity.'
            ], 422);
        }
    
        try {
            // Update the Course
            $course->update([
                'course_type_id'       => $data['course_type_id'],
                'group_type_id'        => $data['group_type_id'],
                'instructor_id'        => $data['instructor_id'],
                'start_date'           => $data['start_date'],
                'pre_test_date'        => $data['pre_test_date'], // Update pre test date
                'mid_exam_date'        => $data['mid_exam_date'],
                'final_exam_date'      => $data['final_exam_date'],
                'end_date'             => $data['final_exam_date'],
                'student_capacity'     => $data['student_capacity'],
                'status'               => $status,
                'days'                 => $selectedDays->isEmpty()
                                          ? ''
                                          : implode('-', $selectedDays->toArray()),
                'time'                 => $request->time,
                'student_count'        => count($data['students']),
                'meeting_platform_id'  => $data['meeting_platform_id'],
                'whatsapp_group_link'  => $data['whatsapp_group_link'],
            ]);
    
            // Delete old schedules before re-inserting
            $course->schedules()->delete();
    
            if(isset($data['levels'])) {
                $course->levels()->sync($data['levels']);
            }
    
            // Re-create schedule records
            foreach ($data['schedule'] as $item) {
                $course->schedules()->create([
                    'day'       => $item['day'],
                    'date'      => $item['date'],
                    'from_time' => $item['fromTime'],
                    'to_time'   => $item['toTime'],
                ]);
            }
    
            // Sync students
            $course->students()->sync($data['students']);
    
            // Update exam records (if exam dates or pre test date have changed)
            // Update Pre-test exam
            $examPre = $course->exams()->where('exam_type', 'pre')->first();
            if ($examPre) {
                $examPre->update(['exam_date' => $data['pre_test_date']]);
            }
    
            // Update Mid exam
            $examMid = $course->exams()->where('exam_type', 'mid')->first();
            if ($examMid) {
                $examMid->update(['exam_date' => $data['mid_exam_date']]);
            }
    
            // Update Final exam
            $examFinal = $course->exams()->where('exam_type', 'final')->first();
            if ($examFinal) {
                $examFinal->update(['exam_date' => $data['final_exam_date']]);
            }
    
            AuditLog::create([
                'user_id'      => Auth::id(),
                'description'  => 'Updated course: ' . ($course->courseType->name ?? 'Unnamed'),
                'type'         => 'update',
                'entity_id'    => $course->id,
                'entity_type'  => Course::class,
            ]);
    
            return response()->json([
                'message' => 'Course updated successfully',
                'course'  => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the course',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully.');
    }

    public function courseRequirements()
    {
        $groupTypes = GroupType::where('status','active')->get();
        $courseTypes = CourseType::where('status','active')->with('skills')->get();
        $instructors = User::role('instructor')->with('skills','levels')->get();
        $students = Student::with('skills')->orderByDesc('id')->get();
        $levels = Level::all();
        $meeting_platforms = MeetingPlatform::all();
        return response()->json([
            'groupTypes' => $groupTypes,
            'courseTypes' => $courseTypes,
            'instructors' => $instructors,
            'students' => $students,
            'levels' => $levels,
            "meeting_platforms" => $meeting_platforms,
            'course' => Course::with(['instructor','schedules','students','courseType','groupType','levels'])->find(request()->id),
        ]);
    }

    public function show($id)
    {


        $course = Course::with(['students'])->findOrFail($id);
        
        if (request()->wantsJson()) {
            $schedule = CourseSchedule::with('attendances')->whereId(request()->schedule_id)->first();
        
            // Get the previous week's range from Saturday to Tuesday
            $start = Carbon::now()->startOfWeek(Carbon::SATURDAY)->subWeek();
            $end = (clone $start)->addDays(3);
        
           
        
            return response()->json([
                'course'   => $course,
                'schedule' => $schedule,
            ]);
        }
        
        

        $students = Student::whereDoesntHave('courses', function($query) use ($id) {
            $query->where('courses.id', $id);
        })->get();
        
     

        // Also fetch reasons to pass to the modals
        $excludeReasons = ExcludeReason::orderBy('name')->get();
        $withdrawnReasons = WithdrawnReason::orderBy('name')->get();
    
        return view('admin.courses.show', [
            'course' => $course,
            'logs'   => $course->logs,
            'excludeReasons'   => $excludeReasons,
            'withdrawnReasons' => $withdrawnReasons,
            'students' => $students,
        ]);
    }
    

    public function cancel($id)
    {
        $course = Course::findOrFail($id);

        // Update status to 'cancelled'
        $course->status = 'canceled';
        $course->save();

        // Optionally log this action (e.g. AuditLog)
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Cancelled course #{$course->id}",
            'type'        => 'cancel',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);

        // Return or redirect as needed
        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course cancelled successfully.');
    }


    public function enroll($id, Request $request)
    {
        $request->validate([
            "student_id" => "required|exists:students,id",
        ]);
    
        $course = Course::findOrFail($id);
    
        // Count how many students are actively enrolled 
        // (statuses that count toward capacity, e.g. 'ongoing', 'upcoming')
        // If your pivot table has a 'status' column, filter out withdrawn/excluded
        $activeCount = $course->students()
            ->wherePivotIn('status', ['ongoing']) // or whatever statuses count
            ->count();
    
        // Check capacity
        if ($activeCount >= $course->student_capacity) {
            return redirect()
                ->back()
                ->withErrors([
                    'capacity' => 'This course is full (capacity reached).'
                ]);
        }
    
        // Otherwise, proceed to enroll
        // If you want to ensure student not already enrolled, 
        // you can do an additional check before creating a new pivot record
        CourseStudent::create([
            'course_id'  => $id,
            'student_id' => $request->student_id,
            'status'     => 'ongoing', // Or 'upcoming', etc.
        ]);
    
        $course->increment('student_count', 1);

        // Log the action
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Enrolled student #{$request->student_id} in course #{$course->id}",
            'type'        => 'enroll',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);
    
        return redirect()->back()->with('success', 'Student enrolled successfully.');
    }
    
    public function courses_suggestions(Request $request)
    {
        // Get course types that are connected to the selected skills
        $course_types = CourseType::whereHas('skills', function($query) use ($request) {
            $query->whereIn('skills.id', $request->skills);
        })->get();
    
        // Get courses that have a course_type_id in the above course types and with status upcoming or ongoing
        $courses = Course::with(["instructor","courseType","groupType"])->whereIn('course_type_id', $course_types->pluck('id')->toArray())
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->get();
    
        // Remove dd so that the response can be returned as JSON
        return response()->json($courses);
    }

    public function print($id)
    {
        $course = Course::with(['students'])->findOrFail($id);
        return view('admin.courses.print', compact('course'));
    }


        /**
     * Reactivate (unpause) a paused course.
     * Switch status to "ongoing" if end_date > today, otherwise "completed".
     */
    public function reactive($id)
    {
        $course = Course::findOrFail($id);

        // If the course is "paused", we want to unpause it
        // If end_date is in the future, set status to "ongoing"
        // else set it to "completed"
        $today    = now()->startOfDay();
        $endDate  = $course->end_date ? Carbon::parse($course->end_date)->startOfDay() : null;

        if ($endDate && $endDate->isFuture()) {
            $course->status = 'ongoing';
        } else {
            $course->status = 'completed';
        }

        $course->save();

        // Log the action in the audit log
        AuditLog::create([
            'user_id'      => auth()->id(),
            'description' => "Reactivated course ID {$course->id} (" 
            . (
                $course->courseType && $course->courseType->name 
                  ? $course->courseType->name 
                  : 'N/A'
              ) 
            . "). Status changed to {$course->status}.",          
            'type'         => 'update',
            'entity_id'    => $course->id,
            'entity_type'  => Course::class,
        ]);

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Course reactivated successfully.');
    }

    public function search()
    {
        $course = Course::whereId(request()->code)->first();
        if($course)
        {
            return redirect()->route('admin.courses.show', $course);
        } else 
        {
            return redirect()->back()->withErrors(['Course Not Found']);
        }
    }


    public function store_attendance(Request $request)
    {
        $data = $request->validate([
            'course_schedule_id' => 'required|exists:course_schedules,id',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.attendance' => 'required|in:present,absent',
            'students.*.notes' => 'nullable|string',
            'students.*.homework_submitted' => 'required|boolean',
        ]);
    
        $course = auth()->user()->courses()
            ->whereHas('schedules', function ($q) use ($data) {
                $q->where('id', $data['course_schedule_id']);
            })->first();
    
        if (!$course) {
            return response()->json(['message' => 'Course not found for this schedule.'], 404);
        }
    
        foreach ($data['students'] as $student) {
            // Save attendance
            CourseAttendance::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'student_id' => $student['student_id'],
                    'course_schedule_id' => $data['course_schedule_id'],
                ],
                [
                    'attendance' => $student['attendance'],
                    'notes' => $student['notes'] ?? null,
                    'homework_submitted' => $student['homework_submitted'],
                    'notes' => $student['notes'] ?? null,
                ]
            );
    
            // Count total absences and unsubmitted homework for this course
            $absences = CourseAttendance::where([
                ['course_id', '=', $course->id],
                ['student_id', '=', $student['student_id']],
                ['attendance', '=', 'absent'],
            ])->count();
    
            $unsubmittedHomeworks = CourseAttendance::where([
                ['course_id', '=', $course->id],
                ['student_id', '=', $student['student_id']],
                ['homework_submitted', '=', false],
            ])->count();
    
            // If exceeded limit, exclude student
            if ($absences >= 6 || $unsubmittedHomeworks >= 6) {
                $course->students()->updateExistingPivot($student['student_id'], [
                    'status' => 'excluded'
                ]);
            }
        }
    
        // Mark attendance as taken for this schedule
        CourseSchedule::where('id', $data['course_schedule_id'])->update([
            'attendance_taken_at' => now(),
        ]);

        // log
        AuditLog::create([
            'user_id'      => Auth::id(),
            'description'  => 'Took attendance for course schedule #' . $data['course_schedule_id'],
            'type'         => 'update',
            'entity_id'    => $course->id,
            'entity_type'  => Course::class,
        ]);
    
        return response()->json(['message' => 'Attendance saved successfully']);
    }
    
    
    
}
