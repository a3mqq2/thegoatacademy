<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total students
        $students = Student::count();
    
        // Count courses based on status
        $upcoming_courses = Course::where('status', 'upcoming')->count();
        $ongoing_courses  = Course::where('status', 'ongoing')->count();
        $completed_courses = Course::where('status', 'completed')->count();
    
        // Fetch New Users Data (for the chart)
        $todayCount = Student::whereDate('created_at', Carbon::today())->count();
        $weeklyCount = Student::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $monthlyCount = Student::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->count();
    
        // Fetch Students Created per Day (Last 7 Days)
        $chartData = Student::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($data) {
                return [
                    'x' => $data->date, // Date as X-Axis
                    'y' => [0, $data->count] // Y-Axis Range Data
                ];
            });
    
        return view('admin.dashboard', compact(
            'students', 'upcoming_courses', 'ongoing_courses', 'completed_courses',
            'todayCount', 'weeklyCount', 'monthlyCount', 'chartData'
        ));
    }
    
}
