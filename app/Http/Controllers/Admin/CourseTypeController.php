<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CourseTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseType::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('duration')) {
            $query->where('duration', $request->duration);
        }

        $courseTypes = $query->paginate(10);

        return view('admin.course_types.index', compact('courseTypes'));
    }

    public function create()
    {
        return view('admin.course_types.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255|unique:course_types',
            'status'   => 'required|in:active,inactive',
            'duration' => 'nullable|in:week,month,half_year',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $courseType = CourseType::create($request->only(['name', 'status', 'duration']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Created a new course type: ' . $courseType->name,
            'type' => 'create',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type created successfully.');
    }

    public function edit($id)
    {
        $courseType = CourseType::findOrFail($id);
        return view('admin.course_types.edit', compact('courseType'));
    }

    public function update(Request $request, $id)
    {
        $courseType = CourseType::findOrFail($id);
        $oldValues = $courseType->getOriginal();

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255|unique:course_types,name,' . $courseType->id,
            'status'   => 'required|in:active,inactive',
            'duration' => 'nullable|in:week,month,half_year',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $courseType->update($request->only(['name', 'status', 'duration']));

        $newValues = $courseType->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Updated course type: ' . $courseType->name . ' (' . implode(', ', $changesDescription) . ')',
            'type' => 'update',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type updated successfully.');
    }

    public function destroy($id)
    {
        $courseType = CourseType::findOrFail($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted course type: ' . $courseType->name,
            'type' => 'delete',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        $courseType->delete();
        return redirect()->route('admin.course-types.index')->with('success', 'Course Type deleted successfully.');
    }

    public function toggle($id)
    {
        $courseType = CourseType::findOrFail($id);
        $oldStatus = $courseType->status;
        $courseType->status = $courseType->status == 'active' ? 'inactive' : 'active';
        $courseType->save();

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Toggled course type status: ' . $courseType->name . ' from ' . $oldStatus . ' to ' . $courseType->status,
            'type' => 'toggle_status',
            'entity_id' => $courseType->id,
            'entity_type' => CourseType::class,
        ]);

        return redirect()->route('admin.course-types.index')->with('success', 'Course Type status updated successfully.');
    }
}
