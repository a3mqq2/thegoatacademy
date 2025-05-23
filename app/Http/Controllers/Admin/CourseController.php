<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Setting;
use App\Models\Student;
use App\Models\AuditLog;
use App\Models\GroupType;
use App\Models\CourseType;
use App\Models\ProgressTest;
use Illuminate\Http\Request;
use App\Models\CourseStudent;
use App\Models\ExcludeReason;
use App\Models\CourseSchedule;
use App\Services\WaapiService;
use App\Models\MeetingPlatform;
use App\Models\WithdrawnReason;
use App\Models\CourseAttendance;
use Illuminate\Support\Facades\DB;
use App\Models\ProgressTestStudent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['courseType', 'groupType', 'instructor', 'schedules']);
    
        if ($request->filled('course_name')) {
            $query->whereHas('courseType', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->course_name . '%');
            });
        }
    
        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
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

            /* 1. validate -------------------------------------------------- */
            $validator = Validator::make($request->all(), [
                'course_type_id'        => 'required|exists:course_types,id',
                'group_type_id'         => 'required|exists:group_types,id',
                'instructor_id'         => 'required|exists:users,id',
                'start_date'            => 'required|date',
                'pre_test_date'         => 'nullable|date',
                'mid_exam_date'         => 'nullable|date',
                'final_exam_date'       => 'nullable|date',
                'meeting_platform_id'   => 'nullable|exists:meeting_platforms,id',
                'student_capacity'      => 'required|integer|min:1',
                'schedule'              => 'required|array|min:1',
                'schedule.*.day'        => 'required|string',
                'schedule.*.date'       => 'required|date',
                'schedule.*.fromTime'   => 'required',
                'schedule.*.toTime'     => 'required',
                'students'              => 'required|array|min:1',
                'students.*'            => 'exists:students,id',
                'whatsapp_group_link'   => 'nullable',
                'levels'                => 'nullable|array',
                'levels.*'              => 'exists:levels,id',
                'progress_tests'       => 'nullable|array',
            ]);



            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            /* 2. sanitize dates  (Y‑m‑d for DATE columns) ------------------ */
            $data = $validator->validated();
            foreach (['start_date','pre_test_date','mid_exam_date','final_exam_date'] as $f) {
                $data[$f] = empty($data[$f])
                    ? null
                    : Carbon::parse($data[$f])->toDateString();   // 2025‑04‑22
            }

            /* 3. always grab arrays safely -------------------------------- */
            $students   = $data['students']   ?? [];
            $levels     = $data['levels']     ?? [];
            $rawSchedule= $data['schedule']   ?? [];
            $selected   = $request->input('selected_days', []);


         

            /* guard: capacity */
            if (count($students) > $data['student_capacity']) {
                return response()->json([
                    'message' => 'The number of students exceeds the student capacity.',
                ], 422);
            }

            /* 4. derive helper fields ------------------------------------- */
            $daysMap = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
            $daysStr = collect($selected)->map(fn($d) => $daysMap[$d])->implode('-');

            $today  = now();
            $status = $today->lt($data['start_date'])
                ? 'upcoming'
                : ($data['final_exam_date'] && $today->gt($data['final_exam_date']) ? 'completed' : 'ongoing');

            /* remove duplicate/overlap schedule rows */
            $examDates = collect([$data['pre_test_date'],$data['mid_exam_date'],$data['final_exam_date']])->filter();
            $schedule  = collect($rawSchedule)
                ->unique('date')
                ->reject(fn($r) => $examDates->contains($r['date']))
                ->values();

            if ($schedule->isEmpty()) {
                return response()->json([
                    'message' => 'Schedule is empty or overlaps entirely with exam dates.',
                ], 422);
            }

            /* 5. persist  -------------------------------------------------- */
            DB::beginTransaction();
            try {

                $setting_allowed_abcences_instructor = Setting::where('key', 'Allowed instructor absence count per course')->first();
                $setting_alert_abcences_instructor = Setting::where('key', 'Instructor absence warning threshold per course')->first();
                $warnAbsent      = (int) Setting::where('key','Student’s absence alert')->value('value');
                $warnHomework    = (int) Setting::where('key','Student’s missing homework’s alert')->value('value');
                $stopAbsent      = (int) Setting::where('key','Dismissing the student because of absence')->value('value');
                $stopHomework    = (int) Setting::where('key','Dismissing the student because of not delivering the homework.')->value('value');

        
                $course = Course::create([
                    'course_type_id'       => $data['course_type_id'],
                    'group_type_id'        => $data['group_type_id'],
                    'instructor_id'        => $data['instructor_id'],
                    'start_date'           => $data['start_date'],
                    'pre_test_date'        => $data['pre_test_date'],
                    'mid_exam_date'        => $data['mid_exam_date'],
                    'final_exam_date'      => $data['final_exam_date'],
                    'end_date'             => $data['final_exam_date'],
                    'student_capacity'     => $data['student_capacity'],
                    'status'               => $status,
                    'days'                 => $daysStr,
                    'time'                 => $request->time,
                    'student_count'        => count($students),
                    'meeting_platform_id'  => $data['meeting_platform_id'] ?? null,
                    'whatsapp_group_link'  => $data['whatsapp_group_link'] ?? null,
                    'progress_test_day'    => $request->progress_test_day,
                    'allowed_abcences_instructor' => $setting_allowed_abcences_instructor ? $setting_allowed_abcences_instructor->value : '',
                    'alert_abcences_instructor' => $setting_alert_abcences_instructor ? $setting_alert_abcences_instructor->value : '',
                    'warn_absent'           => $warnAbsent,
                    'warn_homework'         => $warnHomework,
                    'stop_absent'           => $stopAbsent,
                    'stop_homework'         => $stopHomework,
                ]);



                // 1) load the full close‐window in hours
                $hoursForClose = (int) Setting::where('key', 'Updating the students’ Attendance after the class.')
                                            ->value('value');

                // 2) convert that to minutes, then take half
                $minutesForAlert = ($hoursForClose * 60) / 2;

                foreach ($schedule as $row) {
                    // 3) build a Carbon for the actual end of the session
                    //    adjust the format string if your $row['toTime'] has seconds
                    $endDateTime = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    "{$row['date']} {$row['toTime']}"
                    );

                    // 4) compute the two timestamps
                    $alertAt = $endDateTime->copy()->addMinutes($minutesForAlert);
                    $closeAt = $endDateTime->copy()->addHours($hoursForClose);

                    // 5) persist both
                    $course->schedules()->create([
                        'day'       => $row['day'],
                        'date'      => $row['date'],
                        'from_time' => $row['fromTime'],
                        'to_time'   => $row['toTime'],
                        'alert_at'  => $alertAt,   // halfway point
                        'close_at'  => $closeAt,   // full window
                    ]);
                }
     
                
                foreach ($request->progress_tests as $ptData) {
                    // load settings
                    $updateWindow = (int) Setting::where('key', 'Allow updating progress tests after class end time (hours)')
                                                ->value('value');
                    $alertDelay   = (int) Setting::where('key', 'Notify instructor after update grace period (hours)')
                                                ->value('value');

                    // find the matching schedule for this date
                    $schedule = $course->schedules()->first();
                    if (! $schedule) {
                        throw new \Exception("No schedule found for {$ptData['date']}");
                    }

                    // use only the end time
                    $toTime   = $schedule->to_time; // e.g. "17:44"
                    $testEnd  = Carbon::createFromFormat('Y-m-d H:i', "{$ptData['date']} {$toTime}");

                    // alert first, then hard close
                    $willAlertAt = $testEnd->copy()->addHours($alertDelay);
                    $closeAt     = $willAlertAt->copy()->addHours($updateWindow);

                    // create the ProgressTest
                    $progressTest = ProgressTest::create([
                        'date'          => $ptData['date'],
                        'time'          => $toTime,        // <-- now only the end-time
                        'course_id'     => $course->id,
                        'week'          => $ptData['week'],
                        'will_alert_at' => $willAlertAt,
                        'close_at'      => $closeAt,
                    ]);

                    // seed students
                    foreach ($students as $student_id) {
                        ProgressTestStudent::create([
                            'progress_test_id' => $progressTest->id,
                            'student_id'       => $student_id,
                            'course_id'        => $course->id,
                        ]);
                    }
                }
                $examMap = ['pre'=>'pre_test_date','mid'=>'mid_exam_date','final'=>'final_exam_date'];
                foreach ($examMap as $type=>$field) {
                    if ($data[$field]) {
                        $course->exams()->create([
                            'exam_type' => $type,
                            'exam_date' => $data[$field],  // YYYY‑MM‑DD
                            'status'    => 'new',
                        ]);
                    }
                }

                $course->students()->sync($students);
                $course->levels()->sync($levels);

                AuditLog::create([
                    'user_id'     => Auth::id(),
                    'description' => 'Created course: '.($course->courseType->name ?? 'Unnamed'),
                    'type'        => 'create',
                    'entity_id'   => $course->id,
                    'entity_type' => Course::class,
                ]);

                DB::commit();
                return response()->json([
                    'message' => 'Course created successfully',
                    'course'  => $course->load('schedules','exams','students','levels'),
                ], 201);

            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'An error occurred while creating the course',
                    'error'   => $e->getMessage(),
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

  
    
    public function update(Request $request, Course $course)
    {
        $validator = Validator::make($request->all(), [
            'course_type_id'        => 'required|exists:course_types,id',
            'group_type_id'         => 'required|exists:group_types,id',
            'instructor_id'         => 'required|exists:users,id',
            'start_date'            => 'required|date',
            'pre_test_date'         => 'nullable|date',
            'mid_exam_date'         => 'nullable|date',
            'final_exam_date'       => 'nullable|date',
            'meeting_platform_id'   => 'nullable|exists:meeting_platforms,id',
            'student_capacity'      => 'required|integer|min:1',
            'schedule'              => 'required|array|min:1',
            'schedule.*.day'        => 'required|string',
            'schedule.*.date'       => 'required|date',
            'schedule.*.fromTime'   => 'required',
            'schedule.*.toTime'     => 'required',
            'students'              => 'required|array|min:1',
            'students.*'            => 'exists:students,id',
            'whatsapp_group_link'   => 'nullable',
            'levels'                => 'nullable|array',
            'levels.*'              => 'exists:levels,id',
        ]);
    


        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }
    
        $data = $validator->validated();
        foreach (['start_date','pre_test_date','mid_exam_date','final_exam_date'] as $f) {
            $data[$f] = empty($data[$f])
                ? null
                : Carbon::parse($data[$f])->toDateString();
        }
    
        $students    = $data['students']   ?? [];
        $levels      = $data['levels']     ?? [];
        $rawSchedule = $data['schedule']   ?? [];
        $selected    = $request->input('selected_days', []);
    
        if (count($students) > $data['student_capacity']) {
            return response()->json([
                'message' => 'The number of students exceeds the student capacity.',
            ], 422);
        }
    
        $daysMap = [0=>'Sun',1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
        $daysStr = collect($selected)->map(fn($d) => $daysMap[$d])->implode('-');
    
        $today  = now();
        $status = $today->lt($data['start_date'])
            ? 'upcoming'
            : ($data['final_exam_date'] && $today->gt($data['final_exam_date']) ? 'completed' : 'ongoing');
    
        $examDates = collect([$data['pre_test_date'],$data['mid_exam_date'],$data['final_exam_date']])->filter();
        $schedule  = collect($rawSchedule)
            ->unique('date')
            ->reject(fn($r) => $examDates->contains($r['date']))
            ->values();
    
        if ($schedule->isEmpty()) {
            return response()->json([
                'message' => 'Schedule is empty or overlaps entirely with exam dates.',
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $course->update([
                'course_type_id'       => $data['course_type_id'],
                'group_type_id'        => $data['group_type_id'],
                'instructor_id'        => $data['instructor_id'],
                'start_date'           => $data['start_date'],
                'pre_test_date'        => $data['pre_test_date'],
                'mid_exam_date'        => $data['mid_exam_date'],
                'final_exam_date'      => $data['final_exam_date'],
                'end_date'             => $data['final_exam_date'],
                'student_capacity'     => $data['student_capacity'],
                'status'               => $status,
                'days'                 => $daysStr,
                'time'                 => $request->time,
                'student_count'        => count($students),
                'meeting_platform_id'  => $data['meeting_platform_id'] ?? null,
                'whatsapp_group_link'  => $data['whatsapp_group_link'] ?? null,
                'progress_test_day'     => $request->progress_test_day,
            ]);
    
            $course->schedules()->delete();


            foreach($request->progress_tests as $progress_test)
            {
                ProgressTest::updateOrCreate([
                    'course_id' => $course->id,
                    'week' => $progress_test['week'],
                ], [
                    'date' => $progress_test['date'],
                ]);
            }
            

            foreach ($schedule as $row) {
                $course->schedules()->create([
                    'day'       => $row['day'],
                    'date'      => $row['date'],
                    'from_time' => $row['fromTime'],
                    'to_time'   => $row['toTime'],
                ]);
            }
    
            $course->exams()->delete();
            $examMap = ['pre'=>'pre_test_date','mid'=>'mid_exam_date','final'=>'final_exam_date'];
            foreach ($examMap as $type => $field) {
                if ($data[$field]) {
                    $course->exams()->create([
                        'exam_type' => $type,
                        'exam_date' => $data[$field],
                        'status'    => 'new',
                    ]);
                }
            }
    
            $course->students()->sync($students);
            $course->levels()->sync($levels);
    
            AuditLog::create([
                'user_id'     => Auth::id(),
                'description' => 'Updated course: '.($course->courseType->name ?? ''),
                'type'        => 'update',
                'entity_id'   => $course->id,
                'entity_type' => Course::class,
            ]);
    
            DB::commit();
    
            return response()->json([
                'message' => 'Course updated successfully',
                'course'  => $course->load('schedules','exams','students','levels'),
            ], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'An error occurred while updating the course',
                'error'   => $e->getMessage(),
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
        $instructors = User::role('instructor')->with('skills','levels')
        ->whereDoesntHave('courses', function($q) {
            $q->whereIn('status', ['ongoing', 'upcoming']);
        })->get();
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


        $course = Course::with(['students','progressTests','courseType','groupType','courseType.skills'])->findOrFail($id);
        
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


    public function store_attendance(Request $request, WaapiService $waapi)
    {
        $data = $request->validate([
            'course_schedule_id'            => 'required|exists:course_schedules,id',
            'students'                      => 'required|array',
            'students.*.student_id'         => 'required|exists:students,id',
            'students.*.attendance'         => 'required|in:present,absent',
            'students.*.notes'              => 'nullable|string',
            'students.*.homework_submitted' => 'required|boolean',
        ]);
    
        $schedule = CourseSchedule::with('course')->findOrFail($data['course_schedule_id']);
        $course = $schedule->course;
    
        if ($course->instructor_id != Auth::id()) {
            return response()->json(['message' => 'Access denied.'], 403);
        }
    
        $lectureStart = Carbon::parse("{$schedule->date} {$schedule->from_time}");
        $lectureEnd = Carbon::parse("{$schedule->date} {$schedule->to_time}");
        if ($lectureEnd->lte($lectureStart)) {
            $lectureEnd->addDay();
        }
    
        $limitHrs = (int) Setting::where('key', 'Updating the students’ Attendance after the class.')->value('value');
        $modifyWindowEnd = $lectureEnd->copy()->addHours($limitHrs);
    
        $newSchedule = null;
    
        if (Carbon::parse($schedule->date)->lt(today())) {
            $schedule->update([
                'attendance_taken_at' => now(),
                'status'              => 'absent',
            ]);
    
            $newSchedule = CourseSchedule::create([
                'course_id' => $course->id,
                'day'       => today()->dayOfWeek,
                'date'      => today()->toDateString(),
                'from_time' => $schedule->from_time,
                'to_time'   => $schedule->to_time,
                'status'    => 'done',
                'attendance_taken_at' => now(),
                'extra_date'  => $schedule->date,
            ]);
        } else {
            $schedule->update([
                'attendance_taken_at' => now(),
                'status'              => 'done',
            ]);
        }
    
        foreach ($data['students'] as $student) {
            CourseAttendance::updateOrCreate(
                [
                    'course_id'          => $course->id,
                    'student_id'         => $student['student_id'],
                    'course_schedule_id' => $newSchedule?->id ?? $schedule->id,
                ],
                [
                    'attendance'         => $student['attendance'],
                    'homework_submitted' => $student['homework_submitted'],
                    'notes'              => $student['notes'] ?? null,
                ]
            );
        }
    
        foreach ($course->students as $stu) {
            $absences = CourseAttendance::where([
                            ['course_id',   '=', $course->id],
                            ['student_id',  '=', $stu->id],
                            ['attendance',  '=', 'absent'],
                        ])->count();
    
            $missHw = CourseAttendance::where([
                            ['course_id',          '=', $course->id],
                            ['student_id',         '=', $stu->id],
                            ['homework_submitted', '=', false],
                        ])->count();
    
            if ($absences >= $course->stop_absent || $missHw >= $course->stop_homework) {
                if ($stu->pivot->status !== 'excluded') {
                    $course->students()->updateExistingPivot($stu->id, ['status' => 'excluded']);
    
                    if ($course->groupType && strtolower($course->groupType->name) === 'private') {
                        $course->update(['status' => 'paused']);
                    }
    
                    $msg = "🚫 *تنبيه هام*\n"
                        . "تم فصل الطالب *{$stu->name}* من الكورس رقم *{$course->id}* "
                        . "بسبب تجاوز حدّ الغياب/الواجب.\n"
                        . "عدد الغيابات: $absences  عدد الواجبات غير المسلَّمة: $missHw.";
    
                    $waapi->sendText(formatLibyanPhone($stu->phone), $msg);
                }
            } elseif ($absences >= $course->warn_absent || $missHw >= $course->warn_homework) {
                $msg = "⚠️ *إنذار للطالب {$stu->name}*\n"
                     . "عدد غياباتك الحالية: $absences (الحدّ الإنذاري $course->warn_absent)\n"
                     . "عدد الواجبات غير المسلَّمة: $missHw (الحدّ الإنذاري $course->warn_homework)\n"
                     . "يرجى الالتزام لتفادي الفصل.";
    
                $waapi->sendText(formatLibyanPhone($stu->phone), $msg);
            }
    
            if (
                $stu->pivot->status === 'excluded' &&
                $absences < $course->stop_absent &&
                $missHw  < $course->stop_homework
            ) {
                $course->students()->updateExistingPivot($stu->id, ['status' => 'ongoing']);
            }
        }
    
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Took attendance for schedule #{$schedule->id}",
            'type'        => 'update',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);
    
        return response()->json(['message' => 'Attendance saved successfully']);
    }
    
    public function restore($courseId)
    {

        $course = Course::findOrFail($courseId);

        // check the start date if > today and < end date set ongoing
        $today = now()->startOfDay();
        $startDate = Carbon::parse($course->start_date)->startOfDay();
        $endDate = $course->end_date ? Carbon::parse($course->end_date)->startOfDay() : null;

        if ($startDate->isFuture() && ($endDate && $endDate->isFuture())) {
            $course->status = 'upcoming';
        } elseif ($endDate && $endDate->isPast()) {
            $course->status = 'completed';
        } else {
            $course->status = 'ongoing';
        }

        $course->save();

        // Optionally log this action (e.g. AuditLog)
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Restored course #{$course->id}",
            'type'        => 'restore',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);

        return redirect()->back()->with('success', 'Course restored successfully.');
    }
    
    
    public function updateStatus(Request $request, Course $course)
    {
        $request->validate([
            'status' => 'required|in:upcoming,ongoing,paused,canceled',
        ]);
    
        $today = now()->toDateString();
    
        if (in_array($request->status, ['paused', 'canceled'])) {
            $course->update(['status' => $request->status]);
            $course->exams()->update(['status' => $request->status]);
        } else {
            if ($course->start_date <= $today && $course->final_exam_date >= $today) {
                $course->update(['status' => 'ongoing']);
                $course->exams()->update(['status' => 'new']);
            } elseif ($course->start_date > $today) {
                $course->update(['status' => 'upcoming']);
                $course->exams()->update(['status' => 'new']);
            }
        }
        return redirect()->route('admin.courses.show', $course->id)->with('success', 'Course status updated successfully.');
    }
    
    
}
