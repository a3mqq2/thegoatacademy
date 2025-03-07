<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $users =  User::count();
        $students =  Student::count();
        // cources by status count
        $upcoming_courses =  Course::where('status', 'upcoming')->count();
        $ongoing_courses =  Course::where('status', 'ongoing')->count();
        $completed_courses =  Course::where('status', 'completed')->count();
        $cancelled_courses =  Course::where('status', 'cancelled')->count();
        
        $data = [
            'users' => $users,
            'students' => $students,
            'upcoming_courses' => $upcoming_courses,
            'ongoing_courses' => $ongoing_courses,
            'completed_courses' => $completed_courses,
            'cancelled_courses' => $cancelled_courses,
        ];
        return view('admin.dashboard',$data);
    }
}
