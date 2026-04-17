<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Package::select('*');
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->editColumn('price', function($row){
                         return 'R$ ' . number_format($row->price, 2, ',', '.');
                    })
                    ->editColumn('extra_photo_price', function($row){
                         return 'R$ ' . number_format($row->extra_photo_price, 2, ',', '.');
                    })
                    ->addColumn('action', function($row){
                           $btn = '<a href="/admin/packages/'.$row->id.'/edit" class="btn btn-primary btn-sm me-1"><i class="bi bi-pencil"></i></a>';
                           $btn .= '<button class="btn btn-danger btn-sm" onclick="globalDelete(\''.$row->id.'\')"><i class="bi bi-trash"></i></button>';
                           return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        
        return view('admin.packages.index');
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'included_photos_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'extra_photo_price' => 'required|numeric|min:0',
        ]);

        $validated['uuid'] = Str::uuid()->toString();
        $validated['is_active'] = true;

        Package::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Plano de cobrança criado com sucesso!');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'included_photos_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'extra_photo_price' => 'required|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Plano atualizado com sucesso!');
    }
}
