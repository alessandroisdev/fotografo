<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Eager carregando o client
            $data = Gallery::with('user')->select('galleries.*');
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('client_name', function($row){
                         return $row->user ? $row->user->name : 'N/A';
                    })
                    ->addColumn('action', function($row){
                           $btn = '<a href="/admin/galleries/'.$row->id.'" class="btn btn-info btn-sm text-white me-1"><i class="bi bi-cloud-arrow-up"></i> Gerenciar Fotos</a>';
                           $btn .= '<a href="/admin/galleries/'.$row->id.'/edit" class="btn btn-primary btn-sm me-1"><i class="bi bi-pencil"></i></a>';
                           return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('admin.galleries.index');
    }

    public function create()
    {
        $clients = User::where('role', \App\Enums\UserRoleEnum::CLIENT)->get();
        return view('admin.galleries.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['uuid'] = Str::uuid()->toString();
        $validated['status'] = \App\Enums\GalleryStatusEnum::DRAFT;
        $validated['is_public'] = $request->has('is_public');

        $gallery = Gallery::create($validated);

        return redirect()->route('admin.galleries.show', $gallery->id)->with('success', 'Galeria contêiner criada. Inicie o upload!');
    }

    public function show(Gallery $gallery)
    {
        // View de visualização e Dropzone upload
        $gallery->load(['photos' => function($q) {
            $q->orderBy('created_at', 'desc');
        }]);
        return view('admin.galleries.show', compact('gallery'));
    }

    public function edit(Gallery $gallery)
    {
        $clients = User::where('role', \App\Enums\UserRoleEnum::CLIENT)->get();
        return view('admin.galleries.edit', compact('gallery', 'clients'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => [new \Illuminate\Validation\Rules\Enum(\App\Enums\GalleryStatusEnum::class)]
        ]);

        $validated['status'] = \App\Enums\GalleryStatusEnum::tryFrom($request->status);
        $validated['is_public'] = $request->has('is_public');

        $gallery->update($validated);

        return redirect()->route('admin.galleries.show', $gallery->id)->with('success', 'Configurações e Público-Alvo atualizados!');
    }
}
