<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gallery;

class PublicPortfolioController extends Controller
{
    /**
     * Display the specified gallery with only public photos.
     */
    public function show($uuid)
    {
        $gallery = Gallery::where('uuid', $uuid)
                 ->where('is_public', true)
                 ->with(['photos' => function($q) {
                     $q->where('status', 'ready')
                       ->where('is_public', true)
                       ->orderBy('created_at', 'desc');
                 }, 'user'])
                 ->firstOrFail();

        return view('public.portfolio.show', compact('gallery'));
    }
}
