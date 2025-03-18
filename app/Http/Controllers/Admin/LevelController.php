<?php

namespace App\Http\Controllers\Admin;

use App\Models\Level;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        $query = Level::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        $levels = $query->paginate(10);

        return view('admin.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.levels.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:levels',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $level = Level::create($request->only(['name']));

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Created a new level: ' . $level->name,
            'type' => 'create',
            'entity_id' => $level->id,
            'entity_type' => Level::class,
        ]);

        return redirect()->route('admin.levels.index')->with('success', 'Level created successfully.');
    }

    public function edit($id)
    {
        $level = Level::findOrFail($id);

        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, $id)
    {
        $level = Level::findOrFail($id);
        $oldValues = $level->getOriginal();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:levels,name,' . $level->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $level->update($request->only(['name']));

        $newValues = $level->getChanges();
        $changesDescription = [];

        foreach ($newValues as $key => $value) {
            $changesDescription[] = "$key changed from '{$oldValues[$key]}' to '{$value}'";
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Updated level: ' . $level->name . ' (' . implode(', ', $changesDescription) . ')',
            'type' => 'update',
            'entity_id' => $level->id,
            'entity_type' => Level::class,
        ]);

        return redirect()->route('admin.levels.index')->with('success', 'Level updated successfully.');
    }

    public function destroy($id)
    {
        $level = Level::findOrFail($id);

        AuditLog::create([
            'user_id' => Auth::id(),
            'description' => 'Deleted level: ' . $level->name,
            'type' => 'delete',
            'entity_id' => $level->id,
            'entity_type' => Level::class,
        ]);

        $level->delete();

        return redirect()->route('admin.levels.index')->with('success', 'Level deleted successfully.');
    }
}
