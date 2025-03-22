<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $path = $file->store('videos', 'public');
            return response()->json(['path' => $path]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function revert(Request $request)
    {
        return response('', 200);
    }
}
