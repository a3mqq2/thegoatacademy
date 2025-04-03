<?php

namespace App\Http\Controllers\ExamOfficer;

use App\Models\Course;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $courses = Course::whereIn('status', ['upcoming', 'ongoing'])->get();
    
        $logs = \App\Models\AuditLog::where('type', 'update_exam_dates')
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();
    
        return view('exam_officer.dashboard', compact('courses', 'logs'));
    }
    
    public function updateExamDates(Request $request, Course $course)
    {
        $originalMid   = $course->mid_exam_date;
        $originalFinal = $course->final_exam_date;
    
        $course->update([
            'mid_exam_date'   => $request->mid_exam_date,
            'final_exam_date' => $request->final_exam_date
        ]);
    
        $changes = [];
    
        if ($originalMid != $request->mid_exam_date) {
            $changes[] = "Mid Exam Date changed from [$originalMid] to [{$request->mid_exam_date}]";
        }
    
        if ($originalFinal != $request->final_exam_date) {
            $changes[] = "Final Exam Date changed from [$originalFinal] to [{$request->final_exam_date}]";
        }
    
        if (count($changes)) {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'description' => 'Edited course exam dates (Course ID: ' . $course->id . '): ' . implode(' | ', $changes),
                'type'        => 'update_exam_dates',
                'entity_id'   => $course->id,
                'entity_type' => Course::class,
            ]);
        }
    
        return response()->json(['success' => true]);
    }
    
}
