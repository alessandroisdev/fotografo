@extends('layouts.public')

@section('title', 'Área do Cliente')

@section('content')
<div class="row pt-4 text-center text-white mb-5">
    <div class="col-12">
        <h2 class="display-5 fw-bold">Olá, {{ explode(' ', $client->name)[0] }}!</h2>
        <p class="lead">Aqui estão os ensaios separados para sua curadoria.</p>
    </div>
</div>

<ul class="nav nav-pills mb-4 justify-content-center" id="dashboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 me-2" id="galleries-tab" data-bs-toggle="tab" data-bs-target="#galleries" type="button" role="tab" aria-selected="true"><i class="bi bi-images me-2"></i>Meus Ensaios</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-selected="false"><i class="bi bi-wallet2 me-2"></i>Histórico e Downloads</button>
    </li>
</ul>

<div class="tab-content" id="dashboardTabsContent">
    <!-- Tab Galerias (Original) -->
    <div class="tab-pane fade show active" id="galleries" role="tabpanel" tabindex="0">
        <div class="row g-4">
            @forelse($galleries as $gallery)
            <div class="col-md-4">
                <div class="gallery-card p-3 text-center text-white text-decoration-none d-block">
                    <div class="bg-dark rounded d-flex align-items-center justify-content-center mb-3" style="min-height: 200px; background: url('{{ $gallery->cover_path ? Storage::url($gallery->cover_path) : '' }}') no-repeat center/cover; position: relative;">
                        @if(!$gallery->cover_path)
                            <i class="bi bi-camera fs-1 text-secondary"></i>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $gallery->name }}</h4>
                    <p class="small text-muted mb-3">{{ $gallery->created_at->format('d M, Y') }}</p>
                    <span class="badge bg-primary text-white mb-3">{{ $gallery->photos_count }} Fotos Trabalhadas</span>
                    
                    <a href="{{ route('client.galleries.show', $gallery->uuid) }}" class="btn btn-outline-secondary w-100 rounded-pill"><i class="bi bi-grid-3x3-gap me-2"></i> Fazer Minha Seleção</a>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-white-50 py-5">
                <i class="bi bi-folder-x display-1 mb-3"></i>
                <h4 class="fw-normal">Nenhum álbum liberado no momento.</h4>
                <p>Quando as suas fotos estiverem prontas, elas aparecerão magicamente aqui.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Tab Faturas / Orders / Downloads -->
    <div class="tab-pane fade" id="orders" role="tabpanel" tabindex="0">
        <div class="row g-4">
            @forelse($orders as $order)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 bg-dark text-white shadow-sm rounded-4 h-100 gallery-card">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold mb-1"><i class="bi bi-receipt me-2"></i> Pedido #{{ substr($order->uuid, 0, 8) }}</h5>
                                <span class="badge bg-secondary opacity-75">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($order->status == 'paid')
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Aprovado</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Cancelado</span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Aguardando Pgto</span>
                            @endif
                        </div>
                        
                        <div class="mb-4 flex-grow-1">
                            <p class="mb-1 text-white-50 small">Ensaio Associado:</p>
                            <p class="mb-2 fw-bold">{{ $order->gallery->name ?? 'Indisponível' }}</p>
                            
                            <p class="mb-1 text-white-50 small">Pacote e Seleção:</p>
                            <p class="mb-0 fw-bold">{{ $order->package->name ?? 'Pacote Personalizado' }} <span class="text-secondary fw-normal">({{ $order->items_count }} fotos finais)</span></p>
                        </div>
                        
                        <div class="border-top border-secondary pt-3 mt-auto d-flex justify-content-between align-items-center mb-3">
                            <span class="text-white-50 small">Valor Total</span>
                            <span class="fw-bold fs-5 text-success">R$ {{ number_format($order->total_amount, 2, ',', '.') }}</span>
                        </div>

                        <div class="d-grid gap-2">
                             @if($order->status == 'paid')
                                 <a href="{{ route('client.orders.download', $order->uuid) }}" class="btn btn-primary rounded-pill fw-bold"><i class="bi bi-file-earmark-zip-fill me-2"></i> Baixar ZIP em Alta Qualidade</a>
                                 <a href="{{ route('client.orders.show', $order->uuid) }}" class="btn btn-outline-light rounded-pill"><i class="bi bi-eye"></i> Visualizar Minhas Fotos</a>
                             @else
                                 <button class="btn btn-secondary rounded-pill opacity-50" disabled><i class="bi bi-lock-fill me-2"></i> Liberado Após Baixa</button>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-white-50 py-5">
                <i class="bi bi-clipboard-x display-1 mb-3"></i>
                <h4 class="fw-normal">Você ainda não enviou nenhum pedido.</h4>
                <p>Navegue pelos seus ensaios e faça a seleção das suas fotos favoritas para fechar sua compra.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
