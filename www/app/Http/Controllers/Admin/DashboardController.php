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
            'revenue_paid' => \App\Models\Order::where('status', 'paid')->sum('total_amount'),
            'revenue_pending' => \App\Models\Order::where('status', 'pending')->sum('total_amount'),
        ];
        
        $gateways = \App\Models\Order::select('gateway', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
                  ->whereNotNull('gateway')
                  ->groupBy('gateway')
                  ->pluck('total', 'gateway')->toArray();

        return view('admin.dashboard', compact('stats', 'gateways'));
    }
}
