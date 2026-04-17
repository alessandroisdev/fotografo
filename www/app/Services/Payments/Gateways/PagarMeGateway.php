<?php

namespace App\Services\Payments\Gateways;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use App\Services\Payments\DTO\PaymentResponse;
use Illuminate\Support\Facades\Log;

class PagarMeGateway implements PaymentGatewayInterface
{
    private bool $isSandbox;
    private string $apiKey;

    public function __construct()
    {
        $this->isSandbox = config('settings.pagarme_environment', 'sandbox') !== 'production';
        $this->apiKey = config('settings.pagarme_api_key', '');
    }

    public function generateCharge(Order $order): PaymentResponse
    {
        try {
            // PagarMe Core V5 requer formatação estrita.
            $document = preg_replace('/[^0-9]/', '', $order->user->document ?? '');
            if (empty($document) || (strlen($document) !== 11 && strlen($document) !== 14)) {
                $document = \Illuminate\Support\Facades\App::environment('local') ? '19119119100' : '00000000000';
            }

            // O PagarMe exige array de 'phones' para transparent charges às vezes. Inject dummy if none.
            $phone = '999999999';
            $area = '11';

            // Branching base payment method
            $baseCustomer = [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'document' => $document,
                'type' => strlen($document) === 14 ? 'company' : 'individual',
                'phones' => [
                    'mobile_phone' => [
                        'country_code' => '55',
                        'area_code' => $area,
                        'number' => $phone
                    ]
                ]
            ];

            $baseItems = [[
                'amount' => round($order->total_amount, 2) * 100, // Centavos V5
                'description' => 'Fotografias Finais - ' . $order->gallery->name,
                'quantity' => 1,
                'code' => (string) $order->id
            ]];

            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                // Fluxo Transparente Pix - Ordem fechada contendo o Payment Method Pix
                $payload = [
                    'code' => $order->uuid,
                    'customer' => $baseCustomer,
                    'items' => $baseItems,
                    'payments' => [[
                        'payment_method' => 'pix',
                        'pix' => [
                            'expires_in' => 3600,
                            'additional_information' => [
                                ['Name' => 'Referencia', 'Value' => $order->uuid]
                            ]
                        ]
                    ]]
                ];

                $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                    ->timeout(15)
                    ->post('https://api.pagar.me/core/v5/orders', $payload);

                if ($response->failed()) {
                    Log::error('PagarMe API Pix Error', ['body' => $response->json()]);
                    return PaymentResponse::make(false, 'Servidor global Pagar.me não processou a ordem Pix.');
                }
                
                $pgmOrder = $response->json();
                $qrCodeUrl = $pgmOrder['charges'][0]['last_transaction']['qr_code_url'] ?? null;
                $chargeId = $pgmOrder['charges'][0]['id'] ?? $pgmOrder['id'];

                $order->update(['status' => 'pending', 'gateway_transaction_id' => $chargeId]);

                return PaymentResponse::make(
                    success: true,
                    message: 'Gerando o QR Code Pix pelo Pagar.me...',
                    redirectUrl: $qrCodeUrl, // Serve o QrCode Url (em um cenário real seria interessante a string bruta copy+paste também)
                    externalId: $chargeId
                );

            } else {
                // Fluxo Cartões / Boleto: Payment Link API para interface de alta conversão
                $payload = [
                    'name' => 'Pedido #' . substr($order->uuid, 0, 8),
                    'items' => $baseItems,
                    'payment_settings' => [
                        'accepted_payment_methods' => ['credit_card', 'boleto'],
                        'credit_card_settings' => [
                            'installments_setup' => [
                                'interest_type' => 'simple'
                            ]
                        ]
                    ],
                    'customer' => $baseCustomer,
                    'metadata' => [
                        'uuid' => (string) $order->uuid
                    ]
                ];

                $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                    ->timeout(15)
                    ->post('https://api.pagar.me/core/v5/paymentlinks', $payload);

                if ($response->failed()) {
                    Log::error('PagarMe API Payment Link Error', ['body' => $response->json()]);
                    return PaymentResponse::make(false, 'Servidor global Pagar.me não responde.' . $response->body());
                }

                $order->update(['status' => 'pending']);
                $pgmLink = $response->json();

                return PaymentResponse::make(
                    success: true,
                    message: 'Encaminhando ao Link Único Seguro do Pagar.me',
                    redirectUrl: $pgmLink['url'] ?? null,
                    externalId: $pgmLink['id'] ?? null // ID do link, no webhook virá o Code = OrderUuid
                );
            }
        } catch (\Exception $e) {
            Log::error('PagarMe Fallback: ' . $e->getMessage());
            return PaymentResponse::make(false, 'Falha interna na API Pagar.Me.');
        }
    }

    public function refundCharge(Order $order, ?float $amount = null): bool
    {
        if (empty($order->gateway_transaction_id)) {
            Log::error('PagarMe Refund: No charge ID on file.');
            return false;
        }

        try {
            $payload = [];
            if (!is_null($amount)) {
                $payload['amount'] = round($amount, 2) * 100;
            }

            // O webhook substituiu gateway_transaction_id pra o "charge_id" (ch_...)
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                ->timeout(15)
                ->delete('https://api.pagar.me/core/v5/charges/' . $order->gateway_transaction_id, $payload);

            if ($response->failed()) {
                Log::error('PagarMe Refund Error', ['b' => $response->json()]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('PagarMe Refund Exc: ' . $e->getMessage());
            return false;
        }
    }
}
