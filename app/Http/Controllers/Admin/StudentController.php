<?php

namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('books_due')) {
            $query->where('books_due', $request->books_due);
        }

        $students = $query->paginate(10);

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:15|unique:students',
        ]);

        if ($validator->fails()) {
            if(request()->wantsJson())
            {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $student = Student::create($request->only(['name', 'phone','books_due']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Created a new student: ' . $student->name,
            'type' => 'create',
            'entity_id' => $student->id,
            'entity_type' => Student::class,
        ]);


        if(request()->wantsJson())
        {
            return response()->json(['student' => $student], 200);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student created successfully.');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $oldValues = $student->getOriginal();

        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:15|unique:students,phone,' . $student->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $student->update($request->only(['name', 'phone', 'books_due']));

        $newValues = $student->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Updated student: ' . $student->name . ' (' . implode(', ', $changesDescription) . ')',
            'type' => 'update',
            'entity_id' => $student->id,
            'entity_type' => Student::class,
        ]);

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

        return redirect()->route('admin.students.index')->with('success', 'Student excluded successfully.');
    }

    public function withdraw(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'withdrawal_reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $student->update(['status' => 'withdrawn', 'withdrawal_reason' => $request->withdrawal_reason]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Withdrawn student: ' . $student->name . ' - Reason: ' . $request->withdrawal_reason,
            'type' => 'withdraw',
            'entity_id' => $student->id,
            'entity_type' => Student::class,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Student withdrawn successfully.');
    }
}
