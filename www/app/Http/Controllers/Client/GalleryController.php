<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;

class GalleryController extends Controller
{
    public function show($uuid)
    {
        $gallery = Gallery::where('uuid', $uuid)
                 ->where('status', '!=', \App\Enums\GalleryStatusEnum::DRAFT)
                 ->with(['photos' => function($q){
                     // Apenas fotos prontas são exibidas para cliente
                     $q->where('status', 'ready');
                 }])->firstOrFail();

        return view('client.galleries.show', compact('gallery'));
    }
}
