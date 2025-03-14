<?php

namespace App\Http\Controllers\Admin;

use App\Models\Skill;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function index(Request $request)
    {
        $query = Skill::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $skills = $query->paginate(10);

        return view('admin.skills.index', compact('skills'));
    }

    public function create()
    {
        return view('admin.skills.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:skills',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $skill = Skill::create($request->only(['name']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Created a new skill: ' . $skill->name,
            'type' => 'create',
            'entity_id' => $skill->id,
            'entity_type' => Skill::class,
        ]);

        return redirect()->route('admin.skills.index')->with('success', 'Skill created successfully.');
    }

    public function edit($id)
    {
        $skill = Skill::findOrFail($id);

        return view('admin.skills.edit', compact('skill'));
    }

    public function update(Request $request, $id)
    {
        $skill = Skill::findOrFail($id);
        $oldValues = $skill->getOriginal();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:skills,name,' . $skill->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $skill->update($request->only(['name']));

        $newValues = $skill->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Updated skill: ' . $skill->name . ' (' . implode(', ', $changesDescription) . ')',
            'type' => 'update',
            'entity_id' => $skill->id,
            'entity_type' => Skill::class,
        ]);

        return redirect()->route('admin.skills.index')->with('success', 'Skill updated successfully.');
    }

    public function destroy($id)
    {
        $skill = Skill::findOrFail($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted skill: ' . $skill->name,
            'type' => 'delete',
            'entity_id' => $skill->id,
            'entity_type' => Skill::class,
        ]);

        $skill->delete();

        return redirect()->route('admin.skills.index')->with('success', 'Skill deleted successfully.');
    }
}
