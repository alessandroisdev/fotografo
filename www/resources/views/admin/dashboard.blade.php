@extends('layouts.admin')

@section('title', 'Dashboard Financeiro')
@section('header_title', 'Visão Geral do Estúdio')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="card-body">
                <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 text-secondary">Clientes Salvos</h5>
                <h2 class="display-5 fw-bold">{{ $stats['clients'] }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="card-body">
                <i class="bi bi-images text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 text-secondary">Galerias Criadas</h5>
                <h2 class="display-5 fw-bold">{{ $stats['galleries'] }}</h2>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100">
            <div class="card-body">
                <i class="bi bi-currency-dollar text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 text-secondary">Vendas / Faturamento</h5>
                <h2 class="display-5 fw-bold">R$ {{ number_format($stats['revenue'], 2, ',', '.') }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <h4 class="mb-4 text-primary"><i class="bi bi-rocket-takeoff text-secondary me-2"></i> Próximos Passos</h4>
                <p>Seja bem-vindo de volta, Fotógrafo! Este é o seu painel central. Abaixo no menu da esquerda, você pode gerenciar a tabela de clientes, criar galerias enviando fotos brutas e personalizar as cores públicas de exibição do estúdio.</p>
                <a href="/admin/galleries/create" class="btn btn-primary btn-lg mt-2"><i class="bi bi-cloud-arrow-up"></i> Fazer Upload de Nova Galeria</a>
            </div>
        </div>
    </div>
</div>
@endsection
