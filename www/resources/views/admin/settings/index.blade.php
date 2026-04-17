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
                    
                    <hr class="mt-5 mb-4 border-secondary">
                    <h5 class="fw-bold mb-3"><i class="bi bi-key-fill text-secondary me-2"></i> Credenciais e Isolamento de Ambiente (Sandbox / Produção)</h5>
                    <p class="small text-muted mb-4">A segurança de rotas é feita isoladamente. Você pode manter o Paypal de testes enquanto assina cartões reais pelo Stripe simultaneamente.</p>

                    <div class="accordion shadow-sm" id="accordionGateways">
                        
                        <!-- ASAAS -->
                        <div class="accordion-item border-0 mb-3 bg-light rounded">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded fw-bold text-dark border" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAsaas">
                                <i class="bi bi-bank me-2 text-primary"></i> Asaas Gateway
                            </button>
                            </h2>
                            <div id="collapseAsaas" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                            <div class="accordion-body bg-white border border-top-0 rounded-bottom">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ambiente de Execução</label>
                                    <select name="asaas_environment" class="form-select bg-light">
                                        <option value="sandbox" {{ config('settings.asaas_environment') !== 'production' ? 'selected' : '' }}>Homologação (Sandbox)</option>
                                        <option value="production" {{ config('settings.asaas_environment') === 'production' ? 'selected' : '' }}>Produção (Valendo Dinheiro Real)</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold">API Key (Access Token)</label>
                                    <input type="password" class="form-control bg-light" name="asaas_api_key" value="{{ config('settings.asaas_api_key') }}" placeholder="$aact_...">
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- MERCADO PAGO -->
                        <div class="accordion-item border-0 mb-3 bg-light rounded">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded fw-bold text-dark border" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMP">
                                <i class="bi bi-bag-check me-2 text-info"></i> Mercado Pago
                            </button>
                            </h2>
                            <div id="collapseMP" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                            <div class="accordion-body bg-white border border-top-0 rounded-bottom">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ambiente de Execução</label>
                                    <select name="mercadopago_environment" class="form-select bg-light">
                                        <option value="sandbox" {{ config('settings.mercadopago_environment') !== 'production' ? 'selected' : '' }}>Homologação (Testes)</option>
                                        <option value="production" {{ config('settings.mercadopago_environment') === 'production' ? 'selected' : '' }}>Produção Oficial</option>
                                    </select>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">Public Key (Frontend)</label>
                                        <input type="password" class="form-control bg-light" name="mercadopago_public_key" value="{{ config('settings.mercadopago_public_key') }}" placeholder="TEST-xxxx...">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">Access Token (Backend)</label>
                                        <input type="password" class="form-control bg-light" name="mercadopago_access_token" value="{{ config('settings.mercadopago_access_token') }}" placeholder="TEST-0000...">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- STRIPE -->
                        <div class="accordion-item border-0 mb-3 bg-light rounded">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded fw-bold text-dark border" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStripe">
                                <i class="bi bi-credit-card-2-front me-2 text-primary"></i> Stripe Global
                            </button>
                            </h2>
                            <div id="collapseStripe" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                            <div class="accordion-body bg-white border border-top-0 rounded-bottom">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ambiente de Execução</label>
                                    <select name="stripe_environment" class="form-select bg-light">
                                        <option value="sandbox" {{ config('settings.stripe_environment') !== 'production' ? 'selected' : '' }}>Modo Teste (Test Mode)</option>
                                        <option value="production" {{ config('settings.stripe_environment') === 'production' ? 'selected' : '' }}>Modo Produção</option>
                                    </select>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">Publishable Key</label>
                                        <input type="password" class="form-control bg-light" name="stripe_publishable_key" value="{{ config('settings.stripe_publishable_key') }}" placeholder="pk_test_...">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">Secret Key</label>
                                        <input type="password" class="form-control bg-light" name="stripe_secret_key" value="{{ config('settings.stripe_secret_key') }}" placeholder="sk_test_...">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- PAYPAL -->
                        <div class="accordion-item border-0 mb-3 bg-light rounded">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded fw-bold text-dark border" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePaypal">
                                <i class="bi bi-paypal me-2 text-primary"></i> PayPal Business
                            </button>
                            </h2>
                            <div id="collapsePaypal" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                            <div class="accordion-body bg-white border border-top-0 rounded-bottom">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ambiente de Operação</label>
                                    <select name="paypal_environment" class="form-select bg-light">
                                        <option value="sandbox" {{ config('settings.paypal_environment') !== 'production' ? 'selected' : '' }}>Ambiente Sandbox (Mock)</option>
                                        <option value="production" {{ config('settings.paypal_environment') === 'production' ? 'selected' : '' }}>Ambiente Live Oficial</option>
                                    </select>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">REST Oauth Client ID</label>
                                        <input type="password" class="form-control bg-light" name="paypal_client_id" value="{{ config('settings.paypal_client_id') }}">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label fw-bold">REST Oauth Secret</label>
                                        <input type="password" class="form-control bg-light" name="paypal_secret" value="{{ config('settings.paypal_secret') }}">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>

                        <!-- PAGAR ME -->
                        <div class="accordion-item border-0 mb-3 bg-light rounded">
                            <h2 class="accordion-header">
                            <button class="accordion-button collapsed rounded fw-bold text-dark border" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePagarme">
                                <i class="bi bi-wallet2 me-2 text-success"></i> Pagar.Me
                            </button>
                            </h2>
                            <div id="collapsePagarme" class="accordion-collapse collapse" data-bs-parent="#accordionGateways">
                            <div class="accordion-body bg-white border border-top-0 rounded-bottom">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ambiente</label>
                                    <select name="pagarme_environment" class="form-select bg-light">
                                        <option value="sandbox" {{ config('settings.pagarme_environment') !== 'production' ? 'selected' : '' }}>Testes</option>
                                        <option value="production" {{ config('settings.pagarme_environment') === 'production' ? 'selected' : '' }}>Produção</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold">API Secret Key</label>
                                    <input type="password" class="form-control bg-light" name="pagarme_api_key" value="{{ config('settings.pagarme_api_key') }}" placeholder="sk_test_...">
                                </div>
                            </div>
                            </div>
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
