@extends('layouts.admin')

@section('title', 'Vendas e Faturas')
@section('header_title', 'Pedidos dos Clientes')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 bg-primary text-white shadow-sm rounded-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-wallet2 text-secondary me-2"></i> Caixa e Checkout</h5>
                    <p class="mb-0 opacity-75">Monitore os pacotes selecionados pelos seus clientes e aprove transferências PIX ou Cartão.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <table id="ordersTable" class="table table-hover table-borderless align-middle" style="width:100%">
            <thead class="table-light">
                <tr>
                    <th>Ref ID</th>
                    <th>Cliente</th>
                    <th>Pacote Ofertado</th>
                    <th>Qtd Fotos</th>
                    <th>Valor Devido</th>
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
            selector: '#ordersTable',
            url: "{{ route('admin.orders.index') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'client_name', name: 'user.name' },
                { data: 'package_name', name: 'package.name' },
                { data: 'total_photos', name: 'total_photos' },
                { data: 'total_amount', name: 'total_amount' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endsection
