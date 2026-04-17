<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $galleries = Gallery::with(['photos' => function($query) {
                        $query->where('status', 'ready')
                              ->where('is_public', true)
                              ->inRandomOrder()
                              ->take(1);
                    }])
                    ->withCount(['photos' => function ($q) {
                        $q->where('is_public', true);
                    }])
                    ->where('is_public', true)
                    ->latest()
                    ->take(6)
                    ->get();
                    
        return view('home', compact('galleries'));
    }
}
