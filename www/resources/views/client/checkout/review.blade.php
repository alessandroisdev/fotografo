@extends('layouts.public')

@section('title', 'Finalizar Pedido')

@section('content')
<div class="row pt-4 text-white mb-5 justify-content-center">
    <div class="col-12 col-md-8 text-center">
        <h2 class="display-5 fw-bold">Seu Álbum Perfeito</h2>
        <p class="lead">Você escolheu <strong>{{ $totalSelected }}</strong> fotos incríveis. Agora, selecione abaixo o pacote financeiro que mais se adequa ao momento!</p>
    </div>
</div>

<div class="row g-4 justify-content-center mb-5">
    @foreach($packages as $package)
    @php
        $extraPhotos = max(0, $totalSelected - $package->included_photos_count);
        $totalAmount = $package->price + ($extraPhotos * $package->extra_photo_price);
    @endphp
    
    <div class="col-md-5 col-lg-4">
        <div class="card border-0 shadow-lg rounded-4 h-100 bg-dark text-white border {{ $extraPhotos == 0 ? 'border-success' : 'border-secondary' }}" style="--bs-border-opacity: .2;">
            <div class="card-body p-4 p-md-5 d-flex flex-column text-center">
                <h4 class="fw-bold mb-1">{{ $package->name }}</h4>
                <p class="small text-white-50 form-text">{{ $package->description }}</p>
                <hr>
                
                <h1 class="display-4 fw-bold text-primary mb-0"><small class="fs-4 text-white-50">R$</small>{{ number_format($package->price, 2, ',', '.') }}</h1>
                <p class="mt-2 text-white-50"><i class="bi bi-asterisk"></i> Inclui até {{ $package->included_photos_count }} fotos</p>
                
                <ul class="list-unstyled mt-4 mb-4 text-start bg-secondary bg-opacity-25 rounded p-3">
                    <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i> {{ min($totalSelected, $package->included_photos_count) }} fotos garantidas no pacote</li>
                    @if($extraPhotos > 0)
                        <li class="mb-2"><i class="bi bi-plus-lg text-warning me-2"></i> {{ $extraPhotos }} fotos excedentes</li>
                        <li class="mb-0 text-muted small ms-4">+ R$ {{ number_format($extraPhotos * $package->extra_photo_price, 2, ',', '.') }} (R$ {{ number_format($package->extra_photo_price, 2, ',', '.') }} un.)</li>
                    @endif
                </ul>
                
                <div class="mt-auto text-start">
                    <h5 class="fw-bold text-success mb-3 text-center">Total Estimado: R$ {{ number_format($totalAmount, 2, ',', '.') }}</h5>
                    <form action="{{ route('client.checkout.process', $gallery->uuid) }}" method="POST">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        
                        @php
                            $user = Auth::user();
                            $missingReg = empty($user->document) || empty($user->phone) ||
                                          empty($user->zipcode) || empty($user->address) || 
                                          empty($user->address_number) || empty($user->city) || empty($user->state);
                            $userCards = $user->cards ?? collect();
                        @endphp

                        <div class="mb-4">
                            <label class="form-label fw-bold text-white mb-2 d-block">Como deseja pagar?</label>
                            @foreach(\App\Enums\PaymentMethodEnum::cases() as $methodEnum)
                                <div class="form-check mb-2 bg-dark p-2 rounded border border-secondary" style="--bs-border-opacity: .3;">
                                    <input class="form-check-input ms-1 gateway-selector" onchange="toggleFormLogic(this, '{{ $package->id }}')" type="radio" name="payment_method" id="method_{{ $methodEnum->value }}_{{ $package->id }}" value="{{ $methodEnum->value }}" required>
                                    <label class="form-check-label text-white ms-2" for="method_{{ $methodEnum->value }}_{{ $package->id }}">
                                        {{ $methodEnum->label() }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- Formulário Secundário: Cadastro Pendente (Perfil/Endereço) -->
                        @if($missingReg)
                        <div id="registration_form_{{ $package->id }}" class="registration-form-container d-none mb-4 text-start bg-dark bg-opacity-50 p-3 rounded border border-warning" style="--bs-border-opacity: .5;">
                             <h6 class="text-warning mb-3 fw-bold"><i class="bi bi-person-lines-fill me-2"></i>Completar Cadastro de Cobrança (Obrigatório)</h6>
                             <p class="small text-white-50 form-text">Precisamos de seus dados de faturamento (Endereço e Contato).</p>
                             
                             @if(empty($user->document))
                             <div class="mb-3">
                                  <label class="form-label text-white-50 small mb-1">Seu CPF / CNPJ <span class="text-danger">*</span></label>
                                  <input type="text" name="document" class="form-control bg-dark text-white border-secondary reg-input" placeholder="Apenas números">
                             </div>
                             @endif

                             @if(empty($user->phone))
                             <div class="mb-3">
                                  <label class="form-label text-white-50 small mb-1">Telefone / WhatsApp <span class="text-danger">*</span></label>
                                  <input type="text" name="phone" class="form-control bg-dark text-white border-secondary reg-input" placeholder="(11) 90000-0000">
                             </div>
                             @endif

                             @if(empty($user->zipcode))
                             <div class="mb-3">
                                  <label class="form-label text-white-50 small mb-1">CEP <span class="text-danger">*</span></label>
                                  <input type="text" name="zipcode" class="form-control bg-dark text-white border-secondary reg-input" placeholder="00000-000">
                             </div>
                             @endif

                             @if(empty($user->address))
                             <div class="row g-2 mb-3">
                                  <div class="col-8">
                                       <label class="form-label text-white-50 small mb-1">Endereço <span class="text-danger">*</span></label>
                                       <input type="text" name="address" class="form-control bg-dark text-white border-secondary reg-input" placeholder="Rua/Av Principal">
                                  </div>
                                  <div class="col-4">
                                       <label class="form-label text-white-50 small mb-1">Número <span class="text-danger">*</span></label>
                                       <input type="text" name="address_number" class="form-control bg-dark text-white border-secondary reg-input" placeholder="100">
                                  </div>
                             </div>
                             <div class="row g-2 mb-3">
                                  <div class="col-8">
                                       <label class="form-label text-white-50 small mb-1">Cidade <span class="text-danger">*</span></label>
                                       <input type="text" name="city" class="form-control bg-dark text-white border-secondary reg-input" placeholder="São Paulo">
                                  </div>
                                  <div class="col-4">
                                       <label class="form-label text-white-50 small mb-1">UF <span class="text-danger">*</span></label>
                                       <input type="text" name="state" class="form-control bg-dark text-white border-secondary reg-input" placeholder="SP">
                                  </div>
                             </div>
                             @endif
                        </div>
                        @endif

                        <!-- Formulário Transparente de Cartão (Oculto por Padrão) -->
                        <div id="card_form_{{ $package->id }}" class="card-form-container d-none mb-4 text-start bg-dark p-3 rounded border border-secondary shadow-sm" style="--bs-border-opacity: .3;">
                             <h6 class="text-white mb-3 fw-bold"><i class="bi bi-credit-card me-2"></i>Dados do Cofre e Cartão</h6>
                             
                             @if($userCards->count() > 0)
                                 <div class="mb-4 p-2 bg-secondary bg-opacity-10 rounded border border-secondary" style="--bs-border-opacity: .5;">
                                     <label class="form-label text-white-50 small fw-bold mb-2 ps-1">Seus Cartões Salvos</label>
                                     
                                     @foreach($userCards as $card)
                                     <div class="form-check mb-2">
                                         <input class="form-check-input vault-selector" type="radio" onchange="toggleVault(this, '{{ $package->id }}')" name="saved_card_id" id="card_saved_{{ $card->id }}_{{ $package->id }}" value="{{ $card->id }}">
                                         <label class="form-check-label text-white" for="card_saved_{{ $card->id }}_{{ $package->id }}">
                                             <i class="bi bi-credit-card mx-2 text-primary"></i>
                                             {{ strtoupper($card->card_brand ?: 'CARTÃO') }} final {{ $card->last_four }}
                                         </label>
                                     </div>
                                     @endforeach
                                     
                                     <div class="form-check mt-3 pt-2 border-top border-secondary">
                                         <input class="form-check-input vault-selector" type="radio" onchange="toggleVault(this, '{{ $package->id }}')" name="saved_card_id" id="card_new_{{ $package->id }}" value="new" checked>
                                         <label class="form-check-label text-white" for="card_new_{{ $package->id }}">
                                             Usar um Novo Cartão
                                         </label>
                                     </div>
                                 </div>
                             @else
                                 <input type="hidden" name="saved_card_id" value="new">
                             @endif

                             <div id="new_card_container_{{ $package->id }}" class="new-card-form">
                                 <div class="mb-3">
                                      <label class="form-label text-white-50 small mb-1">Nome Impresso no Cartão</label>
                                      <input type="text" name="card_holder" class="form-control bg-dark text-white border-secondary card-input" placeholder="EX: JOAO DA SILVA">
                                 </div>
                                 <div class="mb-3">
                                      <label class="form-label text-white-50 small mb-1">Número do Cartão</label>
                                      <input type="text" name="card_number" class="form-control bg-dark text-white border-secondary card-input" placeholder="0000 0000 0000 0000">
                                 </div>
                                 <div class="row g-2 mb-3">
                                      <div class="col-6">
                                           <label class="form-label text-white-50 small mb-1">Validade (MM/YY)</label>
                                           <input type="text" name="card_expiry" class="form-control bg-dark text-white border-secondary card-input" placeholder="12/28">
                                      </div>
                                      <div class="col-6">
                                           <label class="form-label text-white-50 small mb-1">CVV</label>
                                           <input type="text" name="card_cvv" class="form-control bg-dark text-white border-secondary card-input" placeholder="123">
                                      </div>
                                 </div>

                                 <!-- Salvar Cartão Checkbox -->
                                 <div class="form-check mt-3 border-top border-secondary pt-3">
                                     <input class="form-check-input" type="checkbox" name="save_new_card" value="1" id="save_card_{{ $package->id }}" checked>
                                     <label class="form-check-label text-white small" for="save_card_{{ $package->id }}">
                                         <strong>Salvar Cartão</strong> de forma segura (AES-256) no cofre para compras futuras mais rápidas.
                                     </label>
                                 </div>
                             </div>

                             <!-- Consentimento LGPD Obrigatório (Processamento) -->
                             <div class="form-check mt-3 pt-2">
                                 <input class="form-check-input card-input lgpd-checkbox" type="checkbox" name="lgpd_consent" value="1" id="lgpd_{{ $package->id }}">
                                 <label class="form-check-label text-white opacity-75 small" style="font-size: 0.8rem;" for="lgpd_{{ $package->id }}">
                                     Autorizo o servidor a manipular este meio de pagamento de forma criptografada blindada perante a operadora financeira e os conformes contratuais e LGPD.
                                 </label>
                             </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow">Confirmar e Finalizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    function toggleFormLogic(radio, packageId) {
        // Esconde todos os forms e limpa validations
        document.querySelectorAll('.card-form-container, .registration-form-container').forEach(el => {
            el.classList.add('d-none');
            el.querySelectorAll('input').forEach(input => input.required = false);
        });
        
        let missingReg = {{ $missingReg ? 'true' : 'false' }};
        
        // Se houver dados faltantes (CPF/Phone), exibe a cortina do Cadastro + LGPD pra QUALQUER método
        if (missingReg) {
            const regContainer = document.getElementById('registration_form_' + packageId);
            if (regContainer) {
                regContainer.classList.remove('d-none');
                regContainer.querySelectorAll('input.reg-input').forEach(input => input.required = true);
            }
        }
        
        // Se for Cartão, deve exibir a cortina do Cartão
        if (radio.value === 'credit_card') {
            const cardContainer = document.getElementById('card_form_' + packageId);
            if (cardContainer) {
                cardContainer.classList.remove('d-none');
                // Seta inputs primários como obrigatórios apenas se estivermos em "Novo Cartão"
                // O estado inicial pode forçar
                checkVaultState(packageId);
            }
        }
    }

    function toggleVault(radio, packageId) {
        checkVaultState(packageId);
    }
    
    function checkVaultState(packageId) {
        // Encontra o seletor marcado
        const selected = document.querySelector('input[name="saved_card_id"][id$="_' + packageId + '"]:checked');
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
        
        // Mantem checkbox LGPD sempre required quando Cartão visível
        const lgpd = document.getElementById('lgpd_' + packageId);
        if(lgpd) lgpd.required = true;
    }
</script>
@endpush
