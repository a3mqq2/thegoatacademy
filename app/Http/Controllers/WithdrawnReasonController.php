<?php

namespace App\Http\Controllers;

use App\Models\WithdrawnReason;
use Illuminate\Http\Request;

class WithdrawnReasonController extends Controller
{
    public function index()
    {
        $withdrawnReasons = WithdrawnReason::orderBy('id', 'desc')->paginate(10);
        return view('withdrawn_reasons.index', compact('withdrawnReasons'));
    }

    public function create()
    {
        return view('withdrawn_reasons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable|string',
        ]);

        WithdrawnReason::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.withdrawn_reasons.index')
                         ->with('success', 'Withdrawn reason created successfully.');
    }

    // Optional: show/edit/update/destroy if needed
}
