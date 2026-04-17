<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\Photo;
use Illuminate\Support\Str;
use App\Jobs\ProcessImageJob;

class PhotoController extends Controller
{
    public function store(Request $request, Gallery $gallery)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:512000', // max 500MB
        ]);

        $file = $request->file('file');
        
        // Storage Disk padrão para High Res -> local private path
        $path = $file->store('raw_photos/' . $gallery->uuid, 'local');

        $photo = Photo::create([
            'uuid' => Str::uuid()->toString(),
            'gallery_id' => $gallery->id,
            'original_name' => $file->getClientOriginalName(),
            'original_path' => $path,
            'storage_driver' => 'local',
            'status' => 'processing'
        ]);

        // Dispatch do Job em Background para Redimensionamento e Watermark
        ProcessImageJob::dispatch($photo);

        return response()->json([
            'success' => true,
            'photo_id' => $photo->id,
            'uuid' => $photo->uuid
        ]);
    }
}
