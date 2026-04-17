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
                        
                        @if(empty(Auth::user()->document))
                        <div class="mb-4 text-start">
                             <label class="form-label fw-bold text-white mb-2">Seu CPF (Para Faturamento) <span class="text-danger">*</span></label>
                             <input type="text" name="document" class="form-control bg-dark text-white border-secondary" required placeholder="Digite apenas números">
                             <div class="form-text text-white-50 small"><i class="bi bi-shield-lock me-1"></i> Necessário p/ emissão segura do Pix.</div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-bold text-white mb-2 d-block">Como deseja pagar?</label>
                            @foreach(\App\Enums\PaymentMethodEnum::cases() as $methodEnum)
                                <div class="form-check mb-2 bg-dark p-2 rounded border border-secondary" style="--bs-border-opacity: .3;">
                                    <input class="form-check-input ms-1 gateway-selector" onchange="toggleCardForm(this, '{{ $package->id }}')" type="radio" name="payment_method" id="method_{{ $methodEnum->value }}_{{ $package->id }}" value="{{ $methodEnum->value }}" required>
                                    <label class="form-check-label text-white ms-2" for="method_{{ $methodEnum->value }}_{{ $package->id }}">
                                        {{ $methodEnum->label() }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- Formulário Transparente de Cartão (Oculto por Padrão) -->
                        <div id="card_form_{{ $package->id }}" class="card-form-container d-none mb-4 text-start bg-dark p-3 rounded border border-secondary shadow-sm" style="--bs-border-opacity: .3;">
                             <h6 class="text-white mb-3 fw-bold"><i class="bi bi-credit-card me-2"></i>Dados do Cartão (Pagamento Seguro)</h6>
                             <div class="mb-3">
                                  <label class="form-label text-white-50 small mb-1">Nome Impresso no Cartão</label>
                                  <input type="text" name="card_holder" class="form-control bg-dark text-white border-secondary card-input" placeholder="EX: JOAO DA SILVA">
                             </div>
                             <div class="mb-3">
                                  <label class="form-label text-white-50 small mb-1">Número do Cartão</label>
                                  <input type="text" name="card_number" class="form-control bg-dark text-white border-secondary card-input" placeholder="0000 0000 0000 0000">
                             </div>
                             <div class="row g-2">
                                  <div class="col-6">
                                       <label class="form-label text-white-50 small mb-1">Validade (MM/YY)</label>
                                       <input type="text" name="card_expiry" class="form-control bg-dark text-white border-secondary card-input" placeholder="12/28">
                                  </div>
                                  <div class="col-6">
                                       <label class="form-label text-white-50 small mb-1">CVV</label>
                                       <input type="text" name="card_cvv" class="form-control bg-dark text-white border-secondary card-input" placeholder="123">
                                  </div>
                             </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow">Confirmar Pacote e Pagar</button>
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
    function toggleCardForm(radio, packageId) {
        // Esconde todos os forms e limpa validations
        document.querySelectorAll('.card-form-container').forEach(el => {
            el.classList.add('d-none');
            el.querySelectorAll('input').forEach(input => input.required = false);
        });
        
        // Se escolheu credit_card, mostra o container correspondente e seta required
        if (radio.value === 'credit_card') {
            const container = document.getElementById('card_form_' + packageId);
            if (container) {
                container.classList.remove('d-none');
                container.querySelectorAll('input').forEach(input => input.required = true);
            }
        }
    }
</script>
@endpush
