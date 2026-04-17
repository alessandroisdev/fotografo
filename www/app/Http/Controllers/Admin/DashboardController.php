<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Setting;
use App\Models\Gallery;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'clients' => User::where('role', 'client')->count(),
            'galleries' => Gallery::count(),
            'revenue' => 0 // Mock por enquanto
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
