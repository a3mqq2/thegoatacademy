<?php

namespace App\Http\Controllers;

use App\Models\ExcludeReason;
use Illuminate\Http\Request;

class ExcludeReasonController extends Controller
{
    public function index()
    {
        $excludeReasons = ExcludeReason::orderBy('id', 'desc')->paginate(10);
        return view('exclude_reasons.index', compact('excludeReasons'));
    }

    public function create()
    {
        return view('exclude_reasons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
        ]);

        ExcludeReason::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.exclude_reasons.index')
                         ->with('success', 'Exclude reason created successfully.');
    }

}
