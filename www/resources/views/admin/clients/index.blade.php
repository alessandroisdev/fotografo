@extends('layouts.admin')

@section('title', 'Gestão de Clientes')
@section('header_title', 'Seus Clientes')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-people text-secondary me-2"></i> Cadastro de Clientes</h5>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo Cliente</a>
        </div>

        <table id="clientsTable" class="table table-hover table-borderless align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>CPF / Doc</th>
                    <th>Telefone</th>
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
            selector: '#clientsTable',
            url: "{{ route('admin.clients.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'document', name: 'document' },
                { data: 'phone', name: 'phone' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
    
    // Função global p/ deletar usando o Modal do Bootstrap!
    window.deleteClient = function(id) {
        window.askConfirm(
            '<i class="bi bi-trash-fill me-2"></i> Deletar Cliente', 
            'Tem certeza que deseja apagar este cliente permanentemente? A conta será desativada mas os dados isolados serão retidos no banco se houver galerias.',
            function() {
                // Lógica de Submissão Delete passaria aqui (Ex: Formulário Oculto)
                console.log("Deletar ID Concluído pelo Modal: ", id);
                
                // MOCKUP Feedback Toast:
                // Criar formulário programaticamente, injetar DELETE token, e dar submit.
                // Mas por enquanto vamos apenas mostrar no console durante o test.
            }
        );
    }
</script>
@endsection
