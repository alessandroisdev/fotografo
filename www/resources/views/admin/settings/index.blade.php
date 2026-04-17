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
            <a href="#cloud" data-bs-toggle="tab" class="list-group-item list-group-item-action border-0 rounded p-3">
                <i class="bi bi-cloud-arrow-up-fill text-info me-2"></i> Nuvem e Arquivos
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

                <!-- BUSINESS DATA -->
                <div class="tab-pane fade p-4" id="company">
                    <h5 class="fw-bold mb-4"><i class="bi bi-person-vcard text-secondary me-2"></i> Dados Visíveis no Portal</h5>
                    
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">Nome do Estúdio / Fotógrafo</label>
                            <input type="text" class="form-control" name="studio_name" value="{{ config('settings.studio_name', 'Nome Padrão') }}">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold">WhatsApp Oficial <span class="badge bg-secondary">Op</span></label>
                            <input type="text" class="form-control" name="studio_whatsapp" placeholder="Ex: 551199999999" value="{{ config('settings.studio_whatsapp') }}">
                            <small class="text-muted">Injetará um balão automático de suporte.</small>
                        </div>
                    </div>
                </div>

                <!-- CLOUD & ARCHIVE -->
                <div class="tab-pane fade p-4" id="cloud">
                    <h5 class="fw-bold mb-3"><i class="bi bi-server text-info me-2"></i> Motor de Arquivamento (Nuvem)</h5>
                    <div class="alert alert-light border border-info border-opacity-25 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill text-info me-2"></i> Transfira fotos brutas pesadas (RAWs) do seu VPS para provedores massivos em nuvem automaticamente. Quando um cliente comprar mais fotos, os geradores de ZIP farão a ponte oculta.
                    </div>

                    <div class="mb-4 p-3 bg-light rounded-3 border">
                        <label class="form-label fw-bold text-dark">Nuvem Padrão de Arquivamento RAW</label>
                        <select name="archive_disk" class="form-select bg-white">
                            <option value="local" {{ config('settings.archive_disk', 'local') === 'local' ? 'selected' : '' }}>Servidor Local (Não Arquivar para Nuvem)</option>
                            <option value="s3" {{ config('settings.archive_disk') === 's3' ? 'selected' : '' }}>Amazon AWS S3 / R2 Cloudflare (Recomendado)</option>
                            <option value="google" {{ config('settings.archive_disk') === 'google' ? 'selected' : '' }}>Google Drive API (Básico 15GB)</option>
                        </select>
                        <small class="text-muted mt-2 d-block">Todas as Thumbnails públicas continuarão vivendo em <code>Local</code> sempre para altíssima performance.</small>
                    </div>

                    <ul class="nav nav-pills mt-3 mb-3 border-bottom pb-3" id="cloudTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark bg-transparent" id="s3-tab" data-bs-toggle="pill" data-bs-target="#s3" type="button" role="tab">Amazon S3 / R2</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-success bg-transparent" id="google-tab" data-bs-toggle="pill" data-bs-target="#google" type="button" role="tab">Google Drive</button>
                      </li>
                    </ul>

                    <div class="tab-content">
                        <!-- S3 CONFIGS -->
                        <div class="tab-pane fade show active" id="s3" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">AWS Access Key ID</label>
                                    <input type="text" class="form-control" name="s3_key" value="{{ config('settings.s3_key') }}" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">AWS Secret Access Key</label>
                                    <input type="password" class="form-control" name="s3_secret" value="{{ config('settings.s3_secret') }}" autocomplete="off">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Region (Ex: us-east-1)</label>
                                    <input type="text" class="form-control" name="s3_region" value="{{ config('settings.s3_region') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Bucket Name</label>
                                    <input type="text" class="form-control" name="s3_bucket" value="{{ config('settings.s3_bucket') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Endpoint <span class="badge bg-secondary">Op</span></label>
                                    <input type="text" class="form-control" name="s3_endpoint" value="{{ config('settings.s3_endpoint') }}" placeholder="Para Cloudflare R2 / MinIO">
                                </div>
                            </div>
                        </div>

                        <!-- GOOGLE DRIVE CONFIGS -->
                        <div class="tab-pane fade" id="google" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Google Client ID</label>
                                    <input type="text" class="form-control border-success" name="google_client_id" value="{{ config('settings.google_client_id') }}" autocomplete="off">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Google Client Secret</label>
                                    <input type="password" class="form-control border-success" name="google_client_secret" value="{{ config('settings.google_client_secret') }}" autocomplete="off">
                                </div>
                                <div class="col-md-12">
                                    <h6 class="fw-bold mb-2">Autenticação Automática OAuth 2.0</h4>
                                    @if(config('settings.google_client_id') && config('settings.google_client_secret'))
                                        <div class="d-flex align-items-center mb-3">
                                            <a href="{{ route('admin.settings.google.auth') }}" class="btn btn-outline-success fw-bold px-4 py-2 me-3">
                                                <i class="bi bi-google"></i> Autenticar no Google Drive
                                            </a>
                                            @if(config('settings.google_refresh_token'))
                                                <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Máquina Conectada e Autenticada!</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="alert alert-warning py-2 mb-3">
                                            <i class="bi bi-exclamation-triangle"></i> Preencha e salve o Client ID e Client Secret primeiro para habilitar o botão de Autenticação Automática Mágica.
                                        </div>
                                    @endif
                                    
                                    <details class="w-100">
                                        <summary class="text-muted small cursor-pointer" style="cursor: pointer;">Avançado: Entrar com o Refresh Token (Playground Mode) manualmente</summary>
                                        <div class="mt-3 p-3 bg-light border border-success border-opacity-25 rounded">
                                            <label class="form-label fw-bold">Refresh Token Físico Criptografado</label>
                                            <input type="password" class="form-control border-success" name="google_refresh_token" value="{{ config('settings.google_refresh_token') }}" autocomplete="off">
                                            <small class="text-muted d-block mt-1">Geralmente isso é preenchido automaticamente ao clicar no Botão acima, não digite se não souber operar a API Restritiva.</small>
                                        </div>
                                    </details>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">ID da Pasta Raiz (Folder ID)</label>
                                    <input type="text" class="form-control" name="google_folder_id" value="{{ config('settings.google_folder_id') }}" placeholder="Ex: 1A2b3C4d5E6f_T...">
                                </div>
                            </div>
                        </div>
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
