@extends('layouts.admin')

@section('title', 'Detalhes da Venda')
@section('header_title', 'Inspeção do Pedido')

@section('content')
<div class="row mb-4">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold fs-6 text-uppercase text-muted mb-3">Dados Financeiros</h5>
                <p class="mb-1"><strong>Cliente:</strong> {{ $order->user->name }}</p>
                <p class="mb-1"><strong>Pacote Origem:</strong> {{ $order->package->name ?? 'Avulso' }}</p>
                <p class="mb-1"><strong>Data da Compra:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <div class="mt-3 fs-5 fw-bold text-success">
                    R$ {{ number_format($order->total_amount, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm bg-light rounded-4 h-100">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <h5 class="fw-bold mb-3">Status do Pagamento</h5>
                <div>
                     @if($order->status === \App\Enums\OrderStatusEnum::PAID)
                         <span class="badge bg-success fs-5 px-4 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle"></i> Venda Aprovada</span>
                     @elseif($order->status === \App\Enums\OrderStatusEnum::CANCELLED)
                         <span class="badge bg-danger fs-5 px-4 py-2 rounded-pill shadow-sm"><i class="bi bi-x-circle"></i> Fatura Cancelada</span>
                     @else
                         <span class="badge bg-warning text-dark fs-5 px-4 py-2 rounded-pill shadow-sm"><i class="bi bi-hourglass-split"></i> Aguardando Pagamento</span>
                     @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0"><i class="bi bi-images me-2 text-primary"></i> Fotografias Selecionadas <span class="badge bg-secondary ms-2">{{ $order->items_count ?? count($order->items) }} Fotos</span></h5>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Voltar para Vendas</a>
                </div>

                <div class="row g-3">
                    @foreach($order->items as $item)
                        @if($item->photo)
                        <div class="col-4 col-md-3 col-lg-2">
                            <div class="position-relative">
                                <a href="{{ Storage::url($item->photo->thumbnail_path) }}" target="_blank">
                                    <img src="{{ Storage::url($item->photo->thumbnail_path) }}" class="img-fluid rounded border shadow-sm w-100" style="height:140px; object-fit:cover;" alt="{{ $item->photo->original_name }}">
                                </a>
                                @if($item->is_extra)
                                    <span class="position-absolute top-0 start-0 badge bg-warning text-dark m-1 shadow-sm"><i class="bi bi-plus-circle"></i> Foto Adicional</span>
                                @endif
                                <span class="position-absolute bottom-0 text-truncate bg-dark bg-opacity-75 text-white w-100 text-center small py-1" style="border-bottom-left-radius: 0.375rem; border-bottom-right-radius: 0.375rem;">
                                    {{ $item->photo->original_name }}
                                </span>
                            </div>
                        </div>
                        @else
                        <div class="col-4 col-md-3 col-lg-2">
                             <div class="bg-light border rounded d-flex align-items-center justify-content-center text-muted" style="height:140px;">Foto Deletada</div>
                        </div>
                        @endif
                    @endforeach
                </div>
                
                @if($order->status === \App\Enums\OrderStatusEnum::PAID)
                <div class="mt-5 text-center bg-light p-4 rounded-3 border">
                     <h6 class="fw-bold text-success mb-2"><i class="bi bi-check-circle-fill me-1"></i> Pacote Finalizado e Pago</h6>
                     <p class="text-muted small mb-0">As imagens marcadas já se encontram isoladas na Área do Cliente e no ZIP de Download Autorizado dele.</p>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</div>

<!-- AUDITORIA DE TENTATIVAS DE PAGAMENTOS E GATEWAYS -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h5 class="fw-bold mb-0 text-secondary"><i class="bi bi-diagram-3 me-2"></i> Auditoria de Transações & Tentativas</h5>
                </div>
                
                @if($order->attempts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Data/Hora</th>
                                <th>Gateway</th>
                                <th>Método</th>
                                <th>Status Resultante</th>
                                <th>Mensagem Diagnóstica</th>
                                <th>Payload</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->attempts as $attempt)
                            <tr>
                                <td class="text-nowrap text-muted small">{{ $attempt->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="fw-medium text-uppercase"><span class="badge bg-secondary">{{ $attempt->gateway }}</span></td>
                                <td class="text-uppercase"><span class="badge bg-dark">{{ $attempt->payment_method }}</span></td>
                                <td>
                                     @if($attempt->status === 'success')
                                         <span class="badge bg-success"><i class="bi bi-check"></i> Sucesso</span>
                                     @else
                                         <span class="badge bg-danger"><i class="bi bi-x"></i> Bloqueio/Falha</span>
                                     @endif
                                </td>
                                <td><span class="text-muted small" style="max-width:300px; display:inline-block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $attempt->message }}">{{ $attempt->message }}</span></td>
                                <td>
                                    @if(!empty($attempt->response_payload))
                                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#payloadModal{{ $attempt->id }}">
                                            <i class="bi bi-braces"></i> Ver JSON
                                        </button>
                                        
                                        <!-- Modal JSON -->
                                        <div class="modal fade" id="payloadModal{{ $attempt->id }}" tabindex="-1" aria-hidden="true">
                                          <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                              <div class="modal-header">
                                                <h1 class="modal-title fs-5">Snapshot de Resposta Operacional</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                              </div>
                                              <div class="modal-body p-0">
                                                  <pre class="bg-dark text-success p-3 mb-0" style="max-height: 500px; overflow-y: auto;"><code>{{ json_encode($attempt->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    @else
                                        <span class="text-muted fw-light small">Oco</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-secondary text-center">Nenhum evento de captura registrado neste rastreador financeiro ainda.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
