<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class MercadoPagoGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $publicKey;
    private string $accessToken;

    public function __construct()
    {
        $this->isSandbox = config('settings.mercadopago_environment', 'sandbox') !== 'production';
        $this->publicKey = config('settings.mercadopago_public_key', '');
        $this->accessToken = config('settings.mercadopago_access_token', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // Branching implementation: se for PIX direto, usar API v1 Transparente do Mercado Pago.
            // O Order salva o "gateway" como o payment_method_id selecionado na nossa base.
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                // Monta Transparente API
                $document = preg_replace('/[^0-9]/', '', $order->user->document ?? '');
                // Mercado Pago Sandbox/Production exige CPF Válido. Se for ambiente de testes, injeta CPF Fake padrão MP.
                if (empty($document) || (strlen($document) !== 11 && strlen($document) !== 14)) {
                    $document = \Illuminate\Support\Facades\App::environment('local') ? '19119119100' : '00000000000';
                }

                $payload = [
                    'transaction_amount' => round($order->total_amount, 2),
                    'description' => 'Fotografias Finais - ' . $order->gallery->name,
                    'payment_method_id' => 'pix',
                    'payer' => [
                        'email' => $order->user->email,
                        'first_name' => explode(' ', $order->user->name)[0],
                        'identification' => [
                            'type' => strlen($document) === 14 ? 'CNPJ' : 'CPF',
                            'number' => $document
                        ]
                    ],
                    'external_reference' => $order->uuid
                ];

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'X-Idempotency-Key' => $order->uuid,
                    'Content-Type' => 'application/json'
                ])->timeout(15)->post('https://api.mercadopago.com/v1/payments', $payload);

            } else {
                // Checkout Pro padrão para Cartões e Boleto
                $dashboardUrl = route('client.dashboard');
                $payload = [
                    'items' => [
                        [
                            'title' => 'Fotografias Finais - ' . $order->gallery->name,
                            'description' => 'Acesso definitivo ao Acervo Fotográfico.',
                            'quantity' => 1,
                            'currency_id' => 'BRL',
                            'unit_price' => round($order->total_amount, 2),
                        ]
                    ],
                    'payer' => [
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                    ],
                    'external_reference' => $order->uuid,
                    'back_urls' => [
                        'success' => $dashboardUrl,
                        'pending' => $dashboardUrl,
                        'failure' => $dashboardUrl
                    ]
                ];

                if (!\Illuminate\Support\Facades\App::environment('local')) {
                    $payload['auto_return'] = 'approved';
                }

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ])->timeout(15)->post('https://api.mercadopago.com/checkout/preferences', $payload);
            }

            if ($response->failed()) {
                Log::error('MercadoPago Checkout/Payment Error', ['res' => $response->json(), 'route' => $order->gateway]);
                return PaymentResponse::make(false, 'Integração indisponível com a malha do Mercado Pago no momento (Verifique suas credenciais de Access Token ou pendências do titular na plataforma).');
            }

            $order->update(['status' => 'pending']);

            $payloadRes = $response->json();
            
            // Tratamento de Resposta
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                // Pagamento Pix Transparente
                $redirectUrl = $payloadRes['point_of_interaction']['transaction_data']['ticket_url'] ?? null;
                $externalId = $payloadRes['id'] ?? null;
                $message = 'Redirecionando para o QR Code PIX (Mercado Pago)...';
            } else {
                // Preference de Cartão / Boleto
                $redirectKey = $this->isSandbox ? 'sandbox_init_point' : 'init_point';
                $redirectUrl = $payloadRes[$redirectKey] ?? null;
                $externalId = $payloadRes['id'] ?? null; // ID da Preference
                $message = 'Encaminhando ao Cofre Seguro Automático do Mercado Pago...';
            }

            return PaymentResponse::make(
                success: true,
                message: $message,
                redirectUrl: $redirectUrl,
                externalId: $externalId
            );

        } catch (\Exception $e) {
            Log::error('MercadoPago Gateway Falha Rede: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Houve colisão sistêmica temporária. Evite duas vias e tente amanhã.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        // Se a transação for preference_id não dá pra estornar. Só dá se o webhook tiver atualizado com 'payment_id' numérico do MP.
        // O webhook MP deve atualizar o "gateway_transaction_id" pra o Numeric ID na aprovação.
        if (empty($order->gateway_transaction_id) || str_starts_with($order->gateway_transaction_id, 'pref_')) {
            Log::error('MercadoPago Error: Impossível realizar estorno. Payload ausente do Webhook do Pagamento ou Misto.');
            return false;
        }

        try {
            $payload = [];
            if (!is_null($amount)) {
                $payload['amount'] = round($amount, 2);
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-Idempotency-Key' => $order->uuid . rand(),
                'Authorization' => 'Bearer ' . $this->accessToken,
            ])->timeout(15)->post('https://api.mercadopago.com/v1/payments/' . $order->gateway_transaction_id . '/refunds', $payload);

            if ($response->failed()) {
                Log::error('MercadoPago Refund Fallback', ['b' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('MP Refund Exc: ' . $e->getMessage());
            return false;
        }
    }
}
