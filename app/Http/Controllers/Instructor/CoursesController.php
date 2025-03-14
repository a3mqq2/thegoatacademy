<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            "status" => "required|in:upcoming,ongoing",
        ]);

        $courses = auth()->user()->courses()->where('status', $request->status)->paginate(20);
        return view('instructor.courses.index', compact('courses'));
    }
}
