<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MeetingPlatform;
use Illuminate\Http\Request;

class MeetingPlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $platforms = MeetingPlatform::all();

        // If you want to return JSON
        if (request()->wantsJson()) {
            return response()->json($platforms, 200);
        }

        // Otherwise return a Blade view
        return view('admin.meeting_platforms.index', compact('platforms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // If you want to return JSON, do so, or just skip:
        // return response()->json(['message' => 'Show create form']);

        return view('admin.meeting_platforms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate form input
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Create record
        $platform = MeetingPlatform::create($data);

        // JSON response if desired
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Meeting platform created successfully.',
                'platform' => $platform
            ], 201);
        }

        // Or redirect to index
        return redirect()
            ->route('admin.meeting_platforms.index')
            ->with('success', 'Meeting platform created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MeetingPlatform $meetingPlatform)
    {
        // If JSON:
        if (request()->wantsJson()) {
            return response()->json($meetingPlatform, 200);
        }

        // Otherwise a Blade view
        return view('admin.meeting_platforms.show', compact('meetingPlatform'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MeetingPlatform $meetingPlatform)
    {
        // If JSON:
        // return response()->json(['platform' => $meetingPlatform]);

        return view('admin.meeting_platforms.edit', compact('meetingPlatform'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeetingPlatform $meetingPlatform)
    {
        // Validate form input
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Update record
        $meetingPlatform->update($data);

        // JSON response if needed
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Meeting platform updated successfully.',
                'platform' => $meetingPlatform
            ], 200);
        }

        // Or redirect
        return redirect()
            ->route('admin.meeting_platforms.index')
            ->with('success', 'Meeting platform updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetingPlatform $meetingPlatform)
    {
        $meetingPlatform->delete();

        // If JSON
        if (request()->wantsJson()) {
            return response()->json([
                'message' => 'Meeting platform deleted successfully.'
            ], 200);
        }

        // Or redirect
        return redirect()
            ->route('admin.meeting_platforms.index')
            ->with('success', 'Meeting platform deleted successfully.');
    }
}
