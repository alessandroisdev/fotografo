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
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 border border-success" style="--bs-border-opacity: .3;">
            <div class="card-body">
                <i class="bi bi-currency-dollar text-success" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 text-secondary">Faturamento Líquido (Pago)</h5>
                <h2 class="display-5 fw-bold text-success">R$ {{ number_format($stats['revenue_paid'], 2, ',', '.') }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4">
            <div class="card-body">
                <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                <h6 class="card-title mt-3 text-secondary">Receita Pendente</h6>
                <h4 class="fw-bold">R$ {{ number_format($stats['revenue_pending'], 2, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-bar-chart-fill text-secondary me-2"></i> Transações por Gateway Mapeado</h5>
                <div class="row text-center">
                    @forelse($gateways as $method => $count)
                    <div class="col">
                        <div class="bg-light rounded p-3">
                            <span class="fs-5 fw-bold">{{ $count }}</span>
                            <div class="text-muted small text-uppercase mt-1">{{ $method }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="col text-muted">Ainda não existem faturas processadas no sistema por gatilhos financeiros.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5">
                <h4 class="mb-4 text-primary"><i class="bi bi-rocket-takeoff text-secondary me-2"></i> Próximos Passos</h4>
                <p>Seja bem-vindo de volta, Fotógrafo! Este é o seu painel central. Acima estão os balanços reais extraídos do nosso Webhook Financeiro.<br>Abaixo no menu da esquerda, você pode gerenciar a tabela de clientes ou criar novas galerias enviando fotos brutas (RAW/JPG) em alta qualidade.</p>
                <a href="/admin/galleries/create" class="btn btn-primary btn-lg mt-2"><i class="bi bi-cloud-arrow-up"></i> Fazer Upload de Nova Galeria</a>
            </div>
        </div>
    </div>
</div>
@endsection
