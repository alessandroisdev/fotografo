@extends('layouts.admin')

@section('title', 'Gestão de Galerias')
@section('header_title', 'Galerias Fotográficas')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-images text-secondary me-2"></i> Portfólios e Ensaios</h5>
            <a href="{{ route('admin.galleries.create') }}" class="btn btn-primary"><i class="bi bi-camera-fill"></i> Nova Galeria</a>
        </div>

        <table id="galleriesTable" class="table table-hover table-borderless align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Cliente</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script type="module">
    document.addEventListener("DOMContentLoaded", function() {
        new window.DataTableApp({
            selector: '#galleriesTable',
            url: "{{ route('admin.galleries.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'client_name', name: 'client_name' },
                { data: 'status', name: 'status', render: function(data) {
                    if(data === 'draft') return '<span class="badge bg-secondary">Rascunho</span>';
                    if(data === 'published_public') return '<span class="badge bg-success">Público</span>';
                    return '<span class="badge bg-warning text-dark">Privado</span>';
                }},
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
