@extends('layouts.admin')

@section('title', 'Planos de Cobrança')
@section('header_title', 'Seus Pacotes de Venda')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-box-seam text-secondary me-2"></i> Pacotes de Fotos</h5>
            <a href="{{ route('admin.packages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Pacote</a>
        </div>

        <table id="packagesTable" class="table table-hover table-borderless align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Qtd Inclusa</th>
                    <th>Valor Base</th>
                    <th>Preço Foto Extra</th>
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
            selector: '#packagesTable',
            url: "{{ route('admin.packages.index') }}",
            columns: [
                { data: 'name', name: 'name' },
                { data: 'included_photos_count', name: 'included_photos_count' },
                { data: 'price', name: 'price' },
                { data: 'extra_photo_price', name: 'extra_photo_price' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
    
    window.globalDelete = function(id) {
        window.askConfirm('Atenção', 'Deseja excluir este pacote?', function() {
            // Em aplicação real, submeter formulário de destroy via ajax.
            console.log("Mock de delete pacote: ", id);
        });
    }
</script>
@endsection
