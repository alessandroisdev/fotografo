<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = User::where('role', 'client')->select('*');
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){
                           $btn = '<a href="/admin/clients/'.$row->id.'/edit" class="btn btn-primary btn-sm"><i class="bi bi-pencil"></i> Editar</a>';
                           $btn .= ' <button class="btn btn-danger btn-sm" onclick="deleteClient(\''.$row->id.'\')"><i class="bi bi-trash"></i> Excluir</button>';
                           return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('admin.clients.index');
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'document' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['role'] = 'client';
        $validated['uuid'] = Str::uuid()->toString();
        $validated['password'] = Hash::make(Str::random(12)); // Senha inicial randômica

        User::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Cliente salvo com sucesso!');
    }
}
