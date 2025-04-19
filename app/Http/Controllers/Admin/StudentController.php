<?php

namespace App\Http\Controllers\Admin;

use App\Models\Skill;
use App\Models\Course;
use App\Models\Student;
use App\Models\AuditLog;
use App\Models\CourseType;
use App\Models\StudentFile;
use Illuminate\Http\Request;
use App\Models\ExcludeReason;
use App\Models\WithdrawnReason;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        // if ($request->filled('name')) {
        //     $query->where('name', 'like', '%' . $request->input('name') . '%');
        // }

        // if ($request->filled('phone')) {
        //     $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        // }

        // if ($request->filled('status')) {
        //     $query->where('status', $request->status);
        // }

        // if ($request->filled('books_due')) {
        //     $query->where('books_due', $request->books_due);
        // }

        // if ($request->filled('city')) {
        //     $query->where('city', 'like', '%' . $request->input('city') . '%');
        // }

        // if ($request->filled('age')) {
        //     $query->where('age', $request->age);
        // }

        if(request('search'))
        {
            $query->where('name', 'like', "%" . request('search') . "%")
            ->orWhere('city','like', '%' . request('search') . '%')
            ->orWhere('specialization','like', '%' . request('search') . '%')
            ->orWhere('phone','like', '%' . request('search') . '%');
        }

        // if ($request->filled('specialization')) {
        //     $query->where('specialization', 'like', '%' . $request->input('specialization') . '%');
        // }


        $students = $query->orderByDesc('id')->paginate(10);

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        $skills = Skill::all();
        return view('admin.students.create', compact('skills'));
    }

    public function store(Request $request)
    {
        // 1) Validate basic Student fields as before
        $validatedData = $request->validate([
            'name'              => 'required|string|max:255',
            'phone'             => 'required|string|max:20',
            'city'              => 'nullable|string|max:255',
            'age'               => 'nullable|integer|min:1|max:100',
            'specialization'    => 'nullable|string|max:255',
            'gender'            => 'required|in:male,female',
            'avatar'            => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emergency_phone'   => 'nullable|string|max:20',
            'skills'            => 'nullable|array',
    
            // 2) Validate the arrays for multiple files
            //    "file_names.*" => each file name in the table row
            //    "files.*"      => the actual file in the table row
            'file_names'        => 'array',
            'file_names.*'      => 'required|string|max:255',
            'files'             => 'array',
            'files.*'           => 'file|max:2048', // add mime types if you wish
        ]);
    
        // 3) Handle single avatar upload (if any)
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = $avatarPath;
        }
    
        // 4) Create the Student
        $student = Student::create($validatedData);
    
        // 5) Attach any skills
        if (isset($validatedData['skills'])) {
            $student->skills()->attach($validatedData['skills']);
        }
    
        // 6) Handle the multiple file uploads (if any)
        //    For each row in the table, we have one file name + one file
        if ($request->has('file_names') && $request->hasFile('files')) {
            $fileNames = $request->input('file_names'); // array of text inputs
            $files     = $request->file('files');       // array of uploaded files
    
            foreach ($files as $index => $file) {
                // 7) Store the file
                $path = $file->store('student_files', 'public');
    
                // 8) Create a record in "student_files" table 
                //    (assuming you have a "StudentFile" model & migration)
                StudentFile::create([
                    'student_id' => $student->id,
                    'name'       => $fileNames[$index],  // name from the text input
                    'path'       => $path,
                ]);
            }
        }
    
        // 9) Return JSON or redirect, based on your needs
        if ($request->wantsJson()) {
            return response()->json(['student' => $student], 201);
        }
    
        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student created successfully along with multiple files.');
    }
    

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        $skills = Skill::all();
        return view('admin.students.edit', compact('student','skills'));
    }

    public function update(Request $request, Student $student)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'city' => 'nullable|string|max:255',
            'age' => 'nullable|integer',
            'specialization' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'emergency_phone' => 'nullable|string|max:20',
            'skills' => 'nullable|array',
        ]);
    
        if ($request->hasFile('avatar')) {
            if ($student->avatar) {
                Storage::disk('public')->delete($student->avatar);
            }
            $validatedData['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
    
        $student->update($validatedData);
    

        if (isset($validatedData['skills'])) {
            $student->skills()->sync($validatedData['skills']);
        } else {
            $student->skills()->detach();
        }

    
        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully.');
    }


    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted student: ' . $student->name,
            'type' => 'delete',
            'entity_id' => $student->id,
            'entity_type' => Student::class,
        ]);

        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully.');
    }

    public function exclude($id)
    {
        $student = Student::findOrFail($id);
        $student->update(['status' => 'excluded']);


        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Excluded student: ' . $student->name,
            'type' => 'exclude',
            'entity_id' => $student->id,
            'entity_type' => Student::class,
        ]);

        return redirect()->back()->with('success', 'Student excluded successfully.');
    }

    public function withdraw(Request $request, $id)
    {
        // Basic validation for the reason
        $validator = Validator::make($request->all(), [
            'withdrawal_reason' => 'required|string|max:500',
            'course_id'         => 'required|integer|exists:courses,id', // pass the course_id
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        // Find the student & course
        $student = Student::findOrFail($id);
        $course  = Course::findOrFail($request->course_id);

        // Update the pivot table to set the student's status to 'withdrawn'
        // Example: if you have a course_student pivot with a 'status' column
        // (If your setup is different, adapt accordingly)
        $course->students()->updateExistingPivot($student->id, [
            'status' => 'withdrawn',
            'withdrawal_reason' => $request->withdrawal_reason,
        ]);

        // Decrement the course's student_count column
        if ($course->student_count > 0) {
            $course->student_count -= 1;
        }
        $course->save();

        // Count how many students have pivot status = withdrawn
        $withdrawnCount = $course->students()
            ->wherePivot('status', 'withdrawn')
            ->count();

        // If 2 or more students are withdrawn, set course->status = 'paused'
        if ($withdrawnCount >= 2) {
            $course->status = 'paused';
            $course->save();
        }

        // If you store "status" on the student model itself (like your snippet),
        // then also do something like:
        $student->update([
            'status'            => 'withdrawn',
            'withdrawal_reason' => $request->withdrawal_reason
        ]);

        // Log the action
        AuditLog::create([
            'user_id'      => Auth::id(),
            'description'  => 'Withdrawn student: ' . $student->name . ' from course ' . $course->name 
                            . ' - Reason: ' . $request->withdrawal_reason,
            'type'         => 'withdraw',
            'entity_id'    => $student->id,
            'entity_type'  => Student::class,
        ]);

        return redirect()->back()->with('success', 'Student withdrawn successfully.');
    }



    public function show($id)
    {
        $student = Student::with(['courses'])->find($id);

        $skills = $student->skills->pluck('id')->toArray();

        $course_types = CourseType::whereHas('skills', function($query) use ($skills) {
            $query->whereIn('skills.id', $skills);
        })->get();

        $courses = Course::with(["instructor", "courseType", "groupType"])
            ->whereIn('course_type_id', $course_types->pluck('id')->toArray())
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereDoesntHave('students', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->whereNotIn('course_students.status', ['withdrawn', 'excluded']);
            })
            ->withCount('students')
            ->get();

        $withdrawnReasons = WithdrawnReason::all();
        $excludeReasons = ExcludeReason::all();
        return view('admin.students.show', compact('student', 'courses', 'withdrawnReasons', 'excludeReasons'));
    }


    public function print_suggestion_courses($id)
    {
        $student = Student::with(['courses'])->find($id);

        $skills = $student->skills->pluck('id')->toArray();

        $course_types = CourseType::whereHas('skills', function($query) use ($skills) {
            $query->whereIn('skills.id', $skills);
        })->get();

        $courses = Course::with(["instructor", "courseType", "groupType"])
            ->whereIn('course_type_id', $course_types->pluck('id')->toArray())
            ->whereIn('status', ['upcoming', 'ongoing'])
            ->whereDoesntHave('students', function ($query) use ($student) {
                $query->where('student_id', $student->id)
                    ->whereNotIn('course_students.status', ['withdrawn', 'excluded']);
            })
            ->withCount('students')
            ->get();

        return view('admin.students.print_suggestion_courses', compact('student', 'courses'));
    }
}
