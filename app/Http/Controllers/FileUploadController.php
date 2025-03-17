<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Process the file upload.
     */
    public function upload(Request $request)
    {
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            // Save the file to the 'videos' directory on the 'public' disk.
            $path = $file->store('videos', 'public');
            return response()->json(['path' => $path]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    /**
     * Revert the file upload (delete the temporary file).
     */
    public function revert(Request $request)
    {
        // FilePond sends the file path as the request content.
        $filePath = $request->getContent();
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        return response()->json(['reverted' => true]);
    }
}
