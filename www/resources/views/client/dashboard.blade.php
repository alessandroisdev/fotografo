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
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-selected="false"><i class="bi bi-person-lock me-2"></i>Cofre e Cobrança</button>
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
                            @if($order->status === \App\Enums\OrderStatusEnum::PAID)
                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Aprovado</span>
                            @elseif($order->status === \App\Enums\OrderStatusEnum::CANCELLED)
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
                             @if($order->status === \App\Enums\OrderStatusEnum::PAID)
                                 <a href="{{ route('client.orders.download', $order->uuid) }}" class="btn btn-primary rounded-pill fw-bold"><i class="bi bi-file-earmark-zip-fill me-2"></i> Baixar ZIP em Alta Qualidade</a>
                                 <a href="{{ route('client.orders.show', $order->uuid) }}" class="btn btn-outline-light rounded-pill"><i class="bi bi-eye"></i> Visualizar Minhas Fotos</a>
                             @else
                                 @if($order->gateway_payload)
                                     <div class="bg-black border border-secondary rounded p-3 mb-2 text-center">
                                         @if(($order->gateway_payload['type'] ?? '') === 'pix')
                                             <h6 class="text-success mb-2 fw-bold"><i class="bi bi-qr-code"></i> Escaneie o Pix</h6>
                                             @if(!empty($order->gateway_payload['qr_code_base64']))
                                                 <img src="data:image/png;base64,{{ $order->gateway_payload['qr_code_base64'] }}" alt="QR Code Pix" class="img-fluid bg-white p-1 rounded mb-2" style="max-width: 140px;">
                                             @endif
                                             @if(!empty($order->gateway_payload['qr_code']))
                                                 <div class="input-group input-group-sm">
                                                     <input type="text" class="form-control bg-dark text-white border-secondary" value="{{ $order->gateway_payload['qr_code'] }}" id="pix-{{ $order->uuid }}" readonly>
                                                     <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('pix-{{ $order->uuid }}').value); alert('Código Pix Copiado com sucesso!')"><i class="bi bi-clipboard"></i> Copiar</button>
                                                 </div>
                                             @endif
                                         @elseif(($order->gateway_payload['type'] ?? '') === 'boleto')
                                             <h6 class="text-warning mb-2 fw-bold"><i class="bi bi-upc-scan"></i> Boleto Bancário</h6>
                                             @if(!empty($order->gateway_payload['barcode']))
                                                 <p class="small text-white user-select-all text-break mb-2 fw-bold bg-dark p-2 border border-secondary rounded fs-6">{{ $order->gateway_payload['barcode'] }}</p>
                                             @endif
                                             @if(!empty($order->gateway_payload['pdf_url']))
                                                 <a href="{{ $order->gateway_payload['pdf_url'] }}" target="_blank" class="btn btn-sm btn-warning fw-bold text-dark rounded-pill"><i class="bi bi-file-pdf me-1"></i> Visualizar/Imprimir PDF</a>
                                             @endif
                                         @endif
                                     </div>
                                 @else
                                     <button class="btn btn-secondary rounded-pill mb-2 opacity-75" disabled><i class="bi bi-clock-history me-2"></i> Aguardando Confirmação</button>
                                 @endif
                                 
                                 <button type="button" class="btn btn-outline-warning rounded-pill mt-1 fw-bold" data-bs-toggle="modal" data-bs-target="#retryModal-{{ $order->uuid }}">
                                     <i class="bi bi-arrow-repeat me-2"></i> Alterar Meio ou Retentar Pgto
                                 </button>
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

    <!-- Tab Profile / Billing Vault -->
    <div class="tab-pane fade" id="profile" role="tabpanel" tabindex="0">
        <div class="row g-4 justify-content-center">
             <div class="col-md-6">
                 <div class="card bg-dark text-white border-secondary mb-4">
                     <div class="card-header border-bottom border-secondary py-3">
                         <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-person me-2 text-warning"></i>Meus Dados Fiscais de Cobrança</h5>
                     </div>
                     <div class="card-body">
                         <ul class="list-group list-group-flush" style="--bs-list-group-bg: transparent;">
                              <li class="list-group-item text-white border-secondary d-flex justify-content-between">
                                   <span class="text-white-50 small">Nome</span>
                                   <span class="fw-bold">{{ $client->name }}</span>
                              </li>
                              <li class="list-group-item text-white border-secondary d-flex justify-content-between">
                                   <span class="text-white-50 small">CPF/CNPJ</span>
                                   <span class="fw-bold">{{ $client->document ?: 'Não informado' }}</span>
                              </li>
                              <li class="list-group-item text-white border-secondary d-flex justify-content-between">
                                   <span class="text-white-50 small">Telefone / WhatsApp</span>
                                   <span class="fw-bold">{{ $client->phone ?: 'Não informado' }}</span>
                              </li>
                              <li class="list-group-item text-white border-secondary d-flex justify-content-between">
                                   <span class="text-white-50 small">Endereço Principal</span>
                                   <span class="fw-bold">{{ $client->address ? ($client->address . ', ' . $client->address_number . ' - ' . $client->city . '/' . $client->state) : 'Necessário no momento da compra.' }}</span>
                              </li>
                         </ul>
                     </div>
                 </div>
             </div>

             <div class="col-md-6">
                 <div class="card bg-dark text-white border-secondary">
                     <div class="card-header border-bottom border-secondary py-3">
                         <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card-2-front me-2 text-success"></i>Cofre de Pagamento AES (Seguro)</h5>
                     </div>
                     <div class="card-body">
                         @php $userCards = $client->cards ?? collect(); @endphp
                         
                         @forelse($userCards as $card)
                             <div class="d-flex justify-content-between align-items-center bg-secondary bg-opacity-10 border border-secondary p-3 rounded mb-2">
                                  <div>
                                      <i class="bi bi-credit-card fs-4 text-primary me-2 align-middle"></i>
                                      <span class="fw-bold">{{ strtoupper($card->card_brand ?: 'CARTÃO') }} final {{ $card->last_four }}</span>
                                      <div class="small text-white-50 mt-1">Titular: {{ $card->card_holder }}</div>
                                  </div>
                                  <div><span class="badge bg-success bg-opacity-25 text-success">Criptografado Mestre</span></div>
                             </div>
                         @empty
                             <div class="text-center py-4">
                                  <i class="bi bi-safe fs-1 text-secondary mb-2 d-block"></i>
                                  <p class="text-white-50 mb-0">Nenhum cartão salvo localmente para agilizar suas compras ainda.</p>
                             </div>
                         @endforelse
                     </div>
                 </div>
             </div>
        </div>
    </div>
