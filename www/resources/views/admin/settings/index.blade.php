@extends('layouts.admin')

@section('title', 'Configurações')
@section('header_title', 'Configurações Globais do Sistema')

@section('content')
<div class="row align-items-start g-4">
    <div class="col-12 col-md-4">
        <!-- Settings Nav List -->
        <div class="list-group list-group-flush shadow-sm rounded-4 bg-white p-2">
            <a href="#ui" data-bs-toggle="tab" class="list-group-item list-group-item-action border-0 rounded p-3 active">
                <i class="bi bi-palette-fill text-primary me-2"></i> Identidade Visual
            </a>
            <a href="#payment" data-bs-toggle="tab" class="list-group-item list-group-item-action border-0 rounded p-3">
                <i class="bi bi-credit-card-fill text-success me-2"></i> Gestão de Pagamentos
            </a>
            <a href="#company" data-bs-toggle="tab" class="list-group-item list-group-item-action border-0 rounded p-3">
                <i class="bi bi-buildings-fill text-secondary me-2"></i> Dados Comerciais
            </a>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <form action="{{ route('admin.settings.store') }}" method="POST">
            @csrf
            <div class="tab-content card border-0 shadow-sm rounded-4">
                
                <!-- UI THEME -->
                <div class="tab-pane fade show active p-4" id="ui">
                    <h5 class="fw-bold mb-4"><i class="bi bi-brush text-primary me-2"></i> Cores e Customização</h5>
                    
                    <div class="row g-4 align-items-center">
                        <div class="col-8 col-md-9">
                            <label class="form-label fw-bold">Cor Primária (Tema Geral)</label>
                            <p class="text-muted small mb-0">Essa cor vai sobrescrever automaticamente todos os botões, detalhes e destaques do Painel Administrativo e do Site Público dos clientes (SaaS Labeling).</p>
                        </div>
                        <div class="col-4 col-md-3 text-end">
                            <input type="color" class="form-control form-control-color w-100 float-end border-0 shadow-sm" name="primary_color" value="{{ config('settings.primary_color', '#0d6efd') }}" title="Escolha sua Cor">
                        </div>
                    </div>
                </div>

                <!-- PAYMENT MULTIPLEXER -->
                <div class="tab-pane fade p-4" id="payment">
                    <h5 class="fw-bold mb-3"><i class="bi bi-diagram-3-fill text-success me-2"></i> Multiplexador de Gateways</h5>
                    <div class="alert alert-light border border-success border-opacity-25" role="alert">
                        <i class="bi bi-shield-lock-fill text-success me-2"></i> Defina qual Instituição Financeira processará cada tipo de transação (PIX, Cartão e Boleto). O sistema roteará os pagamentos dos clientes magicamente.
                    </div>

                    <div class="row g-4 mt-2">
                        @foreach(\App\Enums\PaymentMethodEnum::cases() as $method)
                            @if($method->value === 'manual_cash') @continue @endif
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-primary">{{ $method->label() }} via:</label>
                                <select name="gateway_{{ $method->value }}" class="form-select border-0 shadow-sm bg-light">
                                    <option value="">-- Desativado --</option>
                                    @foreach(\App\Enums\PaymentGatewayEnum::cases() as $gateway)
                                        @if($gateway->value === 'manual') @continue @endif
                                        <option value="{{ $gateway->value }}" {{ config('settings.gateway_' . $method->value) == $gateway->value ? 'selected' : '' }}>
                                            {{ $gateway->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                    
                    <hr class="mt-5 mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-key-fill text-secondary me-2"></i> Chaves Secretas Unificadas</h5>
                    <p class="small text-muted mb-4">As chaves de API secretas das instituições financeiras configuradas acima.</p>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Asaas | Access Token</label>
                            <input type="password" class="form-control bg-light" name="asaas_api_key" value="{{ config('settings.asaas_api_key') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">MercadoPago | Access Token</label>
                            <input type="password" class="form-control bg-light" name="mercadopago_token" value="{{ config('settings.mercadopago_token') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Stripe | Secret Key</label>
                            <input type="password" class="form-control bg-light" name="stripe_key" value="{{ config('settings.stripe_key') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Pagar.Me | Secret Key</label>
                            <input type="password" class="form-control bg-light" name="pagarme_key" value="{{ config('settings.pagarme_key') }}">
                        </div>
                    </div>
                </div>

                <!-- COMPANY -->
                <div class="tab-pane fade p-4" id="company">
                    <h5 class="fw-bold mb-3"><i class="bi bi-info-circle-fill text-secondary me-2"></i> Identificação do Fotógrafo</h5>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nome / Título da Marca</label>
                        <input type="text" class="form-control" name="site_title" value="{{ config('settings.site_title', 'Fotógrafo Pró') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">WhatsApp / Contato Suporte (Rodapé do Cliente)</label>
                        <input type="text" class="form-control mask-phone" name="support_whatsapp" value="{{ config('settings.support_whatsapp') }}">
                    </div>
                </div>

                <!-- Global Form Footer Actions -->
                <div class="card-footer bg-light border-top p-4 d-flex justify-content-end rounded-bottom-4 mt-2">
                    <button type="submit" class="btn btn-primary btn-lg shadow-sm rounded-pill px-5 fw-bold"><i class="bi bi-save me-2"></i> Salvar Parametrizações</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
