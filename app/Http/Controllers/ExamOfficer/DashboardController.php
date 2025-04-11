<?php

namespace App\Http\Controllers\ExamOfficer;

use Carbon\Carbon;
use App\Models\Exam;
use App\Models\Course;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if user is an Exam Manager
        $isExamManager = $user->permissions->contains('name', 'Exam Manager');

        // Basic queries - either show all or only for assigned examiner
        if ($isExamManager) {
            $totalExams      = Exam::count();
            $newExams        = Exam::where('status', 'new')->count();
            $pendingExams    = Exam::where('status', 'pending')->count();
            $completedExams  = Exam::where('status', 'completed')->count();
            $overdueExams    = Exam::where('status', 'overdue')
            ->count();
            // Full set for "todayExams" and "upcomingExams"
            $todayExams      = Exam::whereDate('exam_date', Carbon::today())->with('course')->get();
            $upcomingExams   = Exam::whereDate('exam_date','>', Carbon::today())
                                   ->orderBy('exam_date')
                                   ->take(5)
                                   ->with('course')
                                   ->get();
        } else {
            // Show only if user is assigned as examiner
            $totalExams      = Exam::where('examiner_id', $user->id)->count();
            $newExams        = Exam::where('examiner_id', $user->id)->where('status', 'new')->count();
            $pendingExams    = Exam::where('examiner_id', $user->id)->where('status', 'pending')->count();
            $completedExams  = Exam::where('examiner_id', $user->id)->where('status', 'completed')->count();
            $overdueExams    = Exam::where('examiner_id', $user->id)
            ->where('status','overdue')
            ->count();
            // Only user’s exams for "todayExams" and "upcomingExams"
            $todayExams      = Exam::where('examiner_id', $user->id)
                                   ->whereDate('exam_date', Carbon::today())
                                   ->with('course')
                                   ->get();
            $upcomingExams   = Exam::where('examiner_id', $user->id)
                                   ->whereDate('exam_date','>', Carbon::today())
                                   ->orderBy('exam_date')
                                   ->take(5)
                                   ->with('course')
                                   ->get();
        }

        // Last 10 Audit logs related to "exams"
        $recentLogs = AuditLog::where('type', 'exams')
                              ->orderByDesc('created_at')
                              ->limit(10)
                              ->get();

        return view('exam_officer.dashboard', compact(
            'isExamManager',
            'totalExams',
            'newExams',
            'pendingExams',
            'completedExams',
            'overdueExams',
            'todayExams',
            'upcomingExams',
            'recentLogs'
        ));
    }
    
    public function logs()
    {
        $logs = \App\Models\AuditLog::where('type', 'exams')
                    ->orderByDesc('created_at')
                    ->limit(20)
                    ->get();
    
        return view('exam_officer.logs', compact('logs'));
    }
}
