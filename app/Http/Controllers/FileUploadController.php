<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Process the file upload.
     */
    // Example upload method for FilePond (process)
    public function upload(Request $request)
    {
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos', 'public');
            return response()->json(['path' => $path]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    // Example revert method for FilePond
    public function revert(Request $request)
    {
        // If you need to remove the file physically, do it here.
        // For now, return a simple 200 OK response.
        return response('', 200);
    }
}
