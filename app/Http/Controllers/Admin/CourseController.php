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
            'course_type_id'   => 'required|exists:course_types,id',
            'group_type_id'    => 'required|exists:group_types,id',
            'instructor_id'    => 'required|exists:users,id',
            'start_date'       => 'required|date',
            'mid_exam_date'    => 'required|date',
            'final_exam_date'  => 'required|date',
            'student_capacity' => 'required|integer|min:1',
            'schedule'         => 'required|array|min:1',
            'schedule.*.day'   => 'required|string',
            'schedule.*.date'  => 'required|date',
            'schedule.*.fromTime' => 'required',
            'schedule.*.toTime'   => 'required',
            'students'         => 'required|array|min:1',
            'students.*'       => 'exists:students,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Determine the status based on today's date and the provided start/end dates
        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['final_exam_date']);
        $today = Carbon::now();

        $status = 'upcoming'; // default if 'start_date' has not arrived yet

        if ($today->lt($start)) {
            // Today is before the start date => 'upcoming'
            $status = 'upcoming';
        } elseif ($today->gt($end)) {
            // Today is after the end date => 'completed'
            $status = 'completed';
        } else {
            // Otherwise => 'ongoing'
            $status = 'ongoing';
        }

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
            'course_type_id'   => 'required|exists:course_types,id',
            'group_type_id'    => 'required|exists:group_types,id',
            'instructor_id'    => 'required|exists:users,id',
            'start_date'       => 'required|date',
            'mid_exam_date'    => 'required|date',
            'final_exam_date'  => 'required|date',
            'student_capacity' => 'required|integer|min:1',
            'schedule'         => 'required|array|min:1',
            'schedule.*.day'   => 'required|string',
            'schedule.*.date'  => 'required|date',
            'schedule.*.fromTime' => 'required',
            'schedule.*.toTime'   => 'required',
            'students'         => 'required|array|min:1',
            'students.*'       => 'exists:students,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Keep track of original fields for comparison
        // (We'll only examine fields we expect might change)
        $original = [
            'course_type_id'   => $course->course_type_id,
            'group_type_id'    => $course->group_type_id,
            'instructor_id'    => $course->instructor_id,
            'start_date'       => $course->start_date,
            'mid_exam_date'    => $course->mid_exam_date,
            'final_exam_date'  => $course->final_exam_date,
            'end_date'         => $course->end_date,
            'student_capacity' => $course->student_capacity,
            // We won't track schedules/students this way, as they are arrays/relationships.
        ];

        // Determine new status based on today's date vs. final_exam_date
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

        // Update the course main fields
        $course->update([
            'course_type_id'   => $data['course_type_id'],
            'group_type_id'    => $data['group_type_id'],
            'instructor_id'    => $data['instructor_id'],
            'start_date'       => $data['start_date'],
            'mid_exam_date'    => $data['mid_exam_date'],
            'final_exam_date'  => $data['final_exam_date'],
            'end_date'         => $data['final_exam_date'],
            'student_capacity' => $data['student_capacity'],
            'status'           => $status,
        ]);

        // Refresh the course so we have updated fields for comparison
        $course->refresh();

        // Rebuild schedules: remove old, insert new
        $course->schedules()->delete();
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

        // Build a description of changed fields
        $changedFields = [];
        // Compare each relevant field
        foreach ($original as $field => $oldVal) {
            $newVal = $course->{$field};
            if ($oldVal != $newVal) {
                $changedFields[] = "$field: [$oldVal] => [$newVal]";
            }
        }

        // If schedules or students changed, you can note that too
        // For example, if your app wants to note it specifically:
        // e.g. $changedFields[] = "Schedules updated";
        // e.g. $changedFields[] = "Students synced";

        // Combine changes into one string
        $changesDescription = implode('; ', $changedFields);
        if (!$changesDescription) {
            $changesDescription = "No direct field changes (relationships may have changed)";
        }

        // Create the Audit Log
        $courseName = $course->courseType->name ?? '(no name)';
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Updated course #{$course->id} ({$courseName}) | Changes: {$changesDescription}",
            'type'        => 'update',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);

        return response()->json([
            'message' => 'Course updated successfully',
            'course'  => $course
        ], 200);
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
        $courseTypes = CourseType::where('status','active')->get();
        $instructors = User::role('instructor')->get();
        $students = Student::get();

        return response()->json([
            'groupTypes' => $groupTypes,
            'courseTypes' => $courseTypes,
            'instructors' => $instructors,
            'students' => $students,
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
        
        CourseStudent::create([
            'course_id' => $id,
            'student_id' => $request->student_id,
            'status' => 'ongoing',
        ]);

        $course = Course::findOrFail($id);

        AuditLog::create([
            'user_id'     => Auth::id(),
            "description" => "Enrolled student #{$request->student_id} in course #{$course->id}",
            'type'        => 'enroll',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Student enrolled successfully.');
    }

}
