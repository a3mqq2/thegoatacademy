<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use App\Models\AuditLog;
use App\Models\GroupType;
use App\Models\CourseType;
use Illuminate\Http\Request;
use App\Models\ExcludeReason;
use App\Models\CourseSchedule;
use App\Models\WithdrawnReason;
use App\Http\Controllers\Controller;
use App\Models\CourseStudent;
use App\Models\MeetingPlatform;
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
            'course_type_id'     => 'required|exists:course_types,id',
            'group_type_id'      => 'required|exists:group_types,id',
            'instructor_id'      => 'required|exists:users,id',
            'start_date'         => 'required|date',
            'mid_exam_date'      => 'required|date',
            'final_exam_date'    => 'required|date',
            'student_capacity'   => 'required|integer|min:1',
            'schedule'           => 'required|array|min:1',
            'schedule.*.day'     => 'required|string',
            'schedule.*.date'    => 'required|date',
            'schedule.*.fromTime'=> 'required',
            'schedule.*.toTime'  => 'required',
            'students'           => 'required|array|min:1',
            'students.*'         => 'exists:students,id',
            'meeting_platform_id' => "required|exists:meeting_platforms,id",
            'whatsapp_group_link' => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
    
        $daysMapping = [
            0 => 'Sat',
            1 => 'Sun',
            2 => 'Mon',
            3 => 'Tue',
            4 => 'Wed',
            5 => 'Thu',
            6 => 'Fri',
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
            // Create the Course
            $course = Course::create([
                'course_type_id'   => $data['course_type_id'],
                'group_type_id'    => $data['group_type_id'],
                'instructor_id'    => $data['instructor_id'],
                'start_date'       => $data['start_date'],
                'mid_exam_date'    => $data['mid_exam_date'],
                'final_exam_date'  => $data['final_exam_date'],
                'end_date'         => $data['final_exam_date'],
                'student_capacity' => $data['student_capacity'],
                'status'           => $status,
                'days'             => implode('-', $selectedDays->toArray()),
                'time'             => $request->time,
                'student_count'    => count($data['students']),
                'meeting_platform_id' => $data['meeting_platform_id'],
                'whatsapp_group_link' => $data['whatsapp_group_link'],
            ]);
    
            // Create the schedule records
            foreach ($data['schedule'] as $item) {
                $course->schedules()->create([
                    'day'       => $item['day'],
                    'date'      => $item['date'],
                    'from_time' => $item['fromTime'],
                    'to_time'   => $item['toTime'],
                ]);
            }
    
            // Attach students
            $course->students()->sync($data['students']);
    
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
            // يمكنك تسجيل الخطأ هنا إذا رغبت
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
    
            // **Add these lines to validate selected_days**
            'selected_days'         => 'required|array|min:1',
            'selected_days.*'       => 'integer|in:0,1,2,3,4,5,6',
    
            'meeting_platform_id'   => 'required|exists:meeting_platforms,id',
            'whatsapp_group_link'   => 'nullable',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
    
        $daysMapping = [
            0 => 'Sat',
            1 => 'Sun',
            2 => 'Mon',
            3 => 'Tue',
            4 => 'Wed',
            5 => 'Thu',
            6 => 'Fri',
        ];
    
        // Convert numeric days to short codes (e.g. "Sun","Mon",...)
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
        $instructors = User::role('instructor')->with('skills')->get();
        $students = Student::with('skills')->orderByDesc('id')->get();
        $meeting_platforms = MeetingPlatform::all();
        return response()->json([
            'groupTypes' => $groupTypes,
            'courseTypes' => $courseTypes,
            'instructors' => $instructors,
            'students' => $students,
            "meeting_platforms" => $meeting_platforms,
            'course' => Course::with(['instructor','schedules','students','courseType','groupType'])->find(request()->id),
        ]);
    }

    public function show($id)
    {

        $students = Student::whereDoesntHave('courses', function($query) use ($id) {
            $query->where('courses.id', $id);
        })->get();
        
        $course = Course::with(['students'])->findOrFail($id);
        
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

    
}
