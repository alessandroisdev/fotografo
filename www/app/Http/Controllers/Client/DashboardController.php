<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Gallery;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user();

        $galleries = Gallery::where('user_id', $client->id)
                            ->where('status', '!=', \App\Enums\GalleryStatusEnum::DRAFT)
                            ->withCount('photos')
                            ->latest()
                            ->get();
                            
        $orders = \App\Models\Order::where('user_id', $client->id)
                            ->with(['package', 'gallery'])
                            ->withCount('items')
                            ->latest()
                            ->get();

        return view('client.dashboard', compact('client', 'galleries', 'orders'));
    }
}
