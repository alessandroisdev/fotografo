<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Simulando Cliente Logado
        $client = User::where('role', 'client')->first();
        
        if(!$client) {
            return redirect('/')->with('error', 'Nenhum cliente cadastrado no sistema para simular a área.');
        }

        $galleries = Gallery::where('user_id', $client->id)
                            ->where('status', '!=', 'draft')
                            ->withCount('photos')
                            ->get();

        return view('client.dashboard', compact('client', 'galleries'));
    }
}
