<?php

namespace App\Http\Controllers\Admin;

use App\Models\GroupType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class GroupTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = GroupType::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('student_capacity')) {
            $query->where('student_capacity', '>=', $request->student_capacity);
        }

        $groupTypes = $query->paginate(10);

        return view('admin.group_types.index', compact('groupTypes'));
    }

    public function create()
    {
        return view('admin.group_types.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255|unique:group_types',
            'student_capacity' => 'required|integer|min:1',
            'status'           => 'required|in:active,inactive',
            'lesson_duration'  => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $groupType = GroupType::create($request->only(['name', 'student_capacity', 'status', 'lesson_duration']));

        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Created a new group type: ' . $groupType->name,
            'type'        => 'create',
            'entity_id'   => $groupType->id,
            'entity_type' => GroupType::class,
        ]);

        return redirect()->route('admin.group-types.index')->with('success', 'Group Type created successfully.');
    }

    public function edit($id)
    {
        $groupType = GroupType::findOrFail($id);
        return view('admin.group_types.edit', compact('groupType'));
    }

    public function update(Request $request, $id)
    {
        $groupType = GroupType::findOrFail($id);
        $oldValues = $groupType->getOriginal();

        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255|unique:group_types,name,' . $groupType->id,
            'student_capacity' => 'required|integer|min:1',
            'status'           => 'required|in:active,inactive',
            'lesson_duration'  => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $groupType->update($request->only(['name', 'student_capacity', 'status', 'lesson_duration']));

        $newValues = $groupType->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Updated group type: ' . $groupType->name . ' (' . implode(', ', $changesDescription) . ')',
            'type'        => 'update',
            'entity_id'   => $groupType->id,
            'entity_type' => GroupType::class,
        ]);

        return redirect()->route('admin.group-types.index')->with('success', 'Group Type updated successfully.');
    }

    public function destroy($id)
    {
        $groupType = GroupType::findOrFail($id);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Deleted group type: ' . $groupType->name,
            'type'        => 'delete',
            'entity_id'   => $groupType->id,
            'entity_type' => GroupType::class,
        ]);

        $groupType->delete();
        return redirect()->route('admin.group-types.index')->with('success', 'Group Type deleted successfully.');
    }

    public function toggle($id)
    {
        $groupType = GroupType::findOrFail($id);
        $oldStatus = $groupType->status;
        $groupType->status = $groupType->status == 'active' ? 'inactive' : 'active';
        $groupType->save();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Toggled group type status: ' . $groupType->name . ' from ' . $oldStatus . ' to ' . $groupType->status,
            'type'        => 'toggle_status',
            'entity_id'   => $groupType->id,
            'entity_type' => GroupType::class,
        ]);

        return redirect()->route('admin.group-types.index')->with('success', 'Group Type status updated successfully.');
    }
}
