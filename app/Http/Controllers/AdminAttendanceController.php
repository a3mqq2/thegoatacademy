<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSchedule;
use App\Models\Attendance;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    /**
     * Take attendance with admin override capability
     */
    public function take(Request $request, Course $course, CourseSchedule $courseSchedule)
    {
        try {
            // Admin can always access, regardless of time restrictions
            $isAdmin = true;

            // Load course with students and existing attendance for this schedule
            $course->load([
                'students' => function ($query) {
                    $query->wherePivot('status', 'ongoing');
                }
            ]);

            // Get existing attendance records for this schedule
            $existingAttendance = $courseSchedule->attendances()
                ->with('student')
                ->get()
                ->keyBy('student_id');

            // Prepare students data with existing attendance
            $students = $course->students->map(function ($student) use ($existingAttendance) {
                $attendance = $existingAttendance->get($student->id);
                
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'phone' => $student->phone,
                    'absencesCount' => $this->getStudentAbsencesCount($student, $course),
                    'attendancePresent' => $attendance ? ($attendance->attendance == 'present') : false,
                    'homeworkSubmitted' => $attendance ? ($attendance->homework_submitted == 1) : false,
                    'notes' => $attendance ? $attendance->notes : '',
                    'existing_id' => $attendance ? $attendance->id : null,
                    'pivot' => [
                        'status' => $student->pivot->status
                    ]
                ];
            });

            // Return JSON response for API calls or view for web requests
            if ($request->expectsJson()) {
                return response()->json([
                    'course' => [
                        'id' => $course->id,
                        'students' => $students
                    ],
                    'schedule' => [
                        'id' => $courseSchedule->id,
                        'date' => $courseSchedule->date,
                        'from_time' => $courseSchedule->from_time,
                        'to_time' => $courseSchedule->to_time,
                        'close_at' => $courseSchedule->close_at,
                        'attendance_taken_at' => $courseSchedule->attendance_taken_at,
                        'attendances' => $courseSchedule->attendances
                    ],
                    'isAdmin' => $isAdmin
                ]);
            }

            // For web requests, return view with admin flag
            return view('admin.courses.attendance', compact('course', 'courseSchedule', 'isAdmin'));

        } catch (\Exception $e) {
            Log::error('Error loading admin attendance: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to load attendance data'], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to load attendance data.');
        }
    }

    /**
     * Store attendance with admin override capability
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'course_schedule_id' => 'required|exists:course_schedules,id',
            'students' => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.attendance' => 'required|in:present,absent',
            'students.*.homework_submitted' => 'required|boolean',
            'students.*.notes' => 'nullable|string|max:500',
            'students.*.existing_id' => 'nullable|exists:attendances,id',
            'admin_override' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $courseSchedule = CourseSchedule::findOrFail($request->course_schedule_id);
            $isAdminOverride = $request->boolean('admin_override');

            foreach ($request->students as $studentData) {
                if (isset($studentData['existing_id']) && $studentData['existing_id']) {
                    // Update existing attendance
                    $attendance = Attendance::findOrFail($studentData['existing_id']);
                    $attendance->update([
                        'attendance' => $studentData['attendance'],
                        'homework_submitted' => $studentData['homework_submitted'],
                        'notes' => $studentData['notes'] ?? '',
                        'updated_by_admin' => true,
                        'admin_override' => $isAdminOverride
                    ]);
                } else {
                    // Create new attendance record
                    Attendance::create([
                        'course_schedule_id' => $courseSchedule->id,
                        'student_id' => $studentData['student_id'],
                        'attendance' => $studentData['attendance'],
                        'homework_submitted' => $studentData['homework_submitted'],
                        'notes' => $studentData['notes'] ?? '',
                        'created_by_admin' => true,
                        'admin_override' => $isAdminOverride
                    ]);
                }
            }

            // Mark attendance as taken
            $courseSchedule->update([
                'attendance_taken_at' => now(),
                'status' => 'done'
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Took attendance for course #{$course->id} on {$courseSchedule->date}" . ($isAdminOverride ? ' (Admin Override)' : ''),
                'type' => 'attendance_taken',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            $message = 'Attendance has been saved successfully' . ($isAdminOverride ? ' (Admin Override)' : '') . '!';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving admin attendance: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving attendance. Please try again later.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error saving attendance. Please try again later.');
        }
    }

    /**
     * Get student's absences count for the course
     */
    private function getStudentAbsencesCount($student, $course)
    {
        return Attendance::whereHas('courseSchedule', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })
        ->where('student_id', $student->id)
        ->where('attendance', 'absent')
        ->count();
    }

    /**
     * Get detailed attendance statistics for admin
     */
    public function getAttendanceStats(Course $course)
    {
        try {
            $stats = [];
            
            // Overall course attendance statistics
            $totalSchedules = $course->schedules()->where('status', 'done')->count();
            $totalStudents = $course->students()->wherePivot('status', 'ongoing')->count();
            
            // Attendance rate calculation
            $totalPossibleAttendances = $totalSchedules * $totalStudents;
            $totalPresentAttendances = Attendance::whereHas('courseSchedule', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->where('attendance', 'present')->count();
            
            $attendanceRate = $totalPossibleAttendances > 0 
                ? ($totalPresentAttendances / $totalPossibleAttendances) * 100 
                : 0;

            $stats = [
                'total_schedules' => $totalSchedules,
                'total_students' => $totalStudents,
                'attendance_rate' => round($attendanceRate, 2),
                'total_present' => $totalPresentAttendances,
                'total_absent' => $totalPossibleAttendances - $totalPresentAttendances
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting attendance stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading attendance statistics'
            ], 500);
        }
    }
}