</div>

<!-- Modals Isolados no Root para Evitar Z-Index Trap -->
@foreach($orders as $order)
    @if($order->status === \App\Enums\OrderStatusEnum::PENDING)
    <div class="modal fade" id="retryModal-{{ $order->uuid }}" tabindex="-1" aria-hidden="true" data-bs-theme="dark">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ route('client.checkout.retry', $order->uuid) }}" method="POST" class="modal-content bg-dark border-secondary">
                @csrf
                <div class="modal-header border-bottom border-secondary">
                    <h5 class="modal-title fw-bold text-white"><i class="bi bi-wallet2 me-2"></i> Checkout Retentativa #{{ substr($order->uuid, 0, 8) }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Retry Body Similar to Main Checkout logic -->
                <div class="modal-body text-start text-white">
                    <div class="mb-4">
                        <label class="form-label text-white-50 mb-3">Selecione uma Formação</label>
                        @foreach(\App\Enums\PaymentMethodEnum::cases() as $methodEnum)
                            <div class="form-check mb-2 bg-dark p-2 rounded border border-secondary" style="--bs-border-opacity: .3;">
                                <input class="form-check-input ms-1 gateway-selector" onchange="toggleFormLogic(this, 'retry_{{ $order->id }}')" type="radio" name="payment_method" id="method_retry_{{ $methodEnum->value }}_{{ $order->id }}" value="{{ $methodEnum->value }}" required>
                                <label class="form-check-label text-white ms-2" for="method_retry_{{ $methodEnum->value }}_{{ $order->id }}">
                                    {{ $methodEnum->label() }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @php
                        // Check missing details locally
                        $missingReg = empty($client->document) || empty($client->phone) ||
                                      empty($client->zipcode) || empty($client->address) || 
                                      empty($client->address_number) || empty($client->city) || empty($client->state);
                    @endphp

                    @if($missingReg)
                    <div id="registration_form_retry_{{ $order->id }}" class="registration-form-container d-none mb-4 text-start bg-secondary bg-opacity-10 p-3 rounded border border-warning" style="--bs-border-opacity: .5;">
                         <h6 class="text-warning mb-3 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Completar Cadastro de Cobrança (Obrigatório)</h6>
                         <p class="small text-white-50 form-text">É necessário preencher os dados de faturamento pendentes na sua conta.</p>
                         
                         <div class="row g-2">
                             @if(empty($client->document))
                             <div class="col-md-6 mb-3">
                                  <label class="form-label text-white-50 small mb-1">Seu CPF / CNPJ *</label>
                                  <input type="text" name="document" class="form-control bg-dark text-white border-secondary reg-input">
                             </div>
                             @endif
                             @if(empty($client->phone))
                             <div class="col-md-6 mb-3">
                                  <label class="form-label text-white-50 small mb-1">Telefone *</label>
                                  <input type="text" name="phone" class="form-control bg-dark text-white border-secondary reg-input">
                             </div>
                             @endif
                         </div>

                         @if(empty($client->address))
                         <div class="row g-2 mb-3">
                              <div class="col-3"><label class="form-label text-white-50 small mb-1">CEP *</label><input type="text" name="zipcode" class="form-control bg-dark text-white border-secondary reg-input"></div>
                              <div class="col-6"><label class="form-label text-white-50 small mb-1">Endereço *</label><input type="text" name="address" class="form-control bg-dark text-white border-secondary reg-input"></div>
                              <div class="col-3"><label class="form-label text-white-50 small mb-1">Número *</label><input type="text" name="address_number" class="form-control bg-dark text-white border-secondary reg-input"></div>
                         </div>
                         <div class="row g-2">
                              <div class="col-8"><label class="form-label text-white-50 small mb-1">Cidade *</label><input type="text" name="city" class="form-control bg-dark text-white border-secondary reg-input"></div>
                              <div class="col-4"><label class="form-label text-white-50 small mb-1">UF *</label><input type="text" name="state" class="form-control bg-dark text-white border-secondary reg-input"></div>
                         </div>
                         @endif
                    </div>
                    @endif

                    <!-- Card Form for Retry -->
                    <div id="card_form_retry_{{ $order->id }}" class="card-form-container d-none mb-4 text-start bg-dark p-3 rounded border border-secondary shadow-sm" style="--bs-border-opacity: .3;">
                         <h6 class="text-white mb-3 fw-bold"><i class="bi bi-credit-card me-2"></i>Confirmação com Cartão Seguro</h6>
                         
                         @php $userCards = $client->cards ?? collect(); @endphp
                         @if($userCards->count() > 0)
                             <div class="mb-4">
                                 @foreach($userCards as $card)
                                 <div class="form-check mb-2">
                                     <input class="form-check-input vault-selector" type="radio" onchange="toggleVault(this, 'retry_{{ $order->id }}')" name="saved_card_id" id="card_saved_retry_{{ $card->id }}_{{ $order->id }}" value="{{ $card->id }}">
                                     <label class="form-check-label text-white" for="card_saved_retry_{{ $card->id }}_{{ $order->id }}">
                                         Utilizar {{ strtoupper($card->card_brand ?: 'CARTÃO') }} em cofre final {{ $card->last_four }}
                                     </label>
                                 </div>
                                 @endforeach
                                 <div class="form-check mt-3 pt-2 border-top border-secondary">
                                     <input class="form-check-input vault-selector" type="radio" onchange="toggleVault(this, 'retry_{{ $order->id }}')" name="saved_card_id" id="card_new_retry_{{ $order->id }}" value="new" checked>
                                     <label class="form-check-label text-white" for="card_new_retry_{{ $order->id }}">Usar Cartão Inédito</label>
                                 </div>
                             </div>
                         @else
                             <input type="hidden" name="saved_card_id" value="new">
                         @endif

                         <div id="new_card_container_retry_{{ $order->id }}" class="new-card-form">
                             <div class="row g-2 mb-3">
                                  <div class="col-6"><input type="text" name="card_holder" class="form-control bg-dark text-white border-secondary card-input" placeholder="Nome Impresso"></div>
                                  <div class="col-6"><input type="text" name="card_number" class="form-control bg-dark text-white border-secondary card-input" placeholder="Cartão Neutro 0000..."></div>
                             </div>
                             <div class="row g-2 mb-3">
                                  <div class="col-6"><input type="text" name="card_expiry" class="form-control bg-dark text-white border-secondary card-input" placeholder="Validade MM/YY"></div>
                                  <div class="col-6"><input type="password" name="card_cvv" class="form-control bg-dark text-white border-secondary card-input" placeholder="CVV ***"></div>
                             </div>
                             <div class="form-check mt-2 pt-2 border-top border-secondary">
                                 <input class="form-check-input" type="checkbox" name="save_new_card" value="1" id="save_card_retry_{{ $order->id }}" checked>
                                 <label class="form-check-label text-white small" for="save_card_retry_{{ $order->id }}">Criptografar Cartão no Meu Cofre</label>
                             </div>
                         </div>
                         <div class="form-check mt-3 pt-2">
                             <input class="form-check-input card-input lgpd-checkbox" type="checkbox" name="lgpd_consent" value="1" id="lgpd_retry_{{ $order->id }}">
                             <label class="form-check-label text-white opacity-75 small" for="lgpd_retry_{{ $order->id }}">Autorizo Processar LGPD e PCI de Operação Externa.</label>
                         </div>
                    </div>

                </div>
                <div class="modal-footer border-top border-secondary">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">Recuar</button>
                    <button type="submit" class="btn btn-primary rounded-pill fw-bold px-4"><i class="bi bi-shield-lock me-1"></i> Autorizar Novo Pagamento</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endsection

@push('scripts')
<script>
    function toggleFormLogic(radio, packageId) {
        document.querySelectorAll(`#registration_form_${packageId}, #card_form_${packageId}`).forEach(el => {
            if(el) {
                el.classList.add('d-none');
                el.querySelectorAll('input, select').forEach(input => input.required = false);
            }
        });
        
        let missingReg = {{ $missingReg ? 'true' : 'false' }};
        
        if (missingReg) {
            const regContainer = document.getElementById('registration_form_' + packageId);
            if (regContainer) {
                regContainer.classList.remove('d-none');
                regContainer.querySelectorAll('input.reg-input').forEach(input => input.required = true);
            }
        }
        
        if (radio.value === 'credit_card') {
            const cardContainer = document.getElementById('card_form_' + packageId);
            if (cardContainer) {
                cardContainer.classList.remove('d-none');
                checkVaultState(packageId);
            }
        }
    }

    function toggleVault(radio, packageId) {
        checkVaultState(packageId);
    }
    
    function checkVaultState(packageId) {
        const selected = document.querySelector(`input[name="saved_card_id"][id$="_${packageId}"]:checked`);
        const isNew = !selected || selected.value === 'new';
        const newCardBlock = document.getElementById('new_card_container_' + packageId);
        
        if (newCardBlock) {
            if (isNew) {
                newCardBlock.classList.remove('d-none');
                newCardBlock.querySelectorAll('input.card-input').forEach(input => input.required = true);
            } else {
                newCardBlock.classList.add('d-none');
                newCardBlock.querySelectorAll('input.card-input').forEach(input => input.required = false);
            }
        }
        
        const lgpd = document.getElementById('lgpd_' + packageId);
        if(lgpd) lgpd.required = true;
    }
</script>
@endpush
