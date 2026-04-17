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
            'file' => 'required|file|max:512000', // max 500MB
        ]);

        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['jpeg', 'png', 'jpg', 'webp', 'cr2', 'cr3', 'dng', 'arw', 'nef'])) {
             return response()->json(['error' => 'Formato de imagem ou arquivo RAW não suportado.'], 422);
        }

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

    public function poll(Request $request, Gallery $gallery)
    {
        $ids = $request->input('ids');
        if (empty($ids) || !is_array($ids)) {
            return response()->json([]);
        }

        $photos = Photo::where('gallery_id', $gallery->id)
                       ->whereIn('id', $ids)
                       ->where('status', 'ready')
                       ->get();

        return response()->json(
            $photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'thumbnail_url' => \Illuminate\Support\Facades\Storage::url($photo->thumbnail_path)
                ];
            })
        );
    }
}
