<?php

namespace App\Http\Controllers\Admin;

use App\Models\CourseType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Skill;
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
        $skills = Skill::all();
        return view('admin.course_types.create', compact('skills'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:255|unique:course_types',
            'status'                    => 'required|in:active,inactive',
            'duration'                  => 'nullable',
            'skills'                    => 'nullable|array',
            'skill_grades'              => 'nullable|array',
            'skill_grades.*.mid_max'    => 'required|numeric|min:0',
            'skill_grades.*.final_max'  => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        // Create the course type from the validated request data.
        $courseType = CourseType::create($request->only(['name', 'status', 'duration']));
    
        // If the dynamic skill grades data is provided, attach each skill with pivot data.
        if ($request->filled('skill_grades')) {
            $pivotData = [];
            foreach ($request->input('skill_grades') as $skillId => $grades) {
                $pivotData[$skillId] = [
                    'mid_max'   => $grades['mid_max'] ?? 0,
                    'final_max' => $grades['final_max'] ?? 0,
                ];
            }
            $courseType->skills()->attach($pivotData);
        } elseif ($request->filled('skills')) {
            // Fallback: if only skills are provided without grade details.
            $courseType->skills()->attach($request->skills);
        }
    
        // Log the creation event.
        AuditLog::create([
            'user_id'     => Auth::id(),
            'description' => 'Created a new course type: ' . $courseType->name,
            'type'        => 'create',
            'entity_id'   => $courseType->id,
            'entity_type' => CourseType::class,
        ]);
    
        return redirect()->route('admin.course-types.index')->with('success', 'Course Type created successfully.');
    }
    

    public function edit($id)
    {
        $courseType = CourseType::findOrFail($id);
        $skills = Skill::all();
        return view('admin.course_types.edit', compact('courseType', 'skills'));
    }

  
    public function update(Request $request, CourseType $courseType)
    {
        $validator = Validator::make($request->all(), [
            'name'                      => 'required|string|max:255|unique:course_types,name,' . $courseType->id,
            'status'                    => 'required|in:active,inactive',
            'duration'                  => 'nullable',
            'skills'                    => 'nullable|array',
            'skill_grades'              => 'nullable|array',
            'skill_grades.*.mid_max'    => 'required|numeric|min:0',
            'skill_grades.*.final_max'  => 'required|numeric|min:0',
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        $originalData = $courseType->getOriginal();
    
        $courseType->update($request->only(['name', 'status', 'duration']));
    
        if ($request->filled('skill_grades')) {
            $pivotData = [];
            foreach ($request->input('skill_grades') as $skillId => $grades) {
                $pivotData[$skillId] = [
                    'mid_max'   => $grades['mid_max'] ?? 0,
                    'final_max' => $grades['final_max'] ?? 0,
                ];
            }
            $courseType->skills()->sync($pivotData);
        } elseif ($request->filled('skills')) {
            // Fallback: attach skills with default pivot values if grades are not provided.
            $pivotData = [];
            foreach ($request->input('skills') as $skillId) {
                $pivotData[$skillId] = ['mid_max' => 0, 'final_max' => 0];
            }
            $courseType->skills()->sync($pivotData);
        } else {
            $courseType->skills()->detach();
        }
    
        $changes = [];
        foreach ($courseType->getChanges() as $field => $newValue) {
            $changes[] = ucfirst($field) . ": " . ($originalData[$field] ?? 'N/A') . " → " . $newValue;
        }
    
        if (!empty($changes)) {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'description' => 'Updated course type: ' . $courseType->name . ' (' . implode(', ', $changes) . ')',
                'type'        => 'update',
                'entity_id'   => $courseType->id,
                'entity_type' => CourseType::class,
            ]);
        }
    
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
