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
    private string $baseUrl;

    public function __construct()
    {
        $this->isSandbox = config('settings.pagarme_environment', 'sandbox') !== 'production';
        $this->apiKey = config('settings.pagarme_api_key', '');
        
        $this->baseUrl = 'https://api.pagar.me/core/v5';
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

            $paymentsObj = [];
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                $paymentsObj = [[
                    'payment_method' => 'pix',
                    'pix' => [
                        'expires_in' => 3600,
                        'additional_information' => [
                            ['Name' => 'Referencia', 'Value' => $order->uuid]
                        ]
                    ]
                ]];
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BOLETO->value) {
                $paymentsObj = [[
                    'payment_method' => 'boleto',
                    'boleto' => [
                        'instructions' => 'Pagamento de Pacote Fotográfico.',
                        'due_at' => date('Y-m-d\TH:i:s\Z', strtotime('+3 days')),
                        'document_number' => $document,
                        'type' => 'DM'
                    ]
                ]];
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::CREDIT_CARD->value && !empty($paymentData)) {
                $paymentsObj = [[
                    'payment_method' => 'credit_card',
                    'credit_card' => [
                        'installments' => 1,
                        'statement_descriptor' => 'FOTOGRAFO',
                        'card' => [
                            'number' => preg_replace('/[^0-9]/', '', $paymentData['card_number']),
                            'holder_name' => $paymentData['card_holder'],
                            'exp_month' => (int) explode('/', $paymentData['card_expiry'])[0],
                            'exp_year' => (int) explode('/', $paymentData['card_expiry'])[1],
                            'cvv' => $paymentData['card_cvv'],
                            'billing_address' => [
                                'line_1' => 'Rua Principal, 100',
                                'zip_code' => '01001000',
                                'city' => 'Sao Paulo',
                                'state' => 'SP',
                                'country' => 'BR'
                            ]
                        ]
                    ]
                ]];
            } else {
                 return PaymentResponse::make(false, 'Modo desabilitado no PagarMe.');
            }

            // Disparo Server to Server Nativo (Transparente em Tudo)
            $payload = [
                'code' => $order->uuid,
                'customer' => $baseCustomer,
                'items' => $baseItems,
                'payments' => $paymentsObj
            ];

            $response = \Illuminate\Support\Facades\Http::withBasicAuth($this->apiKey, '')
                ->timeout(15)
                ->post("{$this->baseUrl}/orders", $payload);

            if ($response->failed()) {
                Log::error('PagarMe API Transparent Order Error', ['body' => $response->json()]);
                return PaymentResponse::make(false, 'Servidor global Pagar.me não processou a ordem ' . ($order->gateway) . '.');
            }

            $pgmOrder = $response->json();
            $chargeId = $pgmOrder['charges'][0]['id'] ?? $pgmOrder['id'];
            $gatewayPayload = null;

            // Extração Assíncrona Nativa (Transparente)
            if ($order->gateway === \App\Enums\PaymentMethodEnum::PIX->value) {
                $order->update(['status' => 'pending', 'gateway_transaction_id' => $chargeId]);
                $gatewayPayload = [
                    'type' => 'pix',
                    'qr_code' => $pgmOrder['charges'][0]['last_transaction']['qr_code'] ?? null,
                    'qr_code_base64' => $pgmOrder['charges'][0]['last_transaction']['qr_code_url'] ?? null, // QrCodeUrl acts as image source if needed
                ];
                $msg = 'QR Code PIX gerado com blindagem Server-Side (Pagar.me)!';
            } elseif ($order->gateway === \App\Enums\PaymentMethodEnum::BOLETO->value) {
                $order->update(['status' => 'pending', 'gateway_transaction_id' => $chargeId]);
                $gatewayPayload = [
                    'type' => 'boleto',
                    'barcode' => $pgmOrder['charges'][0]['last_transaction']['line'] ?? null,
                    'pdf_url' => $pgmOrder['charges'][0]['last_transaction']['pdf'] ?? null,
                ];
                $msg = 'Boleto Eletrônico Pagar.me emitido localmente!';
            } else {
                $status = $pgmOrder['status'] ?? 'pending';
                if ($status === 'paid') {
                     $order->update(['status' => \App\Enums\OrderStatusEnum::PAID, 'paid_at' => now(), 'gateway_transaction_id' => $chargeId]);
                     $msg = 'Transação Transparente V5 (Cartão) Autorizada!';
                } else {
                     $order->update(['status' => 'pending', 'gateway_transaction_id' => $chargeId]);
                     $msg = "Transação capturada ({$status}), no aguardo da liquidação da bandeira.";
                }
            }

            return PaymentResponse::make(
                success: true,
                message: $msg,
                redirectUrl: null, // Escapes Anulados - Transparência Unificada!
                externalId: $chargeId,
                payload: $gatewayPayload
            );
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
                ->delete("{$this->baseUrl}/charges/" . $order->gateway_transaction_id, $payload);

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
