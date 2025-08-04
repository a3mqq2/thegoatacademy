<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Models\CourseSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CourseScheduleController extends Controller
{
    /**
     * Store a new schedule for the course
     */
    public function store(Request $request, Course $course)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'day' => 'required|integer|between:0,6',
            'from_time' => 'required|date_format:H:i',
            'to_time' => 'required',
            'extra_date' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();


            $hoursForClose = (int) Setting::where('key', 'Updating the students’ Attendance after the class.')
                                          ->value('value');


            $closeAt = Carbon::parse($request->date . ' ' . $request->to_time)
                ->addHours($hoursForClose)
                ->format('Y-m-d H:i:s');

            $schedule = $course->schedules()->create([
                'date' => $request->date,
                'day' => $request->day,
                'from_time' => $request->from_time,
                'to_time' => $request->to_time,
                'close_at' => $closeAt,
                'extra_date' => $request->boolean('extra_date') == true ? now() : null,
                'status' => 'pending'
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Added new schedule to course #{$course->id} for date {$request->date}",
                'type' => 'schedule_create',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Schedule added successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating schedule: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error creating schedule. Please try again.');
        }
    }

    /**
     * Update an existing schedule
     */
    public function update(Request $request, Course $course, CourseSchedule $schedule)
    {
        $request->validate([
            'date' => 'required|date',
            'day' => 'required|integer|between:0,6',
            'from_time' => 'required|date_format:H:i',
            'to_time' => 'required|date_format:H:i|after:from_time',
            'extra_date' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate schedules on the same date (excluding current schedule)
            $existingSchedule = $course->schedules()
                ->where('date', $request->date)
                ->where('id', '!=', $schedule->id)
                ->first();

            if ($existingSchedule) {
                return redirect()->back()->with('error', 'A schedule already exists for this date.');
            }

            $oldData = $schedule->toArray();

            // Update close_at time if date or to_time changed
            $closeAt = Carbon::parse($request->date . ' ' . $request->to_time)
                ->addHours(2)
                ->format('Y-m-d H:i:s');

            $schedule->update([
                'date' => $request->date,
                'day' => $request->day,
                'from_time' => $request->from_time,
                'to_time' => $request->to_time,
                'close_at' => $closeAt,
                'extra_date' => $request->boolean('extra_date') == true ? now() : null,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Updated schedule #{$schedule->id} in course #{$course->id}",
                'type' => 'schedule_update',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Schedule updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating schedule: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating schedule. Please try again.');
        }
    }

    /**
     * Delete a schedule
     */
    public function destroy(Course $course, CourseSchedule $schedule)
    {
        try {
            DB::beginTransaction();

            // Check if attendance has been taken
            if ($schedule->attendances()->exists()) {
                return redirect()->back()->with('error', 'Cannot delete schedule with existing attendance records.');
            }

            $scheduleData = $schedule->toArray();

            $schedule->delete();

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Deleted schedule #{$schedule->id} from course #{$course->id}",
                'type' => 'schedule_delete',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Schedule deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting schedule: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error deleting schedule. Please try again.');
        }
    }

    /**
     * Update schedule status via AJAX
     */
    public function updateStatus(Request $request, Course $course, CourseSchedule $schedule)
    {
        $request->validate([
            'status' => 'required|in:pending,done,absent'
        ]);

        try {
            $oldStatus = $schedule->status;
            
            $schedule->update([
                'status' => $request->status
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => Auth::id(),
                'description' => "Updated schedule #{$schedule->id} status from {$oldStatus} to {$request->status} in course #{$course->id}",
                'type' => 'schedule_status_update',
                'entity_id' => $course->id,
                'entity_type' => Course::class,
            ]);

            // Return JSON for AJAX requests, redirect for regular requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Schedule status updated successfully',
                    'new_status' => $request->status
                ]);
            }

            return response()->json([], 200);
        } catch (\Exception $e) {
            Log::error('Error updating schedule status: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating schedule status'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error updating schedule status.');
        }
    }
}