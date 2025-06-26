<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentFileController extends Controller
{
    /**
     * Store a newly created file (upload).
     */
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file'
        ]);

        // Upload file
        $path = $request->file('file')->store('student_files', 'public');

        // Create record
        $studentFile = StudentFile::create([
            'student_id' => $student->id,
            'name'       => $request->input('name'),
            'path'       => $path,
        ]);

        return redirect()
            ->back()
            ->with('success', 'File uploaded successfully!');
    }

    /**
     * Download a file.
     */
    public function download(Student $student, StudentFile $file)
    {
        // Ensure this file belongs to the given student
        if ($file->student_id != $student->id) {
            abort(403, 'Unauthorized action.');
        }

        return Storage::disk('public')->download($file->path, $file->name);
    }

    /**
     * Update the specified file (rename or replace).
     */
    public function update(Request $request, StudentFile $file)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|file'
        ]);

        // If user attached a new file, replace it
        if ($request->hasFile('file')) {
            // Delete the old file if needed
            Storage::disk('public')->delete($file->path);

            // Store new file
            $path = $request->file('file')->store('student_files', 'public');
            $file->path = $path;
        }

        $file->name = $request->input('name');
        $file->save();

        return redirect()
            ->back()
            ->with('success', 'File updated successfully!');
    }

    /**
     * Remove the specified file from storage.
     */
    public function destroy(StudentFile $file)
    {
        // Optionally remove file from disk
        Storage::disk('public')->delete($file->path);

        $file->delete();

        return redirect()
            ->back()
            ->with('success', 'File deleted successfully!');
    }
}
