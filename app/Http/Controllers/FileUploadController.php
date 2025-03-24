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
            $path = Storage::disk('s3')->putFile('videos', $file);
            // Generate a pre-signed URL valid for 10 minutes
            $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(10));
            return response($url, 200);
        }
        
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function revert(Request $request)
    {
        return response('', 200);
    }
}
