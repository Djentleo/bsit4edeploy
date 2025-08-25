<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Kreait\Firebase\Factory;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,mp4|max:10240',
            'incident_id' => 'required|string',
        ]);

        // Store file locally
        $path = $request->file('file')->store('uploads', 'public');

        // Update Firebase with file path
        $firebase = (new Factory)->withServiceAccount(config('firebase.credentials'))->createDatabase();
        $database = $firebase->getReference('incidents/' . $request->incident_id . '/attachments');

        $database->push([
            'type' => $request->file('file')->getMimeType(),
            'url' => asset('storage/' . $path),
        ]);

        return response()->json(['message' => 'File uploaded successfully', 'path' => $path], 200);
    }
}
