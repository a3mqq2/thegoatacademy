<?php

namespace App\Http\Controllers\Admin;

use App\Models\Course;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ExcludeReason;
use App\Models\WithdrawnReason;
use App\Http\Controllers\Controller;

class CourseStudentController extends Controller
{
    public function exclude(Request $request, $courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        $student = Student::findOrFail($studentId);

        // Validate the chosen reason
        $request->validate([
            'exclude_reason_id' => 'required|exists:exclude_reasons,id',
        ]);

        $course->decrement('student_count', 1);
        $course->save();

        // Update the pivot record with status and exclude reason
        $course->students()->updateExistingPivot($student->id, [
            'exclude_reason_id' => $request->input('exclude_reason_id'),
            'withdrawn_reason_id' => null,  // Clear any withdrawn reason if needed
            'status' => 'excluded', // Update status
        ]);

        // Log the action in audit logs
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Excluded student '{$student->name}' from course '{$course->courseType->name}' (ID: {$course->id})",
            'type'        => 'update',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);

        return redirect()->back()->with('success', 'Student excluded successfully.');
    }

    public function withdraw(Request $request, $courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        $student = Student::findOrFail($studentId);
    
        $request->validate([
            'withdrawn_reason_id' => 'required|exists:withdrawn_reasons,id',
        ]);
    
        $course->students()->updateExistingPivot($student->id, [
            'withdrawn_reason_id' => $request->input('withdrawn_reason_id'),
            'exclude_reason_id'   => null,
            'status'             => 'withdrawn', 
        ]);
    
        // Decrement student_count if it's > 0
        if ($course->student_count > 0) {
            $course->student_count -= 1;
        }
        $course->save();
    
        $withdrawnCount = $course->students()
            ->wherePivot('status', 'withdrawn')
            ->count();
    
        if ($withdrawnCount >= 2) {
            $course->status = 'paused';
            $course->save();
        }
    
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => "Withdrawn student '{$student->name}' from course '{$course->courseType->name}' (ID: {$course->id})",
            'type'        => 'update',
            'entity_id'   => $course->id,
            'entity_type' => Course::class,
        ]);
    
        return redirect()->back()->with('success', 'Student withdrawn successfully.');
    }
    
}
