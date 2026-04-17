@extends('layouts.public')

@section('title', 'Detalhes da Compra')

@section('content')
<div class="row pt-4 text-white mb-4">
    <div class="col-12 text-center">
        <h2 class="display-6 fw-bold">Pedido: {{ substr($order->uuid, 0, 8) }}</h2>
        <p class="lead text-white-50">Resumo da sua seleção de fotos finais.</p>
    </div>
</div>

<div class="row mb-5 justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card bg-dark text-white border-0 shadow-lg rounded-4">
            <div class="card-body p-4">
                 <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-3">
                     <span class="text-white-50">Status Financeiro:</span>
                     @if($order->status == 'paid')
                         <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Liberado</span>
                     @elseif($order->status == 'cancelled')
                         <span class="badge bg-danger fs-6"><i class="bi bi-x-circle"></i> Cancelado</span>
                     @else
                         <span class="badge bg-warning text-dark fs-6"><i class="bi bi-hourglass-split"></i> Aguardando Confirmação</span>
                     @endif
                 </div>
                 
                 <div class="d-flex justify-content-between mb-2">
                     <span class="text-white-50">Fotos Inclusas no Pacote:</span>
                     <span class="fw-bold">{{ $order->included_photos }}</span>
                 </div>
                 <div class="d-flex justify-content-between mb-2">
                     <span class="text-white-50">Fotos Extras Escolhidas:</span>
                     <span class="fw-bold text-warning">+{{ $order->extra_photos }}</span>
                 </div>
                 
                 <div class="d-flex justify-content-between fs-4 fw-bold mt-4 pt-3 border-top border-secondary text-success">
                     <span>Total Geral:</span>
                     <span>R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                 </div>

                 <!-- Botão de Download Principal caso Pago -->
                 @if($order->status == 'paid')
                    <div class="mt-4 pt-2">
                        <a href="{{ route('client.orders.download', $order->uuid) }}" class="btn btn-primary w-100 rounded-pill py-3 fw-bold fs-5 shadow-sm">
                            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Baixar Arquivos ZIP Oficiais
                        </a>
                        <p class="text-center text-white-50 small mt-3 px-3"><i class="bi bi-info-circle me-1"></i> O download das imagens pode ser um pouco pesado dependendo da qualidade das fotos em alta resolução configuradas pelo fotógrafo.</p>
                    </div>
                 @endif
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 border-bottom border-secondary pb-2">
        <h4 class="text-white"><i class="bi bi-images me-2 text-primary"></i> Suas Escolhas Finais</h4>
    </div>
</div>

<div class="row g-3">
    @foreach($order->items as $item)
        @if($item->photo)
        <div class="col-4 col-md-3 col-lg-2">
            <div class="position-relative">
                <img src="{{ Storage::url($item->photo->thumbnail_path) }}" class="img-fluid rounded border shadow-sm w-100" style="height:120px; object-fit:cover;" alt="Miniatura">
                @if($item->is_extra)
                    <span class="position-absolute top-0 start-0 badge bg-warning text-dark m-1" title="Foto Adicional Extra">Extra</span>
                @endif
                <span class="position-absolute bottom-0 end-0 badge bg-success m-1"><i class="bi bi-check2-all"></i></span>
            </div>
        </div>
        @endif
    @endforeach
</div>

<div class="row mt-5">
    <div class="col-12 text-center">
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i> Voltar ao Painel</a>
    </div>
</div>
@endsection
